<?php
namespace ZHT\Controller;
use Think\Controller;

class BasesettingController extends CommonController{
            
   /*
    * Author: zhengni
    * 显示首页  
    * 只显示名下的人员   如果是超级管理员  则全部显示出来
    */ 
    public function index(){ 
        $keywords = I('keywords');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $where .= "admin.aid > 0 and admin.status > 0 ";
        $url = '/admin/index.html';
        $table = 'xmw_admin  as admin';
        $joinRU = 'xmw_admin_role_user as role_user on role_user.user_id = admin.aid';
        $joinRole = 'xmw_admin_role as role on role_user.role_id = role.id'; 
        $field = 'admin.*,role.remark as rolename';
        //$field .= ",(SELECT COUNT(*) FROM xmw_product AS product WHERE product.uid IN (SELECT uid FROM xmw_user AS users WHERE users.kefu_id = admin.aid)) AS pcount";
        //$field .= ",(SELECT COUNT(*) FROM xmw_resource AS resource WHERE resource.uid IN (SELECT uid FROM xmw_user AS users WHERE users.kefu_id = admin.aid)) AS rcount";
        $field .=',0 AS pcount,0 AS rcount';
        $count = M('admin')->table($table)->where($where)->count(); 
        $list =  M('admin')->table($table)->join($joinRU,'LEFT')->join($joinRole,'LEFT')->where($where)->field($field)->limit(($p-1)*$size,$size)->select(); 
        
        $this->page = getPageHtml($p,$count,$url,$size,$keywords);
        $this->data = $list; 
        $this->display('index'); 
    }
    /*
    * Author: zhengni
    * 显示会员等级列表
    */ 
    public function setvip(){
        $membergra=M('membership_grade');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $list=$membergra->limit(($p-1)*$size,$size)->select(); 
        $this->data=$list;
        $this->display();
    }
    /*
    * Author: zhengni
    * 添加会员等级设置
    */ 
    public function addGrade(){
        $type=I('type');
        $id=I('id');
        $membergra=M('membership_grade');
        if(!empty($type)){
          $list=$membergra->where("id={$id}")->find();
          $this->type=$type;

          $this->list=$list;
        }
        else{
            $vip=$membergra->getField("max(id)");
            $vip=$vip+1;
            $titles='vip'.$vip;
            $this->titles=$titles;
        }
        $this->display();
    }
    /*
    * Author: zhengni
    * 添加限时抢购设置
    */ 
    public function flashSetting(){
        $setting=M('setting');
        $start=$setting->where("`key`='starttime'and typeid=1")->getField('value');
        $end=$setting->where("`key`='endtime'and typeid=1")->getField('value');
        $type=1;
        if(!empty($start)||!empty($end)){
            $start=date("Y-m-d h:i:s",$start);
            $end=date("Y-m-d h:i:s",$end);
            $type=2;
        }
        $this->start=$start;
        $this->end=$end;
        $this->type=$type;
        $this->display();
    }
    /*
    * Author: zhengni
    * 添加处理设置限时抢购函数  
    */
    public function addFlashsaleHandle(){
        $start=I('start');
        $end=I('end');
        $setting=M('setting');
        $data1=array(
            'value'=>strtotime($start)
            );
        $data2=array(
            'value'=>strtotime($end)
            );
        //添加验证
        $sql="`key`='starttime'and typeid=1";
        $checkStart=$setting->where($sql)->select();
        $checkEnd=$setting->where("`key`='endtime'and typeid=1")->find();
           if($checkStart){
                 $check1=$setting->where($sql)->save($data1);  
            }
            else{
                 $data1['key']="starttime";
                 $data1['typeid']=1;
                 $data1['typename']="限时抢购";
                 $check1=$setting->add($data1);
            }
            if($checkEnd){
                 $check2=$setting->where("`key`='endtime'and typeid=1")->save($data2);
            }
            else{
                 $data2['key']="endtime";
                 $data2['typeid']=1;
                 $data2['typename']="限时抢购";
                 $check2=$setting->add($data2);
            }
        if(!$check1&&!$check2){
            echo "设置限时抢购时间失败..addFlashsaleHandle()";die;           
        }else{
            redirect('/basesetting/flashSetting.html',2,'设置抢购时间成功！');
        }
    }
   /*
    * Author: zhengni
    * 添加处理等级积分函数  
    */
    public function addBaseHandle(){
        $membergra=M('membership_grade');
        $data = array(
            'titles' => I('titles'),
            'integral_min' => I('integral_min'),
            'integral_max' => I('integral_max'),
            'rates' => I('rates'),
            'fixed_num' => I('fixed_num'),
            'quality_num' =>I('quality_num'),
            'flashsale_num' => I('flashsale_num'),
            // 'addtime' => time(),
            'status'=>I('radio'),
            'updatetime' => time(),
        );
        $id=I('id');
        //$isexist=$membergra->where("titles='{$data['titles']}'")->find();
        if($id){
                $check=$membergra->where("id={$id}")->save($data); 
        }
        else{
                $data['addtime']=time();
                $check=$membergra->add($data); 
        }
        if($check){
            redirect('/basesetting/setvip.html',2,'添加会员等级成功！');
        }else{
            echo "添加会员等级失败..addBaseHandle()";die;
        }
 
    }
    /**
     * Author: zhengni
     * Desc: 异步获取数据
     */
    public function getAjax() { 
        if (!IS_AJAX) {return;} 
        $step = I('post.act'); 
        switch ($step) {
            //删除角色-单id 
            case 'deleteRolebyID':{
                $id = I('post.selectedID'); 
                $str = D('admin')->deleteRoleByid($id);
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //删除节点-单id 
            //$level 1:应用 2：控制器  3：方法
            case 'deleteNodebyID':{
                $id = I('post.selectedID');
                $level = I('post.level');
                $res = D('admin')->deleteNodeByid($id,$level);
                if($res){
                    $str = 'ok';
                }else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //添加用户的时候  检查用户名的唯一性
            case 'checkNameOnly':{
                $str = D("Admin")->checkUserNameOnly($_POST); 
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //删除用户
            case 'deleteUserbyID':{
                $id = I('post.selectedID');
                if(D('admin')->deleteUserByid($id)){
                    D('Adminaction')->actionLog('对用户ID：'.$id."进行了 删除 操作.");
                    $str = 'yes';
                }else{
                    $str = 'no';
                }
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //锁定用户  状态：1 正常；2 锁定
            case 'lockUserbyID':{
                $id = I('post.selectedID');
                if(M('admin')->where("aid = '$id'")->setField("status",2))
                {
                    D('Adminaction')->actionLog('对用户ID：'.$id."进行了 锁定 操作.");
                    $str = 'yes';
                }else{
                    $str = 'no';
                }
                $this->ajaxReturn($str,'json');
                break;
            }
            //解锁用户  状态：1 正常；2 锁定
            case 'unlockUserbyID':{
                $id = I('post.selectedID');
                if(M('admin')->where("aid = '$id'")->setField("status",1)){
                    D('Adminaction')->actionLog('对用户ID：'.$id."进行了 解锁 操作.");
                    $str = 'yes';
                }else{
                    $str = 'no';
                }
                $this->ajaxReturn($str,'json');
                break;
            }
            //判断用户名是否可用
            case 'checkRoleName':{
                $str = D("Admin")->checkRoleNameOnly($_POST);
                $this->ajaxReturn($str,'json');
                break;
            }
        }
    }
    
    /*
    * Author: yutanghua
    * 操作日志  
    * 2015.11.07
    */ 
    public function actionLog(){  
        $keywords = I('keywords');
        $p = I('p');
        $size = 20; 
        if($p == '') $p = 1;
        $where = "id > 0";
        $url = '/admin/actionLog.html';
        if($keywords){
            $where .= " and (memo like '%".$keywords."%' or username like '%".$keywords."%')";
        }
        $count = M('admin_action')->where($where)->count(); 
        $list =  M('admin_action')->where($where)->order("dateline desc")->limit(($p-1)*$size,$size)->select(); 
        
        $this->page = getPageHtml($p,$count,$url,$size,$keywords);
        $this->data = $list;
        $this->keywords = $keywords;
        $this->display(); 
    }
}

?>
