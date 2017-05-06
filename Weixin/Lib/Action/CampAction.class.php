<?php
class CampAction extends CommAction {
	
	private $pageNumber=0;
	private $pageCount=10000;
	
	public function index(){
		$this->display();
	}
	
	//列出前往所属管辖区学员的列表
	public function list_all_stu(){
	
		$teacher_room = M('hw001.camp_classroom',null);
		$teacher_floor = M('hw001.camp_floor_responsor',null);
		$trace = M('hw001.student_trace_log',null);
		
		
		$rooms = $teacher_room->where("class_responsor='" . session('name') . "'")->getField('floor,class_name');
		$floors = $teacher_floor->where("responsor='" . session('name') . "'")->getField('floor',true);
		
		$stu_lst1 = array();
		if($floors){
			$stu_lst1 = $trace->where([floor=>array("in",$floors)])->select();
		}
		
		
		$w = '';
		foreach ($rooms as $k=>$v){
			$w .= "(floor = " . $k . " AND room='" . $v . "') OR "; 
		}
		
		$w = substr($w,0,strlen($w)-3);
		
		$stu_lst2 = array();
		if($w){
			$stu_lst2 = $trace->where($w)->select();
		}
		
		if(!$stu_lst1){
			$stu_lst1 = array();
		}
		
		if(!$stu_lst2){
			$stu_lst2 = array();
		}
		
		$stu_lst = array_merge($stu_lst1,$stu_lst2);
		
		//数组去重
		foreach ($stu_lst as $m=>$n){
			foreach ($stu_lst as $mu=>$nu){
				if($mu>$m){
					if($n['stuid'] == $nu['stuid']){
						unset($stu_lst[$mu]);
					}
				}
				
			}
		}
		
		$stu_lst = array_filter($stu_lst);
		
		$this->data = $stu_lst;
		
		$this->display();
	
	}
	
	
	public function claim(){
		//=============================楼层、房间负责人更新定位===========================================
		
		$ids = $_POST['stuid'];
		
		$trace = M('hw001.student_trace_log',null);
		//多人统一处理
		if(strpos($_POST['stuid'],',') !== false){
			$stuid_array = split(',',$_POST['stuid']);
			if(count($stuid_array)>0){
				$logs = $trace->where(['stuid' => array('in',$ids)])->select();
			}else{
				$this->error('请选择相应的学员！','list_all_stu');
			}
			
			if($logs){
				$stu = M('hw001.class_student',null);
				
				foreach ($logs as $m=>$n){
					if($n['floor'] && $n['room']){
						$content = $n['floor'] . '->' . $n['room'];
					}else{
						if($n['floor']){
							$content = $n['floor'] . '层';
						}
						if($n['room']){
							$content = $n['room'] . '房间';
						}
					}
					
					$stu->where('stuid=' . $n['stuid'])->save(['position_now'=>$content]);
					
					//通知班级负责人
					$lst = $stu->alias('stu')->join('hw001.camp_class_teacher as teacher on stu.grade_id = teacher.grade_id')->where('stu.stuid=' . $n['stuid'])->getField('teacher_name',true);
					foreach ($lst as $ob){
						$this->text(7,"$ob", $n['stu_name'] .' 学员已经抵达目的地 ' . $content);
					}
					
					$trace->where('stuid=' . $n['stuid'])->delete();
				}
				$this->success('已告知学员相应班级负责人!','list_all_stu');
			}
			
		}else{
			//单人处理
			if($ids){
				$log = $trace->where(['stuid' => array('in',$ids)])->select();
			}
			
			if($log){
				if($log[0]['floor'] && $log[0]['room']){
					$content = $log[0]['floor'] . '->' . $log[0]['room'];
				}else{
					if($log[0]['floor']){
						$content = $log[0]['floor'] . '层';
					}
					if($log[0]['room']){
						$content = $log[0]['room'] . '房间';
					}
				}
			}
				
			$stu = M('hw001.class_student',null);
			$stu->where('stuid=' . $log[0]['stuid'])->save(['position_now'=>$content]);
			
			$trace->where('stuid=' . $log[0]['stuid'])->delete();
			
			//通知班级负责人			
			$lst = $stu->alias('stu')->join('hw001.camp_class_teacher as teacher on stu.grade_id = teacher.grade_id')->where('stu.stuid=' . $log[0]['stuid'])->getField('teacher_name',true);
			foreach ($lst as $ob){
				$this->text(7,"$ob", $log[0]['stu_name'] .' 学员已经抵达目的地 ' . $content);
			}
			
			$this->success('已告知学员相应班级负责人!','list_all_stu');
		}
		
		
		
		
		//==============================================================================
	}
	
	//进入教室上课
	public function inclassroom(){
		
		$teacher = M('hw001.camp_class_teacher',null);
		$gid = $teacher->field('grade_id')->where("teacher_name='" . session('name') . "'")->find();
		if($gid){
			
			$class = M('hw001.class_student',null);
			$room = M('hw001.camp_classroom',null);
			if($class->where(['grade_id'=>array('in',$gid)])->count()){
				$this->success('查看课堂','camp');
				
			}else{
				$model = new Model();
				$data = $model->query("SELECT stu.school,stu.gid as grade_id,stu.stuid,stu.name as stu_name,CONCAT_WS('->',room.floor ,room.class_name) as class_room,CONCAT_WS('->',room.floor ,room.class_name) as position_now FROM hw001.camp_class_teacher as teacher, hw001.school_grade as grade,hw001.stu_grade_t  AS stu,hw001.camp_classroom  AS room WHERE  grade.is_del = 0 AND grade.id = teacher.grade_id  AND teacher.teacher_name ='" . session('name') . "' AND grade.id= stu.gid AND grade.id = room.grade_id " );
				
				if($data){
					foreach ($data as $obj){
						$class->add($obj);
					}
					$room->where(['grade_id' =>array('in',$gid)])->save(['state'=>2]);
					$this->success('全体学员进入课堂','camp');
				}else{
					$this->error('暂时没有与您匹配的班级与学员，请与系统管理员联系！','index');
				}
					
			}
		}else{
			$this->error('暂时没有与您匹配的班级，请与系统管理员联系！','index',false);
		}
		
	}
	
	
	//查看课堂学员
	public function camp(){
		
		$student = M('hw001.class_student',null);
		$model = M();
		//正常课堂人员
		$data1 = $model->query("select  stu.id, stu.school, stu.grade_id, stu.stuid, stu.stu_name, stu.stu_state, stu.class_room, stu.position_now from hw001.class_student as stu,hw001.camp_class_teacher as teacher where stu.grade_id = teacher.grade_id AND teacher.teacher_name = '" . session("name") . "' AND stu_state=1");
		
		//$data2 = $model->query("select  stu.id, stu.school, stu.grade_id, stu.stuid, stu.stu_name, stu.stu_state, stu.class_room, stu.position_now from hw001.class_student as stu,hw001.camp_class_teacher as teacher where stu.grade_id = teacher.grade_id AND teacher.teacher_name = '" . session("name") . "' AND stu_state=2");
		//离开且抵达目的地人员
		$data2 = $model->query("select student.id, student.school, student.grade_id, student.stuid, student.stu_name, student.stu_state, student.class_room, student.position_now, CONCAT_WS('->',slog.floor ,slog.room) as destination from (SELECT stu.id, stu.school, stu.grade_id, stu.stuid, stu.stu_name, stu.stu_state, stu.class_room, stu.position_now FROM hw001.class_student AS stu, hw001.camp_class_teacher AS teacher WHERE     stu.grade_id = teacher.grade_id AND teacher.teacher_name = '" . session("name") . "' AND stu.stu_state = 2 AND (stu.position_now != '' AND stu.position_now is not null AND stu.class_room != stu.position_now)) as student left join hw001.student_trace_log as slog  on student.stuid = slog.stuid");
		
		//$data3 = $model->query("select  stu.id, stu.school, stu.grade_id, stu.stuid, stu.stu_name, stu.stu_state, stu.class_room, stu.position_now from hw001.class_student as stu,hw001.camp_class_teacher as teacher where stu.grade_id = teacher.grade_id AND teacher.teacher_name = '" . session("name") . "' AND stu_state=2 AND num>=10");
		//离开未抵达目的地人员
		$data3 = $model->query("select student.id, student.school, student.grade_id, student.stuid, student.stu_name, student.stu_state, student.class_room, student.position_now, CONCAT_WS('->',slog.floor ,slog.room) as destination from (SELECT stu.id, stu.school, stu.grade_id, stu.stuid, stu.stu_name, stu.stu_state, stu.class_room, stu.position_now FROM hw001.class_student AS stu, hw001.camp_class_teacher AS teacher WHERE     stu.grade_id = teacher.grade_id AND teacher.teacher_name = '" . session("name") . "' AND stu.stu_state = 2 AND (stu.position_now = '' OR stu.position_now is null) ) as student left join hw001.student_trace_log as slog  on student.stuid = slog.stuid");
		//课堂正常人员
		$this->data1 = $data1;
		$this->data1count = count($data1);
		//正常离开课堂人员
		$this->data2 = $data2;
		$this->data2count = count($data2);
		//呼叫超过10次，仍未得到相关人员答复的学员；
		$this->data3 = $data3;
		$this->data3count = count($data3);
		
		$floor = M('hw001.camp_floor_responsor');
		
		$floor_list = $floor->getField('floor',true);
		
		$this->floor_list = $floor_list;
		
		$room = M();
		$room_list = $room->query('select grade_id,class_name,floor from hw001.camp_classroom where grade_id in (select id from hw001.school_grade where school="' . session('school_s') . '")');

		$this->room_list = $room_list;
		$this->display();
	}
	
	//指派学员到相应楼层或者房间
	public function assignRoom(){
		
		$trace = M('hw001.student_trace_log',null);
		$trace->create();
		
		if(strpos($_POST['grade_id'],',') !== false){
			$grade_id_array = split(',',$_POST['grade_id']);
			$stuid_array = split(',',$_POST['stuid']);
			$stu_name_array = split(',',$_POST['stu_name']);
			
			$grade_id_array = array_filter($grade_id_array);
			$stuid_array = array_filter($stuid_array);
			$stu_name_array = array_filter($stu_name_array);
			
			foreach ($stuid_array as $k=>$v){
				
				$param = array();
				
				if(!$_POST['floor'] && !$_POST['room']){
					$this->error('请选择楼层或者教室！','camp');
					die();
				}
				
				$student = M('hw001.class_student',null);
				$pos = $student->where('stuid='. $v)->getField('position_now');
				
				$param['stuid'] = $v;
				$param['stu_name'] = $stu_name_array[$k];
				//$param['grade_id'] = $grade_id_array[$k];
				$param['floor'] = $_POST['floor'];
				$param['room'] = $_POST['room'];
				$param['reason'] = $_POST['reason'];
				
				$now_floor = split('->',$pos)[0];
				$now_room = split('->',$pos)[1];
				
				
				$floor_list = array();
					
				if($_POST['floor']){
					$condition = '';
					if($now_floor>=$_POST['floor']){
						$condition = 'floor>=' . $_POST['floor'] . ' and floor <=' . $now_floor ;
					}else{
						$condition = 'floor>=' . $now_floor . ' and floor <=' . $_POST['floor'] ;
					}
						
					$floor = M('hw001.camp_floor_responsor',null);
						
					$floor_list = $floor->where($condition . ' AND school="' . session('school_s') . '"')->getField('responsor',true);
						
						
					$r['floor'] = $_POST['floor'];
				}
				
				$room_list = array();
				$room = M('hw001.camp_classroom',null);
					
				if($_POST['room']){
					$r['class_name'] = $_POST['room'];
						
					$r['school'] = session('school_s');
						
					$room_list = $room->where($r)->getField('class_responsor',true);
				}
				
				$teacher = M('hw001.camp_class_teacher',null);
					
				$teacher_list = $teacher->where('grade_id=' . $grade_id_array[$k])->getField('teacher_name',true);
					
				$notice = array_merge($floor_list,$room_list,$teacher_list);
				$notice = array_unique($notice);
				
				$content = $stu_name_array[$k] . '同学前往 ';
					
				if($_POST['floor']){
					$content .= $_POST['floor'] . '层 ';
				}
					
				if($_POST['room']){
					$content .= $_POST['room'] . '房间';
				}
					
				$stuid = $stuid_array[$k];
				
				if($stuid){
						
					foreach ($notice as $ob){
						if($ob){
							$this->text(7,"$ob",$content . '；注意：相关人员，请及时核对……');
						}
					}
						
					$mem = implode(',',$notice);
						
					$param['wx'] = $mem;
						
					if($trace->add($param)){
						//=============================清空学生更新定位===========================================
						$stu = M('hw001.class_student',null);
						$stu->where('stuid=' . $stuid)->save(['position_now'=>'','stu_state'=>2,'num'=>1]);
						//==============================================================================
					}
						
				}
				
			}
			
			$this->success('已通告相关人员!','camp');
		}else{
			$trace->grade_id = $_POST['grade_id'];
			
			if(!$trace->floor && !$trace->room){
				$this->error('请选择楼层或者教室！','camp');
			}
			
			$student = M('hw001.class_student',null);
			
			$pos = $student->where('stuid='. $trace->stuid)->getField('position_now');
			
			$now_floor = split('->',$pos)[0];
			$now_room = split('->',$pos)[1];
			
			$floor_list = array();
			
			if($trace->floor){
				$condition = '';
				if($now_floor>=$trace->floor){
					$condition = 'floor>=' . $trace->floor . ' and floor <=' . $now_floor ;
				}else{
					$condition = 'floor>=' . $now_floor . ' and floor <=' . $trace->floor ;
				}
					
				$floor = M('hw001.camp_floor_responsor',null);
					
				$floor_list = $floor->where($condition . ' AND school="' . session('school_s') . '"')->getField('responsor',true);
					
					
				$r['floor'] = $trace->floor;
			}
			
			$room_list = array();
			$room = M('hw001.camp_classroom',null);
			
			if($trace->room){
				$r['class_name'] = $trace->room;
					
				$r['school'] = session('school_s');
					
				$room_list = $room->where($r)->getField('class_responsor',true);
			}
			
			
			$teacher = M('hw001.camp_class_teacher',null);
			
			$teacher_list = $teacher->where('grade_id=' . $trace->grade_id)->getField('teacher_name',true);
			
			$notice = array_merge($floor_list,$room_list,$teacher_list);
			$notice = array_unique($notice);
			
			$content = $trace->stu_name . '同学前往 ';
			
			if($trace->floor){
				$content .= $trace->floor . '层 ';
			}
			
			if($trace->room){
				$content .= $trace->room . '房间';
			}
			
			$stuid = $trace->stuid;
			if($stuid){
					
				foreach ($notice as $ob){
					if($ob){
						$this->text(7,"$ob",$content . '；注意：相关人员，请及时核对……');
					}
				}
					
				$mem = implode(',',$notice);
					
				$trace->wx = $mem;
					
				if($trace->add()){
						
						
					//=============================楼层、房间负责人更新定位===========================================
					/* $stu = M('hw001.class_student',null);
					 if($trace->floor && $trace->room){
					 $content = $trace->floor + '->' + $trace->room;
					 }else{
					 if($trace->floor){
					 $content = $trace->floor + '层';
					 }
					 if($trace->room){
					 $content = $trace->room + '房间';
					 }
					 }
			
					 $stu->where('stuid=' . $trace->stuid)->save(['position_now'=>$content]); */
					//==============================================================================
						
						
					//=============================清空学生更新定位===========================================
					$stu = M('hw001.class_student',null);
					$stu->where('stuid=' . $stuid)->save(['position_now'=>'','stu_state'=>2,'num'=>1]);
					//==============================================================================
					$this->success('已通告相关人员:' . $mem ,'camp');
				}
					
			}
		}
	}
	
	
	
	//循环轮询通知相应负责人该学员前往所属区域，注意查看
	public function wxNotice(){
		
		// 发送给页面的数据
		$stu = M('hw001.class_student');
		
		$notice = M('hw001.student_trace_log',null);
		$list = $notice->select();
		
		$flag = 1;
		
		foreach($list as $obj){

			if((time() - strtotime($obj['create_time'])) >= 5*60){
				$flag = 2;
				$content = $obj['stu_name'] . '同学前往 ';
					
				if($obj['floor']){
					$content .= $obj['floor'] . '层 ';
				}
				if($obj['room']){
					$content .=  $obj['room'] . '房间';
				}
					
				if($obj['stuid'] && $obj['wx']){
					$person = split(',',$obj['wx']);
					foreach($person as $p){
						$this->text(7,"$p",$content . '；注意：相关人员，请及时核对……');
					}
				}
				$obj['create_time'] = date('Y-m-d H:i:s');
				$notice->save($obj);
				$num = $stu->where(['stuid'=>$obj['stuid']])->getField('num');
				$stu->where(['stuid'=>$obj['stuid']])->save(['num' => $num+1]);
			}
		}
			
		//$this->text(7,"张晓明", '注意：相关人员，请及时核对……');
		if($flag == 2){
			$this->ajaxReturn([
			
					'count'=>count($list),//查询结果
					'data'=>$list
			
			]);
		}else{
			$this->ajaxReturn([
						
					'count'=>0,//查询结果
					'data'=>'本次没有消息发送！'
						
			]);
		}
		
	}
	
	//重置学员状态，学员回归课堂，初始化原始状态
	public function resetStu(){
		$stu = M('hw001.class_student',null);
		$trace = M('hw001.student_trace_log',null);
		
		$name = $_POST['stu_name'];
		$wx = '';
		if($_POST['stuid'] && $_POST['stu_name']){
			if(strpos($_POST['stuid'],',') !== false){
				$stuid_array = split(',',$_POST['stuid']);
				$stuid_array = array_filter($stuid_array);
				foreach ($stuid_array as $ob){
					$mo = $stu->where(['stuid'=>$ob])->select();
					if($ob && $mo){
						$mo[0]['position_now'] = $mo[0]['class_room'];
						$mo[0]['num'] = 0;
						$mo[0]['stu_state'] = 1;
						$stu->where(['stuid'=>$ob])->save($mo[0]);
						
						$wx .= "," . $trace->where(['stuid'=>$ob])->getField('wx');
						
						$trace->where(['stuid'=>$ob])->delete();
					}
				}
				
				$wx = split(',',$wx);
				$wx = array_unique($wx);
				$wx = array_filter($wx);
				
				foreach ($wx as $to){
					if($to){
						$this->text(7,"$to",'注意：' . $name . '学员，已归位!');
					}
				}
				$this->success("$name" . ' 学员归位成功！' ,'camp');
			}else{
				$stuid = $_POST['stuid'];
				$obj = $stu->where(['stuid'=>$stuid])->select();
					
				if($stuid && $obj){
			
					$obj[0]['position_now'] = $obj[0]['class_room'];
					$obj[0]['num'] = 0;
					$obj[0]['stu_state'] = 1;
					$stu->where(['stuid'=>$stuid])->save($obj[0]);
					
					$wx .= $trace->where(['stuid'=>$stuid])->getField('wx');
					
					$trace->where(['stuid'=>$stuid])->delete();
					$wx = split(',',$wx);
					$wx = array_unique($wx);
					$wx = array_filter($wx);
					
					foreach ($wx as $to){
						if($to){
							$this->text(7,"$to",'注意：' . $name . '学员，已归位!');
						}
					}
					
					$this->success( "$name" . ' 学员归位成功！' ,'camp');
				}else{
					$this->error( "$name" . ' 学员归位失败，请联系管理员！' ,'camp');
				}
			}
		}else{
			$this->error('学员归位失败，请选择学员！' ,'camp');
		}
		
	}
	
	//登出系统，清空课堂及轮询学员信息；
	public function logout(){
		
		$teacher = M('hw001.camp_class_teacher',null);
		$gid = $teacher->field('grade_id')->where("teacher_name='" . session('name') . "'")->find();
		if($gid){
				
			$class = M('hw001.class_student',null);
			$trace = M('hw001.student_trace_log',null);
			
			
			if($class->where(['grade_id'=>array('in',$gid)])->count()){
				$stuids = $class->where(['grade_id'=>array('in',$gid)])->getField('stuid',true);
				
				$class->where(['grade_id'=>array('in',$gid)])->delete();
				if($stuids){
					$trace->where(['stuid'=>array('in',$stuids)])->delete();
				}
				$room = M('hw001.camp_classroom',null);
				$room->where(['grade_id'=>array('in',$gid)])->save(['state'=>1]);
			}
		}
		
		$this->success('下课成功！' ,'index');
	}
	
}