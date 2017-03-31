<?php
// 本类由系统自动生成，仅供测试用途
class TeacherAction extends CommAction {

    public function index(){
        $this->display();
    }

	public function fankui(){

		$wx['name']=session('name');
		if(!M('hw003.person_all',null)->where($wx)->find()){
			echo "<meta charset='utf-8'><br/><h1>访问受限，请联系小文处理……</h1>";
			die;
		}

		//旷课、试听确认按钮
		if($_POST['kuangke']){
			$w['id']=$_POST['cid'];
			M('hw001.class',null)->where($w)->setField('fankui','2');
			$this->redirect('fankui');
		}
		//授课反馈按钮
		if($_POST['cid']){
// 			$info=M('hw001.class',null)->find($_POST['cid']);
			$info=M('hw001.class',null)->where("id=".$_POST['cid'])->find();
			$m=M('hw001.fankui',null);
			$m->create();
			$m->week=date('W',$info['timee']);
			$m->teacher=$info['teacher'];
			$m->class=$info['class'];
			$m->date=$info['timee'];
			$m->school=$info['school'];
			if($_POST['fk_c'])$m->fk_c=implode(',', $_POST['fk_c']);
			$m->add();
			$w['id']=$_POST['cid'];
			M('hw001.class',null)->where($w)->setField('fankui','1');
			$this->redirect('fankui');
		}
		$w['teacher'] = session('name');
		$w['timee'] = array('between',array('2014-05-06',date('Y-m-d')));
		$w['fankui'] = 0;
		$m=M('hw001.class',null)->where($w)->order('timee asc,time1 asc')->field('id,stuid,timee,time1,time2,class')->select();
		foreach ($m as $v) {
			$s=$v;
			$s['student']=M('hw001.student',null)->field('name,type,xueguan')->find($v['stuid']);
			$m2[]=$s;
		}
		$this->list=$m2;
		$this->display();
	}


    
}
?>