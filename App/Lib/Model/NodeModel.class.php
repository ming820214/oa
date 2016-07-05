<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/


// 节点模型
class NodeModel extends CommonModel {
	protected $_validate	=	array(
		array('name','checkNode','节点已经存在',0,'callback'),
	);

	public function checkNode() {
		$map['name']	 =	 $_POST['name'];
		$map['pid']	=	isset($_POST['pid'])?$_POST['pid']:0;
        $map['is_del'] = 1;
        if(!empty($_POST['id'])) {
			$map['id']	=	array('neq',$_POST['id']);
        }
		$result	=	$this->where($map)->field('id')->find();
        if($result) {
        	return false;
        }else{
			return true;
		}
	}
	
	public function access_list(){
		$emp_id=get_user_id();
		$sql="		SELECT  c.id, c.pid, c.name, c.url,sum(b.admin) as 'admin' ,sum(b.write) as  'write' ,sum(b.read) as 'read',c.icon ";
		$sql.="		FROM ".$this->tablePrefix."role_user AS a, ".$this->tablePrefix."role_node b, ".$this->tablePrefix."node AS c ";
		$sql.="		WHERE a.role_id = b.role_id and c.is_del=0 ";
		$sql.="		AND a.user_id ={$emp_id}";
		$sql.="		AND c.is_del =0 ";
		$sql.="		AND c.id = b.node_id ";
		$sql.="		group by c.id";
		$sql.="		ORDER BY c.sort ";
		$rs = $this->db->query($sql);
		return $rs;
	}

	public function get_top_menu(){
		$emp_id=get_user_id();
		$sql="		SELECT distinct c.id, c.pid, c.name, c.url,c.icon";
		$sql.="		FROM ".$this->tablePrefix."role_user AS a, ".$this->tablePrefix."role_node b, ".$this->tablePrefix."node AS c ";
		$sql.="		WHERE a.role_id = b.role_id and c.is_del=0 ";
		$sql.="		AND a.user_id ={$emp_id}";
		$sql.="		AND c.is_del =0 ";		
		$sql.="		AND c.id = b.node_id ";
		$sql.="		AND c.pid = 0 ";
		$sql.="		ORDER BY c.sort asc";
		$rs = $this->db->query($sql);
		return $rs;
	}
}
?>