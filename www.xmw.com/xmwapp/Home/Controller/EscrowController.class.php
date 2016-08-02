<?php
/**
 * @author: qiuyifeng <qiuyifeng@juwang.com.cn>
 * @todo:网站前台域名中介
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */
namespace Home\Controller;
use Think\Controller;
class EscrowController extends Controller {
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:step_1:域名中介
	 */
    public function index(){
		$userinfo = session('XMUserInfo');
		$sxfv = C('SXF');
		$sxf = $sxfv['vip'.$userinfo['vip']];//手续费费率
		$this->assign('sxf',$sxf);
    	$this->display();
    }
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:step_2:域名中介提交
	 */
	public function operation(){
		$userinfo = session('XMUserInfo');
		if(IS_POST && $userinfo['uid']){
			$Escrow = M('escrow');
			$EscrowDomain = M('escrow_domain');
			$EscrowExt = M('escrow_ext');
			$domainName = I('post.domainName');
			$money = I('post.money');
			$touid = I('post.user');
			$dprice = I('post.dprice');
			$roledprice = I('post.roledprice');
			$C_role = array('buy'=>1,'sell'=>2);//角色:1买家 2卖家
			$C_rolename = array('1'=>'买家','2'=>'卖家');//角色名:1买家 2卖家
			$C_roledprice = array('one'=>1,'two'=>2,'three'=>3);//由谁承担中介费:1买家 2卖家 3双方
			
			$data['uid'] = $userinfo['uid'];//用户ID
			$data['domain'] = '';//中介域名
			$data['touid'] = $touid;//对方的会员ID
			$data['role'] = $C_role[$dprice];//角色
			$data['roledprice'] = $C_roledprice[$roledprice];//由谁承担
			$data['price'] = 0;//交易总额
			$data['intime'] = time();//创建时间
			$datetime = date("YmdHis",$data['intime']);//返回地址的时间点
			$error = '';
			$sxfv = C('SXF');
			$sxf = $sxfv['vip'.$userinfo['vip']]/100;//手续费费率
			foreach($domainName as $k=>$v){
				if($v && $money[$k]){
					$data['domain'] .= $v.',';
					$data['price'] += (int)$money[$k];
				}
			}
			$data['domain'] = trim($data['domain'],',');
			if($data['role'] == 1){
				$data['status'] = 1;
			}else{
				$data['status'] = 0;
			}
			
			//记录域名中介
			$esid = $Escrow->add($data);
			//记录每条域名信息
			foreach($domainName as $k=>$v){
				$data2['esid'] = $esid;
				$data2['domain'] = $v;
				$data2['price'] = (int)$money[$k];
				$esdid = $EscrowDomain->add($data2);
			}
			//记录域名中介交易进度
			$data3['esid'] = $esid;
			$data3['remark'] = $C_rolename[$data['role']].'发起了本次交易';
			$data3['type'] = $data['role'];
			$data3['price'] = $data['price'];
			$data3['roledprice'] = $data['roledprice'];
			$data3['status'] = 1;
			if($data3['role'] == 1){
				$data3['status'] = 1;
			}else{
				$data3['status'] = 0;
			}
			$data3['intime'] = $data['intime'];
			$eseid = $EscrowExt->add($data3);
			
			//记录到交易记录中
			$deal['typefrom'] = 3;//'来源：1.一口价 2.极速竞价 3.中介 4.优质 5.议价'
			$deal['fromid'] = $esid;//交易信息来源表索引id
			$deal['domain'] = $data['domain'];//域名描述
			$deal['price'] = $data['price'];//交易价格
			//买卖双方id
			if($data['role'] == 1){
				$deal['buyid'] = $data['uid'];
				$deal['sellid'] = $data['touid'];
			}elseif($data['role'] == 2){
				$deal['buyid'] = $data['touid'];
				$deal['sellid'] = $data['uid'];
			}
			
			//'状态：0.等待买家同意条款 1.等待卖家同意条款 2.等待买家付款 3.等待卖家转移域名 4.等待买家确认收到域名 5.交易完成 6.交易取消'
			if($data['role'] == 1){
				$deal['status'] = 1;
			}else{
				$deal['status'] = 0;
			}
			$deal['intime'] = $data['intime'];
			$deal['updatetime'] = $data['intime'];
			$dealid = M('deals')->add($deal);
			
			redirect('/account/dealdetail/'.$datetime.$esid);
			
		}
	}
}