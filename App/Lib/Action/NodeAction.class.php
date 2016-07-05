<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class NodeAction extends CommonAction {

	protected $config=array('app_type'=>'master','action_auth'=>array('node'=>'admin'));

	public function index(){
		$node = M("Node");		
		if (!empty($_POST['eq_pid'])) {
			$eq_pid = $_POST['eq_pid'];
		} elseif (!empty($_GET['eq_pid'])) {
			$eq_pid = $_GET['eq_pid'];
		} else {
			$eq_pid = $node -> where('pid=0') -> order('sort asc') -> getField('id');
		}
				
		$this -> assign('eq_pid', $eq_pid);
				
		$list = $node -> where('pid=0') -> order('sort asc') -> getField('id,name');
		$this -> assign('groupList', $list);
				
		$menu = array();
		$menu = $node -> field('id,pid,name') -> order('sort asc') -> select();
		$tree = list_to_tree($menu, $eq_pid);
		
		$model = M("Node");
		$list = $model -> order('sort asc') -> getField('id,name');
		$this -> assign('node_list', $list);
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> display();
	}

	protected function _insert(){
		$model = D('Node');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if(strpos($model->url,'##')!==false){			
			$model->sub_folder=ucfirst(get_module(str_replace("##","",$model->url)))."Folder";
		}else{
			$model->sub_folder='';
		}
		//保存当前数据对象
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			//失败提示
			$this -> error('新增失败!');
		}
	}

	protected function _update() {		
		$id = $_POST['id'];
		$model = D("Node");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if(strpos($model->url,'##')!==false){
			$model->sub_folder=ucfirst(get_module(str_replace("##","",$model->url)))."Folder";
		}else{
			$model->sub_folder='';
		}
		// 更新数据
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
	}
	
	function winpop() {
		$menu = D("Node") -> order('sort asc') -> select();
		$tree = list_to_tree($menu);
		$this -> assign('menu', popup_tree_menu($tree));
		$this -> display();
	}
	
	function del()
	{
		$node_id=$_POST['id'];
		
		$model = M("RoleNode");
		$where['node_id'] = $node_id;
		$model->where($where)->delete();		
		$this->_destory($node_id);				
	}	
}
?>