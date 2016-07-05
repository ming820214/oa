<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class ProductAction extends CommonAction {
	protected $config = array('app_type' => 'folder', 'action_auth' => array('folder' => 'read','mark' => 'admin','type'=>'write'));

	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword'])) {
			$keyword = $_POST['keyword'];
			$where['name'] = array('like', "%" . $keyword . "%");
			$where['content'] = array('like', "%" . $keyword . "%");			
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
	}

	function type(){
		$model = D('ProductType');
		$where['is_del'] = 0;
		$list = $model -> where($where)->order('sort')-> select();
		$this -> assign("list", $list);
		$this -> _assign_tag_list();
		$this-> assign("fid",$_REQUEST['fid']);
		$this -> display();
	}

	function index(){
		$model = D('ProductType');
		$where['is_del'] = 0;
		$list = $model -> where($where)->order('sort')-> select();
		$this -> assign("list", $list);
		$this -> _assign_tag_list();
		$this -> display();
	}

	function folder() {
		$widget['date'] = true;
		$this -> assign("widget", $widget);

		$folder = $_REQUEST['fid'];
		$this -> assign("folder", $folder);

		if(empty($folder)){
			$this ->error("系统错误");
		}

		$emp_no = get_emp_no();
		$user_id = get_user_id();

		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		$model = D("ProductView");
		$this->assign("auth",$this->config['auth']);
		$this -> _list($model, $map);
		$this -> _assign_folder_list();
		$this -> display();
	}

	function add() {
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);
		$this -> assign("folder", $_REQUEST['fid']);

		$type_id = $_REQUEST['type'];
		$model = M("ProductType");
		$product_type = $model -> find($type_id);
		$this -> assign("product_type", $product_type);

		$model_product_field=D("ProductField");
		$field_list = $model_product_field ->get_field_list($type_id);
		$this -> assign("field_list", $field_list);
		
		$this -> display();
	}

	/** 插入新新数据  **/
	protected function _insert(){
		$model = D("Product");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
 
		/*保存当前数据对象 */
		$list = $model -> add();
	
		$model_product_filed=D("ProductField")->set_field($list);

		if ($list !== false){//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			$this -> error('新增失败!');
			//失败提示
		}
	}

	function read(){
		$model = D("Product");
		$id = $_REQUEST['id'];
		$vo = $model -> getById($id);
		$product_type_id=$vo['type'];
		$this -> assign('vo', $vo);
		$this -> assign("emp_no", $vo['emp_no']);
		$this -> assign("user_name", $vo['user_name']);

		$model_product_field=D("ProductField");
		$field_list = $model_product_field ->get_data_list($id);
		$this -> assign("field_list", $field_list);

		$this -> display();
	}

	function edit() {
		$widget['date'] = true;
		$widget['uploader'] = true;
		$widget['editor'] = true;
		$this -> assign("widget", $widget);

		$model = D("Product");
		$id = $_REQUEST['id'];
		$vo = $model -> getById($id);
		$this -> assign('vo', $vo);

		$model_product_field=D("ProductField");
		$field_list = $model_product_field ->get_data_list($id);
		$this -> assign("field_list", $field_list);
		$this -> display();
	}

	/* 更新数据  */
	protected function _update() {
		$name = $this -> getActionName();
		$model = D($name);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$product_id=$model->id;
		$list = $model -> save();

		$model_product_filed=D("ProductField")->set_field($product_id);
		if (false !== $list) {
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
			//成功提示
		} else {
			$this -> error('编辑失败!');
			//错误提示
		}
	}

	public function mark() {
		$action = $_REQUEST['action'];
		$user_id = $_REQUEST['user_id'];
		$emp_no=$_REQUEST['emp_no'];
		switch ($action) {
			case 'approve' :
				$model = D("ProductLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
				$model -> result = 1;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$product_id = $model -> product_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();

				$model = D("ProductLog");
				$model -> where("step=$step and product_id=$product_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					D("Product") -> next_step($product_id,$step);
					$this -> assign('jumpUrl', get_return_url(1));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			case 'back' :		
				$model = D("ProductLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
				
				$model -> result = 2;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$product_id = $model -> product_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();

				$model = D("ProductLog");
				$model -> where("step=$step and product_id=$product_id and result is null") ->delete();

				if ($list !== false) {//保存成功					
					D("Product") -> next_step($product_id,$step,$emp_no);
					$this -> assign('jumpUrl', get_return_url(1));
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;				
			case 'reject' :
				$model = D("ProductLog");
				if (false === $model -> create()) {
					$this -> error($model -> getError());
				}
				$model -> result = 0;
				if (in_array('user_id', $model -> getDbFields())) {
					$model -> user_id = get_user_id();
				};
				if (in_array('user_name', $model -> getDbFields())) {
					$model -> user_name = get_user_name();
				};

				$product_id = $model -> product_id;
				$step = $model -> step;
				//保存当前数据对象
				$list = $model -> save();
				//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
				$model = D("ProductLog");
				$model -> where("step=$step and product_id=$product_id and result is null") ->delete();

				if ($list !== false) {//保存成功
					D("Product") -> where("id=$product_id") -> setField('step', 0);
					$this -> assign('jumpUrl', get_return_url());
					$this -> success('操作成功!');
				} else {
					//失败提示
					$this -> error('操作失败!');
				}
				break;
			default :
				break;
		}
	}

	public function approve() {

		$model = D("ProductLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 1;
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name = get_user_name();
		};

		$product_id = $model -> product_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();

		$model = D("ProductLog");
		$model -> where("step=$step and product_id=$product_id and result is null") -> setField('is_del', 1);

		if ($list !== false) {//保存成功
			D("Product") -> next_step($product_id, $step);
			$this -> assign('jumpUrl', get_return_url());

			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function reject() {
		$model = D("ProductLog");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		$model -> result = 0;
		if (in_array('user_id', $model -> getDbFields())) {
			$model -> user_id = get_user_id();
		};
		if (in_array('user_name', $model -> getDbFields())) {
			$model -> user_name =get_user_name();
		};

		$product_id = $model -> product_id;
		$step = $model -> step;
		//保存当前数据对象
		$list = $model -> save();
		//可以裁决的人有多个人的时候，一个人评价完以后，禁止其他人重复裁决。
		$model = D("ProductLog");
		$model -> where("step=$step and product_id=$product_id and result is null") -> setField('is_del', 1);

		if ($list !== false) {//保存成功
			D("Product") -> where("id=$product_id") -> setField('step', 0);
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('操作成功!');
		} else {
			//失败提示
			$this -> error('操作失败!');
		}
	}

	public function down() {
		$this -> _down();
	}

	public function upload() {
		$this -> _upload();
	}

	protected function _assign_tag_list() {
		$model = D("SystemTag");
		$tag_list = $model -> get_tag_list('id,name','ProductType');
		$this -> assign("tag_list", $tag_list);
	}
}
