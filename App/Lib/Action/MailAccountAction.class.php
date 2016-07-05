<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class MailaccountAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	public function index(){
		$mail_user = M("MailAccount") -> find(get_user_id());
		$this -> assign('mail_user', $mail_user);
		if (count($mail_user)) {
			$this -> assign('opmode', 'edit');
		} else {
			$this -> assign('opmode', 'add');
		}
		$this -> display();
	}

	protected function _set_email($email) {
		$model = M("User");
		$user_id = get_user_id();
		$data['id'] = $user_id;
		$data['email'] = $email;
		$model -> save($data);
	}

	protected function _insert() {
		$model = M('MailAccount');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('id', $model -> getDbFields())) {
			$model -> id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
		$email = $_POST['email'];
		//保存当前数据对象
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> _set_email($email);
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			//失败提示
			$this -> error('新增失败!');
		}
	}

	protected function _update() {

		$model = M('MailAccount');

		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('id', $model -> getDbFields())) {
			$model -> id = get_user_id();
		};
		// 更新数据
		$email = $_POST['email'];
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> _set_email($email);
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
	}

}
?>