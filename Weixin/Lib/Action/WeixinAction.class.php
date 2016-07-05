<?php

class WeixinAction extends Action {
		
	//	public function getWeixinAskInfo($time1,$time2){
	public function getWeixinAskInfo($time){
		$wx = D('WeixinAsk');
//		$result = $wx->getWeinxinAskInfo($time1,$time2);
		$result = $wx->getWeinxinAskInfo($time);
		echo(json_encode($result));
		//return json_encode($result);
	}		
}
	