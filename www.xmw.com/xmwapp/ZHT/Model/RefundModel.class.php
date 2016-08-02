<?php

/**
 * Description of RefundModel
 *
 * @author Peter
 */
namespace ZHT\Model;

use Think\Model;
class RefundModel extends Model {

    public function iRefundWithdraw($withdrawInfo, $admin_id) {
        $flag = false;
        $time = time();
        $model = new Model();
        $model->startTrans();
        // update withdraw
        $withdraw_count = $model->table(C('DB_PREFIX') . 'withdraw')->where(array('id' => $withdrawInfo['id'], 'status' => '1'))->setField(array('flag' => '4', 'update_time' => $time));
        // update user.balance (+)
        $user_count = $model->table(C('DB_PREFIX') . 'users')->where(array('uid' => $withdrawInfo['user_id'], 'status' => '1'))->setInc('money', $withdrawInfo['money']);
        // insert refund
        $refund_count = $model->table(C('DB_PREFIX') . 'refund')->add($this->genRefendData($withdrawInfo['id'], $admin_id, $time));
        if ($withdraw_count && $user_count && $refund_count) {
            $model->commit();
            $flag = true;
        } else {
            $model->rollback();
        }
        return $flag;
    }

    private function genRefendData($withdraw_id, $admin_id, $time) {
        $data = array();
        $data['admin_id'] = $admin_id;
        $data['type'] = '1';
        $data['fid'] = $withdraw_id;
        $data['add_time'] = $time;
        return $data;
    }

}
