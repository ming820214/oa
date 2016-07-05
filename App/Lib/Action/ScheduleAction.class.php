<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ScheduleAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
	//过滤查询字段
	function _search_filter(&$map) {
		if (!empty($_POST["name"])) {
			$map['name'] = array('like', "%" . $_POST['name'] . "%");
		}
		$map['user_id'] = array('eq', get_user_id());
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST["start_date"])) {
			$map['start_date'] = array("egt", $_POST["start_date"]);
		}
		if (!empty($_POST["end_date"])) {
			$map['end_date'] = array("elt", $_POST["end_date"]);
		}
		$map['is_del'] = array('eq', '0');
	}

	public function upload() {
		$this -> _upload();
	}

	function read() {
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		$model = M('Schedule');
		$id = $_REQUEST['id'];
		$list = $_REQUEST['list'];
		$this -> assign("list", $list);
		$list = array_filter(explode("|", $list));
		$current = array_search($id, $list);

		if ($current !== false) {
			$next = $list[$current + 1];
			$prev = $list[$current - 1];
		}
		$this -> assign('next', $next);
		$this -> assign('prev', $prev);

		$where['id'] = $id;
		$where['user_id'] = get_user_id();

		$vo = $model -> where($where) -> find();
		$this -> assign('vo', $vo);
		$this -> display();
	}

	function search() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		if (empty($_POST["start_date"])) {
			$start_date = toDate(mktime(0, 0, 0, date("m"), 1, date("Y")), 'Y-m-d');
			$map['start_date'] = array("egt", $start_date);
		} else {
			$start_date = $_POST["start_date"];
		}
		if (empty($_POST["end_date"])) {
			$end_date = toDate(mktime(0, 0, 0, date("m") + 1, 0, date("Y")), 'Y-m-d');
			$map['end_date'] = array("elt", toDate(time(), 'Y-m-d'));
		} else {
			$end_date = $_POST["end_date"];
		}
		$this -> assign('start_date', $start_date);
		$this -> assign('end_date', $end_date);

		$model = D("Schedule");

		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> assign('type_data', $this -> type_data);
		$this -> display();
		return;
	}

	public function down() {
		$this -> _down();
	}

	public function add() {
		$widget['jquery-ui'] = true;
		$widget['date'] = true;	
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$this -> display();
	}

	public function edit() {
		$widget['jquery-ui'] = true;
		$widget['date'] = true;		
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$id = $_REQUEST['id'];
		$model = M('Schedule');
		$where['user_id'] = get_user_id();
		$where['id'] = $id;
		$vo = $model -> where($where) -> find();

		$vo['start_time'] = fix_time($vo['start_time']);
		$vo['end_time'] = fix_time($vo['end_time']);

		$this -> assign('vo', $vo);
		$this -> display();
	}

	public function day_view() {
		$this -> index();
	}

	public function read2(){
		$this -> read();
	}
	
	public function del(){
		$this->_del();
	}

	function json() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Content-Type:text/html; charset=utf-8");
		$user_id = get_user_id();
		$start_date = $_REQUEST["start_date"];
		$end_date = $_REQUEST["end_date"];

		$where['user_id'] = $user_id;
		$where['is_del']=array('eq',0);
		$where['start_date'] = array( array('egt', $start_date), array('elt', $end_date));
		$list = M("Schedule") -> where($where) -> order('start_date,priority desc') -> select();
		exit(json_encode($list));
	}

}
?>