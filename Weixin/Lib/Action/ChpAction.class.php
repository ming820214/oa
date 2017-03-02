<?php
class ChpAction extends CommAction {
	
	private $pageNumber=0;
	private $pageCount=10000;
	
	public function gift_chp(){
		
		$this->display();
	}
	
	public function detail(){

		$mod = M('hongwen_oa.chpInfo','oa_');
		$condition['is_del'] = 1; //正常记录
		$condition['user_id'] = $_GET['param'];
		$condition['record_type'] = 1;
		
		$list=$mod->where($condition)->limit($pageNumber,$pageCount)->select();
		
		$score = $mod->where($condition)->sum('worth'); //累计获得积分
		
		$this->score = $score?$score:0;
		
		foreach ($list as &$v) {//跟踪人
			if($v['record_type'] == 1){
				$v['record_type'] = "积分";
			}else if($v['record_type'] == 2){
				$v['record_type'] = "兑换";
			}
		
			if($v['flag'] == 1){
				$v['flag'] = "申请积分兑换";
			}else if($v['flag'] == 2){
				$v['flag'] = "兑换完成";
			}else{
				$v['flag'] = null;
			}
			
			$v['user_name'] =  M('person_all')->where(['id'=>$v['user_id']])->getField('name');
			$v['school'] = M('person_all')->where(['id'=>$v['user_id']])->getField('school');

			$this->school = $v['school'];
			$this->user_name = $v['user_name'];
			
			$v['scheme'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['scheme']])->getField('name');
			$v['item1'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item1']])->getField('name');
			if($v['item2']){
				$v['item2'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item2']])->getField('name');
			}else{
				$v['item2'] = null;
			}
		}
		
		$this->list=$list;
		$this->display();
	}

	public function chp(){
		
		$mod = M('hongwen_oa.chpInfo','oa_');
		$condition['is_del'] = 1; //正常记录
		$condition['user_id'] = session('pid');
		
		$worth = $mod->where($condition)->sum('worth'); //剩余积分
		$this->worth = $worth?$worth:0;
		
		$self_postion = 0;
		$prev_gap = 0;
		$flag = false;
		//积分排行榜
		
		$worth_list = $mod->where(['is_del'=>1])->group('user_id')->field('user_id,sum(worth) as score')->order('score DESC')->select();
		
		foreach($worth_list as $key=>$m){
			if($m['user_id'] == session('pid')){
				$self_postion = $key;
				$flag = true;
			}
			$worth_list[$key]['user_name'] =  M('person_all')->where(['id'=>$m['user_id']])->getField('name');
			$worth_list[$key]['school'] = M('person_all')->where(['id'=>$m['user_id']])->getField('school');
		}
		
		if($worth_list){
			$prev_gap = $self_postion?($worth_list[$self_postion-1]['score']-$worth):0;
		}else{
			$prev_gap = -1;
		}
		
		//标志自己是否有积分
		$this->flag = $flag;
		
		//本人排行位置
		$this->self_position = $self_postion+1;
		//本人与上一位的荣誉值差距
		$this->prev_gap = $prev_gap;
		
		
		//积分排行榜
		$this->worth_list = $worth_list;
		
		
		$model = new Model();
		//本月可使用的积分总数
		$can_use_worth = $model->query("select sum(worth) as use_worth from (select  id, user_id, record_type, scheme, item1, item2, descp, worth, flag, is_del, creator, create_time, updator, update_time,year(create_time) as `year`,month(create_time) as `month` from hongwen_oa.oa_chp_info where 1=1 and is_del = 1 and user_id=" . session('pid') . " ) as sub_sel  where ((`year`<" . date('Y') . ") OR (`month`<" . date('n') . ")) OR (record_type=2) ");
		
		$this->use_worth = $can_use_worth[0]['use_worth']?$can_use_worth[0]['use_worth']:0;
		
		$worth = $mod->where($condition)->field('')->sum('worth'); //剩余积分
		
		
		$condition['record_type'] = 1;
		
		$list=$mod->where($condition)->limit($pageNumber,$pageCount)->select();
		
		$score = $mod->where($condition)->sum('worth'); //累计获得积分
		
		$this->score = $score?$score:0;
		
		foreach ($list as &$v) {//跟踪人
			if($v['record_type'] == 1){
				$v['record_type'] = "积分";
			}else if($v['record_type'] == 2){
				$v['record_type'] = "兑换";
			}
		
			if($v['flag'] == 1){
				$v['flag'] = "申请积分兑换";
			}else if($v['flag'] == 2){
				$v['flag'] = "兑换完成";
			}else{
				$v['flag'] = null;
			}
		
			$v['scheme'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['scheme']])->getField('name');
			$v['item1'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item1']])->getField('name');
			if($v['item2']){
				$v['item2'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item2']])->getField('name');
			}else{
				$v['item2'] = null;
			}
		}
		
		$condition['record_type'] = 2; //积分兑换
		$condition['flag'] = array('in','1,2'); //积分兑换
		
		$consume_list=$mod->where($condition)->order('flag')->limit($pageNumber,$pageCount)->select();
		
		unset($v);
		
		foreach ($consume_list as &$v) {//跟踪人
			if($v['record_type'] == 1){
				$v['record_type'] = "积分";
			}else if($v['record_type'] == 2){
				$v['record_type'] = "兑换";
			}
			
			if($v['flag'] == 1){
				$v['flag'] = "申请积分兑换";
			}else if($v['flag'] == 2){
				$v['flag'] = "兑换完成";
			}else{
				$v['flag'] = null;
			}	
			
		
			$v['scheme'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['scheme']])->getField('name');
			$v['item1'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item1']])->getField('name');
			if($v['item2']){
				$v['item2'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item2']])->getField('name');
			}else{
				$v['item2'] = null;
			}
			
			$v['worth'] = -$v['worth']; //消耗积分转正显示
		
		}
		
		//$condition['is_del'] = 1; //兑换申请以及退回记录
		$consume_score = $mod->where($condition)->sum('worth'); //累计消耗积分
		
		$condition['is_del'] = 3; //兑换申请以及退回记录
		
		$return_list=$mod->where($condition)->limit($pageNumber,$pageCount)->select();
		
		unset($v);
		foreach ($return_list as &$v) {//跟踪人
			if($v['record_type'] == 1){
				$v['record_type'] = "积分";
			}else if($v['record_type'] == 2){
				$v['record_type'] = "兑换";
			}
				
			$v['flag'] = '积分兑换退回';
				
		
			$v['scheme'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['scheme']])->getField('name');
			$v['item1'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item1']])->getField('name');
			if($v['item2']){
				$v['item2'] = M('hongwen_oa.chpDictionary','oa_')->where(['id'=>$v['item2']])->getField('name');
			}else{
				$v['item2'] = null;
			}
				
			$v['worth'] = -$v['worth']; //消耗积分转正显示
		
		}
		
		$this->return_list = $return_list; //退回的记录
		
		$return_count = $mod->where($condition)->count(); //累计消耗积分
		
		$this->return_count = $return_count; //退回的记录数
		
		//CHP积分方案列表
		$scheme_list = M('hongwen_oa.chpDictionary','oa_')->where("pid=0 and is_del=1 and `group`=2 ")->order('`sort`')->select();
		
		$this->scheme_list = $scheme_list;
		
		
		$this->list=$list;
		
		$this->consume_list = $consume_list;
		
		$this->consume_score = -$consume_score?(-$consume_score):0;
		
		$this->display();
	}
	
	
	//获取相关方案下所属积分项
	public  function getItems(){
		$pid = isset($_POST['pid'])?$_POST['pid']:null;
		$item_list = M('hongwen_oa.chpDictionary','oa_')->where(['pid'=>$pid,'is_del'=>1])->order('`sort`')->select();
	
		if(IS_AJAX && $item_list){
			// 发送给页面的数据
			$this->ajaxReturn([
						
					'state'=>'ok',//查询结果
					'data'=>$item_list
						
			]);
		}else{
			return null;
		}
	
	}
	
	public function exchange(){
		
		if(session('pid')){
			$mod=M('hongwen_oa.chpInfo','oa_');
			$mod->create();
			
			$mod->record_type=2;
			$mod->worth = -$mod->worth;
			$mod->user_id = session('pid');
			$mod->creator=session('pid');
			$mod->create_time = date('Y-m-d H:i:s');
			$mod->exchange_time = date('Y-m-d H:i:s');
			$mod->flag = 1;
			$mod->is_del = 1;
			
			if($mod->add()) {
				$this->success("恭喜，兑换申请成功！","chp");
			}
		}else{
			$this->error("无法辨识您的身份，请与系统管理员联系！","chp");
		} 
	}
}