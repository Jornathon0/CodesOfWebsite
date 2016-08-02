<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:网站前台相关模块
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:网站首页
	 */
	
	public function _initialize(){

		$userinfo = session('XMUserInfo');

		$this->userinfo = $userinfo;

	}

    public function index(){
    	
		$status = $this->checkStatus();
		
		$this->assign('status',$status);

    	$this->display();
		
	
	}
    
	//查看状态
    private function checkStatus(){

		$uid = $this->userinfo['uid'];

		if(empty($uid)){

			$status = '0' ;
		
		}elseif($uid){
			
			$today=$this->today();
			$start = $today['0'];
			$end = $today['1'];
			$record = M('packets_record');
			$num = $record->where("addtime between $start and $end")->order('id desc')->count();

			$settings = M('packets_settings');
			$total = $settings->getField('total',1);
			$permin = $settings->getField('permin',1);
			$set_num = $settings->getField('num',1);
			
	    	$record2 = $record->where("(addtime between $start and $end) and uid=$uid")->find();

	    	if($record2){

	    		$status = '2';//今日红包已抢

	    	}else{

	    		if($num < $set_num){

		    		$status = '1';//今日红包未抢

				}else{
	
					$status = '3';//红包已经抢完
				
		    	}
			}
		}else{

			$status = '-1';

		}
		// var_dump($status);
		// exit();
		return $status;
    }

    private function today(){

    	$starttime = date('Y-m-d');

    	$endtime = date('Y-m-d 23:59:59');
    	
    	$starttime = strtotime($starttime);
    	
    	$endtime = strtotime($endtime);

    	$today = array($starttime,$endtime);

    	return $today;

    }

}