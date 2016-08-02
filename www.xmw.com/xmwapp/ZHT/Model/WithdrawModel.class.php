<?php

namespace ZHT\Model;

use Think\Model;

class WithdrawModel extends Model {

    public function getWithdrawById($id) {
        $model = new Model('withdraw');
        $res = $model->where("id='{$id}'")->find();
        return $res; 
    }

    //修改提现状态： 1 申请提现；2提现处理中；3提现成功 ；４退款
    public function uWithdrayFlag($withdraw_id, $admin_id,$flag) { 
        $model = new Model();
        $model->startTrans();
        $res = false;
        $time = time();
        // update withdraw
        $withdraw_count = $model->table(C('DB_PREFIX') . 'withdraw')->where(array('id' => $withdraw_id))->setField(array('flag' => $flag, 'update_time' => $time));
        // insert withdraw_log
        $withdraw_log_count = $model->table(C('DB_PREFIX') . 'withdraw_confirm_log')->add($this->genWithdrawLogData($withdraw_id, $admin_id, $time));
        if ($withdraw_count && $withdraw_log_count) {
            
            $model->commit();
            $res = true;
        } else {
            $model->rollback();
        } 
        return $res;
    }
    
//    public function uWithdrayFlag($withdraw_id, $admin_id) { 
//        $model = new Model();
//        $model->startTrans();
//        $time = time();
//        // update withdraw
//        $withdraw_count = $model->table(C('DB_PREFIX') . 'withdraw')->where(array('id' => $withdraw_id))->setField(array('flag' => '3', 'update_time' => $time));
//        // insert withdraw_log
//        $withdraw_log_count = $model->table(C('DB_PREFIX') . 'withdraw_confirm_log')->add($this->genWithdrawLogData($withdraw_id, $admin_id, $time));
//        if ($withdraw_count && $withdraw_log_count) {
//            $model->commit();
//            $flag = true;
//        } else {
//            $model->rollback();
//        }
//        return $flag;
//    }

    private function genWithdrawLogData($withdraw_id, $admin_id, $time) {
        $data = array();
        $data ['admin_id'] = $admin_id;
        $data ['withdraw_id'] = $withdraw_id;
        $data ['add_time'] = $time;
        return $data;
    }

}
