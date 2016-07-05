<?php

class ReportAction extends CommonAction {
	protected $config=array('app_type'=>'asst');

	public function Index(){

		if(session('?date')){
		}else{
			$date=date('Y-m');
			session('date',"$date");
		}
		$this->display();
	}

//================ 财务数据=================
	public function ls(){

		if(session('schooll')!='集团'){
			$w['b']=session('schooll');
			$w['school']=session('schooll');
			// $ww['school']=session('schooll');
			$www['school']=session('schooll');
		}

		if($_POST['date']){
			$date=$this->_POST('date');
			$w['date']=$_POST['date'];
			$ww['date']=$date;
		}else{
			$w['date']=session('date');
			$ww['date']=session('date');
		}
		if($_POST['time1']&&$_POST['time2']){
			$t1=$this->_POST('time1');
			$t2=$this->_POST('time2');
			$w['d']=array('between',"$t1,$t2");
			$ww['time']=array('between',"$t1,$t2");
		}

		$w['state']=array('BETWEEN','0,3');
		$ww['state']=5;

		//预算统计
		$ss=M('hw003.money_budget',null)->where($ww)->select();
			foreach ($ss as  $vv) {
					$school[$vv['jsxq']]['预算']+=round($vv['d']*$vv['e'],2);
			}

		//花销及其它
		$s=M('hw003.money_add',null)->where($w)->select();
			foreach ($s as $key => $v) {
				switch ($v['g']) {
					case '员工借款':
						$school[$v['b']]['借款']+=round($v['kk']*$v['l'],2);
						break;
					case '押金':
						$school[$v['b']]['押金']+=round($v['kk']*$v['l'],2);
						break;
					case '福利金':
						$school[$v['b']]['福利金']+=round($v['kk']*$v['l'],2);
						break;
					default:
						$school[$v['b']]['花销']+=round($v['kk']*$v['l'],2);
						break;
				}
			}

		//@相关记录
		$ss=M('hw003.money_add_note',null)->where($www)->select();
			foreach ($ss as  $vvv) {
				$school[$vvv['school']]['预支']=round($vvv['b'],2);
				$school[$vvv['school']]['结余']=round($vvv['c'],2);
				$school[$vvv['school']]['差额']=round($school[$vvv['school']]['预算']-$school[$vvv['school']]['花销']-$school[$vvv['school']]['押金']-$school[$vvv['school']]['借款']-$school[$vvv['school']]['福利金']-$school[$vvv['school']]['预支']-$school[$vvv['school']]['结余'],2);
				$school[$vvv['school']]['备注']=$vvv['e'];
			}
		//过滤校区
			if(session('schooll')!='集团'){
				$sch[session('schooll')]=$school[session('schooll')];
			}else{
				$sch=$school;
			}

		ksort($sch,SORT_STRING);//按键名排序
		$this->school=$sch;
		$this->w=$w;
		$this->display();
	}

	public function note(){
		if($_POST['s']){
			$data['b']=$this->_POST('b');
			$data['c']=$this->_POST('c');
			$data['e']=$this->_POST('e');
			$w['school']=$this->_POST('s');
			if(M('hw003.money_add_note',null)->where($w)->save($data))print(json_encode('1'));
		}
	}

	public function tx(){
		if($_POST['date']){
			$date=$_POST['date'];
		}else{
			$date=session('date');
		}
		//数据列表
		$w['state']=3;
		$w['date']=$date;
		$list=M('hw003.money_add',null)->where($w)->order('id desc')->select();
		$this->list=$list;

			foreach ($list as $v) {

				$school[$v['b']]['花销']+=round($v['kk']*$v['l'],2);
				$school[$v['c']]['归属']+=round($v['kk']*$v['l'],2);

				if($v['mm']>1)
					for ($i=0; $i < $v['mm']; $i++) {
						$t=strtotime(date('Y-m',strtotime($v['f'])))+$i*date('t',strtotime($v['f']))*24*3600;
						if(strtotime($date)<$t){
							$school[$v['c']]['未来']+=round($v['kk']*$v['l'],2)/$v['mm'];
						}
					}
			}

		//之前摊销到当期的
		$w2['date']=array('lt',$date);
		$w2['state']=3;
		$li2=M('hw003.money_add',null)->where($w2)->select();
			foreach ($li2 as $vv) {
				if($vv['mm']>1)
					for ($i=0; $i < $vv['mm']; $i++) {
						$t=strtotime(date('Y-m',strtotime($vv['f'])))+$i*date('t',strtotime($vv['f']))*24*3600;
						if(strtotime(date('Y-m',strtotime($vv['f'])))==$t){
							$school[$vv['c']]['曾经']+=round($vv['kk']*$vv['l'],2)/$vv['mm'];
						}
					}
			}

		$this->school=$school;

		$this->display();

	}

/**
学管数据维护
*/
	//记录前一天学管数据
	public function record_xueguan(){
		$where['date']=date('Y-m-d');
		if(!M('hw001.report_xueguan',null)->where($where)->find()){
			$xu['position']='学习管理师';
			$xueguan=M('user')->where($xu)->select();
			// var_dump($xueguan);
			// die;
			foreach ($xueguan as $val) {
				$data['xueguan']=$val['name'];
				$data['school']=$val['school'];
				$data['date']=date('Y-m-d');
				$w['xueguan']=$val['name'];
				$w['state']=2;//停课
				if(M('hw001.student',null)->where($w)->getField('id',true))$data['bb']=implode('|',M('hw001.student',null)->where($w)->getField('id',true));
				$w['state']=3;//结课
				if(M('hw001.student',null)->where($w)->getField('id',true))$data['cc']=implode('|',M('hw001.student',null)->where($w)->getField('id',true));
				$w['state']=5;//退费
				if(M('hw001.student',null)->where($w)->getField('id',true))$data['dd']=implode('|',M('hw001.student',null)->where($w)->getField('id',true));
				$w['state']=1;//正常
				if(M('hw001.student',null)->where($w)->getField('id',true))$data['aa']=implode('|',M('hw001.student',null)->where($w)->getField('id',true));
				M('hw001.report_xueguan',null)->add($data);
				unset($w);
				unset($data);
			}
		}
	}

// ======================学管数据=========================
	public function weihu_xueguan(){
		$w['xueguan']=session('user_name');
		$w['state']=array('in','1,2,3,5');
		$m=M('hw001.student',null)->where($w)->Field('id,state,name,grade,wl,xueguan,jiaoxue,tk')->select();
		$km=array('数学','语文','英语','物理','化学','生物','政治','历史','地理');
		$data['aa1']=count($m);
		foreach ($m as $k => $v) {
			$w2['stuid']=$v['id'];
			$w2['timee']=array('egt',date('Y-m-d'));
			if(M('hw001.class',null)->where($w2)->find() && $v['state']==1){
				$data['aa2']++;//本月正常在读
				$w2['timee']=array('like',date('Y-m')."%");
				$m2=M('hw001.class',null)->where($w2)->field('class,count')->select();//本月
				foreach ($m2 as $v2) {
					$data['aaa'][$v2['class']]+=$v2['count'];//本月正常各科课时统计
					$data['aa4']+=$v2['count'];//本月总课时
					// $kmx[$v2['class']]+=$v2['count'];
				}
				//单科非正常在读
				foreach ($km as $v3) {
					if(!substr_count($v['tk'],$v3)){
						$w3['stuid']=$v['id'];
						$w3['class']=$v3;
						$max=M('hw001.class',null)->where($w3)->max('timee');
						if($max>'2014-05-05'&&$max<date('Y-m-d'))$data['aba'][$v3]++;
					}
				}
			}else{
				//本月非正常在读
				if($v['state']==1)$data['ab1']++;
				//停课学员数据
				if($v['state']==2)$data['ac1']++;
				//退费学员数据
				if($v['state']==5)$data['ac5']++;
				//结课学员数据
				if($v['state']==3)$data['ac9']++;
			}
		}
		// var_dump($data['xxx']);
		// die;
		//维护任务监控
		unset($w['state']);
		$w['date']=array('like',date('Y-m')."%");
		$wei=M('hw001.weihu',null)->where($w)->field('stuid,type,state')->select();
		foreach ($wei as $v4) {
			$weihu[$v4['type']][]=$v4['stuid'];//所有的任务
			if($v4['state']==0){
				$weihux[$v4['type']][]=$v4['stuid'];//未完成的任务
			}
			array_unique($weihu[$v4['type']]);
			array_unique($weihux[$v4['type']]);
		}
		$data['ada']=$weihu;//所有维护任务人次
		$data['adb']=$weihux;//所有未完成人次

		//讲师维护意见
		$data['adc']=M('hw001.weihu_advice',null)->where($w)->count();//所有维护意见
		$w['state']=1;
		$data['add']=M('hw001.weihu_advice',null)->where($w)->count();//已完成的意见

		$this->data=$data;
		$this->display();
	}

	public function weihu_school(){
		$w['school']=session('schooll');
		if($_POST['school'])$w['school']=$_POST['school'];

		//统计课时
		$class=self::school($w['school']);
		$data['bax']=$class[$w['school']];

		$w['state']=array('in','1,2,3,5');
		$m=M('hw001.student',null)->where($w)->Field('id,state,xueguan,tk')->select();
		$data['ba1']=count($m);
		foreach ($m as $v) {
			$w2['stuid']=$v['id'];
			$km=array('数学','语文','英语','物理','化学','生物','政治','历史','地理');
			$w2['timee']=array('egt',date('Y-m-d'));
			if(M('hw001.class',null)->where($w2)->find() && $v['state']==1){
				$data['baa'][$v['xueguan']]['正常'][]=$v['id'];//学管学员列表
				$data['bax']['人数']++;//本月正常在读人数
				//单科非正常在读学员(曾经上过，现在不上了)
				foreach ($km as $value) {
					if(!substr_count($v['tk'],$value)){//相应科目没有停课
						$w4['stuid']=$v['id'];
						$w4['class']=$value;
						$max=M('hw001.class',null)->where($w4)->max('timee');
						if($max > '2014-05-05' && $max < date('Y-m-d')){
							$data['baa'][$v['xueguan']]['单非'][$value][]=$v['id'];
								$data['baa'][$v['xueguan']]['单非'][$value]=array_unique($data['baa'][$v['xueguan']]['单非'][$value]);
							$data['bba']['单非'][$value][]=$v['id'];
								$data['bba']['单非'][$value]=array_unique($data['bba']['单非'][$value]);
							$data['baa'][$v['xueguan']]['单非']['总'][]=$v['id'];
								$data['baa'][$v['xueguan']]['单非']['总']=array_unique($data['baa'][$v['xueguan']]['单非']['总']);
							$data['bba']['单非']['总'][]=$v['id'];
								$data['bba']['单非']['总']=array_unique($data['bba']['单非']['总']);
						}
						unset($w4['class']);
					}
				}
			}else{
				//本月非正常在读
				if($v['state']==1){
					$data['baa'][$v['xueguan']]['非正常'][]=$v['id'];
					$data['bbx']['人数']++;//非正常在读
				}
				//停课学员数据
				if($v['state']==2){
					$data['baa'][$v['xueguan']]['停课'][]=$v['id'];
					$data['bcx1']['人数']++;
				}
				//退费学员数据
				if($v['state']==5){
					$data['baa'][$v['xueguan']]['退费'][]=$v['id'];
					$data['bcx2']['人数']++;//非正常在读
				}
				//结课学员数据
				if($v['state']==3){
					$data['baa'][$v['xueguan']]['结课'][]=$v['id'];
					$data['bcx3']['人数']++;//非正常在读
				}
			}
		}
		$km=array('数学','语文','英语','物理','化学','生物','政治','历史','地理');
		foreach ($data['baa'] as $k => $val) {//学管内部循环
			$w3['stuid']=array('in',$data['baa'][$k]['正常']);
			$w3['timee']=array('egt',date('Y-m-d'));
			foreach ($km as $v2) {
				$w3['class']=$v2;
				$data['baa'][$k][$v2]=M('hw001.class',null)->where($w3)->sum('count');
				$data['baa'][$k]['总数']+=$data['baa'][$k][$v2];
			}
			//变化量统计
			$where['xueguan']=$k;
			$where['date']=date('Y-m-d',(time()-24*3600));
			$xg=M('hw001.report_xueguan',null)->where($where)->find();
			if($xg)$data['baa'][$k]['新增']['停课']=array_diff(explode('|',$xg['bb']),$data['baa'][$k]['停课']);
			if($xg)$data['baa'][$k]['新增']['退费']=array_diff(explode('|',$xg['dd']),$data['baa'][$k]['退费']);
			if($xg)$data['baa'][$k]['新增']['结课']=array_diff(explode('|',$xg['cc']),$data['baa'][$k]['结课']);
			if($xg)$data['baa'][$k]['激活']['停课']=array_intersect($data['baa'][$k]['正常'],explode('|',$xg['bb']));
			if($xg)$data['baa'][$k]['激活']['退费']=array_intersect($data['baa'][$k]['正常'],explode('|',$xg['dd']));
			if($xg)$data['baa'][$k]['激活']['结课']=array_intersect($data['baa'][$k]['正常'],explode('|',$xg['cc']));
			unset($xg);
		}
		// var_dump($data['baa']['王丽丽']);
		//维护任务监控
		unset($w['state']);
		$w['date']=array('like',date('Y-m')."%");
		$wei=M('hw001.weihu',null)->where($w)->field('stuid,type,xueguan,state')->select();
		foreach ($wei as $v4) {
			$weihu[$v4['type']][$v4['xueguan']]['全'][]=$v4['stuid'];//所有的任务
			if($v4['state']==1)$weihu[$v4['type']][$v4['xueguan']]['已'][]=$v4['stuid'];//已的任务
			array_unique($weihu[$v4['type']][$v4['xueguan']]['全']);
			array_unique($weihu[$v4['type']][$v4['xueguan']]['已']);
			$data['bdx'][$v4['type']]['全']++;
			if($v4['state']==1)$data['bdx'][$v4['type']]['已']++;
		}
		$data['bda']=$weihu;//所有维护任务

		//讲师维护意见
		$m2=M('hw001.weihu_advice',null)->where($w)->field('state,xueguan')->select();
		foreach ($m2 as $v5) {
			$data['bdc'][$v5['xueguan']]['全']++;
			if($v5['state']==1)$data['bdc'][$v5['xueguan']]['已']++;
			$data['bdx']['意见']['全']++;
			if($v5['state']==1)$data['bdx']['意见']['已']++;
		}
		$this->data=$data;
		$this->week=R('Xueguan/get_week',array(date('Y')));//输出任务添加日期
		$this->display();
	}

//页面调用查询
	public function weihu_api($data,$km=''){
		$w['xueguan']=session('user_name');
		$w['state']=array('in','1,2,3,5');
		switch ($data) {
			case 'aa1'://我的学员
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;

			case 'aa2'://本月正常在读
				$w['state']=1;
				$s=M('hw001.student',null)->where($w)->getField('id,state,xueguan',true);
				foreach ($s as $val) {
					$wx['stuid']=$val['id'];
					$wx['timee']=array('egt',date('Y-m-d'));
					if(M('hw001.class',null)->where($wx)->find())$stuid[]=$val['id'];
				}
				break;

			case 'ab1'://本月非正常在读，当下往后没有排课的情况
				$w['state']=1;
				$s=M('hw001.student',null)->where($w)->getField('id,state,xueguan',true);
				foreach ($s as $val) {
					$wx['stuid']=$val['id'];
					$wx['timee']=array('egt',date('Y-m-d'));
					if(M('hw001.class',null)->where($wx)->find()){
					}else{
						$stuid[]=$val['id'];
					}
				}
				break;

			case 'ab2'://单科非正常查询
				$w['state']=1;
				$s=M('hw001.student',null)->where($w)->getField('id,state,xueguan,tk',true);
				foreach ($s as $val) {
					$wx['stuid']=$val['id'];
					$wx['timee']=array('egt',date('Y-m-d'));
					if(M('hw001.class',null)->where($wx)->find()){
						if(!substr_count($val['tk'],$km)){
							$wx['class']=$km;
							unset($wx['timee']);
							$max=M('hw001.class',null)->where($wx)->max('timee');
							if($max>'2014-05-05' && $max<date('Y-m-d'))$stuid[]=$val['id'];
							unset($wx['class']);
						}
					}
				}
				break;

			case 'ac1'://停课
				$w['state']=2;
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;

			case 'ac5'://退费
				$w['state']=5;
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;
			case 'ac9'://结课
				$w['state']=3;
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;

			case 'ad1':
				$w['type']='普通维护';
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'ad3':
				$w['type']='A级维护';
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'ad5':
				$w['type']='2A级维护';
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'ad7':
				$w['state']=1;
				$w['type']='3A级维护';
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'ad9':
				$stuid=M('hw001.weihu_advice',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;

		}

			$da=self::stuid($stuid);
			print(json_encode($da));
	}

//校区数据
	public function weihu_apis($data,$nm='',$school=''){
		$w['state']=array('in','1,2,3,5');
		$w['school']=session('schooll');
		if($school)$w['school']=$school;
		if($nm)$w['xueguan']=$nm;
		switch ($data) {
			case 'ba1'://学员总数
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;

			case 'ba2'://本月正常在读
				$s=M('hw001.student',null)->where($w)->getField('id,state,xueguan',true);
				foreach ($s as $val) {
					$wx['stuid']=$val['id'];
					$wx['timee']=array('egt',date('Y-m-d'));
					if(M('hw001.class',null)->where($wx)->find() && $val['state']==1)$stuid[]=$val['id'];
				}
				break;

			case 'bb1'://非正常在读
				$w['state']=1;
				$s=M('hw001.student',null)->where($w)->getField('id,state,xueguan',true);
				foreach ($s as $val) {
					$wx['stuid']=$val['id'];
					$wx['timee']=array('egt',date('Y-m-d'));
					if(!M('hw001.class',null)->where($wx)->find())$stuid[]=$val['id'];
				}
				break;
			case 'bb2'://单科非正常在读
				$w['state']=1;
				$s=M('hw001.student',null)->where($w)->getField('id,state,xueguan,tk',true);
				foreach ($s as $val) {
					$wx['stuid']=$val['id'];
					$km=array('数学','语文','英语','物理','化学','生物','政治','历史','地理');
					if(M('hw001.class',null)->where($wx)->max('timee') >= date('Y-m-d')){
						foreach ($km as $value) {
							if(!substr_count($val['tk'],$value)){//相应科目没有停课
								$wx['class']=$value;
								$max=M('hw001.class',null)->where($wx)->max('timee');
								if($max > '2014-05-06' && $max < date('Y-m-d'))$stuid[]=$val['id'];
							}
							unset($wx['class']);
						}
					}
				}
				$stuid=array_unique($stuid);
				break;

			case 'bc1'://停课
				$w['state']=2;
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;

			case 'bc2'://新增停课
				unset($w['state']);
				$cc=explode('|',M('hw001.report_xueguan',null)->where($w)->getField('bb'));
				$w['state']=2;
				$stuid=array_diff(M('hw001.student',null)->where($w)->getField('id',true),$cc);
				break;
			case 'bc3'://停课激活
				unset($w['state']);
				$cc=explode('|',M('hw001.report_xueguan',null)->where($w)->getField('bb'));
				$w['state']=1;
				$stuid=array_diff(M('hw001.student',null)->where($w)->getField('id',true),$cc);
				break;

			case 'bc5'://退费
				$w['state']=5;
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;
			case 'bc6'://新增退费
				unset($w['state']);
				$cc=explode('|',M('hw001.report_xueguan',null)->where($w)->getField('dd'));
				$w['state']=5;
				$stuid=array_diff(M('hw001.student',null)->where($w)->getField('id',true),$cc);
				break;
			case 'bc7'://退费激活
				unset($w['state']);
				$cc=explode('|',M('hw001.report_xueguan',null)->where($w)->getField('dd'));
				$w['state']=1;
				$stuid=array_diff(M('hw001.student',null)->where($w)->getField('id',true),$cc);
				break;

			case 'bc9'://结课
				$w['state']=3;
				$stuid=M('hw001.student',null)->where($w)->getField('id',true);
				break;
			case 'bc10'://新增结课
				unset($w['state']);
				$cc=explode('|',M('hw001.report_xueguan',null)->where($w)->getField('cc'));
				$w['state']=3;
				$stuid=array_diff(M('hw001.student',null)->where($w)->getField('id',true),$cc);
				break;
			case 'bc11'://结课激活
				unset($w['state']);
				$cc=explode('|',M('hw001.report_xueguan',null)->where($w)->getField('cc'));
				$w['state']=1;
				$stuid=array_diff(M('hw001.student',null)->where($w)->getField('id',true),$cc);
				break;

			case 'bd1':
				unset($w['state']);
				$w['type']='普通维护';
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd2':
				$w['type']='普通维护';
				$w['state']=1;
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd3':
				unset($w['state']);
				$w['type']='A级维护';
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd4':
				$w['type']='A级维护';
				$w['state']=1;
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd5':
				unset($w['state']);
				$w['type']='2A级维护';
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd6':
				$w['type']='2A级维护';
				$w['state']=1;
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd7':
				unset($w['state']);
				$w['type']='3A级维护';
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd8':
				$w['state']=1;
				$w['type']='3A级维护';
				$w['date']=array('like',date('Y-m')."%");
				$stuid=M('hw001.weihu',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd9'://老师维护意见
				unset($w['state']);
				$stuid=M('hw001.weihu_advice',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
			case 'bd10'://老师维护意见
				$w['state']=1;
				$stuid=M('hw001.weihu_advice',null)->where($w)->getField('stuid',true);
				$stuid=array_unique($stuid);
				break;
		}
			$da=self::stuid($stuid);
			print(json_encode($da));
	}

//格式化学员数据api
	public static function stuid($stuid){
			foreach ($stuid as $v) {
				$w2['stuid']=$v;
				$dat[$v]['info']=M('hw001.student',null)->find($v);
				$w3['stuid']=$v;
				$w3['timee']=array('egt',date('Y-m-d'));
				$class=M('hw001.class',null)->where($w3)->select();
				foreach ($class as $v2){
					$c[$v2['class']]+=$v2['count'];
					$cc+=$v2['count'];
				}
				if($cc && $dat[$v]['info']['state']==1){
					$dat[$v]['state']='正常';
				}else{
					if($dat[$v]['info']['state']==1)$dat[$v]['state']='非正常';
					if($dat[$v]['info']['state']==2)$dat[$v]['state']='停课';
					if($dat[$v]['info']['state']==3)$dat[$v]['state']='结课';
					if($dat[$v]['info']['state']==5)$dat[$v]['state']='退费';
				}

				//统计课时量
				$dat[$v]['a']=$c['语文']+0;
				$dat[$v]['b']=$c['数学']+0;
				$dat[$v]['c']=$c['英语']+0;
				$dat[$v]['d']=$c['物理']+$c['地理'];
				$dat[$v]['e']=$c['化学']+$c['历史'];
				$dat[$v]['f']=$c['生物']+$c['政治'];

				//统计是否报名
				$km=array('数学','语文','英语','物理','化学','生物','政治','历史','地理');
				foreach ($km as $k) {
					$w2['class']=$k;
					if(!M('hw001.class',null)->where($w2)->find())$pk[$k]='无';
				}
				if($pk['语文']=='无')$dat[$v]['a']='无';
				if($pk['数学']=='无')$dat[$v]['b']='无';
				if($pk['英语']=='无')$dat[$v]['c']='无';
				if($pk['物理']=='无' && $pk['地理']=='无')$dat[$v]['d']='无';
				if($pk['化学']=='无' && $pk['历史']=='无')$dat[$v]['e']='无';
				if($pk['生物']=='无' && $pk['政治']=='无')$dat[$v]['f']='无';
				unset($pk);

				//统计停课
				if(substr_count($dat[$v]['info']['tk'],'语文') && $dat[$v]['a']!='无')$dat[$v]['a']='停课';
				if(substr_count($dat[$v]['info']['tk'],'数学') && $dat[$v]['b']!='无')$dat[$v]['b']='停课';
				if(substr_count($dat[$v]['info']['tk'],'英语') && $dat[$v]['c']!='无')$dat[$v]['c']='停课';
				if(substr_count($dat[$v]['info']['tk'],'物理') && $dat[$v]['d']!='无')$dat[$v]['d']='停课';
				if(substr_count($dat[$v]['info']['tk'],'化学') && $dat[$v]['e']!='无')$dat[$v]['e']='停课';
				if(substr_count($dat[$v]['info']['tk'],'生物') && $dat[$v]['f']!='无')$dat[$v]['f']='停课';

				//临时加课问题
				if(substr_count($dat[$v]['info']['tk'],'临时')){$dat[$v]['tag']='临时加课';}elseif($dat[$v]['state']=='非正常'){$dat[$v]['tag']='未处理';}else{$dat[$v]['tag']=$dat[$v]['state'];}

				//维护任务统计
				$w2['state']=1;
				$dat[$v]['weihu'][0]=M('hw001.weihu',null)->where($w2)->max('date');
				$w2['state']=0;
				$dat[$v]['weihu'][1]=M('hw001.weihu',null)->where($w2)->min('date');
				unset($c);
				unset($cc);
				$da[]=$dat[$v];
			}
		return $da;
	}
//临时挪用
	function school($school){
			$aa['school']=$school;
			$ss=$school;
			if($_POST['time']){
				$day=$_POST['time'];
				$date=date('Y-m',strtotime($day));
			}else{
				$date=date('Y-m');
				$day=date('Y-m-d');
			}
			$aa['timee']=array('like',"$date%");
			$aa['state']=array('NEQ',2);
			$class=M('hw001.class',null)->where($aa)->order('timee asc,grade asc,time1 asc,class asc,teacher asc,state asc')->select();

					foreach ($class as $classl) {
	                    if($classl['timee']==$a&&$classl['time1']==$b&&$classl['time2']==$c&&$classl['class']==$d&&$classl['teacher']==$e){
	                    }else{
							switch ($classl['class']) {
								case '数学':
									$vm["$ss"]['数学']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['数学']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['数学']+=$classl['count'];
									break;
								case '语文':
									$vm["$ss"]['语文']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['语文']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['语文']+=$classl['count'];
									break;
								case '英语':
									$vm["$ss"]['英语']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['英语']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['英语']+=$classl['count'];
									break;
								case '物理':
									$vm["$ss"]['物理']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['物理']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['物理']+=$classl['count'];
									break;
								case '化学':
									$vm["$ss"]['化学']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['化学']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['化学']+=$classl['count'];
									break;
								case '生物':
									$vm["$ss"]['生物']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['生物']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['生物']+=$classl['count'];
									break;
								case '政治':
									$vm["$ss"]['政治']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['政治']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['政治']+=$classl['count'];
									break;
								case '历史':
									$vm["$ss"]['历史']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['历史']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['历史']+=$classl['count'];
									break;
								case '地理':
									$vm["$ss"]['地理']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['地理']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['地理']+=$classl['count'];
									break;
							}
	                    }
	                        $a=$classl['timee'];
	                        $b=$classl['time1'];
	                        $c=$classl['time2'];
	                        $d=$classl['class'];
	                        $e=$classl['teacher'];
					}

		//月度每日变化量统计
		$w['date']=date('Y-m-d',strtotime($day)-24*3600);;
		$b=M('hw001.tongji',null)->where($w)->select();
		foreach ($b as $val){
			$hj=$val['a']+$val['b']+$val['c']+$val['d']+$val['e']+$val['f']+$val['g']+$val['h']+$val['i'];
			$bh[$val['school']]=array('a'=>$val['a'],'b'=>$val['b'],'c'=>$val['c'],'d'=>$val['d'],'e'=>$val['e'],'f'=>$val['f'],'g'=>$val['g'],'h'=>$val['h'],'i'=>$val['i'],'bh'=>$hj);
		}

		foreach ($vd as $k2 => $v2) {
			$xx[$k2]=$v2['数学']+$v2['语文']+$v2['英语']+$v2['物理']+$v2['化学']+$v2['生物']+$v2['政治']+$v2['历史']+$v2['地理'];
		}
		arsort($xx);
		foreach ($xx as $k3 => $v3) {
			$vdd[$k3]=$vd[$k3];
		}
		foreach ($vm as $k4 => $v4) {
			$xxx[$k4]=$v4['数学']+$v4['语文']+$v4['英语']+$v4['物理']+$v4['化学']+$v4['生物']+$v4['政治']+$v4['历史']+$v4['地理'];
		}
		arsort($xxx);
		foreach ($xxx as $k5 => $v5) {
			$vmm[$k5]=$vm[$k5];
		}
		return $vmm;
	}


}
