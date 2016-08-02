<?php
namespace ZHT\Controller;
use Think\Controller;

class AdminController extends CommonController{
            
   /*
    * Author: yutanghua
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
    * Author: yutanghua
    * 添加  
    * 只显示名下的人员   如果是超级管理员  则全部显示出来
    */
    public function addMember(){
        $UserInfo = session('XZHTUserInfo');
        $id = $UserInfo['aid']; 
        if($id == 1){
            $this->roledata = M('admin_role')->select();
        }else{
            $tempdata = M('admin_role_user')->where("user_id = $id")->find();
            $roleId = $tempdata['role_id'];
            $roledata = M('admin_role')->select();
            $this->roledata = g_getchildNode($roledata,$roleId); 
            if($this->roledata == null)
            {
                echo '你没有权限添加用户';die;
            }
        }
        $this->display();
    }
    
   /*
    * Author: yutanghua
    * 添加处理函数  
    */
    public function addMemberHandle(){
        $user = array(
            'username'        => I('name'),
            'passwd'          => md5(I('password')),
            'last_login_time' => date('Y-m-d H:i:s'),
            'status'          => 1,
        );
        $role = array(
            'role_id' => I('role') 
        );
        if(D('admin')->addUserByid($user,$role)){
            redirect('/admin/index.html');
        }else{
            echo "添加用户失败..addMemberHandle()";die;
        } 
    }
    
    /*
    * Author: yutanghua
    * 节点列表  
    */
    public function node(){
        $data = M('admin_node')->order("name asc")->select();
        if(!$data){
            $arr = array(
                'name'     => '0',
                'status'   => '1',
                'title'    => '总后台',
                'level'    => '1',
                'pid'      => '0',
            );
            M('admin_node')->add($arr);
            $data = M('admin_node')->select();//->where('status = 1')
        }
        $this->data = node_merge($data);
        $this->display(); 
    }
    
    /*
    * Author: yutanghua
    * 添加修改节点  
    */
    public function addNode(){
        $this->type = I('type',1,'intval');
        //pid  如果是type=1：pid添加节点的父id   ，type=2：pid要修改的节点id
        $pid = I('pid',0,'intval'); 
        $this->pid = $pid; 
        if($this->type == 2){
            $this->idData = M('admin_node')->where("id = $pid")->find();
        }
        $this->level = I('level',1,'intval');
        switch ($this->level){
            case 1:
                $this->title = '应用';
                break;
            case 2:
                $this->title = '控制器';
                break;
            case 3:
                $this->title = '动作方法';
                break;
        }
        $this->display();
    }
    
    /*
    * Author: yutanghua
    * 添加节点处理  
    * $type   1： 添加节点   2：修改节点 
    */
    public function addNodeHandle(){
        $type = I('type',1,'intval');
        if($type == 2){
            $id = I('pid',-1,'intval');
            $arr = array(
                'name'     => I('post.name'),
                'status'   => I('post.radio'),
                'title'   => I('post.title'),
                'level'   => I('post.level'), 
            	'sort'   => I('post.sort'),
            );
            if(M('admin_node')->where("id = $id")->save($arr)){
                D('Adminaction')->actionLog("修改了节点:".$arr['title'].'  节点ID:'.$id);
                redirect('/admin/node.html');
            }else{
                $this->error('修改节点失败');
            } 
        }else{
            $arr = array(
                'name'     => I('name'),
                'status'   => I('radio'),
                'title'   => I('title'),
                'level'   => I('level'),
                'pid'   => I('pid'),
            );
            $nodeid = M('admin_node')->add($arr);
            if($nodeid){
                D('Adminaction')->actionLog("添加了节点:".$arr['title'].'  节点ID:'.$nodeid);
                redirect('/admin/node.html');
            }else{
                $this->error('添加节点失败');
            } 
        }
    }
    
    /*
    * Author: yutanghua
    * 角色列表  
    */
    public function role(){
        $table = 'xmw_admin_role as a';
        $join1 = 'xmw_admin_role AS b on b.id = a.pid'; 
        $field = "a.*,b.name as parentname";                           
        $list =  M('admin_role')->table($table)->join($join1,'LEFT')->field($field)->select(); 
        $this->data = $list;
        $this->display();
    }
    
    /*
    * Author: yutanghua
    * type   1: 添加角色  2：修改角色
    * 添加 or 修改角色  
    */
    public function addRole(){
        $rid = I('rid');
        $type = 1;
        if($rid){
            $type = 2;
            $this->rid = $rid;
            $this->roleData = M('admin_role')->where("id = $rid")->find();
        } 
        $this->type = $type;
        $this->dataList = M('admin_role')->where('status = 1')->select();
        $this->display();
    }
    
    /*
    * Author: yutanghua
    * type   1: 添加角色  2：修改角色
    * 添加 or 修改角色处理  
    */
    public function addRoleHandle(){
        $type = I('type');
        $arr = array(
            'name'     => I('name'),
            'status'   => I('radio'),
            'remark'   => I('remark'),
            'pid'   => I('id'),
        );
        //添加角色
        if($type == 1){
            $roleid = M('admin_role')->add($arr);
            if($roleid){
                D('Adminaction')->actionLog("添加了角色:".$arr['remark']."  角色id:".$roleid);
                redirect('/admin/role.html');
            }else{
                $this->error('添加角色失败');
            }
        }else{
        //修改角色
            $id = I('rid'); 
            if(M('admin_role')->where("id = $id")->save($arr)){
                D('Adminaction')->actionLog("修改了角色:".$arr['remark']."  角色id:".$id);
                redirect('/admin/role.html');
            }else{
                $this->error('修改角色失败');
            }
        }
    }
    /*
    * Author: yutanghua
    * 权限配置  
    */
    public function access(){
        $rid = I('rid',0,'intval');
        $data = M('admin_role')->where("id = $rid")->find();
        $this->roleName = $data['name'];
        $data = M('admin_node')->where('status = 1')->order("name asc")->select();
        $access = m('admin_access')->where("role_id = $rid")->getField('node_id',true);
        $this->data = node_merge($data,$access);
        $this->rid = $rid;
        $this->display();     
    }
    
    /*
    * Author: yutanghua
    * 修改权限  
    */
    public function accessHandle(){
        $rid = I('rid',0,'intval');
        if(!$rid){echo '权限配置参数错误  accessHandle';die;} 
        
        if(D('admin')->ModifyAccess($rid,$_POST['access'])){
            redirect('/admin/role.html');
        }else{
            echo '权限配置参数错误  accessHandle2';die;
        } 
    }
    
    /**
     * Author: yutanghua
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
