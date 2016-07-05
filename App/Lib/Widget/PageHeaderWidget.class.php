<?php
class PageHeaderWidget extends Widget {
	public function render($data){
		$name=$data['name'];
		if(is_array($name)){
			$data['name']=implode($name);		
		}
		
		$search=$data['search'];
		if(empty($search)&&empty($dropdown)){
			$content = $this->renderFile('page_header_simple',$data);	
		}else{
			switch ($search) {
				case 'A':
					$content = $this->renderFile('page_header_date',$data);	
					break;
				case 'S':
					$content = $this->renderFile('page_header_search',$data);	
					break;
				case 'M':
					$content = $this->renderFile('page_header_adv_search',$data);
					break;
				case 'L':
					$content = $this->renderFile('page_header_local_search',$data);
					break;				
				default:
					$content = $this->renderFile('page_header_simple',$data);						
					break;
			}
		}
		return $content;
	}
}
?>