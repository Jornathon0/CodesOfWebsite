<?php

namespace ZHT\Model;

use Think\Model;

class MessageModel extends Model { 

    /*
     * Author: yutanghua
     * 发送系统消息  
     * 2015.11.5 
     */
    public function sendSystemMsg($id,$title) { 
        $model = new Model();
        $arr = array(
            'title'  =>  $title,
            'type'  =>  1,
            'uid'   =>  $id,
            'read'  =>  2,
            'status'  => 1,
            'add_time'  => time());
        return $model->table(C('DB_PREFIX') . 'message')->add($arr);
    }
   
}
