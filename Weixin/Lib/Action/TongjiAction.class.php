<?php
// 本类由系统自动生成，仅供测试用途
class TongjiAction extends CommAction {

/*
    //校区数据
    public function index(){
        weixin();
        $time=time();
        if(IS_POST){
            $time=strtotime($this->_post('date'));
        }
        $w['timee']=array('like',date('Y-m',$time)."%");
        $w['state']=array('in','0,1');
        
        $m=M('hw001.class',null)->where($w)->order('school,timee,teacher,time1,grade')->getField('id,school,timee,class,grade,time1,teacher,count,cwqr',true);
        foreach ($m as $v) {

            // 阜新二部、阜新实验校区合并统计为阜新实验校区
            if($v['school']=='阜新二部' || $v['school']=='阜新实验校区')$v['school']='阜新实验校区';


            unset($v['id']);
            if($v!=$cc&&$v['school']!='集团'&&$v['school']!=''){
                
                $month[$v['school']][$v['class']]+=$v['count'];
                $month[$v['school']]['合计']+=$v['count'];
                if($v['timee']==date('Y-m-d',$time)){
                    $day[$v['school']][$v['class']]+=$v['count'];
                    $day[$v['school']]['合计']+=$v['count'];
                }
            }
            $cc=$v;
        }
        $day=array_sort($day,'合计',desc);
        $month=array_sort($month,'合计',desc);
        //获取昨天的数据
        $m2=M('hw001.tongji',null)->where(array('date'=>date('Y-m-d',$time-24*3600)))->getField('school,a,b,c,d,e,f,g,h,i',true);
        foreach ($m2 as $k => $v) {
            if($v['school']=='阜新二部' || $v['school']=='阜新实验校区'){
                $m2['阜新实验校区']['a']+=$v['a'];
                $m2['阜新实验校区']['b']+=$v['b'];
                $m2['阜新实验校区']['c']+=$v['c'];
                $m2['阜新实验校区']['d']+=$v['d'];
                $m2['阜新实验校区']['e']+=$v['e'];
                $m2['阜新实验校区']['f']+=$v['f'];
                $m2['阜新实验校区']['g']+=$v['g'];
                $m2['阜新实验校区']['h']+=$v['h'];
                $m2['阜新实验校区']['i']+=$v['i'];
                $m2['阜新实验校区']['合计']=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
            }else{
                $m2[$k]['合计']=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
            }
            $m2['a']+=$v['a'];
            $m2['b']+=$v['b'];
            $m2['c']+=$v['c'];
            $m2['d']+=$v['d'];
            $m2['e']+=$v['e'];
            $m2['f']+=$v['f'];
            $m2['g']+=$v['g'];
            $m2['h']+=$v['h'];
            $m2['i']+=$v['i'];
            $m2['合计']+=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
        }
        $this->m2=$m2;
        
        //获取去年数据
        $m3=M('hw001.tongji',null)->where(array('date'=>(date('Y',$time)-1).date('-m-d',$time)))->getField('school,a,b,c,d,e,f,g,h,i',true);
        foreach ($m3 as $k => $v) {
            if($v['school']=='阜新二部' || $v['school']=='阜新实验校区'){
                $m3['阜新实验校区']['a']+=$v['a'];
                $m3['阜新实验校区']['b']+=$v['b'];
                $m3['阜新实验校区']['c']+=$v['c'];
                $m3['阜新实验校区']['d']+=$v['d'];
                $m3['阜新实验校区']['e']+=$v['e'];
                $m3['阜新实验校区']['f']+=$v['f'];
                $m3['阜新实验校区']['g']+=$v['g'];
                $m3['阜新实验校区']['h']+=$v['h'];
                $m3['阜新实验校区']['i']+=$v['i'];
                $m3['阜新实验校区']['合计']=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
            }else{
                $m3[$k]['合计']=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
            }
            $m3['a']+=$v['a'];
            $m3['b']+=$v['b'];
            $m3['c']+=$v['c'];
            $m3['d']+=$v['d'];
            $m3['e']+=$v['e'];
            $m3['f']+=$v['f'];
            $m3['g']+=$v['g'];
            $m3['h']+=$v['h'];
            $m3['i']+=$v['i'];
            $m3['合计']+=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
        }
        $this->m3=$m3;

        $this->day=$day;
        $this->month=$month;
        $this->display('school');
    }
*/


    //校区数据
    public function index(){
        weixin();
        $time=time();
        if(IS_POST){
            $time=strtotime($this->_post('date'));
        }
        $w['timee']=array('like',date('Y-m',$time)."%");
        $w['state']=array('in','0,1');
        
       
        $m=M('hw001.class',null)->where($w)->order('school,timee,teacher,time1,grade')->group('school,timee,class,grade,time1,teacher,count')->getField('id,school,timee,class,grade,time1,teacher,count,cwqr',true);
         foreach ($m as $v) {

            // 阜新二部、阜新实验校区合并统计为阜新实验校区
            if($v['school']=='阜新二部' || $v['school']=='阜新实验校区')$v['school']='阜新实验校区';

            if($v['school']!='集团'&&$v['school']!=''){
                $month[$v['school']][$v['class']]+=$v['count'];
                $month[$v['school']]['合计']+=$v['count'];
                if($v['timee']==date('Y-m-d',$time)){
                    $day[$v['school']][$v['class']]+=$v['count'];
                    $day[$v['school']]['合计']+=$v['count'];
                }
            }
        }
         
        $day=array_sort($day,'合计',desc);
        $month=array_sort($month,'合计',desc);
        //获取昨天的数据
        $m2=M('hw001.tongji',null)->where(array('date'=>date('Y-m-d',$time-24*3600)))->getField('school,a,b,c,d,e,f,g,h,i',true);
        foreach ($m2 as $k => $v) {
            if($v['school']=='阜新二部'){
                $m2['阜新实验校区']['a']+=$v['a'];
                $m2['阜新实验校区']['b']+=$v['b'];
                $m2['阜新实验校区']['c']+=$v['c'];
                $m2['阜新实验校区']['d']+=$v['d'];
                $m2['阜新实验校区']['e']+=$v['e'];
                $m2['阜新实验校区']['f']+=$v['f'];
                $m2['阜新实验校区']['g']+=$v['g'];
                $m2['阜新实验校区']['h']+=$v['h'];
                $m2['阜新实验校区']['i']+=$v['i'];
                $m2['阜新实验校区']['合计']=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
            }else{
                $m2[$k]['合计']=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
            }
            $m2['a']+=$v['a'];
            $m2['b']+=$v['b'];
            $m2['c']+=$v['c'];
            $m2['d']+=$v['d'];
            $m2['e']+=$v['e'];
            $m2['f']+=$v['f'];
            $m2['g']+=$v['g'];
            $m2['h']+=$v['h'];
            $m2['i']+=$v['i'];
            $m2['合计']+=$v['a']+$v['b']+$v['c']+$v['d']+$v['e']+$v['f']+$v['g']+$v['h']+$v['i'];
        }
        $this->m2=$m2;

        //获取去年数据
        $wc['timee']=array('like',(date('Y',$time)-1).date('-m',$time).'%');
        $wc['state']=1;
        $wc['cwqr'] = array(array('exp','is not null'),array('NEQ',''));
        
        $m=M('hw001.class',null)->where($wc)->order('school,timee,teacher,time1,grade')->group('school,timee,class,grade,time1,teacher,count')->getField('id,school,timee,class,grade,time1,teacher,count,cwqr',true);
         foreach ($m as $v) {

            // 阜新二部、阜新实验校区合并统计为阜新实验校区
            if($v['school']=='阜新二部' || $v['school']=='阜新实验校区')$v['school']='阜新实验校区';

            if($v['school']!='集团'&&$v['school']!=''){
                $m3[$v['school']]['合计']+=$v['count'];
               
            }
        }
         $this->m3=$m3; 
        
        $this->day=$day;
        $this->month=$month;
        $this->display('school');
    }


    //讲师数据
    public function teacher(){
        weixin();
        $time=time();
        if(IS_POST){
            if($_POST['date'])$time=strtotime($this->_post('date'));
            if($_POST['school'])$w['school']=$_POST['school'];
            if($_POST['teacher'])$w['teacher']=$_POST['teacher'];
        }
        $monday=$time-((date('w',$time)==0?7:date('w',$time))-1)*24*3600;
        $sunday=$time-((date('w',$time)==0?7:date('w',$time))-1)*24*3600+6*24*3600;
        $w['timee']=array('like',date('Y-m',$time)."%");
        $w['state']=array('in','0,1');
        $m=M('hw001.class',null)->where($w)->order('timee,time1,class,teacher,grade')->getField('id,timee,class,grade,time1,teacher,count',true);
        foreach ($m as $v) {
            unset($v['id']);
            if($v!=$cc){
                $month[$v['class']][$v['teacher']]+=$v['count'];
                if($v['timee']>=date('Y-m-d',$monday) && $v['timee']<=date('Y-m-d',$sunday)){
                    $week[$v['class']][$v['teacher']]+=$v['count'];
                }
            }
            $cc=$v;
        }

        foreach ($week as $k => $v) {
            arsort($week[$k]);
        }
        foreach ($month as $k => $v) {
            arsort($month[$k]);
        }

        $this->week=$week;
        $this->month=$month;
        $this->display();
    }
}
