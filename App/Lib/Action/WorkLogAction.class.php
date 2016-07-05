<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class WorkLogAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('folder' => 'read', 'tag_manage' => 'admin', 'mark' => 'admin','read_emp' => 'admin'));
	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if(!empty($_POST['content'])){
			$where['content']=array('like','%'.$_POST['content'].'%');
			$where['plan']=array('like','%'.$_POST['content'].'%');
			$where['_logic']='or';
			$map['_complex'] = $where;
		}
	}

	public function index(){
		$widget['date'] = true;		
		$this ->assign("widget", $widget);
		$this->assign('user_id',get_user_id());				
		$this->assign("title",'日志查询');		

		$auth=$this->config['auth'];
		$this->assign('auth',$auth);		
		if($auth['admin']){
			$node = D("Dept");
			$dept_id=get_dept_id();	
			$dept_name=get_dept_name();
			$menu = array();
			$dept_menu = $node -> field('id,pid,name') ->where("is_del=0")-> order('sort asc') -> select();
			$dept_tree = list_to_tree($dept_menu,$dept_id);			
			$count=count($dept_tree);
			if(empty($count)){
				/*获取部门列表*/				
				$html ='';
				$html = $html . "<option value='{$dept_id}'>{$dept_name}</option>";
				$this -> assign('dept_list',$html);			
				/*获取人员列表*/
				$where['dept_id']=array('eq',$dept_id);
				$emp_list=D("User")->where($where)->getField('id,name');
				$this->assign('emp_list',$emp_list);			
			}else{
				/*获取部门列表*/								
				$this -> assign('dept_list', select_tree_menu($dept_tree));
				$dept_list=tree_to_list($dept_tree);
				$dept_list=rotate($dept_list);
				$dept_list=$dept_list['id'];
				
				/*获取人员列表*/
				$where['dept_id']=array('in',$dept_list);
				$emp_list=D("User")->where($where)->getField('id,name');
				$this->assign('emp_list',$emp_list);				
			}
		}
				
		$map = $this -> _search();
		if($auth['admin']){
			if(empty($map['dept_id'])){
				if(!empty($dept_list)){
					$map['dept_id']=array('in',array_merge($dept_list,array($dept_id)));
				}else{
					$map['dept_id']=array('eq',$dept_id);
				}				
			}
		}else{
			$map['user_id']=get_user_id();
		}

		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}
		
		$model = D("WorkLog");				
		if (!empty($model)) {
			$this -> _list($model,$map);
		}
		$this -> display();
	}

	public function edit() {
		$widget['date'] = true;	
		$widget['uploader'] = true;		
		$this -> assign("widget", $widget);
		$this->_edit();
	}

	public function add() {
		$widget['date'] = true;	
		$widget['uploader'] = true;		
		$this -> assign("widget", $widget);		
		$this->display();
	}
	
	function upload() {
		$this -> _upload();
	}

	function down() {
		$this -> _down();
	}

	/** 插入新新数据  **/
	protected function _insert() {
		$name = $this -> getActionName();
		$model = D($name);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};
		if (in_array('dept_id', $model -> getDbFields())) {
			$model -> dept_id = get_dept_id();
		};
		if (in_array('dept_name', $model -> getDbFields())) {
			$model -> dept_name = get_dept_name();
		};
		$model->create_time=time();
		/*保存当前数据对象 */
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}
}
