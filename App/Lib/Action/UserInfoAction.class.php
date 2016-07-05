<?php
class UserInfoAction extends CommonAction {
	protected $config = array('app_type' => 'common', 'action_auth' => array('search' => 'admin', 'emp_info' => 'admin','my_info_pdf'=>'admin','my_info'=>'read','base'=>'read'));

	//过滤查询字段
	function _filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_REQUEST['keyword']) && empty($map['title'])) {
			$map['title'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	}

	function my_info(){
		$where['is_check'] = array('eq', 1);
		$where['is_del'] = array('eq', 0);
		$base = M("UserInfoBase") -> where($where) -> order("create_time desc") -> find();
		$this -> assign('base', $base);

		$start_time = $_POST['be_create_time'];
		$end_time = $_POST['en_create_time'];
		if (!empty($start_time) && !empty($end_time)) {
			$map['create_time'] = array( array('egt', date_to_int(trim($start_time))), array('elt', date_to_int(trim($end_time))));
			$this -> assign('start_time', $start_time);
			$this -> assign('end_time', $end_time);
		}
		$map['user_id'] = array('eq', get_user_id());
		$map['is_del'] = array('eq', 0);

		for ($i = 1; $i <= 14; $i++) {
			$sql = D("UserInfo\\Tab{$i}View") -> buildSql();
			$model = new Model();
			$list = $model -> table($sql . "a") -> where($map) -> select();
			//dump($model->getLastSql();
			$this -> assign("tab" . $i, $list);
		}
		$this -> display();
	}
	
	function index(){
		$map = $this -> _search("UserView",true);
		if (method_exists($this, '_filter')) {
			$this -> _filter($map);
		}	
		$sql = D("UserView") ->buildSql();
		$model = new Model();
		$model -> table($sql. "a");
		if (!empty($model)) {
			$this -> _list($model, $map);
		}
		$this -> display();
	}

	function emp_info(){
		$user_id=$_REQUEST['user_id'];
		if(!empty($user_id)){
			$where['user_id'] = $user_id;
			$where['is_check'] = array('eq', 1);
			$where['is_del'] = array('eq', 0);
			$base = M("UserInfoBase") -> where($where) -> order("create_time desc") -> find();
			$this -> assign('base', $base);

			$start_time = $_POST['be_create_time'];
			$end_time = $_POST['en_create_time'];
			if (!empty($start_time) && !empty($end_time)) {
				$map['create_time'] = array( array('egt', date_to_int(trim($start_time))), array('elt', date_to_int(trim($end_time))));
				$this -> assign('start_time', $start_time);
				$this -> assign('end_time', $end_time);
			}
			$map['user_id'] = array('eq', $user_id);
			$map['is_del'] = array('eq', 0);

			for ($i = 1; $i <= 14; $i++) {
				$sql = D("UserInfo\\Tab{$i}View") -> buildSql();
				$model = new Model();
				$list = $model -> table($sql . "a") -> where($map) -> select();
				//dump($model->getLastSql();
				$this -> assign("tab" . $i, $list);
			}
		}
		$this -> display();
	}


	function my_info_pdf() {
		$where['is_check'] = array('eq', 1);
		$where['is_del'] = array('eq', 0);
		$base = M("UserInfoBase") -> where($where) -> order("create_time desc") -> find();
		$this -> assign('base', $base);

		$start_time = $_REQUEST['be_create_time'];
		$end_time = $_REQUEST['en_create_time'];
		if (!empty($start_time) && !empty($end_time)) {
			$map['create_time'] = array( array('egt', date_to_int(trim($start_time))), array('elt', date_to_int(trim($end_time))));
			$this -> assign('start_time', $start_time);
			$this -> assign('end_time', $end_time);
		}
		$map['user_id'] = array('eq', get_user_id());
		$map['is_del'] = array('eq', 0);

		for ($i = 1; $i <= 14; $i++) {
			$sql = D("UserInfo\\Tab{$i}View") -> buildSql();
			$model = new Model();
			$list = $model -> table($sql . "a") -> where($map) -> select();
			//dump($model->getLastSql();
			$this -> assign("tab" . $i, $list);
		}

		Vendor('Tcpdf.tcpdf');
		//require_once('tcpdf_include.php');

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf -> SetCreator(PDF_CREATOR);
		//$pdf->SetAuthor('Nicola Asuni');
		$pdf -> SetTitle('档案');
		$pdf -> SetSubject('档案');
		$pdf -> SetKeywords('档案');

		// set default header data
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);

		// set header and footer fonts
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf -> SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf -> setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set font
		$pdf -> SetFont('stsongstdlight', '', 10);

		// add a page
		$pdf -> AddPage();

		/* NOTE:
		 * *********************************************************
		 * You can load external XHTML using :
		 *
		 * $html = file_get_contents('/path/to/your/file.html');
		 *
		 * External CSS files will be automatically loaded.
		 * Sometimes you need to fix the path of the external CSS.
		 * *********************************************************
		 */

		// define some HTML content with style
		//$html = $this->display();
		$html = $this -> fetch();
		//dump($html);

		// output the HTML content
		$pdf -> writeHTML($html, true, false, true, false, '');

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// add a page
		//$pdf->AddPage();

		// reset pointer to the last page
		$pdf -> lastPage();

		// ---------------------------------------------------------

		//Close and output PDF document
		$pdf -> Output('example_061.pdf', 'I');
	}

	public function import(){
		$save_path = C('SAVE_PATH');
		$opmode = $_POST["opmode"];
		if ($opmode == "import") {
			import("@.ORG.Util.UploadFile");
			$upload = new UploadFile();
			$upload -> savePath = $save_path;
			$upload -> allowExts = array('xlsx');
			$upload -> saveRule = uniqid;
			$upload -> autoSub = false;

			if (!$upload -> upload()) {
				$this -> error($upload -> getErrorMsg());
			} else {
				//取得成功上传的文件信息
				$uploadList = $upload -> getUploadFileInfo();
				Vendor('Excel.PHPExcel');
				//导入thinkphp第三方类库

				$inputFileName = $save_path . $uploadList[0]["savename"];
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				$sheetData = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
				$model = M("Contact");
				for ($i = 2; $i <= count($sheetData); $i++) {
					$data = array();
					$data['name'] = $sheetData[$i]["A"];
					$data['company'] = $sheetData[$i]["B"];
					$data['letter'] = get_letter($sheetData[$i]["A"]);
					$data['dept'] = $sheetData[$i]["C"];
					$data['position'] = $sheetData[$i]["D"];
					$data['email'] = $sheetData[$i]["G"];
					$data['office_tel'] = $sheetData[$i]["E"];
					$data['mobile_tel'] = $sheetData[$i]["F"];
					$data['website'] = $sheetData[$i]["I"];
					$data['im'] = $sheetData[$i]["H"];
					$data['address'] = $sheetData[$i]["J"];
					$data['user_id'] = get_user_id();
					$data['remark'] = $sheetData[$i]["K"];
					$data['is_del'] = 0;
					$model -> add($data);
				}
				//dump($sheetData);
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				$this -> assign('jumpUrl', $this -> _get_return_url());
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}

	function base() {
		if ($_POST) {
			$this -> save("UserInfoBase");
		}
		$this -> assign($_GET);
		if ($_GET['mode'] == 'admin') {
			$this -> assign('opmode', 'add');
		} else {
			$where['is_del'] = array('eq', 0);
			$where['user_id'] = array('eq', get_user_id());
			$vo = M("UserInfoBase") -> where($where) -> order("create_time desc") -> find();
			if (!empty($vo)) {
				$this -> assign('vo', $vo);
				$is_check = $info['is_check'];
				if ($is_check == 1) {
					$this -> assign('opmode', 'add');
				} else {
					$this -> assign('opmode', 'edit');
				}
			} else {
				$this -> assign('opmode', 'add');
			}
		}
		$this -> display();
	}

	function check_list_base() {
		$this -> assign("check_type", "only");
		$this -> assign("model", "Base");
		$this -> _check_list("Base");
	}

	function _empty(){
		$arr_tab = array('tab1', 'tab2', 'tab3', 'tab4', 'tab5', 'tab6', 'tab7', 'tab8', 'tab9', 'tab10', 'tab11', 'tab12', 'tab13', 'tab14');
		if (in_array(ACTION_NAME, $arr_tab)) {
			$model = ucfirst(ACTION_NAME);
			$this -> _tab($model);
		}

		$arr_tab = array('list_tab1', 'list_tab2', 'list_tab3', 'list_tab4', 'list_tab5', 'list_tab6', 'list_tab7', 'list_tab8', 'list_tab9', 'list_tab10', 'list_tab11', 'list_tab12', 'list_tab13', 'list_tab14');

		if (in_array(ACTION_NAME, $arr_tab)) {
			$model = explode("_", ACTION_NAME);
			$model = $model[1];
			$model = ucfirst($model);
			$this -> _list_tab($model);
		}

		$arr_tab = array('check_list_tab1', 'check_list_tab2', 'check_list_tab3', 'check_list_tab4', 'check_list_tab5', 'check_list_tab6', 'check_list_tab7', 'check_list_tab8', 'check_list_tab9', 'check_list_tab10', 'check_list_tab11', 'check_list_tab12', 'check_list_tab13', 'check_list_tab14');
		if (in_array(ACTION_NAME, $arr_tab)) {
			$model = explode("_", ACTION_NAME);
			$model = $model[2];
			$model = ucfirst($model);
			$this -> _list_tab($model);
		}
	}

	function _tab($model) {
		if ($_POST) {
			$this -> save("UserInfo{$model}");
		}
		$this -> assign($_GET);
		if ($_GET['mode'] = 'admin') {
			$this -> assign('opmode', 'add');
		} else {
			$id = $_REQUEST['id'];
			if (!empty($id)) {
				$this -> assign('opmode', 'edit');
				$vo = M("UserInfo{$model}") -> find($id);
				$this -> assign('vo', $vo);
			} else {
				$this -> assign('opmode', 'add');
			}
		}
		$this -> display();
	}

	function _list_tab($model) {
		$this -> assign("model", $model);
		$this -> _check_list($model);
	}

	function _check_list_tab($model) {
		$this -> assign("model", $model);
		$this -> _check_list($model);
	}

	function _check_list($model) {
		$map = $this -> _search("UserInfo\\{$model}View", true);
		if (method_exists($this, '_filter')) {
			$this -> _filter($map);
		}
		$sql = D("UserInfo\\{$model}View") -> buildSql();
		$model = new Model();
		$list = $model -> table($sql . "a") -> where($map) -> select();
		//dump($map);
		$this -> assign("list", $list);
		$this -> display();
	}

	function check() {
		$model = $_REQUEST['model'];
		$id = $_REQUEST['id'];

		$where['id'] = array('eq', $id);
		$result = M("UserInfo" . $model) -> where($where) -> setField('is_check', 1);

		$check_type = $_REQUEST['check_type'];
		if (!empty($check_type)) {
			$info = M("UserInfo" . $model) -> find($id);
			$where = array();
			$where['user_id'] = $user_id;
			M("UserInfo" . $model) -> where($where) -> setField('is_del', 1);
			$where = array();
			$where['id'] = array('eq', $id);
			M("UserInfo" . $model) -> where($where) -> setField('is_del', 0);
		}
		if ($result !== false) {//保存成功
			$this -> assign('jumpUrl', $this -> _get_return_url());
			$this -> success('审核成功!');
		} else {
			//失败提示
			$this -> error('审核失败!');
		}
	}

	function un_check() {
		$model = $_REQUEST['model'];
		$id = $_REQUEST['id'];

		$where['id'] = array('eq', $id);
		$result = M("UserInfo" . $model) -> where($where) -> setField('is_check', 0);

		$check_type = $_REQUEST['check_type'];

		if ($result !== false) {//保存成功
			$this -> assign('jumpUrl', $this -> _get_return_url());
			$this -> success('反审核成功!');
		} else {
			//失败提示
			$this -> error('反审核失败!');
		}
	}

	function read() {
		$this -> assign("is_read", 1);
		$this -> edit();
	}

	function edit() {
		$model = $_REQUEST['model'];
		$id = $_REQUEST['id'];
		if ($_POST) {
			$this -> save("UserInfo" . $model);
		}
		$where['user_id'] = get_user_id();
		$where['id'] = $id;
		$this -> assign('opmode', 'edit');
		$vo = M("UserInfo" . $model) -> where($where) -> find();
		$this -> assign('vo', $vo);
		$this -> display(strtolower($model));
	}

	function admin() {
		$model = $_REQUEST['model'];
		$id = $_REQUEST['id'];
		if ($_POST) {
			$this -> save("UserInfo" . $model);
		}
		$this -> assign('opmode', 'edit');
		$info = M("UserInfo" . $model) -> find($id);
		$this -> assign('vo', $info);
		$this -> display(strtolower($model));
	}

	function del() {
		$model = $_REQUEST['model'];
		$id = $_REQUEST['id'];
		$where['id'] = array('eq', $id);
		$info = M("UserInfo" . $model) -> where($where) -> delete();
		if ($info !== false) {//保存成功
			$this -> assign('jumpUrl', $this -> _get_return_url());
			$this -> success('删除成功!');
		} else {
			//失败提示
			$this -> error('新增失败!');
		}
	}

	function save($model) {
		$opmode = $_POST["opmode"];
		if ($opmode == "add") {
			$this -> insert($model);
		}
		if ($opmode == "edit") {
			$this -> update($model);
		}
	}

	function insert($model) {
		$model = D($model);
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		};
		$mode = $_REQUEST['mode'];

		if ($mode == "admin") {
			$user_list = array_filter(explode(",", $_REQUEST['user_id']));
			foreach ($user_list as $user_id) {
				$model -> create();
				$user = M("User") -> find($user_id);
				$model -> user_id = $user['id'];
				$model -> user_name = $user['name'];
				//保存当前数据对象
				$model -> create_time = time();
				$list = $model -> add();
			}
		} else {
			if (in_array('user_id', $model -> getDbFields())) {
				$model -> user_id = get_user_id();
			};
			if (in_array('user_name', $model -> getDbFields())) {
				$model -> user_name = $this -> _session("user_name");
			};
			$model -> create_time = time();
			//保存当前数据对象
			$list = $model -> add();
		}

		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', $this -> _get_return_url());
			$this -> success('新增成功!');
		} else {
			//失败提示
			$this -> error('新增失败!');
		}
	}

	function update($model) {
		$model = D($model);

		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}
		// 更新数据
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> assign('jumpUrl', $this -> _get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
	}

	public function mark() {
		$action = $_REQUEST['action'];
		$id = $_REQUEST['notice_id'];
		switch ($action) {
			case 'del' :
				$where['id'] = array('in', $id);
				$folder = M("Doc") -> distinct(true) -> where($where) -> field("folder") -> select();
				if (count($folder) == 1) {
					$auth = D("Folder") -> _get_folder_auth($folder[0]["folder"]);
					if ($auth['admin'] == true) {
						$field = 'is_del';
						$this -> set_field($id, $field, 1);
					}
					$this -> ajaxReturn('', "删除成功", 1);
				} else {
					$this -> ajaxReturn('', "删除失败", 1);
				}
				break;
			default :
				break;
		}
	}

	public function upload() {
		R('File/upload');
	}

	public function down() {
		$attach_id = $_REQUEST["attach_id"];
		R("File/down", array($attach_id));
	}

}
