<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class IndexController extends CommonController {

    public function index() { 
        $idArr = array();
        $userinfo = session('XZHTUserInfo');
        $id = $userinfo['aid'];
      
        if($id != 1){
            //$sql = "SELECT node.name FROM xmw_admin_role AS role,xmw_admin_role_user AS USER,xmw_admin_access AS access ,xmw_admin_node AS node WHERE user.user_id=$id AND user.role_id=role.id AND ( access.role_id=role.id OR (access.role_id=role.pid AND role.pid!=0 ) ) AND role.status=1 AND access.node_id=node.id  AND node.status=1";
            //$res = M('admin_role')->query($sql);  
            $table = "xmw_admin_role_user as roleuser";
            $join1 = 'xmw_admin as admin on admin.aid = roleuser.user_id';
            $join2 = 'xmw_admin_role as role on role.id = roleuser.role_id'; 
            $field = "role.*";
            $res = M('admin_role_user')->table($table)->join($join1,'LEFT')->join($join2,'LEFT')->where("admin.aid = $id")->field($field)->find();
            $roleid = $res['id']; 
            if(!$roleid){echo "您所在角色不存在，请联系管理员";die;}
            $table = "xmw_admin_node as node";
            $join = "xmw_admin_access as access on access.role_id = $roleid";
            $field = "node.id,node.name,node.title,node.pid,node.level";
            $res = M("admin_node")->table($table)->join($join,'LEFT')->where("access.node_id = node.id and node.status<>-1 ")->field($field)->order('node.sort asc')->select(); 
        }
        //超级管理员权限
        else {
        	$res = M("admin_node")->field('id,name,title,pid,level')->where("status<>-1")->order('sort asc')->select();
        }
        $idArr = $idArr1 = array();
        foreach ($res as $v){
        	if($v['level']==3){
        		$idArr1[$v['pid']][] = $v;
        	}
        }
        foreach ($res as $v){
        	if($v['level']==2)
        	{
        		$idArr[$v['id']] = $v;
        		$idArr[$v['id']]['list'] = $idArr1[$v['id']];
        	}
        }
        $this->username = $userinfo['username'];
        $this->menulist = $idArr; 
        $this->display();
    }
}
