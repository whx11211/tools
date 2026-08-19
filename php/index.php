<?php

/**
 * 配置（都可用环境变量覆盖，不要把敏感值写进仓库）
 *   TOOLS_PHP_BIN         执行用户代码的 php 可执行文件绝对路径
 *   TOOLS_RUN_TIMEOUT     用户代码超时秒数，默认 5
 *   TOOLS_OPEN_BASEDIR    用户代码可访问的目录，默认系统临时目录
 *   TOOLS_URL_ALLOWLIST   curl 目标域名白名单，逗号分隔，支持 .example.com 匹配子域
 *   TOOLS_CURL_INSECURE   置 1 时跳过 https 证书校验（仅用于内网自签名证书）
 */

const RUN_MAX_OUTPUT = 1048576; // 输出上限 1M，防止刷屏撑爆内存

switch ($_REQUEST['type'] ?? '') {
	case 'run':
		echo run_code((string)($_POST['code'] ?? ''));
		break;
	case 'version':
		echo "document.write('" . PHP_VERSION . "');";
		break;
	case 'curl':
		$data = $_POST;
		$url = (string)($data['url'] ?? '');
		$is_post = strtolower((string)($data['request_type'] ?? '')) === 'post';
		unset($data['url'], $data['request_type']);
		try {
			echo curl($url, $data, $is_post);
		}
		catch (Exception $e) {
			http_response_code(400);
			echo 'request blocked: ' . $e->getMessage();
		}
		break;
	default:
		echo "type error";
		break;
}

/* ------------------------------------------------------------------ *
 * 用户代码执行：写临时文件后交给独立子进程，不在 web 进程里 eval
 * ------------------------------------------------------------------ */

function run_code($code) {
	$php = php_binary();
	if (!$php) {
		return '找不到 php 可执行文件，请设置环境变量 TOOLS_PHP_BIN';
	}

	$timeout = (int)(getenv('TOOLS_RUN_TIMEOUT') ?: 5);
	$basedir = getenv('TOOLS_OPEN_BASEDIR') ?: sys_get_temp_dir();

	$file = tempnam(sys_get_temp_dir(), 'run_');
	file_put_contents($file, $code);

	// 数组形式不经过 shell，无需拼接转义；PHP 7.4 起支持
	$argv = array(
		$php,
		'-d', 'open_basedir=' . $basedir,
		'-d', 'memory_limit=64M',
		'-d', 'display_errors=1',
		'-d', 'max_execution_time=' . $timeout,
		$file,
	);
	if (PHP_VERSION_ID < 70400) {
		$argv = implode(' ', array_map('escapeshellarg', $argv));
	}

	$pipes = array();
	$proc = proc_open(
		$argv,
		array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')),
		$pipes,
		sys_get_temp_dir()
	);

	if (!is_resource($proc)) {
		unlink($file);
		return '子进程启动失败';
	}

	fclose($pipes[0]);
	stream_set_blocking($pipes[1], false);
	stream_set_blocking($pipes[2], false);

	$out = '';
	$killed = false;
	$deadline = microtime(true) + $timeout;

	while (true) {
		$running = proc_get_status($proc);
		$out .= (string)stream_get_contents($pipes[1]);
		$out .= (string)stream_get_contents($pipes[2]);

		if (strlen($out) > RUN_MAX_OUTPUT) {
			$out = substr($out, 0, RUN_MAX_OUTPUT) . "\n\n[输出超过 1M，已截断]";
			$killed = true;
			break;
		}
		if (!$running['running']) {
			break;
		}
		if (microtime(true) > $deadline) {
			$out .= "\n\n[执行超过 {$timeout} 秒，已终止]";
			$killed = true;
			break;
		}
		usleep(20000);
	}

	if ($killed) {
		proc_terminate($proc, 9);
	}
	fclose($pipes[1]);
	fclose($pipes[2]);
	proc_close($proc);
	unlink($file);

	return $out;
}

function php_binary() {
	$env = getenv('TOOLS_PHP_BIN');
	if ($env && is_executable($env)) {
		return $env;
	}
	// web 环境下 PHP_BINARY 可能是 php-fpm，不能拿来跑脚本
	if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
		return PHP_BINARY;
	}
	foreach (array('/opt/homebrew/bin/php', '/usr/local/bin/php', '/usr/bin/php') as $path) {
		if (is_executable($path)) {
			return $path;
		}
	}
	return '';
}

/* ------------------------------------------------------------------ *
 * 接口代理：限制协议、域名白名单、拒绝内网地址、锁定解析结果
 * ------------------------------------------------------------------ */

function curl($url, $post_data, $is_post) {
	list($host, $port, $ip) = check_url($url);

	$post_data = http_build_query($post_data);

	$re = curl_init();
	curl_setopt($re, CURLOPT_HEADER, 0);
	curl_setopt($re, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($re, CURLOPT_TIMEOUT, 30);
	curl_setopt($re, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($re, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? 'tools');
	// 只允许 http/https，挡掉 file:// gopher:// 之类
	curl_setopt($re, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
	// 不跟随跳转，否则 302 到内网地址就绕过了上面的检查
	curl_setopt($re, CURLOPT_FOLLOWLOCATION, false);
	// 锁定已校验过的 IP，避免校验后 DNS 再次解析到内网（DNS rebinding）
	curl_setopt($re, CURLOPT_RESOLVE, array("$host:$port:$ip"));

	$insecure = getenv('TOOLS_CURL_INSECURE') === '1';
	curl_setopt($re, CURLOPT_SSL_VERIFYPEER, !$insecure);
	curl_setopt($re, CURLOPT_SSL_VERIFYHOST, $insecure ? 0 : 2);

	if ($is_post) {
		curl_setopt($re, CURLOPT_URL, $url);
		curl_setopt($re, CURLOPT_POST, 1);
		curl_setopt($re, CURLOPT_POSTFIELDS, $post_data);
	}
	else {
		if ($post_data !== '') {
			$url .= (strpos($url, '?') === false ? '?' : '&') . $post_data;
		}
		curl_setopt($re, CURLOPT_URL, $url);
	}

	$res = curl_exec($re);
	if ($res === false) {
		$res = 'curl error: ' . curl_error($re);
	}
	curl_close($re);

	return $res;
}

/**
 * 校验目标地址，返回 [host, port, ip]
 */
function check_url($url) {
	$parts = parse_url($url);
	if (!$parts || empty($parts['host'])) {
		throw new Exception('无效的 URL');
	}

	$scheme = strtolower($parts['scheme'] ?? '');
	if ($scheme !== 'http' && $scheme !== 'https') {
		throw new Exception('只允许 http/https');
	}

	$host = $parts['host'];
	$port = (int)($parts['port'] ?? ($scheme === 'https' ? 443 : 80));

	check_allowlist($host);

	$ips = resolve_host($host);
	if (!$ips) {
		throw new Exception("域名无法解析：$host");
	}
	// 所有解析结果都必须是公网地址，任何一条命中内网就拒绝
	foreach ($ips as $ip) {
		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			throw new Exception("目标指向内网地址：$ip");
		}
	}

	return array($host, $port, $ips[0]);
}

/**
 * 白名单未配置时不限制域名，仅靠内网地址检查兜底
 */
function check_allowlist($host) {
	$raw = (string)getenv('TOOLS_URL_ALLOWLIST');
	$allow = array_filter(array_map('trim', explode(',', $raw)));
	if (!$allow) {
		return;
	}

	$host = strtolower($host);
	foreach ($allow as $item) {
		$item = strtolower(ltrim($item, '*'));
		if ($host === ltrim($item, '.')) {
			return;
		}
		// .example.com 形式匹配子域
		if ($item[0] === '.' && substr($host, -strlen($item)) === $item) {
			return;
		}
	}

	throw new Exception("域名不在白名单内：$host");
}

function resolve_host($host) {
	if (filter_var($host, FILTER_VALIDATE_IP)) {
		return array($host);
	}

	$ips = gethostbynamel($host);
	$ips = $ips ? $ips : array();

	$aaaa = @dns_get_record($host, DNS_AAAA);
	if ($aaaa) {
		foreach ($aaaa as $record) {
			if (!empty($record['ipv6'])) {
				$ips[] = $record['ipv6'];
			}
		}
	}

	return $ips;
}
