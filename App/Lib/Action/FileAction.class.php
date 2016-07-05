<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class FileAction extends CommonAction {
	protected $config = array('app_type' => 'asst');
	//过滤查询字段

	function player() {
		$user_id = get_user_id();
		$this -> assign("user_id", $user_id);

		$file_id = $_GET['attach_id'];
		$file_id = f_decode($file_id);
		$add_file = explode(";", $add_file);
		$add_file = $add_file[0];
		$where['id'] = $file_id;
		$video_path = M("File") -> where($where) -> getField("savename");
		$video_ext = M("File") -> where($where) -> getField("extension");
		$this -> assign('video_file', get_save_path() . $video_path);
		$this -> assign('video_ext', $video_ext);
		$this -> display();
	}
}
