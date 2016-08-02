<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:会员相关模块
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */

namespace Home\Controller;
use Think\Controller;
class AccountController extends CommonController {

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:初始化
	 */
	public function _initialize() {
		$userinfo = session('XMUserInfo');
		$this->userinfo = $userinfo;
		
		if (empty($userinfo)) {
			header("HTTP/1.0 404 Not Found");
			//使HTTP返回404状态码
			redirect('/login');
		} else {
			//分配域名经纪人
			$agentInfo = M('agents')->where("agid = {$userinfo['agid']}")->find();
			$this->assign('agentInfo', $agentInfo);
		}
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:买卖身份的确认以及手续费的计算
	 */
	private function getPoundage($fromid,$typefrom) {
		
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$Deals = M('deals');
		$Dealdata = $Deals->where("typefrom = '{$typefrom}' and fromid = '{$fromid}'")->find();
		//买卖方身份的确定
		if ($uid == $Dealdata['buyid']) {
			$returndata['roleact'] = 'buy';
		} elseif ($uid == $Dealdata['sellid']) {
			$returndata['roleact'] = 'sell';
		}
		
		//计算手续费
		$sxfv = C('SXF');
		$sxf = $sxfv['vip' . $userinfo['vip']] / 100;
		if($typefrom == 3){
			$Escrow = M('escrow');
			$roledprice = $Escrow->where("esid = {$fromid}")->getField('roledprice');
		}
		//手续费费率
		//买卖家手续费计算
		if ($returndata['roleact'] == 'buy') {
			if ($roledprice == 1) {
				$returndata['poundage'] = floor((int)$Dealdata['price'] * $sxf);
			} elseif ($roledprice == 2) {
				$returndata['poundage'] = 0;
			} elseif ($roledprice == 3) {
				$returndata['poundage'] = floor((int)$Dealdata['price'] * $sxf / 2);
			}
		} else if ($returndata['roleact'] == 'sell') {
			if ($roledprice == 1) {
				$returndata['poundage'] = 0;
			} elseif ($roledprice == 2) {
				$returndata['poundage'] = floor((int)$Dealdata['price'] * $sxf);
			} elseif ($roledprice == 3) {
				$returndata['poundage'] = floor((int)$Dealdata['price'] * $sxf / 2);
			}
		}
		return $returndata;
	}
	
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:登录验证
	 */
	public function index() {
		//用户信息相关
		$userinfo = $this->userinfo;
		$sxfv = C('SXF');
		$sxf = $sxfv['vip' . $userinfo['vip']];
		$userinfo['sxf'] = $sxf;
		$userinfo['local'] = M('users_login_log')->where("uid = {$userinfo['uid']}")->order('intime desc')->getField('local');
		$array['userinfo'] = $userinfo;
		//未完成交易记录
		$Deals = M('deals');
		//$EscrowExt = M('escrow_ext');
		$uid = $this->userinfo['uid'];
		$dealingData = $Deals->where("(buyid = {$uid} or sellid= {$uid}) and (status != 5 and status != 6)")->order('updatetime desc')->limit(5)->select();
		foreach ($dealingData as $k => $v) {
			$domains = explode(',', $v['domain']);
			$domainsnum = count($domains);
			if ($domainsnum > 1) {
				$dealingData[$k]['tit'] = $domains[0] . ' 等' . $domainsnum . '个域名';
			} else {
				$dealingData[$k]['tit'] = $domains[0];
			}
			//来源：1.一口价 2.极速竞价 3.中介 4.优质 5.议价
			if ($v['typefrom'] == 1) {

			} elseif ($v['typefrom'] == 2) {

			} elseif ($v['typefrom'] == 3) {
				if ($v['status'] == 0) {
					//0.等待买家同意条款
					if ($uid == $v['buyid']) {
						$dealingData[$k]['act'] = 'buy';
						$dealingData[$k]['tip'] = "等待您同意条款";
					} else {
						$dealingData[$k]['act'] = 'sell';
						$dealingData[$k]['tip'] = "等待买家同意条款";
					}
				} elseif ($v['status'] == 1) {
					//1.等待卖家同意条款
					if ($uid == $v['buyid']) {
						$dealingData[$k]['act'] = 'buy';
						$dealingData[$k]['tip'] = "等待卖家同意条款";
					} else {
						$dealingData[$k]['act'] = 'sell';
						$dealingData[$k]['tip'] = "等待您同意条款";
					}
				} elseif ($v['status'] == 2) {
					//2.等待买家付款
					if ($uid == $v['buyid']) {
						$dealingData[$k]['act'] = 'buy';
						$dealingData[$k]['tip'] = "等待您付款到xmw.com";
					} else {
						$dealingData[$k]['act'] = 'sell';
						$dealingData[$k]['tip'] = "等待买家付款";
					}
				} elseif ($v['status'] == 3) {
					//3.等待卖家同意条款
					if ($uid == $v['buyid']) {
						$dealingData[$k]['act'] = 'buy';
						$dealingData[$k]['tip'] = "等待卖家转移域名";
					} else {
						$dealingData[$k]['act'] = 'sell';
						$dealingData[$k]['tip'] = "等待您转移域名";
					}
				} elseif ($v['status'] == 4) {
					//4.等待买家确认收到域名
					if ($uid == $v['buyid']) {
						$dealingData[$k]['act'] = 'buy';
						$dealingData[$k]['tip'] = "等待您确认收到域名";
					} else {
						$dealingData[$k]['act'] = 'sell';
						$dealingData[$k]['tip'] = "等待买家确认收到域名";
					}
				}
				if (strpos($dealingData[$k]['tip'], '您')) {
					$dealingData[$k]['color'] = 1;
				} else {
					$dealingData[$k]['color'] = 0;
				};

			} elseif ($v['typefrom'] == 4) {

			} elseif ($v['typefrom'] == 5) {

			}

		}

		//print_r($dealingData);
		$array['dealing'] = $dealingData;
		$this->assign($array);
		$this->display();
	}

	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:登录验证
	 */
	public function GetUserInfo() {

	}

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:所有交易记录
	 */
	public function deals() {
		$act = I('act');

		$this->assign('act', $act);
		$this->display();
	}

 

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:所有交易记录异步获取数据
	 */
	public function dealsinf() {
		$getVar = I();
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];

		$wheresql = "(buyid = {$uid} or sellid = {$uid})";
		switch ($getVar['type']) {
			case '0' :
				$wheresql .= '';
				break;
			case '1' :
				$wheresql .= ' and status != 5 and  status != 6';
				break;
			case '2' :
				$wheresql .= ' and status = 5';
				break;
			case '3' :
				$wheresql .= ' and status = 6';
				break;
		}
		if ($getVar['domainName']) {
			$wheresql .= " domain like '%" . $getVar['domainName'] . "%'";
		}
		if ($getVar['starttime']) {
			$stime = strtotime($getVar['starttime']);
			$wheresql .= " updatetime >= {$stime}";
		}
		if ($getVar['endtime']) {
			$etime = strtotime($getVar['endtime']);
			$wheresql .= " updatetime <= {$etime}";
		}
		switch ($getVar['order']) {
			case 'title' :
				$order = 'domain';
				break;
			case 'buymoney' :
				$order = 'price';
				break;
			case '_type' :
				$order = 'typefrom';
				break;
			case 'behavior' :
				$order = 'buyid';
				break;
			case '_state' :
				$order = 'status';
				break;
			default :
				$order = 'id';
		}
		$ordersql = $order . ' ' . $getVar['ordertype'] . ',updatetime desc';
		$Deals = M('deals');
		$Escrow = M('escrow');
		$data['total'] = $Deals->where($wheresql)->count();
		$dealsdata = $Deals->where($wheresql)->order($ordersql)->limit(20)->page($getVar['pageindex'])->select();
		//echo $Deals->getlastsql();exit;
		$list = array();
		foreach ($dealsdata as $k => $v) {
			$list[$k]['RowID'] = $v['id'];
			$list[$k]['id'] = date("YmdHis", $v['intime']) . $v['fromid'];
			$list[$k]['buyer'] = $v['buyid'];
			$list[$k]['seller'] = $v['sellid'];
			$list[$k]['_money'] = $v['price'];

			$list[$k]['title'] = '';
			$domains = explode(',', $v['domain']);
			$domainsnum = count($domains);
			if ($domainsnum > 1) {
				$list[$k]['title'] = $domains[0] . ' 等' . $domainsnum . '个域名';
			} else {
				$list[$k]['title'] = $domains[0];
			}
			$list[$k]['_type'] = $v['typefrom'];
			$list[$k]['_state'] = $v['status'];
			if ($uid == $v['buyid']) {
				$list[$k]['ib'] = 0;
				//买入
			} else {
				$list[$k]['ib'] = 1;
				//卖出
			}
			$list[$k]['pid'] = 0;
			//批量交易
		}
		$data['data'] = json_encode($list);

		$this->ajaxReturn($data);
	}

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:域名中介
	 */
	public function Dealdetail() {
		// 域名中介提交后的跳转地址
		// /account/dealdetail/2016031614195212 时间+esid
		// 现在这里暂时只处理中介的订单;
		$datetime = substr(I('datetime'), 0, 14);
		$esid = substr(I('datetime'), 14);
		$intime = strtotime($datetime);
		$array['datetimeUrl'] = I('datetime');

		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];

		$Escrow = M('escrow');
		$Deals = M('deals');
		$escrowdata = $Escrow->where("(uid = '{$uid}' or touid = '{$uid}') and intime = '{$intime}' and esid = '{$esid}'")->find();
		if ($escrowdata['esid']) {
			$EscrowDomain = M('escrow_domain');
			$EscrowExt = M('escrow_ext');
			$escrowdata['domain'];
			$domains = explode(',', $escrowdata['domain']);
			$domainsnum = count($domains);
			if ($domainsnum > 1) {
				$escrowdata['tit'] = $domains[0] . ' 等' . $domainsnum . '个域名';
			} else {
				$escrowdata['tit'] = $domains[0];
			}

			//查询域名信息
			$eDomainData = $EscrowDomain->where("esid = {$escrowdata['esid']}")->select();
			$array['num'] = count($eDomainData);

			//查询交易信息
			$eExtData = $EscrowExt->where("esid = {$escrowdata['esid']}")->order('intime desc')->select();

			//买卖家状态和提示信息确定
			//买卖方身份的确定
			if ($uid == $escrowdata['uid']) {
				if ($escrowdata['role'] == 1) {
					$escrowdata['roleact'] = 'buy';
				} else if ($escrowdata['role'] == 2) {
					$escrowdata['roleact'] = 'sell';
				}
			} elseif ($uid == $escrowdata['touid']) {
				if ($escrowdata['role'] == 1) {
					$escrowdata['roleact'] = 'sell';
				} else if ($escrowdata['role'] == 2) {
					$escrowdata['roleact'] = 'buy';
				}
			}
			
			//计算手续费
			$sxfv = C('SXF');
			$sxf = $sxfv['vip' . $userinfo['vip']] / 100;
			//手续费费率
			//买卖家手续费计算
			if ($escrowdata['roleact'] == 'buy') {
				if ($escrowdata['roledprice'] == 1) {
					$escrowdata['poundage'] = floor((int)$escrowdata['price'] * $sxf);
				} elseif ($escrowdata['roledprice'] == 2) {
					$escrowdata['poundage'] = 0;
				} elseif ($escrowdata['roledprice'] == 3) {
					$escrowdata['poundage'] = floor((int)$escrowdata['price'] * $sxf / 2);
				}
			} else if ($escrowdata['roleact'] == 'sell') {
				if ($escrowdata['roledprice'] == 1) {
					$escrowdata['poundage'] = 0;
				} elseif ($escrowdata['roledprice'] == 2) {
					$escrowdata['poundage'] = floor((int)$escrowdata['price'] * $sxf);
				} elseif ($escrowdata['roledprice'] == 3) {
					$escrowdata['poundage'] = floor((int)$escrowdata['price'] * $sxf / 2);
				}
			}

			$array['eData'] = $escrowdata;
			$array['eDomainData'] = $eDomainData;
			$array['eExtData'] = $eExtData;
			/*if($eExtData['type'] == $escrowdata['role']){
			 echo 111;
			 }else{echo 222;}
			 echo '<pre>';print_r($array);exit;*/

			$this->assign($array);
			if($escrowdata['status'] == 0){
				//买卖双方议价-等待买方	
				if($escrowdata['roleact'] == 'buy'){
					$this->assign('handlePower',1);
				}else{
					$this->assign('handlePower',0);
				}
				$temp = 'dealdetail_talk';
			}elseif($escrowdata['status'] == 1){
				//买卖双方议价-等待卖方
				if($escrowdata['roleact'] == 'buy'){
					$this->assign('handlePower',0);
				}else{
					$this->assign('handlePower',1);
				}
				$temp = 'dealdetail_talk';
			}elseif($escrowdata['status'] == 2){
				//付款
				$balance =  M('users')->where("uid = {$userinfo['uid']}")->getField('money');
				$this->assign('balance',(int)$balance);
				if($escrowdata['roleact'] == 'buy'){
					$this->assign('handlePower',1);
				}else{
					$this->assign('handlePower',0);
				}
				$temp = 'dealdetail_pay';
			}elseif($escrowdata['status'] == 3){
				//转移域名
				if($escrowdata['roleact'] == 'buy'){
					$this->assign('handlePower',0);
				}else{
					$this->assign('handlePower',1);
				}	
				$temp = 'dealdetail_transfer';
			}elseif($escrowdata['status'] == 4){
				//确认收到域名
				if($escrowdata['roleact'] == 'buy'){
					$this->assign('handlePower',1);
				}else{
					$this->assign('handlePower',0);
				}
				$dietime = $eExtData[0]['intime'] - 15*24*3600;
				$nowtime = time();
				$dieExtdata = $eExtData[0];
				if($nowtime > $dietime){
					//echo '过期了';
					unset($dieExtdata['eseid']);//移除自增变量
					$dieExtdata['remark'] = "交易自动完成";
					$dieExtdata['status'] = 5;
					$dieExtdata['intime'] = $nowtime;
					//自动取消操作
					$EscrowExt->add($dieExtdata);
					$Escrow->where("esid = '{$esid}'")->setField('status', '5');
					$dieDeal['status'] =  5;
					$dieDeal['updatetime'] = $nowtime;
					$Deals->where("typefrom = 3 and fromid = '{$esid}'")->save($dieDeal);
					redirect('/account/dealdetail/'.I('datetime'));
				}
				$this->assign('dietime',$dietime);
				$temp = 'dealdetail_confirm';
			}elseif($escrowdata['status'] == 5){
				//成功
				if($escrowdata['roleact'] == 'buy'){
					$this->assign('handlePower',0);
				}else{
					$this->assign('handlePower',1);
				}
				$temp = 'dealdetail_over';
			}elseif($escrowdata['status'] == 6){
				//取消
				$temp = 'dealdetail_over';
			}
			$this->display($temp);
			//$this->display('dealdetail_sell');
		} else {
			redirect('/404.html');
		}
	}

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:域名中介议价过程相关操作
	 */
	public function deal_upd() {
		$who = I('who');
		$id = I('id');
		$money = I('money');
		$roledprice = I('roledprice');
		$C_roledprice = array('one' => 1, 'two' => 2, 'three' => 3);
		//由谁承担中介费:1买家 2卖家 3双方
		$datetime = substr($id, 0, 14);
		$esid = substr($id, 14);
		$intime = strtotime($datetime);
		$nowtime = time();

		$Escrow = M('escrow');
		$EscrowExt = M('escrow_ext');
		$Deals = M('deals');
		//处理更新escrow
		//由谁承担
		$edata['roledprice'] = $C_roledprice[$roledprice];
		//交易总额
		$edata['price'] = $money;
		//状态
		if($who == 'buy'){
			$edata['status'] = 1;
		}else{
			$edata['status'] = 0;
		}
		$Escrow->where("esid = {$esid}")->save($edata);
		//处理添加escrow_ext
		$eExtdata['esid'] = $esid;
		if ($who == 'buy') {
			$eExtdata['remark'] = '买家修改了交易条款，等待卖家同意条款';
		} else {
			$eExtdata['remark'] = '卖家修改了交易条款，等待买家同意条款';
		}
		//由谁承担
		$eExtdata['roledprice'] = $C_roledprice[$roledprice];
		//交易总额
		$eExtdata['price'] = $money;
		//状态
		if($who == 'buy'){
			$eExtdata['status'] = 1;
		}else{
			$eExtdata['status'] = 0;
		}
		$eExtdata['intime'] = $nowtime;
		$EscrowExt->add($eExtdata);

		//处理更新deals
		//typefrom=3 fromid=$esid
		//交易总额
		$Ddata['price'] = $money;
		//状态
		if($who == 'buy'){
			$Ddata['status'] = 1;
		}else{
			$Ddata['status'] = 0;
		}
		$Ddata['updatetime'] = $nowtime;
		$Deals->where("typefrom = 3 and fromid = {$esid}")->save($Ddata);
		
		redirect('/account/dealdetail/'.$id);
	}

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:域名中介同意取消等相关操作
	 */
	public function dealdetailoperation() {
		$type = I('type');
		$id = I('id');
		$datetime = substr($id, 0, 14);
		$esid = substr($id, 14);
		$intime = strtotime($datetime);

		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		$Escrow = M('escrow');
		$escrowdata = $Escrow->where("(uid = '{$uid}' or touid = '{$uid}') and intime = '{$intime}' and esid = '{$esid}'")->find();
		//echo $Escrow->getlastsql();exit ;
		if ($escrowdata['esid']) {
			$Deals = M('deals');
			$EscrowExt = M('escrow_ext');
			//$type 1.买家取消 2.卖家取消 3.买家同意 4.卖家同意 5.买家付款
			if ($type == 1 || $type == 2) {
				//取消操作
				$data['esid'] = $esid;
				//$data['type'] = $type;
				$data['price'] = $escrowdata['price'];
				$data['roledprice'] = $escrowdata['roledprice'];
				$data['status'] = 6;
				$data['intime'] = time();
				if ($type == 1) {
					$data['remark'] = '买家取消了本次交易';
				} else {
					$data['remark'] = '卖家取消了本次交易';
				}
				$eExtID = $EscrowExt->add($data);
				$Escrow->where("esid = '{$esid}'")->setField('status', '6');
				//来源：1.一口价 2.极速竞价 3.中介 4.优质 5.议价
				//状态：0.等待买家同意条款 1.等待卖家同意条款 2.等待买家付款 3.等待卖家转移域名 4.等待买家确认收到域名 5.交易完成 6.交易取消
				$Deals->where("typefrom = 3 and fromid = {$esid}")->save(array('status' => 6, 'updatetime' => $data['intime']));

				if ($eExtID) {
					$this->ajaxReturn(array('data' => '1', 'msg' => '取消操作成功'));
				} else {
					$this->ajaxReturn(array('data' => '0', 'msg' => '操作异常！'));
				}
			} elseif ($type == 3 || $type == 4) {
				//同意操作
				$data['esid'] = $esid;
				$data['price'] = $escrowdata['price'];
				$data['roledprice'] = $escrowdata['roledprice'];
				$data['status'] = 2;
				$data['intime'] = time();
				if ($type == 3) {
					$data['remark'] = '买家同意了交易，等待买家付款中';
				} else {
					$data['remark'] = '卖家接受了交易，等待买家付款中';
				}
				$eExtID = $EscrowExt->add($data);
				$Escrow->where("esid = '{$esid}'")->setField('status', '2');
				//来源：1.一口价 2.极速竞价 3.中介 4.优质 5.议价
				//状态：0.等待买家同意条款 1.等待卖家同意条款 2.等待买家付款 3.等待卖家转移域名 4.等待买家确认收到域名 5.交易完成 6.交易取消
				$Deals->where("typefrom = 3 and fromid = {$esid}")->save(array('status' => 2, 'updatetime' => $data['intime']));

				if ($eExtID) {
					$this->ajaxReturn(array('data' => '1', 'msg' => '同意操作成功'));
				} else {
					$this->ajaxReturn(array('data' => '0', 'msg' => '操作异常！'));
				}
			} elseif ($type == 5) {
				//付款操作
				//查看用户余额是否足够支付本次交易
				$balance =  M('users')->where("uid = {$userinfo['uid']}")->getField('money');
				$price = $escrowdata['price'];
				
				$payinfo = $this->getPoundage($esid,3);
				$payment = $price+$payinfo['poundage'];
				

				if($balance < $payment){
					$this->ajaxReturn(array('data' => '0', 'msg' => '您帐号的余额不足，立即前往充值', 'href' => '/account/recharge'));
				}else{
					//付款操作
					//具体操作和付款记录后续开发，
					
					//付款成功改变状态
					if(I('return') == 1){
						$edata['trantype'] = 1;
						$zcsinfo = explode(':',I('returnfngshi'));
						$edata['zcs'] = $zcsinfo[0];
						$edata['zcsid'] = $zcsinfo[1];
					}else{
						$edata['trantype'] = 0;
					}
					$edata['status'] = 3;
					
					$data['esid'] = $esid;
					$data['price'] = $escrowdata['price'];
					$data['roledprice'] = $escrowdata['roledprice'];
					$data['status'] = 3;
					$data['intime'] = time();
					$data['remark'] = '买家已经付款到平台，等待卖家转移域名';
					$eExtID = $EscrowExt->add($data);
					$Escrow->where("esid = '{$esid}'")->save($edata);//->setField('status', '3');
					$Deals->where("typefrom = 3 and fromid = {$esid}")->save(array('status' => 3, 'updatetime' => $data['intime']));
					$this->ajaxReturn(array('data' => '1', 'msg' => '付款成功'));
				}
				
			} elseif ($type == 6) {
				//域名转移
				$edata['code'] = I('code')?I('code'):'';
				$edata['status'] = 4;
				
				$data['esid'] = $esid;
				$data['price'] = $escrowdata['price'];
				$data['roledprice'] = $escrowdata['roledprice'];
				$data['status'] = 4;
				$data['intime'] = time();
				$data['remark'] = '卖家已经转移域名，等待买家确认收到域名';
				$eExtID = $EscrowExt->add($data);
				$Escrow->where("esid = '{$esid}'")->save($edata);//->setField('status', '3');
				$Deals->where("typefrom = 3 and fromid = {$esid}")->save(array('status' => 4, 'updatetime' => $data['intime']));
				
				$this->ajaxReturn(array('data' => '1', 'msg' => '域名转移成功'));
			} elseif ($type == 7) {
				//确认转移
				$data['esid'] = $esid;
				$data['price'] = $escrowdata['price'];
				$data['roledprice'] = $escrowdata['roledprice'];
				$data['status'] = 5;
				$data['intime'] = time();
				$data['remark'] = '交易完成';
				$eExtID = $EscrowExt->add($data);
				$Escrow->where("esid = '{$esid}'")->setField('status', '5');
				$Deals->where("typefrom = 3 and fromid = {$esid}")->save(array('status' => 5, 'updatetime' => $data['intime']));
				
				//收取相应手续费并将货款打入卖家帐号，后续开发
				
				$this->ajaxReturn(array('data' => '1', 'msg' => '交易完成'));
			}
		} else {
			$this->ajaxReturn(array('data' => '0', 'msg' => '非法操作'));
		}

	}

	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:我的终端域名
	 */
	public function terminal() {
		//
		/*echo '查询到的域名终端信息<br><pre>';
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		if ($uid > 0) {
			$Terminal = M('terminal');
			$row = $Terminal->where("uid = '{$uid}'")->select();
			print_r($row);
		}*/
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:获取终端域名数据
	 */
	public function terminalInf() {
		#测试数据
		//$list[] = array('id'=>'1','domainName'=>'111.com','publishtime'=>'2016-01-02 12:00:45');
		//$list[] = array('id'=>'2','domainName'=>'222.com','publishtime'=>'2016-01-02 17:00:45');
		$list = '';
		$data['total'] = 0;
		$data['data'] = json_encode($list);
		$this->ajaxReturn($data);
	}
	
	/**##暂不需要
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:出售页管理
	 */
	public function sellpage() {
		
		$this->display();
	}
	
	/**##暂不需要
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:获取出售页数据
	 */
	public function sellpageinf() {
		#测试数据
		$list[] = array('RowID'=>'1','id'=>'620405','domainName'=>'884z.com','templateName'=>'蓝色清新');
		$list[] = array('RowID'=>'2','id'=>'620404','domainName'=>'jiumuge.com','templateName'=>'绿色清新');

		$data['total'] = 2;
		$data['data'] = json_encode($list);
		$this->ajaxReturn($data);
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:米表管理
	 */
	public function mibiao() {
		
		if(IS_POST){
			//米表修改
			$this->assign('updateType',1);//1.修改成功 2.失败
		}
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:充值页面
	 */
	public function recharge() {
		$this->display();
	}
	
	
	##帐号管理相关
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:手机绑定
	 */
	public function bind() {
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:邮箱绑定
	 */
	public function bind_email() {
		$this->display();
	}

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:操作保护
	 */
	public function bind_protect() {
		//1.查数据库看有没有设置安全问题，没有的话进行添加操作
		// $temp = 'addmibao';
		
		//2.有相关问题的话进行验证操作
		// $temp = 'yzmibao';
	
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];

		$mibao = M('users_mibao')->where("uid = '{$uid}'")->find();
		$key = $uid.'userverified';
		$key = md5($key);
		$status = '';
		if(I('cookie.status')=='1'){
			$status = '1';
		}
		if(I('cookie.status')=='-1'){
			$status = '-1';
		}
		$this->assign('status',$status);
		cookie('status',null);
		if (!(I('cookie.CNZZDATA1258442956') == $key) && !is_null($mibao)) {

			$mibao = M('users_mibao')->where("uid = '{$uid}'")->order("rand()")->find();
			$this ->assign('mibao',$mibao);

			$this ->assign('up_status',I('post.data'));//更改密码状态和添加密保判断
			
			$temp = 'yzmibao';

		} else {
			
			$mibao1 = M('users_mibao')->where("uid = '{$uid}'")->select();
			$this ->assign('mibao',$mibao1);
			
			$temp = 'addmibao';

		}
		
		$this->display($temp);
				
	}
	

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:添加密保
	 */
	public function addmibao() {
		//处理一下添加操作，并且记录下session操作的状态
		$userinfo = $this ->userinfo;
		$q = I('post.txtques');
		$a = I('post.txtans');
		$p = I('post.txtlogpwd');
		$p = md5($p);
		$usermibao = M('users_mibao');

		$check1 = M('users')->where("password = '{$p}' ")->find();//验证密码
		$check2 = $usermibao->where("question = '{$q}' and uid = '{$userinfo['uid']}'")->find();//防止密保重复
		$check3 = $usermibao->where("uid = '{$userinfo['uid']}'")->count();//控制密保数量

		if (!is_null($check1) && is_null($check2) && $check3 < 5) {
			
			$data = array(
					
					'uid' => $userinfo['uid'],
					'question' => $q,
					'answer' => $a,
					'addtime' => time(),
					);

			M('users_mibao')->add($data);
			cookie('status','1',1800);
			
		}else{
			cookie('status','-1',1800);
		}
	
		header('location: bind/protect');
		
	}
	
	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:删除密保
	 */
	public function delmibao() {
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$id = I('post.id');
		$Mibao = M('users_mibao');
		$status = $Mibao->where("id = '{$id}' and uid = '{$uid}'")->find();
		
		if(!is_null($status)){
			$Mibao->where("id = '{$id}' and uid = '{$uid}'")->delete();
			$this ->ajaxReturn(array('data' => '1'));
		}
	}

	//验证密保操作
	public function yzmibao() {

		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];		

		$que = I('post.hid');
		$anc = I('post.txtanc');
		$pwd = I('post.txtlogpwd');
		$pwd = md5($pwd);

		$check1 = M('users')->where("uid = '{$uid}' and password = '{$pwd}'")->find();//密码验证
		$check2 = M('users_mibao')->where("uid = '{$uid}' and question = '{$que}' and answer = '{$anc}'")->find();//密保验证

		if(is_null($check1) || is_null($check2)){
			cookie('status','-1',1800);
			header('location: bind/protect');
		}else{
			
			$key = $uid.'userverified';
			cookie('CNZZDATA1258442956',md5("$key"),80);

			if (I('post.yzbd') == '1') {
				cookie('status','1',1800);
				header('location: updatepwd');
			} else {
				cookie('status','1',1800);
				header('location:bind/protect');
			}
		}
	}
	
	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:修改密码
	 */
	public function updatepwd() {
		//先到密码保护验证下安全问题
		//redirect('/account/bind_protect');
		//验证通过了进入修改页面
		// $up_value = $this->up_value;
		
		// $this->display();
		$cookie1 = I('cookie.CNZZDATA1258442956');
		if (empty($cookie1)) {
			
			jumpPost('bind/protect','1');
			
		} else {

			if(I('cookie.status')=='1'){
				$status = '1';
			}
			if(I('cookie.status')=='-1'){
				$status = '-1';
			}
			$this->assign('status',$status);
			cookie('status',null);
			$this->display();
		
		}
	}
	
	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:修改密码
	 */
	public function updatepwds() {
		$cookie2 = I('cookie.CNZZDATA1258442956');
		if (empty($cookie2)) {
			
			jumpPost('bind/protect','1');
			
		} else {
			//更改密码操作
			$userinfo = $this->userinfo;
			$uid = $userinfo['uid'];
			$pwd = md5(I('post.txtrawpw'));
			$check3 = M('users')->where("uid = '{$uid}' and password = '{$pwd}'")->find();
			$new_pwd = md5(I('post.txtnewpw'));
			$con_pwd = md5(I('post.txtnewspw'));
			
			if( ($new_pwd == $con_pwd) && !is_null($check3)){
				
				$data = array('password'=>$new_pwd);
				M('users')->where("uid = '{$uid}'")->save($data);
				
				cookie('status','1',1800);
				
			}else{
				cookie('status','-1',1800);			
			}

				header('location: updatepwd');
		}
	}

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:登录日志
	 */
	public function Loglog() {

	$this->display(); // 输出模板
	}

	
	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:解析登录日志
	 */
	public function Loglogs() {
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$User = M('users_login_log'); // 实例化User对象

		$count = $User->where("uid = $uid")->count();// 查询满足要求的总记录数
		$start_page = I('post.pageindex')-1;
		$list = $User->where("uid = $uid ")->order('intime desc')->limit($start_page.','.'10')->select();
		
		foreach ($list as $key => $value) {

			$list[$key]['intime']= date('Y-m-d H:i:s',$value['intime']);

		}
		$list = json_encode($list);
		$datas = array(
				'data' => $list,
				'total' => $count,
				);
		$this ->ajaxReturn($datas);
	
	}
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:whois查询测试
	 */
	public function whois() {
		echo $test = GetWhoisInfo('baidu.com');exit;	
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:域名批量设置 yjset（议价的批量设置） 
	 */
	public function pl_set() {
		$act = safeFilter(I('act'));
		if($act == ''){
			header("HTTP/1.1 404 Not Found");
			header('location:/404.html');
			exit;
		}
		
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$sqlwhere = "uid = {$uid} and checked = 1 ";
		
		
		$ckl = cookie('selectAll');
		preg_match ("/(.+?)\&(.*)/", $ckl, $matchs);
		//p($matchs);
		$arrID = explode(',',$matchs[2]);
		if(is_numeric($arrID[0])){
			//设置指定id的
			$ids = trim($matchs[2],',');	
		}else{
			//设置全部
			$ids = 'all';
		}
		
		$DomainGroup = M('domain_group');
		$myGroupInfo = $DomainGroup->where("uid = '{$uid}'")->select();
		$myGroupInfo2 = array();
		foreach($myGroupInfo as $k=>$v){
			$myGroupInfo2[$v['dgid']] = $v;
		}
		$array['myGroupInfo2'] = $myGroupInfo2;
		
		$Domain = M('domain');
		
		//议价设置
		if($act == 'yjset'){
			$temp = 'plset_yjset';
			$sqlwhere .= " and type = 1";
			if($ids == 'all'){
				
			}else{
				$sqlwhere .= ' and did IN ('.$ids.')';
			}
			
			$setDomain = $Domain->where($sqlwhere)->select();
			$array['setDomain'] = $setDomain;
			
		}
		//限时抢购设置
		else if($act == 'buynow'){
			$temp = 'plset_buynow';
			
			$registrar = C('Registrar');//注册商
			$array['registrar'] = $registrar;
			$sqlwhere .= " and type = 4";
			
			$setDomain = $Domain->where($sqlwhere)->select();
			$array['setDomain'] = $setDomain;
		}
		
		//p($array);exit;
		$this->assign($array);
		$this->display($temp);
	}
	
	/**
	 * @author: 
	 * @todo:Whois邮箱页面管理
	 */
	public function whoisemail() {
		
		$this->display();
	}
	
	/**
	 * @author: 
	 * @todo:第三方绑定页面管理
	 */
	public function thirdparty() {
		
		$this->display();
	}

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:修改个人资料
	 */
	public function updateprofile() {
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];

		$user = M('users');
		$info = $user->where("uid = '{$uid}'")->find();
		$this ->assign('info',$info);

		$this->display();

	}

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:修改个人资料
	 */
	public function updatefile() {
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$info = array(
				
				'umobile' => I('post.txtmobphone'),
				'telephone' => I('post.txttelphone'),
				'qq' => I('post.txtQQ'),

				);

		M('users')->where("uid = '{$uid}'")->save($info);
		// jumpPost('updateprofile','1');//跳到updateprofile,传一个状态
		header('location: updateprofile');
	}

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:修改个人资料
	 */
	public function luckymoney() {
		$this->display();
	}

	/**
	 * @author: linguojin <linguojin@juwang.com>
	 * @todo:修改个人资料
	 */
	public function get_luck(){
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$records = M('packets_record'); // 实例化records对象

		$count = $records->where("uid = $uid")->count();// 查询满足要求的总记录数
		$start_page = I('post.pageindex')-1;
		$list = $records->where("uid = $uid ")->order('addtime desc')->limit($start_page.','.'10')->select();
		
		foreach ($list as $key => $value) {

			$list[$key]['addtime']= date('Y-m-d H:i:s',$value['addtime']);

		}
		$list = json_encode($list);
		$datas = array(
				'data' => $list,
				'total' => $count,
				);
		$this ->ajaxReturn($datas);
	
	} 

	public function __call($name, $value) {
	   	header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
	   	redirect('/404.html');
   }
	
	
	###买家相关操作###
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com.cn>
	 * @todo:买家相关
	 */
	public function buynow() {
		
		$act = safeFilter(I('act'));
		$array['act'] = $act;
		if($act == ''){
			$temp = '';
		}elseif($act == 'check'){
			//$temp = 'sellnow_check';
		}elseif($act == 'wait'){
			//$temp = 'sellnow_wait';
		}elseif($act == 'end'){
			//$temp = 'sellnow_end';
		}
		
		$this->assign($array);
		$this->display();
	}
	
	//获取买家限时抢购已买数据
	public function buynowInf() {
		$act = safeFilter(I('act'));
		$page = (int) I('page');
		$pagesize = (int) I('pagesize');
		$page = $page?$page:1;
		$pagesize = $pagesize?$pagesize:20;
		
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		$Domain = M('domain');
		$Deals = M('deals');
		
		$sqlwhere = "typefrom = '4' and sellid = '{$uid}'";
		if($act == 'check'){
			$addwhere = " and (tradestatus = '-1' or tradestatus = '1')";
		}else if($act == 'wait'){
			$addwhere = " and (tradestatus = '2')";
		}else if($act == 'end'){
			$addwhere = " and (tradestatus = '3')";
		}
		$sqlwhere .= $addwhere;
		
		$dealInfos = $Deals->where($sqlwhere)->limit($pagesize)->page($page)->select();
		$return['total'] = $Deals->where($sqlwhere)->count();
		
		$data = array();
		foreach($dealInfos as $k=>$v){
			$data[$k]['domain'] = $v['domain'];
			$data[$k]['price'] = $v['price'];
			$data[$k]['buyid'] = $v['buyid'];
			$data[$k]['intime'] = $v['intime'];
			$data[$k]['indate'] = date('Y-m-d H:i:s',$v['intime']);
			$data[$k]['status'] = $v['tradestatus'];
			switch ($v['tradestatus']) {
				case '-1':
					$data[$k]['tip'] = '审核失败';
					$data[$k]['class'] = '';
					break;
				case '1':
					$data[$k]['tip'] = '待审核';
					$data[$k]['class'] = '';
					break;
				case '2':
					$data[$k]['tip'] = '待交易';
					$data[$k]['class'] = 's-75b';
					break;
				case '3':
					$data[$k]['tip'] = '完成';
					$data[$k]['class'] = 's-ff6';
					break;
			}
		}
		$return['data'] = json_encode($data,true);
		$this->ajaxReturn($return);
		
	}
}
