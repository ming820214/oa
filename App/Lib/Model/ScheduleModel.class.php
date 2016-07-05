<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


// 用户模型
class ScheduleModel extends CommonModel {

	function _after_insert($data,$options){

		$date=explode("-",$data["start_date"]);
		$time=explode(":",$data["start_time"]);
		$warn_time=$data["warn_time"];
		$id=$data['id'];
		$name=$data['name'];
		
		$year=$date[0];
		$month=$date[1];
		$day=$date[2];

		$hour=$time[0];
		$minute=$time[1];

		if ($warn_time!="none"){
			$tmp=explode("_",$warn_time);
			if ($tmp[0]="h"){
				$hour=$hour-$tmp[1];
			}
			if ($tmp[0]="m"){
				$minute=$minute-$tmp[1];
			}
			if ($tmp[0]="d"){
				$day=$day-$tmp[1];
			}
		}
		
		$time=mktime($hour,$minute,0,$month,$day,$year);
	}

	function _after_update($data,$options){

		$date=explode("-",$data["start_date"]);
		$time=explode(":",$data["start_time"]);
		$warn_time=$data["warn_time"];
		$id=$data['id'];
		$name=$data['name'];
		
		$year=$date[0];
		$month=$date[1];
		$day=$date[2];

		$hour=$time[0];
		$minute=$time[1];

		if ($warn_time!="none"){
			$tmp=explode("_",$warn_time);
			if ($tmp[0]="h"){
				$hour=$hour-$tmp[1];
			}
			if ($tmp[0]="m"){
				$minute=$minute-$tmp[1];
			}
			if ($tmp[0]="d"){
				$day=$day-$tmp[1];
			}
		}
		
		$time=mktime($hour,$minute,0,$month,$day,$year);
	}
}
	
?>