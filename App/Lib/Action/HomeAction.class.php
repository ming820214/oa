<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class HomeAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	//过滤查询字段

	function _search_filter(&$map) {
		if (!empty($_POST['keyword'])) {
			$map['type|name|code'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	public function index() {
		if(!session('?date')){$date=date('Y-m');session('date',"$date");}
		
		$widget['jquery-ui'] = true;		
		$this -> assign("widget", $widget);
				
		cookie("current_node", null);
		cookie("top_menu", null);

		$config = D("UserConfig") -> get_config();
		$this -> assign("home_sort", $config['home_sort']);
		$this -> _mail_list();
		$this -> _flow_list();
		$this -> _schedule_list();
		$this -> _notice_list();
		$this -> _doc_list();
		$this -> _forum_list();
		$this -> display();
	}

	public function set_sort() {
		$val = $_REQUEST["val"];
		$data['home_sort'] = $val;
		$model = D("UserConfig") -> set_config($data);
	}

	protected function _mail_list() {
		$user_id = get_user_id();
		$model = D('Mail');

		//获取最新邮件
		$where['user_id'] = $user_id;
		$where['is_del'] = array('eq', '0');
		$where['folder'] = array( array('eq', 1), array('gt', 6), 'or');

		$new_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('new_mail_list', $new_mail_list);
	
		//获取未读邮件
		$where['read'] = array('eq', '0');
		$unread_mail_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign('unread_mail_list', $unread_mail_list);
	}

	protected function _flow_list(){
		$user_id = get_user_id();
		$emp_no = get_emp_no();
		$model = D('Flow');
		//带审批的列表
		$FlowLog = M("FlowLog");
		$where['emp_no'] = $emp_no;
		$where['_string'] = "result is null";
		$log_list = $FlowLog -> where($where) -> field('flow_id') -> select();
		$log_list = rotate($log_list);
		if (!empty($log_list)) {
			$map['id'] = array('in', $log_list['flow_id']);
			$todo_flow_list = $model -> where($map) -> field("id,name,create_time") -> limit(6)-> order("create_time desc")->select();
			$this -> assign("todo_flow_list", $todo_flow_list);
		}
		//已提交
		$map = array();
		$map['user_id'] = $user_id;
		$map['step'] = array('gt', 10);
		$submit_process_list = $model -> where($map) -> field("id,name,create_time") -> limit(6)->order("create_time desc")-> select();
		$this -> assign("submit_flow_list", $submit_process_list);
	}

	protected function _doc_list() {
		$user_id = get_user_id();
		$model = D('Doc');
		//获取最新邮件

		$where['is_del'] = array('eq', '0');
		$common_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("doc_list", $common_list);
	}

	protected function _schedule_list() {
		$user_id = get_user_id();
		$model = M('Schedule');
		//获取最新邮件
		$start_date = date("Y-m-d");
		$where['user_id'] = $user_id;
		$where['start_date'] = array('egt', $start_date);
		$schedule_list = M("Schedule") -> where($where) -> order('start_date,priority desc') -> limit(6) -> select();
		$this -> assign("schedule_list", $schedule_list);

		$model = M("Todo");
		$where = array();
		$where['user_id'] = $user_id;
		$where['status'] = array("in", "1,2");
		$todo_list = M("Todo") -> where($where) -> order('priority desc,sort asc') -> limit(6) -> select();
		$this -> assign("todo_list", $todo_list);
	}

	protected function _notice_list() {
		$model = D('Notice');
		//获取最新通知
		$where['is_del'] = array('eq', '0');
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id(),"NoticeFolder");
		$where['folder']=array("in",$folder_list);
		$new_notice_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("new_notice_list", $new_notice_list);
	}

	protected function _forum_list() {
		$model = D('Forum');
		$where['is_del'] = array('eq', '0');
		$folder_list=D("SystemFolder")->get_authed_folder(get_user_id(),"ForumFolder");
		$where['folder']=array("in",$folder_list);		
		$new_forum_list = $model -> where($where) -> field("id,name,create_time") -> order("create_time desc") -> limit(6) -> select();
		$this -> assign("new_forum_list", $new_forum_list);
	}

}
?>