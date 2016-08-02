<?php
/**
 * @author: qiuyifeng <qiuyifeng@juwang.com.cn>
 * @todo:网站前台终端域名
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */
namespace Home\Controller;
use Think\Controller;
class TerminalController extends Controller {
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:step_1:终端域名
	 */
    public function index(){
    	$this->display();
    }
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:step_2:终端域名提交
	 */
    public function operation(){
		//echo '<pre>';print_r(I());
		$userinfo = session('XMUserInfo');
		if(IS_POST && $userinfo['uid']){
			$Terminal = M('terminal');
			$domainName = I('post.domainName');//域名
			$min_price = I('post.min_price');//最低价格
			$max_price = I('post.max_price');//期望价格
			$ipt_terminal = I('post.ipt_terminal');//希望终端
			$tt_msg = I('post.tt_msg');//留言
			
			$data['uid'] = $userinfo['uid'];//用户ID
			$data['remark'] = $tt_msg;//留言
			$data['intime'] = time();//创建时间
			
			$error = '';
			foreach($domainName as $k=>$v){
				if($v && $min_price[$k] && $max_price[$k]){
					$data['domain'] = $v;
					$data['minprice'] = (int)$min_price[$k];
					$data['maxprice'] = (int)$max_price[$k];
					$data['terminal'] = $ipt_terminal[$k];
					$result = $Terminal->data($data)->add();
					if (!$result) {
                        $error = 'insert sql error!';
                    }
				}
			}
			if($error) {
				die($error);
			}else{
				redirect('/account/terminal');
			}
		}
    }
}