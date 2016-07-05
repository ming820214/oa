<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class XueguanAction extends CommonAction {	
	protected $config = array('app_type' => 'personal');
	
	public function index(){
		// $tm=date('Y-m-d',strtotime(date('W').'week'));
		// var_dump($tm);
		
		$this -> display();
	}

	//学管里的我的学员
	public function student(){

		if (!empty($_POST['keyword'])) {
			$w['name|jiaoxue'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		$w['school']=session('schooll');
		$w['xueguan']=session('user_name');
		if($_POST['state']=='流失学员')$w['state']=array('in','2,3,5');
		if($_POST['type'])$w['type']=$_POST['type'];//学员类型
		if($_POST['grade'])$w['grade']=$_POST['grade'];//年级
		$m3=M('hw001.student',null)->where($w)->select();
		foreach ($m3 as $k => $v) {
			//1、学员信息
			$data[$k]['info']=$v;
			//学员停课科目
			$tke=explode('|',$v['tk']);
			if($tke){
				foreach ($tke as $v0) {
					$data[$k]['tk'][$v0]='停';
				}
			}
			$w3['stuid']=$v['id'];
			//2、课时统计
			$arr=array('语文','数学','英语','物理','地理','化学','历史','生物','政治');
			foreach ($arr as $v2) {
				$w3['class']=$v2;
				$c=M('hw001.class',null)->where($w3)->max('timee');
				if($c > date('Y-m-d')){
					$data[$k]['class'][$v2]['state']='正常';
					$w3['timee']=array('egt',date('Y-m-d'));
					$data[$k]['class'][$v2]['count']=M('hw001.class',null)->where($w3)->sum('count');
				}elseif($c && $c <= date('Y-m-d') && $v['state']==1){
					$data[$k]['class'][$v2]['state']='非正常';
				}else{
					$data[$k]['class'][$v2]['state']='未报名';
				}
			}
			//学员总状态
			$w4['stuid']=$v['id'];
			if((M('hw001.class',null)->where($w4)->max('timee') < date('Y-m-d')) &&  $v['state']==1){
				$data[$k]['state']='非正常';
			}elseif((M('hw001.class',null)->where($w4)->max('timee') >= date('Y-m-d')) &&  $v['state']==1){
				$data[$k]['state']='正常';
			}elseif($v['state']==2){
				$data[$k]['state']='停课';
			}elseif($v['state']==3){
				$data[$k]['state']='结课';
			}elseif($v['state']==5){
				$data[$k]['state']='退费';
			}
			if($_POST['state']=='正常在读' && $data[$k]['state']!='正常'){
				unset($data[$k]);
				continue;
			}elseif($_POST['state']=='非正常在读' && $data[$k]['state']!='非正常'){
				unset($data[$k]);
				continue;
			}elseif($_POST) {
			}else{
				if($data[$k]['state']!='正常'){
					unset($data[$k]);
					continue;
				}
			}
		}

	    //学管输出
		$wx['school']=session('schooll');
	    $wx['position']='学习管理师';
	    $this->xueguan=M('user')->where($wx)->getField('name',true);
		$this->list=$data;
		$this->display();

	}
	
	//讲师里的我的学员
	public function student2(){

		$w['teacher']=session('user_name');
		$m=M('hw001.class',null)->where($w)->getField('stuid',true);
		$v=array_unique($m);
		$w['timee']=array('egt',date('Y-m-d'));
		
		if($_POST['search']=='非正常在读'){
			foreach ($v as $val) {
				$w['stuid']=$val;
				if(!M('hw001.class',null)->where($w)->find())$f[]=$val;
			}
		}else{
			foreach ($v as $val) {
				$w['stuid']=$val;
				if(M('hw001.class',null)->where($w)->find())$f[]=$val;
			}
		}
		if($_POST['search']=='流失学员'){
			$w2['id']=array('in',$v);
			$w2['state']=array('neq',1);
		}else{
			$w2['id']=array('in',$f);
			$w2['state']=1;
		}
		$data=M('hw001.student',null)->where($w2)->field('id,name,grade,type,xueguan,jiaoxue,state')->select();
		$this->list=$data;
		$this->display();

 	}

	//全日制学员管理
	public function quan(){
		$w['school']=session('schooll');
		$w['xueguan']=session('user_name');
		$w['type']='全日制';
		$student=M('hw001.student',null)->where($w)->select();
		foreach ($student as $key=>$val) {
			# code...
			$w2['stuid']=$val['id'];
			$md0=date('Y-m-d',time()-((date('w')==0?7:date('w'))-1)*24*3600-7*24*3600);//上周一
			$md=date('Y-m-d',time()-((date('w')==0?7:date('w'))-1)*24*3600);
			$fd=date('Y-m-d',time()-((date('w')==0?7:date('w'))-1)*24*3600+6*24*3600);
			$w2['timee']=array('between',"$md0,$fd");
			$class=M('hw001.class',null)->where($w2)->select();
			//排课情况统计
			foreach ($class as $v) {
				$d[$v['class']]=$v['teacher'];
				if($v['timee'] < $md)$d['上周排课']+=$v['count'];
				if(($v['timee'] < $md) && $v['state']==1)$d['上周完成']+=$v['count'];
				if($v['timee'] >= $md)$d['本周排课']+=$v['count'];
			}
			//维护统计
			$d['上次维护']=M('hw001.weihu',null)->where("stuid = $val")->order('date asc')->getField('date');
			$data[$key]=$val;
			$data[$key]['data']=$d;
			unset($d);
		}

		$this->week=$this->get_week(date('Y'));
		// $week=$this->get_week(date('Y'));
		// var_dump($week);

		$this->data=$data;
		$this -> display();
	}

	//讲师反馈的的填写页面
	public function fankui(){
		//旷课按钮
		if($_POST['kuangke']){
			$w['id']=$_POST['cid'];
			M('hw001.class',null)->where($w)->setField('fankui','2');
			$this->redirect('fankui');
		}
		//授课反馈按钮
		if($_POST['cid']){
			$m=M('hw001.fankui',null);
			$m->create();
			$m->week=date('W',strtotime($_POST['date']));
			$m->school=session('schooll');
			if($_POST['fk_c'])$m->fk_c=implode(',', $_POST['fk_c']);
			$m->add();
			$w['id']=$_POST['cid'];
			M('hw001.class',null)->where($w)->setField('fankui','1');
			$this->redirect('fankui');
		}
		
		$w['teacher'] = session('user_name');
		$w['school'] = session('schooll');
		$w['timee'] = array('between',array('2014-04-01',date('Y-m-d')));
		//$w['time2'] = array('lt',date('H:i'));
		$w['fankui'] = 0;
		$m=M('hw001.class',null)->where($w)->field('id,stuid,timee,time1,time2,class')->select();

		foreach ($m as $v) {
			$s=$v;
			$s['student']=M('hw001.student',null)->field('name,tel,type')->find($v['stuid']);
			$m2[]=$s;
		}
		$this->data=$m2;
		$this->display();
	}

	//教学主任维护的页面
	public function weihu(){

		if($_POST['id']){
			$m=M('hw001.weihu',null);
			$m->create();
			$m->state=1;
			$m->save();
			$this->redirect('weihu');
		}
		if($_POST['sid']){
			$m=M('hw001.weihu',null);
			$m->create();
			$m->stuid=$_POST['sid'];
			$m->school=session('schooll');
			$m->type='普通维护';
			$m->xueguan=session('user_name');
			$m->state=1;
			$m->date=date('Y-m-d');
			$m->week=date('W');
			$m->add();
			$this->success('添加成功……');die;
		}

		$w['xueguan']=session('user_name');
		$w['state']=0;
		$w['type']=array('in','普通维护,A级维护');
		$m=M('hw001.weihu',null)->where($w)->select();
		foreach ($m as $v) {
			$s=$v;
			$s['剩余']=intval((strtotime($v['date'])-time())/86400);
			if($v['date'] < date('Y-m-d'))$s['超时']="任务超时";
			$s['student']=M('hw001.student',null)->field('name,tel,grade')->find($v['stuid']);
			$m2[]=$s;
		}
		$this->data=$m2;
		$this->display();
	}

	//维护副校长的任务设置页面
	public function set(){
		if (!empty($_POST['keyword'])) {
			$w['name|xueguan|jiaoxue'] = array('like', "%" . $_POST['keyword'] . "%");
		}
		$w['school']=session('schooll');
		if($_POST['state']=='流失学员')$w['state']=array('in','2,3,5');
		if($_POST['type'])$w['type']=$_POST['type'];//学员类型
		if($_POST['grade'])$w['grade']=$_POST['grade'];//年级
		if($_POST['xueguan'])$w['xueguan']=$_POST['xueguan'];
		$m3=M('hw001.student',null)->where($w)->select();
		foreach ($m3 as $k => $v) {
			//1、学员信息
			$data[$k]['info']=$v;
			//学员停课科目
			$tke=explode('|',$v['tk']);
			if($tke){
				foreach ($tke as $v0) {
					$data[$k]['tk'][$v0]='停';
				}
			}
			$w3['stuid']=$v['id'];
			//2、课时统计
			$arr=array('语文','数学','英语','物理','地理','化学','历史','生物','政治');
			foreach ($arr as $v2) {
				$w3['class']=$v2;
				$c=M('hw001.class',null)->where($w3)->max('timee');
				if($c > date('Y-m-d')){
					$data[$k]['class'][$v2]['state']='正常';
					$w3['timee']=array('egt',date('Y-m-d'));
					$data[$k]['class'][$v2]['count']=M('hw001.class',null)->where($w3)->sum('count');
				}elseif($c && $c <= date('Y-m-d') && $v['state']==1){
					$data[$k]['class'][$v2]['state']='非正常';
				}else{
					$data[$k]['class'][$v2]['state']='未报名';
				}
			}
			//学员总状态
			$w4['stuid']=$v['id'];
			if((M('hw001.class',null)->where($w4)->max('timee') < date('Y-m-d')) &&  $v['state']==1){
				$data[$k]['state']='非正常';
			}elseif((M('hw001.class',null)->where($w4)->max('timee') >= date('Y-m-d')) &&  $v['state']==1){
				$data[$k]['state']='正常';
			}elseif($v['state']==2){
				$data[$k]['state']='停课';
			}elseif($v['state']==3){
				$data[$k]['state']='结课';
			}elseif($v['state']==5){
				$data[$k]['state']='退费';
			}
			if($_POST['state']=='正常在读' && $data[$k]['state']!='正常'){
				unset($data[$k]);
				continue;
			}elseif($_POST['state']=='非正常在读' && $data[$k]['state']!='非正常'){
				unset($data[$k]);
				continue;
			}elseif($_POST) {
			}else{
				if($data[$k]['state']!='正常'){
					unset($data[$k]);
					continue;
				}
			}
			//上次维护时间
			$w2['stuid']=$v['id'];
			$w2['state']=1;
			$data[$k]['上次']=M('hw001.weihu',null)->where($w2)->max('date');//上次维护时间
			$w2['state']=0;
			$data[$k]['本次']=M('hw001.weihu',null)->where($w2)->find();//本次维护时间
			//任务状态
			if($_POST['state2']){
					$w2['state']=0;
				if($_POST['state2']==1){
					if(!M('hw001.weihu',null)->where($w2)->find())unset($data[$k]);
				}else{
					if(M('hw001.weihu',null)->where($w2)->find())unset($data[$k]);
				}
			}
			//维护类型
			if($_POST['type2']){
				$w2['type']=$_POST['type2'];
				if(!M('hw001.weihu',null)->where($w2)->find())unset($data[$k]);
			}
		}

	    //学管输出
		$wx['school']=session('schooll');
	    $wx['position']='学习管理师';
	    $this->xueguan=M('user')->where($wx)->getField('name',true);
		$this->list=$data;
		$this->week=R('Xueguan/get_week',array(date('Y')));//输出任务添加日期
		// var_dump($data);
		$this->display();

	}

	//添加维护任务
	public function set_add(){
		if($_GET['stuid']){
			$m=M('hw001.student',null)->find($_GET['stuid']);
			$date=date('Y').'-'.$_GET['date'];
			$t=strtotime($date)+24*3600*$_GET['mm'];
			$m2=M('hw001.weihu',null);
			$d['stuid']=$_GET['stuid'];
			$d['type']=$_GET['type'];
			$d['xueguan']=$m['xueguan'];
			$d['school']=session('schooll');
			$d['date']=date('Y-m-d',"$t");
			$d['week']=date('W',"$t");
			if($m2->add($d))print(json_encode(1));
		}
	}

	public function set_delt($wd){
		$w['id']=$wd;
		$w['state']=0;
		M('hw001.weihu',null)->where($w)->delete();
		$this->success('任务删除成功');
	}

	public function advice(){
		if(session('position')=='讲师'){
			// $w['school']=session('school');
			$w['teacher']=session('user_name');
			$w['timee']=array('gt',date('Y-m-d',time()-10*24*3600));
			$m=M('hw001.class',null)->where($w)->getField('stuid',true);
			$v=array_unique($m);
			$w2['id']=array('in',$v);
			$w2['state']=1;
			$s=M('hw001.student',null)->where($w2)->field('id,name')->select();
			$this->student=$s;
		}

		//学管回复
		if($_POST['hf']){
			$w3['id']=$this->_POST('id');
			$dat['results']=$_POST['results'];
			$dat['state']=1;
			$dat['time2']=date('Y-m-d H:i:s');
			M('hw001.weihu_advice',null)->where($w3)->save($dat);
		}
		//讲师确认
		if($_POST['qr']&&$_POST['id']){
			$w3['id']=$this->_POST('id');
			$w3['results']=array('neq','');
			M('hw001.weihu_advice',null)->where($w3)->setField('state',1);
		}
		//添加建议
		if($_POST['add']){
			$w4['name']=session('user_name');
			$class=M('hw001.teacher',null)->where($w4)->getField('class');
			$xueguan=M('hw001.student',null)->find($_POST['stuid']);
			$m3=M('hw001.weihu_advice',null);
			$m3->create();
			$m3->state=0;
			$m3->school=session('schooll');
			$m3->advice='原因：'.$_POST['advice1'].'；建议：'.$_POST['advice2'];
			$m3->teacher=session('user_name');
			$m3->xueguan=$xueguan['xueguan'];
			$m3->class=$class;
			$m3->add();
		}

		$w3['teacher|xueguan']=session('user_name');
		$w3['state']=0;
		$m3=M('hw001.weihu_advice',null)->where($w3)->select();
		foreach ($m3 as $k => $v) {
			$data[$k]=$v;
			$ss=M('hw001.student',null)->find($v['stuid']);
			$data[$k]['name']=$ss['name'];
		}
		$this->data=$data;
		$this->display();
	}

	//维护记录查询
	public function advice_record(){
		if($_POST['xueguan'])$w['xueguan']=$_POST['xueguan'];
		if($_POST['state']!='')$w['state']=$_POST['state'];
		if($_POST['type'])$w['type']=$_POST['type'];
		$w['school']=session('schooll');
		$m=M('hw001.weihu_advice',null)->where($w)->select();
		foreach ($m as $k => $v) {
			$w2['id']=$v['stuid'];
			$m[$k]['name']=M('hw001.student',null)->where($w2)->getField('name');
		}
		$this->data=$m;
		$this->xueguan();
		$this->display();
	}
//页面js调用
//=============相关页面独立调用==================
	public function fankui_api($cid){
		$m=M('hw001.class',null)->field('stuid,timee,teacher,class,time1,time2')->find($cid);
		print(json_encode($m));
	}
//==============共用box调用=========================
	public function info($stuid){
		$m=M('hw001.student',null)->find($stuid);
		print(json_encode($m));
	}
	public function weihu_record($stuid){
		$w['stuid']=$stuid;
		$w['state']=1;
		$t1=$_GET['t1'];
		$t2=$_GET['t2'];
		if($_GET['k']==2)$w['date']=array('like',"%".date('Y-m')."%");
		if($_GET['k']==3)$w['date']=array('between',"$t1,$t2");
		$m=M('hw001.weihu',null)->where($w)->order('date desc')->limit(20)->select();
		foreach ($m as $v) {
			if($v['type']=='3A级维护'){
				$data[]=array('学情分析：'.$v['xq_a'].'讲师：'.$v['xq_b'].'参会人：'.$v['xq_c'].'记录人：'.$v['xq_d'],'问题诊断：'.$v['xq_info1'].'解决方案：'.$v['xq_info2'],'家长意见：'.$v['info3']);
			}else{
				$data[]=array('维护日期：'.$v['date'].'，维护时长：'.$v['count'].'分钟，接听家长：'.$v['name'],'沟通情况：'.$v['info1'],'反馈情况：'.$v['info2']);
			}
		}
		if($_GET['k']==1 && $data){
			$data2[]=$data[0];
		}else{
			$data2=$data;
		}
		print(json_encode($data2));
	}

	public function fankui_record($stuid){
		if($stuid==88888)die;
		$w['stuid']=$stuid;
		$s=M('hw001.student',null)->find($stuid);
		$t1=$_GET['t1'];
		$t2=$_GET['t2'];
		if($_GET['k']==1)$w['date']=array('egt',date('Y-m-d',time()-7*24*3600));
		if($_GET['k']==2)$w['date']=array('like',"%".date('Y-m')."%");
		if($_GET['k']==3)$w['date']=array('between',"$t1,$t2");
		if($_GET['week'])$w['week']=$_GET['week'];
		if($_GET['km'])$w['class']=$_GET['km'];
		$m=M('hw001.fankui',null)->where($w)->order('date desc')->limit(20)->select();
		foreach ($m as $v) {
			if($s['type']=='全日制'){
				$data[]=array('上课日期：'.$v['date'].'...科目：'.$v['class'].'...讲师：'.$v['teacher'],'教学内容：'.$v['fkq_a'].'；学员表现：'.$v['fkq_b'].'；本日作业：'.$v['fkq_c'],'昨日作业情况：'.$v['fkq_d'].'；晚间答疑跟进:'.$v['fkq_e']);
			}else{
				$data[]=array('上课日期：'.$v['date'].'...科目：'.$v['class'].'...讲师：'.$v['teacher'],'模块：'.$v['fk_a'].'；内容：'.$v['fk_b'].'；状态：'.$v['fk_c'].'；亮点：'.$v['fk_d'].'；作业：'.$v['fk_e'].',作业'.$v['fk_f'],'沟通需要：'.$v['fk_g'].'；备注:'.$v['fk_h']);
			}
		}
		print(json_encode($data));
	}

	public function set_record($stuid){
		$w['stuid']=$stuid;
		$w['state']=0;
		$m=M('hw001.weihu',null)->where($w)->field('id,week,date,type,xueguan')->order('date desc')->limit(10)->select();
		print(json_encode($m));
	}

	public function advice_api($state){
		if($state==1){
			$w['teacher']=session('user_name');
			$w['timee']=array('gt',date('Y-m-d'));
			$m=M('hw001.class',null)->where($w)->getField('stuid',true);
			$v=array_unique($m);
			$w2['id']=array('in',$v);
			$w2['state']=1;
			$s=M('hw001.student',null)->where($w2)->field('id,name')->select();
			print(json_encode($s));
		}elseif ($state==2) {//正常非在读
			$w['teacher']=session('user_name');
			$m=M('hw001.class',null)->where($w)->getField('stuid',true);
			$v=array_unique($m);
			$w['timee']=array('egt',date('Y-m-d'));
			foreach ($v as $val) {
				$w['stuid']=$val;
				if(!M('hw001.class',null)->where($w)->find())$f[]=$val;
			}
			$w2['id']=array('in',$f);
			$w2['state']=1;
			$s=M('hw001.student',null)->where($w2)->field('id,name')->select();
			print(json_encode($s));
		}elseif ($state==3) {//流失学员
			$w['teacher']=session('user_name');
			$m=M('hw001.class',null)->where($w)->getField('stuid',true);
			$v=array_unique($m);
			$w['timee']=array('egt',date('Y-m-d'));
			foreach ($v as $val) {
				$w['stuid']=$val;
				if(!M('hw001.class',null)->where($w)->find())$f[]=$val;
			}
			$w2['id']=array('in',$f);
			$w2['state']=array('in','2,3,5');
			$s=M('hw001.student',null)->where($w2)->field('id,name')->select();
			print(json_encode($s));
		}

	}

	function xueguan(){
	    //学管输出
		$wx['school']=session('schooll');
	    $wx['position']='学习管理师';
	    $this->xueguan=M('user')->where($wx)->getField('name',true);
	}

	//计算一年多少个星期和每周的开始和结束日期
	function get_week($year) { 
	    $year_start = $year . "-01-01"; 
	    $year_end = $year . "-12-31"; 
	    $startday = strtotime($year_start); 
	    if (intval(date('N', $startday)) != '1') { 
	        $startday = strtotime("next monday", strtotime($year_start)); //获取年第一周的日期 
	    } 
	    $year_mondy = date("Y-m-d", $startday); //获取年第一周的日期 
	 
	    $endday = strtotime($year_end); 
	    if (intval(date('W', $endday)) == '7') { 
	        $endday = strtotime("last sunday", strtotime($year_end)); 
	    } 
	 
	    $num = intval(date('W', $endday)); 
	    for ($i = 0; $i <= $num; $i++) { 
	        $j = $i -1; 
	        $start_date = date("Y-m-d", strtotime("$year_mondy $j week ")); 
	 
	        $end_day = date("m-d", strtotime("$start_date +6 day")); 
	        $start_date = date("m-d", strtotime("$year_mondy $j week ")); 
	 
	        $week_array[$i+1] = array ($start_date,$end_day); 
	        // $week_array[$i+1] = array ( 
	        //     str_replace("-", 
	        //     ".", 
	        //     $start_date 
	        // ), str_replace("-", ".", $end_day)); 
	    } 
	    return $week_array;
	} 

}
