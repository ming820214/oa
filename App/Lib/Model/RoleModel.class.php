<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


// 角色模型
class RoleModel extends CommonModel {
	public $_validate = array(
		array('name','require','名称必须'),
		);

	public $_auto		=	array(
		array('create_time','time',self::MODEL_INSERT,'function'),
		array('update_time','time',self::MODEL_UPDATE,'function'),
		);

	function get_node_list($role_id)
	{
		$rs = $this->db->query('select * from '.$this->tablePrefix.'role_node as a  where a.role_id='.$role_id.' ');
		return $rs;
	}

	function del_node($role_id,$node_list)
	{
		if(empty($node_list)){
			return true;
		}
		if (is_array($node_list)){
			$node_list=array_filter($node_list);
		}else{
			$node_list=explode(",",$node_list);
			$node_list=array_filter($node_list);
		}
		$node_list=implode(",",$node_list);
		$table = $this->tablePrefix.'role_node';
		//dump('delete from '.$table.' where role_id='.$role_id.' and node_id in ('.$node_list.')');
		
		$result = $this->db->execute('delete from '.$table.' where role_id='.$role_id.' and node_id in ('.$node_list.')');
	
		if($result===false) {
			return false;
		}else {
			return true;
		}
	}

	function set_node($role_id,$node_list)
	{			
		if(empty($node_list)){
			return true;
		}
		if (is_array($node_list)){
			$node_list=array_filter($node_list);
		}else{
			$node_list=explode(",",$node_list);
			$node_list=array_filter($node_list);
		}
		
		foreach($node_list as $node){
			$result = $this->db->execute('INSERT INTO '.$this->tablePrefix.'role_node (role_id,node_id) values('.$role_id.','.$node.')');
			if($result===false){
				return false;
			}
		}
			return true;
	}

	function get_role_list($user_Id)
	{
		$table = $this->tablePrefix.'role_user';
		$rs = $this->db->query('select a.role_id from '.$table.' as a where a.user_id='.$user_Id.' ');
		return $rs;
	}

	function del_role($user_list)
	{
		if(empty($user_list)){
			return true;
		}
		if (is_array($user_list)){
			$user_list=array_filter($user_list);
		}else{
			$user_list=explode(",",$user_list);
			$user_list=array_filter($user_list);
		}
		$user_list=implode(",",$user_list);

		$table = $this->tablePrefix.'role_user';

		$result = $this->db->execute('delete from '.$table.' where user_id in ('.$user_list.')');
		if($result===false) {
			return false;
		}else {
			return true;
		}
	}

	function set_role($user_list,$role_list){

		if(empty($user_list)){
			return true;
		}
		if(empty($role_list)){
			return true;
		}
		if (is_array($user_list)){
			$user_list=array_filter($user_list);
		}else{
			$user_list=explode(",",$user_list);
			$user_list=array_filter($user_list);
		}
		$user_list=implode(",",$user_list);

		if (is_array($role_list)){
			$role_list=array_filter($role_list);
		}else{
			$role_list=explode(",",$role_list);
			$role_list=array_filter($role_list);
		}
		$role_list=implode(",",$role_list);

		$where = 'a.id in ('.$user_list.') AND b.id in('.$role_list.')';
		$sql='INSERT INTO '.$this->tablePrefix.'role_user (user_id,role_id) ';
		$sql.=' SELECT a.id, b.id FROM '.$this->tablePrefix.'user a, '.$this->tablePrefix.'role b WHERE '.$where;
		$result = $this->execute($sql);
		if($result===false){
			return false;
		}else{
			return true;
		}
	}


	function get_duty_list($role_id)
	{
		$rs = $this->db->query('select duty_id from '.$this->tablePrefix.'role_duty as a  where a.role_id='.$role_id.' ');
		return $rs;
	}

	function del_duty($role_list)
	{
		if(empty($role_list)){
			return true;
		}
		if (is_array($role_list)){
			$role_list=array_filter($role_list);
		}else{
			$role_list=explode(",",$role_list);
			$role_list=array_filter($role_list);
		}
		$role_list=implode(",",$role_list);

		$table = $this->tablePrefix.'role_duty';

		$result = $this->db->execute('delete from '.$table.' where role_id in ('.$role_list.')');
		if($result===false) {
			return false;
		}else {
			return true;
		}
	}

	function set_duty($role_id,$duty_list)
	{			
		if(empty($duty_list)){
			return true;
		}
		if (is_array($duty_list)){
			$duty_list=array_filter($duty_list);
		}else{
			$duty_list=explode(",",$duty_list);
			$duty_list=array_filter($duty_list);
		}
		$duty_list=implode(",",$duty_list);

		$where = 'a.id ='.$role_id.' AND b.id in('.$duty_list.')';
		$sql='INSERT INTO '.$this->tablePrefix.'role_duty (role_id,duty_id)';
		$sql.=' SELECT a.id, b.id FROM '.$this->tablePrefix.'role a, '.$this->tablePrefix.'duty b WHERE '.$where;
		$result = $this->execute($sql);

		return result;
	}

}
?>