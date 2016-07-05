<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class TongjiAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	//过滤查询字段

	public function index(){

		//循环校区
		$w['school']=array('neq','集团');
		$school=M('hw001.school',null)->where($w)->select();
		foreach ($school as $key => $value1) {
			//查询校区
			$aa['school']=$value1['school'];
			if($_POST['time']){
				$day=$_POST['time'];
				$date=date('Y-m',strtotime($day));
			}else{
				$date=date('Y-m');
				$day=date('Y-m-d');
			}
			$aa['timee']=array('like',"$date%");
			$aa['state']=array('NEQ',2);
			$class=M('hw001.class',null)->where($aa)->order('timee asc,grade asc,time1 asc,class asc,teacher asc,state asc')->select();

				// $a['class']='数学';
					$ss=$value1['school'];
					$vd[$ss]=array();//本日数据====================
					$vw[$ss]=array();//本周数据====================
					$vm[$ss]=array();//本月数据====================
		            //获取本周一的时间
		            $monday=date('Y-m-d',(strtotime($day)-((date('w',$day)==0?7:date('w',$day))-1)*24*3600));
		            $weekend=date('Y-m-d',(strtotime($day)-((date('w',$day)==0?7:date('w',$day))-1)*24*3600+7*24*3600));

					foreach ($class as $classl) {
	                    if($classl['timee']==$a&&$classl['time1']==$b&&$classl['time2']==$c&&$classl['class']==$d&&$classl['teacher']==$e){
	                    }else{
							switch ($classl['class']) {
								case '数学':
									$vm["$ss"]['数学']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['数学']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['数学']+=$classl['count'];
									break;
								case '语文':
									$vm["$ss"]['语文']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['语文']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['语文']+=$classl['count'];
									break;
								case '英语':
									$vm["$ss"]['英语']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['英语']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['英语']+=$classl['count'];
									break;
								case '物理':
									$vm["$ss"]['物理']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['物理']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['物理']+=$classl['count'];
									break;
								case '化学':
									$vm["$ss"]['化学']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['化学']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['化学']+=$classl['count'];
									break;
								case '生物':
									$vm["$ss"]['生物']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['生物']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['生物']+=$classl['count'];
									break;
								case '政治':
									$vm["$ss"]['政治']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['政治']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['政治']+=$classl['count'];
									break;
								case '历史':
									$vm["$ss"]['历史']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['历史']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['历史']+=$classl['count'];
									break;
								case '地理':
									$vm["$ss"]['地理']+=$classl['count'];
									if($classl['timee']>=$monday && $classl['timee']<$weekend)$vw["$ss"]['地理']+=$classl['count'];
									if($classl['timee']==$day)$vd["$ss"]['地理']+=$classl['count'];
									break;
							}
	                    }
	                        $a=$classl['timee'];
	                        $b=$classl['time1'];
	                        $c=$classl['time2'];
	                        $d=$classl['class'];
	                        $e=$classl['teacher'];
					}

		}
		//月度每日变化量统计
		$w['date']=date('Y-m-d',strtotime($day)-24*3600);;
		$b=M('hw001.tongji',null)->where($w)->select();
		foreach ($b as $val){
			$hj=$val['a']+$val['b']+$val['c']+$val['d']+$val['e']+$val['f']+$val['g']+$val['h']+$val['i'];
			$bh[$val['school']]=array('a'=>$val['a'],'b'=>$val['b'],'c'=>$val['c'],'d'=>$val['d'],'e'=>$val['e'],'f'=>$val['f'],'g'=>$val['g'],'h'=>$val['h'],'i'=>$val['i'],'bh'=>$hj);
		}

		foreach ($vd as $k2 => $v2) {
			$xx[$k2]=$v2['数学']+$v2['语文']+$v2['英语']+$v2['物理']+$v2['化学']+$v2['生物']+$v2['政治']+$v2['历史']+$v2['地理'];
		}
		arsort($xx);
		foreach ($xx as $k3 => $v3) {
			$vdd[$k3]=$vd[$k3];
		}
		foreach ($vm as $k4 => $v4) {
			$xxx[$k4]=$v4['数学']+$v4['语文']+$v4['英语']+$v4['物理']+$v4['化学']+$v4['生物']+$v4['政治']+$v4['历史']+$v4['地理'];
		}
		arsort($xxx);
		foreach ($xxx as $k5 => $v5) {
			$vmm[$k5]=$vm[$k5];
		}
		$this->bh=$bh;
		$this->ms=$date;//输出查询的月份
		$this->dayy=$day;//输出查询的周份
		$this->vd=$vdd;
		$this->vm=$vmm;
		$this->vw=$vw;
		$this->display();
	}

	public function teacher(){
		// die('功能维护中……');
		// $this->db(1,"mysql://root:123456@localhost:3306/test");

		// foreach ($school as $key => $ ) {
			//查询校区
			if($_POST['school']=='所有校区'){
			}elseif($_POST['school']){
				$aa['school']=$this->_post('school');
			}else{
				$aa['school']='######';				
			};
			if($_POST['teacher'])$aa['teacher']=$this->_post('teacher');

			if($_POST['time']){
				$day=$_POST['time'];
				$date=date('Y-m',strtotime($day));
			}else{
				$date=date('Y-m');
				$day=date('Y-m-d');
			}
			$aa['timee']=array('like',"$date%");
			if($_POST['time']&&$_POST['time2'])$aa['timee']=array('between',[$_POST['time'],$_POST['time2']]);
			$aa['state']=array('NEQ',2);
			$class=M('hw001.class',null)->where($aa)->order('school asc,class asc,teacher asc,grade asc,timee asc,time1 asc')->field('class,teacher,timee,time1,grade,count')->select();

				// $a['class']='数学';
					$ss=$aa['school'];
					$tw[$ss]=array();//本周数据====================
					$tm[$ss]=array();//本月数据====================
		            //获取本周一的时间
					$p=strtotime($day);
			        $eee=$p-((date('w',$p)==0?7:date('w',$p))-1)*86400;
		            $moday=$eee;
		            $weday=$eee+2*24*3600;
		            $saday=$eee+5*24*3600;
		            $weekend=$eee+7*24*3600;
					$weekarray=array("0","1","2","3","4","5","6");//为循环判断周几用的

					foreach ($class as $v){
						if($v['grade']==0){
							$tm[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
							$tm[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
							$tm[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								//================================
							if($weekarray[date("w",strtotime($v['timee']))] == 1 or $weekarray[date("w",strtotime($v['timee']))]== 2){//周一、周二（月）统计
									$tw[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
									$tw[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								if(strtotime($v['timee']) >= $moday && strtotime($v['timee']) < $weday){//周一、周二月（周）统计
									$tw[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
									$tw["$ss"][$v['class']][$v['teacher']]['12']+=$v['count'];
									if($v['time1']>='21:30')$tw["$ss"][$v['class']][$v['teacher']]['129']+=$v['count'];
								}
								$tm[$ss][$v['class']][$v['teacher']]['12']+=$v['count'];
								if($v['time1']>='21:30')$tm["$ss"][$v['class']][$v['teacher']]['129']+=$v['count'];
							}elseif($weekarray[date("w",strtotime($v['timee']))] >= 3 && $weekarray[date("w",strtotime($v['timee']))] <= 5){//周一、周二（月）统计
									$tw[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
									$tw[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								if(strtotime($v['timee']) >= $weday && strtotime($v['timee']) < $saday){//周一、周二月（周）统计
									$tw[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
									$tw["$ss"][$v['class']][$v['teacher']]['345']+=$v['count'];
									if($v['time1']>='21:30')$tw["$ss"][$v['class']][$v['teacher']]['3459']+=$v['count'];
								}
								$tm[$ss][$v['class']][$v['teacher']]['345']+=$v['count'];
								if($v['time1']>='21:30')$tm["$ss"][$v['class']][$v['teacher']]['3459']+=$v['count'];
							}elseif($weekarray[date("w",strtotime($v['timee']))] == 6 or $weekarray[date("w",strtotime($v['timee']))]== 0){//周一、周二（月）统计
									$tw[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
									$tw[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								if(strtotime($v['timee']) >= $saday && strtotime($v['timee']) < $weekend){//周一、周二月（周）统计
									$tw[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
									$tw["$ss"][$v['class']][$v['teacher']]['67']+=$v['count'];
									if($v['time1']>='21:30')$tw["$ss"][$v['class']][$v['teacher']]['679']+=$v['count'];
								}
								$tm[$ss][$v['class']][$v['teacher']]['67']+=$v['count'];
								if($v['time1']>='21:30')$tm["$ss"][$v['class']][$v['teacher']]['679']+=$v['count'];
							}
								//======================================
						}elseif($v['grade'] == $g && $v['time1']==$t1 && $v['timee']==$time && $v['teacher']==$teacher && $v['class']== $clas){
						}else{
							$tm[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
							$tm[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
							$tm[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								//================================
							if($weekarray[date("w",strtotime($v['timee']))] == 1 or $weekarray[date("w",strtotime($v['timee']))]== 2){//周一、周二（月）统计
									$tw[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
									$tw[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								if(strtotime($v['timee']) >= $moday && strtotime($v['timee']) < $weday){//周一、周二月（周）统计
									$tw[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
									$tw["$ss"][$v['class']][$v['teacher']]['12']+=$v['count'];
									if($v['time1']>='21:30')$tw["$ss"][$v['class']][$v['teacher']]['129']+=$v['count'];
								}
								$tm[$ss][$v['class']][$v['teacher']]['12']+=$v['count'];
								if($v['time1']>='21:30')$tm["$ss"][$v['class']][$v['teacher']]['129']+=$v['count'];
							}elseif($weekarray[date("w",strtotime($v['timee']))] >= 3 && $weekarray[date("w",strtotime($v['timee']))] <= 5){//周一、周二（月）统计
									$tw[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
									$tw[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								if(strtotime($v['timee']) >= $weday && strtotime($v['timee']) < $saday){//周一、周二月（周）统计
									$tw[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
									$tw["$ss"][$v['class']][$v['teacher']]['345']+=$v['count'];
									if($v['time1']>='21:30')$tw["$ss"][$v['class']][$v['teacher']]['3459']+=$v['count'];
								}
								$tm[$ss][$v['class']][$v['teacher']]['345']+=$v['count'];
								if($v['time1']>='21:30')$tm["$ss"][$v['class']][$v['teacher']]['3459']+=$v['count'];
							}elseif($weekarray[date("w",strtotime($v['timee']))] == 6 or $weekarray[date("w",strtotime($v['timee']))]== 0){//周一、周二（月）统计
									$tw[$ss][$v['class']][$v['teacher']]['name']=$v['teacher'];
									$tw[$ss][$v['class']][$v['teacher']]['class']=$v['class'];
								if(strtotime($v['timee']) >= $saday && strtotime($v['timee']) < $weekend){//周一、周二月（周）统计
									$tw[$ss][$v['class']][$v['teacher']]['已排课时']+=$v['count'];
									$tw["$ss"][$v['class']][$v['teacher']]['67']+=$v['count'];
									if($v['time1']>='21:30')$tw["$ss"][$v['class']][$v['teacher']]['679']+=$v['count'];
								}
								$tm[$ss][$v['class']][$v['teacher']]['67']+=$v['count'];
								if($v['time1']>='21:30')$tm["$ss"][$v['class']][$v['teacher']]['679']+=$v['count'];
							}
								//======================================
						}

						$g = $v['grade'];
						$t1 = $v['time1'];
						$time = $v['timee'];
						$teacher = $v['teacher'];
						$clas = $v['class'];
					}

		$tw=$this->sortx($tw);
		$tm=$this->sortx($tm);

		$this->ms=$date;//输出查询的月份
		$this->dayy=$day;//输出查询的周份
		$sc=M('hw001.school',null)->select();
		$this->sc=$sc;
		$this->tm=$tm;
		if(!($_POST['time']&&$_POST['time2']))$this->tw=$tw;
		$this->school=$aa['school'];

		$this->display();

	}


    




		//内部排序调用
	public function sortx($tmk){

		//组装成一维数组
		foreach ($tmk as $school) {
				foreach ($school as $val) {
					foreach ($val as $v) {
						$arr[]=$v;
					}
				}
		}
		//数组排序
		for($i=0;$i<count($arr)-1;$i++){//循环比较
			for($j=$i+1;$j<count($arr);$j++){
				if($arr[$j]['已排课时']>$arr[$i]['已排课时']){//执行交换
				$temp=$arr[$i];
				$arr[$i]=$arr[$j];
				$arr[$j]=$temp;
				}
			}
		}
		//重新抽取分组
		$g=array();
		for ($v=0; $v < count($arr); $v++) { 
			$g[$arr[$v]['class']][$arr[$v]['name']]=$arr[$v];
		}
		//重新转换成一维数组做排序
		foreach ($g as $a) {
			foreach ($a as $b) {
				if($b['class']=='数学')$gg[0][]=$b;
				if($b['class']=='语文')$gg[1][]=$b;
				if($b['class']=='英语')$gg[2][]=$b;
				if($b['class']=='物理')$gg[3][]=$b;
				if($b['class']=='化学')$gg[4][]=$b;
				if($b['class']=='生物')$gg[5][]=$b;
				if($b['class']=='政治')$gg[6][]=$b;
				if($b['class']=='历史')$gg[7][]=$b;
				if($b['class']=='地理')$gg[8][]=$b;
			}
		}

		for ($i=0; $i < 9; $i++) { 
			for ($ii=0; $ii < count($gg[$i]); $ii++) { 
				$ggg[]=$gg[$i][$ii];
			}
		}

		return $ggg;

	}

/**
校区各科讲师人均课时统计
*/

	public function classs(){
		if($_POST){
			$m=M('hw003.person_all',null)->where(['position'=>['in','讲师,教学副校长'],'state'=>1])->order('school')->getField('id,school,name',true);
			$m[]=["name"=>"毛健","school"=>"水木清华"];
			$m[]=["name"=>"杨桂超","school"=>"水木清华"];
			$m[]=["name"=>"杨丽娜","school"=>"水木清华"];
			$m[]=["name"=>"孙晓慧","school"=>"水木清华"];
			$m[]=["name"=>"何男","school"=>"水木清华"];
			$m[]=["name"=>"赵阳阳","school"=>"水木清华"];
			$m[]=["name"=>"魏广忠","school"=>"水木清华"];
			$m[]=["name"=>"于斌","school"=>"日月兴城"];
			$m[]=["name"=>"王坤","school"=>"日月兴城"];
			$m[]=["name"=>"杨志新","school"=>"日月兴城"];
			$m[]=["name"=>"于娇梅","school"=>"日月兴城"];
			$m[]=["name"=>"吴冬梅","school"=>"日月兴城"];
			$m[]=["name"=>"张梅","school"=>"日月兴城"];
			$m[]=["name"=>"刘晓越","school"=>"日月兴城"];
			$m[]=["name"=>"李邦源","school"=>"日月兴城"];
			$m[]=["name"=>"李环宇","school"=>"天丽校区"];
			foreach ($m as $v) {
				$km=M('hw001.teacher',null)->where(['name'=>$v['name']])->find();
				$tj=$this->tongji($_POST['t1'],$_POST['t2'],$v['name'],$v['school']);
				$data[$v['school']][$km['class']][$v['name']]=$tj;
				$data[$v['school']][$km['class']]['合计']+=$tj;
			}
			foreach ($data as $k => $v) {
				if($k!='集团')
				foreach ($v as $k2 => $val) {
					$list[$k][$k2]['a']=count($val)-1;
					$list[$k][$k2]['b']=round($val['合计']/($list[$k][$k2]['a']),2);
				}
			}
		}
		$this->list=$list;
		$this->display('class');
	}

    public function tongji($t1,$t2,$teacher=0,$school=0){
        $w['timee']=array('between',[$t1,$t2]);
        $w['grade']=0;
        $w['state']=['neq',2];
        if($school)$w['school']=$school;
        if($teacher)$w['teacher']=$teacher;
        $data1=M('hw001.class',null)->where($w)->sum('count');
        
        $w['grade']=array('gt',0);
        $m=M('hw001.class',null)->where($w)->order('school,timee,grade,time1')->field('school,timee,grade,time1,teacher,count,class')->select();
        foreach ($m as $v) {
            if($v!=$aa)$data2+=$v['count'];
            $aa=$v;
        }
        $data=$data1+$data2;
        return $data;
    }


}
?>