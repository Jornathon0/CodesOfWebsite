<?php
/**
 * Author: king <linzujin@juwang.com>
 * Desc:上传图片
 * $file是$_FILES  *必填字段
 * $inputName 是文件字段域  *必填字段
 * $array是数组类型  array('dir'=>'保存的目录'，'filename'=>'文件名称','thumbList'=>array(要生成图片的大小数组))
 */
function uploadFiles($_file,$inputName,$array = array()) {
	extract($array);
	//设置文件保存目录
	$baseDir = './Uploads/';
	$attachDir =isset($dir)? $dir : './'.date('Ym').'/';
	$saveName = isset($filename)?$filename : (date('His').strtolower(mt_rand(16)));
	//上传类配置信息
	$config = array(
			'maxSize' => 2097152,
			'exts' => array('jpg', 'jpeg', 'png', 'gif','JPG', 'JPEG', 'PNG', 'GIF'),
			'rootPath' => $baseDir,
			'savePath' => $attachDir,
			'subName' =>  isset($subname)?$subname:'/',
			'saveName' => $saveName,
			'replace'	=> isset($replace) && $replace == true? true : false ,
			'hash' => false
	);
	//初始化上传类
	$upload = new \Think\Upload();

	$upload->maxSize   =     3145728 ;// 设置附件上传大小
	$upload->exts      =      array('jpg', 'jpeg', 'png', 'gif','JPG', 'JPEG', 'PNG', 'GIF');// 设置附件上传类型
	$upload->rootPath  =     $baseDir; // 设置附件上传根目录
	$upload->savePath  =     $attachDir; // 设置附件上传根目录
	$upload->subName =  '/'.(isset($subname)?$subname:'');
	$upload->saveName =  $saveName;
	$upload->replace =  isset($replace) && $replace == true? true : false;

	//检查是否选择图片
	$inputName = empty($inputName)?'photo':$inputName;
	//缩略图列表，数组为空则不生成缩略图
	//键为缩略图文件名后缀，例如：20140221abc_a.jpg
	//值为缩略图宽/高
	$thumbList = isset($thumbList)?$thumbList:array(
			array(160, 160),
			array(80, 80)
	);
	//初始化缩略图类
	if(!empty($thumbList)) {
		$image = new \Think\Image();
	}
	$_file[$inputName]['name'] = strtolower($_file[$inputName]['name']);
	//开始上传
	$file = $upload->upload($_file);
	//上传成功
	if(!empty($file)) {
		//缩略图
		if(!empty($thumbList)) {
			$path = $baseDir.$file[$inputName]['savepath'].$upload->saveName;
			$fileExt = $file[$inputName]['ext'];
			$filePath = $path.'.'.$fileExt;
			$file[$inputName]['uploadpath'] = str_replace('//','/',$path);
			$file[$inputName]['uploadfile'][] = str_replace('//','/',$path.'.'.$fileExt);
			//生成缩略图，按照原图的比例
			foreach($thumbList as $thumbName => $thumbSize) {
				if(empty($thumbSize)) continue;
				$image->open($filePath);
				$image->thumb($thumbSize[0], $thumbSize[1])->save($path.'_'.($thumbSize[0].'_'.$thumbSize[1]).'.'.$fileExt);
				$file[$inputName]['uploadfile'][] = str_replace('//','/',$path.'_'.($thumbSize[0].'_'.$thumbSize[1]).'.'.$fileExt);
			}
		}
	}
	//成功提示
	$res = $upload->getError();
	if(empty($res)) {
		return $file;
	} else {
		return array();
	}
}
/**
 * Author: king <linzujin@juwang.com>
 * Desc:上传图片
 * $file是$_FILES
 * $dir是上传的目录,前不带/，后带/
 */
function uploads($file,$dir='',$subdir=''){
	$upload = new \Think\Upload();// 实例化上传类
	$upload->maxSize   =     3145728 ;// 设置附件上传大小
	$upload->exts      =     array('jpg', 'gif" />', 'png', 'jpeg');// 设置附件上传类型
	$upload->rootPath  =      'Uploads/'.$dir; // 设置附件上传根目录
	$upload->subName = empty($subdir)?array('date','Ymd'):$subdir;
	// 上传单个文件
	$info   =   $upload->uploadOne($file);
	$result = array();
	if(!$info) {
		// 上传错误提示错误信息
		$result['info'] = $upload->getError();
		$result['error'] = false;
	}else{
		// 上传成功 获取上传文件信息
		$result['info'] = $upload->rootPath.$info['savepath'].$info['savename'];
		$result['error'] = true;
	}
	return $result;
}

 /**
  * Author:   yutanghua
  * 循环创建目录
  * @param [$dir]  判断的目录   
  * time：2015.10.25
 */
function mk_dir($dir, $mode = 0755) 
{ 
    if (is_dir($dir) || @mkdir($dir,$mode)) return true; 
    if (!mk_dir(dirname($dir),$mode)) return false; 
    return @mkdir($dir,$mode); 
} 

//随机产生6位数验证码
function authnum() {
	$numarr = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
	for ($i = 1; $i <= 6; $i++) {
		if($i==1)
		{
			$code .= rand(1, 9);
		}else
		{
			$code .= $numarr[rand(0, 9)];
		}

	}
	return $code;
}

/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $m 模型，引用传递
 * @param $where 查询条件
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage(&$m,$where,$pagesize=20){
    $m1=clone $m;//浅复制一个模型
    $count = $m->where($where)->count();//连惯操作后会对join等操作进行重置
    $m=$m1;//为保持在为定的连惯操作，浅复制一个模型
    $p=new Think\Page($count,$pagesize);
    $p->lastSuffix=false;
    $pagecount = (int)($count/$pagesize) + 1;
    $p->setConfig('header',"<span class='pagecount'>每页显示<b>$pagesize</b>条记录 (共<b>%TOTAL_ROW%</b>条/<b>$pagecount</b>页记录)</span>");
    $p->setConfig('prev','上一页');
    $p->setConfig('next','下一页');
    $p->setConfig('last','末页');
    $p->setConfig('first','首页');
    $p->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');

    $p->parameter=I('get.');

    $m->limit($p->firstRow,$p->listRows);

    return $p;
}

function get_real_ip() {
    if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) {
        $ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
    } elseif ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) {
        $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
    } elseif ($HTTP_SERVER_VARS["REMOTE_ADDR"]) {
        $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
    } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } elseif (getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    } else {
        $ip = "127.0.0.1";
    }
    return $ip;
}

function getIpLocal()
{
	$ip = get_real_ip();
	$data = curl_get('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='.$ip);
	return json_decode($data,true);
}
function redirect301($url) {
    //发出301头部 
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
    exit;
}

/*
 * 功能：循环检测并创建文件夹
 * 参数：$path 文件夹路径
 * 返回：
 */

function createDir($path) {
    if (!file_exists($path)) {
        createDir(dirname($path));
        mkdir($path, 0777);
    }
}

function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
    if (function_exists("mb_substr")) {
        if ($suffix)
            return mb_substr($str, $start, $length, $charset) . "...";
        else
            return mb_substr($str, $start, $length, $charset);
    }
    elseif (function_exists('iconv_substr')) {
        if ($suffix)
            return iconv_substr($str, $start, $length, $charset) . "...";
        else
            return iconv_substr($str, $start, $length, $charset);
    }
    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef]
                  [x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix)
        return $slice . "…";
    return $slice;
}
/**
 * @author king <linzujin@juwang.com>
 * 发送邮件信息
 */
function sendEmail($email,$title = '',$html= '')
{
	if(empty($email)) return false;
	$content = '
            <style type="text/css">
            *{margin:0;padding:0;}
            html{text-align:center;background:#fff;}
            body{position:relative;text-align:left;font:12px/1.6em Verdana, Lucida, Arial, Helvetica, 宋体,sans-serif;color:#000;}
            h1, h2, h3, h4, h5, h6{font-size:100%;}
            address, caption, cite, code, dfn, em, strong, th, var, b, i{font-style:normal;font-weight:normal;}
            table{border-collapse:collapse;border-spacing:0;empty-cells:show;}
            ol, ul{list-style:none;}
            :focus{outline :0;}
            fieldset, img{border:0;}
            caption, th{text-align :left;}
            a{color:#005F9A;text-decoration:none;}
            a:hover{text-decoration:underline;}
            input, select, textarea{font-size:12px;padding:0 2px;}
            textarea{padding:2px;}
            * html .en_ie6_fixed{zoom:1;vertical-align:baseline;position:relative;top:-2px;}
            .clearfix{overflow:hidden;zoom:1;}
            .pop{background:#d1d1d1;margin-top:3px;margin-left:3px;margin:20px;}
            .pop_inner{border:1px #cecece solid;border-top:0;position:relative;zoom:1;left:-3px;top:-3px;padding:0 20px 20px;background:#fff;overflow:hidden;}
            .pop_inner i{height:4px;position:absolute;overflow:hidden;left:0;top:0;width:200%;background:#307DAB  0 0;z-index:2;}
            .pop_inner h1{font-size:14px;line-height:14px;margin:4px 0 0;height:14px;color:#367108;padding:20px 0;border-bottom:1px #cecece dashed;}
            .lh20{line-height:2em;}
            .lh15{line-height:1.5em;}
            .mt20{margin-top:20px;}
            .normal{font-style:normal;font-weight:400;}
            .circle ul{margin-left:18px;list-style:disc;}
            strong{font-weight:bold;font-size:14px;}
            b{font-weight:bold;}
            </style>
            <div class="pop">
                <div class="pop_inner">
                    <i></i>
                    <h1>这是来自新米网的邮件</h1>
                    <div class="mt20 lh20">
                        <p>'.$html.'</p>
                    </div>
                    <p class="mt20"><strong>这是一封系统邮件，请勿回复 </strong></p>
                </div>
            </div>';
	SendMail($email, $title, $content);
}
/* * ********
 * 发送邮件 *
 * ******** */

function SendMail($address, $title, $message) {

    vendor('PHPMailer.class#phpmailer');
    $mail = new phpmailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();

    //设置html支持
    $mail->ishtml(true);
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet = 'UTF-8';

    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);

    // 设置邮件正文
    $mail->Body = $message;

    // 设置邮件头的From字段。
    $mail->From = 'qiuyifeng@juwang.com.cn';//notice@9944.com

    // 设置发件人名字
    $mail->FromName = '新米网';

    // 设置邮件标题
    $mail->Subject = $title;

    // 设置SMTP服务器。
    $mail->Host = 'smtp.exmail.qq.com';

    // 设置为"需要验证"
    $mail->SMTPAuth = true;

    // 设置用户名和密码。
    $mail->Username = 'qiuyifeng@juwang.com.cn';//notice@9944.com
    $mail->Password = 'Srjw123456';//94.com

    // 发送邮件。
    return($mail->Send());
}

function sendMsg($mobile, $code) {
	$uid = '57882'; //用户账号 老55763
	$pwd = '5c6c6i'; //密码 老密码：665k5p
	$content = '亲，你获得的验证码是：' . $code . '，感谢使用新米，祝你愉快。【新米】';
	$content = mb_convert_encoding($content, "gbk", "utf-8");
	return sendSMS($uid, $pwd, $mobile, $content);
}
/* --------------------------------
 功能:HTTP接口 发送短信
 修改日期:	2009-04-08
 说明:		http://localhost/tx/?uid=用户账号&pwd=MD5位32密码&mobile=号码&content=内容
 状态:
 100 发送成功
 101 验证失败
 102 短信不足
 103 操作失败
 104 非法字符
 105 内容过多
 106 号码过多
 107 频率过快
 108 号码内容空
 109 账号冻结
 110 禁止频繁单条发送
 111 系统暂定发送
 112	有错误号码
 113	定时时间不对
 114	账号被锁，10分钟后登录
 115	连接失败
 116 禁止接口发送
 117	绑定IP不正确
 120 系统升级
 -------------------------------- */

function sendSMS($uid, $pwd, $mobile, $content, $time = '', $mid = '') {
	$http = 'http://http.yunsms.cn/tx/';
	$data = array
	(
			'uid' => $uid, //用户账号
			'pwd' => strtolower(md5($pwd)), //MD5位32密码
			'mobile' => $mobile, //号码
			'content' => $content, //内容
			'time' => $time, //定时发送
			'mid' => $mid      //子扩展号
	);
	$re = postSMS($http, $data);   //POST方式提交
	//    if (trim($re) == '100') {
	//        return "发送成功!";
	//    } else {
	//        return "发送失败! 状态：" . $re;
	//    }
	return $re;
}

function postSMS($url, $data = '') {
	$row = parse_url($url);
	$host = $row['host'];
	$port = $row['port'] ? $row['port'] : 80;
	$file = $row['path'];
	while (list($k, $v) = each($data)) {
		$post .= rawurlencode($k) . "=" . rawurlencode($v) . "&"; //转URL标准码
	}
	$post = substr($post, 0, -1);
	$len = strlen($post);
	$fp = @fsockopen($host, $port, $errno, $errstr, 10);
	if (!$fp) {
		return "$errstr ($errno)\n";
	} else {
		$receive = '';
		$out = "POST $file HTTP/1.1\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Content-Length: $len\r\n\r\n";
		$out .= $post;
		fwrite($fp, $out);
		while (!feof($fp)) {
			$receive .= fgets($fp, 128);
		}
		fclose($fp);
		$receive = explode("\r\n\r\n", $receive);
		unset($receive[0]);
		return implode("", $receive);
	}
}

/**
 * isValidEmail函数:检测参数的值是否为正确的邮箱格式
 * 
 */
function isValidEmail($email, $test_mx = false)
{
	if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
	{
        if($test_mx)
        {
            list($username, $domain) = split("@", $email);
            return getmxrr($domain, $mxrecords);
        }else
        {
            return true;
        }
	}else{
        return false;
	}
}

/**
 * IsMobile函数:检测参数的值是否为正确的中国手机号码格式
 * 返回值:是正确的手机号码返回手机号码,不是返回false
 */
function isMobile($str){
	if(strlen($str)!=11) return false; 
    if(preg_match('/13[0-9]\d{8}|[145|176|178|177]\d{8}||15[0|1|2|3|5|6|7|8|9]\d{8}|18[1|2|0|5|6|7|8|9]\d{8}/',$str)){ 
        return true; 
    }else{ 
        return false; 
    } 
	
}

/**
 * 验证身份证号
 * @param $vStr
 * @return bool
 */
function isCreditNo($vStr)
{
	$vCity = array(
			'11','12','13','14','15','21','22',
			'23','31','32','33','34','35','36',
			'37','41','42','43','44','45','46',
			'50','51','52','53','54','61','62',
			'63','64','65','71','81','82','91'
	);
	if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
	if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
	$vStr = preg_replace('/[xX]$/i', 'a', $vStr);
	$vLength = strlen($vStr);
	if ($vLength == 18)
	{
		$vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
	} else {
		$vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
	}
	if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
	if ($vLength == 18)
	{
		$vSum = 0;
		for ($i = 17 ; $i >= 0 ; $i--)
		{
			$vSubStr = substr($vStr, 17 - $i, 1);
			$vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
		}
		if($vSum % 11 != 1) return false;
	}
	return true;
}

/**
 * Author: copy-king <linzujin@juwang.com>
 * Desc:生成验证码
 * $array是一个数组，默认为空
 */
function verify($array=array())
{
	//echo 222222;
	extract($array);
	$Verify =     new \Think\Verify();
	$Verify->fontSize = isset($fontSize)?$fontSize:30;
	$Verify->length   = isset($length)?$length:4;
	$Verify->useNoise = false;
	//$Verify->useImgBg =  isset($useImgBg)?$useImgBg:true;
	$Verify->entry();
}
// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = ''){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * GET 发送信息
 * @param string $url URL地址
 * @param mixed $params 参数
 * @return string
 */
function curl_get($url, $params = array()) {
	return curl_request($url, $params, 'GET');
}

/**
 * CURL发送Request请求,含POST和REQUEST
 * @param string $url 请求的链接
 * @param mixed $params 传递的参数
 * @param string $method 请求的方法
 * @param mixed $options CURL的参数
 * @return array
 */
function curl_request($url, $params = array(), $method = 'POST', $options = array()) {
	$method = strtoupper($method);
	$protocol = substr($url, 0, 5);
	$query_string = is_array($params) ? http_build_query($params) : $params;

	$ch = curl_init();
	$defaults = array();
	if ( 'GET' == $method) {
		$geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?" ) . $query_string : $url;
		$defaults[CURLOPT_URL] = $geturl;
	} else {
		$defaults[CURLOPT_URL] = $url;
		$defaults[CURLOPT_POST] = 1;
		$defaults[CURLOPT_POSTFIELDS] = $query_string;
	}

	$defaults[CURLOPT_HEADER] = false;
	$defaults[CURLOPT_RETURNTRANSFER] = true;
	$defaults[CURLOPT_CONNECTTIMEOUT] = 3;

	// disable 100-continue
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:'));

	if ( 'https' == $protocol) {
		$defaults[CURLOPT_SSL_VERIFYPEER] = false;
		$defaults[CURLOPT_SSL_VERIFYHOST] = false;
	}

	curl_setopt_array($ch, (array) $options + $defaults);

	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}

function jumpPost($url,$pram=''){
	if(empty($pram))  $pram =  '';
	if(is_array($pram))  $pram = json_encode($pram);
	$pram = addslashes($pram);
	$html =  '<form id="jumpPostAction" method="post" action="'.$url.'">
					<input type="hidden"  name="data" value='.$pram.'>
					<script src="/public/xmwcom/js/jquery-1.8.2.min.js"></script>
					<script>
					$(function (){
						$("#jumpPostAction").submit();
					})
					</script>
					</form>';
	echo $html;
	exit;
}

/*加密方法*/
function encrypt($data, $key)
{
	$char = '';
	$str = '';
	$key	=	md5($key);
    $x		=	0;
    $len	=	strlen($data);
    $l		=	strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= $key[$x];
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data[$i]) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}

/*解密方法*/
function decrypt($data, $key)
{
	$char = '';
	$str = '';
	$key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

/*错误调试*/
function p($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

/**
 * Author:   qiuyifeng
 * Desc:     安全过滤
 * $str：     要过滤的字符串
 * time：2016.05.05
 */
function safeFilter($str){

    $html_string = array("&amp;", "&nbsp;", "'", '"', "<", ">", "\t", "\r");
    $html_clear = array("&", " ", "&#39;", "&quot;", "&lt;", "&gt;", "&nbsp; &nbsp; ", "");

    $js_string = array("/<script(.*)<\/script>/isU");
    $js_clear = array("");

    $frame_string = array("/<frame(.*)>/isU", "/<\/fram(.*)>/isU", "/<iframe(.*)>/isU", "/<\/ifram(.*)>/isU",);
    $frame_clear = array("", "", "", "");

    $style_string = array("/<style(.*)<\/style>/isU", "/<link(.*)>/isU", "/<\/link>/isU");
    $style_clear = array("", "", "");
	
	$sql_string = array("select", 'insert', "update", "delete", "\'", "\/\*","\.\.\/", "\.\/", "union", "into", "load_file", "outfile");
 	$sql_clear = array("","","","","","","","","","","","");

    $str = trim($str);
	
    //过滤字符串
    $str = str_replace($html_string, $html_clear, $str);

    //过滤JS
    $str = preg_replace($js_string, $js_clear, $str);

    //过滤ifram
    $str = preg_replace($frame_string, $frame_clear, $str);

    //过滤style
    $str = preg_replace($style_string, $style_clear, $str);
	
	//过滤sql
    $str = str_replace($sql_string, $sql_clear, $str);

    return $str;

}

/**
 * Author:   qiuyifeng
 * Desc:     域名whois信息查询
 * $str：     要查询的域名
 * time：2016.07.16
 */
function GetWhoisInfo($domain){
	header('Content-Type: text/html; charset=UTF-8');
	
	if($domain == ''){
		return 'need domain';
		exit;
	}
	//$_GET['query']
	//$_GET['output']
	$getoutput = 'nice';//nice.则会带上html的结构
	
	//$out =  implode('', file('example.html'));
	
	//$out = str_replace('{self}', $_SERVER['PHP_SELF'], $out);
	
	//$resout = extract_block($out, 'results');
	
	if (isSet($domain))
		{
		$query = $domain;
	
		if (!empty($getoutput)){
			$output = $getoutput;
		}
		else{
			$output = '';
		}
		include_once('whois/whois.main.php');
		include_once('whois/whois.utils.php');
		$whois = new Whois();
	
		// Set to true if you want to allow proxy requests
		$allowproxy = false;
	
		// get faster but less acurate results
		$whois->deep_whois = empty($_GET['fast']);
		
		// To use special whois servers (see README)
		//$whois->UseServer('uk','whois.nic.uk:1043?{hname} {ip} {query}');
		//$whois->UseServer('au','whois-check.ausregistry.net.au');
	
		// Comment the following line to disable support for non ICANN tld's
		$whois->non_icann = true;
	
		$result = $whois->Lookup($query);
		//print_r($result);
		//echo $result;
		//$resout = str_replace('{query}', $query, $resout);
		//$resultout = $result;
		$winfo = '';
	
		switch ($output)
			{
			case 'object':
				if ($whois->Query['status'] < 0)
					{
					$winfo = implode($whois->Query['errstr'],"\n<br></br>");
					}
				else
					{
					$utils = new utils;
					$winfo = $utils->showObject($result);
					}
				break;
	
			case 'nice':
				if (!empty($result['rawdata']))
					{
					$utils = new utils;
					$winfo = $utils->showHTML($result);
					}
				else
					{
					if (isset($whois->Query['errstr']))
						$winfo = implode($whois->Query['errstr'],"\n<br></br>");
					else
						$winfo = 'Unexpected error';
					}
				break;
	
			case 'proxy':
				if ($allowproxy)
					exit(serialize($result));
	
			default:
				if(!empty($result['rawdata']))
					{
					$winfo .= '<pre>'.implode($result['rawdata'],"\n").'</pre>';
					}
				else
					{
					$winfo = implode($whois->Query['errstr'],"\n<br></br>");
					}
			}
		
		//$resout = str_replace('{result}', $winfo, $resout);
		$resout = $winfo;
		}
	else{
		$resout = '';
	}
	
	return $resout;
}
//whois 一些输出 所需的函数
function extract_block (&$plantilla,$mark,$retmark='')
{
$start = strpos($plantilla,'<!--'.$mark.'-->');
$final = strpos($plantilla,'<!--/'.$mark.'-->');

if ($start === false || $final === false) return;

$ini = $start+7+strlen($mark);

$ret=substr($plantilla,$ini,$final-$ini);

$final+=8+strlen($mark);

if ($retmark===false)
	$plantilla=substr($plantilla,0,$start).substr($plantilla,$final);
else	
	{
	if ($retmark=='') $retmark=$mark;
	$plantilla=substr($plantilla,0,$start).'{'.$retmark.'}'.substr($plantilla,$final);
	}
	
return $ret;
}