<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class UdfSalaryAction extends UdfAction {
	protected $config=array('app_type'=>'common','action_auth'=>array('mark'=>'admin'));	
	protected $first_row = 4; 
	protected $field_count =13; 
	//过滤查询字段
	function _search_filter(&$map){		
		if($this->config['auth']['admin']==false){
			$map['emp_no'] = array('eq',get_emp_no());
		}			
		if (!empty($_POST['keyword'])){
			$map['B|C'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index(){
		$this -> assign('auth',$this -> config['auth']);
		$this->_index();
	}
}
?>