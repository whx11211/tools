<?php 
$types = array(
    1   =>  '压缩',
    2   =>  '格式化',
);

$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : 1;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>HTML/JS代码处理</title>
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
    min-height:550px;
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
            <h2>需要转化的HTML/JS代码</h2>
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
            <?php 
                if ($msg) {
                    if (!in_array($type, array_keys($types))) {
                        echo '转化类型不存在';
                    }
                    else {
                        switch ($type) {
                            case 1:
                                //后面跟一个空格的关键词
                                $space_keys = array(
                                    'return ',
                                    'var ',
                                    'function '
                                );
                                $tmp = '';
                                $intag = 0;
                                $incss = 0;
                                $skip = 0;
                                for($i=0; $i<strlen($msg); $i++) {
                                    switch ($msg[$i]) {
                                        case '<':
                                            $intag = 1;
                                            break;
                                        case '>':
                                            $intag = 0;
                                            break;
                                        case '{':
                                            $incss += 1;
                                            break;
                                        case '}':
                                            $incss -= 1;
                                            break;
                                        case ':':
                                            if ($incss) {
                                                $incss += 1;
                                            }
                                            break;
                                        case ';':
                                            if ($incss) {
                                                $incss -= 1;
                                            }
                                            break;
                                        case '/':
                                            if ($incss && $msg[$i+1] == '/') {
                                                $skip = 1;
                                            }
                                            break;
                                        case "\r":
                                            if ($msg[$i+1] == "\n") {
                                                $skip = 0;
                                            }
                                            break;
                                    }
                                    foreach ($space_keys as $key) {
                                        if ($msg[$i] == $key[0] && substr($msg, $i, strlen($key)) == $key) {
                                            //匹配到关键词
                                            $i += strlen($key);
                                            $tmp .= $key;
                                            continue;
                                        }
                                    }
                                    if ($skip) {
                                        continue;
                                    }
                                    if ($incss == 2 || $intag || !in_array($msg[$i], array(" ", "\t", "\r", "\n"))) {
                                        $tmp .= $msg[$i];
                                    }
                                }
                                echo htmlentities($tmp);
                                break;
                            case 2:
                                $deep = 0;
                                $tmp = '';
                                $intag = 0;
                                $injs = 0;
                                $space = "  ";
                                for($i=0; $i<strlen($msg); $i++) {
                                    $nextstr = isset($msg[$i+1]) ? $msg[$i+1] : '';
                                    $before = $after = '';
                                    switch ($msg[$i]) {
                                        case '<':
                                            if ($nextstr == "/") {
                                                $intag -= 1;
                                                $before = "\r\n" . str_repeat($space, $intag);
                                            }
                                            else if ($nextstr != "!") {
                                                $before = "\r\n" . str_repeat($space, $intag);
                                                $intag += 1;
                                            }
                                            if (substr($msg, $i+1, 6) == 'script') {
                                                $injs = 1;
                                            }
                                            else if (substr($msg, $i+1, 7) == '/script') {
                                                $injs = 0;
                                            }
                                            break;
                                        case '>':
                                            if ($nextstr != "<") {
                                                $after = "\r\n" . str_repeat($space, $intag);
                                            }
                                            break;
                                        case '{':
                                            $before = ' ';
                                            $intag += 1;
                                            $after = "\r\n" . str_repeat($space, $intag);
                                            break;
                                        case '}':
                                            $intag -= 1;
                                            $before = "\r\n" . str_repeat($space, $intag);
                                            if ($nextstr != "<") {
                                                $after = "\r\n" . str_repeat($space, $intag);
                                            }
                                            break;
                                        case ';':
                                            if ($injs && $nextstr != "}") {
                                                $after = "\r\n" . str_repeat($space, $intag);
                                            }
                                            break;
                                        case '/':
                                            if ($nextstr == ">") {
                                                $intag -= 1;
                                            }
                                            break;
                                        default:
                                            
                                    }

                                    //echo $i,':',$msg[$i],':',$intag,"<br/>";
                                    $tmp .= $before . $msg[$i] . $after;

                                }
                                echo "<pre>",htmlentities($tmp),"</pre>";
                                break;
                        }
                    }
                }
            ?>
        </div>
    </div>
</body>
</html>