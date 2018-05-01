<?php 

$filename = isset($_POST['filename']) ? $_POST['filename'] : '';

$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
//echo $file;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>自动生成Dos输出语句</title>
</head>
<style>
#main {
    width:80%;
    margin: auto;
    text-align: center;
    font: 13px "sans-serif", "Arial", "Microsoft YaHei";
}
#msg{
    margin: auto;
    padding:5px;
    width:100%;
    min-height:500px;
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
    width: 100px;
    padding: 0 3px;
}
#sub {
    margin: 12px auto 10px;
    height: 40px;
}
#info {
    color: red;
    width: 350px;
    margin: 12px auto 10px;
    text-align: left;
	line-height: 20px;	
}
#sub input {
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
</style>
<body>
    <div id="main">
        <form method="post">
            <h2>需要转化的内容</h2>
            <textarea id="msg" name="msg" ><?php echo $msg?></textarea>
            <table id="config">
                <tr>
                    <td class="td1">输出文件名：</td>
                    <td class="td2"><input name="filename" value="<?php echo $filename;?>"></td>
                </tr>
            </table>
            <div id="sub">
                <input type="submit" value="生成" />
                <input type="button" onclick="window.location='./'" value="返回首页" />
            </div>
        </form>
        <hr/>
        <pre id="res"><?php 
            if ($msg) {
                $find_str    = array('^',  '&',  '<',  '>',  '|',  '`',  ',',  ';', ' =',  '!');
                $replace_str = array('^^', '^&', '^<', '^>', '^|', '^`', '^,', '^;', '^=', '^!');
                $msg = str_replace($find_str, $replace_str, $msg);
                $msg_arr = explode("\r\n", $msg);
                $first = 1;
                $all_content = '';
                foreach ($msg_arr as $v) {
                    $content = '';
                    if (preg_match('/\S+/', $v)) {
                        $content = 'echo ' . $v;
                    }
                    else {
                        //空白行
                        $content = 'echo.';
                    }
                    if ($filename) {
                        //输出到文件
                        if ($first) {
                            $content .= '>'.$filename;
                            $first = 0;
                        }
                        else {
                            $content .= '>>'.$filename;
                        }
                    }
                    $all_content .= $content."\r\n";
                }
                echo htmlspecialchars($all_content);
            }
            ?>
        </pre>
    </div>
</body>
</html>