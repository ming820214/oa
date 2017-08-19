<?php
// 本类由系统自动生成，仅供测试用途
class TaskAction extends Comm2Action {
    public function index(){

        $url='http://i.ihongwen.com/oa_old/weixin.php/task/info/id/';
        if(IS_POST){
            if($this->_post('add')){
                $mod=M("hw003.task",null);
                $mod->create();
                $mod->pid=session('pid');
                $mod->uid=session("uid");
                $id=$mod->add();
                
                if($id)$this->news(11,session('pname'),session('name'),"有新任务：".$_POST['title'],$url.$id);
            }
            if($this->_post('advice')){
                $mod=M("hw003.task_advice",null);
                $mod->create();
                $mod->pid=session('pid');
                $mod->type=0;
                $mod->uid=session("uid");
                if($mod->add())$this->text(11,session('pname'),session('name')."，问题反馈：".$_POST['info']);
            }
        }
        if(IS_AJAX){
//          申请关闭任务
            if($_GET['delt']){
                $title=M('hw003.task',null)->where(array('id'=>$_GET['delt']))->getField('title');
                M('hw003.task',null)->where(array('id'=>$_GET['delt']))->setField('level','申请关闭任务');
                $this->news(11,session('pname'),session('name'),"申请关闭任务",$url.$_GET['delt']);
                print 1;die;
            }elseif($_GET['ask']){
                $title=M('hw003.task',null)->where(array('id'=>$_GET['delt']))->getField('title');
                M('hw003.task',null)->where(array('id'=>$_GET['delt']))->setField('level','等待回复');
                $this->text(11,session('pname'),session('name')."，申请快速答复：".$title);
                print 1;die;
            }else{
                $msg=M('hw003.task_info',null)->add(array('tid'=>$_GET['id'],'info'=>$_GET['info']));
                if($msg)
                print_r(json_encode(M('hw003.task_info',null)->find($msg)));
                $this->news(11,session('pname'),session('name'),"新进展：".$_GET['info'],$url.$_GET['id']);
                die;
            }
        }
        $w['uid']=session('uid');
        $w['state']=0;
        $list=M('hw003.task',null)->where($w)->order('level desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]=$v;
            $list[$k]['tasked']=M('hw003.task_info',null)->where(array('tid'=>$v['id']))->order('timestamp')->field('id,info,timestamp,type')->select();
        }
        $this->list1=$list;
        $this->advice=M('hw003.task_advice',null)->where(array('uid'=>session('uid')))->order('timestamp desc')->limit(11)->select();

        //2、工作组===============================>>
        $ww['pid']=session('pid');
        $ww['state']=0;
        $modd=M('hw003.task_group',null)->where($ww)->select();
        foreach ($modd as $k0=>$v) {
            $zai=array_flip(explode(',',$v['uid']));
            if(isset($zai[session('uid')])){
                $list3[$k0]['info']=$v;
                //任务列表
                $w['group']=$v['id'];
                $w['state']=0;

                $group_task=M('hw003.task',null)->where($w)->order('level desc')->select();
                foreach ($group_task as $k => $v) {
                    $name=M('hw003.task_user',null)->where(array('id'=>$v['uid']))->getField('name');
                    $list3[$k0]['task'][$name][$k]=$v;
                    $list3[$k0]['task'][$name][$k]['tasked']=M('hw003.task_info',null)->where(array('tid'=>$v['id']))->order('timestamp')->field('id,info,timestamp,type')->select();
                }
            }
        }
        $this->list2=$list3;

        $this->display();
    }

    public function reply($date=null){
        $url='http://i.ihongwen.com/oa_old/weixin.php/task/info/id/';
        if(IS_POST){
//      创建工作组
            if($this->_post('group_add')){
                $mod=M('hw003.task_group',null);
                $mod->create();
                $mod->uid=implode(',', $_POST['uid']);
                $mod->pid=session('uid');
                $mod->add();
                $name=M('hw003.task_user',null)->where(array('id'=>$_POST['uid']))->getField('name');
                $this->text(11,$name,'新任务：'.$_POST['title'].';'.$_POST['info']);
            }
//      创建任务
            if($this->_post('add')){
                // var_dump($_POST);die;
                $user=M('hw003.task_user',null)->find($_POST['uid']);
                $mod=M("hw003.task",null);
                $mod->create();
                $mod->pid=$user['pid'];
                $mod->uid=$_POST['uid'];
                $mod->add();
                $name=M('hw003.task_user',null)->where(array('id'=>$_POST['uid']))->getField('name');
                $this->text(11,$name,'新任务：'.$_POST['title'].';'.$_POST['info']);
            }
        }
        if(IS_AJAX){
//      添加批示
            if($_GET['tid']){
                $mod=M('hw003.task_info',null);
                if($mod->add(array('tid'=>$_GET['tid'],'info'=>$_GET['info'],'type'=>1))){
                    $uid=M('hw003.task',null)->where(array('id'=>$_GET['tid']))->getField('uid');
                    $name=M('hw003.task_user',null)->find($uid);
                    $info=M('hw003.task_info',null)->where(['tid'=>$_GET['tid'],'type'=>1])->order('id desc')->find();
                    $this->text(11,$name['name'],$name['title'].",新指示：".$_GET['info']);
                    print 1;die;
                }
            }
//          关闭任务
            if($_GET['delt']){
                if(M('hw003.task',null)->where(array('id'=>$_GET['delt']))->setField('state',1))
                print 1;die;
            }
//          变更任务状态
            if($_GET['change']){
                $mod=M('hw003.task',null);
                if($mod->where(array('id'=>$_GET['change']))->setField('level',$_GET['level'])){
                    $task=$mod->where(array('id'=>$_GET['change']))->find();
                    $name=M('hw003.task_user',null)->where(['id'=>$task['uid']])->getField('name');
                    $this->text(11,$name,"提示：任务,".$task['title'].",状态变更为：".$_GET['level']);
                    $this->ajaxReturn(1);
                }
            }
//          回复反馈的问题
            if($_GET['name']){
                $msg=M('hw003.task_advice',null);
                $uid=M('hw003.task_user',null)->where(array('name'=>$_GET['name']))->find();
                if($msg->add(array('uid'=>$uid['id'],'pid'=>$uid['pid'],'info'=>$_GET['info'],'type'=>1)))
                print 1;
                $this->text(11,$_GET['name'],"新反馈：".$_GET['info']);die;
            }
//      关闭任务组
            if($_GET['delt_group']){
                if(M('hw003.task_group',null)->where(array('id'=>$_GET['delt_group']))->setField('state',1))
                print 1;die;
            }
        }
        //1、任务列表=======================================>>
        $w['state']=0;
        $w['pid']=session('uid');

        //========重叠查看权限=====
        if(session('name')=='周晓伟')$w['pid']=5;
        if($date)unset($w['pid']);
        //======结束

        $list=M('hw003.task',null)->where($w)->order('level desc')->select();
        foreach ($list as $k => $v) {
            $name=M('hw003.task_user',null)->where(array('id'=>$v['uid']))->getField('name');
            $list1[$name][$k]=$v;
            $list1[$name][$k]['tasked']=M('hw003.task_info',null)->where(array('tid'=>$v['id']))->order('timestamp')->field('id,info,timestamp,type')->select();
        }
        $this->list1=$list1;
        //3、问题反馈相关==============================>>
        unset($w['state']);
        $advice=M('hw003.task_advice',null)->where($w)->order('timestamp desc')->select();
        foreach ($advice as $v) {
            $name=M('hw003.task_user',null)->where(array('id'=>$v['uid']))->getField('name');
            if(!$list2[$name][10]){
                $list2[$name][]=$v;
            }
        }
        $this->advice=$list2;
        //2、工作组===============================>>
        $ww['pid']=session('uid');
        $ww['state']=0;
        $modd=M('hw003.task_group',null)->where($ww)->select();
        foreach ($modd as $k0=>$v) {
            $list3[$k0]['info']=$v;
            //任务列表
            $w['group']=$v['id'];
            $w['state']=0;

            $group_task=M('hw003.task',null)->where($w)->order('level desc')->select();
            foreach ($group_task as $k => $v) {
                $name=M('hw003.task_user',null)->where(array('id'=>$v['uid']))->getField('name');
                $list3[$k0]['task'][$name][$k]=$v;
                $list3[$k0]['task'][$name][$k]['tasked']=M('hw003.task_info',null)->where(array('tid'=>$v['id']))->order('timestamp')->field('id,info,timestamp,type')->select();
            }
        }
        $this->list2=$list3;

        $this->group=M('hw003.task_group',null)->where(array('pid'=>session('uid')))->getField('id,title,state',true);

        //今日工作汇报
        foreach ($list as $v) {
            $taskid[]=$v['id'];
        }
        $w3['tid']=array('in',$taskid);
        $w3['type']=0;
        $w3['timestamp']=array('like',date('Y-m-d')."%");
        if($date)$w3['timestamp']=array('like',$date."%");
        $day=M('hw003.task_info',null)->where($w3)->order('timestamp')->select();
        foreach ($day as $k=>$v) {
            $in=M('hw003.task',null)->field('uid,title,level')->find($v['tid']);
            $name=M('hw003.task_user',null)->where(array('id'=>$in['uid']))->getField('name');
            $data[$name][$k]['title']=$in['title'];
            $data[$name][$k]['level']=$in['level'];
            $data[$name][$k]['info']=$v;
            $data[$name][$k]['tid']=$v['tid'];
        }

        $this->day=$data;

        $this->uid=$this->uid_name();
        $this->display();
    }

    //微信链接进入参看新进展，任务id
    public function info($id){
        //任务列表
        $v=M('hw003.task',null)->find($id);
        $name=M('hw003.task_user',null)->where(array('id'=>$v['uid']))->getField('name');
        $list[$name]=$v;
        $list[$name]['tasked']=M('hw003.task_info',null)->where(array('tid'=>$v['id']))->order('timestamp')->field('id,info,timestamp,type')->select();
        $this->name=$name;
        $this->list=$list;
        $this->display();
    }

    //获取子员工id姓名
    function uid_name(){
        $mod=M('hw003.task_user',null)->where(array('pid'=>session('uid')))->select();
        return $mod;
    }

    //浏览已关闭的任务
    function closed(){

        $w['state']=1;
        $w['pid']=session('uid');

        //========重叠查看权限=====
        if(session('name')=='周晓伟')$w['pid']=5;
        if($date)unset($w['pid']);
        //======结束

        $list=M('hw003.task',null)->where($w)->order('level desc')->select();
        foreach ($list as $k => $v) {
            $name=M('hw003.task_user',null)->where(array('id'=>$v['uid']))->getField('name');
            $list1[$name][$k]=$v;
            $list1[$name][$k]['tasked']=M('hw003.task_info',null)->where(array('tid'=>$v['id']))->order('timestamp')->field('id,info,timestamp,type')->select();
        }
        $this->list1=$list1;
        $this->display();
    }

}
