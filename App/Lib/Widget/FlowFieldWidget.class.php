<?php
class FlowFieldWidget extends Widget {
	public function render($data){
		$data['data']=$data;
		$control_layout=$data['control_layout'];
		switch ($control_layout){
			case '1':
				$content = $this->renderFile('1',$data);	
				break;
			case '2':
				$content = $this->renderFile('2',$data);
				break;
			case '3':
				$content = $this->renderFile('3',$data);
				break;
			case '4':
				$content = $this->renderFile('4',$data);
				break;	
			default:
				$content = $this->renderFile('1',$data);						
				break;
		}
		return $content;
	}
}
?>