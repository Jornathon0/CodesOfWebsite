<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:会员注册相关模块
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */

namespace Home\Controller;
use Think\Controller;
class RegisterController extends Controller {
	
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:显示注册页面
	 */
    public function index(){
		
		//给手机验证的设置一个验证key，避免被刷短信
		$codeTime = time();
		$yzTime = encrypt('xmw-'.$codeTime,C('JIAMI_KEY'));
		$array['codeTime'] = $codeTime;
		cookie('yzTime',$yzTime);
		
		//检测是否提交后返回错误
		$array['errortip'] = cookie('errortip');
		cookie('errortip',null);
		
		$this->assign($array);
    	$this->display();
    }
    public function regSuccess()
    {
    	$array = array();
    	//$array['data']= I('post.data');
		$array['email'] = cookie('regEmail');
		cookie('regEmail',null);
    	$aemail = explode("@",$array['email']);
		$array['aemail'] = $aemail[1];
		
    	$array['emailurl']= 'mail.'.$aemail[1];
    	$this->assign($array);
    	$this->display();
    }
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com.cn>
	 * @todo: 注册发送短信
	 */
	public function sendsms(){
		$mobile = safeFilter(I('post.mobile'));
		$yzcode = safeFilter(I('post.yzcode'));
		if(isMobile($mobile)){
			$code = authnum();
			$time = time();
			$sendtime =session('sendtime');
			
			//验证码的判断
			$ckTime = cookie('yzTime');
			if($ckTime != encrypt('xmw-'.$yzcode,C('JIAMI_KEY'))){
				return $this->ajaxReturn(array('status'=>0,'tip'=>'非法提交'));
			}
			
			if($sendtime < $time)
			{
				$res = sendMsg($mobile, $code);
				$rs = explode( '<<>>',str_replace(PHP_EOL, '<<>>', $res));
				//$rs[1] = 100;
				if($rs[1] != 100)
				{
					return $this->ajaxReturn(array('status'=>0,'tip'=>'手机发送失败！'));
				}
				session(md5($mobile),$code);
				session(md5($mobile.'time'),$time+100);
				session('sendtime',$time+100);
				return $this->ajaxReturn(array('status'=>1,'tip'=>$code.'发送成功！'.session(md5($mobile))));
			}else
			{
				return $this->ajaxReturn(array('status'=>0,'tip'=>'100秒内不要重复提交！'));
			}
			
		}else{
			
			return $this->ajaxReturn(array('status'=>0,'tip'=>'手机格式不正确'));
		}
	}
	
	//验证是否为手机验证码
	public function issjyzm(){
		$mobile = I('mobile');
		$yzm = I('yzm');
		$return['status'] = 1;
		$return['tip'] = '验证成功';
		if($yzm != session(md5($mobile)))
		{
			$return['status'] = 0;
			$return['tip'] = '验证码不对！';
		}
		if(session(md5($mobile.'time')) < time())
		{
			//session(md5($mobile),null);
			$return['status'] = 0;
			$return['tip'] = '验证码已失效！'; 
		}
		//echo md5(15270094584);
		//echo session('63acec71a362f6c71a46c9a42e8457d0');exit;
		$return['test'] = session(md5($mobile.'time'));
		$this->ajaxReturn($return);
	}
	
    /**
     * @author: king <linzujin@juwang.com>
     * @todo:会员注册数据入库，表单提交类型
     */
   public function signup(){
   		if(IS_POST){
   			$yzm = I('post.yzm');
			$sj_yzm = I('post.sj_yzm');
   			$codeTime = I('post.codecheck');
   			$form['email'] = I('post.email');
   			if(isValidEmail($form['email'])){
   				$form['email'] = $form['email'];
				if(!check_verify($yzm)){
					$error['tip'] = '验证码错误';
				}
   			}elseif(isMobile($form['email'])){
   				$form['mobile'] = $form['email'];
				$form['email'] = '';
				//p($_SESSION);exit;
				if($sj_yzm != session(md5($form['mobile'])))
				{
					$error['tip'] = $sj_yzm.'验证码不对！'.session(md5($form['mobile']));
				}
				if(session(md5($form['mobile'].'time')) < time())
				{
					session(md5($form['mobile']),null);
					$error['tip'] = '验证码已失效！'; 
				}
				$form['mobilebind'] = 1;
   			}else{
   				//$this->error('账户格式错误！') ;
				$error['tip'] = '账户格式错误！';
   			}
   			$form['password'] = md5(I('post.pwd'));
   			$form['uname'] = I('post.realName');
   			$form['umobile'] = I('post.phone');
   			$form['qq'] = I('post.QQ');
   			$agents = A('public');
   			$agent = $agents->getAgents(1);
   			$form['agid'] =$agent['agid'];
			
			//验证码有错之类的直接返回前一个地址并记下错误cookie，运行一下错误提示
			if($error['tip'] != ''){
				cookie('errortip',$error['tip']);
				redirect('/register.html');
			}
			//echo $error['tip'];
			//p($form);exit;
   			$m = D('users');
   			if($regUid = $m->addUser($form)){
				if($form['email']){
					//jumpPost('/regsuccess.html',$form['email']);
					//用cookie传递变量，jumpPost方法js会报错
					cookie('regEmail',$form['email']);
					redirect('/regsuccess.html');
				}
				if($form['mobile']){
					$data = M('users')->where("uid = '".$regUid."'")->find();
					$_SESSION['XMUserInfo'] = $data;
					D('UsersLog')->writeLog($data['uid']);
					redirect('/account/index');
				}
   			}
   		}else{
   			redirect('/404.html');
   		}
   }
   /**
    * @Author: king <linzujin@juwang.com>
    * @todo: 当用户访问一个不存在方法时做跳转
    */
   public function __call($name, $value) {
   	header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
   	redirect('/404.html');
   }
  
}