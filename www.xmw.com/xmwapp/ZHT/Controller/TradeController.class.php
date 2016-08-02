<?php

/**
 * XMW [ WE ARE No.1 ]
 * @author     zhengni <zhengni@juwang.com.cn>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class TradeController extends CommonController {

    public function index() { 
        $this->display();
    }
       /**
     * Author: zhengni<zhengni@juwang.com.cn>
     * Desc: 限时一口价管理列表
     */
    public function Flashsale(){
        $deals=M('deals');
        $user=M('users');
        $agent=M('agents');
        $p = I('p');//获取页码
        $tradestatus = I('status',0,'intval');
        $p = I('p');
        $keywords = I('keywords');
        if($p == '')$p = 1;
        $where="typefrom=4";
        if($tradestatus && $tradestatus<>0){
        $where.=" and tradestatus = '".$tradestatus."'";
        }
        if($keywords){
            $where .= " And ( orderid like '%".$keywords."%' or domain like '%".$keywords."%')"; 
        }
        $list=$deals->where($where)->field($field)->order('intime desc')->limit(($p-1)*$size,$size)->select(); 
        foreach ($list as $k => $v) {
            $list[$k]['buyername']=$user->where("uid={$v['buyid']}")->getField("uname");
            $list[$k]['sellername']=$user->where("uid={$v['sellid']}")->getField("uname");
            $agid=$user->where("uid={$v['buyid']}")->getField("agid");
            $list[$k]['agentname']=$agent->where("agid={$agid}")->getField("name");   
        }
        $this->data=$list;
        $this->tradestatus=$tradestatus;
        $this->keywords=$keywords;
        $this->display();
    }
       /**
     * Author: zhengni<zhengni@juwang.com.cn>
     * Desc: 异步获取数据
     */
    public function getAjax() {
        if (!IS_AJAX) {return;}     
        $deals = M('deals');
        $table = C('DB_PREFIX').'users as a';
        $step = I('post.act');
        $id = (int) I('post.id');//获取要修改的域名id
        //$hot=(int)I('post.hot');
        //$recommend=(int)I('post.recommend');
        $sql="id={$id}";
        switch ($step) {
            //审核通过：限时抢购交易
            case 'limitcheckbyID':{ 
                $check=$deals->where($sql)->setField("tradestatus", 2);
                if($check){
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');  
                break;
            }
            //审核不通过：限时抢购交易
            case 'limituncheckbyID':{ 
               $check=$deals->where($sql)->setField("tradestatus",-1);
               if($check){
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');    
                break;
            }
            // //人工验证域名
            // case 'verifydomain':
            //     $check=$domain->where($sql)->setField("checked", 1);
            //     if($check){
            //        // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
            //         //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
            //         $str = 'ok';
            //     }
            //     else{
            //         $str = 'fail';
            //     }
            //     $this->ajaxReturn($str,'json');    
            //     break;
            // case 'deletedomain':
            //     $check=$domain->where($sql)->setField("status", 0);
            //     if($check){
            //         $str = 'ok';
            //     }
            //     else{
            //         $str = 'fail';
            //     }
            //     $this->ajaxReturn($str,'json');  
            //     break;
            // //修改推荐状态
            // case 'set_rec':
            // $value=(int)I('post.value');
            // $check=$domain->where($sql)->setField("recstatus", $value);
            // if($check){
            //        // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
            //         //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
            //         $str = 'ok';
            // }
            // else{
            //         $str = 'fail';
            // }
            //     $this->ajaxReturn($str,'json');   
            //     break;
           
        }//switch
   } 
}
