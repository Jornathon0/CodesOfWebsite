<?php
/**
* @author linguojin <linguojin@juwang.com.cn>
* @todo:网站红包功能
* @copyright  Copyright (c) Juwang Technologies Inc.
*/
namespace Home\Controller;

use Think\Controller;


class LuckyController extends Controller
{

	
	public function _initialize(){

		$userinfo = session('XMUserInfo');

		$this->userinfo = $userinfo;

	}

	//设置红包
    public function setLuckyMoney(){

    	$userinfo = $this->userinfo;

    	// 此处仍需要添加操作人的身份识别
    	if (!empty($userinfo)) {
    		$uid = $userinfo['uid'];
	    	
	    	// $data = array(
	    	// 		'total' => I(post.total),
	    	// 		'num' => I(post.num),
	    	// 		'aid' =>session('XMUserInfo')['uid'],//此处管理员的ID暂时未知
	    	// 		'permin' => I(post.min),
	    	// 	);

    		$num = '5';

	    	$total = '100';

	    	$min='0.01';

	    	$checkset = M('packets_settings')->getField('id',1);

	    	if(is_null($checkset)){

	    		$data = array(

	    			'total' => $total,
	    			'num' => $num,
	    			'aid' => '123',
	    			'permin' => $min,
	    			'addtime' => time(),
	    			'updatetime' => time(),
	    			);
	    		M('packets_settings') ->add($data);
	    	}elseif ($checkset) {
	    		
	    		$data = array(

	    			'total' => $total,
	    			'num' => $num,
	    			'aid' => '234',
	    			'permin' => $min,
	    			'updatetime' => time(),

	    			);
	    		M('packets_settings')->where("id=$checkset")->save($data);
	    	}else{
	    		$this ->ajaxReturn('Syeterm error!!');
	    	}
    		
    		

    	} else {
    		$this ->ajaxReturn('You don\'t have the privilege to do this! ');
    	}
    	

    }


	 // 抢红包
    public function getLuckyMoney()
    {
    	$uid = $this->userinfo['uid'];

    	$status = $this->checkStatus();

    	switch ($status) {

    		case '3':
    			$data = array(
            		'status' => '3',
            		'Msg' => '很遗憾，今天的红包已经被抢光，明天再来吧。',
            		);
    			$this ->ajaxReturn($data);
    			break;
    		
    		case '2':
    			$data = array(
            		'status' => '2',
            		'Msg' => '很遗憾，您今天的红包已经抢过了，明天再来吧。',
            		);
    			$this ->ajaxReturn($data);
    			break;

    		case '1':

    			//待修改
    			$today = $this->today();
    			$start = $today['0'];
    			$end = $today['1'];
    			
    			$records = M('packets_record')->where("addtime between $start and $end")->getField('packetsmoney',true);
    			
    			if(is_null($records)){
    				$sum = 0;
    			}else{
    				$sum = array_sum($records);
    			}

    			$num = count($records);    			
	    		
	    		$settings = M('packets_settings');
				$total = $settings->getField('total',1);
				$permin = $settings->getField('permin',1);
				$set_num = $settings->getField('num',1);

				$left_num = $set_num - $num;

				$left_money = $total - $sum;

				$money = $this->calculateMoney($left_num,$min,$left_money);
				
				$data = array(

						'uid' => $uid,
						'addtime' => time(),
						'packetsmoney' =>$money,
						);

				M('packets_record')->add($data);

				//更新个人账号
				$tem_user = M('users');
				$tot_money = $tem_user->where("uid = '{$uid}'")->getField('money',1);
				$tot_money = $tot_money + $money;
				$tem_user->money = $tot_money;
				$tem_user->where("uid = '{$uid}'")->save();

				$Msg = array(
						'status' => $status, 
						'Msg' => '恭喜您抢得'.$money.'元',
						);
    			$this ->ajaxReturn($Msg);
    			break;

    		case '0':
    			$data = array(
            		'status' => '0',
            		'Msg' => '请登录您的账号。'
            		);
    			$this ->ajaxReturn($data);
    			break;

    		case '-1':
    			$data = array(
            		'status' => '-1',
            		'Msg' => '很抱歉，系统错误！'
            		);
    			$this ->ajaxReturn($data);
    			break;

    	}

    }


	//计算分配红包
    private function calculateMoney($num,$min,$total)
    {
    	if($num == '1'){
    	
    		$money = $total;
    	
    	}else{
    	
	    	for ($i=1; $i < $num; $i++) { 
		    			$safe_total = ($total - ($num - $i)*$min)/($num - $i);
		    			$lest_total = $safe_total/10;
		    			$min = $min > $lest_total?$min:$lest_total;
		    			$money = mt_rand($min*100 , $safe_total*100)/100;
		    			$total = $total -$money;
		    			$luckymoney[$i] = $money;
		   	}

			$luckymoney[$num] = $total;
			$key = array_rand($luckymoney,1);
			$money = $luckymoney[$key];			
    	}

		return $money ;
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