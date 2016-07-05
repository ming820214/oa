<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


// 用户模型
class TagModel extends CommonModel {
	public function get_list($module=MODULE_NAME,$public=null){
		$where['module']=$module;
		if(empty($public)){
			$user_id=get_user_id();
			$where['user_id']=$user_id;
		}
		$field="id,name";
		$list=$this->where($where)->getfield($field);
		return $list;
	}

	public function get_data_list($module=MODULE_NAME,$tag_id=null){
		$model=M("TagData");
		$user_id=get_user_id();
		$where="tag.user_id='$user_id' and tag.module='$module'";
		if(!empty($tag_id)){
			$where.=" and tag_id=$tag_id";
		}
		$join='join '.$this->tablePrefix.'tag tag on tag_id=tag.id';
		$list=$model->join($join)->where($where)->field("row_id,tag_id")->select();
		return $list;
	}

	function del_data_by_row($row_list,$module=MODULE_NAME){
		$model=M("TagData");
		$where['row_id']=array('in',$row_list);
		$where['module']=$module;
		$result=$model->where($where)->delete();
		return $result;
	}

	function del_data_by_tag($tag_list){
		$model=M("TagData");
		if (is_array($tag_list)){
			$tag_list=array_filter($tag_list);
		}else{
			$tag_list=explode(",",$tag_list);
		}
		$where['tag_id']=array('in',$tag_list);
		$result=$model->where($where)->delete();
		return $result;			
	}

	function set_tag($row_list,$tag_list,$module=MODULE_NAME){
		if(empty($row_list)){
			return true;
		}
		if(empty($tag_list)){
			return true;
		}
		if (is_array($row_list)){
			$row_list=array_filter($row_list);
		}else{
			$row_list=explode(",",$row_list);
			$row_list=array_filter($row_list);
		}
		$row_list=implode(",",$row_list);
		if (is_array($tag_list)){
			$tag_list=array_filter($tag_list);
		}else{
			$tag_list=explode(",",$tag_list);
			$tag_list=array_filter($tag_list);
		}
		$tag_list=implode(",",$tag_list);
		$where = 'a.id in ('.$row_list.') AND b.id in('.$tag_list.')';
		$sql='INSERT INTO '.$this->tablePrefix.'tag_data (row_id,module,tag_id) SELECT a.id,b.module,b.id ';
		$sql.=' FROM '.$this->tablePrefix.$module.' a, '.$this->tablePrefix.'tag b WHERE '.$where;
		
		$result = $this->execute($sql);
		if($result===false){
			return false;
		}else {
			return true;
		}
	}
}
?>