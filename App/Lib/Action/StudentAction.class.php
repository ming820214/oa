<?php

class StudentAction extends CommonAction {
	protected $config=array('app_type'=>'master');
//===================学员档案
	public function index(){

		if (!empty($_POST['keyword'])) {
			$w['name|tel|xueguan|jiaoxue'] = array('like', "%" . $_POST['keyword'] . "%");
		}
	
		if($_POST['add']){
			$this->addx();
		}elseif ($_POST['delt']) {
			$this->deltx();
		}else{
			$w['school']=session('schooll');
			if(session('schooll')=='集团')unset($w['school']);//给集团帐号做的特别处理
			if($_GET['state'])$w['state']=$_GET['state'];
			if($_GET['grade'])$w['grade']=$_GET['grade'];
			if($_GET['type'])$w['type']=$_GET['type'];
			if($_GET['xueguan'])$w['xueguan']=$_GET['xueguan'];
			if($_GET['jiaoxue'])$w['jiaoxue']=$_GET['jiaoxue'];
			if($_POST['xueguan'])$w['xueguan']=$this->_post('xueguan');
			if($_POST['jiaoxue'])$w['jiaoxue']=$this->_post('jiaoxue');
			if($_POST['state'])$w['state']=$this->_post('state');
			if($_POST['grade'])$w['grade']=$this->_post('grade');
			if($_POST['type'])$w['type']=$this->_post('type');
		    $Data = M('hw001.Student',null); // 实例化Data数据对象
		    import('ORG.Util.Page');// 导入分页类
		    $count      = $Data->where($w)->count();// 查询满足要求的总记录数 $map表示查询条件
		    $Page       = new Page($count,25);// 实例化分页类 传入总记录数
			 //分页跳转的时候保证查询条件
			foreach($w as $key=>$val) {
			    $Page->parameter   .=   "$key=".urlencode($val).'&';
			}
		    $show       = $Page->show();// 分页显示输出
		    // 进行分页数据查询
		    $list = $Data->where($w)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
		    
		    $this->assign('list',$list);// 赋值数据集
		    $this->assign('page',$show);// 赋值分页输出

		    //学管输出
			$w2['school']=session('schooll');
		    $w2['position']='学习管理师';
		    $this->xueguan=M('user')->where($w2)->getField('name',true);
		    //教学主任输出
			$w3['school']=session('schooll');
		    $w3['position']='教学主任';
		    $this->jiaoxue=M('user')->where($w3)->getField('name',true);

		    $this->display(); // 输出模板
		}

	}
	//外部api调用
	public function info($id){
		$info= M('hw001.Student',null)->find($id);
		$info['tk']=explode('|', $info['tk']);
        print(json_encode($info));
	}

	function addx(){
		$m = M('hw001.Student',null);
		$m->create();
		if($_POST['tk']){
			$tk=implode('|',$_POST['tk']);
			$m->tk=$tk;
		}
		$m->school=session('schooll');
		$m->id='';
		if($m->add()){
			$this->success('数据添加成功……');
		}else{
			$this->error('操作失败……');
		}
	}

	function savex(){
		$m = M('hw001.Student',null);
		$m->create();
		if($_POST['tk']){
			$tk=implode('|',$_POST['tk']);
			$m->tk=$tk;
		}else{
			$m->tk='';
		}
		if($m->save())print_r(json_encode(1));
	}
	
	function deltx(){
		$id=$_POST['id'];
		if(M('hw001.Student',null)->delete($id)){
			$this->success('数据删除成功……');
		}else{
			$this->error('操作失败……');
		}
	}

//=================资费明细
	public function money($id){
		$w['stuid']=$id;
		$m=M('hw001.student_price',null)->where($w)->select();
		$w2['id']=$id;
		$this->stuid=$id;
		$this->student=M('hw001.student',null)->where($w2)->getField('name');
		$this->list=M('hw001.student_order',null)->where($w)->select();
		$this->money=M('hw001.student_money',null)->where($w)->getField('money');
		$this->display();
	}
//课程订购
	public function order_add(){
		if($_POST['orderadd']){
				$w['id']=$_POST['stuid'];
				$m=M('hw001.student',null)->where($w)->find();
				$m2=M('hw001.student_order',null);
			if($_POST['yjk']){
				$w2['stuid']=$_POST['stuid'];
				$w2['lx']='预交款';
				$m3=$m2->where($w2)->find();
				$d['cc3']=$d['cc2']=$_POST['cc2']-$m3['cc2'];
				$d['cd3']=$d['cd2']=$_POST['cd2']-$m3['cd2'];
				$d['ce3']=$d['ce2']=$_POST['ce2']-$m3['ce2'];
				$d['cf3']=$d['cf2']=$_POST['cf2']-$m3['cf2'];
				$d['class']=$_POST['class'];
				$d['policyid']=$_POST['policyid'];
				$d['lx']='订课程';
				$d['money']=$_POST['money']+$m3['money'];
				$d['record']=$m3['record'].'|课程订购，合并预交款：'.'预交款'.$m3['money'].'补款'.$_POST['money'].'元，时间：'.date('Y-m-d');
				if(M('hw001.student_order',null)->where($w2)->save($d)){
					$this->redirect('Student/money',array('id'=>$_POST['stuid']));
				}else{
					$this->success('操作失败……');
				}
			}else{
					$m2->create();
					$m2->count3=$_POST['count2'];
					$m2->school=$m['school'];
					$m2->cc3=$_POST['cc2'];
					$m2->cd3=$_POST['cd2'];
					$m2->ce3=$_POST['ce2'];
					$m2->cf3=$_POST['cf2'];
					$m2->record='课程订购：扣费金额'.$_POST['money'].'元，时间'.date('Y-m-d');
					if($m2->add()){
						$this->success('订单订购成功……');
					}else{
						$this->success('失败……');
					}
			}
		}elseif($_POST['moneyadd']){//预交款
			if($_POST['money1']){
				$w['id']=$_POST['stuid'];
				$m=M('hw001.student',null)->where($w)->find();
				$d['stuid']=$_POST['stuid'];
				$d['school']=$m['school'];
				$d['lx']='预交款';
				$d['money']=$_POST['money1'];
				$d['other']=$_POST['other1'];
				$d['record']='预交款：'.$_POST['money1'].'元，备注：'.$_POST['other1'].'时间：'.date('Y-m-d');
				if(M('hw001.student_order',null)->where($w)->add($d))$this->redirect('Student/money',array('id'=>$_POST['stuid']));
			}
		}
	}

//优惠计算
	public function mapi(){
		$where['id']=$_POST['stuid'];
		$st=M('hw001.student',null)->where($where)->find();
		$w['school']=$st['school'];
		$w['class']=$_POST['class'];
		if($_POST['count']!='undefined')$w['count']=array('elt',$_POST['count']);
		$m=M('hw003.school_policy',null)->where($w)->order('count desc')->find();
		if($m){
			$list=array('id'=>$m['id'],'count2'=>$m['count2'],'price'=>$mm['price'],'info'=>'报名：'.$m['count'].'课时，赠送：'.$m['count2'].'课时');
		}
			print(json_encode($list));
	}

	public function mapii(){
		$c=$_POST['cc']?$_POST['cc']:0;
		$d=$_POST['cd']?$_POST['cd']:0;
		$e=$_POST['ce']?$_POST['ce']:0;
		$f=$_POST['cf']?$_POST['cf']:0;
		$con=$c+$d+$e+$f;
		$where['id']=$_POST['stuid'];
		$st=M('hw001.student',null)->where($where)->find();
		$w['school']=$st['school'];
		$w['class']=$_POST['class'];
		if($_POST['count']!='undefined')$w['count']=array('elt',$_POST['count']);
		$m=M('hw003.school_policy',null)->where($w)->order('count desc')->find();
		if($m){
			//计算赠送课时比例
			$count2=$m['count2'];
			$list['cc']=round($count2*$c/$con,2)+$c;
			$list['cd']=round($count2*$d/$con,2)+$d;
			$list['ce']=round($count2*$e/$con,2)+$e;
			$list['cf']=round($count2*$f/$con,2)+$f;
		}
			//计算对应价格
			$w2['grade']=$st['grade'];
			$w2['school']=$st['school'];
			$w2['class']=$_POST['class'];
			$m2=M('hw003.person_level',null)->where($w2)->select();
			foreach ($m2 as $v) {
				$m3[$v['level']]=$v['price'];
			}
			$list['money']=$c*$m3['高级']+$d*$m3['3A']+$e*$m3['5A']+$f*$m3['7A'];
			print(json_encode($list));
	}
//退费计算，充分比较x与A+△B的大小计算最优惠的退费
	public function order_return($orid){
		$m=M('hw001.student_order',null)->find($orid);
		$stu=M('hw001.student',null)->find($m['stuid']);
		$w['school']=$m['school'];
		$w['class']=$m['class'];
		$w['count']=array('elt',$m['count2']-$m['count3']);
		$m2=M('hw003.school_policy',null)->where($w)->order('count desc')->select();
		$w['grade']=$stu['grade'];
		$m3=M('hw003.person_level',null)->where($w)->select();
		foreach ($m3 as $val) {
			$price[$val['level']]=$val['price'];
		}
		if($m2[0]){//实际消耗符合赠送等级
			if($m2[1]){//实际消耗的下一等级
				if($m['count2']-$m['count3']-$m2[0]['count']-$m2[1]['count2']>=0){//以当前赠送计算
					$msg['policy']='购买：'.$m2[0]['count'].'赠送：'.$m2[0]['count2'].'课时';
					$msg['policy_a']=$m2[0]['count'];
					$msg['policy_b']=$m2[0]['count2'];
					$msg['xs']=$m2[0]['count2']/($m2[0]['count']+$m2[0]['count2']);//赠送系数
				}else{//以下一等级计算
					$msg['policy']='购买：'.$m2[1]['count'].'赠送：'.$m2[1]['count2'].'课时';
					$msg['policy_a']=$m2[1]['count'];
					$msg['policy_b']=$m2[1]['count2'];
					$msg['xs']=$m2[1]['count2']/($m2[1]['count']+$m2[1]['count2']);//赠送系数
				}
			}else{
				if($m2[0]['count']+$m2[0]['count2']-($m['count2']-$m['count3'])){
					$msg['policy']='购买：'.$m2[0]['count'].'赠送：'.$m2[0]['count2'].'课时';
					$msg['policy_a']=$m2[0]['count'];
					$msg['policy_b']=$m2[0]['count2'];
					$msg['xs']=$m2[0]['count2']/($m2[0]['count']+$m2[0]['count2']);//赠送系数
				}else{
					$msg['policy']='购买：'.$m2[0]['count'].'赠送：'.$m2[0]['count2'].'课时';					
					$msg['policy_a']=$m2[0]['count'];
					$msg['policy_b']=$m2[0]['count2'];
					$msg['xs']=$m2[0]['count2']/($m2[0]['count']+$m2[0]['count2']);//赠送系数
				}
			}
		}

		if($m2){
			if($m['count2']-$m['count3']-$msg['policy_a']-$msg['policy_b']>=0){// 需要补充付款

				$cc=($m['cc2']-$m['cc3'])*$price['高级'];
				$cd=($m['cd2']-$m['cd3'])*$price['3A级'];
				$ce=($m['ce2']-$m['ce3'])*$price['5A级'];
				$cf=($m['cf2']-$m['cf3'])*$price['7A级'];

				$bk=$m['count2']-$m['count3']-$msg['policy_a']-$msg['policy_b'];//需要补款的课时量
				$msg['zs']=0;
			}else{
				$cc=($m['cc2']-$m['cc3'])*$price['高级'];
				$cd=($m['cd2']-$m['cd3'])*$price['3A级'];
				$ce=($m['ce2']-$m['ce3'])*$price['5A级'];
				$cf=($m['cf2']-$m['cf3'])*$price['7A级'];

				$bk=0;
				$msg['zs']=$msg['policy_a']+$msg['policy_b']-($m['count2']-$m['count3']);
			}
		}else{
			$cc=($m['cc2']-$m['cc3'])*$price['高级'];
			$cd=($m['cd2']-$m['cd3'])*$price['3A级'];
			$ce=($m['ce2']-$m['ce3'])*$price['5A级'];
			$cf=($m['cf2']-$m['cf3'])*$price['7A级'];

			$bk=0;
			$msg['zs']=0;
		}
			$money=$cc+$cd+$ce+$cf;//应退金额
		//组装发送的数据
		$list['name']=$stu['name'];
		$list['grade']=$stu['grade'];
		$list['class']=$m['class'];
		$list['cc']=$m['cc2']-$m['cc3'];
		$list['cd']=$m['cd2']-$m['cd3'];
		$list['ce']=$m['ce2']-$m['ce3'];
		$list['cf']=$m['cf2']-$m['cf3'];
		$list['cc_zs']=round(($m['cc2']-$m['cc3'])*$msg['xs'],2);
		$list['cd_zs']=round(($m['cd2']-$m['cd3'])*$msg['xs'],2);
		$list['ce_zs']=round(($m['ce2']-$m['ce3'])*$msg['xs'],2);
		$list['cf_zs']=round(($m['cf2']-$m['cf3'])*$msg['xs'],2);
		$list['cc_p']=$price['高级'];
		$list['cd_p']=$price['3A级'];
		$list['ce_p']=$price['5A级'];
		$list['cf_p']=$price['7A级'];
		$list['policy']=$m2?$msg['policy']:'没有符合条件的优惠政策';
		$list['zs']=$msg['zs'];
		$list['bk']=$bk;
		$list['money']=($list['cc']-$list['cc_zs'])*$price['高级']+($list['cd']-$list['cd_zs'])*$price['3A级']+($list['ce']-$list['ce_zs'])*$price['5A级']+($list['cf']-$list['cf_zs'])*$price['7A级'];
		$list['returnd']=$m['money']-$money;

			print(json_encode($list));

	}

//==============课程设置============
	public function class_set($id){

		$w['stuid']=$id;
		$s=M('hw001.student',null)->find($id);
		$w['state']=1;
		$m=M('hw001.student_teacher',null)->where($w)->select();
		$this->student=$s['name'];
		$this->stuid=$s['id'];
		$this->list=$m;
		$this->display();
	}

	public function class_api(){
		if ($_POST) {
			$w['stuid']=$_POST['stuid'];
			$w['state']=1;
			$m=M('hw001.student_order',null)->where($w)->order('lx asc')->select();
			if($m[0]['lx']=='预交款'){
				$msg='预交款计算';
			}else{
				foreach ($m as $val) {
					if($val['class']==$_POST['class'])$msg+=$val[$_POST['level']];
				}
				$msg=$msg?$msg:0;
			}
			print(json_encode($msg));
		}
	}

	public function class_teacher(){
		if($_POST['stuid']){
			$w['id']=$_POST['stuid'];
			$s=M('hw001.student',null)->where($w)->getField('school');
			$m=M('hw001.student_teacher',null);
			$m->create();
			$m->school=$s;
			if($m->add())$this->success();
		}
	}

}

?>