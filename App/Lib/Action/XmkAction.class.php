<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class XmkAction extends CommonAction {	
	protected $config = array('app_type' => 'folder', 'action_auth' => array('mark' => 'admin','save_report'=>'write','edit_report'=>'write','reply_report'=>'write','del_report'=>'admin'));
	
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['64'])) {
			$map['name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index(){
		$widget['date'] = true;		
		$this -> assign("widget", $widget);
									
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
				
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id());
		$map['folder']=array("in",$folder_list);
		$model = D("XmkView");
				
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
	}

	public function folder(){
		$widget['date'] = true;		
		$this -> assign("widget", $widget);
		$this -> assign('auth', $this -> config['auth']);
		
		$model = D("XmkView");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$folder_id = $_REQUEST['fid'];
		$map['folder'] = $folder_id;
		if (!empty($model)) {
			$this -> _list($model, $map);
		}

		$where = array();
		$where['id'] = array('eq', $folder_id);

		$folder_name = M("SystemFolder") -> where($where) -> getField("name");
		$this -> assign("folder_name", $folder_name);
		$this -> assign("folder", $folder_id);

		$this -> _assign_folder_list();
		$this -> display();
		return;
	}

	public function add(){
		$widget['editor'] = true;
		$widget['date'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);		
		
		$fid = $_REQUEST['fid'];
		$type = D("SystemFolder") -> where("id=$fid") -> getField("folder");
		$this -> assign('folder', $fid);
		$this -> display();
	}

	public function read() {
		$widget['editor'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);

		$id = $_REQUEST['id'];		
		$model = M("Xmk");
		$folder_id = $model -> where("id=$id") -> getField('folder');
		$this -> assign("auth", D("SystemFolder") -> get_folder_auth($folder_id));



		$model=M("XmkReport");
		$where['xid']=array('eq',$id);
		$where['is_del']=array('eq','0');
		$xmk_report=$model->where($where)->select();
		$this->assign("xmk_report",$xmk_report);
		
		$model = D("XmkView");
		$id=$_REQUEST['id'];
		$vo = $model -> getById($id);

		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "", 0);
			} else {
				die ;
			}
		}
		$this -> assign('vo', $vo);
		$this -> display();
	}

	public function edit() {
		$widget['editor'] = true;
		$widget['date'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);

		$id = $_REQUEST['id'];		
		$model = M("Xmk");
		$folder_id = $model -> where("id=$id") -> getField('folder');
		$this -> assign("auth", D("SystemFolder") -> get_folder_auth($folder_id));				

		$model = D("XmkView");
		$id=$_REQUEST['id'];
		$vo = $model -> getById($id);

		if ($this -> isAjax()) {
			if ($vo !== false) {// 读取成功
				$this -> ajaxReturn($vo, "", 0);
			} else {
				die ;
			}
		}
		$this -> assign('vo', $vo);
		$this -> display();

	}

	public function mark(){
		$action = $_REQUEST['action'];
		$id = $_REQUEST['id'];
		if (!empty($id)) {
			switch ($action){
				case 'del' :
					$where['id'] = array('in', $id);
					$folder = M("Xmk") -> distinct(true) -> where($where) -> field("folder") -> select();
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
					$folder = M("Xmk") -> distinct(true) -> where($where) -> field("folder") -> select();
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
				default :
					break;
			}
		}
	}

	function add_report(){
		$this->display();
	}

	function edit_report(){		
		$widget['editor'] = true;
		$widget['uploader'] = true;
		$this -> assign("widget", $widget);		

		$report_id=$_REQUEST['report_id'];
		$xid=M("XmkReport")->where("id=$report_id")->getField("xid");
		$fid=M("Xmk")->where("id=$xid")->getField("folder");
		$this->assign("fid",$fid);
		$this->_edit("XmkReport",$report_id);
	}

	function reply_report(){
		$this->edit_report();
	}

	function save_report(){
		$this->_save("XmkReport");
	}

	function del_report(){
		$report_id=$_REQUEST['report_id'];
		$this->_del($report_id,"XmkReport");
	}

	function tag_manage() {
		$this -> _tag_manage("标签管理");
	}

	function upload() {
		$this -> _upload();
	}

	function down() {
		$this -> _down();
	}
}
