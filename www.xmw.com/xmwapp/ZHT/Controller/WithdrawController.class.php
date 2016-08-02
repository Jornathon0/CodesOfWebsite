<?php

namespace ZHT\Controller;

use Think\Controller;

class WithdrawController extends Controller { 
    /*
     * Author: yutanghua
     * type  1： 提现申请列表   2： 确认转账列表   
     */
    public function index(){
        $keywords = I('keywords'); 
        $type = I('type');
        if(!$type){
            $type = 1;
        }
        $this->type = $type;
        $p = I('p');
        $size = 20; 
        if($p == '')    $p = 1; 
        $this->p = $p;
        if($type == 1)
           $where .= " a.flag = 1 and a.status = 1";
        else
           $where .= " a.flag = 2 and a.status = 1";
        if($keywords){
            $where .= " and (b.mobile like '%".$keywords."%'  or b.uname like '%".$keywords."%'  or b.email like '%".$keywords."%' or c.name like '%".$keywords."%')";
        }
        //时间搜索
        $starttime = I('start');
        $endtime = I('end');
        $this->starttime = $starttime;
        $this->endtime   = $endtime;
        //类型搜索
        $clickstatus = I('clickstatus',0,'intval');
        $this->clickstatus = $clickstatus;
        $today = date('Y-m-d',time());
        $todaystart = strtotime($today);
        if($clickstatus == 3){
            //搜索今天的
            $where .= " and a.add_time > ".$todaystart;
        }else if($clickstatus == 2){
            //搜索昨天
            $last1DayStart = $todaystart - 24*60*60;
            $last1Dayend   = $todaystart - 1;
            $where .= " and a.add_time > ".$last1DayStart;
            $where .= " and a.add_time < ".$last1Dayend;
        }else if($clickstatus == 1){
            //搜索前天
            $last2DayStart = $todaystart - 24*60*60 - 24*60*60;
            $last2Dayend   = $todaystart - 24*60*60 - 1; 
            $where .= " and a.add_time > ".$last2DayStart;
            $where .= " and a.add_time < ".$last2Dayend;
        }else{
            //时间搜索
            if($starttime && $endtime && $starttime <= $endtime){
                $where .= ' and a.add_time >= '.(strtotime($starttime)).' and a.add_time <='.(strtotime($endtime)+24*60*60);
            }
        } 
        //排序方式
        $orderby = ''; 
        $ordertype  = I('ordertype',1,'intval');
        $ordervalue = I('ordervalue',0,'intval');  
        $this->ordertype  = $ordertype;
        $this->ordervalue = $ordervalue;
        if($ordertype == 1){
            //入库时间
            if($ordervalue == 0){
                $orderby = "a.add_time desc";
            }else{
                $orderby = "a.add_time asc";
            }
        }else if($ordertype == 2){
            //提现金额
            if($ordervalue == 0){
                $orderby = "a.money desc";
            }else{
                $orderby = "a.money asc";
            }
        }else if($ordertype == 3){
            //收款银行
            if($ordervalue == 0){
                $orderby = "c.name desc";
            }else{
                $orderby = "c.name asc";
            }
        } else if($ordertype == 4){
            //id
            if($ordervalue == 0){
                $orderby = "a.id desc";
            }else{
                $orderby = "a.id asc";
            }
        } 
            
        //类型搜索 
        $table = C('DB_PREFIX').'withdraw as a';
        $join1 = C('DB_PREFIX').'users as b on b.uid = a.user_id';
        $join2 = C('DB_PREFIX').'bank as c on c.id  = bankcard.bankid';
        $join3 = C('DB_PREFIX').'user_bankcard as bankcard on bankcard.id  = a.bank_id';
        $field = 'a.*,b.email,b.uname,b.mobile,bankcard.skname,bankcard.bankcard,bankcard.openingbank,c.name as bankname';
        $count = M('withdraw')->table($table)->join($join1,'LEFT')->join($join3,'LEFT')->join($join2,'LEFT')->where($where)->count(); 
        $list =  M('withdraw')->table($table)->join($join1,'LEFT')->join($join3,'LEFT')->join($join2,'LEFT')->where($where)->field($field)->order($orderby)->limit(($p-1)*$size,$size)->select(); 
        $this->totalmoney = M('withdraw')->table($table)->join($join1,'LEFT')->join($join3,'LEFT')->join($join2,'LEFT')->where($where)->sum('a.money'); 
        //分页显示
        $this->page = getPageHtmlPOST($p,$count,$size);
        $this->dataList=$list; 
        $this->keywords = $keywords;
        $this->type  = $type;
        $this->display();
    }  
    
    //详情处理页面
    public function dowith(){
        $id = I('id');
        $table = C('DB_PREFIX').'withdraw as a';
        $join1 = C('DB_PREFIX').'user_bankcard as b on b.id = a.bank_id';
        $join2 = C('DB_PREFIX').'bank as c on c.id  = b.bankid';
        $field = 'a.*,b.skname,b.openingbank,b.bankcard,c.name as bankname';
        $this->data = M('withdraw')->table($table)->join($join1,'LEFT')->join($join2,'LEFT')->where("a.id = $id")->field($field)->find();
        $this->type = I('type');
        $this->display();
    }
    
    //提现处理 
    public function doWithdraw() {
        $withdraw_id = I('request.withdraw_id'); // 提现id
        $flag = false;
        $withdraw = D('withdraw');
        $withdrawInfo = $withdraw->getWithdrawById($withdraw_id);
        if ($withdrawInfo && in_array($withdrawInfo['flag'],array('1'))&& $withdrawInfo['status']=='1') {
            $UserInfo = session('XZHTUserInfo');
            //状态修改为2提现处理中
            $flag=  $withdraw->uWithdrayFlag($withdrawInfo['id'], $UserInfo['aid'],2);
        }
        if ($flag) {
            redirect('/Withdraw/index.html');
        } else {
            echo '处理失败';
        }
    }
    
     
    
    //提现确认
    public function confirm() {
        $withdraw_id = I('request.withdraw_id'); // 提现id
        $flag = false;
        $withdraw = D('withdraw');
        $withdrawInfo = $withdraw->getWithdrawById($withdraw_id);
        if ($withdrawInfo && in_array($withdrawInfo['flag'],array('2'))&& $withdrawInfo['status']=='1') {
            $UserInfo = session('XZHTUserInfo');
            $flag=  $withdraw->uWithdrayFlag($withdrawInfo['id'], $UserInfo['aid'],3);
        } 
        if ($flag) {
            $this->success('/Withdraw/index.html',2);
        } else {
            echo '确认失败';
        }
    }
    
    /**
     * Author: king <linzujin@juwang.com>
     * Desc: 异步获取数据   
     */
    public function getAjax() { 
        if (!IS_AJAX) {return;}
        $step = I('post.act');
        $id = (int) I('post.id');
        switch ($step) {
            //1：  支付处理
            case 'WithdrawdowithByid':{ 
                $flag = false;
                $str = 'fail';
                $withdraw = D('withdraw');
                $withdrawInfo = $withdraw->getWithdrawById($id);
                $uid =  $withdrawInfo['user_id'];
                $userInfo = M('user')->where("uid = $uid")->find();
                if ($withdrawInfo && $userInfo['status'] == 1 && in_array($withdrawInfo['flag'],array('1'))&& $withdrawInfo['status']=='1') {
                    $UserInfo = session('XZHTUserInfo');
                    //状态修改为2提现处理中
                    $flag=  $withdraw->uWithdrayFlag($withdrawInfo['id'], $UserInfo['aid'],2);
                }  
                if($flag){
                    $str = 'ok';
                    D('Adminaction')->actionLog('提现申请 更改状态为：提现处理中   申请ID：'.$id);
                } 
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //2：  确定转账
            case 'WithdrawpayByid':{ 
                $flag = false;
                $str = 'fail';
                $withdraw = D('withdraw');
                $withdrawInfo = $withdraw->getWithdrawById($id);
                $uid =  $withdrawInfo['user_id'];
                $userInfo = M('users')->where("uid = $uid")->find();
                if ($withdrawInfo && $userInfo['status'] == 1  && $withdrawInfo['status']=='1') {
   
                    $UserInfo = session('XZHTUserInfo');
                    //状态修改为2提现处理中
                    $flag=  $withdraw->uWithdrayFlag($withdrawInfo['id'], $UserInfo['aid'],3);
                }  
                if($flag){
                    $str = 'ok';
                    D('Adminaction')->actionLog('提现申请 更改状态为：提现成功   申请ID：'.$id);
                } 
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //3：  确定退款
            case 'WithdrawbackByid':{ 
                // todo , 未处理情况 -> 非正常状态
                $withdraw = D('withdraw');
                $withdrawInfo = $withdraw->getWithdrawById($id);
                $flag = false;
                $str = 'fail';
                if ($withdrawInfo && isset($withdrawInfo['status']) &&isset($withdrawInfo['flag'])&& $withdrawInfo['status'] == '1'&& in_array($withdrawInfo['flag'], array('1','2'))) {
                    $refund = D('refund');
                    $UserInfo = session('XZHTUserInfo');
                    $flag = $refund->iRefundWithdraw($withdrawInfo,$UserInfo['aid']);
                } 
                if ($flag) {
                    $str = 'ok';
                    D('Adminaction')->actionLog('提现申请 更改状态为：退款   申请ID：'.$id);
                }  
                $this->ajaxReturn($str,'json'); 
                break;
            }
        }
    }
    
}
