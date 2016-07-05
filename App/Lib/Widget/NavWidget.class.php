<?php
class NavWidget extends Widget {
	public function render($data) {		
		$tree = $data['tree'];
		$new_count=$data['new_count'];
		return $this -> tree_nav($tree,$new_count);
	}

	function tree_nav($tree,$new_count,$level = 0) {		
		$level++;
		$html = "";
		//dump($tree);
		if (is_array($tree)){			
			if ($level >1) {
				$html = "<ul class='submenu'>\r\n";			
			} else {				
				$html = "<ul id='left_menu' class='nav nav-list'>\r\n";				
			}
			foreach ($tree as $val){
				if (isset($val["name"])) {
					$title = $val["name"];
					if (!empty($val["url"])) {
						$url = U($val['url']);
					} else {
						$url = "#";
					}
					if (empty($val["id"])) {
						$id = $val["name"];
					}else{
						$id = $val["id"];
					}
					$icon_class="";	
					$bc_count="";

					$icon="fa fa-angle-right";
					$icon_class=$val["icon"];	
					 
					if(strpos($icon_class,"bc-")!==false){
						$bc_class=get_bc_class($icon_class);
						
						$arr_temp=explode("-",$bc_class);
						$temp="bc-".$arr_temp[1];
						//dump($temp);
						$bc_count=$new_count[$temp][$bc_class];
 
						if($bc_count>99){
							$bc_count="99+";
						}
						if($bc_count==0){
							$bc_count=null;
						}
					}
			
					if (isset($val['_child'])) {
						$html .= "<li>\r\n";
						$html .= "<a class=\"dropdown-toggle\" node=\"$id\" href=\"" . "$url\">";
						$html .= "<i class=\"$icon\"></i>";
						$html .= "<span class=\"menu-text\">$title</span>";
						$html .= "<b class=\"arrow fa fa-angle-down\"></b>";
						$html.="<span class=\"badge badge-primary \">$bc_count</span>";
						$html .= "</a>\r\n";
						$html .= $this->tree_nav($val['_child'],$new_count,$level);
						$html = $html . "</li>\r\n";
					} else {
						$html .="<li>\r\n";
						$html .="<a  node=\"$id\" href=\"" . "$url\">\r\n";
						$html .= "<i class=\"$icon\"></i>";
						$html .= "<span class=\"menu-text\">$title</span>";
						$html.="<span class=\"badge badge-primary \">$bc_count</span>";
						$html .="</a>\r\n</li>\r\n";
					}
				}
			}
			$html = $html . "</ul>\r\n";
		}
		return $html;
	}
}
?>