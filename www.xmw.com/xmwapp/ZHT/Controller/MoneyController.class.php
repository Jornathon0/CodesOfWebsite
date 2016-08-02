<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class MoneyController extends CommonController {

    /*
     * Author: yutanghua <yutanghua@juwang.com>
     * 充值记录页面 
     */
    public function index() {
        $keywords = I('keywords');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $this->p = $p;
        $where = "income.id > 1 and user.uid > 0";
        //搜索关键词  这里只有手机号码
        if($keywords){
            $where .= " and (user.email like '%".$keywords."%' or user.mobile like '%".$keywords."%' or user.uname like '%".$keywords."%')";
        }
        //搜索类型
        $clickstatus = I('clickstatus',0,'intval');
        $this->clickstatus = $clickstatus;
        if($clickstatus == 0){
            $where .= ' and income.flag = 2';
        }else if($clickstatus == 1){
            $where .= ' and income.flag = 2 and income.itype = 0 ';
        }else if($clickstatus == 2){
            $where .= ' and income.flag = 2 and income.itype = 1 ';
        }else if($clickstatus == 3){
            $where .= ' and income.flag = 1';
        }
        //时间搜索
        $starttime = I('start');
        $endtime = I('end');
        if($starttime && $endtime && $starttime <= $endtime){
            $where .= ' and income.update_time >= '.(strtotime($starttime)).' and income.update_time <='.(strtotime($endtime)+24*60*60);
        }
        $this->starttime = $starttime;
        $this->endtime   = $endtime;
        //排序方式
        $orderby = ''; 
        $ordertype  = I('ordertype',1,'intval');
        $ordervalue = I('ordervalue',0,'intval');  
        $this->ordertype  = $ordertype;
        $this->ordervalue = $ordervalue;
        if($ordertype == 1){
            //入库时间
            if($ordervalue == 0){
                $orderby = "income.update_time desc";
            }else{
                $orderby = "income.update_time asc";
            }
        }else if($ordertype == 2){
            //充值金额
            if($ordervalue == 0){
                $orderby = "income.money desc";
            }else{
                $orderby = "income.money asc";
            }
        }else if($ordertype == 3){
            //充值金额
            if($ordervalue == 0){
                $orderby = "income.id desc";
            }else{
                $orderby = "income.id asc";
            }
        } 
        $table = C('DB_PREFIX')."income as income";
        $joinUser = C('DB_PREFIX')."users as user on income.user_id = user.uid"; 
        $field = "income.*,user.uname,user.email,user.mobile";
        $count = M('income')->table($table)->where($where)->join($joinUser,'LEFT')->count();  
        $list =  M('income')->table($table)->where($where)->join($joinUser,'LEFT')->order($orderby)->limit(($p-1)*$size,$size)->field($field)->select();
        $this->totalmoney = M('income')->table($table)->where($where)->join($joinUser,'LEFT')->sum('income.money');
        $this->page = getPageHtmlPOST($p,$count,$size);
        $this->data = $list; 
        $this->keywords = $keywords;
        $this->display('index');
    }
    
    public function indexunline(){
        $keywords = I('keywords');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $this->p = $p;
        $where = "income.id > 1";
        //搜索关键词  这里只有手机号码
        if($keywords){
            $where .= " and (user.mobile like '%".$keywords."%' or admin.username  like '%".$keywords."%')";
        }
        $where .= ' and income.flag = 2 and income.itype = 1 ';
        //时间搜索
        $starttime = I('start');
        $endtime = I('end');
        if($starttime && $endtime && $starttime <= $endtime){
            $where .= ' and income.update_time >= '.(strtotime($starttime)).' and income.update_time <='.(strtotime($endtime)+24*60*60);
        }
        $this->starttime = $starttime;
        $this->endtime   = $endtime;
        //排序方式
        $orderby = ''; 
        $ordertype  = I('ordertype',1,'intval');
        $ordervalue = I('ordervalue',0,'intval');  
        $this->ordertype  = $ordertype;
        $this->ordervalue = $ordervalue;
        if($ordertype == 1){
            //入库时间
            if($ordervalue == 0){
                $orderby = "income.update_time desc";
            }else{
                $orderby = "income.update_time asc";
            }
        }else if($ordertype == 2){
            //充值金额
            if($ordervalue == 0){
                $orderby = "income.money desc";
            }else{
                $orderby = "income.money asc";
            }
        }else if($ordertype == 3){
            //充值金额
            if($ordervalue == 0){
                $orderby = "income.id desc";
            }else{
                $orderby = "income.id asc";
            }
        } 
        $table = C('DB_PREFIX')."income as income";
        $joinUser = C('DB_PREFIX')."user as user on income.user_id = user.uid"; 
        $joinadmin = C('DB_PREFIX')."admin as admin on admin.aid = income.adminid"; 
        $field = "income.*,user.mobile,admin.username";
        $count = M('income')->table($table)->where($where)->join($joinUser,'LEFT')->join($joinadmin,'LEFT')->count();  
        $list =  M('income')->table($table)->where($where)->join($joinUser,'LEFT')->join($joinadmin,'LEFT')->order($orderby)->limit(($p-1)*$size,$size)->field($field)->select(); 
        $this->totalmoney = M('income')->table($table)->where($where)->join($joinUser,'LEFT')->join($joinadmin,'LEFT')->sum('money');
        $this->page = getPageHtmlPOST($p,$count,$size);
        $this->data = $list; 
        $this->keywords = $keywords;
        $this->display();
    }
    
    /*
     * Author: yutanghua
     * 提现记录页面  个人提现 
     */
    public function withdraw(){
        $keywords = I('keywords');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $this->p = $p;
        $where = "withdraw.id > 0";
        if($keywords){
            $where .= " and user.mobile like '%".$keywords."%'";
        }
        //搜索类型
        $clickstatus = I('clickstatus',0,'intval');
        $this->clickstatus = $clickstatus;
        if($clickstatus != 0){ 
            $where .= ' and withdraw.flag = '.$clickstatus;
        } 
        //时间搜索
        $starttime = I('start');
        $endtime = I('end');
        if($starttime && $endtime && $starttime <= $endtime){
            $where .= ' and withdraw.update_time >= '.(strtotime($starttime)).' and withdraw.update_time <='.(strtotime($endtime)+24*60*60);
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
                $orderby = "withdraw.add_time desc";
            }else{
                $orderby = "withdraw.add_time asc";
            }
        }else if($ordertype == 2){
            //更新时间
            if($ordervalue == 0){
                $orderby = "withdraw.update_time desc";
            }else{
                $orderby = "withdraw.update_time asc";
            }
        }else if($ordertype == 3){
            //充值金额
            if($ordervalue == 0){
                $orderby = "withdraw.money desc";
            }else{
                $orderby = "withdraw.money asc";
            }
        } else if($ordertype == 4){
            //id
            if($ordervalue == 0){
                $orderby = "withdraw.id desc";
            }else{
                $orderby = "withdraw.id asc";
            }
        }  else if($ordertype == 5){
            //id
            if($ordervalue == 0){
                $orderby = "withdraw.total desc";
            }else{
                $orderby = "withdraw.total asc";
            }
        } 
        
        $this->starttime = $starttime;
        $this->endtime   = $endtime;    
        $table = C('DB_PREFIX')."withdraw as withdraw";
        $joinUser = C('DB_PREFIX')."users as user on withdraw.user_id = user.uid"; 
        $joinBank = C('DB_PREFIX')."user_bankcard as bankcard on withdraw.bank_id = bankcard.id"; 
        $field = "withdraw.*,user.mobile,user.uname,user.email,bankcard.bankcard,bankcard.openingbank";
        $count = M('withdraw')->table($table)->where($where)->join($joinUser,'LEFT')->count();  
        $list =  M('withdraw')->table($table)->where($where)->join($joinUser,'LEFT')->join($joinBank,'LEFT')->order($orderby)->limit(($p-1)*$size,$size)->field($field)->select(); 
        $this->totalmoney = M('withdraw')->table($table)->where($where)->join($joinUser,'LEFT')->join($joinBank,'LEFT')->sum('withdraw.money');
        $this->totalfee   = M('withdraw')->table($table)->where($where)->join($joinUser,'LEFT')->join($joinBank,'LEFT')->sum('taxfee');
        $this->page = getPageHtmlPOST($p,$count,$size);
        $this->data = $list;
        $this->keywords = $keywords;
        $this->display();
    }
    /*
     * Author: yutanghua
     * 产品支出记录页面 
     */
    public function productexpend(){
        $keywords = I('keywords');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $where = "id > 0";
        if($keywords){
            $where .= " and user.truename like '%".$keywords."%'";
        }
        $url = '/money/productexpend.html';
        $table = C('DB_PREFIX')."user_balance_change_log as log";
        $joinUser = C('DB_PREFIX')."user as user on log.uid = user.uid"; 
        $field = "log.*,user.truename";
        $count = M('user_balance_change_log')->table($table)->where($where)->join($joinUser,'LEFT')->count();  
        $list =  M('user_balance_change_log')->table($table)->where($where)->join($joinUser,'LEFT')->order("log.dateline desc")->limit(($p-1)*$size,$size)->field($field)->select(); 
        $this->page = getPageHtml($p,$count,$url,$size,$keywords);
        $this->data = $list; 
        $this->keywords = $keywords;
        $this->display();
    }
    
    //线下充值
    public function income(){ 
        $this->banks = M('bank')->select();
        $this->display();
    }
    
    
    public function incomeHandle(){
        if(!IS_POST) return;
        $uname = I('uname');
        $userinfo = M('users')->where("mobile = '$uname' or email='$uname'")->find();
        if(!$userinfo){
            $this->error('没有这个用户！');
        }
        if($userinfo['type'] == 2){
            $this->error('个人用户不能充值！');
        }
        $money = I('money');
        if(!is_numeric($money)){
            $this->error('充值金额格式不对！');
        }
        $bank_id = I('bank_id');
        $UserInfo = session('XZHTUserInfo');
        $adminid = $UserInfo['aid'];
        $admininfo = M('admin')->where('aid = '.$adminid)->find();
        if(!$admininfo){
            $this->error('充值管理员不存在！');
        }
        $img = $this->_uploadimg($_FILES['img']);
        $incomedata = array(
            'user_id'     =>  $userinfo['uid'],
            'bank_id'     =>  $bank_id,
            'money'       =>  $money,
            'flag'        =>   2,
            'add_time'    =>  time(),
            'update_time' => time(),
            'itype'       => 1,
            'img'         => $img,
            'adminid'     => $adminid);
        $res = D('Income')->addIncome($incomedata);
        if($res){
            $this->success('充值成功','/money/income.html');
        }else{
            $this->error('充值失败！');
        }
        
    }
            
    /**
     * Author: yutanghua <yutanghua@juwang.com>
     * Desc: 异步获取数据
     */
    public function getAjax() {
        if (!IS_AJAX) {return;}     
        $step = I('post.act');
        switch ($step) {
            case 'getuserinfobymobile':{
                $mobile = I('mobile');
                $userinfo = M('users')->where("mobile = '".$mobile."' or email='".$mobile."'")->find();
                $res = array();
                if(!$userinfo){
                    $res['status'] = 1;
                    $res['tips']   = '没有这个用户';
                }else if($userinfo['type'] == 2){
                    $res['status'] = 2;
                    $res['tips']   = '个人用户不能充值';
                }else{
                    $res['status'] = 0;
                    $res['tips']   = $userinfo['company_name'];
                }
                $this->ajaxReturn($res,'JSON');
                break;
            }
        }//switch
   } 
   
   //上传图片方法
    private function _uploadimg($file) {
        $upload = new \Think\Upload(); // 实例化上传类    
        $upload->maxSize = 3145728; // 设置附件上传大小    
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型    
        $upload->savePath = '/Pic/unlineincome/'; // 设置附件上传目录    // 上传单个文件     
        $info = $upload->uploadOne($file);
        if (!$info) {// 上传错误提示错误信息        
            $this->error($upload->getError());
        } else {
            // 上传成功 获取上传文件信息
            return '/Uploads'.$info['savepath'] . $info['savename'];
        }
    }
}