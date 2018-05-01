<?php 
$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PHP在线执行</title>
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
            <h2>需要执行的PHP代码</h2>
            <textarea id="msg" name="msg" ><?php echo $msg?></textarea>
            </table>
            <div id="info"></div>
            <div id="sub">
                <input type="submit" value="执行" />
                <input type="button" onclick="window.location='./'" value="返回" />
            </div>
        </form>
        <hr/>
        <div id="res">
            <?php 
                eval($msg);
            ?>
        </div>
    </div>
</body>
</html>