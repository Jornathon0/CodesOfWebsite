<?php

/**
 * XiongZhang [ WE ARE No.1 ]
 * @author     zhengni <zhengni@juwang.com.cn>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class DomainController extends CommonController {

    public function index() { 
        $domain=M('domain a');
        $tag=M('tag');
        $agent=M('agents');
        $suffix= C('SUFFIX');//域名后缀下拉检索条件
        $p = I('p');//获取页码
        $extend=I('extend');//获取后缀条件
        $tagtype=I('tagtype');//获取标签条件
        $starttime=I('start');//获取开始时间检索条件
        $endtime=I('end');//获取结束时间检索条件
        $checked=I('checked');
        //var_dump($end);exit;
        $keywords=I('keywords');//获取检索条件
        $size = 20; 
        if($p == '')$p = 1;
        $type= I('type',0,'intval');
        $where = "a.status > 0";
        if($checked){
            $where.=" and checked = '".$checked."'";
        }
        if($type && $type<>0){
            $where.=" and type = '".$type."'";
        }
        if($extend){
            $where.=" and extend = '".$extend."'";
        }
        if($tagtype){
            $where.=" and tagid = '".$tagtype."'";
            $tsql="id = {$tagtype}";
            $tagname=$tag->where($tsql)->getField("title");
            $this->tagname=$tagname;
        }
        if($keywords){
            $where .= " And ( a.title like '%".$keywords."%' or a.remark like '%".$keywords."%' or b.uname like '%".$keywords."%')"; 
        }
        if($starttime && $endtime && $starttime <= $endtime){
            $where .= " and intime >= ".(strtotime($starttime))." and intime <=".(strtotime($endtime)+24*60*60);
        }

        $join = C('DB_PREFIX')."users as b on b.uid = a.uid";
        $list =$domain->table($table)->join($join,"LEFT")->join($join2,"LEFT")->where($where)->field($field)->order('intime desc')->limit(($p-1)*$size,$size)->select(); 
        foreach ($list as $k => $v) {
            $list[$k]['agentname']=$agent->where("agid={$v['agid']}")->getField("name");
        }
        //echo $domain->getLastSql();exit;
        $taglist=$tag->select();
        $this->taglist=$taglist;
        $this->data=$list;
        $this->type=$type;
        $this->extendname=$extend;//==0?全部:$extend;
        $this->keywords=$keywords;
        $this->suffix=$suffix;
        $this->starttime=$starttime;
        $this->endtime=$endtime;
        $this->checked=$checked;
        $this->display();
    }
       /**
     * Author: zhengni<zhengni@juwang.com.cn>
     * Desc: 议价管理列表
     */
    public function bargain(){
        $this->display();
    }
    public function addHandle() { 
        if(!IS_POST)  
        $this->error('页面不存在');
        $type=$_POST['type'];
        $agent=$_POST['agent'];
        $did=$_POST['did'];
        $domain=M('domain a');
        $agents=M('agents c');
        $users=M('users b');
        //加一些验证的  
        $field="a.uid,a.type,b.agid";
        $join1 =C('DB_PREFIX')."users as b on a.uid=b.uid";
        $join2 =C('DB_PREFIX')."agents as c on b.agid=c.agid";
        $list=$domain->field($field)->join($join1)->join($join2)->where("did={$did}")->find();

        $arr1=array(
            'agid'=>$agent
            );
        $arr2=array(
            'type'=>$type
            );
        //修改专属经纪人
        if($list['agid']!==$agent){
          $check1=$users->where("uid={$list['uid']}")->save($arr1);
        }
        //修改域名展示类型
        if($list['type']!==$type){
          $check2=$domain->where("did={$did}")->save($arr2);
        }
        if($check1 && $check2){
            D("Adminaction")->actionLog("修改域名信息成功");
            echo "修改域名信息成功";
            redirect('/Domain/index.html');
        }
        elseif(!$check1 && $check2){
            D("Adminaction")->actionLog("修改了域名展示类型");
            echo "修改域名展示类型成功";
            redirect('/Domain/index.html');
        }
        elseif($check1 && !$check2){
            D("Adminaction")->actionLog("修改了专属经纪人");
            redirect('/Domain/index.html');
        }
        else{
             echo "修改域名信息失败，原因可能是插入数据库错误addHandle() ";die;    
        }
        
    }
     /**
     * Author: zhengni<zhengni@juwang.com.cn>
     * Desc: 修改域名信息
     */
    public function edit(){
        $domain=M('domain a');
        $agents=M('agents c');
        $join =C('DB_PREFIX')."users as b on a.uid=b.uid";
        $join2 =C('DB_PREFIX')."agents as c on b.agid=c.agid";
        $did = (int) I('did');//获取要修改的域名id
        $this->did=$did;
        $field="a.*,b.*,c.agid,c.name,c.type as atype";
        $list=$domain->field($field)->join($join)->join($join2)->where("did={$did}")->find();
        $agentslist=$agents->where("status='1' and type<3")->select();
    
        $this->data=$list;
        $this->agentslist=$agentslist;
        $this->display();
    }
       /**
     * Author: zhengni<zhengni@juwang.com.cn>
     * Desc: 异步获取数据
     */
    public function getAjax() {
        if (!IS_AJAX) {return;}     
        $domain = M('domain');
        $table = C('DB_PREFIX').'users as a';
        $step = I('post.act');
        $did = (int) I('post.did');//获取要修改的域名id
        $hot=(int)I('post.hot');
        $recommend=(int)I('post.recommend');
        $sql="did={$did}";
        switch ($step) {
            //设置、取消火热标识
            case 'sethot':{ 
                $check=$domain->where($sql)->setField("hot", $hot);
                if($check){
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');  
                break;
            }
            //设置、取消推荐
            case 'setrec':{ 
               $check=$domain->where($sql)->setField("recommend",$recommend);
               if($check){
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');    
                break;
            }
            //人工验证域名
            case 'verifydomain':
                $value=(int)I('post.shenhetype');
                $reason=empty(I('post.reason'))?'':I('post.reason');
                $data=array(
                    'checked'=>$value,
                    'reason'=>$reason
                    );
                $check=$domain->where($sql)->save($data);
                if($check){
                   // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
                    //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');    
                break;
            case 'deletedomain':
                $check=$domain->where($sql)->setField("status", 0);
                if($check){
                    $str = 'ok';
                }
                else{
                    $str = 'fail';
                }
                $this->ajaxReturn($str,'json');  
                break;
            //修改推荐状态
            case 'set_rec':
            $value=(int)I('post.value');
            $check=$domain->where($sql)->setField("recstatus", $value);
            if($check){
                   // D("Message")->sendSystemMsg($id,'会员手机/邮箱解绑成功！');
                    //D('Admin_action')->actionLog("会员手机/邮箱解绑成功，  会员ID:".$id);
                    $str = 'ok';
            }
            else{
                    $str = 'fail';
            }
                $this->ajaxReturn($str,'json');   
                break;
           
        }//switch
   } 
}
