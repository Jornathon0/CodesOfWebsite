<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class NewsController extends CommonController {
            
    /*
     * Author: yutanghua
     * 资讯记录列表页面 
     * $type  1:列表显示  2：搜索显示 
     */
    public function index() {
        $keywords = I('keywords');
        $size = 20; 
        $m = M('news');
        $p = I('p');
        if($p == '') $p = 1;
        $url = '/news/index.html';
        $where = 'a.status = 1';
        //栏目搜索
        $scategory = I('scategory');
        if(!$scategory) $scategory = 0;
        if($keywords != ''){
            $where .= " and a.title like '%".$keywords."%'"; 
        } 
        if($scategory != 0){
            $where .= " and (b1.ncid = $scategory or b2.ncid = $scategory or b3.ncid = $scategory) ";
        }
        $table = C('DB_PREFIX').'news as a';
        $join1 = C('DB_PREFIX').'news_category AS b1 on a.ncid = b1.ncid';
        $join2 = C('DB_PREFIX').'news_category AS b2 ON b1.parentid = b2.ncid';
        $join3 = C('DB_PREFIX').'news_category AS b3 ON b2.parentid = b3.ncid';
        $field = "a.*,b1.typename as name1,b2.typename as name2,b3.typename as name3"; 
        $count = $m->table($table)->join($join1,'LEFT')->join($join2,'LEFT')->join($join3,'LEFT')->where($where)->count(); 
        $list =  $m->table($table)->join($join1,'LEFT')->join($join2,'LEFT')->join($join3,'LEFT')->where($where)->field($field)->order('a.add_time desc')->limit(($p-1)*$size,$size)->select(); 
        //显示栏目的名称 如果是子栏目则按 子栏目-> 父栏目->top栏目显示模式
        for($i = 0;$i<count($list);$i++){
            $list[$i]['categoryName'] = $list[$i]['name1'];
            if($list[$i]['name2']){
                $list[$i]['categoryName'] = $list[$i]['categoryName'].'->'.$list[$i]['name2'];
            }
            if($list[$i]['name3']){
                $list[$i]['categoryName'] = $list[$i]['categoryName'].'->'.$list[$i]['name3'];
            }
        }
        //所有的栏目
        $datacategory = M('news_category')->where('`show` = 1')->select();
        $this->datacategory = g_mergeArray($datacategory,'ncid',$html='&nbsp;&nbsp;',$pid=0,$level=0);
        if($scategory == 0){
            $this->scategoryName = '所有栏目';
        }else{
            $tempData = M('news_category')->where("ncid = $scategory")->field("typename")->find();
            $this->scategoryName = $tempData['typename'];
        }
        $this->scategory = $scategory;
        $this->keywords = $keywords;
        $this->page = getPageHtml($p,$count,$url,$size,$keywords);
        $this->dataList=$list; 
        $this->display();
    }
    
    /*
     * 添加资讯
     * $data['type'] = 1  添加资讯
     * $data['type'] = 2  编辑资讯
     */
    public function add() {
        $dataList = M('news_category')->where('`show` = 1')->select();
        $this->dataList = g_mergeArray($dataList,'ncid',$html='&nbsp;&nbsp;',$pid=0,$level=0);
        $data['type'] = 1;
        $data['nid'] = -1;
        $this->data = $data;
        $this->display();
    }
    
    /*
     * 编辑资讯
     */
    public function edit() {
        $nid = I('nid'); 
        $data = M('news')->where("nid = $nid")->find();
        if(!$data){
            $this->error('页面不存在');
        }
        $data['type'] = 2; 
        //根据id获取到的资讯记录
        $this->data = $data;
        //所有的资讯类型
        $dataList = M('news_category')->where('`show` = 1')->select();
        $dataList = g_mergeArray($dataList,'ncid',$html='&nbsp;&nbsp;',0,0);
        $this->dataList = $dataList; 
        $this->display('add');
    }
    
     /*
     * 添加资讯处理函数
     */
    public function addHandle() { 
        if(!IS_POST)  
            $this->error('页面不存在');
        $thumb_path = '';
        $isThumb = false;
        //加一些验证的  
        $arr = array( 
            'ncid'       => $_POST['nidtypes'],
            'title'      => trim($_POST['title']),
            'content'    => $_POST['editorValue'],
            'intro'      => $_POST['intro'],
        );
        if($_FILES['slide_pic']['name']){
            $arr['slide_pic'] = self::_uploadimg($_FILES['slide_pic']);
                
            $image_path = $_SERVER["DOCUMENT_ROOT"].'\Uploads'.$arr['slide_pic'];
            $file_path = pathinfo($arr['slide_pic']);
            $thumb_path = $file_path ['dirname'].'/'.$file_path ['filename'].'_small.'.$file_path ['extension']; 
            $isThumb = true; 
        }
        
        if($_FILES['thumbnail']['name']){
            $arr['thumbnail'] = self::_uploadimg($_FILES['thumbnail']); 
        }
        
        //编辑修改
        if(I('fromtype') == 2){
            $nid = I('fromid');
            $arr['update_time'] = time();
            if(!M('news')->where("nid = $nid")->save($arr))
            {
                echo "修改资讯失败，原因可能是插入数据库错误addHandle（） ";die;
            }else{
                D("Adminaction")->actionLog("修改了资讯：".$arr['title']."   ID:".$nid);
                redirect('/news/index.html');     
            }
        }else{
            //添加数据
            $arr['add_time']    = time();
            $arr['update_time'] = time();
            $arr['click']       = 0; 
            $arr['status']      = 1; 
            $user = session('XZHTUserInfo'); 
            $arr['author_id']   = $user[aid];
            $arr['author_name'] = $user[username]; 
            $id = M('news')->add($arr);
            if($id){
                D("Adminaction")->actionLog("添加了资讯：".$arr['title']."   ID:".$id);
                redirect('/news/index.html'); 
            } else{
                echo "添加资讯失败，原因可能是插入数据库错误addHandle（） ";die;    
            }
        }
        if($isThumb)
        {
            $times = 5;
            while($times > 0)
            {
               $times--;
               if(file_exists($image_path))
                {
                    //生成缩略图
                    $Img = new \Think\Image();
                    $Img->open($image_path);  
                    $Img->thumb(368,184)->save($_SERVER["DOCUMENT_ROOT"].'\Uploads'.$thumb_path);
                    $times = -1;
                    break;
                } 
                sleep(1);
            } 
        }
        
    }
    
    /**
     * Author: yth
     * Desc: 增加资讯
     */
    public function addCategory(){
        $dataList = M('news_category')->where('`show` = 1')->select();
        $this->dataList = g_mergeArrayTop2Level($dataList,'ncid',$html='&nbsp;&nbsp;&nbsp;&nbsp;');
        $data['type'] = 1;  
        $data['ncid'] = -1; 
        $this->data = $data;
        $this->display();
    }
    
    /* 
     * Author: yth
     * 编辑栏目
     */
    public function editCategory() {
        $ncid = I('ncid'); 
        $data = M('news_category')->where("ncid = $ncid")->find();
        if(!$data){
            $this->error('页面不存在');
        }
        $data['type'] = 2; 
        //根据id获取到的资讯记录
        $this->data = $data;
        //所有的资讯类型
        $dataList = M('news_category')->where('`show` = 1')->select();
        $this->dataList = g_mergeArray($dataList,'ncid',$html='&nbsp;&nbsp;',$pid=0,$level=0);
        $this->display('addCategory');
    }
    
    /**
     * Author: yth 
     * 显示资讯栏目页面  
     * param  1：主页显示  2：搜索显示
     */
    public function categoryList(){
        $keywords = I('keywords');
        $size = 20;  
        $p = I('p');
        if($p == '') $p = 1;
        $where = '';
        $url = '/news/categoryList.html';
        if($keywords != ''){
            $where = " t1.typename like '%".$keywords."%'";
        } 
        $table = C('DB_PREFIX').'news_category as t1';
        $join1 = C('DB_PREFIX').'news_category as t2 on t1.parentid = t2.ncid';
        $join2 = C('DB_PREFIX').'news_category as t3 on t1.topid = t3.ncid'; 
        $field = "t1.*,t2.typename as parentname,t3.typename as topname";
        
        $count = M('news_category')->table($table)->join($join1,'LEFT')->join($join2,'LEFT')->where($where)->count(); 
        $list =  M('news_category')->table($table)->join($join1,'LEFT')->join($join2,'LEFT')->where($where)->field($field)->limit(($p-1)*$size,$size)->select(); 
        $this->keywords = $keywords;
        $this->page = getPageHtml($p,$count,$url,$size,$keywords);
        $this->dataList=$list; 
        $this->display('categoryList');
    }
            
    /*
     * 添加资讯栏目处理
     */
    public function AddCategoryHandle(){ 
         if(!IS_POST)  
            $this->error('页面不存在');
        $pid = (int)I('ncid',0,intval());
        if($pid < 0 || $pid > 10000000 ) 
            $this->error('页面不存在_ID不正确');
        
        $_POST['title'] = trim(I('title')); 
        if($_POST['title'] == '')
            $this->error('页面不存在_名称不能为空');
        //下面把中文的逗号  替换成英文的逗号
        $_POST['title'] = str_replace('，',',',$_POST['title']); 
        //分解
        $titles = explode(",", I('title')); 
        $size = count($titles); 
        //获取topid  一直循环查找其父id，直到父id为0  则其topid为此id
        $topid = 0;
        if($pid != 0){ 
            $id = $pid;
            while (true){
                $db = M('news_category')->where(array('ncid' => $id))->select();
                if($db[0]['parentid'] == 0){ 
                    $topid = $id;
                    break;
                }
                $id = $db[0]['parentid'];
            }
        }    
        //插入数据 
        if(I('fromtype') == 1){
            $msg = "添加了资讯栏目：";
            $dataList = array();
            //遍历数组，插入数据库
            for($i = 0;$i < $size ; $i++){
                $arr = array(
                    'typename' => $titles[$i],
                    'parentid' => $pid,
                    'topid'    => $topid, 
                    'weight'   => (int)I('weight',0,  intval()), 
                    'show'     => (int)I('radio') ); 
                $msg .= " ".$arr['typename'].','; 
                $dataList[] = $arr;
            }
            $firstID = M('news_category')->addAll($dataList);
            if($firstID > 0){
                $msg .= "  第一个ID为：".$firstID;
                D("Adminaction")->actionLog($msg);
                redirect('/news/categoryList.html'); 
            }
            echo "插入数据库错误，AddCategoryHandle（）";die;
            
        }else{
            //修改数据
            $arr = array(
                    'typename' => $titles[0],
                    'parentid' => $pid,
                    'topid'    => $topid, 
                    'weight'   => (int)I('weight',0,  intval()), 
                    'show'     => (int)I('radio'),
            );
            $ncid = I('fromid'); 
            if(M('news_category')->where("ncid = $ncid")->save($arr) === false)
            {
                echo "修改栏目 数据库错误，AddCategoryHandle（）";die;
            }
            D("Adminaction")->actionLog("修改了资讯栏目：".$arr['typename']."  ID:".$ncid);
            redirect('/news/categoryList.html'); 
        }
    }
    
    /**
     * Author: yth <linzujin@juwang.com>
     * Desc: 异步获取数据
     */
    public function getAjax() {
        //if (!IS_AJAX) {return;}     
        $user = M('news_category');
        $step = I('post.act');
        $id = (int) I('post.id');
        switch ($step) {
            //修改记录
            case 'modify':{
                $str = array( 
                    'weight' => I('post.weight',0,'intval') ,
                    'show' => I('post.show',0,'intval'),
                );
                if($user->where("ncid = $id")->setField($str))
                {
                    $str['status'] = 1;
                }  else {
                    $str['status'] = 0;
                }
                $this->ajaxReturn($str,'json');
                break;
            }
            //删除记录
            case 'del':{
                if($user->where("ncid = $id")->setField('status',-1))
                {
                    $str['status'] = 1;
                }  else {
                    $str['status'] = 0;
                }
                $this->ajaxReturn($str,'json');
                break;
            }
            //阅读  点击次数增加
            case 'reading':{
                $str['id1'] = D('News')->NewsIncreaseClicks($id);
                $str['id2'] = D('News')->NewsUpdatetime($id);
                $str['status'] = 1;
                $str['id'] = $id;
                $this->ajaxReturn($str,'json');
            }
            //获取子类别
            case 'getchild':{
                $str = $user->where("parentid = $id and `show` = 1")->select();
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //资讯删除数据-多id
            case 'deleteDatabyIDs':{
                $str = I('post.selectedIDs');
                M('news')->where(array('nid'=>array('in',$str)))->delete();
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //资讯删除数据-单id
            case 'deleteDatabyID':{
                $id = I('post.selectedID');
                $str = '-1';
                $pdata = M('news')->where("nid = $id")->find();
                if($pdata && M('news')->where("nid = $id")->setField('status',-1))
                {
                    $str = '0';
                    $msg = "删除了资讯：".$pdata['title']."   ID:".$id;
                    D("Adminaction")->actionLog($msg);
                } 
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //栏目删除数据-单id
            case 'deleteCategorybyID':{ 
                $str = D("News")->deleteCategorybyID(I('post.selectedID'));
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //栏目删除数据-多id
            case 'deleteCategorybyIDs':{
                $str = I('post.selectedIDs');
                M('news_category')->where(array('ncid'=>array('in',$str)))->delete();
                $this->ajaxReturn($str,'json'); 
                break;
            }
            //判断资讯名称是否合法
            case 'checkNewsCagetoryName':{
                $str = D("News")->checkCagetoryNameValid($_POST);
                $this->ajaxReturn($str,'json');
                break;
            }

        }//switch
   }//getAjax()
   
   //上传图片方法
    private function _uploadimg($file) {
        $upload = new \Think\Upload(); // 实例化上传类    
        $upload->maxSize = 3145728; // 设置附件上传大小    
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型    
        $upload->savePath = '/Pic/News/'; // 设置附件上传目录    // 上传单个文件
        $info = $upload->uploadOne($file);
        if (!$info) {// 上传错误提示错误信息        
            $this->error($upload->getError());
        } else {
            // 上传成功 获取上传文件信息
            return $info['savepath'] . $info['savename'];
        }
    }
}