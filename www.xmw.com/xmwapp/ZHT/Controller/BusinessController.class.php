<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class BusinessController extends CommonController {
     private  $atype = array(
     		1=>'经纪人',
     		2=>'VIP客服',
     		3=>'客户合作',
     		4=>'账号支付'
     );
    /*
     * Author: yutanghua
     * 商务合作列表页面 
     * $type  1:列表显示  2：搜索显示 
     */
    public function index() {  
        $keywords = I('keywords');
        $size = 20; 
        $m = M('agents');
        $p = I('p');
        if($p == '') $p = 1;
        $url = '/Business/index.html';
        $where = 'business.status = "1" ';
        if($keywords != ''){
            $where .= " and business.name like '%".$keywords."%'"; 
        } 
        $count = $m->table(C('DB_PREFIX').'agents as business')->where($where)->count();
        $join = C('DB_PREFIX').'admin as admin on admin.aid = business.aid';
        $field= "business.*,admin.username as author_name";
        $list =  $m->table(C('DB_PREFIX').'agents as business')->where($where)->join($join,'LEFT')->order('business.add_time desc')->field($field)->limit(($p-1)*$size,$size)->select(); 
        $this->keywords = $keywords;
        $this->qtype = $this->atype;
        $this->page = getPageHtml($p,$count,$url,$size,$keywords);
        $this->dataList=$list; 
        $this->display();
    }
    
    /*
     * 添加&编辑
     */
    public function add() {
        $id = I('id',0,'intval'); 
        $fromtype = 1;
        $data = array();
        if($id){
            $fromtype = 2;
            $data = M('agents')->where("status = '1' and agid = $id")->find();
            if(!$data){
                $this->error('页面不存在');
            }
        }else{
        }
        //根据id获取到的资讯记录
        $this->data = $data;
        $this->qtype = $this->atype;
        $this->fromtype = $fromtype;
        $this->display();
    }
    
     /*
     * 添加资讯处理函数
     */
    public function addHandle() { 
        if(!IS_POST)  
            $this->error('页面不存在');
        //加一些验证的  
        $arr = array( 
            'type'    => I('type'),
            'name'    => $_POST['name'],
            'update_time' => time(),
            'email'      => trim($_POST['email']),
            'telphone'      => trim($_POST['telphone']),
            'qq'      => trim($_POST['qq']),
        );
        
        //编辑修改
        if(I('fromtype') == 2){
            $id = I('fromid');
            if(!M('agents')->where("agid = $id")->save($arr))
            {
                echo "修改问答，原因可能是插入数据库错误addHandle（） ";die;
            }else{
                D("Adminaction")->actionLog("修改了商务合作：".$arr['answer']."   ID:".$id);
                redirect('/Business/index.html');     
            }
        }else{
            //添加数据
            $arr['add_time']    = time();
            $arr['status']      = '1'; 
            $user = session('XZHTUserInfo'); 
            $arr['aid']   = $user[aid];
            $id = M('agents')->add($arr);
            if($id){
                D("Adminaction")->actionLog("添加了商务合作：".$arr['question']."   ID:".$id);
                redirect('/Business/index.html'); 
            } else{
                echo "添加问题失败，原因可能是插入数据库错误addHandle（） ";die;    
            }
        }
        
    }
    
    /**
     * Author: yth <linzujin@juwang.com>
     * Desc: 异步获取数据
     */
    public function getAjax() {
        //if (!IS_AJAX) {return;}     
        $user = M('news_category');
        $step = I('post.act');
        $id = (int) I('post.id');
        switch ($step) {
            //资讯删除数据-单id
            case 'deleteDatabyID':{
                $id = I('post.selectedID');
                $str = '-1';
                $pdata = M('agents')->where("agid = $id")->find();
                if($pdata && M('agents')->where("agid = $id")->setField('status','-1'))
                {
                    $str = '0';
                    $msg = "删除了商务合作：".$pdata['title']."   ID:".$id;
                    D("Adminaction")->actionLog($msg);
                } 
                $this->ajaxReturn($str,'json'); 
                break;
            }

        }//switch
   }//getAjax()
   
   //上传图片方法
    private function _uploadimg($file) {
        $upload = new \Think\Upload(); // 实例化上传类    
        $upload->maxSize = 3145728; // 设置附件上传大小    
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型    
        $upload->savePath = '/Pic/Gonggao/'; // 设置附件上传目录    // 上传单个文件
        $info = $upload->uploadOne($file);
        if (!$info) {// 上传错误提示错误信息        
            $this->error($upload->getError());
        } else {
            // 上传成功 获取上传文件信息
            return $info['savepath'] . $info['savename'];
        }
    }
}