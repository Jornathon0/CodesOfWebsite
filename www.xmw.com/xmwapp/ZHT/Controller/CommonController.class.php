<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     yutanghua <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class CommonController extends Controller {
    public function _initialize() {  
        $UserInfo = session('XZHTUserInfo');
        if ($UserInfo['aid'] == '') {
            redirect('/login.html', 1, '请先登录...');
        }
       if(C('USER_AUTH_ON')){
           $Rbac = new \Org\Util\Rbac();
           if(!$Rbac::AccessDecision('zht'))
               redirect('/Pages/index.html', 1, '没权限...');
       }
    }
}
