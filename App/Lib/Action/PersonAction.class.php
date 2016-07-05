<?php

class PersonAction extends CommonAction {
    protected $config = array('app_type' => 'personal');
    //过滤查询字段
    function map_search() {
        if($_POST['name'])$map['name'] = array('like', "%" . $_POST['name'] . "%");
        if($_POST['card'])$map['card'] = array('like', "%" . $_POST['card'] . "%");
        if($_POST['school'])$map['school'] = $_POST['school'];
        if($_POST['part'])$map['part'] = $_POST['part'];
        if($_POST['position'])$map['position'] = $_POST['position'];
        return $map;
    }

    function api_level($position){
        $level=M('hw003.person_level',null)->where(array('position'=>$position))->getField('level',true);
        print(json_encode(array_values(array_unique($level))));
    }

/**
* 
申请导出管理
*/

    public function _ask_import($class){

        $w['class']=$class;
        if(session('schooll')!='集团')$w['school']=session('schooll');
        $w['state']=array('in','审核通过,材料补充');
        $w['date|time1|time2|timestamp']=array('like',session('date')."%");
        $mm=M('hw003.person_ask',null)->where($w)->order('id desc')->select();

        $output = "<HTML>";
        $output .= "<HEAD>";
        $output .= "<META http-equiv=Content-Type content=\"text/html; charset=utf-8\">";
        $output .= "</HEAD>";
        $output .= "<BODY>";
        $output .= "<TABLE BORDER=1>";
        $output .= "<tr><td>序号</td><td>审核状态</td><td>项目类别</td><td>申请人</td><td>所在校区</td><td>所在部门</td><td>性质</td><td>申请时间</td><td>开始时间</td><td>结束时间</td><td>共计天数</td><td>内容</td><td>附件一</td><td>附件二</td><td>附件三</td><td>数据创建时间</td><td>审核记录</td><td>批语</td></tr>";
            foreach ($mm as $m) {
                if($m['pic1'])$m['pic1']='http://i.ihongwen.com/oa/Public/Uploads/ask/'.$m['pic1'];
                if($m['pic2'])$m['pic2']='http://i.ihongwen.com/oa/Public/Uploads/ask/'.$m['pic2'];
                if($m['pic3'])$m['pic3']='http://i.ihongwen.com/oa/Public/Uploads/ask/'.$m['pic3'];
                $output .= "<tr><td>".$m['id']."</td><td>".$m['state']."</td><td>".$m['class']."</td><td>".$m['name']."</td><td>".$m['school']."</td><td>".$m['part']."</td><td>".$m['aa']."</td><td>".$m['date']."</td><td>".$m['time1']."</td><td>".$m['time2']."</td><td>".$m['gong']."</td><td>".$m['info']."</td><td>".$m['pic1']."</td><td>".$m['pic2']."</td><td>".$m['pic3']."</td><td>".$m['timestamp']."</td><td>".$m['record']."</td><td>".$m['why']."</td></tr>";
            }
        $output .= "</TABLE>";
        $output .= "</BODY>";
        $output .= "</HTML>";

        $filename='申请明细导出表'.date('Y-m-d');
        header("Content-type:application/msexcel");
        header("Content-disposition: attachment; filename=$filename.xls");
        header("Cache-control: private");
        header("Pragma: private");
        print($output);
    }

/**
员工档案管理
*/  


    //职级管理
    public function position(){
        $data1=M('hw003.person_xc_rule1',null)->order('position,level')->getField('id,position,level',true);
        foreach ($data1 as $k => $v) {
            $data1[$k]['new']=M('hw003.person_level',null)->where(array('level'=>$v['level']))->getField('new');
        }
        $data2=M('hw003.person_xc_rule2',null)->order('position,level')->getField('id,position,level',true);
        foreach ($data2 as $k => $v) {
            $data2[$k]['new']=M('hw003.person_level',null)->where(array('level'=>$v['level']))->getField('new');
        }
        $this->data1=$data1;
        $this->data2=$data2;
        $this->display();
    }

    public function index(){

        $map = self::map_search();
        $map['state'] = 1; 
        $user=M('hw003.person_all',null);
        import('ORG.Util.Page');
        $count=$user->where($map)->count();
        $page= new Page($count,20);
        $show=$page->show();
        $list=$user->where($map)->order('id')->limit($page->firstRow.','.$page->listRows)->select();
        foreach ($list as $k => $v) {
            $listt[$k]=$v;
            $listt[$k]['info']=M('hw003.person_info',null)->field('sex')->find($v['id']);
        }
        $this->list=$listt;
        $this->page=$show;
        $this->display();
    }

    //添加和更新员工信息
    Public function add(){
        $m=M('hw003.person_info',null);
        $m2=M('hw003.person_all',null);
        if($_POST['pid']){
            $m->create();
            $m->id=$_POST['pid'];
            if($_POST['id'])$m->save();
            if(!$_POST['id'])$m->add();
            $m2->create();
            $m2->id=$_POST['pid'];
            $m2->save();
            $this->success('信息更新成功……');
            record('员工管理','更新档案,'.$_POST['pid'].','.$_POST['name']);
        }elseif ($_POST['name']&&!$_POST['id']) {
            unset($_POST['id']);
            $m2->create();
            $pid=$m2->add();
            if($pid){
                $m->create();
                $m->id=$pid;
                $id=$m->add();
                if(M('hw003.person_xc_5x1d',null)->add(array('id'=>$pid)))
                if(M('hw003.person_xc_fulid',null)->add(array('id'=>$pid)))
                if(R('Wage/wage',array($pid,$_POST['school'])))
                if($id)$this->success('信息添加成功……');
                record('员工管理','添加员工,'.$pid.','.$_POST['name']);
            }else{
                $this->error('操作失败……');
            }
        }else{
            $this->display('dangan');
        }
    }

    public function diaodong(){
        $w['aa']='调动';
        if($_POST['a_t'])$w['a_t']=$_POST['a_t'];
        if($_POST['a_b'])$w['a_b']=$_POST['a_b'];
        if($_POST['a_c'])$w['a_c']=$_POST['a_c'];
        if($_POST['a_d'])$w['a_d']=$_POST['a_d'];
        $m=M('hw003.person_record',null)->where($w)->order('a_t desc')->select();
        $dat=self::search($m);
        $this->data=$dat;
        $this->display();
    }

    public function jiangli(){
        $w['aa']='奖励';
        if($_POST['c_t'])$w['c_t']=$_POST['c_t'];
        $m=M('hw003.person_record',null)->where($w)->order('c_t desc')->select();
        $dat=self::search($m);
        $this->data=$dat;
        $this->display();
    }

    public function chufa(){
        $w['aa']='处罚';
        if($_POST['d_t'])$w['d_t']=$_POST['d_t'];
        $m=M('hw003.person_record',null)->where($w)->order('d_t desc')->select();
        $dat=self::search($m);
        $this->data=$dat;
        $this->display();
    }

    public function lizhi(){
        if($this->isAjax()){
            if($_POST['b_c']){
                M('hw003.person_record',null)->where(array('id'=>$this->_post('id')))->save(array('b_c'=>$this->_post('b_c')));
            }elseif ($_POST['fz']) {
                M('hw003.person_all',null)->where(array('id'=>$this->_post('fz')))->setField('state',1);
                M('hw003.person_record',null)->where(array('pid'=>$this->_post('fz')))->setField('hide',1);
                $name=M('hw003.person_all',null)->where(array('id'=>$_POST['fz']))->getField('name');
                record('员工管理','复职,'.$_POST['fz'].','.$name);
            }die;
        }
        $w['aa']='离职';
        $w['hide']=0;
        if($_POST['b_t'])$w['b_t']=$_POST['b_t'];
        if($_POST['b_c'])$w['b_c']=$_POST['b_c'];
        $m=M('hw003.person_record',null)->where($w)->order('b_t desc')->select();
        $dat=self::search($m);
        $this->data=$dat;
        $this->display();
    }

    //添加变动的相关信息
    public function record(){
        if($_POST['pid']){
            $m=M('hw003.person_record',null);
            $m->create();
            if($_POST['aa']=='调动'){
                $w['id']=$_POST['pid'];
                $d['school']=$_POST['a_c'];
                $d['part']=$_POST['a_a'];
                $d['position']=$_POST['a_b'];
                M('hw003.person_all',null)->where($w)->save($d);
            }
            if($_POST['aa']=='离职'){
                $w['id']=$_POST['pid'];
                $d['state']=0;
                $info=M('hw003.person_all',null)->where($w)->save($d);
                $name=M('hw003.person_all',null)->where($w)->getField('name');
                M('hw002.smeoa_user',null)->where(array('name'=>$name))->delete();
                M('hw001.user',null)->where(array('user'=>$name))->delete();
            }
            if($m->add())$this->success("操作成功");
            $name=M('hw003.person_all',null)->where(array('id'=>$_POST['pid']))->getField('name');
            record('员工管理',$_POST['aa'].','.$_POST['pid'].','.$name);
        }
    }

    //生日搜索
    public function birthday(){
        $map=self::map_search();
        $map['state'] = 1; 
        import('@.ORG.Util.Calendar');
        $cal=new Calendar();
        $m=M('hw003.person_all',null)->where($map)->getField('id,name,position,part,school',true);
        $m2=M('hw003.person_info',null)->where(array('birthday'=>array('neq','')))->getField('id,birthstyle,birthday',true);

        foreach ($m as $k=>$v) {
            $m3[$k]=$m2[$k]?array_merge($v,$m2[$k]):$v;
            if($m2[$k]['birthstyle']==1){
                $m3[$k]['date'] = $cal->L2S(date('Y-').$m2[$k]['birthday']);//转公历
                $m3[$k]['nl']   = substr(($cal->S2L($m3[$k]['date'])),15);//转农历
                $m3[$k]['sort']=(int)((strtotime($m3[$k]['date'])-time())/(24*3600));
            }elseif($m2[$k]['birthstyle']==0){
                $m3[$k]['date'] = date('Y-').$m2[$k]['birthday'];
                $m3[$k]['sort']=(int)((strtotime($m3[$k]['date'])-time())/(24*3600));
            }
        }
        $m3=array_sort($m3,'sort');
        $this->data=$m3;
        $this->display();
    }

    //获取员工信息
    public function info($id){
        $m=M('hw003.person_all',null)->find($id);
        $m2=M('hw003.person_info',null)->find($id);
        if($m2){
            $m=array_merge($m,$m2);
        }else{
            unset($m['id']);
        }
        $this->info=$m;
        $m2=M('hw003.person_record',null)->where(array('pid'=>$id))->order('timestamp')->select();
        foreach ($m2 as $v) {
            $data[$v['aa']][]=$v;
        }
        $this->data=$data;
        $this->display('dangan');
    }

//==============仅供员工管理的相关部分调用
    function search($m){//获取到查询的结果，首次过滤
        foreach ($m as $v) {
            $k=$v;
            $m2=M('hw003.person_info',null)->field('card,sex')->find($v['pid']);
            $m3=M('hw003.person_all',null)->field('name,part,position,school')->find($v['pid']);
            $k['card']=$m2['card'];
            $k['sex']=$m2['sex'];
            $k['part']=$m3['part'];
            $k['name']=$m3['name'];
            $k['position']=$m3['position'];
            $k['school']=$m3['school'];
            if($_POST['name']&&$_POST['name']==$m3['name'])$a[]=$k;
            if($_POST['school']&&$_POST['school']==$m3['school'])$b[]=$k;
            if($_POST['position']&&$_POST['position']==$m3['position'])$c[]=$k;
            if($_POST['card']&&$_POST['card']==$m3['card'])$d[]=$k;
            $dat[]=$k;
            unset($k);
        }
        //查询过滤
        if($a)$dat=array_jj($dat,$a);
        if($b)$dat=array_jj($dat,$b);
        if($c)$dat=array_jj($dat,$c);
        if($d)$dat=array_jj($dat,$d);
        return $dat;
    }

/**
级别管理
*/
// 业务副校长
    function level_a($position='业务副校长'){
        //更新业绩调用
        if($this->isAjax()){
            $pid=$this->_get('pid');
            $yeji=$this->_get('yeji');
            $pz=$this->_get('pz');
            M('hw003.person_leveld',null)->where(array('pid'=>$pid,'date'=>session('date')))->save(array('yeji'=>$yeji,'pz'=>$pz));
            $msg=self::_level_a_info(M('hw003.person_all',null)->find($pid));
            print(json_encode($msg));die;
        }
        $m=M('hw003.person_all',null)->where(array('position'=>$position,'state'=>1))->select();
        foreach ($m as $v) {
            $m2[]=self::_level_a_info($v);
        }
        $this->list=$m2;
        if($position=='业务副校长'){
            $this->display();
        }else{
            $this->display('level_b');
        }
    }
    function _level_a_info($m){
        $leveld=M('hw003.person_leveld',null);
        $w=array('pid'=>$m['id'],'date'=>session('date'));

        $info=$leveld->where($w)->find();if(!$info)$leveld->add($w);
        $m['school_mb']=M('hw003.person_level_mb',null)->where(array('school'=>$m['school'],'date'=>session('date')))->getField('yeji');
        $m['pz']=$info['pz'];
        $level=M('hw003.person_level',null)->where(array('position'=>$m['position'],'level'=>$m['level'],'pz'=>$m['pz']))->find();
        $m['bj_mubiao']=$m['school_mb']*$level['bj'];
        $m['yeji']=$info['yeji'];
        //团队业绩
        $team=implode(',',M('hw003.person_all',null)->where(array('position'=>'教学主任','school'=>$m['school']))->getField('id',true));
        $m['team_yeji']=M('hw003.person_leveld',null)->where(array('pid'=>array('in',$team),'date'=>session('date')))->sum('yeji');
        $m['sj_mubiao']=$m['school_mb']*$level['sj'];//升级目标的团队目标
        if($m['yeji']>=$m['bj_mubiao']){
            $m['new']=($m['team_yeji']>=$m['sj_mubiao'])?$level['new']:$m['level'];
        }else{
            $m['new']=M('hw003.person_level',null)->where(array('new'=>$m['level']))->getField('level');
        }
        return $m;
    }
// 咨询副校长
    public function level_b(){
        $this->level_a('咨询副校长');
    }
// 维护副校长
    public function level_c(){
        $m=M('hw003.person_all',null)->where(array('position'=>'维护副校长','state'=>1))->select();
        foreach ($m as $v) {
            $school=M('hw003.person_level_mb',null)->where(array('school'=>$v['school']))->find();
            $level=M('hw003.person_level',null)->where(array('position'=>'维护副校长','level'=>$v['level']))->find();
            $m2['school_xiaohao']=$school['xiaohao'];
            $m2['school_xufei']=$school['xufei'];
            $m2['bj_xiaohao']=$school['xiaohao']*$level['bj_xiaohao'];
            $m2['bj_xufei']=$school['xufei']*$level['bj_xufei'];
            $m2['sj_xiaohao']=$school['xiaohao']*$level['sj_xiaohao'];
            $m2['sj_xufei']=$school['xufei']*$level['sj_xufei'];
            $team=implode(',',M('hw003.person_all',null)->where(array('school'=>$v['school'],'position'=>'学习管理师'))->getField('id',true));
            $m2['team_xufei']=M('hw003.person_leveld',null)->where(array('pid'=>array('in',$team),'date'=>session('date')))->sum('yeji');
            if($m2['team_xiaohao']>=$m2['bj_xiaohao'] && $m2['team_xufei']>=$m2['bj_xufei']){
                $m2['new']=($m2['team_xiaohao']>=$m2['sj_xiaohao'] && $m2['team_xufei']>=$m2['sj_xufei'])?$level['new']:$v['level'];
            }else{
                $m2['new']=M('hw003.person_level',null)->where(array('new'=>$v['level']))->getField('level');
            }
            $m3[]=array_merge($v,$m2);
        }
        $this->list=$m3;
        $this->display();
    }
// 教学主任
    public function level_d(){
        //更新业绩调用
        if($this->isAjax()){
            $pid=$this->_get('pid');
            $yeji=$this->_get('yeji');
            $pz=$this->_get('pz');
            if(M('hw003.person_leveld',null)->where(array('pid'=>$pid,'date'=>session('date')))->save(array('yeji'=>$yeji,'pz'=>$pz))){
                $msg=self::_level_d_info(M('hw003.person_all',null)->find($pid));
                print(json_encode($msg));
            }
            die;
        }
        $map=self::map_search();
        $map['position']='教学主任';
        $map['state']=1;
        $m=M('hw003.person_all',null)->where($map)->select();
        foreach ($m as $v) {
            $m2[]=self::_level_d_info($v);
        }
        $this->list=$m2;
        $this->display();
    }

    public function _level_d_info($m){
        $info=M('hw003.person_leveld',null)->where(array('pid'=>$m['id'],'date'=>session('date')))->find();
        $m['yeji']=$info['yeji'];
        if($m['yeji']==null)M('hw003.person_leveld',null)->add(array('pid'=>$m['id'],'date'=>session('date')));//如果没有创建
        //查询校区目标
        $m['school_mb']=M('hw003.person_level_mb',null)->where(array('school'=>$m['school'],'date'=>session('date')))->getField('yeji');
        //查询校区配置
        $m['pz']=$info['pz'];
        //查询出升降级规则
        if($m['position'])$lv=M('hw003.person_level',null)->where(array('position'=>$m['position'],'level'=>$m['level'],'pz'=>$m['pz']))->find();
        if($lv){
            $m['baoji']=$m['school_mb']*$lv['bj'];
            $m['shenji']=$m['school_mb']*$lv['sj'];
            if($m['yeji']>=$m['baoji'])$m['new']=($m['yeji']>=$m['shenji'])?$lv['new']:$m['level'];
            if($m['yeji']<$m['baoji'])$m['new']=M('hw003.person_level',null)->where(array('new'=>$m['level']))->getField('level');
        }
        return $m;
    }

// 学习管理师
    public function level_e(){
        if($this->isAjax()){
            $w['pid']=(int)$_GET['pid'];
            $w['date']=session('date');
            $d['mubiao']=(int)$_GET['mubiao'];
            $d['yeji']=(int)$_GET['yeji'];
            if(M('hw003.person_leveld',null)->where($w)->save($d)){
                $data=self::level_e_info(M('hw003.person_all',null)->find($w['pid']));
                print(json_encode($data));
            }
            die;
        }
        $map=self::map_search();
        $map['position']='学习管理师';
        $map['state']=1;
        $m=M('hw003.person_all',null)->where($map)->select();
        foreach ($m as $v) {
            $m2[]=self::level_e_info($v);
        }
        $this->list=$m2;
        $this->display();
    }

    public function level_e_info($m){
        $leveld=M('hw003.person_leveld',null);
        $w=array('pid'=>$m['id'],'date'=>session('date'));
        $level=M('hw003.person_level',null)->where(array('position'=>$m['position'],'level'=>$m['level']))->find();

        $info=$leveld->where($w)->find();if(!$info)$leveld->add($w);
        $m['mubiao']=$info['mubiao'];
        $m['yeji']=$info['yeji'];
        $m['count']=M('hw001.student',null)->where(array('xueguan'=>$m['name'],'state'=>1))->count();
        $m['bj_mubiao']=$m['mubiao']*$level['bj'];
        $m['sj_mubiao']=$m['mubiao']*$level['sj'];
        $m['sj_count']=$level['pz']?$level['pz']:'';
        if($m['yeji']>=$m['bj_mubiao']){
            $m['new']=($m['count']>=$m['sj_count'] && $m['mubiao']>=$m['sj_mubiao'])?$level['new']:$m['level'];
        }else{
            $m['new']=M('hw003.person_level',null)->where(array('new'=>$m['level']))->getField('level');
        }
        return $m;
    }
// 讲师
    public function level_f(){
        if($this->isAjax()){
            $w['pid']=(int)$_GET['pid'];
            $w['date']=session('date');
            $d['tc_a']=(int)$_GET['aa'];
            $d['tc_c']=(int)$_GET['c'];
            $d['tc_d']=(int)$_GET['d'];
            $d['tc_pass']=(int)$_GET['tc_pass'];
            if(M('hw003.person_leveld',null)->where($w)->save($d)){
                $data=self::level_f_info(M('hw003.person_all',null)->find($w['pid']));
                print(json_encode($data));
            }
            die;
        }
        $map=self::map_search();
        $map['position']='讲师';
        $map['state']=1;
        $user=M('hw003.person_all',null);
        import('ORG.Util.Page');
        $count=$user->where($map)->count();
        $page= new Page($count,20);
        $show=$page->show();
        $m=$user->where($map)->order('id')->limit($page->firstRow.','.$page->listRows)->select();
        foreach ($m as $v) {
            $m2[]=self::level_f_info($v);
        }
        $this->list=$m2;
        $this->page=$show;
        $this->display();
    }

    public function level_f_info($v){
        $level=M('hw003.person_level',null)->where(array('position'=>$v['position'],'level'=>$v['level']))->find();
        $v['tc_a']=$level['tc_a'];
        $v['tc_b']=$level['tc_b'];

        $leveld=M('hw003.person_leveld',null)->where(array('pid'=>$v['id'],'date'=>session('date')))->find();
        if(!$leveld)M('hw003.person_leveld',null)->add(array('pid'=>$v['id'],'date'=>session('date')));
        $v['a']=$leveld['tc_a'];
        $v['b']=$leveld['tc_b'];
        $v['c']=$leveld['tc_c'];
        $v['d']=$leveld['tc_d'];
        $v['tc_pass']=$leveld['tc_pass'];
        $v['e']=$level['tc_e'];
        $v['f']=$level['tc_f'];
        //讲师统计课时量
        $w=array('teacher'=>$v['name'],'grade'=>0);
        $w['timee']=array('like',session('date')."%");
        $count=round(M('hw001.class',null)->where($w)->sum('count'),2);
        $w['grade']=array('neq',0);
        $count2=M('hw001.class',null)->where($w)->order('timee,time1')->select();
        foreach ($count2 as $v2) {
            if($time1!=$v2['time1']&&$timee!=$v2['timee'])$count+=$v2['count'];
            $timee=$v2['timee'];
            $time1=$v2['time1'];
        }
        unset($timee,$time1);
        $v['count']=$count;
        $v['new']=$v['level'];
        if($leveld['tc_a']>=$level['tc_a']&&$leveld['tc_b']>=$level['tc_b']&&$count>=$level['tc_c']&&$leveld['tc_d']>=$level['tc_d']&&$v['level'])$v['dabiao']=1;
        if($v['dabiao']&&$leveld['tc_pass']&&$v['level']){
            $v['new']=$level['new'];
        }
        return $v;

    }
//校区目标设置
    public function level_school(){
        $m=M('hw003.person_level_mb',null);
        if($_GET['delt']){
            $m->delete((int)$_GET['delt']);
        }
        if($_POST){
            $m->create();
            $m->date=session('date');
            $m->add();
        }
        $this->data=$m->where(array('date'=>session('date')))->select();
        $this->display();
    }

    public function level_level(){
        $mod=M('hw003.person_level',null);
        $this->list=$mod->order('position,level')->select();
        $this->display();
    }

/**
考勤管理部分
*/
    //考勤机同步考勤记录
    function kq_api(){

        $start=M('hw003.person_kq',null)->max('date');
        $end=date('Y-m-d');
        // $start='2015-05-04';
        // $end='2015-05-05';
            $stime= microtime(true);//程序开始执行的时间
            $ch = curl_init();
            $time = time();
            $data = array(
                'account'=>'21c4a357f585a1a50ea794fcf96fad73',//API帐号
                'requesttime'=>$time,//请求时间，与服务器时间差不能超过60秒
                );
            $data['start'] = $start;
            $data['end'] = $end;
            ksort($data);
            $sign = md5(join('',$data).'hongwenhr001');
            $data['sign'] = $sign;
            // var_dump($data);
            curl_setopt($ch, CURLOPT_URL, "http://kq.qycn.com/index.php/Api/Api/recordlog?".http_build_query($data));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $retjson = curl_exec($ch); //返回的数据，json格式
            curl_close($ch);
            $s=json_decode($retjson)->data->totalpage;

            // var_dump($s);

        for ($i=$s; $i >0 ; $i--) {
            $ch = curl_init();
            $time = time();
            $data = array(
                'account'=>'21c4a357f585a1a50ea794fcf96fad73',//API帐号
                'requesttime'=>$time,//请求时间，与服务器时间差不能超过60秒
                );
            $data['start'] = $start;
            $data['end'] = $end;
            $data['page']= $i;
            ksort($data);
            $sign = md5(join('',$data).'hongwenhr001');
            $data['sign'] = $sign;
            // var_dump($data);
            curl_setopt($ch, CURLOPT_URL, "http://kq.qycn.com/index.php/Api/Api/recordlog?".http_build_query($data));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $retjson = curl_exec($ch); //返回的数据，json格式
            curl_close($ch);
            $m2=json_decode($retjson)->data->attendata;
            foreach ($m2 as $v) {
                if($v->atten_time-strtotime($v->atten_date) < 4*3600){
                    $atten_date=date('Y-m-d',strtotime($v->atten_date)-24*3600);
                    $atten_time=strtotime($v->atten_date)-1;
                }else{
                    $atten_date=$v->atten_date;
                    $atten_time=$v->atten_time;
                }
                $mm[]=array('id'=>"$v->atten_id",'cc'=>"$v->atten_uid",'school'=>"$v->remark",'time'=>$atten_time,'date'=>$atten_date,'device'=>"$v->atten_device");
            }
            unset($ch);
            M('hw003.person_kq',null)->addAll($mm,'',true);
            if((time()-$stime)>120)$this->error('已同步到：'.$start.'，<br/>继续获取中……',U('kq_api'));
        }
        $this->success('数据已全部同步……',U('kq_info'));
    }

    // 考勤具体规则
    public function kq_rule(){
        if(IS_AJAX){
            if($_POST['id']){
                if(M("hw003.person_kq_rule",null)->where(array('id'=>$_POST['id']))->setField('name',$_POST['name']))print(json_encode(1));die;
            }
        }
        if($_GET['delt']){
            M("hw003.person_kq_rule",null)->where(array('id'=>$_GET['delt']))->setField('state',1);die;
        }
        if($_POST['ruleadd']){
            $m=M('hw003.person_kq_rule',null);
            $m->create();
            $m->m1a=$_POST['m1a'].':00';
            $m->m1b=$_POST['m1b'].':00';
            $m->m2a=$_POST['m2a'].':00';
            $m->m2b=$_POST['m2b'].':00';
            $m->m3a=$_POST['m3a'].':00';
            $m->m3b=$_POST['m3b'].':00';
            $m->m4a=$_POST['m4a'].':00';
            $m->m4b=$_POST['m4b'].':00';
            $m->m5a=$_POST['m5a'].':00';
            $m->m5b=$_POST['m5b'].':00';
            $m->m6a=$_POST['m6a'].':00';
            $m->m6b=$_POST['m6b'].':00';
            $m->m7a=$_POST['m7a'].':00';
            $m->m7b=$_POST['m7b'].':00';
            $m->add();
        }
        $w['school']=$_GET['school']?$_GET['school']:'';
        $w['state']=0;
        $this->list=M('hw003.person_kq_rule',null)->where($w)->select();
        $this->display();
    }

    //考勤分组
    public function kq_rules(){
        //获取rules
        if(IS_AJAX){
            //修改时获取考勤方案
            if($_GET['rules']){
                $rules=explode(',',M('hw003.person_kq_rules',null)->where(array('id'=>$_GET['rules']))->getField('ruleid'));
                print(json_encode($rules));die;
            }
            //重命名回调
            if($_POST['id']){
                if(M("hw003.person_kq_rules",null)->where(array('id'=>$_POST['id']))->setField('name',$_POST['name']))print(json_encode(1));
                die;
            }
        }
        //删除方案组
        if($_GET['delt']){
            M('hw003.person_kq_rules',null)->delete($_GET['delt']);
            M('hw003.person_kq_ruled',null)->where(array('rules'=>$_GET['delt'],'date'=>session('date')))->setField('rules',0);
        }
        //方案组顺延操作
        if($_GET['syid']){
            $d=M('hw003.person_kq_rules',null)->find($_GET['syid']);
            unset($d['id'],$d['timestamp']);
            $d['date']=date('Y-m',strtotime(session('date'))+40*24*3600);
            M('hw003.person_kq_rules',null)->add($d);
        }
        if($_POST['guanlian']){
            $d['name']=$_POST['name'];
            $d['school']=$_GET['school'];
            $d['ruleid']=implode(',',$_POST['rule']);
            $d['date']=session('date');
            M('hw003.person_kq_rules',null)->add($d);
        }
        if($_POST['bangding']){
            $w['school']=$_GET['school'];
            if($_POST['position'])$w['position']=$_POST['position'];
            if($_POST['pid'])$w['id']=$_POST['pid'];
            if($_POST['name'])$w['name']=$_POST['name'];
            if($w)$m=M('hw003.person_all',null)->where($w)->getField('id',true);
            foreach ($m as $v) {
                $d['date']=session('date');
                $d['pid']=$v;
                if(M('hw003.person_kq_ruled',null)->where(array('date'=>session('date'),'pid'=>$v))->find()){
                    $dd['rules']=$_POST['rules'];
                    M('hw003.person_kq_ruled',null)->where($d)->save($dd);
                }else{
                    $d['rules']=$_POST['rules'];
                    M('hw003.person_kq_ruled',null)->add($d);
                }
            }
        }
        //修改方考勤案组
        if($_POST['change']){
            $d['id']=$_POST['id'];
            $d['name']=$_POST['name'];
            $d['school']=$_GET['school'];
            $d['ruleid']=implode(',',$_POST['rule']);
            $d['date']=session('date');
            M('hw003.person_kq_rules',null)->save($d);
        }
        //切换校区
        if($_GET['school']){
            $w2['date']=session('date');
            $w2['school']=$_GET['school'];
            $list=M('hw003.person_kq_rules',null)->where($w2)->select();
            foreach ($list as $k2=>$v2) {
                $name=explode(",",$v2['ruleid']);
                foreach ($name as $k3=>$v3) {
                    $name[$k3]=M('hw003.person_kq_rule',null)->where(array('id'=>$v3))->getField('name');
                }
                $list[$k2]['ruleid']=$name;
            }
            $this->list=$list;
            $w3['school']=array('in',$_GET['school'].',');
        }
        $month[0]=date('W',strtotime(session('date').'-01'));//开始周次
        $month[1]=date('W',strtotime(session('date').'-'.date('t',strtotime(session('date').'-01'))));//结束周次
        $w3['state']=0;
        $this->rule=M('hw003.person_kq_rule',null)->where($w3)->getField('id,name,m1a',true);
        $this->month=$month;
        $this->display();
    }

    public function kq_ruled(){
        $w=self::map_search();
        $w['school']=$w['school']?$w['school']:session('schooll');
        $lzid=M('hw003.person_record',null)->where(['aa'=>'离职','b_t'=>['like',session('date')."%"]])->getField('pid',true);
        if($lzid){
            $wx['state']=1;
            $wx['id']=['in',$lzid];
            $wx['_logic']='or';
            $w['_complex']=$wx;
        }else{
            $w['state']=1;
        }
        $m=M('hw003.person_all',null)->where($w)->order('id')->getField('id,name,school,part,position',true);
        foreach ($m as $k=>$v) {
            $rules=M('hw003.person_kq_ruled',null)->where(array('date'=>session('date'),'pid'=>$k))->find();
            $m[$k]['rules']=M('hw003.person_kq_rules',null)->where(array('id'=>$rules['rules']))->getField('name');
            if(!$rules)M('hw003.person_kq_ruled',null)->add(array('date'=>session('date'),'pid'=>$k));
            if($_POST['state']&&$_POST['state']==1 && !$rules['rules'])unset($m[$k]);
            if($_POST['state']&&$_POST['state']==2 && $rules['rules'])unset($m[$k]);
        }
        $this->list=$m;
        $this->display();
    }

    //打卡明细
    public function kq_info(){
        if(IS_POST){
            $w=self::map_search();
            $w['school']=$w['school']?$w['school']:session('schooll');
            $all=M('hw003.person_all',null)->where($w)->getField('cc,id,name,school,position',true);
            $cc=M('hw003.person_all',null)->where($w)->getField('cc',true);
            $w2['cc']=['in',$cc];
            $w2['date']=['BETWEEN',[$_POST['date1'],$_POST['date2']]];
            $dat=M('hw003.person_kq2',null)->where($w2)->select();
            $this->all=$all;
            $this->data=$dat;
        }
        $this->display();
    }

    public function kq_a(){
        $w=self::map_search();
        if(session('schooll')!='集团')$w['school']=session('schooll');
        $w['class']='意外事项';
        if($_POST['import'])self::_ask_import('意外事项');

        $w['date|time1|time2|timestamp']=array('like',session('date')."%");
        $w['state']=array('not in','申请失败,退回修改');
        $m=M('hw003.person_ask',null)->where($w)->order('date desc')->getField('id,state,date,name,school,info,record',true);
        foreach ($m as $v) {
            if(substr_count($v['info'],'上班未打卡，')){$v['info']=str_replace('上班未打卡，','',$v['info']);$v['wdk']='上班';}
            if(substr_count($v['info'],'下班未打卡，')){$v['info']=str_replace('下班未打卡，','',$v['info']);$v['wdk']=$v['wdk'].'下班';}
            $mm[]=$v;
        }
        $this->data=$mm;
        $this->display();
    }

    public function kq_b(){
        if($_POST['import'])self::_ask_import('灵活作息');
        $w=self::map_search();
        if(session('schooll')!='集团')$w['school']=session('schooll');
        $w['state']=array('not in','申请失败,退回修改');
        $w['date|time1|time2|timestamp']=array('like',session('date')."%");
        $w['class']='灵活作息';
        $m=M('hw003.person_ask',null)->where($w)->order('date desc')->getField('id,state,date,name,school,info,record',true);
        $this->data=$m;
        $this->display();
    }

    public function kq_c(){
        if($_POST['import'])self::_ask_import('请假');
        $w=self::map_search();
        if(session('schooll')!='集团')$w['school']=session('schooll');
        $w['state']=array('not in','申请失败,退回修改');
        $w['date|time1|time2|timestamp']=array('like',session('date')."%");
        $w['class']='请假';
        $m=M('hw003.person_ask',null)->where($w)->order('time1 desc')->select();
        $this->data=$m;
        $this->display();
    }

    public function kq_cc(){//灵活假期
        $w=self::map_search();
        if(session('schooll')!='集团')$w['school']=session('schooll');
        $w['state']=array('not in','申请失败,退回修改');
        $w['class']='请假';
        $w['aa']='灵活假期';
        $m=M('hw003.person_ask',null)->where($w)->order('time1 desc')->select();
        $this->data=$m;
        $this->display();
    }

    public function kq_d(){
        if($_POST['import'])self::_ask_import('加班');
        $w=self::map_search();
        if(session('schooll')!='集团')$w['school']=session('schooll');
        $w['state']=array('not in','申请失败,退回修改');
        $w['date|time1|time2|timestamp']=array('like',session('date')."%");
        $w['class']='加班';
        $m=M('hw003.person_ask',null)->where($w)->order('date desc')->getField('id,state,date,name,school,info,record,time1,time2',true);
        $this->data=$m;
        $this->display();
    }

    //获取某月的考勤规则
    public function kq_kq_rule($pid,$month){
        $ruled=M('hw003.person_kq_ruled',null)->where(array('pid'=>$pid,'date'=>$month))->getField('rules');//获取考勤组id
        $ruleid=M('hw003.person_kq_rules',null)->find($ruled);
        if(!$ruleid)return false;
        $rules=explode(',',$ruleid['ruleid']);//获取各周的考勤规则id
        foreach ($rules as $v) {
            $rule[]=M('hw003.person_kq_rule',null)->find($v);
        }
        $c=strtotime($month.'-01');
        $monday=$c-((date('w',$c)==0?7:date('w',$c))-1)*24*3600;
        foreach ($rule as $v) {
            $kq[date('Y-m-d',$monday)]['t1']=($v['m1a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m1a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m1b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m1b']):0;
                $monday+=24*3600;
            $kq[date('Y-m-d',$monday)]['t1']=($v['m2a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m2a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m2b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m2b']):0;
                $monday+=24*3600;
            $kq[date('Y-m-d',$monday)]['t1']=($v['m3a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m3a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m3b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m3b']):0;
                $monday+=24*3600;
            $kq[date('Y-m-d',$monday)]['t1']=($v['m4a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m4a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m4b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m4b']):0;
                $monday+=24*3600;
            $kq[date('Y-m-d',$monday)]['t1']=($v['m5a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m5a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m5b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m5b']):0;
                $monday+=24*3600;
            $kq[date('Y-m-d',$monday)]['t1']=($v['m6a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m6a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m6b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m6b']):0;
                $monday+=24*3600;
            $kq[date('Y-m-d',$monday)]['t1']=($v['m7a']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m7a']):0;
            $kq[date('Y-m-d',$monday)]['t2']=($v['m7b']!='00:00:00')?strtotime(date('Y-m-d',$monday).' '.$v['m7b']):0;
                $monday+=24*3600;
        }
        return $kq;
    }

    //考勤记录计算,dat获取某天的打卡数据
    function kq($pid,$month,$dat=null){
        //离职员工处理
        if(M('hw003.person_all',null)->where(['id'=>$pid,'state'=>0])->find()){
            $lizhi=M('hw003.person_record',null)->where(['pid'=>$pid,'aa'=>'离职','b_t'=>['like',$month."%"]])->order('timestamp desc')->find();
            if($lizhi)$lizhi=$lizhi['b_t'];
        }
        $t=date('t',strtotime($month.'-01'));
        $rule=$this->kq_kq_rule($pid,$month);
        if($rule){
            $kq=M('hw003.person_kq2',null)->where(array('pid'=>$pid,'date'=>array('like',$month."%")))->getField('date,t1,t2');
            $month=strtotime($month.'-01');
            if($dat){
                $dat=array('t1'=>$rule[$dat]['t1'],'t2'=>$rule[$dat]['t2'],'t11'=>$kq[$dat]['t1'],'t22'=>$kq[$dat]['t2']);
                return $dat;//打卡异常详情
            }

            for ($i=0; $i < $t; $i++) {
                $day=date('Y-m-d',$month+$i*24*3600);
                if($day>=$lizhi && $lizhi)break;
                if($rule[$day]['t1']){//有上班时间规则开始计算
                    $data['r']++;//应出勤天数
                    $data['rt']+=$rule[$day]['t2']-$rule[$day]['t1'];//应出勤秒
                    if(!isset($kq[$day])){//都没打卡，旷工
                        $data['dd'][]=$day;
                    }elseif ($kq[$day]['t1']==$kq[$day]['t2']) {//打一次卡，未打卡
                        $data['cc'][]=$day;
                    }elseif ($kq[$day['t1']]>$rule[$day]['t1']) {//首次打卡时间大于规则，迟到（也会包含早退的情况）
                        $data['aa'][]=$day;
                    }elseif ($kq[$day]['t1']<=$rule[$day]['t1']&&$kq[$day]['t2']<$rule[$day]['t2']) {//首次正常，第二次早退
                        $data['bb'][]=$day;
                    }
                }
            }
        }

        $data['aa']=implode('|', $data['aa']);
        $data['bb']=implode('|', $data['bb']);
        $data['cc']=implode('|', $data['cc']);
        $data['dd']=implode('|', $data['dd']);
        return $data;
    }

    //相关申请数据,type:1请假，2加班，3灵活作息，4意外事项
    function kq_apply($pid,$date,$type=0){
        // $w['state']='审核通过';
        $w['pid']=$pid;
        $mod=M('hw003.person_ask',null);
        if($type){
            switch ($type) {
                case '1':
                    $w['class']='请假';
                    $w['time1']=array('lt',$date.' 00:00:00');
                    $w['time2']=array('gt',$date.' 23:59:59');
                    $dat1=$mod->where(array('time1|time2'=>array('like',$date."%"),'pid'=>$pid,'class'=>'请假'))->find();//当天请假的情况
                    $dat2=$mod->where($w)->find();
                    $data=$dat1?$dat1:$dat2;
                    return $data;
                // case '2':
                //     $w['class']='加班';
                //     $w['time1']=array('lt',$date.' 23:59:59');
                //     $w['time2']=array('gt',$date.' 23:59:59');
                //     return $mod->where($w)->find();
                case '3':
                    $w['class']='灵活作息';
                    $w['date']=$date;
                    return $mod->where($w)->find();
                case '4':
                    $w['class']='意外事项';
                    $w['date']=$date;
                    return $mod->where($w)->find();
                default:
                    break;
            }
        }else{
            $w['class']='请假';
            $w['time1']=array('lt',$date.' 00:00:00');
            $w['time2']=array('gt',$date.' 23:59:59');
            $dat1=$mod->where(array('time1|time2'=>array('like',$date."%"),'pid'=>$pid,'class'=>'请假'))->find();//当天请假的情况
            $dat2=$mod->where($w)->find();
            $data[]=$dat1?$dat1:$dat2;
            // $w['class']='加班';
            // $data[]=$mod->where($w)->find();
            unset($w['time1'],$w['time2']);
            $w['class']='灵活作息';
            $w['date']=$date;
            $data[]=$mod->where($w)->find();
            $w['class']='意外事项';
            $data[]=$mod->where($w)->find();
            return $data;
        }
    }

    //重新生成考勤报表
    public function kq_rest($pid=null,$type=0){
        if($pid)$w['pid']=$pid;
        $mod=M('hw003.person_kq_ruled',null);
        $w['date']=session('date');
        $d['aa']=$d['bb']=$d['cc']=$d['dd']=$d['ee']=$d['qingjia']=$d['jiaban']=$d['save']=$d['r']=$d['s']=$d['t']=$d['rt']='';
        $step1=$mod->where($w)->save($d);//清空数据
        $m=$mod->where($w)->getField('id,pid',true);
        foreach ($m as $k => $v) {
            $d=$this->kq($v,session('date'));
            M('hw003.person_kq_ruled',null)->where(array('id'=>$k,'date'=>session('date')))->save($d);
            //重置微信申请的数据
            $where['date']   = array('like', session('date')."%");
            $where['time1']  = array('like', session('date')."%");
            $where['time2']  = array('like', session('date')."%");
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
            $map['pid']  = $v;
            M('hw003.person_ask',null)->where($map)->setField('kq',0);
        }
        if($type==0){
            $this->success('报表已重新生成……');
        }else{
            $this->redirect('kq_kq');
        }
    }

    public function kq_kq($school=null){
        $w = self::map_search();
        $lzid=M('hw003.person_record',null)->where(['aa'=>'离职','b_t'=>['like',session('date')."%"]])->getField('pid',true);
        if($lzid){
            $wx['state']=1;
            $wx['id']=['in',$lzid];
            $wx['_logic']='or';
            $w['_complex']=$wx;
        }else{
            $w['state']=1;
        }
        $w['school']=$w['school']?$w['school']:session('schooll');
        $m=M('hw003.person_all',null)->where($w)->getfield('id,name,school,part,position',true);
        foreach ($m as $k => $v) {
            $kq=M('hw003.person_kq_ruled',null)->where(array('date'=>session('date'),'pid'=>$k))->find();
            if($kq){
                //考勤数据
                $m[$k]['kq']=$kq;
                $m[$k]['kq']['aa']=$kq['aa']?count(explode('|', $kq['aa'])):0;
                $m[$k]['kq']['bb']=$kq['bb']?count(explode('|', $kq['bb'])):0;
                $m[$k]['kq']['cc']=$kq['cc']?count(explode('|', $kq['cc'])):0;
                $m[$k]['kq']['dd']=$kq['dd']?count(explode('|', $kq['dd'])):0;
                $w=array('pid'=>$k,'time1'=>array('like',session('date')."%"),'class'=>'请假','kq'=>0);
                if(M('hw003.person_ask',null)->where(['kq'=>0,'pid'=>$k,'class'=>'请假','time1'=>['like',session('date')."%"]])->find())$m[$k]['kq']['qingjia']='待处理';
                $w=array('pid'=>$k,'time1'=>array('like',session('date')."%"),'class'=>'加班','kq'=>0);
                if(M('hw003.person_ask',null)->where($w)->find())$m[$k]['kq']['jiaban']='待处理';
                $m[$k]['kq']['s']=$kq['r']-$m[$k]['kq']['aa']-$m[$k]['kq']['bb']-$m[$k]['kq']['cc']-3*$m[$k]['kq']['dd']+$kq['qingjia']+$kq['jiaban']+$kq['ee'];
                $m[$k]['kq']['t']=($kq['aa']||$kq['bb']||$kq['cc']||$kq['dd'])?'否':'满勤';
            }
        }
        $this->data=$m;
        $this->display();
    }

    //查询异常数据并确认
    public function kq_kq_content($pid,$aa){
        $name=M('hw003.person_all',null)->getFieldbyid($pid,'name');
        if($aa) {
            if($aa=='aa'||$aa=='bb'||$aa=='cc'||$aa=='dd'){
                $m=M('hw003.person_kq_ruled',null)->where(array('pid'=>$pid,'date'=>session('date')))->getField($aa);
                $th='<th>日期</th><th>规定上班</th><th>首次打卡</th><th>规定下班</th><th>最后打卡</th><th>核算出勤</th>';
                $l=explode('|', $m);
                if($m)
                foreach ($l as  $v) {
                    $kq=$this->kq($pid,session('date'),$v);
                    $tr.='<tr><td>'.$v.'</td><td>'.date('H:i:s',$kq['t1']).'</td><td>'.(($kq['t11'])?date('H:i:s',$kq['t11']):'无').'</td><td>'.date('H:i:s',$kq['t2']).'</td><td>'.(($kq['t22'])?date('H:i:s',$kq['t22']):'无').'</td><td><a class=\'btn btn-minier btn-danger\' onclick='.'"kq_delt(this,\''.$aa.'\',\''.$v.'\','.$pid.')"'.'>不记入</a></td></tr>';
                    $ask=$this->kq_apply($pid,$v,1);
                    if($ask)$tr.='<tr><td colspan=\'5\'>请假：'.$ask['aa'].';时间：'.$ask['time1'].'--'.$ask['time2'].';共计'.$ask['gong'].'天;请假原因,'.$ask['info'].';'.($ask['pic1']?('<a href=\'./Public/Uploads/ask/'.$ask['pic1'].'\' target=\'blank\'>&nbsp;附件1</a>'):'').($ask['pic2']?('<a href=\'./Public/Uploads/ask/'.$ask['pic2'].'\' target=\'blank\'>&nbsp;附件2</a>'):'').($ask['pic3']?('<a href=\'./Public/Uploads/ask/'.$ask['pic3'].'\' target=\'blank\'>&nbsp;附件3</a>'):'').'</td><td>'.$ask['state'].'</td></tr>';
                    $ask=$this->kq_apply($pid,$v,3);
                    if($ask)$tr.='<tr><td colspan=\'5\'>灵活作息：时间，'.$ask['date'].';申请原因,'.$ask['info'].'</td><td>'.$ask['state'].'</td></tr>';
                    $ask=$this->kq_apply($pid,$v,4);
                    if($ask)$tr.='<tr><td colspan=\'5\'>意外事项：时间，'.$ask['date'].';申请原因,'.$ask['info'].'</td><td>'.$ask['state'].'</td></tr>';
                }
            }elseif ($aa=='qingjia') {
                $th='<th>请假类型</th><th>开始时间</th><th>结束时间</th><th>共计/天</th><th>申请原因</th><th>附件</th><th>审核状态</th><th>核算出勤</th>';
                $where['time1']  = array('like', session('date')."%");
                $where['time2']  = array('like', session('date')."%");
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $map['pid']  = $pid;          
                $map['class']  = '请假';
                $ask=M('hw003.person_ask',null)->where($map)->select();
                if($ask){
                    foreach ($ask as $v) {
                        $tr.='<tr><td>'.$v['aa'].'</td><td>'.$v['time1'].'</td><td>'.$v['time2'].'</td><td>'.$v['gong'].'</td><td>'.$v['info'].'</td><td>'.($ask['pic1']?('<a href=\'./Public/Uploads/ask/'.$ask['pic1'].'\' target=\'blank\'>&nbsp;附件1</a>'):'').($ask['pic2']?('<a href=\'./Public/Uploads/ask/'.$ask['pic2'].'\' target=\'blank\'>&nbsp;附件2</a>'):'').($ask['pic3']?('<a href=\'./Public/Uploads/ask/'.$ask['pic3'].'\' target=\'blank\'>&nbsp;附件3</a>'):'').'</td><td width=\'70px;\'>'.$v['state'].'</td><td width=\'110px;\'>'.(($v['kq']==0)?'<a class=\'btn btn-minier btn-danger\' onclick=\'qingjia_jiaban(this,'.$pid.','.$v['id'].',1)\'>记入</a>-<a class=\'btn btn-minier btn-danger\' onclick=\'qingjia_jiaban(this,'.$pid.','.$v['id'].',2)\'>不记入</a>':(($v['kq']==1)?'计入':'不计')).'</td></tr>';
                    }
                }
            }elseif ($aa=='jiaban') {
                $th='<th>开始时间</th><th>结束时间</th><th>共计/小时</th><th>加班事由</th><th>审核状态</th><th>核算出勤</th>';
                $where['time1']  = array('like', session('date')."%");
                $where['time2']  = array('like', session('date')."%");
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $map['pid']  = $pid;
                $map['class']  = '加班';
                $ask=M('hw003.person_ask',null)->where($map)->select();
                if($ask){
                    foreach ($ask as $v) {
                        $tr.='<tr><td>'.$v['time1'].'</td><td>'.$v['time2'].'</td><td>'.$v['gong'].'</td><td>'.$v['info'].'</td><td width=\'70px;\'>'.$v['state'].'</td><td width=\'110px;\'>'.(($v['kq']==0)?'<a class=\'btn btn-minier btn-danger\' onclick=\'qingjia_jiaban(this,'.$pid.','.$v['id'].',1)\'>记入</a>-<a class=\'btn btn-minier btn-danger\' onclick=\'qingjia_jiaban(this,'.$pid.','.$v['id'].',2)\'>不记入</a>':(($v['kq']==1)?'计入':'不计')).'</td></tr>';
                    }
                }
            }
        }
        $data='<h3>&nbsp;&nbsp;&nbsp;'.$name.'</h3><table class=\'table table-bordered\'><thead><tr>'.$th.'</tr></thead>'.$tr.'</table>';
        print($data);
    }

    //核准存档
    public function ajax_kq_save($pid){
        $mod=M('hw003.person_kq_ruled',null);
        $w=['pid'=>$pid,'date'=>session('date')];
        $m=$mod->where($w)->find();
        $m['aa']=$m['aa']?count(explode('|', $m['aa'])):0;
        $m['bb']=$m['bb']?count(explode('|', $m['bb'])):0;
        $m['cc']=$m['cc']?count(explode('|', $m['cc'])):0;
        $m['dd']=$m['dd']?count(explode('|', $m['dd'])):0;
        $d['t']=($m['aa']||$m['bb']||$m['cc']||$m['dd'])?0:1;
        $d['s']=round($m['r']-$m['aa']-$m['bb']-$m['cc']-3*$m['dd']+$m['qingjia']+$m['jiaban']+$m['ee'],2);
        $mod->where($w)->save($d);
        if($mod->where($w)->setField('save',1))print(1);
    }

    //考勤不计入
    public function ajax_kq_delt($type,$date,$pid){
        $mod=M('hw003.person_kq_ruled',null);
        $m=$mod->where(['pid'=>$pid,'date'=>session('date')])->find();
        $d=array_diff(explode("|",$m[$type]),[$date]);
        $msg['cont']=count($d);
        $msg['state']='ok';
        if($mod->where(['id'=>$m['id']])->setField($type,implode('|',$d)))print_r(json_encode($msg));
    }

    //请假加班审核,state 1给，2不给
    public function ajax_kq_qingjia($id,$pid,$state){
        $m=M('hw003.person_ask',null)->field('id,class,aa,gong')->find($id);
        $da=M('hw003.person_kq_ruled',null)->where(['pid'=>$pid,'date'=>session('date')])->find();
        if($m['class']=='请假'){
            switch ($m['aa']) {
                case '事假':
                    if($m['gong']<=3)$cont=$m['gong'];
                    if($m['gong']>3)$cont=$m['gong']+($m['gong']-3);
                    if($m['gong']>5)$cont=7+($m['gong']-5)*2;
                    if($m['gong']>=8)$cont=15+($m['gong']-8)*3;
                    break;
                case '病假':
                    $age=M('hw003.person_info',null)->where(['id'=>$pid])->getField('ruzhi',false);
                    $age=round((time()-strtotime($age))/(24*3600*365),2);
                    if($age<2)$cont=$m['gong']*0.4;
                    if($age>=2)$cont=$m['gong']*0.3;
                    if($age>=4)$cont=$m['gong']*0.2;
                    if($age>=6)$cont=$m['gong']*0.1;
                    if($age>=8)$cont=0;
                    break;
                case '丧假':
                    if($m['gong']<=3)$cont=0;
                    if($m['gong']>3)die;
                    break;
                case '婚假':
                    if($m['gong']<=3)$cont=0;
                    if($m['gong']>3)die;
                    break;
                case '产假':
                    $cont=0;
                case '公假':
                    die;
                case '灵活假期':
                    $cont=0;
                    break;
            }
            $cont=-$cont;
        }elseif($m['class']=='加班'){
            $cont=round($m['gong']*3600/($da['rt']/$da['r']),2);
        }
        //申请确认
        M('hw003.person_ask',null)->where(['id'=>$id])->setField('kq',$state);
        //存入考勤
        if($state==1 && $m['class']=='请假')$cc=M('hw003.person_kq_ruled',null)->where(['pid'=>$pid,'date'=>session('date')])->setField('qingjia',$da['qingjia']+$cont);
        if($state==1 && $m['class']=='加班')$cc=M('hw003.person_kq_ruled',null)->where(['pid'=>$pid,'date'=>session('date')])->setField('jiaban',$da['jiaban']+$cont);
        if($cc || $cont===0 || $state==2)print('ok');
    }

    //录入考勤的平衡值
    public function ajax_kq_ee($pid,$ee){
        if(IS_AJAX){
            if(M('hw003.person_kq_ruled',null)->where(['pid'=>$pid,'date'=>session('date')])->setField('ee',$ee))print('ok');
        }
    }

    //页面关闭时显示新数据
    public function ajax_kq_clos($pid,$aa){
        $kq=M('hw003.person_kq_ruled',null)->where(array('date'=>session('date'),'pid'=>$pid))->find();
        //考勤数据
        $m=$kq;
        $m['aa']=$kq['aa']?count(explode('|', $kq['aa'])):0;
        $m['bb']=$kq['bb']?count(explode('|', $kq['bb'])):0;
        $m['cc']=$kq['cc']?count(explode('|', $kq['cc'])):0;
        $m['dd']=$kq['dd']?count(explode('|', $kq['dd'])):0;
        $w=array('pid'=>$k,'time1'=>array('like',session('date')."%"),'class'=>'请假','kq'=>0);
        if(M('hw003.person_ask',null)->where(['kq'=>0,'pid'=>$pid,'class'=>'请假','time1'=>['like',session('date')."%"]])->find())$m['qingjia']='待处理';
        $w=array('pid'=>$k,'time1'=>array('like',session('date')."%"),'class'=>'加班','kq'=>0);
        if(M('hw003.person_ask',null)->where(['kq'=>0,'pid'=>$pid,'class'=>'加班','time1'=>['like',session('date')."%"]])->find())$m['jiaban']='待处理';
        $m['s']=$kq['r']-$m['aa']-$m['bb']-$m['cc']-3*$m['dd']+$kq['qingjia']+$kq['jiaban']+$kq['ee'];
        $m['t']=($kq['aa']||$kq['bb']||$kq['cc']||$kq['dd'])?'否':'满勤';
        print(json_encode($m));
    }


/**
简历管理
*/

    public function jl(){
        
        $m=M('hw003.person_jl',null);
        if($_POST['add']){
            $m->create();
            $m->add();
        }
        if($_POST['search']){
            if($_POST['name'])$w['name']=$_POST['name'];
            if($_POST['position'])$w['position']=$_POST['position'];
            if($_POST['city'])$w['city1|city2']=$_POST['city'];
        }
        if($_GET['delt']){
            M('hw003.person_jl',null)->delete((int)$_GET['delt']);
        }
        $this->data=$m->where($w)->order('timestamp desc')->select();
        $this->display();

    }

}
?>