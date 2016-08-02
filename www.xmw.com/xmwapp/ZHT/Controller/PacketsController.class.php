<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class PacketsController extends CommonController {
     // private  $atype = array(
     // 		1=>'经纪人',
     // 		2=>'VIP客服',
     // 		3=>'客户合作',
     // 		4=>'账号支付'
     // );
    /*
     * Author: yutanghua
     * 商务合作列表页面 
     * $type  1:列表显示  2：搜索显示 
     */
    public function index() {  
        $pmoney = M('packets_record');
        $p = I('p');
        if($p == '') $p = 1;
        $size = 20;
        $keywords = I('keywords');
        // $url = '/Business/index.html';
        $where = '';
        if($keywords != ''){
            $where .= " user.uname like '%".$keywords."%' or pmoney.uid like '%".$keywords."%'"; 
        } 
        $join = C('DB_PREFIX').'users as user on user.uid = pmoney.uid';
        $count = $pmoney->table(C('DB_PREFIX').'packets_record as pmoney')->where($where)->join($join,'LEFT')->count();
        $totalcount=$pmoney->getField("sum(packetsmoney) as totalcount");
        $field= "pmoney.*,user.uname";
        $list =  $pmoney->table(C('DB_PREFIX').'packets_record as pmoney')->where($where)->join($join,'LEFT')->order('pmoney.addtime desc')->field($field)->limit(($p-1)*$size,$size)->select(); 
        foreach ($list as $k => $v) {
           $list[$k]['addtime']=date("Y-m-d h:m:s",$v['addtime']);
        }
        $this->keywords = $keywords;
        $this->page = getPageHtml($p,$count,$size);
        $this->data=$list;
        $this->totalcount=$totalcount;
        $this->display();
    }
    
    /*
     * 添加&编辑
     */
    public function settings() {
        $exist=M('packets_settings')->find();
        $arr=array();
        $type=2;//设置红包
        if(!empty($exist)){
            $arr = array( 
                'total'  => $exist['total'],//每天发放总额
                'permin' => $exist['permin'],//单个红包最小金额
                'num'    => $exist['num'],//每日发放红包个数
            ); 
            $type=1;//编辑红包
            
        }
         $this->arr = $arr;
         $this->type=$type;
         $this->display();
    }
         /*
     * 添加红包设置处理函数
     */
    public function addHandle() { 
        if(!IS_POST)  
            $this->error('页面不存在');
        //加一些验证的  
        $arr = array( 
            'total'       => $_POST['total'],//每天发放总额
            'permin'      => $_POST['permin'],//单个红包最小金额
            'num'    => $_POST['num'],//每日发放红包个数
        );
        $exist=M('packets_settings')->find();


        //echo M('packets_settings')->getlastSql();exit;
        //编辑修改
        if(!empty($exist)){
            $packets_settings = M("packets_settings"); // 实例化对象
            // 要修改的数据对象属性赋值
            $arr['updatetime'] = time();
            $arr['total']=$_POST['total'];
            $arr['permin']=$_POST['permin'];
            $arr['num']=$_POST['num'];
            // 根据条件保存修改的数据
            $data=$packets_settings->where("id <> ''")->save($arr); 
            if(!$data)
            {
                echo "修改红包设置失败1，原因可能是插入数据库错误addHandle() ";die;
            }else{
                D("Adminaction")->actionLog("修改了红包设置：每日发放总额".$arr['total']."   单个最低金额:".$arr['permin']."   红包个数:".$arr['num']);
                redirect('/Packets/settings.html');     
            }
        }else{
            //添加数据
            $arr['addtime']    = time();
            $arr['updatetime'] = time();
            $user = session('XZHTUserInfo'); 
            $arr['aid']   = $user[aid];
            $id = M('packets_settings')->add($arr);
            if($id){
                D("Adminaction")->actionLog("修改了红包设置：每日发放总额".$arr['total']."   单个最低金额:".$arr['permin']."   红包个数:".$arr['num']);
                redirect('/Packets/settings.html'); 
            } else{
                echo "红包设置失败，原因可能是插入数据库错误addHandle() ";die;    
            }
        }
        
    }
    
    
}