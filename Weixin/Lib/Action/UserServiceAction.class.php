<?php
/*--------------------------------------------------------------------
 鸿文OA系统 - 让工作更轻松快乐
 --------------------------------------------------------------*/

class UserServiceAction  extends Action {
	
	/**
	 * 提供对OA系统中用户信息查询接口，按时间段进行查询；
	 */
	public function listAllUserRecords($time1,$time2){
    	
    	$user = M('hw003.person_all')->alias('pall')->join('hw003.person_info as info on pall.id=info.id')->field("pall.cc AS atten_uid, '' as rule_id, '' as rule_all_id, '' as rule_disable_id, pall.name, pall.card, CASE pall.sex WHEN 0 THEN '女' ELSE '男' END as sex, info.mz AS nation, info.birthday, info.hunyin AS marriage, info.mian AS polity, pall.school AS campus, pall.position AS post, pall.tel AS phone, info.hometel AS telephone, '' as contacts, '' as urgency_phone, '' as urgency_telephone, info.address1 AS residence_booklet, info.address3 AS live, info.xuexiao AS school, info.zhuanye AS major, info.xueli AS education, '' as degree, '' as seniority, '' as contract_date, '' as entry_date, '' as qq, '' as wechat, '' as email, pall.cc AS `check`, '' as photo_max_url, '' as photo_small_url, '' as status")->where(['pall.timestamp' => array('between',array($time1,$time2))])->select();
		
		echo(json_encode($user));
	}
}