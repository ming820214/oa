<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class XmkModel extends CommonModel {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('name','require','文件名必须',1),
		array('content','require','内容必须'),
		);

	function _before_insert(&$data,$options){
        $sql = "SELECT CONCAT(year(now()),'-',LPAD(count(*)+1,4,0)) xm_no FROM `".$this->tablePrefix."xmk` WHERE 1 and year(FROM_UNIXTIME(create_time))>=year(now())";       
        $rs = $this->db->query($sql);
        if($rs){
            $data['xm_no']= $rs[0]['xm_no'];    
        }else{
            $data['xm_no']= date('Y')."-0001"; 
        }
	}
}	
?>