<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class NavAction extends CommonAction {

	protected $config = array('app_type' => 'master', 'action_auth' => array('Nav' => 'admin'));
	public function index() {
		$model = M("Nav");
		$list = $model -> where('pid=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('groupList', $list);

		if (!empty($_POST['eq_pid'])) {
			$eq_pid = $_POST['eq_pid'];
		} elseif (!empty($_GET['eq_pid'])) {
			$eq_pid = $_GET['eq_pid'];
		} else {
			$eq_pid = $Nav -> where('pid=0') -> order('sort asc') -> getField('id');
		}

		$this -> assign('eq_pid', $eq_pid);
		$menu = array();

		$menu = $Nav -> field('id,pid,name') -> order('sort asc') -> select();
		$tree = list_to_tree($menu, $eq_pid);

		$model = M("Nav");
		$list = $model -> order('sort asc') -> getField('id,name');
		$this -> assign('Nav_list', $list);
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> display();
	}

	function winpop() {
		$menu = D("Nav") -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> display();
	}

}
?>