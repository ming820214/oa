<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


// 用户模型
class RelationModel extends CommonModel {
	public function get_row_list($relation,$relation_list=null){
		if(!empty($relation)){
			$where['relation']=array('eq',$relation_list);
			if(!empty($relation_list)){
				if (is_array($relation_list)){
					$relation_list=array_filter($relation_list);
				}else{
					$relation_list=array_filter(explode(",",$relation_list));
				}
				$where['relation_id']=array('in',$relation_list);
			}
			$list=$this->where($where)->field("row_id,relation_id")->select();
			return $list;
		}
	}

	public function get_relation_list($relation,$row_list=null){
		if(!empty($relation)){
			$where['relation']=array('eq',$relation);
			if(!empty($row_list)){
				if (is_array($row_list)){
					$row_list=array_filter($row_list);
				}else{
					$row_list=array_filter(explode(",",$row_list));
				}
				$where['row_id']=array('in',$row_list);
			}
			$list=$this->where($where)->field("row_id,relation_id")->select();
			$list=rotate($list);

			$relation_module=explode("|",$relation);
			$row_module=$relation_module[0];
			$relation_module=$relation_module[1];

			$model=M($relation_module);
			$where['id']=array('in',$list['relation_id']);
			$list=$model->where($where)->select();
			return $list;
		}
	}


	function del_data_by_row($relation,$row_list){
		if(!empty($relation)&&!empty($row_list)){
			$model=M("Relation");
			if (is_array($row_list)){
				$row_list=array_filter($row_list);
			}else{
				$row_list=array_filter(explode(",",$row_list));
			}
			$where['row_id']=array('in',$row_list);
			$where['module']=$relation;
			$result=$model->where($where)->delete();
			return $result;
		}
	}

	function del_data_by_relation($relation,$relation_list){
		if(!empty($relation)&&!empty($relation_list)){
			$model=M("Relation");
			if (is_array($relation_list)){
				$relation_list=array_filter($relation_list);
			}else{
				$relation_list=array_filter(explode(",",$relation_list));
			}
			$where['relation_id']=array('in',$relation_list);
			$where['module']=$relation;
			$result=$model->where($where)->delete();
			return $result;
		}
	}

	function set_relation($relation,$row_list,$relation_list){
		if(!empty($relation)){
			$this->del_data_by_row($relation,$row_list);
			if(empty($row_list)){
				return true;
			}
			if(empty($relation_list)){
				return true;
			}
			if (is_array($row_list)){
				$row_list=array_filter($row_list);
			}else{
				$row_list=explode(",",$row_list);
				$row_list=array_filter($row_list);
			}
			$row_list=implode(",",$row_list);

			if (is_array($relation_list)){
				$relation_list=array_filter($relation_list);
			}else{
				$relation_list=explode(",",$relation_list);
				$relation_list=array_filter($relation_list);
			}
			$relation_list=implode(",",$relation_list);

			$relation_module=explode("|",$relation);
			$row_module=$relation_module[0];
			$relation_module=$relation_module[1];
			$where = "a.id in (".$row_list.") and b.id in(".$relation_list.")";
			$sql=" insert into ".$this->tablePrefix."relation (row_id,relation,relation_id)";
			$sql.=" select a.id,'".$relation."',b.id ";
			$sql.=" from ".M($row_module)->trueTableName." a, ".M($relation_module)->trueTableName;
			$sql.=" b where ".$where;

			$result = $this->execute($sql);
			if($result===false){
				return false;
			}else {
				return true;
			}
		}
	}
}
?>