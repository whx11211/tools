<?php 
define('B_VALUE', "'");     //值前添加符
define('A_VALUE', "'");      //值后添加符
define('KEY_LEN', '4');     //键长度

$b_value = isset($_POST['b_value']) ? $_POST['b_value'] : B_VALUE;
$a_value = isset($_POST['a_value']) ? $_POST['a_value'] : A_VALUE;
$key_len = isset($_POST['key_len']) ? $_POST['key_len'] : KEY_LEN;

$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
//echo $file;
$msg_arr = explode("\r\n", $msg);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>自动添加引号</title>
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
    width: 50px;
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
                    <td class="td1">值前添加符：</td>
                    <td class="td2"><input name="b_value" value="<?php echo $b_value;?>"></td>
                    <td class="td1">值后添加符：</td>
                    <td class="td2"><input name="a_value" value="<?php echo $a_value;?>"></td>
                </tr>
                <tr>
                    <td class="td1">补齐后的键长度：</td>
                    <td class="td2"><input name="key_len" value="<?php echo $key_len;?>"></td>
                </tr>
            </table>
            <div id="info">
                补齐后的键长度设置会自动补充“ =>  '' ”<br/><br/>
            </div>
            <div id="sub">
                <input type="submit" value="转化" />
                <input type="button" onclick="window.location='./'" value="返回首页" />
            </div>
        </form>
        <hr/>
        <div id="res">
            <?php 
            if ($msg) {
                foreach ($msg_arr as $v) {
                    //键留空计算
                    if ($key_len) {
                        $count = $key_len - strlen($v) - 2 >= 0 ? $key_len - strlen($v) - 2 : 0;
                        echo '&nbsp&nbsp&nbsp&nbsp'.$b_value.$v.$a_value.str_repeat('&nbsp', $count).'=>&nbsp&nbsp\'\','.'<br/>';
                    }
                    else {
                        echo '&nbsp&nbsp&nbsp&nbsp'.$b_value.$v.$a_value.',<br/>';
                    }
                }
                
                echo '<br/><br/>';
                foreach ($msg_arr as $v) {
                    echo "'$v', ";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>