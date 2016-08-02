<?php

namespace ZHT\Model;

use Think\Model;

class IncomeModel extends Model {

    public function getWithdrawById($id) {
        $model = new Model('withdraw');
        $res = $model->where("id='{$id}'")->find();
        return $res; 
    }

    //修改提现状态： 1 申请提现；2提现处理中；3提现成功 ；４退款
    public function addIncome($Incomedata) { 
        $model = new Model();
        $model->startTrans();
        $flag = false;
        $user_id = $Incomedata['user_id'];
        $userinfo = M('user')->where('uid = '.$user_id)->find();
        // add Income
        $Incomeid = $model->table(C('DB_PREFIX') . 'income')->add($Incomedata);
        // insert Income_log
        $user_count1 = $model->table(C('DB_PREFIX') . 'user')->where("uid = '{$user_id}'")->setInc('balance', $Incomedata['money']);
        $user_count2 = $model->table(C('DB_PREFIX') . 'user')->where("uid = '{$user_id}'")->setInc('income', $Incomedata['money']);
        $user_balance_count = $model->table(C('DB_PREFIX') . 'user_balance_change_log')->add($this->genUserBalanceChangeData($Incomedata,$userinfo['balance']));
        $msg = "线下充值：".$Incomedata['money']."元,充值用户id：".$user_id.'  充值管理员id：'.$Incomedata['adminid'];
        $logid = D("Adminaction")->actionLog($msg);
        if ($Incomeid && $user_count1 && $user_count2 && $user_balance_count&&$logid) {
            $model->commit();
            $flag = true;
        } else {
            $model->rollback();
        }
        return $flag;
    }
    

    private function genUserBalanceChangeData($Incomedata,$balance) {
        $data = array();
        $data ['uid']         = $Incomedata['user_id'];
        $data ['category']    = 1;
        $data ['type']        = 1;
        $data ['money']       = $Incomedata['money'];
        $data ['balance']     = $Incomedata['money']+$balance;
        $data ['deal_uid']    = $Incomedata['adminid'];
        $data ['dateline']    = time();
        $data ['memo']        = "线下充值：".$Incomedata['money']."元";
        return $data;
    }

}
