<?php
class ChpAction extends CommAction {
	
	private $pageNumber=0;
	private $pageCount=10000;
	
	public function chp(){
		
		$mod = M('hongwen_oa.chpInfo','oa_');
		$condition['is_del'] = 1; //正常记录
		$condition['user_id'] = session('pid');
		
		$worth = $mod->where($condition)->sum('worth'); //剩余积分
		$this->worth = $worth;
		
		$condition['record_type'] = 1;
		
		$list=$mod->where($condition)->limit($pageNumber,$pageCount)->select();
		
		$score = $mod->where($condition)->sum('worth'); //累计获得积分
		
		$this->score = $score;
		
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
		
		$consume_list=$mod->where($condition)->limit($pageNumber,$pageCount)->select();
		
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
		
		$consume_score = $mod->where($condition)->sum('worth'); //累计获得积分
		
		
		//CHP积分方案列表
		$scheme_list = M('hongwen_oa.chpDictionary','oa_')->where("pid=0 and is_del=1 and `group`=2 ")->order('`sort`')->select();
		
		$this->scheme_list = $scheme_list;
		
		
		$this->list=$list;
		
		$this->consume_list = $consume_list;
		
		$this->consume_score = -$consume_score;
		
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