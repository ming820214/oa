<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class UserConfigAction extends CommonAction {
	protected $config=array('app_type'=>'personal');
	public function index(){
		$config=M("UserConfig")->find(get_user_id());
		$this->assign("config",$config);
		$this -> display();
	}

	function save(){
		$config = M("UserConfig") -> find(get_user_id());		
		if (count($config)) {
			$this -> _update();
		} else {
			$this ->_insert();
		}
	}

	function _insert() {
		$model = M('UserConfig');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('id', $model -> getDbFields())) {
			$model -> id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
		
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

	function _update() {
		//B('FilterString');
		$model = M('UserConfig');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('id', $model -> getDbFields())) {
			$model -> id = get_user_id();
		};
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

}
?>