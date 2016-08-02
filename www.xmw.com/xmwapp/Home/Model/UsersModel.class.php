<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:会员相关的公共模型
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */

namespace Home\Model;

use Think\Model;

class UsersModel extends Model {

    /**
     * 更新用户SESSION
     */
	/*
    public static function init() {
        $userinfo = session('XZUserInfo');
        $uid = $userinfo['uid'];
        $user = M('user');

        $info = $user->where("uid = '{$uid}'")->find();
        session('XZUserInfo', $info);
    }
	*/
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:验证帐号是否存在
	 */
	public function checkUsersExiste($where='',$field = '*')
	{
		if(empty($where)) return NULL;
		return $this->field($field)->where($where)->find();
	}
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo:添加用户数据
	 */
	public function addUser($array = array()){
		if(empty($array)) return false;
		if($uid = $this->add($array))
		{
			//邮箱注册的
			if($array['email'])
			{
				$authnum = authnum();
				$post['uid'] =  $uid;
				$post['email'] =  $array['email'];
				$post['intime'] =  date('Y-m-d H:i:s');
				$post['verify'] =  $authnum;
				M('user_whois')->add($post);
				//$url = C('WEBSITE').'activation/'.$array['email'].'/'.md5($array['email'].$authnum);
				$url = 'http://'.$_SERVER['SERVER_NAME'].'/activation/'.$array['email'].'/'.md5($array['email'].$authnum);
				
				$html= '<p>亲爱的用户，您好！</p>
                        <p>请尽快点击下面的链接地址，完成邮箱验证操作：</p>
                        <p><a href="'.$url.'" target="_blank">'.$url.'</a></p>
                        <p class="mt20">(如果你无法点击此链接，请将上面的链接地址复制到你的浏览器地址栏中，打开页面即可)</p>';
				sendEmail($array['email'],'邮箱验证',$html);
			}
			
			//手机注册的 返回uid后直接登录
			if($array['mobile']){
				
			}
			//D('UsersLog')->writeLog($uid);
			return $uid;
		}
	}
    /**
     * 根据UID获取用户信息(单条信息)
     * @param int $uid
     * @return array
     */
    public function getUserById($uid) {
        $user = M("users");
        return $user->where("uid='{$uid}'")->find();
    }

    /**
     * 根据UID获取用户信息(多条信息)
     * @param array $uid_array
     * @param strint $field
     * @return array
     */
    public function getInfoById($uid_array, $field = '') {

        if (is_array($uid_array)) {
            $user = M("user");
            if (!empty($field)) {
                $user = $user->field($field);
            }
            $struid = implode(',', $uid_array);
            $where = 'uid in ('.$struid.')';
            //$map['uid'] = array('in', $uid_array);
            $info = $user->where($where)->select();
            $new_arr = array();
            if ($info) {
                foreach ($info as $v) {
                    $new_arr[$v['uid']] = $v;
                }
            }
            return $new_arr;
        }
    }
}
