<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class UserTagAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
		
	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['code|name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		if ($_POST){
			$opmode = $_POST["opmode"];
			$model = D("UserTag");
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			if ($opmode == "add") {
				$model -> module = MODULE_NAME;
				$model->user_id=get_user_id();
				$list = $model -> add();
			}
			if ($opmode == "edit") {
				$model->user_id=get_user_id();
				$list = $model -> save();
			}
			if ($opmode == "del") {
				$model->user_id=get_user_id();
				$tag_id = $model -> id;
				$model -> del_tag($tag_id);
			}
		}
		
		$model = D("UserTag");
		$tag_list = $model -> get_tag_list("id,pid,name");
		$tree = list_to_tree($tag_list);
		$this -> assign('menu', sub_tree_menu($tree));

		$tag_list = $model -> get_tag_list();
		$this -> assign("tag_list", $tag_list);
		$this -> assign('js_file',"UserTag:js/index");
		$this -> display('UserTag:index');
	}

	function winpop() {
		$model = M("UserTag");
		$module = $_GET['module'];
		$where['module'] = array('eq', $module);
		$menu = $model -> where($where) -> field('id,pid,name') -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> display();
	}

	function popup() {
		$model = M("UserTag");
		$module = $_GET['module'];
		$where['module'] = array('eq', $module);
		$list = array();
		$list = $model -> where($where) -> field('id,pid,name') -> order('sort asc') -> select();
		$list = list_to_tree($list);
		$this -> assign('list_popup', sub_tree_menu($list));
		$this -> display();
		return;
	}
}
?>