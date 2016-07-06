<?php
// 为任务管理部分提供服务
class Comm2Action extends Action {

    //每次加载是获取用户信息
    Public function _initialize(){
        if(!session('?pname')||!session('uid')){

        //获取员工id
            if (isset($_GET['code'])&&$_GET['code']!=''){
                //==获取code和tokon
                $code=$_GET['code'];
                //获取并判断access_tokon是否过期获取tokon
                $tk=M('hw003.access',null)->find(1);
                if((time()-$tk['timestamp'])>7000){
                    $access_token=accesstokon();
                }else{
                    $access_token=$tk['tokon'];
                }

                //====通过code换取获取员工id信息
                $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=$access_token&code=$code&agentid=2";//$agentid=0调用的应用id
                $info=url_get($url);
                $user_id=$info['UserId'];

                //====通过id换取获取员工资料信息$user_info
                $url = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&userid=$user_id";
                $user_info=url_get($url);
                // 将获取到的值存储到seccion
                session('name',$user_info['name']);

                //存储系统需要的相关信息
                $mod=M('hw003.task_user',null);
                $m=$mod->where(array('name'=>$user_info['name']))->find();
                $pname=$mod->where(array('id'=>$m['pid']))->getField('name');
                session('uid',$m['id']);
                session('pid',$m['pid']);
                session('pname',$pname);
            }else{
                if(session('user_name')){
                    $mod=M('hw003.task_user',null);
                    $m=$mod->where(array('name'=>session('user_name')))->find();
                    $pname=$mod->where(array('id'=>$m['pid']))->getField('name');
                    session('uid',$m['id']);
                    session('pid',$m['pid']);
                    session('name',session('user_name'));
                    session('pname',$pname);
                }
            }
        }
    }

/**
微信发送信息调用
*/
    //发送text消息，应用ID，接收人姓名（可以是数组多人），信息内容。
    public function text($app,$name,$message){
        if(is_array($name)){
            foreach ($name as $val) {
                $n[]=userid($val);
            }
            $name=implode("|",$n);
            $msg['touser']=$name;
        }else{
            $msg['touser']=userid($name);
        }
        $msg['msgtype']='text';
        $msg['agentid']=$app;
        $msg['text']['content']=$message;
        if(!$msg['touser'])return true;
        if(send($msg))return true;
    }

//发送单图文消息
    public function news($agentid,$name,$title=null,$description=null,$url=null){
        $message=array('title'=>$title,'description'=>$description,'url'=>$url);
        if(is_array($name)){
            foreach ($name as $val) {
                $n[]=userid($val);
            }
            $name=implode("|",$n);
            $msg['touser']=$name;
        }else{
            $msg['touser']=userid($name);
        }
        $msg['msgtype']='news';
        $msg['agentid']=$agentid;
        $msg['news']['articles'][]=$message;
        if(!$msg['touser'])return true;
        if(send($msg))return true;
    }

}


?>