<?php
/*---------------------------------------------------------------------------
  鸿文OA系统 - 信息管理系统 

  Copyright (c) 2013 http://ihongwen.com All rights reserved.                                             

  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )  

  Author:  jinzhu.yin<smeoa@qq.com>                         

  Support: https://git.oschina.net/smeoa/smeoa               
 -------------------------------------------------------------------------*/

class TeacherAction extends CommonAction {
	protected $config=array('app_type'=>'asst');
	//过滤查询字段

	public function index(){

		// $this->db(1,"mysql://root:123456@localhost:3306/test");

		// foreach ($school as $key => $ ) {
			//查询校区
			if($_POST['school']=='所有校区'){
			}elseif($_POST['school']){
				$aa['school']=$this->_post('school');
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
			$class=M('hw001.class',null)->where($aa)->order('school asc,class asc,teacher asc,grade asc,timee asc,time1 asc')->select();

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

					foreach ($class as $v) {
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
		$this->tw=$tw;
		$this->school=$aa['school'];

		$this->display();
		// var_dump($tw);

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








}
?>