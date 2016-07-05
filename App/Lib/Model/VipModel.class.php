<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class VipModel extends CommonModel {
	protected $_validate	=	array(
		array('paper_no','check_paper_no','会员已经存在',0,'callback'),
	);

	public function check_paper_no() {
		$map['paper_no']	 =	 $_POST['paper_no'];
		$result	=	$this->where($map)->field('id')->find();
        if($result) {
        	return false;
        }else{
			return true;
		}
	}
}
?>