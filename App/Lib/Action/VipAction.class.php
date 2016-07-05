<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class VipAction extends CommonAction {
	protected $config = array('app_type' => 'public', 'action_auth' => array('set_tag' => 'admin', 'tag_manage' => 'admin'));

	//过滤查询字段
	function _search_filter(&$map) {
		$map['is_del'] = array('eq', '0');
		if (!empty($_POST['keyword'])) {
			$keyword = $_POST['keyword'];
			$where['name'] = array('like', "%" . $keyword . "%");
			$where['active_shop'] = array('like', "%" . $keyword . "%");			
			$where['office_tel'] = array('like', "%" . $keyword . "%");
			$where['mobile_tel'] = array('like', "%" . $keyword . "%");
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
	}

	function index() {
		$model = M("Vip");
		$map = $this -> _search();
		if (method_exists($this, '_search_filter')) {
			$this -> _search_filter($map);
		}

		if (!empty($model)) {
			$this -> _list($model,$map,'area,active_shop,vip_type,name');
		}

		$this -> display();
	}

	function sales(){
		$model = M("VipSales");
		$where['vip_id']=array('eq',$_REQUEST['vip_id']);
		$this->assign('vip_id',$_REQUEST['vip_id']);
		$list=$model->where($where)->select();
		$this->assign('list',$list);
		$this -> display();
	}

	function add_sales(){
		if($this->isPost()){		
			$model=M("VipSales");
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			$model->size=implode(",",$model->size);

			$list = $model -> add();
			if ($list !== false) {//保存成功
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('新增成功!');
			} else {
				//失败提示
				$this -> error('新增失败!');
			}
		}else{
			$widget['date'] = true;	
			$this -> assign("widget", $widget);
			$this -> assign("vip_id",$_REQUEST['vip_id']);
			$this -> display();
		}
	}


	function edit_sales(){

		if($this->isPost()){		
			$model=M("VipSales");
			if (false === $model -> create()) {
				$this -> error($model -> getError());
			}
			$model->size=implode(",",$model->size);

			$list = $model -> save();
			if ($list !== false) {//保存成功
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('编辑成功!');
			} else {
				//失败提示
				$this -> error('编辑失败!');
			}
		}else{
			$widget['date'] = true;	
			$this -> assign("widget", $widget);
			$model=M("VipSales");
			$id=$_REQUEST['id'];
			$list=$model->find($id);
			$this->assign("vo",$list);
			$this -> display();
		}
	}

	function del_sales(){
		$model = M("VipSales");
		$id=$_REQUEST['id'];
		if (!empty($model)){
			if (isset($id)){
				$where['id']=array('eq',$id);
				$result = $model -> where($where) -> delete();
				if ($result !== false){
					$this -> assign('jumpUrl', get_return_url());
					$this -> success("彻底删除{$result}条!");
				} else {
					$this -> error('删除失败!');
				}
			} else {
				$this -> error('没有可删除的数据!');
			}
		}
	}

	function del(){
		$id = $_POST['id'];
		$count = $this ->_del($id,null,true);

		if ($count !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success("成功删除{$count}条!");
		} else {
			//失败提示
			$this -> error('删除失败!');
		}
	}

	function add(){
		$widget['date'] = true;		
		$this -> assign("widget", $widget);
		$this->display();
	}

	function edit(){
		$widget['date'] = true;		
		$this -> assign("widget", $widget);
		$model=M("Vip");
		$id=$_REQUEST['id'];

		$vo = $model -> getById($id);
		$this -> assign('vo', $vo);

		$model=M("VipSales");
		$where=array();
		$where['vip_id']=$id;
		$total_amount=$model->where($where)->sum('amount');
		$total_point=$model->where($where)->sum('point');

		$this->assign("total_amount",$total_amount);
		$this->assign("total_point",$total_point);
		$this->display();
	}

	protected function _insert(){
		$model = D('Vip');
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}

		$model->dressing_style=implode(",",$model->dressing_style);
		$model->top_size=implode(",",$model->top_size);
		$model->bottom_size=implode(",",$model->bottom_size);
		$model->like_color=implode(",",$model->like_color);
		
		//保存当前数据对象
		$list = $model -> add();
		if ($list !== false) {//保存成功
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('新增成功!');
		} else {
			//失败提示
			$this -> error('新增失败!');
		}
	}

	protected function _update() {

		$widget['date'] = true;		
		$this -> assign("widget", $widget);

		$id = $_POST['id'];
		$model = D("Vip");
		if (false === $model -> create()) {
			$this -> error($model -> getError());
		}

		$model->dressing_style=implode(",",$model->dressing_style);
		$model->top_size=implode(",",$model->top_size);
		$model->bottom_size=implode(",",$model->bottom_size);
		$model->like_color=implode(",",$model->like_color);

		// 更新数据
		$list = $model -> save();
		if (false !== $list) {
			//成功提示
			$this -> assign('jumpUrl', get_return_url());
			$this -> success('编辑成功!');
		} else {
			//错误提示
			$this -> error('编辑失败!');
		}
	}

	function export() {
		$model = M("Vip");
		$where['is_del']=0;
		$list = $model -> where($where) -> select();

		Vendor('Excel.PHPExcel');
		//导入thinkphp第三方类库

		$inputFileName = "Public/templete/Vip.xlsx";
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

		$objPHPExcel -> getProperties() -> setCreator("smeoa") -> setLastModifiedBy("smeoa") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("Test result file");
		// Add some data
		$i = 1;
		//dump($list);
		foreach ($list as $val) {
			$i++;
			$objPHPExcel -> setActiveSheetIndex(0) -> setCellValue("A$i", $val["name"]) -> setCellValue("B$i", $val["short"]) -> setCellValue("C$i", $val["biz_license"]) -> setCellValue("D$i", $val["payment"]) -> setCellValue("E$i", $val["address"]) -> setCellValue("F$i", $val["salesman"]) -> setCellValue("G$i", $val["contact"]) -> setCellValue("H$i", $val["email"]) -> setCellValue("I$i", $val["office_tel"]) -> setCellValue("J$i", $val["mobile_tel"]) -> setCellValue("J$i", $val["fax"]) -> setCellValue("L$i", $val["im"]) -> setCellValue("M$i", $val["remark"]);
		}
		// Rename worksheet
		$objPHPExcel -> getActiveSheet() -> setTitle('Vip');

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel -> setActiveSheetIndex(0);
	
		$file_name="Vip.xlsx";
		// Redirect output to a client’s web browser (Excel2007)
		header("Content-Type: application/force-download");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition:attachment;filename =" . str_ireplace('+', '%20', URLEncode($file_name)));
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter -> save('php://output');
		exit ;
	}

	public function import() {
		$save_path = get_save_path();
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
				$model = M("Vip");
				for ($i = 2; $i <= count($sheetData); $i++) {
					$data = array();
					$data['name'] = $sheetData[$i]["A"];
					$data['short'] = $sheetData[$i]["B"];
					$data['letter'] = get_letter($sheetData[$i]["A"]);
					$data['biz_license'] = $sheetData[$i]["C"];
					$data['payment'] = $sheetData[$i]["D"];
					$data['address'] = $sheetData[$i]["E"];
					$data['salesman'] = $sheetData[$i]["F"];
					$data['contact'] = $sheetData[$i]["G"];
					$data['email'] = $sheetData[$i]["H"];
					$data['office_tel'] = $sheetData[$i]["I"];
					$data['mobile_tel'] = $sheetData[$i]["J"];
					$data['fax'] = $sheetData[$i]["K"];
					$data['im'] = $sheetData[$i]["L"];
					$data['remark'] = $sheetData[$i]["M"];
					$data['statu'] = 1;
					$model -> add($data);
				}
				//dump($sheetData);
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName)) {
					unlink($_SERVER["DOCUMENT_ROOT"] . "/" . $inputFileName);
				}
				$this -> assign('jumpUrl', get_return_url());
				$this -> success('导入成功！');
			}
		} else {
			$this -> display();
		}
	}
}
?>