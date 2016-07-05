<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class NoticeAction extends CommonAction {

	protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','sign'=>'read','mark' => 'admin', 'upload' => 'write'));

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['name'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);
		
		$arr_read = explode("_", get_user_config("readed_notice"));
		$arr_readed_notice = array();
		$arr_readed_id = array();
		foreach ($arr_read as $key => $val) {
			$tmp = explode("|", $val);
			$notiec_id = $tmp[0];
			$create_time = $tmp[1];
			if ($create_time >= time() - 3600 * 24 * 30) {
				$arr_readed_notice[] = $val;
				$arr_readed_id[] = $notiec_id;
			}
		}

		$this -> assign("readed_id", $arr_readed_id);

		$user_id = get_user_id();
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id());
		$map['folder']=array("in",$folder_list);
		
		$model = D("NoticeView");
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
		return;
	}

	public function mark() {
		$action = $_REQUEST['action'];
		$id = $_REQUEST['id'];
		switch ($action) {
			case 'del' :
				$where['id'] = array('in', $id);
				$folder = M("Notice") -> distinct(true) -> where($where) -> field("folder") -> select();
				if (count($folder) == 1) {
					$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
					if ($auth['admin'] == true) {
						$field = 'is_del';
						$result = $this -> _set_field($id, $field, 1);
						if ($result) {
							$this -> ajaxReturn('', "删除成功", 1);
						} else {
							$this -> ajaxReturn('', "删除失败", 0);
						}
					}
				} else {
					$this -> ajaxReturn('', "删除失败", 0);
				}
				break;
			case 'move_folder' :
				$target_folder = $_REQUEST['val'];
				$where['id'] = array('in', $id);
				$folder = M("Notice") -> distinct(true) -> where($where) -> field("folder") -> select();
				if (count($folder) == 1) {
					$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
					if ($auth['admin'] == true) {
						$field = 'folder';
						$this -> _set_field($id, $field, $target_folder);
					}
					$this -> ajaxReturn('', "操作成功", 1);
				} else {
					$this -> ajaxReturn('', "操作成功", 1);
				}
				break;

			//增加签收
			default :
				break;
		}
	}

	function sign(){
		$user_id = get_user_id();
		$id = $_REQUEST['id'];
		
		$model = M("Notice");
		$folder_id = $model -> where("id=$id") -> getField('folder');

		$Form = D('Notice_sign');
		$data['notice_id']  =   $id;
		$data['user_id']    =   $user_id;
		$data['folder']     =   $folder_id;
		$data['user_name']  =   get_user_name();
		$data['is_sign']    =   '1';
		$data['sign_time']  =   time();
		$result=$Form->add($data);
		if($result){
			$this ->ajaxReturn('', "签收成功",1);
		}else{
			$this ->ajaxReturn('', "签收失败",0);
		}
	}

	function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$fid = $_REQUEST['fid'];
		$this -> assign('folder', $fid);
		$this -> display();
	}

	public function edit() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this -> _edit();
	}

	public function read() {
		$id = $_REQUEST['id'];
		$this -> _readed($id);
		$user_id = get_user_id();
		$model = M("Notice");
		$folder_id = $model -> where("id=$id") -> getField('folder');		
		$this -> assign("auth", $auth = D("SystemFolder") -> get_folder_auth($folder_id));
			//获得已经签收人员名字
		$User = M('Notice_sign');
		$signlist = $User->where("notice_id=$id")->select();
		$this->assign('signlist',$signlist);
			
		$signok = $User->where("notice_id=$id and user_id=$user_id and is_sign=1")->select();
		$this->assign('is_sign',count($signok));

		$this -> _edit();
	}


	public function folder() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$arr_read = array_filter(explode("_", get_user_config("readed_notice")));
		$arr_readed_notice = array();
		$arr_readed_id = array();
		foreach ($arr_read as $key => $val) {
			$tmp = explode("|", $val);
			$notiec_id = $tmp[0];
			$create_time = $tmp[1];
			if ($create_time >= time() - 3600 * 24 * 30) {
				$arr_readed_notice[] = $val;
				$arr_readed_id[] = $notiec_id;
			}
		}
		$this -> assign("readed_id", $arr_readed_id);
				
		$model = D("Notice");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$folder_id = $_REQUEST['fid'];
		$this -> assign("folder_id", $folder_id);

		$map['folder'] = $folder_id;
		if (!empty($model)) {
			$this -> _list($model, $map);
		}

		$this -> assign("folder_name", D("SystemFolder") -> get_folder_name($folder_id));
		$this -> assign('auth', $this -> config['auth']);
		$this -> _assign_folder_list();

		$this -> display();
	}

	public function upload() {
		$this -> _upload();
	}

	public function down() {
		$this -> _down();
	}

	private function _readed($id) {
		$arr_read = array_filter(explode(",", get_user_config("readed_notice")));
		$arr_readed_notice = array();
		foreach ($arr_read as $key => $val) {
			$tmp = explode("|", $val);
			$create_time = $tmp[1];
			if ($create_time > time() - 3600 * 24 * 30) {
				$arr_readed_notice[] = $val;
			}
		}

		$readed_notice = implode("_", $arr_readed_notice);
		$read_notice = M("Notice") -> field("id,create_time") -> find($id);
		if ($read_notice['create_time'] > time() - 3600 * 24 * 30) {
			$read_notice_str = $read_notice['id'] . "|" . $read_notice['create_time'] . "_";
			$readed_notice = str_replace($read_notice_str, "", $readed_notice);
			trace($readed_notice);
			$readed_notice .= $read_notice_str;
			trace($readed_notice);
			M("UserConfig") -> where(array('eq', get_user_id())) -> setField('readed_notice', $readed_notice);
		}
	}	

}
