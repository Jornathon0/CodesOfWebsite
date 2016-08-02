<?php
/**
 * @author: qiuyifeng <qiuyifeng@juwang.com.cn>
 * @todo:网站前台域名代购
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */
namespace Home\Controller;
use Think\Controller;
class PurchaseController extends Controller {
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:step_1:域名代购
	 */
    public function index(){
    	$this->display();
    }
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:step_2:域名代购提交
	 */
	public function operation(){
		//echo '<pre>';print_r(I());
		$userinfo = session('XMUserInfo');
		if(IS_POST){
			$Purchase = M('purchase');
			$domainName = I('post.domainName');
			$email = I('post.email');
			$money = I('post.money');
			$intro = I('post.intro');
			
			$uid = $userinfo['uid'];//用户ID
			$data['domain'] = $domainName;//域名
			$data['email'] = $email;//联系邮箱
			$data['price'] = $money;//心理预算价格
			$data['remark'] = $intro;//留言
			$data['intime'] = time();//创建时间
			//已登录用户
			if($userinfo['uid'] > 0){
				$data['uid'] = $userinfo['uid'];
				$data['agid'] = $userinfo['agid'];
			}else{
				$data['uid'] = 0;
				//随机分配经纪人
				$agents = A('public');
   				$agent = $agents->getAgents(1);	
				$data['agid'] = $agent['agid'];
			}
			//print_r($data);
			$result = $Purchase->data($data)->add();
			if($result){
				jumpPost('/procurement','ok');
			}else{
				$this->error('域名代购数据出错！');
			}
			
		}
		
		
	}
}