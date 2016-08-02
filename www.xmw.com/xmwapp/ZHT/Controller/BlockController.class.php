<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     xiaoQ <qiuyifeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class BlockController extends CommonController {
            
    /*
     * Author: qiuyifeng
     * 首页礼品自定义管理 
     * 
     */
    public function index() {  
        $setting = M('setting');
        $gift_index = $setting->field('value')->where("`key` = 'index_gift'")->find();
        if ($gift_index) {
            $gift_index = json_decode($gift_index['value'],true);
        }
        $array['gift'] = $gift_index;
		
		
        $this->display();
    }
    
    
   //上传图片方法
    private function _uploadimg($file) {
        $upload = new \Think\Upload(); // 实例化上传类    
        $upload->maxSize = 3145728; // 设置附件上传大小    
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型    
        $upload->savePath = '/Pic/Gonggao/'; // 设置附件上传目录    // 上传单个文件
        $info = $upload->uploadOne($file);
        if (!$info) {// 上传错误提示错误信息        
            $this->error($upload->getError());
        } else {
            // 上传成功 获取上传文件信息
            return $info['savepath'] . $info['savename'];
        }
    }
}