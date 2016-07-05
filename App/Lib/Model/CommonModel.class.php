<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/


class CommonModel extends Model {
	protected $_auto	 =	 array(
		array('is_del','0',self::MODEL_INSERT),
		array('create_time','time',self::MODEL_INSERT,'function'),
		array('update_time','time',self::MODEL_UPDATE,'function'),
		array('user_id','get_user_id',self::MODEL_INSERT,'callback'),
		array('user_name','get_user_name',self::MODEL_INSERT,'callback'),
		);
	
	function get_user_id(){
		$user_id = session(C('USER_AUTH_KEY'));
		return isset($user_id) ? $user_id : 0;
	}

	function get_user_name(){
		$user_name = session('user_name');
		return isset($user_name) ? $user_name : 0;
	}
}
?>