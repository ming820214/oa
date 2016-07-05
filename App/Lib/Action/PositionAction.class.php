<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class PositionAction extends CommonAction {
	protected $config=array(
		'app_type'=>'master'
		);

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['position_no|name'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}
	
	function del(){
		$id=$_POST['id'];
		$this->_destory($id);		
	}
}
?>