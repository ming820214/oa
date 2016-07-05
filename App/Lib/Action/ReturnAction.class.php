<?php

class ReturnAction extends CommonAction {
    protected $config = array('app_type' => 'personal');

    public function add(){
        if($_POST['id']){
            $m=M('hw003.money_return',null);
            $m->create();
            if($_POST['x'])$m->state=1;
            if($m->save())
                R('Return/record',array($value,'修改'));//记录修改
                $this->success('数据修改成功……');
        }elseif($_POST){
            $m=M('hw003.money_return',null);
            $m->create();
            if($_POST['why'])$m->why1=implode('；',$_POST['why']);
            $m->school=session('schooll');
            $m->date=session('date');
            $m->time1=date('Y-m-d');
            $m->ka=session('user_name');
            if($m->add()){
                $this->redirect('Return/add');
            }else{
                $this->error('失败');
            }
            // var_dump($why1);
        }else{
            $w['date']=session('date');
            $w['state']=array('in','-1,0,1');
            $w['school']=session('schooll');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $w['state']=array('gt','1');
            $list2=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list2=$list2;
            $this->display();
        }
    }

    public function delt($state=-2){
        if($_POST['id'])
            foreach ($_POST['id'] as $key => $value) {
                $where['id']=$value;
                R('Return/record',array($value,'刪除'));
                M('hw003.money_return',null)->where($where)->setField('state',$state);
            }
        $this->success('删除成功！'); 
    }

      //修改数据回调使用
    public function api_c($id){
      if(isset($_POST['id'])&&$_POST['id']!=''){
        $where['id']=$_POST['id'];
        $shuchu=M('hw003.money_return',null)->where($where)->find();
        print(json_encode($shuchu));//将信息发送给浏览器
      } }

//财务确认
    public function check1(){
        if($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=2;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                //审核记录
                R('Return/record',array($value,'财务确认'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                R('Return/record',array($value,'数据退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $w['state']=array('in','0,-1');
                $rr=M('hw003.return','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $w['state']=1;
            $w['school']=session('schooll');
            $w['date']=session('date');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $w['state']=array('egt','2');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list2=$list;

            $this->js_file='js/check';
            $this -> display('check');

        }
    }

//校区审核
    public function check2(){
        if($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=3;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                //审核记录
                R('Return/record',array($value,'校区审核'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                R('Return/record',array($value,'数据退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $w['state']=array('in','0,-1');
                $rr=M('hw003.return','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $w['state']=2;
            $w['school']=session('schooll');

//给龙哥特别处理
            if(session('user_name')=='总裁'){
                $w['school']=array('like',array('水木清华','恒泰校区','日月兴城'),'OR');
            }
//给龙哥特别处理
            
            $w['date']=session('date');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $w['state']=array('egt','3');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list2=$list;
            
            $this->js_file='js/check';
            $this -> display('check');

        }
    }

//部门审核
    public function check3(){
        if($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=4;
                $d['time2']=date('Y-m-d');
                $rr=M('hw003.return','money_')->where($w)->save($d);
                //审核记录
                R('Return/record',array($value,'部门审核'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                R('Return/record',array($value,'数据退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $w['state']=array('in','0,-1');
                $rr=M('hw003.return','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $w['state']=3;
            $w['why3']=array('neq','');
            $w['date']=session('date');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $w['state']=array('in','4,5');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list2=$list;
            
            $this->js_file='js/check';
            $this -> display('check');

        }
    }

//总部沟通
    public function gt(){
        if($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=5;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                //审核记录
                R('Return/record',array($value,'集团审核'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                R('Return/record',array($value,'数据退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }else{
            $w['state']=3;
            $w['date']=session('date');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $w['state']=array('in','0,1,2');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list2=$list;
            
            $this->js_file='js/check';
            $this->why3='1';
            $this->bt='1';
            $this -> display('check');

        }
    }

//集团审批
    public function check4(){
        if($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=5;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                //审核记录
                R('Return/record',array($value,'集团审核'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.return','money_')->where($w)->save($d);
                R('Return/record',array($value,'数据退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $w['state']=array('in','0,-1');
                $rr=M('hw003.return','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $w['state']=4;
            $w['date']=session('date');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            //$w['state']=array('egt','5');
            //$list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            //$this->list2=$list;
            
            $this->js_file='js/check';
            $this -> display('check');

        }
    }

//退款确认
    public function check5(){
        if($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=6;
                $d['time3']=date('Y-m-d');
                $d['kb']=session('user_name');
                $rr=M('hw003.return','money_')->where($w)->save($d);
                //审核记录
                R('Return/record',array($value,'退款确认'));
            }
            if($rr){
                $this->success('退款完成！');
            }else{
                $this->success('选择要确认的条目！');
            }
        }else{
            $w['state']=5;
            $w['school']=session('schooll');
            $w['date']=session('date');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $w['state']=array('egt','6');
            $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
            $this->list2=$list;
            
            $this->js_file='js/check';
            $this->bt='1';
            $this -> display('check');
        }
    }

    public function all(){
        $ww['class']='school';
        $selt=M('hw003.money_sort',null)->where($ww)->select();
        $this->selt=$selt;

        $w['date']=session('date');
        $list=M('hw003.money_return',null)->where($w)->order('id desc')->select();
        $this->list=$list;
        
        $this->display();
    }

    public function import(){

        if($_POST['school'])$w['school']=$_POST['school'];
        if($_POST['date'])$w['date']=$_POST['date'];
        if($_POST['state']!='')$w['state']=6;
        $mm=M('hw003.return','money_')->where($w)->order('id desc')->select();

        $output = "<HTML>";
        $output .= "<HEAD>";
        $output .= "<META http-equiv=Content-Type content=\"text/html; charset=utf-8\">";
        $output .= "</HEAD>";
        $output .= "<BODY>";
        $output .= "<TABLE BORDER=1>";
        $output .= "<tr><td>序号</td><td>状态</td><td>期次</td><td>校区</td><td>学员姓名</td><td>年级</td><td>联系电话</td><td>教学主任</td><td>学习管理师</td><td>课程类型</td><td>缴费时间</td><td>交费总额</td><td>缴费课时数</td><td>已上课时</td><td>科目/教师</td><td>剩余计算</td><td>应退金额</td><td>退费原因</td><td>校区反馈</td><td>总部沟通</td><td>部门审核时间</td><td>办理人</td><td>申请时间</td><td>退费人</td><td>退费时间</td><td>备注</td></tr>";
            foreach ($mm as $m) {
                switch ($m['state']) {
                    case '0':
                        $state='退回修改';
                        break;
                    case '1':
                        $state='财务确认';
                        break;
                    case '2':
                        $state='校区审核';
                        break;
                    case '3':
                        $state='部门审核';
                        break;
                    case '4':
                        $state='集团审批';
                        break;
                    case '5':
                        $state='退款确认';
                        break;
                    case '6':
                        $state='退款完成';
                        break;
                }
            $output .= "<tr><td>".$m['id']."</td><td>".$state."</td><td>".$m['date']."</td><td>".$m['school']."</td><td>".$m['student']."</td><td>".$m['grade']."</td><td>".$m['tel']."</td><td>".$m['aa']."</td><td>".$m['bb']."</td><td>".$m['class']."</td><td>".$m['timed']."</td><td>".$m['ze']."</td><td>".$m['count']."</td><td>".$m['countd']."</td><td>".$m['km']."</td><td>".$m['sy']."</td><td>".$m['je']."</td><td>".$m['why1']."</td><td>".$m['why2']."</td><td>".$m['why3']."</td><td>".$m['time3']."</td><td>".$m['ka']."</td><td>".$m['time1']."</td><td>".$m['kb']."</td><td>".$m['time2']."</td><td>".$m['other']."</td></tr>";
            }
        $output .= "</TABLE>";
        $output .= "</BODY>";
        $output .= "</HTML>";

        $filename='财务系统退费明细导出表'.date('Y-m-d');
        header("Content-type:application/msexcel");
        header("Content-disposition: attachment; filename=$filename.xls");
        header("Cache-control: private");
        header("Pragma: private");
        print($output);
    }

    //审核记录
    public function record($id,$info){
        $w['id']=$id;
        $inf=M('hw003.return','money_')->where($w)->find();
        $d['record']=$inf['record'].'<'.$info.date('Y-m-d H:i:s').session('user_name').'>';
        M('hw003.return','money_')->where($w)->save($d);
    }


    //校区课时确认
    public function classd($school=null){
        $md=M('hw001.class',null);
        if($_GET['qr']){
            $md->where(array('id'=>(int)$_GET['qr'],'school'=>session('schooll')))->setField('cwqr',session('user_name'));
        }
        if($_POST['save']){
            if(!$_POST['why'])unset($_POST['why']);
            $md->create();
            if($_POST['count']!=(strtotime(date('Y-m-d ').$_POST['time2'].':00')-strtotime(date('Y-m-d ').$_POST['time1'].':00'))/3600)
                $this->error('课时有错误，请重新修改……');
                $md->save();
        }
        $w['school']=session('schooll');
        $w['timee']=$_POST['date']?$_POST['date']:date('Y-m-d');
        if($_GET['date'])$w['timee']=$_GET['date'];
        // if($school)$w['school']=$school;
        $m=$md->where($w)->order('time1,teacher')->select();
        foreach ($m as $k=>$v) {
            $m[$k]['student']=M('hw001.student',null)->where(array('id'=>$v['stuid']))->getField('name');
            if($v['stuid']==88888)$m[$k]['student']='@试听';
        }

        $this->date=$w['timee'];
        $this->list=$m;
        $this->display();
    }

}
?>