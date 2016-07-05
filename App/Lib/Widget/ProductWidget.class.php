<?php
class ProductWidget extends Widget {
	public function render($data) {		
		$add_file=$data['add_file'];
		$mode=$data['mode'];
		$files = array_filter(explode(';', $add_file));
		$where['sid'] = array('in', $files);
		$model = M("File");
		$file_list = $model -> where($where) -> select();
		$data['file_list']=$file_list;
		switch ($mode) {
				case 'add':
					$content = $this->renderFile('add',$data);	
					break;
				case 'edit':
					$content = $this->renderFile('edit',$data);
					break;
				case 'show':
					$content = $this->renderFile('show',$data);
					break;				
				default:
					$content = $this->renderFile('show',$data);						
					break;
			}
		return $content;
	}
}
?>