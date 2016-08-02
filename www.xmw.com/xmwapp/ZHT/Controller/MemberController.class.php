<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class MemberController extends CommonController { 
    /*
     * Author: zhengni
     * 会员页面 
     * $type  1:列表显示  2：搜索显示 
     */
    public function index() {
        $users=M('users');
        $table = C('DB_PREFIX').'users as a';
        $join = C('DB_PREFIX')."agents as b on b.agid = a.agid";
        $field="a.*,b.name as agentname"; 
        $p = I('p');
        $service= I('service',0,'intval');
        $keywords= I('keywords');
        //var_dump($keywords);exit; 
        $size = 20; 
        if($p == '')
            $p = 1;
        $where = "a.status > -2";
        if($service){
            $where .= ' and a.service = '.$service;
        }
        if($keywords){
            $where .= " And ( a.uname like '%".$keywords."%' or b.name like '%".$keywords."%' or a.mobile like '%".$keywords."%' or a.email like '%".$keywords."%' )"; 
        }
        $list =$users->table($table)->join($join,"LEFT")->join($join2,"LEFT")->where($where)->field($field)->limit(($p-1)*$size,$size)->select(); 
        $count=$users->table($table)->where($where)->count(); 
        $where1 = "status > -2 and TO_DAYS( FROM_UNIXTIME(addtime, '%Y%m%d'))=TO_DAYS(NOW())";
        $todayCount = $users->where($where1)->count();
        $where2 = "status > -2 and TO_DAYS(NOW()) - TO_DAYS( FROM_UNIXTIME(addtime, '%Y%m%d')) = 1";
        $yestodayCount = $users->where($where2)->count();
        $this->todayCount = $todayCount;
        $this->yestodayCount = $yestodayCount;
        $this->data = $list; 
        $this->service = $service; 
        $this->page = getPageHtml($p,$count,$size);
        $this->display('index');
    }
    public function bank(){
        //搜索关键词
        $keywords   = I('keywords'); 
        $status     = I('status',0,'intval'); 
        $p = I('p');
        $size = 20; 
        if($p == '')
            $p = 1;
        $where = "a.status > -2 and a.utype = 0"; 
        if($status){
            $where .= ' and a.status = '.$status;
        }
        if($keywords){
            $where .= " And ( a.skname like '%".$keywords."%' or user.mobile like '%".$keywords."%' or bank.name like '%".$keywords."%')"; 
        }
        $table = C('DB_PREFIX').'user_bankcard as a';
        $field = "a.*,user.mobile,user.email,user.uname,bank.name as bankname";
        $join = C('DB_PREFIX')."users as user on user.uid = a.uid"; 
        $join2 = C('DB_PREFIX')."bank as bank on bank.id = a.bankid";
        $count = M('user_bankcard')->table($table)->join($join,"LEFT")->join($join2,"LEFT")->where($where)->count();  
        $list =  M('user_bankcard')->table($table)->join($join,"LEFT")->join($join2,"LEFT")->where($where)->field($field)->limit(($p-1)*$size,$size)->select(); 
        $this->page = getPageHtmlPOST($p,$count,$size);
        $this->data = $list; 
        $this->p = $p; 
        $this->status = $status; 
        $this->keywords = $keywords; 
        $this->display();
    }
    
    // public function edit($value='')
    // {
    //     $this->display();
    // }
    /*
     * Author: yutanghua
     * 企业会员页面处理页面  审核  锁定 解锁等操作 
     * $type  1:列表显示  2：搜索显示 
     */
    public function companyd(){
        $uid = I('uid');
        if(!$uid){echo 'companyCheck页面不存在';die;} 
        $data = M('user')->where("uid = $uid")->find();
        if(!$data){echo '该用户不存在';die;}
        $this->data = $data;
        $this->display();
    }
    
        /*
     * Author: yutanghua
     * 企业会员页面处理页面  审核  锁定 解锁等操作 
     * $type  1:列表显示  2：搜索显示 
     */
    public function persiond(){
        $uid = I('uid');
        if(!$uid){echo 'companyCheck页面不存在';die;} 
        $data = M('user')->where("uid = $uid")->find();
        if(!$data){echo '该用户不存在';die;}
        $this->data = $data;
        $this->display();
    }
    
    /*
     * Author: yutanghua
     * 企业会员页面处理页面  
     * 处理方法状态：-1 未审核；1 已审核；2 已冻结锁定
     * -1 未审核   -> 1 已审核
     * 2 已冻结锁定 -> 1 已审核
     * 1 已审核    -> 2 已冻结锁定 
     */
    public function CheckHandle(){
        $uid = I('uid');
        if(!$uid){echo 'CheckHandle uid错误';die;}
        $status = I('status');
        if(($status == -1)or($status == 2)){
            $status = 1;
        }else if($status == 1){
            $status = 2;
        }else{
            echo 'CheckHandle 参数错误';die;
        }
        $type = I('type');
        if($type == 2){
            $url = '/member/person.html';
        }
        else {
            $url = '/member/index.html';
        }
        if(M('user')->where("uid = $uid")->setField('status',$status))
            redirect($url);
        else{
            echo 'CheckHandle 数据库操作错误';die;     
        }
    }
    
    /**
     * Author: yth <linzujin@juwang.com>
     * Desc: 异步获取数据
     */
    public function getAjax() {
        if (!IS_AJAX) {return;}     
        $user = M('users');
        $table = C('DB_PREFIX').'users as a';
        $step = I('post.act');
        $id = (int) I('post.id');//获取要修改的会员id
        $unbindedtype=I('post.unbindedtype');
        $sql="uid={$id}";
        switch ($step) {
            //状态：-1 未审核；1 已审核；2 已冻结锁定
            //激活用户
            case 'unbindbyID':{ 
                //解绑手机
                if($unbindedtype==1){
                    $data=array(
                        'mobilebind'=>0,
                        'mobile'=>0
                        );
                    $check=$user->where($sql)->save($data);
                }
                else{
                        $data=array(
                        'emailbind'=>0,
                        'email'=>0
                        );
                   $check=$user->where($sql)->save($data);
                }
                if($check){
                   // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
                    //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');  
                break;
            }
            //锁定用户
            case 'lockbyID':{ 
                $check=$user->where($sql)->setField("status", 2);
                if($check){
                   // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
                    //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');    
                break;
            }
            //解锁用户
            case 'unlockbyID':
                $check=$user->where($sql)->setField("status", 1);
                if($check){
                   // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
                    //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');    
                break;
           
        }//switch
   } 
}