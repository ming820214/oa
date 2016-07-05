<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


class ContactModel extends CommonModel {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('name','require','姓名必须！',1),
		array('email','email','邮箱格式错误！',2),
		);
}
?>