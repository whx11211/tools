<?php 
define('B_KEY', '=');       //键值前分割符
define('A_KEY', ',');       //键值后分割符
define('KEY_LEN', ',');     //拼接符
define('B_VALUE','//消耗');     //匹配关键词1
define('A_VALUE', '//产出');      //匹配关键词2


$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
//echo $file;
$msg_arr = explode("\r\n", $msg);
$b_key = isset($_POST['b_key']) ? $_POST['b_key'] : B_KEY;
$a_key = isset($_POST['a_key']) ? $_POST['a_key'] : A_KEY;

$key_len = isset($_POST['key_len']) ? $_POST['key_len'] : KEY_LEN;

$b_value = isset($_POST['b_value']) ? $_POST['b_value'] : B_VALUE;
$a_value = isset($_POST['a_value']) ? $_POST['a_value'] : A_VALUE;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>配置解析</title>
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
            <h2>需要解析的内容</h2>
            <textarea id="msg" name="msg" ><?php echo $msg?></textarea>
            <table id="config">
                <tr>
                    <td class="td1">键前分割符：</td>
                    <td class="td2"><input name="b_key" value="<?php echo $b_key;?>"></td>
                    <td class="td1">键后分割符：</td>
                    <td class="td2"><input name="a_key" value="<?php echo $a_key;?>"></td>
                </tr>
                <tr>
                    <td class="td1">匹配关键词1：</td>
                    <td class="td2"><input name="b_value" value="<?php echo $b_value;?>"></td>
                    <td class="td1">匹配关键词2：</td>
                    <td class="td2"><input name="a_value" value="<?php echo $a_value;?>"></td>
                </tr>
                <tr>
                    <td class="td1">连接符：</td>
                    <td class="td2"><input name="key_len" value="<?php echo $key_len;?>"></td>
                </tr>
            </table>
            <div id="info">
                键（值）以行为单位进行拆分进行处理<br/>
                键（值）前分隔符为空时从第一个字符开始截取<br/>
                键（值）后分隔符为空时截取到最后一个字符<br/>
                键（值）两侧的空白字符或其他预定义字符会自动去除<br/>
            </div>
            <div id="sub">
                <input type="submit" value="解析" />
                <input type="button" onclick="window.location='./'" value="返回" />
            </div>
        </form>
        <hr/>
        <div id="res">
            <?php 
                $a = $b = array();
                foreach ($msg_arr as $v) {
                    if (($b_key && strpos($v, $b_key) !== false) || ($a_key && strpos($v, $a_key) !== false)) {//可以匹配到键值，开始处理
                        $id = $value = '';
                        //键处理
                        $s_sub = ($b_key && strpos($v, $b_key) !== false) ?  (strpos($v, $b_key) + strlen($b_key)) : 0;
                        $e_sub = ($a_key && strpos($v, $a_key) !== false) ? strpos($v, $a_key) : 0;
                        if ($e_sub && ($e_sub - $s_sub) > 0) {
                            $id = trim(substr($v, $s_sub, $e_sub - $s_sub));
                        }
                        else {
                            $id = trim(substr($v, $s_sub));
                        }
                
                        //值处理
                        if ($b_value && strpos($v, $b_value) !== false) {
                            $b[] = $id;
                        }
                        if ($a_value && strpos($v, $a_value) !== false) {
                            $a[] = $id;
                        }
                        //键留空计算
                
                    }
                }
                echo $b_value,'：<br/>',implode($b, $key_len),'<br/><br/>',$a_value,'：<br/>',implode($a, $key_len);
            ?>
        </div>
    </div>
</body>
</html>