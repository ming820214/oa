<?php
// 本类由系统自动生成，仅供测试用途
class AskAction extends CommAction {

    //请假申请,两级审核
    public function qj(){
        if(substr($_POST['time1'],0,7)!=substr($_POST['time2'],0,7))$this->error('跨月请假请分成两部分申请……');
        if($_POST){
            $this->cf();//放重复提交
            // if($_POST['time1'])
            //查询该员工信息
            $w['name']=session('name');
            $w['state']=1;
            $m1=M('person_all')->where($w)->find();
                if($m1){
                    //判断灵活假期
                    if($_POST['aa']=='灵活假期')
                    {
                        self::linhuo();
                    }
                    //保存提交的相关数据
                    $m2=M('person_ask');
                    $m2->create();
                    $m2->class='请假';
                    $m2->school=$m1['school'];
                    $m2->name=session('name');
                    $m2->pid=session('pid');
                    $m2->state='校区审核';
                    $m2->gong2=round((strtotime($_POST['time2'])-strtotime($_POST['time1']))/86400,2);
                    $m2->date=substr($_POST['time1'],0,10);
                    $info=$this->upload();//文件上传保存
                    if($info[0])$m2->pic1=$info[0]['savename'];
                    if($info[1])$m2->pic2=$info[1]['savename'];
                    if($info[2])$m2->pic3=$info[2]['savename'];

                        if($m1['school']=='集团'){//集团成员请假
                            $m2->part=$m1['part'];
                            $m2->state='总裁审核';
                        }
                        if($m1['position']=='校长'){//校长请假
                            $m2->state='运营审核';
                            $id=$m2->add();
                            if($id&&$this->text(6,'王胜鑫','有待处理的请假申请，请及时审核……'))$this->success('申请提交成功','info2/id/'.$id);
                        }else{
                            $id=$m2->add();
                            if($id&&$this->tz('请假'))$this->success('申请提交成功','info2/id/'.$id);
                        }
                }else{
                    $this->success('数据提交失败,请退出重试！');
                }
        }else{
            $this->display();
        }
    }

    public function qj_file(){
       if($_POST['file']){
            $info=$this->upload();//文件上传保存
            if($info[0]){
                $w['id']=$_POST['id'];
                $m1=M('person_ask')->where($w)->find();
                $m2=M('person_ask');
                if($info[0])$d['pic1']=$info[0]['savename'];
                if($info[1])$d['pic2']=$info[1]['savename'];
                if($info[2])$d['pic3']=$info[2]['savename'];

                    if($m1['position']=='校长'){//校长请假
                        $d['state']='运营审核';
                        if($id=$m2->where($w)->save($d)&&$this->text(6,'王胜鑫','有待处理的请假申请，请及时审核……'))$this->success('申请提交成功','info2/id/'.$_POST['id']);
                    }else{
                        if($m1['school']=='集团'){//集团成员请假
                            $d['state']='总裁审核';
                        }else{
                            $d['state']='校区审核';
                        }
                        if($m2->where($w)->save($d)&&$this->tz('请假'))$this->success('材料提交成功','info2/id/'.$_POST['id']);
                    }
            }else{
                $this->success('操作失败，请退出重试……');
            }
       }
    }

    //加班申请
    public function jb(){
        if($_POST){
            $this->cf();//放重复提交
            if((time()-strtotime($_POST['time1']))<24*3600){
                //查询该员工信息
                $w['name']=session('name');
                $w['state']=1;
                $m1=M('person_all')->where($w)->find();
                if($m1){
                    //保存提交的相关数据
                    $m2=M('person_ask');
                    $m2->create();
                    $m2->class='加班';
                    $m2->pid=session('pid');
                    $m2->school=$m1['school'];
                    $m2->name=session('name');
                    $m2->state='校区审核';
                    if($m1['school']=='集团'){
                        $m2->part=$m1['part'];
                        $m2->state='总裁审核';
                    }
                    $m2->gong=round((strtotime($_POST['time2'])-strtotime($_POST['time1']))/3600,1);
                    if($m2->add()&&$this->tz('加班'))$this->success('申请提交成功');
                }else{
                    $this->success('数据提交失败！');
                }
            }else{
                $this->error('申请超时……');
            }
        }else{
            $this->display();
        }
    }

    //意外事项申请
    public function ywsx(){
        if($_POST){
            $this->cf();//放重复提交
            if((time()-strtotime($_POST['date']))<48*3600){
                //查询该员工信息
                $w['name']=session('name');
                $w['state']=1;
                $m1=M('person_all')->where($w)->find();
                if($m1){
                    /*
                    //保存提交的相关数据
                    foreach ($_POST['dk'] as $v) {
                        $dk.=$v.'，';
                    }
                    */

                    if($_POST['dk']){
                        //保存提交的相关数据
                        foreach ($_POST['dk'] as $v) {
                            $dk.=$v.'，';
                        }
                    }else{
                        $this->error('请选择未打卡时段!');
                    }
                    
                    $m2=M('person_ask');
                    $m2->create();
                    $m2->class='意外事项';
                    $m2->pid=session('pid');
                    $m2->info=$dk.$_POST['info'];
                    $m2->school=$m1['school'];
                    $m2->name=session('name');
                    $m2->state='校区审核';
                    if($m1['school']=='集团'){
                        $m2->part=$m1['part'];
                        $m2->state='总裁审核';
                    }                if($m2->add()&&$this->tz('意外事项'))$this->success('申请提交成功');
                }else{
                    $this->success('数据提交失败！');
                }
            }else{
                $this->error('申请超时……');
            }
        }else{
           $this->display();
        }
    }

    //灵活作息申请
    public function lhzx(){
        if($_POST){
            $this->cf();//放重复提交
            //查询该员工信息
            $w['name']=session('name');
            $w['state']=1;
            $m1=M('person_all')->where($w)->find();
            if($m1){
                //保存提交的相关数据
                $m2=M('person_ask');
                $m2->create();
                $m2->class='灵活作息';
                $m2->pid=session('pid');
                $m2->school=$m1['school'];
                //针对温家宝做的考勤系统进行的调整，添加上班开始和结束时间为必填项；
                if(empty($m2->time1) || empty($m2->time2)){
                    $this->success('数据提交失败！原因：上班开始时间和结束时间未填写，请填写完整后再提交。');
                    die;    
                }
                $m2->name=session('name');
                $m2->state='校区审核';
                if($m1['school']=='集团'){
                    $m2->part=$m1['part'];
                    $m2->state='总裁审核';
                }                if($m2->add()&&$this->tz('灵活作息'))$this->success('申请提交成功');
            }else{
                $this->success('数据提交失败！');
            }
        }else{
            $this->display();
        }
    }

    //文件上传
    Public function upload(){
        if($_POST){
            import('ORG.Net.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            // $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath =  './Public/Uploads/ask/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                // $this->error($upload->getErrorMsg());
                return false;
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
                return $info;
            }
        }
    }

/**
==============审核功能模块==============
*/

    //微信通知校长或主管功能模块
    public function tz($info=''){
        $w['name']=session('name');
        $m=M('person_all')->where($w)->find();
        $w2['school']=$m['school'];
        if($m['school']=='集团'){
           // $w2['part']=($m['part']=='人事中心')?'教学中心':$m['part']; //张毅要求把所有人事中心的请假，加班信息发给他，教学中心的发给侯海洋；edit by zhangxm at 2016-04-02 at 15:20
            $w2['part']= $m['part'];
            $w2['position']='总裁';
            //if(session('name')=='张晓明')$w2=['name'=>'郝振华'];
        }else{
            $w2['position']='校长';
        }
        if(in_array($m['school'],['盘锦一完中校区','盘锦实验中学校区']))$w2=['name'=>'刘浩01'];
        $name=M('person_all')->where($w2)->getField('name');
        $this->text(6,$name,'小文提示：有'.$info.'待审核，请及时查看……');
        return true;
    }

    //微信通知申请人
    public function tz2($id){
        $w['id']=$id;
        $name=M('person_ask')->where($w)->getField('name');
        $this->text(1,$name,'小文提示，亲，您最近申请的项目审核状态有变动，记得关注哦……');
        return true;
    }

    //校长、主管审核部分，审核页面
    public function list1(){
        $w['name']=session('name');
        if(session('name')=='王胜鑫'){
            $w2['_string']="(state='总裁审核' AND part='运营中心') OR (state='运营审核')";
        }elseif(session('name')=='张毅'){
            // $w2['_string']="(state='总裁审核' AND part='教学中心') OR (state='人事确认') OR (state='总裁审核' AND part='人事中心')";
            $w2['_string']="(state='人事确认') OR (state='总裁审核' AND part='人事中心')";
        }elseif(session('name')=='刘浩01'){
            $w2['school']=['in','盘锦一完中校区,盘锦实验中学校区'];
            $w2['state']='校区审核';
        }elseif(session('name') == '张晓明'){
            $w2['school']=['in','集团'];
            $w2['state']='总裁审核';
        }else{
            $m=M('person_all')->where($w)->find();
            $w2['school']=$m['school'];
            $w2['state']='校区审核';
            if($m['school']=='集团'){
                $w2['part']=$m['part'];
                $w2['state']='总裁审核';
            }
        }
      //  if(session('name')=='宫婷'){
        //宫婷手里的灵活作息、请假、加班、意外事项的人事审批权交于彭鑫手中
          if(session('name')=='彭鑫'){ 
            unset($w2);
            $w2['state']='集团确认';
        }
        $m=M('person_ask')->where($w2)->select();

        if($_GET['all']){//一键确认全部
            foreach ($m as $v) {
                $_POST['yes']='同意';
                $_POST['id']=$v['id'];
                $this->check(1);
            }
        }else{
            foreach ($m as $val) {
                $mm[$val['class']][]=$val;
            }
            $this->vo=$mm;
        }
        $this->display('list1');
    }
    //人事秘书 功能，该功能查询全集团所有人当月的请假记录
    public function list2($i){
        $w['state']='审核通过';
        $w['timestamp']=array('like',date('Y-m').'%');
        $m=M('person_ask')->where($w)->order('timestamp desc')->select();
        $w2['state']='审核通过';
        $w2['time1']=array('like',date('Y-m').'%');
        $w2['timestamp']=array('notlike',date('Y-m').'%');
        $m2=M('person_ask')->where($w2)->order('school asc,timestamp desc')->select();
        $mm=$m2?array_merge($m,$m2):$m;
        foreach ($mm as $val) {
            $vo[$val['class']][$val['class']][]=$val;
        }
        if($i==1)$this->vo=$vo['请假'];
        if($i==2)$this->vo=$vo['加班'];
        if($i==3)$this->vo=$vo['意外事项'];
        if($i==4)$this->vo=$vo['灵活作息'];
        $this->display('list2');
    }

    public function list3(){
        $w['name']=session('name');
        $this->vo=M('person_ask')->where($w)->order('timestamp desc')->limit(15)->select();
        $this->display();
    }

    public function info($id,$name=''){
        $id=(int)$id;
        $m=M('person_ask')->find($id);
        $this->vo=$m;
        $this->name=$name;
        $this->display();
    }

    public function info2($id){
        $m=M('person_ask')->find($id);
        $this->vo=$m;
        $this->display();
    }

    public function check($type=null){//默认直接跳转页面
        $this->record($_POST['id'],'['.session('name').']'.$_POST['yes'].$_POST['no'].$_POST['th']);//记录审核操作

        $d['why']=$_POST['why'];//添加审核批语
        $this->assign('id',$_POST['id']);//传id值到审核后的页面
        if($_POST['yes']){
            if($_POST['id'])$w['id']=(int)$_POST['id'];
            $m=M('person_ask')->find($w['id']);
            if($m['state']=='审核通过')die('申请已经审核完成，请勿重复操作');

            $d['state']='集团确认';

            if($m['state']=='校区审核' && $m['class']!='请假'){
                $d['state']='集团确认';
               // $this->text(6,'宫婷','小文提示：有校区申请待确认，请及时查看……');
                 $this->text(6,'彭鑫','小文提示：有校区申请待确认，请及时查看……');
            }

            //if($m['state']=='集团确认' && session('name')=='宫婷')$d['state']='审核通过';
            if($m['state']=='集团确认' && session('name')=='彭鑫')$d['state']='审核通过';
            //请假规则特别处理
            if($m['class']=='请假'){
                if($m['state'] == '人事确认' && session('name')=='张毅'){
                    $d['state']='审核通过';
                }elseif($m['state'] == '校区审核' || $m['state'] == '总裁审核' || $m['state'] == '运营审核') {
                    if(!($m['pic1'] || $m['pic2'] || $m['pic3'])&&$m['aa']=='病假'){
                        $d['state']='材料补充';
                    }else{
                        if($m['gong']>=3){
                            $d['state']='人事确认';
                            $this->text(6,'张毅','小文提示：有请假申请待审核，请及时查看……');
                        }
                    }
                }else{
                    //if(session('name')=='宫婷'){
                    if(session('name')=='彭鑫'){
                        $d['state']='审核通过';
                    }else{
                        $d['state']='集团确认';
                        //$this->text(6,'宫婷','小文提示：有校区申请待确认，请及时查看……');
                        $this->text(6,'彭鑫','小文提示：有校区申请待确认，请及时查看……');
                    }
                    // die('申请已经审核完成，请勿重复操作');
                }
            }

            if(M('person_ask')->where($w)->save($d)&&$this->tz2($w['id'])){
                if($type)return true;//不跳转，只返回
                $this->success('同意，审核完成……','info2/id/'.$_POST['id']);
            }
        }

        if($_POST['no']){
            if($_POST['id'])$w['id']=(int)$_POST['id'];
            $d['state']='申请失败';
            if(M('person_ask')->where($w)->save($d) && $this->tz2($w['id']))$this->success('拒绝，审核完成……','info2/id/'.$_POST['id']);
        }

        if($_POST['th']){
            if($_POST['id'])$w['id']=(int)$_POST['id'];
            $d['state']='退回修改';
            if(M('person_ask')->where($w)->save($d) && $this->tz2($w['id']))$this->success('退回修改，审核完成……','info2/id/'.$_POST['id']);
        }

    }
/**

*/

    function record($id,$info){
        $a['id']=$id;
        $date=date('Y-m-d H:i:s');
        $d['record']=M('person_ask')->where($a)->getField('record');
        $d['record'].='|'.$date.$info;
        if(M('person_ask')->where($a)->save($d))return true;
    }

    public function linhuo(){
        $w['name']=session('name');
        $w['aa']='灵活假期';
        $w['state']=array('in','审核通过,校区审核,总裁审核,人事确认');
        $t1=date('m',strtotime($this->_post('time1')));
        $t2=date('m',strtotime($this->_post('time2')));
        $g=round((strtotime($_POST['time2'])-strtotime($_POST['time1']))/86400,2);

        if($t1>='09' && $t2<='11'){                                                     //（9月-11月）包含2天
            $w['time1']=array('like',array(date('Y-09')."%",date('Y-10')."%",date('Y-11')."%"),'OR');
            if(M('person_ask')->where($w)->sum('gong2')+$g>2)$this->error('数据有误或申请的天数累计超过规定天数了！');
        }elseif(($t1=='01'||$t1=='02'||$t1=='12') && ($t1=='01'||$t1=='02'||$t1=='12')){//（12月-2月）包含1天
            $w['time1']=array('like',array(date('Y-01')."%",date('Y-02')."%",date('Y-12')."%"),'OR');
            if(M('person_ask')->where($w)->sum('gong2')+$g>1)$this->error('数据有误或申请的天数累计超过规定天数了！');
        }elseif($t1>='06' && $t2<='08'){                                                //6月-8月）包含2天
            $w['time1']=array('like',array(date('Y-07')."%",date('Y-08')."%"),'OR');
            $w2=$w;
            $w2['time1']=array('like',array(date('Y-06')."%",date('Y-07')."%",date('Y-08')."%"),'OR');
            if(M('person_ask')->where($w)->sum('gong2')+$g>2&&($t1=='07'||$t1=='08'))$this->error('数据有误或申请的天数累计超过规定天数了！');
            if(M('person_ask')->where($w2)->sum('gong2')+$g>4)$this->error('数据有误或申请的天数累计超过规定天数了！');
        }elseif($t1>='03' && $t1<='05'){
            // if(session('name') == '朱明' || session('name') == '李莹' || session('name') == '乔庭鹤'){

            // }else{
                $this->error('该时间不能使用灵活假期！');    
            // }
            
        }else{
            $this->error('跨区间合并使用灵活假期请分成两个申请提交！');
        }
    }

    public function cf()//防止重复提交
    {
        $w['name']=session('name');
        $t=M('person_ask')->where($w)->order('timestamp desc')->getField('timestamp');
        if(time()-strtotime($t) < 60)$this->error('刚才已成功提交申请了，1分钟之内只能提交一次！');
    }


    //彭鑫超过3天的请假，核查页面
    public function list13(){

        if(session('name')=='彭鑫' || session('name') == '张晓明'){
            $w2['class'] = '请假';
            $w2['state']="审核通过";
            $w2['gong'] = array('egt',3);
            $w2['flag'] = array('neq',1);
            $w2['date'] = array('egt','2016-06-01');
        }
       
        $m=M('person_ask')->where($w2)->select();

        if($_GET['flag']){//一键确认全部
            foreach ($m as $v) {
                $_POST['flag']='隐藏';
                $_POST['id']=$v['id'];
                
                $w['id'] = $v['id'];
                
                $d['flag'] = 1;
                
                
                //记录审核操作
                if($this->record($_POST['id'],'['.session('name').']'.$_POST['flag'])){
                    M('person_ask')->where($w)->save($d);   
                }
                
            }
            $_GET['flag'] = 0;
            $this->success('操作成功!');
        }else{
            foreach ($m as $val) {
                $mm[$val['class']][]=$val;
            }
            $this->vo=$mm;
            $this->display('list13');
        }
        
        
    }
    
    //超过3天的请假,彭鑫给予隐藏
    public function hiddenInfo3(){
        
        if($_POST['id']){
            $w['id'] = $_POST['id'];
            
            $d['flag'] = 1;
            
            $this->record($_POST['id'],'['.session('name').']'.'隐藏');//记录审核操作
            M('person_ask')->where($w)->save($d);
            $this->success('操作成功!','list13',1);
        }else{
            $this->success('错误操作，请联系系统管理员!','info3/id/'.$_POST['id'].'/name/'.session('name'));
        }   
                
                
    }

    public function info3($id,$name=''){
        $id=(int)$id;
        $m=M('person_ask')->find($id);
        $this->vo=$m;
        $this->name=$name;
        $this->display();
    }
}
