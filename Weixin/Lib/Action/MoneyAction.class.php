<?php
// 财务申请相关部分
class MoneyAction extends CommAction {
	public function index(){
		if(!session('?oa_school')){
			$oa=M('hw002.smeoa_user',null)->where(array('name'=>session('name')))->find();
			if($oa){
				session('oa_school',$oa['school']);
				session('oa_part',$oa['bm']);
				session('oa_position',$oa['position']);
				session('date',date('Y-m'));
			}
			$this->display();
		}else{
			$this->display();
		}
	}
/**
发起、审核部分
*/
	public function faqi(){
		if($_POST['date'])session('date',$_POST['date']);
		//预算
		$w1['name']=session('name');
		$w1['date']=session('date');
		$this->list1=M('hw003.money_budget',null)->where($w1)->limit(50)->order('timestamp desc')->select();//预算
		//花销
		$w2['r']=session('name');
		$w2['date']=session('date');
		$this->list2=M('hw003.money_add',null)->where($w2)->limit(50)->order('timestamp desc')->select();//花销
		//退费
		$w3['ka']=session('name');
		$w3['date']=session('date');
		$this->list3=M('hw003.money_return',null)->where($w3)->limit(50)->order('timestamp desc')->select();//退费

		$this->display();
	}

	public function shenhe(){
		if($_POST['date'])session('date',$_POST['date']);
		//预算
		if(session('oa_position')=='校长')$budget=M('hw003.money_budget',null)->where(array('date'=>session('date'),'state'=>1,'school'=>session('oa_school')))->select();
		if(session('oa_position')=='主管'){
			$w['state']=(session('oa_part')=='财务部')?array('in','2,4'):2;
			$w['bm']=session('oa_part');
		}
		if(session('name')=='李文龙'){
			$w['state']=3;
		}
		if($w)$budget=M('hw003.money_budget',null)->where($w)->select();
		// 花销
		if(session('oa_position')=='校长')$huaxiao=M('hw003.money_add',null)->where(array('date'=>session('date'),'state'=>1,'b'=>session('oa_school')))->select();
		if(session('name')=='张毅')$huaxiao=M('hw003.money_add',null)->where(array('date'=>session('date'),'state'=>2))->select();
		//退费
		if(session('oa_position')=='财务')$return=M('hw003.money_return',null)->where(array('date'=>session('date'),'state'=>array('in','1,5'),'school'=>session('oa_school')))->select();
		if(session('oa_position')=='校长')$return=M('hw003.money_return',null)->where(array('date'=>session('date'),'state'=>2,'school'=>session('oa_school')))->select();
		if(session('name')=='王胜鑫')$return=M('hw003.money_return',null)->where(array('date'=>session('date'),'state'=>3,'why3'=>array('neq','')))->select();
		if(session('name')=='张毅')$return=M('hw003.money_return',null)->where(array('date'=>session('date'),'state'=>4))->select();

		$this->list1=$budget;
		$this->list2=$huaxiao;
		$this->list3=$return;
		$this->button=1;
		$this->display('faqi');
	}

/**
预算部分
*/
	public function budget(){
		// 添加申请
		if(IS_POST){
			$m=M('hw003.money_budget',null);
			$m->create();
            $h=$_POST['d'];
            $m->d=round($h,2);
			$m->date=session('date');
			$m->time=date('y-m-d');
			$m->school=$_SESSION['oa']['school'];
			$m->name=session('name');
            if($_SESSION['oa']['school']=='集团')$m->state=2;//集团帐号跳过校区审核的步骤
			if($m->add())$this->success('申请提交成功……','index');
		}else{
			$this->display();
		}
	}

	public function money(){
		if($_POST){
			$m=M('hw003.money_add',null);
            $m->kk=round($_POST['d'],2);
			$m->create();
			$m->r=session('name');
			$m->b=session('oa_school');
			if($_POST['mm']=='')$m->mm=1;
			if(session('oa_school')=='集团')$m->state=2;
			if($m->add())$this->$this->success('申请提交成功……','index');
		}else{
			$this->display();
		}
	}

	public function returm(){
		if(IS_POST){
            $m=M('hw003.money_return',null);
            $m->create();
            if($_POST['why'])$m->why1=implode('；',$_POST['why']);
            $m->school=session('oa_school');
            $m->date=session('date');
            $m->time1=date('Y-m-d');
            $m->ka=session('name');
            if($m->add())$this->$this->success('申请提交成功……','index');
		}else{
			$this->display('return');
		}
	}

/**
页面调用
*/
	// 成本类型返回二级科目
	public function api($p){
      if(isset($_POST['p'])&&$_POST['p']!=''){
        $where['class']=$_POST['p'];
        $shuchu=M('hw003.money_sort',null)->where($where)->select();
        print(json_encode($shuchu));//将信息发送给浏览器
      }
   	}

   	// 审核操作按钮abc通过、退回、失败
   	public function check($type,$id,$abc){
   		if(session('name'))
   		if(IS_AJAX){
   			if($type=='budget'){
   				if($abc=='a')M('hw003.money_budget',null)->where(array('id'=>$id))->setInc('state');//通过
   				if($abc=='b')M('hw003.money_budget',null)->where(array('id'=>$id))->setfield('state',0);//退回
   				if($abc=='c')M('hw003.money_budget',null)->where(array('id'=>$id))->setfield('state',-1);//失败
   				// if(M('hw003.money_budget',null)->where(array('id'=>$id))->getField('state')==3)$this->text(6,'李文龙','系统有预算待审核。');
   				$this->budget_record($id,$abc);
   			}
   			if($type=='money'){
   				if($abc=='a')M('hw003.money_add',null)->where(array('id'=>$id))->setInc('state');//通过
   				if($abc=='b')M('hw003.money_add',null)->where(array('id'=>$id))->setfield('state',0);//退回
   				if($abc=='c')M('hw003.money_add',null)->where(array('id'=>$id))->setfield('state',-1);//失败
   			}
   			if($type=='return'){
   				if($abc=='a')M('hw003.money_return',null)->where(array('id'=>$id))->setInc('state');//通过
   				if($abc=='b')M('hw003.money_return',null)->where(array('id'=>$id))->setfield('state',0);//退回
   				if($abc=='c')M('hw003.money_return',null)->where(array('id'=>$id))->setfield('state',0);//失败
   			}
   			echo('ok');
   		}
   	}

/**
内部调用
*/
    //预算审核记录
    public function budget_record($id,$info){
    	$w['id']=$id;
    	$inf=M('hw003.budget','money_')->where($w)->find();
    	$d['record']=$inf['record'].'<'.$info.date('Y-m-d H:i:s').session('name').'>';
    	M('hw003.budget','money_')->where($w)->save($d);
    }
    // //花销审核记录
    // public function money_record($id,$info){
    // 	$w['id']=$id;
    // 	$inf=M('hw003.add','money_')->where($w)->find();
    // 	$d['record']=$inf['record'].'<'.$info.date('Y-m-d H:i:s').session('name').'>';
    // 	M('hw003.add','money_')->where($w)->save($d);
    // }
    //退费审核记录
    // public function return_record($id,$info){
    // 	$w['id']=$id;
    // 	$inf=M('hw003.return','money_')->where($w)->find();
    // 	$d['record']=$inf['record'].'<'.$info.date('Y-m-d H:i:s').session('name').'>';
    // 	M('hw003.return','money_')->where($w)->save($d);
    // }
}