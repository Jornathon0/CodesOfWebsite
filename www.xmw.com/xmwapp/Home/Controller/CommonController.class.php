<?php

/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:会员相关的公共控制器，有登录验证
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */

namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller {

    public $userinfo; //用户登录信息

    /**
     * @author: king <linzujin@juwang.com>
     * @todo:全局通用判断是否登入
     */

    public function _initialize() {
        $userinfo = session('XMUserInfo');
        if ($uid) {
            $userinfo['uid'] = $uid;
        } else {
            if (empty($userinfo)) {
                //$this->error('请先登录','/login.html',100);
                redirect301('/login.html');
            }
        }
       
    }
    
    /**
     * @Author: king <linzujin@juwang.com>
     * @todo: 当用户访问一个不存在方法时做跳转
     */
    public function __call($name, $value) {
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码 
        $this->display("Public:404"); 
    }
}
