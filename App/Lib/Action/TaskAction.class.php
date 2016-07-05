<?php
class TaskAction extends CommonAction{
    protected $config = array('app_type' => 'personal');
//	任务管理直接调用微信页面
	public function task(){
		$this->show("<layout name='Layout/ace_layout'/><iframe src='/oa/weixin.php/task/index' width='900px' height='500px' frameborder='0' scrolling='auto'/></iframe>");
	}
	
	public function reply(){
		$this->show("<layout name='Layout/ace_layout'/><iframe src='/oa/weixin.php/task/reply' width='900px' height='500px' frameborder='0' scrolling='auto'/></iframe>");
	}
}
?>