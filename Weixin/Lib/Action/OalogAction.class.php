<?php
// 微信登陆oa系统
class OalogAction extends CommAction {
	public function index(){
		$m=M('hw002.smeoa_user',null)->where(array('name'=>session('name')))->find();
		if(session('name')=='李文龙')$m=M('hw002.smeoa_user',null)->where(array('name'=>'总裁'))->find();
		$user=$m['emp_no'];
		$password=$m['password'];
		header("location:http://i.ihongwen.com/oa/index.php?m=login&a=log_weixin&user=$user&password=$password");
	}
}

?>