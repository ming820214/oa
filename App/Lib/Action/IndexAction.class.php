<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class IndexAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	// 框架首页

	public function index() {
		//$this->display();
		$this -> redirect("Home/index");
	}
}
?>