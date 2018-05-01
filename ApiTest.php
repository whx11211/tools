<?php
define('DEGUG', 1);
DEGUG ? error_reporting(7) : error_reporting(0);

define('PARAM_CNT', 8);
ini_set('date.timezone', 'Asia/Shanghai');

function post_par($key) {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    return '';
}

function curl($url, $post_data, $is_post) {
    $post_data = http_build_query($post_data);
    $re=curl_init();//实例化cURL   http://161.202.43.125/
    curl_setopt($re, CURLOPT_HEADER, 0);//0关闭打印相应头,直接打印需为1,
    curl_setopt($re, CURLOPT_RETURNTRANSFER, 1);//0获取后直接打印出来 
    curl_setopt($re, CURLOPT_TIMEOUT,100);   //只需要设置一个秒的数量就可以  
    if ($is_post) {
        curl_setopt($re, CURLOPT_URL, $url);//初始化路径
        curl_setopt($re, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($re, CURLOPT_POST, 1);//启用时会发送一个常规的POST请求，
        curl_setopt($re, CURLOPT_POSTFIELDS, $post_data);//使用HTTP协议中的"POST"操作来发送的数据
    }
    else {
        curl_setopt($re, CURLOPT_URL, $url.$post_data);//初始化路径
        curl_setopt($re, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    }
    return curl_exec($re);//执行一个cURL会话,返回响应结果
}

$tps = array(
    'paycard'   =>  '礼包卡接口',
    'data'      =>  '数据中心接口',
    'stastic'   =>  '新手统计',
    'tfcard'    =>  '通服礼包卡',
);

if (isset($_GET['tp'])) {
    switch ($_GET['tp']) {
        case 'paycard':
            $form = array(
                'api_url'       =>  'http://10.1.8.59/do.php?paycard&',//接口地址
                'is_res'        =>  1,//是否直接返回结果
                'is_post'       =>  1,//是否post提交
                'key'           =>  array('sid', 'uid', 'tcyb', 'cust'),
                'val'           =>  array(2, 300072421, 0, 10),
            );
            break;
        case 'data':
            $form = array(
                'api_url'       =>  'http://10.1.8.132/data/datalog/Online.php?',//接口地址
                'is_res'        =>  1,//是否直接返回结果
                'is_post'       =>  1,//是否post提交
                'key'           =>  array('version', 'appid', 'svrId', 'eventTime', 'onlineNum'),
                'val'           =>  array('0.4.26', 110232, 1, time(), 20),
            );
            break;
        case 'stastic':
            $form = array(
                'api_url'       =>  'http://10.1.8.145/do.php?stastic',//接口地址
                'is_res'        =>  1,//是否直接返回结果
                'is_post'       =>  1,//是否post提交
                'key'           =>  array('uid', 'act', 'pre_idx', 'sid'),
                'val'           =>  array('6', 50, 3, 1),
            );
            break;
        case 'tfcard':
            $form = array(
                'api_url'       =>  'http://10.1.8.121/do.php?receive_card',//接口地址
                'is_res'        =>  1,//是否直接返回结果
                'is_post'       =>  1,//是否post提交
                'key'           =>  array('sid', 'cid', 'cardid'),
                'val'           =>  array(1, 8555, 'ES23Q2'),
            );
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $form = array(
        'api_url'       =>  post_par('api_url'),//接口地址
        'is_res'        =>  post_par('is_res'),//是否直接返回结果
        'is_post'       =>  post_par('is_post'),//是否post提交
        'key'           =>  post_par('key'),
        'val'           =>  post_par('val'),
    );
    $data = array();
    foreach ($form['key'] as $k => $v) {
        if ($v) {
            $data[$v] = $form['val'][$k];
        }
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>接口测试工具</title>
</head>
<style>
#main {
    width:80%;
    margin: auto;
    text-align: center;
    font: 15px "sans-serif", "Arial", "Microsoft YaHei";
	line-height: 21px;
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
#config .td3 {
    width: 210px;
    text-align: left;
}
#config input {
    width: 100%;
    padding: 0 3px;
}
#config #is_res,#config #is_post {
	padding-top: 2px;
    width: 14px;
	height: 14px;
}
#sub {
    margin: 12px auto 10px;
    height: 40px;
}
#info {
    width: 350px;
    margin: 12px auto 10px;
    text-align: center;
	line-height: 20px;	
}
#info #info_title{
	width:100%;
	font-size: 20px;
	margin-bottom: 8px;
}
#info .common {
	width: 100px;
	float: left;
	margin: 5px;
}
#info .common a{
	text-decoration: none;
	color: blue;
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
.space {
	height: 10px;
}
</style>
<body>
    <div id="main">
        <form method="post">
            <h2>输入接口信息</h2>
            <table id="config">
                <tr>
                    <td class="td1">接口地址：</td>
                    <td class="td3" colspan="3"><input name="api_url" value="<?php echo $form['api_url'];?>"></td>
                </tr>
                <tr class="space"></tr>
                <tr>
                    <td class="td1">输出结果：</td>
                    <td class="td3" colspan="3"><input type="checkbox" id="is_res" name="is_res" <?php echo $form['is_res'] ? 'checked="checkde"' : '';?>></td>
                </tr>
                <tr>
                    <td class="td1">POST提交：</td>
                    <td class="td3" colspan="3"><input type="checkbox" id="is_post" name="is_post" <?php echo $form['is_post'] ? 'checked="checkde"' : '';?>></td>
                </tr>
                <tr class="space"></tr>
                <?php if(isset($form['key'])) {foreach($form['key'] as $k => $v){?>
                <tr>
                    <td class="td1">参数名：</td>
                    <td class="td2"><input name="key[]" value="<?php echo $form['key'][$k];?>"></td>
                    <td class="td1">参数值：</td>
                    <td class="td2"><input name="val[]" value="<?php echo $form['val'][$k];?>"></td>
                </tr>
                <?php }}?>
                
                <?php for($i=count($form['key']); $i<PARAM_CNT; ++$i){?>
                <tr>
                    <td class="td1">参数名：</td>
                    <td class="td2"><input name="key[]" value=""></td>
                    <td class="td1">参数值：</td>
                    <td class="td2"><input name="val[]" value=""></td>
                </tr>
                <?php }?>
                <tr class="space"></tr>
            </table>
            <div id="info">
                <div id="info_title">常用接口模版</div>
                <?php foreach($tps as $k=>$v) {?>
                <div class="common">
                    <a href="?tp=<?php echo $k?>"><?php echo $v?></a>
                </div>
                <?php }?>
            </div>
            <div style="clear:both"></div>
            <div id="sub">
                <input type="submit" value="测试" />
                <input type="button" onclick="window.location='./'" value="返回首页" />
            </div>
        </form>
        <hr/>
        <div id="res">
            <pre><?php 
            if (isset($form) && $_SERVER['REQUEST_METHOD'] == "POST") {
                echo '请求地址：',$form['api_url'],(!$form['is_post'] ? http_build_query($data) : ''),'<br/>';
                echo '请求参数：',http_build_query($data),'<br/>';
                if ($form['is_res']) {
                    echo '返回结果：','<br/>';
                    echo curl($form['api_url'], $data, $form['is_post']);
                }
            }
            ?></pre>
        </div>
    </div>
</body>
</html>