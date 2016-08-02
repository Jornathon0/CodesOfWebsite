<?php
/**
 * @author: qiuyifeng <qiuyifeng@juwang.com.cn>
 * @todo:淘域名-限时抢购等
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @refer url：  http://auction.ename.com/tao/buynow
 */
namespace Home\Controller;
use Think\Controller;
class TaoController extends Controller {
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:限时抢购初始页面
	 */
    public function buynow(){
		
		//限时抢购的时间
		$Setting = M('setting');
		$startHour = $Setting->where("typeid = 1 and `key` = 'starttime'")->getField('value');
		$endHour = $Setting->where("typeid = 1 and `key` = 'endtime'")->getField('value');
		$startTime = strtotime($startHour);
		$endTime = strtotime($endHour);
		$nowTime = time();
		if($nowTime >= $startTime && $nowTime <= $endTime){
			$temp = '';
		}else{
			$temp = 'buynow_wait';
		}
		$array['startTime'] = $startTime;
		$array['endTime'] = $endTime;
		//距离开抢时间
		if($nowTime > $endTime){
			//超过抢购时间
			$kqTime = $startTime+24*3600;
		}
		if($nowTime < $startTime){
			//超过抢购时间
			$kqTime = $startTime;
		}
		$array['kqTime'] = $kqTime;
		$array['nowTime'] = $nowTime;
		
		//定义分页数
		$array['pagesize'] = 10;
		
		//类型 a.数字 b.字母 c.杂
		$Tag = M('tag');
		$lx['a'] = $Tag->where("type = 'a' and typeid > 0 ")->order("id asc")->select();
		$lx['b'] = $Tag->where("type = 'b' and typeid > 0 ")->order("id asc")->select();
		$lx['c'] = $Tag->where("type = 'c' and typeid > 0 ")->order("id asc")->select();
		$array['lx'] = $lx;
		
		//抢购动态
		$Deals = M('deals');
		$dtdeals = $Deals->where("typefrom = '4'")->order('id desc')->limit(5)->select();
		$array['dtdeals'] = $dtdeals;
		
		//p($array);exit;
		$this->assign($array);
    	$this->display($temp);
    }
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:处理用户抢购
	 */
    public function userbuy(){
		if(IS_POST){
			$userinfo = session('XMUserInfo');
			$uid = $userinfo['uid'];
			$id = (int) I('id');
			if($uid > 0){
				$Deals = M('deals');
				$Domain = M('domain');
				$basicWhere = "status = 1 and checked = 1 and type = '4'";
				$theDomain = $Domain->where($basicWhere." and did = '{$id}'")->find();
				//1.该域名已经有订单状态信息了
				if($theDomain['dealsta'] >=0 ){
					$re['status'] = 3;
					$re['tip'] = '该域名已被抢购';
				}
				//2.处理正常抢购
				else{
					$dealData['typefrom'] = '4';
					$dealData['fromid'] = $id;
					$dealData['domain'] = $theDomain['title'];
					$dealData['price'] = $theDomain['price'];
					$dealData['buyid'] = $uid;
					$dealData['sellid'] = $theDomain['uid'];
					$dealData['status'] = '2';
					$theTime = time();
					$dealData['intime'] = $theTime;
					$dealData['updatetime'] = $theTime;
					//$dealData['orderid'] = '';//暂未写
					$dealData['tradestatus'] = '1';//交易状态（1-初审未过、2-审核通过，等待交易、3-交易完成、-1-审核不通过）
					
					if($dealData['buyid'] == $dealData['sellid']){
						$re['status'] = 4;
						$re['tip'] = '请不要抢购自己的域名';
					}else{
						$s = $Deals->add($dealData);
						$Domain->where("did = '{$id}'")->setField('dealsta',1);//给该域名一个抢购状态
						if($s){
							$re['status'] = 1;
							$re['tip'] = '抢购成功';
						}else{
							$re['status'] = 2;
							$re['tip'] = '异常';
						}
					}
					
					
				}
			}else{
				$re['status'] = 0;
				$re['tip'] = '请先登录';	
			}
			$this->ajaxReturn($re);
		}
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:获取限时抢购数据
	 */
	private function buynowData($arr){
		
		$Domain = M('domain');
		
		//检索条件
		$sqlwhere = "type = '4' and checked = 1 and status = 1";
		$sqlwhere = $arr['where']?$sqlwhere." and ".$arr['where']:$sqlwhere;
		//分页限制
		$page = $arr['page']?$arr['page']:'1';
		$pagesize = $arr['pagesize']?$arr['pagesize']:'20';
		//排序
		$sqlorder = $arr['order']?$arr['order']:'did desc';
		
		$total = $Domain->where($sqlwhere)->count();
		$datalist = $Domain->where($sqlwhere)->order($sqlorder)->limit($pagesize)->page($page)->select();
		//echo $Domain->getlastsql();
		
		$return['total'] = $total;
		$return['datalist'] = $datalist;
		
		//p($return);exit;
    	return $return;
    }
	
	//异步获取限时抢购列表
	public function buynowlist(){
    	/*
		print_r($_POST);
		input_val_one: //域名关键字
		input_val_two: //排除关键字
		select_one:0 //域名关键字选择 0.包含 1.开始 2.结束
		select_two:0  //排除关键字选择 0.包含 1.开始 2.结束
		ym_length_one: //域名长度范围1
		ym_length_two: //域名长度范围2
		earge_parse_one: //域名价格范围1
		earge_parse_two:  //域名价格范围2
		hznum: 域名后缀num 
		oper: //注册商（原来是主题的）
		order:5 //5.时间倒序，4.时间正序，3.价格倒序，2.价格正序，1.长度倒序，0.长度正序，
		pageIndex:1
		type:0
		
		style_ym: //域名类型 
		*/
		if(IS_POST){
			
			//1.处理页码
			$pagesize = (int) I('pagesize');
			$page = (int) I('pageIndex');
			$arr['pagesize'] = $pagesize?$pagesize:20;
			$arr['page'] = $page?$page:1;
			
			//2.处理where
			$addwhere = '1=1';
			//2.1 关键字搜索
			$word = safeFilter(I('input_val_one'));
			$wordslc = (int) I('select_one');
			if($word != ''){
				if($wordslc == 0){
					$addwhere .= ' and domainname like "%'.$word.'%"';
				}elseif($wordslc == 1){
					$addwhere .= ' and domainname like "'.$word.'%"';
				}elseif($wordslc == 2){
					$addwhere .= ' and domainname like "%'.$word.'"';
				}
			}
			//2.2 排除关键字搜索
			$noword = safeFilter(I('input_val_two'));
			$nowordslc = (int) I('select_two');
			if($noword != ''){
				if($nowordslc == 0){
					$addwhere .= ' and domainname not like "%'.$noword.'%"';
				}elseif($nowordslc == 1){
					$addwhere .= ' and domainname not like "'.$noword.'%"';
				}elseif($nowordslc == 2){
					$addwhere .= ' and domainname not like "%'.$noword.'"';
				}
			}
			//2.3 域名后缀
			$hznum = safeFilter(I('hznum'));
			if($hznum != ''){
				$arrHz = explode(',',$hznum);
				foreach($arrHz as $k=>$v){
					$hzwhere .= $hzwhere?" or extend = '{$v}'":" extend = '{$v}'";
				}
				$addwhere .= ' and ('.$hzwhere.')';
			}
			//2.4 域名长度
			$ym_length_one = (int) I('ym_length_one');
			$ym_length_two = (int) I('ym_length_two');
			
			if($ym_length_one > 0){
				$lengthWhere .= $lengthWhere?" and titlelen >= '{$ym_length_one}'":" titlelen >= '{$ym_length_one}'";
			}
			if($ym_length_two > 0){
				$lengthWhere .= $lengthWhere?" and titlelen <= '{$ym_length_two}'":" titlelen <= '{$ym_length_two}'";
			}
			if($lengthWhere != ''){
				$addwhere .= ' and ('.$lengthWhere.')';
			}
			//2.5 域名价格
			$earge_parse_one = (int) I('earge_parse_one');
			$earge_parse_two = (int) I('earge_parse_two');
			
			if($earge_parse_one > 0){
				$priceWhere .= $priceWhere?" and price >= '{$earge_parse_one}'":" price >= '{$earge_parse_one}'";
			}
			if($earge_parse_two > 0){
				$priceWhere .= $priceWhere?" and price <= '{$earge_parse_two}'":" price <= '{$earge_parse_two}'";
			}
			if($priceWhere != ''){
				$addwhere .= ' and ('.$priceWhere.')';
			}
			//2.6 域名类型
			$style_ym = safeFilter(I('style_ym'));
			if($style_ym != ''){
				$arrSty = explode(',',$style_ym);
				foreach($arrSty as $k=>$v){
					$tags[$k]['tagtype'] = 	substr($v, 0, 1);
					$tags[$k]['tagid'] = substr($v, 1);
				}
				$tagwhere = '';
				foreach($tags as $k=>$v){
					$onetagwhere = "";
					if($v['tagtype'] != ''){
						$onetagwhere .= $onetagwhere?" and tagtype = '{$v['tagtype']}'":" tagtype = '{$v['tagtype']}'";	
					}
					if($v['tagid'] != ''){
						$onetagwhere .= $onetagwhere?" and tagid = '{$v['tagid']}'":" tagtype = '{$v['tagid']}'";	
					}
					if($onetagwhere != ''){
						$tagwhere .= $tagwhere?' or ('.$onetagwhere.')':' ('.$onetagwhere.') ';
					}
				}
				$addwhere .= ' and ('.$tagwhere.')';
			}
			
			//2.7 注册商
			$zcs = safeFilter(I('oper'));
			if($zcs != ''){
				$arrZcs = explode(',',$zcs);
				foreach($arrZcs as $k=>$v){
					$zcswhere .= $zcswhere?$zcswhere." or registrars = '{$v}'":" registrars = '{$v}'";
				}
				$addwhere .= ' and ('.$zcswhere.')';
			}
			
			
			$arr['where'] = $addwhere;
			//echo  $arr['where'];exit;
			//p($_POST);exit;
			
			//order:5 //5.时间倒序，4.时间正序，3.价格倒序，2.价格正序，1.长度倒序，0.长度正序，
			//3.处理order
			$order = (int) I('order');
			if($order == 7){
				$arr['order'] = 'dealsta desc';
			}elseif($order == 6){
				$arr['order'] = 'dealsta asc';
			}elseif($order == 5){
				$arr['order'] = 'uptime desc';
			}elseif($order == 4){
				$arr['order'] = 'uptime asc';
			}elseif($order == 3){
				$arr['order'] = 'price desc';
			}elseif($order == 2){
				$arr['order'] = 'price asc';
			}elseif($order == 1){
				$arr['order'] = 'titlelen desc';
			}elseif($order == 0){
				$arr['order'] = 'titlelen asc';
			}
			
			$buynowData = $this->buynowData($arr);
			//$return['datalist'] = $buynowData['datalist'];
			$return['total'] = $buynowData['total'];
			
			foreach($buynowData['datalist'] as $k=>$v){
				$buyInfo[$k]['did'] = $v['did'];
				$buyInfo[$k]['title'] = $v['title'];
				$buyInfo[$k]['remark'] = $v['remark'];
				$buyInfo[$k]['registrars'] = $v['registrars']?$v['registrars']:'其他注册商';
				$buyInfo[$k]['price'] = $v['price'];
				$buyInfo[$k]['intime'] = $v['intime'];
				$buyInfo[$k]['uptime'] = $v['uptime'];
				$buyInfo[$k]['dealsta'] = $v['dealsta'];
			}
			
			$return['data'] = json_encode($buyInfo,true);
			$this->ajaxReturn($return);
		}
		
		
    }
	
}