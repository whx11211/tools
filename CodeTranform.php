<?php 
$types = array(
    1   =>  'URL编码',
    2   =>  'URL解码',
    //3   =>  'JSON编码',
    4   =>  'JSON解码',
    5   =>  'BASE64编码',
    6   =>  'BASE64解码',
    7   =>  'URL转JSON',
    8   =>  'JSON转URL',
    9   =>  'URL解析PHP数组',
    10   =>  'JSON解析PHP数组',
);

$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : 1;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>编码转换</title>
</head>
<style>
#main {
    width:700px;
    margin: auto;
    text-align: center;
    font: 13px "sans-serif", "Arial", "Microsoft YaHei";
}
#msg{
    margin: auto;
    padding:5px;
    width:100%;
    min-height:150px;
    font-size:15px;
}
#config {
    margin: 15px auto;
    width: 400px;    
}
#config .td1 {
    width: 100px;
    text-align: right;
}

#config .td2 {
    width: 70px;
    text-align: left;
}
#config input {
    width: 50px;
    padding: 0 3px;
}
#sub {
    width: 700px;
    margin: 12px auto 10px;
    text-align: center;
	line-height: 20px;	
}
#info {
    color: red;
    width: 350px;
    margin: 12px auto 10px;
    text-align: center;
	line-height: 20px;
}
#info input {
    font-size: 16px;
    line-height: 40px;
}
textarea {
    font: 13px "sans-serif", "Arial", "Microsoft YaHei";
}
#res{
    margin: 5px auto;
    padding:5px;
    width:100%;
    min-height:600px;
    font-size:15px;
    text-align: left;
}
#sub .common {
	width: 150px;
	float: left;
	margin: 5px;
	text-align: left;
}
</style>
<body>
    <div id="main">
        <form method="post">
            <h2>需要解析的内容</h2>
            <textarea id="msg" name="msg" ><?php echo $msg?></textarea>
            <br/>
            <div id="sub">
                <?php 
                foreach ($types as $k => $v) {
                    echo '<div class="common"><input type=radio name="type" '.($type==$k ? 'checked="checked"' : '').' value="'.$k.'"/>'.$v.'</div>';
                }
                ?>
            </div>
            <div style="clear: both"></div>
            <div id="info">
                <input type="submit" value="转化" />
                <input type="button" onclick="window.location='./'" value="返回" />
            </div>
        </form>
        <br/>
        <hr/>
        <div id="res">
            <pre><?php 
                if ($msg) {
                    if (!in_array($type, array_keys($types))) {
                        echo '转化类型不存在';
                    }
                    else {
                        switch ($type) {
                            case 1:
                                echo urlencode($msg);
                                break;
                            case 2:
                                echo urldecode($msg);
                                break;
                            case 3:
                                echo json_encode($msg);
                                break;
                            case 4:
                                print_r(json_decode($msg, true));
                                break;
                            case 5:
                                echo base64_encode($msg);
                                break;
                            case 6:
                                echo base64_decode($msg);
                                break;
                            case 7:
                                if (strpos('?', $msg) > 0) {
                                    $tmp = parse_url($msg);
                                    $msg = isset($tmp['query']) ? $tmp['query'] : $msg;
                                }
                                
                                if ($msg) {
                                    parse_str($msg, $arr);
                                    if ($arr) {
                                        echo json_encode($arr);
                                    }
                                    else {
                                        echo 'url解析失败';
                                    }
                                }
                                
                                break;
                            case 8:
                                echo htmlspecialchars(http_build_query(json_decode($msg)));
                                break;
                            case 9:
                                if (strpos('?', $msg) > 0) {
                                    $tmp = parse_url($msg);
                                    $msg = isset($tmp['query']) ? $tmp['query'] : $msg;
                                }
                                
                                if ($msg) {
                                    parse_str($msg, $arr);
                                    if (is_array($arr)) {
                                        echo 'array(<br/>';
                                        $len = 0;
                                        foreach ($arr as $k => $v) {
                                            $len = max($len, strlen($k));
                                        }
                                        $len +=2;
                                        foreach ($arr as $k => $v) {
                                            $count = $len - strlen($k) >= 0 ? $len - strlen($k) : 0;
                                            echo '&nbsp&nbsp&nbsp&nbsp'."'$k'".str_repeat('&nbsp', $count).'=>&nbsp&nbsp\''.$v.'\','.'<br/>';
                                        }
                                        echo ');';
                                    }
                                    else {
                                        echo 'url解析失败';
                                    }
                                }
                                break;
                            case 10:
                                $arr = json_decode($msg, true);
                                if (is_array($arr)) {
                                    echo 'array(<br/>';
                                    $len = 0;
                                    foreach ($arr as $k => $v) {
                                        $len = max($len, strlen($k));
                                    }
                                    $len +=2;
                                    foreach ($arr as $k => $v) {
                                        $count = $len - strlen($k) >= 0 ? $len - strlen($k) : 0;
                                        echo '&nbsp&nbsp&nbsp&nbsp'."'$k'".str_repeat('&nbsp', $count).'=>&nbsp&nbsp\''.$v.'\','.'<br/>';
                                    }
                                    echo ');';
                                }
                                else {
                                    echo 'json解析失败';
                                }
                                break;
                        }
                    }
                }
            ?></pre>
        </div>
    </div>
</body>
</html>