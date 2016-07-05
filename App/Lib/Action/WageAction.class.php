<?php
/**
薪酬管理工资核算
*/
class WageAction extends CommonAction {
    protected $config = array('app_type' => 'personal');
    //月初需要执行的操作
    function wage($pid=null,$school=null){
        if(date('d')=='01'){
            if(!M('hw003.person_xc',null)->where(array('date'=>date('Y-m')))->find()){
                $md=M('hw003.person_xc',null);
                $pid=M('hw003.person_all',null)->where('state=1')->getField('id,school,position',true);
                    $w['date']=date('Y-m');
                foreach ($pid as $v) {
                    $w['pid']=$v['id'];
                    $w['school']=$v['school'];
                    $w['level']=$md->where(array('type'=>0,'pid'=>$v['id'],'date'=>date('Y-m',time()-24*3600)))->getField('level_new');
                    $w['class']=strstr($v['position'],'业务副校长咨询副校长教学副校长维护副校长')?2:1;
                    if(!$md->where($w)->find())$md->add($w);
                }
            }
        }
        if($pid&&$school){//新员工录入添加工资条
            if(M('hw003.person_xc',null)->add(array('school'=>$school,'pid'=>$pid,'date'=>date('Y-m'))))return true;
        }
    }
    //过滤查询字段
    function map_search() {
        if($_POST['name'])$map['name'] = array('like', "%" . $_POST['name'] . "%");
        if($_POST['card'])$map['card'] = array('like', "%" . $_POST['card'] . "%");
        if($_POST['school'])$map['school'] = $_POST['school'];
        if($_POST['part'])$map['part'] = $_POST['part'];
        if($_POST['position'])$map['position'] = $_POST['position'];
        return $map;
    }

    function level($i){
        if($i==1){
            $this->level=M('hw003.person_xc_rule1',null)->getField('level',true);
        }elseif($i==2){
            $this->level=M('hw003.person_xc_rule2',null)->getField('level',true);
        }elseif($i==3){//获取职务
            $this->position=array_unique(M('hw003.person_xc_rule1',null)->getField('position',true));
        }elseif($i==4) {
            $this->level=array_merge(M('hw003.person_xc_rule1',null)->getField('level',true),M('hw003.person_xc_rule2',null)->getField('level',true));
        }
    }

    public function cc(){
        $m=M('hw003.person_xc',null)->select();
        foreach ($m as $v) {
            $name=M('hw003.person_all',null)->find($v['pid']);
            $d['school']=$name['school'];
            M('hw003.person_xc',null)->where(array('id'=>$v['id']))->save($d);
        }
    }

    public function xc_rule($i){
        $md=M('hw003.person_xc_rule1',null);
        if($_POST){
            $md->create();
            $md->add();
        }
        if($_GET['delt']){
            $md->delete((int)$_GET['delt']);
        }
        $this->list=$md->order('position')->select();
        self::level(3);
        $this->display('xc_rule'.$i);
    }


/**
员工薪酬核算部分
*/

    public function xc_rule1(){
        $md=M('hw003.person_xc_rule1',null);
        if($_POST){
            $md->create();
            $md->add();
        }
        if($_GET['delt']){
            $md->delete((int)$_GET['delt']);
        }
        $this->list=$md->order('position')->select();
        self::level(3);
        $this->display();
    }

    public function xc_xc1($school='',$class=1){
        if($_GET['save']){//数据存档
            $class=M('hw003.person_xc',null)->find($this->_get('save'));
            $data=self::info($class,$class['class']);
            $d=array('5x1'=>$data['5x1'],'5x2'=>$data['5x2'],'fuli1'=>$data['fuli1'],'fuli2'=>$data['fuli2'],'count1'=>$data['count1'],'count2'=>$data['count2'],'count3'=>$data['count3']);
            $d['state']=1;
            M('hw003.person_xc',null)->where(array('id'=>$this->_get('save')))->save($d);die;
        }
        if(IS_AJAX){//数据保存
            $d[$this->_post('aa')]=$this->_post('info');
            M('hw003.person_xc',null)->where(array('id'=>$this->_post('id')))->save($d);die;
        }
        $map = self::map_search();
        $map['state'] = 1;
        if($class==1)$map['school']=array('not in','鞍山校区,日月兴城,水木清华,天丽校区');
        if($school)$map['school']=$school;
        $all=M('hw003.person_all',null)->where($map)->getField('id',true);
        $name=M('hw003.person_all',null)->where($map)->getField('id,name,position',true);

        $map2['pid']=array('in',$all);
        $map2['date']=session('date');
        $map2['class']=$class;
        $user=M('hw003.person_xc',null);
        import('ORG.Util.Page');
        $count=$user->where($map2)->count();
        $page= new Page($count,20);
        $show=$page->show();
        $m=$user->where($map2)->order('pid,type')->limit($page->firstRow.','.$page->listRows)->select();
        foreach ($m as $k=>$v) {
            $m[$k]['name']=$name[$v['pid']]['name'];
        }
        $this->list=$m;
        self::level($class);
        $this->page=$show;
        $this->display('xc_xc'.$class);
    }

    //鞍山校区
    public function xc_xc1as(){
        self::xc_xc1('鞍山校区');
    }

    //盘锦校区
    public function xc_xc1pj(){
        $school=array('in','水木清华,日月兴城,天丽校区');
        self::xc_xc1($school);
    }

    //核算详情数据,data仅获取数据
    public function xc_xc11($i,$data=null){
        $i=M('hw003.person_xc',null)->find($i);
        $i['name']=M('hw003.person_all',null)->field('name,school,tag')->find($i['pid']);
        $i=self::info($i,1);
        if($data){
            return $i;
            die;
        }
        $this->dt=$i;
        $this->display('xc_xc11');
    }

    //兼职操作
    public function xc_other(){
        $delt=(int)$_GET['delt'];
        if($delt){
            if(M('hw003.person_xc',null)->where(array('state'=>0,'id'=>$delt))->delete())print('ok');
        }
        if($_GET['id']==-1){//添加员工兼职工资条
            $id=M('hw003.person_xc',null)->add(array('date'=>session('date'),'type'=>1));
            if($id)print(json_encode($id));
        }
        if($_GET['id']==-2){//添加校长兼职工资条
            $id=M('hw003.person_xc',null)->add(array('date'=>session('date'),'type'=>1,'class'=>2));
            if($id)print(json_encode($id));
        }
    }


/**
校长工资核算部分
*/
    public function xc_rule2(){
        $this->list=M('hw003.person_xc_rule2',null)->select();
        $this->display();
    }

    public function xc_xc2(){
        self::xc_xc1('',2);
    }

    public function xc_xc22($i){
        $i=M('hw003.person_xc',null)->where(array('id'=>$i,'date'=>session('date')))->find();
        $i['name']=M('hw003.person_all',null)->where(array('id'=>$i['pid']))->getField('name');
        $i=self::info($i,2);
        $this->dt=$i;
        $this->display();
    }
/**
核算工资数据模块
*/
    function info($i,$class){
        if($class==1){
            $m=M('hw003.person_xc_rule1',null)->where(array('level'=>$i['level']))->find();

            $i['d']=$m['b'];
            $i['e']=$m['c'];
            $i['f']=$m['d'];
            $i['g']=$m['e'];
            $i['h']=$m['f'];
            $i['i']=$m['p'];
            $i['q']=$m['g'];
            $i['v']=($i['u']*50<=$i['i'])?($i['i']-50*$i['u']):0;
            $i['w']=($i['d']+$i['e']*$i['p']+$i['f']+$i['g']+$i['h']*$i['p']+$i['j']+$i['k']+$i['l']+$i['m']+$i['n']+$i['o'])/$i['r']*$i['s']+$i['q']*$i['t']+$i['v'];
            $i['y']=$m['h'];
            $i['aa']=$i['x']*$i['y']+$i['x']*$i['z'];
            //+++++++++++++鞍山课时费特殊处理
            if($i['school']=='鞍山校区'){
                if($i['level']=='讲师助教'||$i['level']=='初级讲师'){
                    $i['aa']=35*$i['1v1']+45*$i['1v3']*55*$i['1v4'];
                }elseif ($i['level']=='中级讲师') {
                    $i['aa']=45*$i['1v1']+55*$i['1v3']*65*$i['1v4'];
                }elseif ($i['level']=='高级讲师') {
                    $i['aa']=55*$i['1v1']+65*$i['1v3']*75*$i['1v4'];
                }
                $i['ab']=$i['chu'];
            }
            //++++++ end

            $i['ad']=$m['i'];
            $i['ae']=$m['j'];
            $i['af']=$m['k'];
            $i['ag']=$m['l'];
            $i['ah']=$m['m'];
            $i['ai']=$m['n'];
            //+++++++++++++++++++++++++++++业绩部分，盘锦区域特殊处理
                if($i['ac']<10000){
                    $i['aj']=$i['ac']*$i['ad'];
                }elseif ($i['ac']<25000) {
                    $i['aj']=$i['ac']*$i['ae'];
                }elseif ($i['ac']<40000) {
                    $i['aj']=$i['ac']*$i['af'];
                }elseif ($i['ac']<60000) {
                    $i['aj']=$i['ac']*$i['ag'];
                }elseif ($i['ac']<80000) {
                    $i['aj']=$i['ac']*$i['ah'];
                }elseif ($i['ac']>=80000) {
                    $i['aj']=$i['ac']*$i['ai'];
                }

                //特殊处理
                if(strstr('日月兴城水木清华天丽校区',$i['school'])){
                    if($i['ac']<20000){
                        $i['aj']=$i['ac']*$i['ad'];
                    }elseif ($i['ac']<50000) {
                        $i['aj']=$i['ac']*$i['ae'];
                    }elseif ($i['ac']<80000) {
                        $i['aj']=$i['ac']*$i['af'];
                    }elseif ($i['ac']<160000) {
                        $i['aj']=$i['ac']*$i['ag'];
                    }elseif ($i['ac']<250000) {
                        $i['aj']=$i['ac']*$i['ah'];
                    }elseif ($i['ac']>=250000) {
                        $i['aj']=$i['ac']*$i['ai'];
                    }
                }
            //+++++++++++++++++++++++++++++ end
            if($i['ak']<80000){
                $i['al']=0.008;
            }elseif ($i['ak']<160000) {
                $i['al']=0.01;
            }elseif ($i['ak']<240000) {
                $i['al']=0.012;
            }elseif ($i['ak']>=240000) {
                $i['al']=0.015;
            }
            $i['am']=$i['ak']*$i['al'];
            $i['ao']=$m['o'];
            $i['ap']=$i['an']*$i['ao'];
            $i['other']=$i['cd'];

            //兼职数据跳出
            $i['count1']=$i['w']+$i['aa']+$i['aj']+$i['am']+$i['ap']+$i['aq']-$i['ax']-$i['aw']+$i['ar']+$i['as']+$i['av']+$i['ab']+$i['at']+$i['au'];//应付工资
            if($i['type'])return $i;

            $i['count']=M('hw003.person_xc',null)->where(array('date'=>session('date'),'pid'=>$i['pid'],'type'=>1))->sum('count1');//兼职工资
            $i['count1']=$i['w']+$i['aa']+$i['aj']+$i['am']+$i['ap']+$i['aq']-$i['ax']-$i['aw']+$i['ar']+$i['as']+$i['av']+$i['ab']+$i['at']+$i['au']+$i['count'];//应付工资
        }


//校长薪酬核算--------------------------------------------------                           -----------------------------

        if($class==2){
            $m=M('hw003.person_xc_rule2',null)->where(array('level'=>$i['level']))->find();
            //其它数据修改共用部分
                $i['f']=$i['p'];
                $i['g']=$i['r'];
                $i['h']=$i['s'];
                $i['j']=$i['xz_j'];
                $i['u']=$i['xz_u'];
                $i['w']=$i['xz_w'];
                $i['ae']=$i['xz_ae'];
                $i['ak']=$i['xz_ak'];
                $i['an']=$i['xz_an'];
                $i['aq']=$i['xz_aq'];
                $i['ar']=$i['xz_ar'];
                $i['as']=$i['xz_as'];
                $i['at']=$i['xz_at'];
                $i['au']=$i['xz_au'];
                $i['av']=$i['xz_av'];
                $i['aw']=$i['xz_aw'];

            $i['d']=$m['b'];
            $i['e']=$m['c'];
            $i['i']=($i['d']+$i['e']*$i['f'])/$i['g']*$i['h'];
            $i['k']=$m['d'];
            $i['l']=$m['e'];
            $i['m']=$m['f'];
            $i['n']=$m['g'];
            $i['o']=$m['h'];
            $i['p']=$m['i'];
            $i['q']=$m['j'];
            $i['r']=$m['k'];
            $i['s']=$m['l'];
            $i['t']=$m['m'];
            if($i['u']<0.7){
                $i['v']=$i['j']*$i['k'];
            }elseif($i['u']<0.8){
                $i['v']=$i['j']*$i['l'];
            }elseif($i['u']<0.9){
                $i['v']=$i['j']*$i['m'];
            }elseif($i['u']<1){
                $i['v']=$i['j']*$i['n'];
            }elseif($i['u']<1.5){
                $i['v']=$i['j']*$i['o'];
            }elseif($i['u']<2){
                $i['v']=$i['j']*$i['p'];
            }elseif($i['u']<2.5){
                $i['v']=$i['j']*$i['q'];
            }elseif($i['u']<3){
                $i['v']=$i['j']*$i['r'];
            }elseif($i['u']<4){
                $i['v']=$i['j']*$i['s'];
            }elseif($i['u']>=4){
                $i['v']=$i['j']*$i['t'];
            }
            $i['x']=$m['p'];
            $i['y']=$m['q'];
            $i['z']=$m['r'];
            $i['aa']=$m['s'];
            $i['ab']=$m['t'];
            $i['ac']=$m['u'];
            if($i['w']<10000){
                $i['ad']=$i['w']*$i['x'];
            }elseif($i['w']<25000){
                $i['ad']=$i['w']*$i['y'];
            }elseif($i['w']<40000){
                $i['ad']=$i['w']*$i['z'];
            }elseif($i['w']<60000){
                $i['ad']=$i['w']*$i['aa'];
            }elseif($i['w']<80000){
                $i['ad']=$i['w']*$i['ab'];
            }elseif($i['w']>=80000){
                $i['ad']=$i['w']*$i['ac'];
            }
            $i['af']=$m['v'];
            $i['ag']=$m['w'];
            $i['ah']=$m['x'];
            $i['ai']=$m['y'];
            if($i['ae']<60000){
                $i['aj']=$i['ae']*$i['af'];
            }elseif($i['ae']<120000){
                $i['aj']=$i['ae']*$i['ag'];
            }elseif($i['ae']<200000){
                $i['aj']=$i['ae']*$i['ah'];
            }elseif($i['ae']>=200000){
                $i['aj']=$i['ae']*$i['ai'];
            }
            $i['al']=$m['n'];
            $i['am']=$i['ak']*$i['al'];
            $i['ao']=$m['o'];
            $i['ap']=$i['an']*$i['ao'];
            $i['count1']=$i['i']+$i['v']+$i['ad']+$i['aj']+$i['am']+$i['ap']+$i['aq']+$i['ar']+$i['as']+$i['at']+$i['au']+$i['av']+$i['aw'];
        }
            $wx1=self::xc_5x1_info($i['pid']);
            $fuli=self::xc_fuli_info($i['pid']);
            $i['5x1']=$wx1['5x1']+$i['bu_geren'];
            $i['5x2']=$wx1['5x2']+$i['bu_danwei'];
            $i['5x3']=$wx1['5x3']+$i['bu_geren']+$i['bu_danwei'];
            $i['buzhu']=$wx1['buzhu'];
            $i['count2']=$i['count1']-$i['5x1'];//险后工资
            if($i['count1']<3000){
                $i['fuli1']=$fuli['dixin']?$i['count1']*$fuli['bili']:0;
                $i['fuli2']=$fuli['dixin']?$i['count1']*$fuli['bili2']:0;
            }else{
                $i['fuli1']=$i['count1']*$fuli['bili'];
                $i['fuli2']=$i['count1']*$fuli['bili2'];
            }
            $i['fuli3']=$i['fuli1']+$i['fuli2'];
            $i['fuli4']=$i['geren']+$i['danwei']+$i['fuli3'];
            $i['count3']=$i['count2']-$i['fuli1']+$i['bn']-$i['bo'];
            $i['danwei']=$i['count1']+$i['cont']+$i['buzhu']+$i['fuli2'];
            return $i;
    }
/**
员工五险一金管理部分
*/

    //五险一金的计算规则
    public function xc_5x1(){
        $md=M('hw003.person_xc_5x1',null);
        if($_POST){
            $md->create();
            $md->con=round((float)$_POST['aa']*((float)$_POST['i']+(float)$_POST['j']+(float)$_POST['k'])+(float)$_POST['bb']*(float)$_POST['m'],2);
            $md->cont=round((float)$_POST['aa']*((float)$_POST['c']+(float)$_POST['d']+(float)$_POST['e']+(float)$_POST['f']+(float)$_POST['g'])+(float)$_POST['bb']*(float)$_POST['l'],2);
            $md->add();
        }
        if($_GET['delt'])$md->delete((int)$_GET['delt']);
        $this->list=$md->select();
        $this->display();

    }

    //五险一金的管理
    public function xc_5x1d(){
        if($_POST['record']){//五险一金缴纳记录
            $m=M('hw003.person_xc',null)->where(array('pid'=>$this->_post('record'),'type'=>0))->order('date desc')->field('date,5x1,5x2')->select();
            foreach ($m as $k => $v) {
                $m[$k]['5x3']=$v['5x1']+$v['5x2'];
            }
            print(json_encode($m));die;
        }

        if($this->isAjax()){
            $md=M('hw003.person_xc_5x1d',null);
            $md->create();
            $md->id=$this->_post('id');
            $md->$_POST['aa']=$_POST['info'];
            $md->save();die;
        }

        $map = self::map_search();
        $map['state'] = 1; 
        $user=M('hw003.person_all',null);
        $m=$user->where($map)->getField('id,name,position',true);
        $m2=M('hw003.person_xc_5x1d',null);
        foreach ($m as $k=>$v) {
            $vv=self::xc_5x1_info($k,1);
            $m[$k]=$vv?array_merge($v,$vv):$v;
        }
        $m=array_sort($m,'age','desc');
        $this->list=$m;
        // $this->page=$show;
        $this->city=M('hw003.person_xc_5x1',null)->getField('city',true);
        $this->display();
    }

    //获取对应员工的五险一金金额
    public function xc_5x1_info($id,$type=0){
        $m=M('hw003.person_xc_5x1d',null)->find($id);
        $ruzhi=M('hw003.person_info',null)->where(array('id'=>$id))->getField('5x1 ');
        $m['age']=$ruzhi;
        if($ruzhi&&$ruzhi<=session('date')){
            $m['state']=1;
            if($m['type']){
                $b=M('hw003.person_xc_5x1',null)->where(array('city'=>$m['city']))->find();
                $d['5x1']=$b['con'];
                $d['5x2']=$b['cont'];
                $d['5x3']=$b['con']+$b['cont'];
            }else{
                $d['buzhu']=$m['buzhu'];
            }
        }else{
            $m['state']=0;
        }
        if($type)return $m;//管理页面调用
        return $d;//合算工资调用
    }

/**
员工福利金管理部分
*/

    public function xc_fuli(){
        if($_POST['record']){//福利金存入记录
            $m=M('hw003.person_xc',null)->where(array('pid'=>$this->_post('record'),'type'=>0))->order('date desc')->field('date,fuli1,fuli2')->select();
            foreach ($m as $k => $v) {
                $m[$k]['fuli3']=$v['fuli1']+$v['fuli2'];
            }
            print(json_encode($m));die;
        }
        if($this->isAjax()){
            $md=M('hw003.person_xc_fulid',null);
            $md->create();
            $md->id=$this->_post('id');
            $md->$_POST['aa']=$_POST['info'];
            $md->save();die;
        }
        if($this->_post('add')){//记录福利金提取
            $fl=M('hw003.person_xc_fulid',null)->find((int)$_POST['id']);
            $d['tiqu']=$fl['tiqu']+(float)$_POST['money'];
            $d['tiqu_date']=$this->_post('date');
            $d['record']='<提取时间：'.$this->_post('date').'提取金额：'.$this->_post('money').'备注：'.$this->_post('other').'>'.$fl['tiqu'];
            M('hw003.person_xc_fulid',null)->where(array('id'=>(int)$_POST['id']))->save($d);
            M('hw003.person_xc_fulid',null)->where(array('id'=>(int)$_POST['id']))->setInc('tiqu_count'); 
        }

        $map = self::map_search();
        $map['state'] = 1;
        $user=M('hw003.person_all',null);
        $m=$user->where($map)->order('id')->getField('id,name,position',true);
        foreach ($m as $k => $v) {
            $vv=self::xc_fuli_info($k);
            $list[$k]=array_merge($v,$vv);
        }
        $list=array_sort($list,'age','desc');
        $this->list=$list;
        $this->display();
    }

    //福利金的计算，返回比例
    public function xc_fuli_info($id){
        $ruzhi=M('hw003.person_info',null)->where(array('id'=>$id))->getField('ruzhi');
        if((int)$ruzhi)$age=(float)substr(((time()-strtotime($ruzhi))/31557600),0,4);//获取工龄
        $fuli=M('hw003.person_xc_fulid',null)->find($id);
        $fuli['age']=$age;
        if($fuli['tiqu_count']){//遇到提取过福利金的计算
            if((time()-strtotime($fuli['tiqu_date']))<31557600){
                $age=(float)substr(((strtotime($fuli['tiqu_date']-strtotime($ruzhi)))/31557600),0,4);
            }else{
                $age-=$fuli['tiqu_count'];
            }
        }
        $n=strpos($age,'.');//寻找位置
        $age=$n?substr($age,0,$n):$age;//删除后面
        $fuli['bili2']=0.25+0.05*$age;//单位缴纳比例
        $fuli['geren']=M('hw003.person_xc',null)->where(array('pid'=>$id))->sum('fuli1');
        $fuli['danwei']=M('hw003.person_xc',null)->where(array('pid'=>$id))->sum('fuli2');
        return $fuli;
    }

/**
数据导出
*/

    //1普通员工，2鞍山员工，3盘锦员工，4校长
    public function import($i){

        $md=M('hw003.person_all',null);
        if($i==1){
            $w['school']=array('not in','鞍山校区,日月兴城,水木清华,天丽校区');
            $w['tag']='员工';
            $pid=$md->where($w)->getField('id',true);
            foreach ($pid as $v) {
                $output.=self::xc_xc11($v);
            }
        }elseif($i==2){
            $w['school']=鞍山校区;
            $w['tag']='员工';
            $pid=$md->where($w)->getField('id',true);
            foreach ($pid as $v) {
                $output.=self::xc_xc11($v);
            }
        }elseif($i==3){
            $w['school']=array('in','日月兴城,水木清华,天丽校区');
            $w['tag']='员工';
            $pid=$md->where($w)->getField('id',true);
            foreach ($pid as $v) {
                $output.=self::xc_xc11($v);
            }
        }elseif($i==4){
            $w['tag']='校长';
            $pid=$md->where($w)->getField('id',true);
            foreach ($pid as $v) {
                $output.=self::xc_xc22($v);
            }
        }

        $output .= "<HTML>";
        $output .= "<HEAD>";
        $output .= "<META http-equiv=Content-Type content=\"text/html; charset=utf-8\">";
        $output .= "</HEAD>";
        $output .= "<BODY>";
        $output .= "</BODY>";
        $output .= "</HTML>";

        $filename='薪酬明细'.date('Y-m-d');
        header("Content-type:application/msexcel");
        header("Content-disposition: attachment; filename=$filename.xls");
        header("Cache-control: private");
        header("Pragma: private");
        print($output);

    }

/**
临时程序保存数据
*/
    public function fuli_save(){//把福利金存储到7月份
        if($this->isAjax()){
            $md=M('hw003.person_xc',null);
            $d['pid']=$_POST['id'];
            $d['date']='2015-07';
            if($_POST['aa'])$d[$_POST['aa']]=$_POST['info'];
            $w['pid']=$_POST['id'];
            $w['date']='2015-03';
            if($md->where($w)->find()){
                $md->where($w)->save($d);
                // var_dump($d);
            }else{
                $md->add($d);
            }
        }
    }

}
?>