<?php
// 本类由系统自动生成，仅供测试用途
class CommAction extends Action {

	//每次加载是获取用户信息
    Public function _initialize(){
		if(!session('?name')){
		//获取员工id
	    	/*if (isset($_GET['code'])&&$_GET['code']!=''){
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
                // $url = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&userid=$user_id";
                // $user_info=url_get($url);
				// 将获取到的值存储到session
                 $pid=M('hw003.person_all',null)->where(['userid'=>$info['UserId'],'state'=>1])->find(); 
              
                // var_dump($info);
                if(!$pid)die;
                session('pid',$pid['id']);
				session('name',$pid['name']);
				session('userid',$info['UserId']);
		    }else{
		    	  echo("没有获取到员工信息");
		    	  exit;
		    }*/
		    session('pid',123456);
				session('name','张晓明');
				session('userid','ww');
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
        if(send($msg))return true;
    }


}


?>
