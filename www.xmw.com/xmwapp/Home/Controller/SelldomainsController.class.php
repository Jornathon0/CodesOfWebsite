<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:网站前台添加域名，卖域名相关模块
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */
namespace Home\Controller;
use Think\Controller;
class SelldomainsController extends Controller {
	
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
	 * @todo:开通相关页面
	 */
	public function kt() {
		$ktWhat = I('kt');
		$temp = 'kt_'.$ktWhat;
		
		$this->display($temp);
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:开通操作验证
	 */
	public function ktOperation() {
		$type = (int) I('type');
		
		$data['error'] = '';
		$data['data'] = json_encode(0);
		$this->ajaxReturn($data);
	}
	
	
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:卖域名页
	 */
    public function sellDomains(){
    	$this->display('sell-domains');
    }
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:域名分组
	 */
	public function group() {
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		$domainGroup = M('domain_group');
		
		if(IS_POST){
			$act = I('act');
			$id = (int) I('id');
			$groupName = safeFilter(I('groupName'));
			$intro = safeFilter(I('intro'));
			if($act == 'add'){
			//处理add
				$data['uid'] = $uid;
				$data['gname'] = $groupName;
				$data['remark'] = $intro;
				$domainGroup->add($data);
			}else if($act == 'update'){
			//处理upload
				$data['gname'] = $groupName;
				$data['remark'] = $intro;
				$domainGroup->where("dgid = {$id}")->save($data);
			}else if($act == 'del'){
				$domainGroup->where("dgid = {$id}")->delete();
			}
			//print_r($data);
			exit;
		}
		
		$groupsInfo = $domainGroup->where("uid = {$uid}")->select();
		
		$array['groupsInfo'] = $groupsInfo;
		$this->assign($array);
		$this->display();
	}
	
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:添加出售域名页面展示
	 */
	public function adddomains() {
		
		//我的分组
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		
		$myGroup = M('domain_group')->where("uid = {$uid}")->select();
		
		//添加域名错误列
		$FailInfo = cookie('addDomainFail');
		$FailList = json_decode($FailInfo,true);
		cookie('addDomainFail',null);
		$this->assign('FailList',$FailList);
		
		$array['myGroup'] = $myGroup;
		$this->assign($array);
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:添加出售域名成功后的提示展示页面
	 */
	public function adddomainsTip() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:添加批量交易页面展示
	 */
	public function adddomains_pl() {

		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:我的批量交易页面
	 */
	public function pldomain() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:我收到的报价页面
	 */
	public function sellnegotiation() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:极速竞价管理页面
	 */
	public function fastbid() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:一口价管理页面
	 */
	public function aprice() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:提交一口价页面
	 */
	public function aprice_add() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:一口价展示和管理页面
	 */
	public function pl_ykj() {
		
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:优质域名页面
	 */
	public function highgrade() {
		
		$act = I('act');
		$temp = 'highgrade';
		if($act == 'selling'){
			$temp = 'highgrade_selling';
		}else if($act == 'down'){
			$temp = 'highgrade_down';	
		}else if($act == 'sold'){
			$temp = 'highgrade_sold';	
		}
		$this->display($temp);
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:优质域名页数据
	 */
	public function highgradeinf() {
		#测试数据
		//$list[] = array('id'=>'1','domainName'=>'111.com','publishtime'=>'2016-01-02 12:00:45');
		//$list[] = array('id'=>'2','domainName'=>'222.com','publishtime'=>'2016-01-02 17:00:45');
		$list = '';
		$data['total'] = 0;
		$data['data'] = json_encode($list);
		$this->ajaxReturn($data);
		
	}
	
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:添加出售域名
	 */
	public function AddDomainCheck() {
		$userinfo = session('XMUserInfo');
		if($userinfo['uid'] == null){
			redirect('/login');
		}
		
		$type = (int) I('type');//0添加 1删除
		$group = (int) I('group');//域名分组
		$domains = explode("\r\n",I('domain'));
		foreach($domains as $dk=>$dv){
			$onedomaininfo = split(" +",$dv);
			if($onedomaininfo[0] != ''){
				$onedomain[$dk]['domain'] = $onedomaininfo[0];
				$onedomain[$dk]['price'] = $onedomaininfo[1]?$onedomaininfo[1]:0;
				$onedomain[$dk]['price'] = (int) $onedomain[$dk]['price'];
				$onedomain[$dk]['remark'] = $onedomaininfo[2];
			}
		}
		$Domain = M('domain');
		$domainData['uid'] = $userinfo['uid'];
		$domainData['type'] = 1;//1.普通议价 2.一口价 3.优质域名 4.限时抢购
		$domainData['dgid'] = $group;
		$domainData['intime'] = time();
		$domainData['uptime'] = $domainData['intime'];
		$domainData['checked'] = 0;
		
		$Tag = M('tag');
		$allTag = $Tag->select();
		foreach($allTag as $k=>$v){
			$tags[$v['type']][$v['typeid']]['title'] = $v['title'];	
			$tags[$v['type']][$v['typeid']]['id'] = $v['id'];
			$tags[$v['type']][$v['typeid']]['pid'] = $v['pid'];
		}
		
		//a.处理添加
		if($type == 0){
			$array['tiptext'] = '添加';
			foreach($onedomain as $k=>$v){
				$domainData['title'] = GetUrlToDomain($v['domain']);
				$checkDomain = $Domain->where("uid = '{$domainData['uid']}' and title = '{$domainData['title']}' and status = 1")->find();
				//a.1 该用户已经添加过该域名
				if($checkDomain){
					$list['fail'][] = array('domain'=>$v['domain'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'已添加过该域名，添加失败');
				}
				//a.2 该用户未添加过该域名
				else{
					if (strpos($v['domain'], 'com.cn') !== false) {
						$domainData['extend'] = 'com.cn';
					}else{
						$domainData['extend'] = end(explode('.',$v['domain']));
					}
					$allSuffix = C('SUFFIX');
					//a.2.1 该域名不在设定的域名中，域名后缀不合法
					if(!in_array($domainData['extend'],$allSuffix)){
						$list['fail'][] = array('domain'=>$v['domain'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'域名后缀不合法');
					}
					//a.2.2 添加域名
					else{
						$domainData['price'] = $v['price'];
						$domainname = str_replace('.'.$domainData['extend'],'',$domainData['title']);
						$domainData['titlelen'] = mb_strlen($domainname, 'UTF-8');
						$domainData['domainname'] = $domainname;
						$domainData['remark'] = $v['remark']?$v['remark']:'';
						
						
						$PublicFun = A('public');
   						$domainTag = $PublicFun->GetDomainTag($domainname);
						
						if($domainTag != ''){
							$domainData['tagtype'] = $domainTag['type'];
							$domainData['tagid'] = $tags[$domainTag['type']][$domainTag['typeid']]['id'];
						}
						//p($domainData);
						$re = $Domain->add($domainData);
						
						if($re){
							$list['suc'][] = array('domain'=>$domainData['title'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'添加成功');
						}else{
							$list['fail'][] = array('domain'=>$domainData['title'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'添加失败');
						}
					}
				}
			}
		}
		//b.处理删除
		else if($type == 1){
			$array['tiptext'] = '删除';
			foreach($onedomain as $k=>$v){
				$domainData['title'] = GetUrlToDomain($v['domain']);
				$checkDomain = $Domain->where("uid = '{$domainData['uid']}' and title = '{$domainData['title']}' and status = 1")->find();
				//b.1 该用户有添加过该域名可以删除
				if($checkDomain){
					$savaData['status'] = 0;
					$re = $Domain->where("did = {$checkDomain['did']}")->save($savaData);
					if($re !== false){
						$list['suc'][] = array('domain'=>$domainData['title'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'删除成功');	
					}else{
						$list['fail'][] = array('domain'=>$domainData['title'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'删除失败');
					}
				}
				//b.2 该用户未添加过该域名
				else{
					$list['fail'][] = array('domain'=>$v['domain'],'price'=>$v['price'],'remark'=>$v['remark'],'tip'=>'您还未添加过该域名');
				}
			}
		}
		
		$list['count']['fail'] = count($list['fail']);
		$list['count']['suc'] = count($list['suc']);
		
		//有错误的记录相应错误信息，便于返回重新修改提交
		if($list['count']['fail'] > 0){
			$failInfo = json_encode($list['fail']);
			cookie('addDomainFail',$failInfo,3600);
		}
		
		$array['list'] = $list;
		$this->assign($array);
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:待审核的出售域名
	 */
	public function withcheck() {
		$this->display();
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:获取待审核的出售域名列表
	 */
	public function withcheckinf() {
		/*#测试数据
		$list[] = array('id'=>'1','domainName'=>'111.com','publishtime'=>'2016-01-02 12:00:45');
		$list[] = array('id'=>'2','domainName'=>'222.com','publishtime'=>'2016-01-02 17:00:45');
		$data['total'] = 20;
		*/
		$pageindex = (int) I('pageindex');
		//$mode = I('mode')?I('mode'):'json';
		$userinfo = session('XMUserInfo');
		$Domain = M('domain');
		$checkwhere = "uid = '{$userinfo[uid]}' and checked != 1 and status = 1";
		$data['total'] = $Domain->where($checkwhere)->count();
		$domainlist = $Domain->where($checkwhere)->order('did desc')->limit(10)->page($pageindex)->select();
		//echo $Domain->getlastsql();
		$list = array();
		foreach($domainlist as $k=>$v){
			$list[$k]['id'] = $v['did'];
			$list[$k]['domainName'] = $v['title'];
			$list[$k]['publishtime'] = date('Y-m-d H:i:s',$v['intime']);
			$list[$k]['checked'] = $v['checked'];
			$list[$k]['reason'] = $v['reason'];
		}
		//p($domainlist);exit;
		$data['data'] = json_encode($list);
		$this->ajaxReturn($data);
		
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:查whois验证删除待审核的出售域名列表
	 */
	public function checkwithdomain() {
		//echo '验证域名';
		$type = I('type');
		$id = I('id');
		if($type == 0){
			//处理验证
			$checkInfo = D('domain')->docheck($id);
			//print_r($checkInfo);exit;
			$data = $checkInfo;
			if($checkInfo['status'] == 1){
				$data['tip'] = '验证成功';
				M('domain')->where("did = {$id}")->setField('checked',1);
			}else if($checkInfo['status'] == 2){
				$data['tip'] = '验证失败';
			}else if($checkInfo['status'] == 3){
				$data['tip'] = '未检测到域名邮箱';
			}
		}elseif($type == 1){
			//处理删除
			$isdel = D('domain')->dodel($id);
			if($isdel){
				//$data['data'] = true;
				$data['status'] = 1;
				$data['tip'] = '验证成功';
			}else{
				//$data['data'] = false;
				$data['status'] = 0;
				$data['tip'] = '验证失败';
			}
		}elseif($type == 2){
			//处理重新申请验证
			$isdel = D('domain')->doapply($id);
			if($isdel){
				//$data['data'] = true;
				$data['status'] = 1;
				$data['tip'] = '重新验证成功';
			}else{
				//$data['data'] = false;
				$data['status'] = 0;
				$data['tip'] = '重新验证失败';
			}
		}
		
		$this->ajaxReturn($data);
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:批量验证删除待审核的出售域名列表
	 */
	public function CheckDomain() {
		
		/*sleep(2);
		print_r(I());exit;*/
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		$checkwhere = "uid = {$uid} ";
		$type = I('type');
		$resut = array_filter(explode(',',I('resut')));
		//print_r($resut);
		if(is_numeric($resut[0])){
			//处理相应id
			$checkwhere .= " and did IN (".implode(',',$resut).")";;
		}else{
			//处理所有
			array_shift($resut);
			if(count($resut)>0){
				$checkwhere .= " and did NOT IN (".implode(',',$resut).")";;	
			}	
		}
		//echo $checkwhere;exit;
		$dids = M('domain')->field("did")->where($checkwhere)->select();
		//print_r($dids);exit;
		if($type == 0){
			//处理验证
			foreach($dids as $k=>$v){
				D('domain')->docheck($v['did']);
			}
			
			$this->ajaxReturn('check ok');
		}elseif($type == 1){
			//处理删除
			foreach($dids as $k=>$v){
				D('domain')->dodel($v['did']);
			}
			$this->ajaxReturn('del ok');
		}elseif($type == 2){
			//处理删除
			foreach($dids as $k=>$v){
				D('domain')->doapply($v['did']);
			}
			$this->ajaxReturn('apply ok');
		}
		
		
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:我的域名（已审核）
	 */
	public function allsell() {
		//分组信息
		$DomainGroup = M('domain_group');
		$userinfo = $this->userinfo;
		$uid = $userinfo['uid'];
		$myGroupInfo = $DomainGroup->where("uid = '{$uid}'")->select();
		$myGroupInfo2 = array();
		foreach($myGroupInfo as $k=>$v){
			$myGroupInfo2[$v['dgid']] = $v;
		}
		$array['myGroupInfo2'] = $myGroupInfo2;
		
		//类别
		$type = safeFilter(I('type'));
		
		if($type == ''){
		//0.所有
			$checktype = 0;
			$temp = '';
		}else if($type == 'sellnegotiation'){
		//1.议价
			$checktype = 1;
			$temp = '';
		}else if($type == 'buynow'){
		//4.限时抢购
			$checktype = 4;
			$temp = 'allsell_buynow';
		}else if($type == 'hotsell'){
		//2.一口价
			$checktype = 2;
		}else if($type == 'bargain'){
		//3.优质
			$checktype = 3;
		}
		$array['checktype'] = $checktype;
		
		//p($array);exit;
		$this->assign($array);
		$this->display($temp);
	}
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:异步获取我的域名信息
	 */
	public function allsellinf() {
		#测试数据
		#sell-old 1.极速竞价 2.一口价 3.优质域名 4.普通议价
		#sell-new 1.普通议价 2.一口价 3.优质域名 4.限时抢购
		/*$list[] = array('RowID'=>'1','label'=>'4杂','id'=>'620405','sell'=>'1','domainName'=>'884z.com','hot'=>'0','cls'=>'其它','_type'=>'c1','_money'=>'800000.0000','intro'=>'生地黄打防结合地方艰苦房贷还款放大','buyitnowid'=>null,'bargainid'=>null,'auctionid'=>null,'groupname'=>null,'groupid'=>'0');
		$list[] = array('RowID'=>'2','label'=>'三拼','id'=>'620404','sell'=>'4','domainName'=>'jiumuge.com','hot'=>'0','cls'=>'其它','_type'=>'b3','_money'=>'800000.0000','intro'=>'','buyitnowid'=>null,'bargainid'=>null,'auctionid'=>null,'groupname'=>null,'groupid'=>'0');
		$list[] = array('RowID'=>'1','label'=>'4杂','id'=>'620405','sell'=>'3','domainName'=>'884z.com','hot'=>'0','cls'=>'其它','_type'=>'c1','_money'=>'800000.0000','intro'=>'生地黄打防结合地方艰苦房贷还款放大','buyitnowid'=>null,'bargainid'=>null,'auctionid'=>null,'groupname'=>null,'groupid'=>'0');

		$data['total'] = 9;
		$data['data'] = json_encode($list);
		$this->ajaxReturn($data);exit;*/
		
		//print_r(I());
		//exit;
		//Array ( [pageindex] => 1 [type] => 0 [domainName] => [groupid] => [haveprice] => 1 )
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		$Domain = M('domain');
		$DomainGroup = M('domain_group');
		$dwhere = "uid = '{$uid}' and checked = 1 and status = 1 ";
		$pageindex = I('pageindex');
		$type = I('type');
		$domainName = I('domainName');
		$groupid = I('groupid');
		$haveprice = (int) I('haveprice');
		//类型查询    0所有   1议价   2竞价   3一口价   4优质    5、6、7议价[提交一口价、优质、竞价]
		if($type == 0){
			
		}elseif($type == 1){
			$dwhere .= " and type = '1'";
		}elseif($type == 2){
			$dwhere .= " and type = '2'";
		}elseif($type == 3){
			$dwhere .= " and type = '3'";
		}elseif($type == 4){
			$dwhere .= " and type = '4'";
		}
		//搜索域名
		if($domainName != ''){
			$dwhere .= " and title like '%".$domainName."%'";
		}
		//分组
		if($groupid != ''){
			$groupid = (int) $groupid;
			$dwhere .= " and dgid = '".$groupid."'";
		}
		//价格
		if($haveprice == 0){
			$dwhere .= "";
		}else if($haveprice == 1){
			$dwhere .= " and price > 0 ";//有标价
		}else if($haveprice == 2){
			$dwhere .= " and price = 0 ";//无标价
		}
		
		//echo $dwhere;exit;
		$checkwhere = "uid = '{$userinfo[uid]}' and checked = 1 and status = 1";
		$data['total'] = $Domain->where($dwhere)->count();
		$domainlist = $Domain->where($dwhere)->order('did desc')->limit(20)->page(I('pageindex'))->select();
		//echo $Domain->getlastsql();
		$myGroupInfo = $DomainGroup->where("uid = '{$userinfo[uid]}'")->select();
		$myGroupInfo2 = array();
		foreach($myGroupInfo as $k=>$v){
			$myGroupInfo2[$v['dgid']] = $v;
		}
		$list = array();
		foreach($domainlist as $k=>$v){
			$list[$k]['RowID'] = $k;
			$list[$k]['label'] = '';//标签，暂未查询
			$list[$k]['id'] = $v['did'];
			$list[$k]['sell'] = $v['type'];
			$list[$k]['domainName'] = $v['title'];
			$list[$k]['hot'] = $v['hot'];
			$list[$k]['cls'] = '';//其他
			$list[$k]['_type'] = $v['tagtype'].$v['tagid'];//c1
			$list[$k]['_money'] = $v['price'];
			
			$list[$k]['registrars'] = $v['registrars'];
			
			$list[$k]['intro'] = $v['remark'];
			$list[$k]['buyitnowid'] = '';
			$list[$k]['bargainid'] = '';
			$list[$k]['auctionid'] = '';
			$list[$k]['groupname'] = $myGroupInfo2[$v['dgid']]['gname'];
			$list[$k]['groupid'] = $v['dgid'];
		}
		//p($list);exit;
		$data['data'] = json_encode($list);
		$this->ajaxReturn($data);
	}
	
	
	/**
	 * @author: qiuyifeng <qiuyifeng@juwang.com>
	 * @todo:限时抢购管理
	 */
	public function sellnow() {
		
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
		$this->display($temp);
	}
	
	//获取卖家限时抢购已卖数据
	public function sellnowInf() {
		$act = safeFilter(I('act'));
		$page = (int) I('page');
		$pagesize = (int) I('pagesize');
		$page = $page?$page:1;
		$pagesize = $pagesize?$pagesize:20;
		
		$userinfo = session('XMUserInfo');
		$uid = $userinfo['uid'];
		$Domain = M('domain');
		$Deals = M('deals');
		
		$sqlwhere = "typefrom = '4' and buyid = '{$uid}' ";
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
			$data[$k]['sellid'] = $v['sellid'];
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