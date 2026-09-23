<?php

/**
 * 配置（都可用环境变量覆盖，不要把敏感值写进仓库）
 *   TOOLS_PHP_BIN         执行用户代码的 php 可执行文件绝对路径
 *   TOOLS_RUN_TIMEOUT     用户代码超时秒数，默认 5
 *   TOOLS_OPEN_BASEDIR    用户代码可访问的目录，默认系统临时目录
 */

const RUN_MAX_OUTPUT = 1048576; // 输出上限 1M，防止刷屏撑爆内存

switch ($_REQUEST['type'] ?? '') {
	case 'run':
		echo run_code((string)($_POST['code'] ?? ''));
		break;
	case 'version':
		echo "document.write('" . PHP_VERSION . "');";
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

