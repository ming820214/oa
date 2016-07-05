<?php
class MessageModel extends CommonModel {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('content','require','内容必须'),
		);

	public function get_list(){
		$user_id=get_user_id();

		$sql.= "select   t2.*, t3.count ";
		$sql.= "  from   ".$this->tablePrefix."message t2, ";
		$sql.= "         ( select  max(id) id, count(*) count ";
		$sql.= "              from   (select   id, ";
		$sql.= "                               sender_id, ";
		$sql.= "                               receiver_id, ";
		$sql.= "                               create_time ";
		$sql.= "                        from ".$this->tablePrefix."message ";
		$sql.= "                       where       is_del = '0' ";
		$sql.= "                               and owner_id = '$user_id' ";
		$sql.= "                               and receiver_id = '$user_id' ";
		$sql.= "                      union ";
		$sql.= "                      select   id, ";
		$sql.= "                               a.receiver_id, ";
		$sql.= "                               a.sender_id, ";
		$sql.= "                               create_time ";
		$sql.= "                        from ".$this->tablePrefix."message a ";
		$sql.= "                       where   a.is_del = 0 and owner_id =  '$user_id' and sender_id =  '$user_id') ";
		$sql.= "                     t1 ";
		$sql.= "             where   t1.receiver_id = '$user_id' ";
		$sql.= "          group by   t1.sender_id) t3 ";
		$sql.= " where  t3.id = t2.id order by t2.create_time desc";

		$rs = $this->db->query($sql);
		return $rs;
	}
}
?>