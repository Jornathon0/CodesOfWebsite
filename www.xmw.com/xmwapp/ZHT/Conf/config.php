<?php
return array(
		//'配置项'=>'配置值'
		'SKINURL' =>'/public/xmwadmin/',
		'WEBSITE' =>'http://test.xmw.com/',
		
		//RBAC
		'RBAC_SUPERADMIN'  =>  'admin',//超级管理员名称
		'ADMIN_AUTH_KEY'    =>  'superadmin',//超级管理员识别
		'USER_AUTH_ON'      =>  'true',//是否开启验证
		'USER_AUTH_TYPE'    =>  1,      //验证类型（1:登陆验证，   2：时时验证）
		'USER_AUTH_KEY'    =>  'aid',  //用户认证识别号
		'NOT_AUTH_MODULE'   => 'Pages,index',//无需认证控制器
		'NOT_AUTH_ACTION'   => '',//无需动作方法
		'RBAC_ROLE_TABLE'   => 'xmw_admin_role',//角色表名称
		'RBAC_USER_TABLE'   => 'xmw_admin_role_user',//角色与用户中间表名称
		'RBAC_ACCESS_TABLE' => 'xmw_admin_access',//权限表名称
		'RBAC_NODE_TABLE'   => 'xmw_admin_node',//节点表名称
		//RBAC
);