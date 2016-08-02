<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:会员登录相关模块
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */

namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:登录界面
	 */
	public function index()
    {
		$userinfo = session('XMUserInfo');
		if($userinfo['uid']){
			redirect('/account/index');
		}
    	$this->display();
    }
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:登录操作
	 */
    
    public function login(){
    	if(IS_POST){
    		$id = I('post.id');
    		$type = '';
    		if(isValidEmail($id)){
    			$where = " email = '".$id."'";
    			$type = 'email';
    		}elseif(isMobile($id)){
    			$where = " mobile = '".$id."'";
    			$type = 'mobile';
    		}elseif(is_numeric($id)){
    			$where = " uid = '".$id."'";
    			$type = 'uid';
    		}else{
    			$this->ajaxReturn(array('data'=>'0','nexturl'=>''));
    		}
			$yzm = I('yzm');
			if($yzm != ''){
				if(!check_verify($yzm)){
					$this->ajaxReturn(array('data'=>'0','tip'=>'验证码错误'));	
				}
			}
    		$pwd = md5(I('post.pwd'));
    		$user = M('users');
    		$data = $user->where("password = '".$pwd."' and ".$where)->find();
			//echo $user->getlastsql();exit;
    		if($data){
    			if($data[$type.'bind']){
    				$_SESSION['XMUserInfo'] = $data;
    				D('UsersLog')->writeLog($data['uid']);
    				$this->ajaxReturn(array('data'=>'1','nexturl'=>''));
    			}else{
    				//redirect('/404.html');
					$this->ajaxReturn(array('data'=>'2'));
    			}
    		}else{
				$this->ajaxReturn(array('data'=>'0'));
			}
    	}
    }
    public function logout()
    {
    	session('XMUserInfo',null);
    	redirect301('/');
    }
	
	//验证码
	public function yanzhenma()
	{
		ob_clean();
		verify();
	}
}