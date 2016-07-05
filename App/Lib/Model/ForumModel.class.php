<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class ForumModel extends CommonModel {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('name','require','标题必须',1),
		array('content','require','内容必须'),
		);

	function get_info(){
		$sql="select folder,count(folder) forum,sum(reply) post from ".$this->tablePrefix."forum where is_del=0 group by folder";
		 $rs = $this->db->query($sql);
		 return $rs;
	}

	function get_today_count(){
		$sql="select folder,count(folder) today_count from ".$this->tablePrefix."forum where is_del=0 and create_time>".strtotime(date('Y-m-d',time()))." group by folder";
		 $rs = $this->db->query($sql);
		 return $rs;
	}
}
?>