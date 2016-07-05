<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class PostAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
	}

	public function edit(){
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this->_edit();
	}
	
	public function del(){
		$id = $_POST['id'];
		$user_id = get_user_id();
		$post_user_id = M("Post") -> where($where) -> getfield('user_id');
		if ($user_id == $post_user_id) {
			$field = "is_del";
			$this -> _set_field($id, $field, 1);
			$this -> ajaxReturn($arr, "删除成功", 1);
		} else {
			$this -> ajaxReturn($arr, "删除失败", 1);
		}
	}
}
