<?php
class SchoolWidget extends Widget {
	public function render($data) {

		switch ($data) {
			case 'part'://部门分类
				$w['type']='part';
				$part=M('hw003.sort',null)->where($w)->getField('name',true);
				foreach ($part as $val) {
					$content.='<option value=\''.$val.'\'>'.$val.'</option>';
				}
				break;

			case 'position'://职位分类
				$w['type']='position';
				$part=M('hw003.sort',null)->where($w)->getField('name',true);
				foreach ($part as $val) {
					$content.='<option value=\''.$val.'\'>'.$val.'</option>';
				}
				break;
			
			default://校区、区域
				$school=M('hw001.school',null)->getField('school',true);
				foreach ($school as $val) {
					$content.='<option value=\''.$val.'\'>'.$val.'</option>';
				}
				break;
		}
				return $content;
	}

}
?>