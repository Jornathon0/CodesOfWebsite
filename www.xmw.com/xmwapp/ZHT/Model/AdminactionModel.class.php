<?php

namespace ZHT\Model;

use Think\Model;
      
class AdminactionModel extends Model { 
    protected $trueTableName = 'xmw_admin_action';
    /*
     * Author: yutanghua
     * 用户操作记录  
     * 2015.11.6 
     */
    public function actionLog($msg) {
        $UserInfo = session('XZHTUserInfo');
        if ($UserInfo['aid'] == '') {
            return false;
        }  
        $model = new Model();
        $arr = array(
            'memo'      =>  $msg,
            'username'  =>  $UserInfo['username'],
            'aid'       =>  $UserInfo['aid'], 
            'dateline'  =>  time());
        return $model->table(C('DB_PREFIX') . 'admin_action')->add($arr);
    } 
}
