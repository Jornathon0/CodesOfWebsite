<?php
/**
 * @Author: king <linzujin@juwang.com.cn>
 * @todo: 公共控制器，无需登录
 * @copyright: Copyright (c) Juwang Technologies Inc.
 */
namespace Home\Controller;
use Think\Controller;
class PublicController extends Controller {
	/**
	 * @Author:  king <linzujin@juwang.com.cn>
	 * @todo：步验证帐号唯一性
	 * @param: email 参数可以是邮箱和手机号码
	 * @return: 返回json数据
	 */
    public function isemail(){
   		if(IS_AJAX && IS_GET){
   			$email = I('get.email');
   			$where = "";
   			if(isValidEmail($email)){
   				$where = "email = '".$email."'";
   			}elseif(isMobile($email)){
   				$where = "mobile = '".$email."'";
   			}else{
   				$this->ajaxReturn(array('data'=>2));
   			}
   			$user = D('users');
   			$data = $user->checkUsersExiste($where,'uid');
   			if($data){
   				$this->ajaxReturn(array('data'=>1));
   			}else{
   				$this->ajaxReturn(array('data'=>0));
   			}
   		}
    }
    /**
     * @Author:  king <linzujin@juwang.com.cn>
     * @todo: 获取经纪人数据
     * @param: $type 1随机一条数据
     * @return: 返回json数据
     */
    public function getAgents($type = 0)
    {
    	$agents = M('agents');
    	if($type==1)
    	{
    		$data = $agents->order('RAND()')->find();
    	}else {
    		$data = $agents->select();
    	}
    	return $data;
    }
    /**
     * @author: king <linzujin@juwang.com.cn>
     * @todo:异步验证验证码，不情况session
     * @param:yzm 验证码
     * @return: 返回json数据
     */
    public function isyzm(){
    	if(IS_AJAX && IS_GET){
    		$yzm = I('get.yzm');
			$verify = new \Think\Verify();
			
			//var_dump($verify->check($yzm,1));exit;
    		if($verify->check($yzm)){
				//再带一个验证通过的状态值回去
				$code = encrypt(C('JIAMI_KEY').'|'.$yzm,C('JIAMI_KEY'));
    			$this->ajaxReturn(array('data'=>1,'code'=>$code));
    		}else{
				//错误的话也把错误状态带回去
				$code = encrypt(C('JIAMI_KEY').'|error',C('JIAMI_KEY'));
    			$this->ajaxReturn(array('data'=>0,'code'=>$code));
    		}
    	}
    }
    /**
     * @author: king <linzujin@juwang.com.cn>
     * @todo:激活邮箱
     */
    public function activation(){
    	if(IS_POST){
    		$this->display('Register:activation');
    	}else {
    		$url = explode('/',$_SERVER['REQUEST_URI']);
    		$email = $url[count($url)-2];
    		if(!isValidEmail($email)) redirect('/404.html');
    		$code = $url[count($url)-1];
    		$user_whois = M('user_whois');
    		$data = $user_whois -> where("email='".$email."' and isverify = 0")->find();
    		if($data){
    			$codeV = md5($data['email'].$data['verify']);
    			if($code==$codeV){
    				$user_whois->where('uwid='.$data['uwid'])->save(array('isverify'=>1,'verifytime'=>time()));
    				M('users')->where('uid='.$data['uid'])->save(array('emailbind'=>1));
    				jumpPost('/activation.html');
    			}else{
    				redirect('/404.html');
    			}
    		}else {
    			redirect('/404.html');
    		}
    	}
    }
    public function broker()
    {
    	$agents = M('agents');
    	$this->list = $agents->where('status="1"')->select();
    	$this->display();
    }
    /**
     * @author: king <linzujin@juwang.com.cn>
     * @todo:验证码
     * @return:图片类型
     */
    public function getVerify(){
    	$Verify =     new \Think\Verify();
		$Verify->fontSize = 30;
		$Verify->length   = 4;
		$Verify->useNoise = false;
		$Verify->entry();
		//ob_clean();
		//verify();
    }
	
	/**
	 * Author:   qiuyifeng
	 * Desc:     域名标签判定
	 * $domain： str 要判定的域名
	 * time：2016.07.15
	 */
	public function GetDomainTag($domain = ''){
		
		$return['type'] = '';//域名类型
		$return['typeid'] = 0;//域名类型id
		//a.数字类型 1.五数字 2.四数字 3.三数字 4.两数字
		if(is_numeric($domain)){
			$return['type'] = 'a';
			$numlen = strlen($domain);
			if($numlen == 5){
				$return['typeid'] = 1;
				$return['tip'] = '五数字';
			}
			if($numlen == 4){
				$return['typeid'] = 2;
				$return['tip'] = '四数字';
			}
			if($numlen == 3){
				$return['typeid'] = 3;
				$return['tip'] = '三数字';
			}
			if($numlen == 2){
				$return['typeid'] = 4;
				$return['tip'] = '二数字';
			}
		}
		//b.字母类型 1.单拼 2.双拼 3.三拼
		else if(ctype_alpha($domain)){
			$return['type'] = 'b';
			//算拼音字节的
			import('My.PinyinLen');
			$pyLen = new \PinyinLen();
			$pyNum = $pyLen->pinyinLen($domain);
			if($pyNum>0){
				if($pyNum == 1){
					$return['typeid'] = 1;
					$return['tip'] = '单拼';
				}
				if($pyNum == 2){
					$return['typeid'] = 2;
					$return['tip'] = '双拼';
				}
				if($pyNum == 3){
					$return['typeid'] = 3;
					$return['tip'] = '三拼';
				}
			}
		}
		//c.杂类型 1.数字在前 2.字母在前
		else if(preg_match("/^[A-Za-z0-9]+$/i",$domain)){
			$return['type'] = 'c';
			$str1 = substr($domain, 0, 1 );
			if(preg_match("/^[0-9]$/i",$str1)){
				$return['typeid'] = 1;
				$return['tip'] = '数字在前';
			}
			if(preg_match("/^[A-Za-z]$/i",$str1)){
				$return['typeid'] = 2;
				$return['tip'] = '字母在前';
			}
		}
		
		//p($return);
		return 	$return;
	}
}