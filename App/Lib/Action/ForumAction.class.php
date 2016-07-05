<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
-------------------------------------------------------------------------*/

class ForumAction extends CommonAction {
	protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','save_post' => 'write', 'edit_post' => 'write', 'del_post' => 'admin', 'mark' => 'admin', 'upload' => 'write'));
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['fid'])) {
			$map['folder'] = $_REQUEST['fid'];
		}
		if (!empty($_REQUEST['keyword']) && empty($map['name'])) {
			$keyword = $_POST['keyword'];
			$where['name'] = array('like', "%" . $keyword . "%");
			$where['content'] = array('like', "%" . $keyword . "%");
			$where['user_name'] = array('like', "%" . $keyword . "%");
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
	}

	public function index(){
		$model=D("SystemFolder");
		$forum_list=$model->get_folder_list("","id,pid,name,admin");
		$this->assign("forum_list",$forum_list);

		$model=D("Forum");
		$forum_info=$model->get_info();

		$temp=array();
		foreach($forum_info as $item){
			$temp[$item['folder']]=$item;
		}
		$forum_info=$temp;
		$this->assign("forum_info",$forum_info);

		$today_count=$model->get_today_count();
		$temp=array();
		foreach($today_count as $item){
			$temp[$item['folder']]=$item;
		}
		$today_count=$temp;
		$this->assign("today_count",$today_count);		

		$this -> display();
	}

	public function newly() {
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = D("Forum");
		if (!empty($model)) {
			$this -> _list($model,$map);
		}
		$this -> display();
	}

	public function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$this -> assign('folder', $_REQUEST['fid']);
		$this -> display();
	}

	public function edit() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this -> _edit();
	}

	public function read() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget",$widget);

		$this -> assign('auth', $this -> config['auth']);

		$model = M("Forum");

		$id = $_REQUEST['id'];
		$where['id'] = array('eq', $id);

		$folder_id = $_REQUEST['fid'];
		if(!empty($folder_id)){
			$where['folder'] = array('eq', $folder_id);
		}
		$forum = $model -> where($where) -> find();
		$this -> assign('forum',$forum);

		$id = $_REQUEST['id'];
		$user_id = get_user_id();
		$user['user_id'] = $user_id;

		$this -> assign('user', $user);
		$this -> assign('user_id',$user_id);

		$model = M("Forum");
		$model -> where("id=$id") -> setInc('views', 1);

		$model = M("Forum");

		$where = array();
		$where['tid'] = $id;
		$where['is_del'] = 0;

		$model = M("Post");

		if (!empty($model)) {
			$this -> _list($model, $where, "id", true);
		}

		$this -> assign("tid", $id);
		$this -> display();
	}

	public function folder() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$this -> assign('auth', $this -> config['auth']);
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		$model = M("Forum");
		$sortby="is_top desc,id desc";
		if (!empty($model)) {
			$this -> _list($model, $map,$sortby);
		}
		$where = array();
		$folder_id = $map['folder'];
		$where['id'] = array('eq', $folder_id);
		$folder_name = M("SystemFolder") -> where($where) -> getField("name");
		$this -> assign("folder_name", $folder_name);

		$this -> _assign_folder_list('/forum/folder/');
		$this -> assign("folder_id", $folder_id);
		$this -> display();
		return;
	}

	public function mark() {
		$action = $_REQUEST['action'];
		$id = $_REQUEST['id'];
		if (!empty($id)) {
			switch ($action) {
				case 'del' :
					$where['id'] = array('in', $id);
					$folder = M("Forum") -> distinct(true) -> where($where) -> field("folder") -> select();
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
					$folder = M("Forum") -> distinct(true) -> where($where) -> field("folder") -> select();
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
				case 'is_top':
					$where['id'] = array('in', $id);
					$folder = M("Forum") -> distinct(true) -> where($where) -> field("folder,is_top") -> select();
					if (count($folder) == 1) {
						$auth = D("SystemFolder") -> get_folder_auth($folder[0]["folder"]);
						if ($auth['admin'] == true) {
							$field = 'is_top';
							if($folder[0]['is_top']==0){
							$result = $this -> _set_field($id, $field, 1);
							}else{
							$result = $this -> _set_field($id, $field, 0);
							}
							
							if ($result) {
								$this -> ajaxReturn('', "操作成功", 1);
							} else {
								$this -> ajaxReturn('', "操作失败", 0);
							}
						}
					} else {
						$this -> ajaxReturn('', "操作失败", 0);
					}
					break;
				default :
					break;
			}
		}
	}

	public function save_post() {
		R("post/save");
	}

	public function edit_post() {
		R("post/edit");
	}

	public function del_post() {
		R("post/del");
	}

	public function upload() {
		$this -> _upload();
	}

	public function down() {
		$this -> _down();
	}
}
