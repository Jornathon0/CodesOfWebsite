<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class LoginController extends Controller {

    public function login() {
        $UserInfo = session('XZHTUserInfo');
        if ($UserInfo['aid'] != '') {
            redirect('/');
        }
        $this->assign($array);
        $this->display('index');
    }

    public function doact() {
        if (IS_POST) {
            $username = addslashes(I('post.username'));
            $passwd = md5(addslashes(I('post.passwd'))); 
            $admin = M('admin');
            $log = M('admin_log');
            //获取10分钟内登陆失败次数 
            $now_10min = time() - 10 * 60;
            $count = $log->where(" logintime > {$now_10min} and username = '{$username}' and status = -1")->count();
            if ($count > 3) {
                $LastLoginTime = $log->where("username = '{$username}'")->max('logintime');
                //10分钟内有3次输入错误，而且最新一次在5分钟内  则无需判断直接退出
                if (time() - $LastLoginTime < 5 * 60) {
                    redirect('/login/index.html', 1, '已经连续3次输入账号或者密码错误，5分钟后再登陆');
                    //die(json_encode(array('url' => '/login.html', 'error' => '已经连续3次输入账号或者密码错误，5分钟后再登陆')));
                }
                session('FailTimes', 0);
            }
            $userinfo = $admin->where("username = '{$username}' and passwd = '{$passwd}' and `status` != -1")->find();
            if ($userinfo) {
                session('XZHTUserInfo', $userinfo);
                cookie('XZUserInfo', $userinfo);
                
                # 登录日志
                if ($userinfo['status'] != 1) {//用户被锁定
                    self::_adminLog($username, -1);
                    redirect('/login/index.html', 2, '用户被锁定，请联系客服激活账号');
                    //die(json_encode(array('url' => '/login.html', 'error' => '用户被锁定，请联系客服激活账号')));
                }
                
                self::_adminLog($username, 1);
                session('FailTimes', 0);
                //超级管理员识别  写入权限  读取用户权限
                if($userinfo['username'] == C('RBAC_SUPERADMIN')){
                    session(C('ADMIN_AUTH_KEY'),true);
                }else{
                    session(C('ADMIN_AUTH_KEY'),false);
                }
                session('aid', $userinfo['aid']);
                $Rbac = new \Org\Util\Rbac();
                $Rbac::saveAccessList();
                //redirect('/');
                echo '<script type="text/javascript">top.location.href="/index.html";</script>';
                //die(json_encode(array('url' => '/', 'error' => '')));
            } else {
                # 登录日志
                self::_adminLog($username, -1);
                header("Content-Type:text/html;charset=utf-8");
                $FailTimes = session('FailTimes') + 1;
                session('FailTimes', $FailTimes);
                $msg = '用户名或密码错误，请重新输入，你还有' . (3 - $FailTimes) . '次机会!';
                redirect('/login/index.html', 1, $msg);
                //die(json_encode(array('url' => '/login.html', 'error' => $msg)));
            }
        }
    }

    /*
     * 功能：  记录登陆日志
     * param  $username：用户名
     * param  $status  ：1  登陆成功  -1：登陆失败 
     */

    private function _adminLog($username, $status) {
        $this_login_time = date("Y-m-d H:i:s");
        $this_login_ip = get_real_ip(); 
        $Ip = new \Org\Net\IpLocation('UTFWry.dat');
        $area = $Ip->getlocation($this_login_ip);
        $location = $area['country'];
        if($area['area']){
            $location .= '_'.$area['area'];
        }
        if($location == ''){
           $location = '未知'; 
        }
        if ($status == 1) {
            $arr = array(
                'this_login_time' => $this_login_time,
                'this_login_ip' => $this_login_ip,
                'this_login_area' => $location,
            );
            $LastData = M('admin')->where("username = '$username'")->find();
            $arr['last_login_area'] = $LastData['this_login_area'];
            $arr['last_login_ip']   = $LastData['this_login_ip'];
            $arr['last_login_time'] = $LastData['this_login_time'];
            M('admin')->where("username = '$username'")->save($arr);
        }
        $Logarr['status'] = $status;
        $Logarr['logintime'] = time();
        $Logarr['username'] = $username;
        $Logarr['ip'] = $this_login_ip;
        $Logarr['area'] = $area;
        M('admin_log')->add($Logarr);
    }

    public function quit() {
        session('XZHTUserInfo', null);
        redirect('/login.html');
    }

}
