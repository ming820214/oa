<?php

class WeixinAskModel extends Model {
	
	protected $dbName = 'hw003';
	protected $tableName = 'person_all'; 
	
	//public function getWeinxinAskInfo($time1,$time2){
	public function getWeinxinAskInfo($time){

		/*$w['ask.timestamp'] = array(array('egt',$time1),array('elt',$time2));
		$w['_logic'] = 'OR';
		$w['ask.date'] = array(array('egt',$time1),array('elt',$time2));
		$w['_logic'] = 'OR';
		$w['ask.time1'] = array(array('egt',$time1),array('elt',$time2));
		$w['_logic'] = 'OR';
		$w['ask.time2'] = array(array('egt',$time1),array('elt',$time2));*/
		
		$w['ask.timestamp'] = array('like','%' . $time . '%');
		$w['_logic'] = 'OR';
		$w['ask.date'] = array('like','%' . $time . '%');
		$w['_logic'] = 'OR';
		$w['ask.time1'] = array('like','%' . $time . '%');
		$w['_logic'] = 'OR';
		$w['ask.time2'] = array('like','%' . $time . '%');
		
		
		$data = $this->alias('person')->join('person_ask as ask on person.id = ask.pid')
		->where($w)
		//->field('person.id as person_id, person.school as person_school, person.part as person_part, person.position as person_position, person.userid as person_userid, person.name as person_name, person.cc as person_cloudid, person.tel as person_tel, person.sex as person_sex, person.card as person_idcard, person.tag as person_tag, person.class as person_class, person.state as person_state, person.other as person_other, person.`timestamp` as person_createTime,ask.id as ask_id, ask.class as ask_class, ask.state as ask_state, ask.school as ask_school, ask.part as ask_part, ask.pid as ask_pid, ask.name as ask_name, ask.aa as ask_property, ask.`date` as ask_date, ask.time1 as ask_time1, ask.time2 as ask_time2,  ask.gong as ask_gong,  ask.gong2 as ask_gong2,  ask.info as ask_info,  ask.pic1 as ask_pic1,  ask.pic2 as ask_pic2,  ask.pic3 as ask_pic3,  ask.why as ask_why,  ask.`timestamp` as ask_createTime,  ask.record as ask_record,  ask.kq as ask_kq ')
		->field('person.cc as atten_uid,person.school as campus,person.part as part,person.position as post,person.name as name,ask.class as class,ask.state as state,ask.aa as property,ask.date as time_date,ask.time1 as time_begin,ask.time2 as time_stop,ask.gong as count_day1,ask.gong2 as count_day2,ask.info as info,ask.timestamp as create_time ')
		->select();
		
		return $data;
	}
	
}
