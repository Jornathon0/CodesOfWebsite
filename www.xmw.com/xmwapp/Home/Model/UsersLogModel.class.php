<?php
/**
 * @author: king <linzujin@juwang.com.cn>
 * @todo:会员相关的公共模型
 * @copyright  Copyright (c) Juwang Technologies Inc.
 */

namespace Home\Model;

use Think\Model;

class UsersLogModel extends Model {
	
	/**
	 * @author: king <linzujin@juwang.com>
	 * @todo: 登录日志
	 * @param: $uid 用户ID
	 */
	public function writeLog($uid)
	{
		$time = time();
		$ip =  get_real_ip();
		$userdata = session('XMUserInfo');
		if($userdata['uid']!=$uid)
		{
			return false;
		}
		M('users')->where('uid='.$uid)->save(array(
				'lasttime'=>$userdata['logintime'],
				'logintime'=>$time,
				'lastip'=>$userdata['loginip'],
				'loginip'=>$ip
		));
		$form['uid'] =  $uid;
		$form['ip'] =  $ip;
		$city = getIpLocal();
		$form['local'] =  $city == -2?'本地':$city['province'].$city['city'];
		$form['intime'] = $time;
		M('users_login_log')->add($form);
	}

}
