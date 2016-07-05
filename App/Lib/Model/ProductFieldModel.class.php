<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

// 用户模型
class ProductFieldModel extends CommonModel {
	public function get_field_list($type_id){
		$where['type_id']=array('eq',$type_id);
		$where['is_del']=0;
		$list = $this -> where($where) -> order('sort asc') -> select();
		return $list;
	}

	public function get_data_list($product_id){
		$model=M("ProductFieldData");
		$where = "product_id=$product_id";
		$join = 'join ' . $this -> tablePrefix . 'product_field field on field_id=field.id';
		$list = $model -> join($join) -> where($where) ->  order('sort asc') ->select();
		return $list;
	}

	function set_field($product_id){
		$model=M("ProductFieldData");
		$model->where("product_id=$product_id")->delete();
		
		$model=M("ProductFieldData");
		$data['product_id']=$product_id;
		$product_field = array_filter(array_keys($_REQUEST),"filter_flow_field");
		foreach ($product_field as $field){
			$tmp=array_filter(explode("_",$field));
			$data['field_id']=$tmp[2];
			$val=$_REQUEST[$field];
			
			if(is_array($val)){
				$val=implode(",",$val);
			}
			$data['val']=$val;
			$result=$model->add($data);
		}
		if ($result === false) {
			return false;
		} else {
			return true;
		}
	}
}
?>