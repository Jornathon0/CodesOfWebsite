<?php 
namespace ZHT\Model;

use Think\Model;

class AdminModel extends Model {
    //定义主表
    Protected $table = 'xmw_user';
    //定义关联关系
    protected $_link = array(
        'role'  => array(
            'mapping_type'    => MANY_TO_MANY,
            'foreign_key'     => 'user_id',
            'relation_key'    => 'role_id',
            'relation_table'  => 'xmw_admin_role_user',
        )
    );
    
   /*
    * Author: yutanghua
    * 删除节点
    * 2015.11.2
    */ 
    public function deleteNodeByid($id,$level){
        $model = new Model();
        $model->startTrans();
        $mRes1 = false;
        $mRes2 = false;
        $node = $model->table(C('DB_PREFIX') . 'admin_node')->where("id = $id")->find();
        $type = '';
        //删除应用
        if($level == 1){
            //如果是删除应用，判断是否有子控制器方法，如有不让删除
            if($model->table(C('DB_PREFIX') . 'admin_node')->where("pid = $id")->select()){
                //应用不为空，不能直接删除
                $model->rollback();
                return false;
            }else{
                $mRes1 = $model->table(C('DB_PREFIX') . 'admin_access')->where("node_id = $id")->delete();
                $mRes2 = $model->table(C('DB_PREFIX') . 'admin_node')->where("id = $id")->delete();
                $type = '应用';
            }
        } 
        //删除方法
        else if($level == 3){
            $mRes1 = $model->table(C('DB_PREFIX') . 'admin_access')->where("node_id = $id")->delete();
            $mRes2 = $model->table(C('DB_PREFIX') . 'admin_node')->where("id = $id")->delete();
            $type = '方法';
        }
        //删除控制器
        else if($level == 2){
            $Allchildid = $model->table(C('DB_PREFIX') . 'admin_node')->where("pid = $id")->field('id')->select();
            $arr = '';
            foreach($Allchildid as $v){
                $arr .= $v['id'].',';
            }
            $arr .= $id;  
            $map['id'] = array('in',$arr); 
            $map2['node_id'] = array('in',$arr);
            $mRes1 = $model->table(C('DB_PREFIX') . 'admin_access')->where($map2)->delete();
            $mRes2 = $model->table(C('DB_PREFIX') . 'admin_node')->where($map)->delete();
            $type = '控制器';
        }
        if($mRes2)
        {
            $model->commit();
            D('Adminaction')->actionLog("删除了".$type.":".$node['title'].'  节点ID:'.$node['id']);
            return true;
        }else{
            $model->rollback();
            return false;
        }
    }
    
    /*
    * Author: yutanghua
    * 删除角色
    * 2015.11.2
    */ 
    public function deleteRoleByid($id){
        $model = new Model();
        $model->startTrans();
        //判断这个角色是否有子角色  如果有 则不让删除
        if($model->table(C('DB_PREFIX') . 'admin_role')->where("pid = $id")->find())
        {
            $model->rollback();
            return 2; 
        }
        //判断这个角色是否有用户
        if($model->table(C('DB_PREFIX') . 'admin_role_user')->where("role_id = $id")->find())
        {
            $model->rollback();
            return 2; 
        }
        
        //如果没有子角色 则删除  权限表也相应删除
        $roledata  = $model->table(C('DB_PREFIX') . 'admin_role')->where("id = $id")->find();
        $mRes1 = $model->table(C('DB_PREFIX') . 'admin_role')->where("id = $id")->delete();
        $mRes2 = $model->table(C('DB_PREFIX') . 'admin_access')->where("role_id = $id")->delete();
        $rolename = $roledata['remark'];
        D('Adminaction')->actionLog("删除了角色:".$rolename);
        if($mRes1 && $mRes2)
        {
            $model->commit();
            return 0;
        }else{
            $model->rollback();
            return 1;
        }
    }
    
    /*
    * Author: yutanghua
    * 删除用户
    * 2015.11.2
    */ 
    public function deleteUserByid($id){
        $model = new Model();
        $model->startTrans(); 
        $mRes1 = $model->table(C('DB_PREFIX') . 'admin')->where("aid = $id")->setField('status',-1);
        if($model->table(C('DB_PREFIX') . 'admin_role_user')->where("user_id = $id")->find())
           $mRes2 = $model->table(C('DB_PREFIX') . 'admin_role_user')->where("user_id = $id")->delete();
        else {
           $mRes2 = true;
        }
        if($mRes1 && $mRes2)
        {
            $model->commit();
            return true;
        }else{
            $model->rollback();
            return false;
        } 
    }
    
    /*
    * Author: yutanghua
    * 添加用户
    * 2015.11.6
    */ 
    public function addUserByid($user,$role){
        $model = new Model();
        $model->startTrans(); 
        $aid = $model->table(C('DB_PREFIX') . 'admin')->add($user);
        $role['user_id'] = $aid;
        $mRes2 = $model->table(C('DB_PREFIX') . 'admin_role_user')->add($role);
        $roleid = $role['role_id'];
        $role = $model->table(C('DB_PREFIX') . 'admin_role')->where("id = $roleid")->find();
        $rolename = $role['remark'];
        D('Adminaction')->actionLog("添加了:".$rolename."  用户aid：".$aid.'  用户名：'.$user['username']);
        if($aid && $mRes2)
        {
            $model->commit();
            return true;
        }else{
            $model->rollback();
            return false;
        } 
    }
    
    /*
    * Author: yutanghua
    * 修改权限
    * 2015.11.6   
    */ 
    public function ModifyAccess($rid,$access){
        $model = new Model();
        $model->startTrans(); 
        $mRes = false;
        $dataList = array();
        if($model->table(C('DB_PREFIX') . 'admin_access')->where("role_id = $rid")->delete()){
        	
             foreach($access as $v){
                $temp = explode('_',$v);
                $data = array(
                    'role_id' => $rid,
                    'node_id' => $temp[0],
                    'level'   => $temp[1]);
                $dataList[] = $data;
             }
             $mRes = $model->table(C('DB_PREFIX') . 'admin_access')->addAll($dataList);
             $roledata = $model->table(C('DB_PREFIX') . 'admin_role')->where("id = $rid")->find();
             D('Adminaction')->actionLog("修改了:".$roledata['remark']."  的权限.");
        } 
        if($mRes)
        {
            $model->commit();
            return true;
        }else{
            $model->rollback();
            return false;
        } 
    }
    /*
    * Author: yutanghua
    * 判断角色名称是否唯一
    * 2015.11.9 
    * 返回值  0：唯一   1：名称重复
    */ 
    public function checkRoleNameOnly($data){
        $model = new Model(); 
        $name = $data['name'];
        $id = $data['id']; 
        $where = "name = '$name'";
        if($id){
            $where .= " and id != $id";
        }
        $mRes = $model->table(C('DB_PREFIX') . 'admin_role')->where($where)->find();
        if($mRes)
        { 
            return 1;
        }else{ 
            return 0;
        } 
    }
    /*
    * Author: yutanghua
    * 判断用户名是否唯一
    * 2015.11.9 
    *  返回值  0：唯一   1：名称重复
    */ 
    public function checkUserNameOnly($data){
        $model = new Model(); 
        $name = $data['name'];
        $id = $data['id']; 
        $where = "username = '$name' and `status` != -1";
//        if($id){
//            $where .= " and aid != $id";
//        }
        $mRes = $model->table(C('DB_PREFIX') . 'admin')->where($where)->find();
        if($mRes)
        { 
            return 1;
        }else{ 
            return 0;
        } 
    }
}