<?php
//多维数组取交集，关联查询过滤，不考虑键值
function array_jj($a,$b){
    foreach ($a as $v1) {
        foreach ($b as $v2) {
            if($v1==$v2)$data[]=$v1;
        }
    }
    return $data;
}
//性别判断
function sex($sex){
    if ($sex == 0) {
        return "女";
    }
    if ($sex == 1) {
        return "男";
    }
}

//把二维数组里的某值升级为键
function fix_array_key($list, $key) {
    $arr = null;
    foreach ($list as $val) {
        $arr[$val[$key]] = $val;
    }
    return $arr;
}

//计算一年多少个星期和每周的开始和结束日期
function get_week($year) { 
    $year_start = $year . "-01-01"; 
    $year_end = $year . "-12-31"; 
    $startday = strtotime($year_start); 
    if (intval(date('N', $startday)) != '1') { 
        $startday = strtotime("next monday", strtotime($year_start)); //获取年第一周的日期 
    } 
    $year_mondy = date("Y-m-d", $startday); //获取年第一周的日期 
 
    $endday = strtotime($year_end); 
    if (intval(date('W', $endday)) == '7') { 
        $endday = strtotime("last sunday", strtotime($year_end)); 
    } 
 
    $num = intval(date('W', $endday)); 
    for ($i = 0; $i <= $num; $i++) { 
        $j = $i -1; 
        $start_date = date("Y-m-d", strtotime("$year_mondy $j week ")); 
 
        $end_day = date("m-d", strtotime("$start_date +6 day")); 
        $start_date = date("m-d", strtotime("$year_mondy $j week ")); 
 
        $week_array[$i+1] = array ($start_date,$end_day); 
        // $week_array[$i+1] = array ( 
        //     str_replace("-", 
        //     ".", 
        //     $start_date 
        // ), str_replace("-", ".", $end_day)); 
    } 
    return $week_array;
} 

//把给定的时间按周次转换成时间戳,(周，星期，时间H:i),返回时间戳
function timee($week,$i,$info){
    if(!$info)return 0;
    $year=strtotime(date('Y',strtotime(session('date'))).'-01-01');
    if($i)$time=date('Y-m-d',$year-24*3600*date('w',$year)+7*24*3600*($week-1)+24*3600*$i).' '.$info.':00';
    if(!$i)$time=date('Y-m-d',$year-24*3600*date('w',$year)+7*24*3600*$week+24*3600*$i).' '.$info.':00';
    return strtotime($time);
}

//二维数组排序
function array_sort($arr,$keys,$type='asc'){ //保持键值不变
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
            $keysvalue[$k] = $v[$keys];
    }
    if($type == 'asc'){
            asort($keysvalue);
    }else{
            arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
    }
    return $new_array; 
} 

// 判断浏览器
function weixin(){
    if(!strpos($_SERVER['HTTP_USER_AGENT'],"MicroMessenger"))die('此功能只能在微信浏览器中使用');
}
//判断审核状态type:1花销，2预算，3退费
function state($val,$type){
    if($type==1){
        if($val==-1)return "审核失败";
        if($val==0)return "退回修改";
        if($val==1)return "校区审核";
        if($val==2)return "集团审核";
        if($val==3)return "审核通过";
    }
    if($type==2){
        if($val==-1)return "审核失败";
        if($val==0)return "退回修改";
        if($val==1)return "校区审核";
        if($val==2)return "部门审核";
        if($val==3)return "总裁审核";
        if($val==4)return "财务确认";
        if($val==5)return "审核通过";
    }
    if($type==3){
        if($val==0)return "退回修改";
        if($val==1)return "财务确认";
        if($val==2)return "校区审核";
        if($val==3)return "部门审核";
        if($val==4)return "集团审批";
        if($val==5)return "退款确认";
        if($val==6)return "退款完成";
    }
}

/**
微信公众号调用的相关方法
*/

// 把姓名转化成微信id
function userid($name){
    $data=M('hw003.person_all',null)->where(array('name'=>$name))->getfield('userid');
    return $data;
}

//发送微信所有形式数据
function send($msg){
    $data=ch_json_encode($msg);//处理要发送的数据
    $tk=M('hw003.access',null)->where('id=1')->find();//获取access_tokon
    if((time()-$tk['timestamp'])>7000)$tk['tokon']=accesstokon();// 遇到tokon过期的情况重新获取
    $url='https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$tk['tokon'];
    $out=url_post($url,$data);
    if($out['errmsg']=='ok'){
        return true;
    }else{
        var_dump($out);die;
    }
}

//post发送数据
function url_post($url,$data){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSLVERSION,CURLOPT_SSLVERSION_TLSv1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $output = curl_exec($ch);
    curl_close($ch);
    $out =json_decode(stripslashes($output), true);//转成数组
    return $out;
}

//get方式获取数据
function url_get($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_SSLVERSION,CURLOPT_SSLVERSION_TLSv1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $output = curl_exec($ch);
    curl_close($ch);
    $data=json_decode(stripslashes($output), true);
    return $data;
}

//重新获取accesstokon值并保存
function accesstokon(){
    $CorpID=C('CorpID');
    $Secret=C('Secret');
    $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$CorpID&corpsecret=$Secret";  
    $data=url_get($url);
    $dat['tokon']=$data['access_token'];//获取到值
    $dat['timestamp']=time();
    M('hw003.access',null)->where('id=1')->save($dat);
    return $data['access_token'];
}

//解决json将中文编码问题
function ch_json_encode($data) {
    $ret = ch_urlencode($data);
    $ret =json_encode($ret);
    return urldecode($ret);
}
function ch_urlencode($data) {
    if (is_array($data) || is_object($data)) {
        foreach ($data as $k => $v) {
            if (is_scalar($v)) {
                if (is_array($data)) {
                    $data[$k] = urlencode($v);
                } elseif (is_object($data)) {
                    $data->$k =urlencode($v);
                }
            } elseif (is_array($data)) {
                $data[$k] = ch_urlencode($v);//递归调用该函数
            } elseif (is_object($data)) {
                $data->$k = ch_urlencode($v);
            }
        }
    }
    return $data;
}










