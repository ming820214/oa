<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

// 用户模型
class SystemTagModel extends CommonModel {
	public function get_tag_list($field = "id,name", $module = MODULE_NAME) {
		$where['module'] = $module;
		$list = $this -> where($where) -> order('sort asc') -> getfield($field);
		return $list;
	}

	public function get_data_list($module = MODULE_NAME, $tag_id = null) {
		$model = M("SystemTagData");
		$where = "tag.module='$module'";
		if (!empty($tag_id)) {
			$where .= " and tag_id=$tag_id";
		}
		$join = 'join ' . $this -> tablePrefix . 'system_tag tag on tag_id=tag.id';
		$list = $model -> join($join) -> where($where) -> field("row_id,tag_id") -> select();
		return $list;
	}

	function del_data_by_row($row_list, $module = MODULE_NAME) {
		if (isset($row_list)) {
			if (is_array($row_list)) {
				$where['row_id'] = array("in", array_filter($row_list));
			} else {
				$where['row_id'] = array('in', array_filter(explode(',', $row_list)));
			}
			$model = M("SystemTagData");
			$where['module'] = $module;
			$result = $model -> where($where) -> delete();
		}
		return $result;
	}

	function del_tag($tag_id) {
		$model = M("SystemTag");
		$tag_list = tree_to_list(list_to_tree($this -> get_tag_list("id,pid,name"), $tag_id));
		$tag_list = rotate($tag_list);

		$tag_list = implode(",", $tag_list['id']) . ",$tag_id";
		$where['id'] = array('in', $tag_list);
		$this -> where($where) -> delete();
		$this -> _del_data_by_tag($tag_list);
	}

	function set_tag($row_list, $tag_list, $module = MODULE_NAME) {
		if (empty($row_list)) {
			return true;
		}
		if (empty($tag_list)) {
			return true;
		}
		if (is_array($row_list)) {
			$row_list = array_filter($row_list);
		} else {
			$row_list = explode(",", $row_list);
			$row_list = array_filter($row_list);
		}
		$row_list = implode(",", $row_list);
		if (is_array($tag_list)) {
			$tag_list = array_filter($tag_list);
		} else {
			$tag_list = explode(",", $tag_list);
			$tag_list = array_filter($tag_list);
		}
		$module_table=M($module)->trueTableName;
		$tag_list = implode(",", $tag_list);
		$where = 'a.id in (' . $row_list . ') AND b.id in(' . $tag_list . ')';
		$sql = 'INSERT INTO ' . $this -> tablePrefix . 'system_tag_data (row_id,module,tag_id) SELECT a.id,b.module,b.id ';
		$sql .= ' FROM ' . $module_table  . ' a, ' . $this -> tablePrefix . 'system_tag b WHERE ' . $where;

		$result = $this -> execute($sql);
		if ($result === false) {
			return false;
		} else {
			return true;
		}
	}

	protected function _del_data_by_tag($tag_id) {
		if (isset($tag_id)) {
			if (is_array($tag_id)) {
				$where['tag_id'] = array("in", array_filter($tag_id));
			} else {
				$where['tag_id'] = array('in', array_filter(explode(',', $tag_id)));
			}
			$model = M("SystemTagData");
			$result = $model -> where($where) -> delete();
		}
		return $result;
	}
}
?>