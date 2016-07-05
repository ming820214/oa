<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class MailOrganizeAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	public function index() {
		$where["user_id"] = get_user_id();
		$list = M("MailOrganize") -> where($where) -> select();
		$this -> assign("list", $list);
		$this -> display();
	}

	function add() {
		$temp = R("Mail/_assign_mail_folder_list");
		$this -> assign('mail_folder', $temp);
		$this -> display();
	}

	function edit() {
		$model = D("UserFolder");
		$user_folder = $model -> get_folder_list("MailFolder");
		$system_folder = array( array("id" => 1, "name" => "收件箱"), array("id" => 2, "name" => "已发送"));
		if (!empty($user_folder)) {
			$mail_folder = array_merge($system_folder, $user_folder);
		} else {
			$mail_folder = $system_folder;
		}
		$this -> assign('folder_list', $mail_folder);
		$this -> _edit();
	}

	protected function _update() {
		$id = $_POST["id"];
		$model = M("MailOrganize");
		$model -> where("id=$id") -> delete();
		$model = D("MailOrganize");

		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name =get_user_name();
		};
		//保存当前数据对象
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//失败提示
			$this -> error('编辑失败!');
		}
	}

	function del() {
		$id = $_REQUEST["id"];
		$this -> _destory($id);
	}

}
?>