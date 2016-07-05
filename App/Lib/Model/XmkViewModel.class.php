<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class XmkViewModel extends ViewModel {
	public $viewFields=array(
		'Xmk'=>array('*'),
		'SystemFolder'=>array('name'=>'folder_name','_on'=>'Xmk.folder=SystemFolder.id'),
		'XmkLevel'=>array('_table'=>'system_config','_as'=>'XmkLevel','name'=>'level_name','_on'=>'Xmk.level=XmkLevel.val and XmkLevel.code=\'XMK_LEVEL\''),
		'XmkStatus'=>array('_table'=>'system_config','_as'=>'XmkStatus','name'=>'status_name','_on'=>'Xmk.status=XmkStatus.val and XmkStatus.code=\'XMK_STATUS\''),
		);
}
?>