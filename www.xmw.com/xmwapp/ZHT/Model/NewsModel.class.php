<?php

namespace ZHT\Model;

use Think\Model;

class NewsModel extends Model { 

    /*
     * Author: yutanghua
     * 删除栏目 
     * 2015.11.5 
     * 返回值 -1：未知错误  0：正确   1:存在子栏目不能删除  2：该栏目正在使用不能删除 
     */
    public function deleteCategorybyID($id) { 
        $model = new Model();
        //判断是否有子栏目
        $res = $model->table(C('DB_PREFIX') . 'news_category')->where("`show` != -2 and (parentid = $id or topid = $id)")->find();
        if($res){return 1;}
        //判断栏目是否在使用
        $res = $model->table(C('DB_PREFIX') . 'news')->where("ncid = $id and status = 1")->find();
        if($res){return 2;}
        //逻辑删除  设置show=-2
        $res = $model->table(C('DB_PREFIX') . 'news_category')->where("ncid = $id")->setField("show",-2);
        if($res){
            $cData = $model->table(C('DB_PREFIX') . 'news_category')->where("ncid = $id")->find();
            $msg = "删除资讯栏目：".$cData['typename']."  栏目ID:".$id;
            D("Adminaction")->actionLog($msg);
            return 0;
        }else{
            return -1;
        } 
    }
    
    /*
     * Author: yutanghua
     * 判断咨询栏目名称是否唯一
     * $id:  存在  则为修改状态
     * 返回值 -1：未知错误  0：有效名称   1：栏目名称重复 
     * time:2015.11.08
     */
    public function checkCagetoryNameValid($data){ 
        $model = new Model();
        $title = $data['title'];  
        $id = $data['id'];
        $pid = $data['pid'];
        $where = "typename = '$title' and parentid=$pid and `show` > -2";
        if($id){
            $where .= " and ncid != $id";
        }
        $res = $model->table(C('DB_PREFIX') . 'news_category')->where($where)->find();
        if($res)
        {
            return 1;
        } 
        return 0;
    }
   
}
