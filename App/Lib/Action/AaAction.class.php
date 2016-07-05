<?php
/**
*意外数据处理执行处理类
*/
class AaAction extends CommonAction {
    protected $config = array('app_type' => 'personal');

/**
处理班级课出现只有一个人有课，其它人的课没有的情况
*/
    public function aa1(){

        $date='2015-07-14';//处理的日期起点

        $m=M('hw001.stu_grade',null)->order('gid')->select();
        foreach ($m as $k => $v) {
            $m3[$v['gid']][]=$v['stuid'];
        }
        $class=M('hw001.class',null);

        $m2=$class->where(array('grade'=>array('neq',0),'timee'=>array('gt',$date)))->select();
        foreach ($m2 as $v) {
            $id=$v['id'];
            unset($v['id'],$v['stuid'],$v['timestamp']);
            $con=count($class->where($v)->select());
            if($con==1){
                //var_dump($v);die;
                $class->delete($id);
                foreach ($m3[$v['grade']] as $val) {
                    $d=$v;
                    $d['stuid']=$val;
                    if($d['stuid']&&$d['grade'])$class->add($d);
                    unset($d);echo "1";//处理的效果次数
                }
            }
        }
    }
/**
员工档案处理去掉pid
*/
    public function aa2(){
        $m=M('hw003.person_info2',null)->select();
        foreach ($m as $v) {
            $v['id']=$v['pid'];
            M('hw003.person_info',null)->add($v);
        }        
    }

    // public function aa3(){//学员换老师
    //     $w['stuid']=array('in','3578,3579');
    //     $w['timee']=array('gt','2015-05-03');
    //     $w['class']='数学';
    //     $w['teacher']='赵玲';
    //     $d['teacher']='于斌';
    //     $con=M('hw001.class',null)->where($w)->save($d);
    //     echo($con);        
    // }

    public function aa4(){//班级课不完整处理
        $class=M('hw001.class',null);
        $grade=M('hw001.stu_grade',null);
        $w['timee']=array('gt',date('Y-m-d'));//需要设置开始时间
        $w['grade']=array('neq',0);
        $m=$class->where($w)->select();
        foreach ($m as $v) {
            unset($v['id'],$v['stuid'],$v['timestamp']);
            $con1=count($class->where($v)->select());
            $sids=$grade->where(array('gid'=>$v['grade']))->getField('stuid',true);
            if(count($sids)!=$con1){
                // var_dump($v);die;
                $class->where($v)->delete();
                foreach ($sids as $val) {
                    $v['stuid']=$val;
                    $class->add($v);
                }
                echo "1";
            }
        }
    }
/**
员工薪酬主表添加
*/
    public function aa5(){
        $md=M('hw003.person_xc1',null);
        $pid=M('hw003.person_all',null)->getField('id',true);
        foreach ($pid as $v) {
            $w['pid']=$v;
            $w['date']='2015-03';
            if(!$md->where($w)->find())$md->add($w);
            $w['date']='2015-04';
            if(!$md->where($w)->find())$md->add($w);
            $w['date']='2015-05';
            if(!$md->where($w)->find())$md->add($w);
        }
    }

/**
高三学生批量添加退费原因
*/
    public function aa6(){
        $w['grade']='高三';
        $w['school']='天丽校区';
        $w['state']=3;
        $w['why3']='';
        $w['other']=array('notlike',"%家长未签字%");
        $w['date']='2015-06';
        $d['why3']='高三结课';
        $con=M('hw003.money_return',null)->where($w)->save($d);
        echo($con);
    }

/**
系统排课数据统计
*/
    public function aa7(){
        $w['timee']=array('between','2014-07-01,2015-07-01');
        $w['grade']=0;
        // $w['state']=1;
        $km=array('数学','物理','化学','生物','政治','历史','地理','英语','语文');
        foreach ($km as $v) {
            $w['class']=$v;
            $data1[$v]=M('hw001.class',null)->where($w)->sum('count');
        }
        
        $w2['timee']=array('between','2014-07-01,2015-07-01');
        $w2['grade']=array('gt',0);
        // $w2['state']=1;
        $m=M('hw001.class',null)->where($w2)->order('school,timee,grade,time1')->field('school,timee,grade,time1,teacher,count,class')->select();
        foreach ($m as $v) {
            if($v!=$aa)$data2[$v['class']]+=$v['count'];
            $aa=$v;
        }

        foreach ($km as $v) {
            $data[$v]=$data1[$v]+$data2[$v];
        }

        print_r($data);
    }

/**
给ask申请表中的pid绑定
*/
    public function aa8(){
        $m=M('hw003.person_ask',null)->where(array('pid'=>0))->select();
        foreach ($m as $v) {
            $pid=M('hw003.person_all',null)->where(array('name'=>$v['name']))->getField('id');
            if(!is_array($pid)&&$pid){
                M('hw003.person_ask',null)->where(array('id'=>$v['id']))->setField('pid',$pid);
            }else{
                var_dump($pid);
                var_dump($v['name']);
            }
        }
    }
/**
帮老师去掉部分授课反馈
*/
    public function aa9(){
        $w['grade']=1104;
        // $w['timee']=array('lt','2015-07-25');
        $con=M('hw001.class',null)->where($w)->setField('fankui',1);
        var_dump($con);
    }

/**
同步考勤系统的员工考勤id
*/
    public function kq_user(){
        $ch = curl_init();
        $time = time();
        $data = array(
        'account'=>'21c4a357f585a1a50ea794fcf96fad73',//API帐号
        'requesttime'=>$time,//请求时间，与服务器时间差不能超过60秒
        );
        //接口参数
        ksort($data);
        $sign = md5(join('',$data).'hongwenhr001');
        $data['sign'] = $sign;
        curl_setopt($ch, CURLOPT_URL, "http://kq.qycn.com/index.php/Api/Api/getEmployee?".http_build_query($data));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $retjson = curl_exec($ch); //返回的数据，json格式
        curl_close($ch);
        $m=json_decode($retjson)->data->totalpage;
        $m2=array();
        for ($i=1; $i <= $m ; $i++) {

            $ch = curl_init();
            $time = time();
            $data = array(
            'account'=>'21c4a357f585a1a50ea794fcf96fad73',//API帐号
            'requesttime'=>$time,//请求时间，与服务器时间差不能超过60秒
            );
            $data['page']=$i;
            //接口参数
            ksort($data);
            $sign = md5(join('',$data).'hongwenhr001');
            $data['sign'] = $sign;
            curl_setopt($ch, CURLOPT_URL, "http://kq.qycn.com/index.php/Api/Api/getEmployee?".http_build_query($data));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $retjson = curl_exec($ch); //返回的数据，json格式
            curl_close($ch);
            $m3=json_decode($retjson)->data->userData;
            if($m3)$m2=array_merge($m2,$m3);
        }

        foreach ($m2 as $v) {
            $w['name']=$v->realname;
            $d['cc']=$v->account;
            M('hw003.person_all',null)->where($w)->save($d);
        }
    }

/**

*/

    //打卡明细核准开始--截止
    public function cc(){
        $date1='2015-06-01';
        $date2='2015-07-31';
        $w['date']=array('between',"$date1,$date2");
        $m=M('hw003.person_kq',null)->where($w)->order('date,cc,time')->select();

        $all=M('hw003.person_all',null)->where(array('state'=>1))->getField('cc,id,school,position,name',true);
        foreach ($m as $v){
            if($v['cc']==$cc&&$v['date']==$day&&$v['time']>$time){
                $dat[$v['cc']][$v['date']]['t2']=$v['time'];
            }else{
                $dat[$v['cc']][$v['date']]['date']=$v['date'];
                $dat[$v['cc']][$v['date']]['t1']=$v['time'];
                $dat[$v['cc']][$v['date']]['t2']=$v['time'];
            }
            $cc=$v['cc'];
            $day=$v['date'];
            $time=$v['time'];
        }
        // echo(count($dat));die;

        foreach ($dat as $k => $v) {
            foreach ($v as $val) {
                $ww=array('pid'=>$all[$k]['id'],'cc'=>$k,'date'=>$val['date'],'t1'=>$val['t1'],'t2'=>$val['t2']);
                M('hw003.person_kq2',null)->add($ww);
            }
        }
    }

    //获取某月的考勤规则
    public function kq_kq_rule($pid,$month){
        $ruled=M('hw003.person_kq_ruled',null)->where(array('pid'=>$pid,'date'=>$month))->getField('rules');//获取考勤组id
        $ruleid=M('hw003.person_kq_rules',null)->find($ruled);
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

    //考勤记录计算
    function kq($pid,$month){
        $t=date('t',strtotime($month.'-01'));
        $rule=$this->kq_kq_rule($pid,$month);
        $kq=M('hw003.person_kq2',null)->where(array('pid'=>$pid,'date'=>array('like',$month."%")))->getField('date,t1,t2');
        $month=strtotime($month.'-01');
        for ($i=0; $i < $t; $i++) {
            $day=date('Y-m-d',$month+$i*24*3600);
            if($rule[$day]['t1']){//有上班时间规则开始计算
                $data['r']++;//应出勤天数
                $data['rt']++;//应出勤秒
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
                    $w['time1']=array('lt',$date.' 23:59:59');
                    $w['time2']=array('gt',$date.' 23:59:59');
                    return $mod->where($w)->find();
                case '2':
                    $w['class']='加班';
                    $w['time1']=array('lt',$date.' 23:59:59');
                    $w['time2']=array('gt',$date.' 23:59:59');
                    return $mod->where($w)->find();
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
            $w['time1']=array('lt',$date.' 23:59:59');
            $w['time2']=array('gt',$date.' 23:59:59');
            $data[]=$mod->where($w)->select();
            $w['class']='加班';
            $data[]=$mod->where($w)->select();
            unset($w['time1'],$w['time2']);
            $w['class']='灵活作息';
            $w['date']=$date;
            $data[]=$mod->where($w)->select();
            $w['class']='意外事项';
            $data[]=$mod->where($w)->select();
            return $data;
        }
    }

    public function kq_kq(){
        $w['state']=1;//之后需要考虑当月离职人员
        $w['school']='日月兴城';
        $m=M('hw003.person_all',null)->where($w)->getfield('id,name,school,part,position',true);
        foreach ($m as $k => $v) {
            $kq=M('hw003.person_kq_ruled',null)->where(array('date'=>session('date'),'pid'=>$k))->find();
            if($kq){
                $m['kq']=$kq;
            }else{
                $d=$this->kq($k,session('date'));
                $d['pid']=$k;
                $d['date']=session('date');
                M('hw003.person_kq_ruled',null)->add($d);
                $m['kq']=$d;
            }
        }
    }

}
?>