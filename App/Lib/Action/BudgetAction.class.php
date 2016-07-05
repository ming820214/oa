<?php

class BudgetAction extends CommonAction {
	protected $config = array('app_type' => 'personal');

    public function tongzhi($school,$part=null){
        if($school=='集团'){
            if($part)$name=M('hw003.person_all',null)->where(array('part'=>$part,'position'=>'主管'))->getfield('name');
            $this->news(6,$name,'OA提醒','系统中有预算待审核。',wx_oauth(C('WWW').U('login/log_wx?urll=budget/check2')));
        }else{
            $name=M('hw003.person_all',null)->where(array('school'=>$school,'position'=>'校长'))->getfield('name');
            $this->news(6,$name,'OA提醒','系统中有预算待审核。',wx_oauth(C('WWW').U('login/log_wx?urll=budget/check1')));
        }
    }

    public function sort(){
        $ww['class']='school';
        $selt=M('hw003.money_sort',null)->where($ww)->select();
        $this->selt=$selt;
    }

	public function index(){
        if($_POST['add'])$this->add();
        if($_POST['change'])$this->change();
        if($_POST['delt'])$this->delt();
        $this->sort();
        $w['date']=session('date');

        if(session('schooll')=='集团'){
            $w['state']=array('BETWEEN','0,2');
            $w['name']=session('user_name');
        }else{
            $w['state']=array('BETWEEN','0,1');
            $w['school']=session('schooll');
        }
        $list=M('hw003.money_budget',null)->where($w)->order('id desc')->select();
        $this->list=$list;

        $w['date']=session('date');
        if(session('schooll')=='集团'){
            $w['state']=array('gt','2');
        }else{
            $w['state']=array('gt','1');
        }
        $list2=M('hw003.money_budget',null)->where($w)->order('state asc,id desc')->select();
        $w['state']=-1;
        $list3=M('hw003.money_budget',null)->where($w)->order('state asc,id desc')->select();
        if($list3)$list2=array_merge($list2,$list3);
        $this->list2=$list2;
        $this -> display();

    }

    public function add(){
            $m=M('hw003.money_budget',null);
            $m->create();
            $h=$_POST['d'];
            $m->d=round($h,2);
            $m->date=session('date');
            $m->time=date('y-m-d h:i:s');
            $m->school=session('schooll');
            $m->name=session('user_name');
            if(session('schooll')=='集团'){
                $m->state=2;//集团帐号跳过校区审核的步骤
                $this->tongzhi(session('schooll'),$_POST['bm']);
            }else{
                $this->tongzhi(session('schooll'));
            }
            if($m->add()){
                session('jsr',$_POST['jsr']);
                session('card',$_POST['card']);
                session('why',$_POST['why']);
                session('week',$_POST['week']);
                return true;
            }else{
                $this->success('有错误,录入失败');
            }
    }

    public function delt(){
        if($_POST['id'])
            foreach ($_POST['id'] as $key => $value) {
                $where['id']=$value;
                $m=M('hw003.money_budget',null)->where($where)->delete();
            }
        if($m)return true;
    }

    public function api($p){
      if(isset($_POST['p'])&&$_POST['p']!=''){
        $where['class']=$_POST['p'];
        $shuchu=M('hw003.money_sort',null)->where($where)->select();
        print(json_encode($shuchu));//将信息发送给浏览器
      }
    }

      //修改数据回调使用
    public function api_c($id){
      if(isset($_POST['id'])&&$_POST['id']!=''){
        $where['id']=$_POST['id'];
        $shuchu=M('hw003.money_budget',null)->where($where)->find();
        print(json_encode($shuchu));//将信息发送给浏览器
      }
    }

    public function check1(){
        if($_POST['change'] && $this->change()){
                $this->success('更新成功！');
        }elseif($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=2;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //通知审核部门
                $part=M('hw003.budget','money_')->where($w)->getfield('bm');
                $this->tongzhi('集团',$part);
                //审核记录
                R('Budget/record',array($value,'校区审核'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['lose']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=-1;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'校区拒绝'));
            }
            if($rr){
                $this->success('项目已标记为失败！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'校区退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $rr=M('hw003.budget','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $ww['class']='school';
            $selt=M('hw003.money_sort',null)->where($ww)->select();
            $this->selt=$selt;
            if(session('?date')){
                $date=session('date');
                $w['date']=$date;
            }else{
                $date=date('y-m');
                $w['date']=$date;
            }

            $w['state']=1;
            $w['school']=session('schooll');
            // if(session('schooll')=='集团')$w['bm']=session('bm');
            $list=M('hw003.money_budget',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $wl['school']=session('schooll');
            $wl['state']=array('gt','1');
            $wl['date']=session('date');
            // if(session('schooll')=='集团')$wl['bm']=session('bm');
            $list2=M('hw003.money_budget',null)->where($wl)->order('state asc,id desc')->select();
            $wl['state']=-1;
            $list3=M('hw003.money_budget',null)->where($wl)->order('state asc,id desc')->select();
            if($list3)$list2=array_merge($list2,$list3);
            $this->list2=$list2;

            $this->js_file='js/check';
            $this -> display('check');

        }
    }

    public function check2(){
        if($_POST['change'] && $this->change()){
                $this->success('更新成功！');
        }elseif($_POST['aax']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $m=M('hw003.money_budget',null)->where($w)->find();
                //审核记录
                R('Budget/record',array($value,'部门审核'));
                //过滤总裁审核条目
                if($m['d']*$m['e']>=3000){
                    $d['state']=3;//总裁审核状态
                    $this->news(6,'李文龙','OA提醒','系统中有预算待审核。',wx_oauth(C('WWW').U('login/log_wx?urll=budget/check3')));
                }else{
                    $d['state']=4;
                    $this->news(6,'张毅','OA提醒','系统中有预算待审核。',wx_oauth(C('WWW').U('login/log_wx?urll=budget/check4')));
                }
                $rr=M('hw003.budget','money_')->where($w)->save($d);
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['lose']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=-1;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'部门拒绝'));
            }
            if(!$rr)$this->error('选择要标记为失败的数据！');
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=0;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'部门退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $rr=M('hw003.budget','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $ww['class']='school';
            $selt=M('hw003.money_sort',null)->where($ww)->select();
            $this->selt=$selt;
            if(session('?date')){
                $date=session('date');
                $w['date']=$date;
            }else{
                $date=date('y-m');
                $w['date']=$date;
            }
            $w['state']=2;
            $w['bm']=session('bm');
            $list=M('hw003.money_budget',null)->where($w)->order('id desc')->select();
            $this->list=$list;

            $wl['state']=array('gt','2');
            $wl['date']=session('date');
            $wl['bm']=session('bm');
            $list2=M('hw003.money_budget',null)->where($wl)->order('state asc,id desc')->select();
            $wl['state']=-1;
            $list3=M('hw003.money_budget',null)->where($wl)->order('state asc,id desc')->select();
            if($list3)$list2=array_merge($list2,$list3);
            $this->list2=$list2;

            $this->js_file='js/check';
            $this -> display('check');

        }
    }


    public function check3(){//总裁审核处理
        if($_POST['change'] && $this->change()){
                $this->success('更新成功！');
        }elseif($_POST['aax']){
            foreach ($_POST['id'] as $key => $value){
                $w['id']=$value;
                $m=M('hw003.money_budget',null)->where($w)->find();
                $d['state']=4;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                $this->news(6,'张毅','OA提醒','系统中有预算待审核。',wx_oauth(C('WWW').U('login/log_wx?urll=budget/check4')));
                //审核记录
                R('Budget/record',array($value,'总裁审核'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['lose']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=-1;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'总裁拒绝'));
            }
            if($rr){
                $this->success('项目已标记为失败！');
    		}else{
    			$this->error('选择要退回的数据！');
    		}
    	}elseif($_POST['bt']){
    		foreach ($_POST['id '] as $key => $value) {
    			$w['id']=$value;
    			$d['state']=0;
    			$rr=M('hw003.budget','money_')->where($w)->save($d);
    			//审核记录
    			R('Budget/record',array($value,'总裁退回'));
    		}
    		if($rr){
    			$this->success('数据已退回！');
    		}else{
    			$this->error('选择要退回的数据！');
    		}
    	}elseif ($_POST['dl']) {
    		foreach ($_POST['id'] as $key => $value) {
    			$w['id']=$value;
    			$rr=M('hw003.budget','money_')->where($w)->delete();
    		}
    		if($rr)
    			$this->success('删除成功！');
    	}else{
			$ww['class']='school';
			$selt=M('hw003.money_sort',null)->where($ww)->select();
			$this->selt=$selt;
			if(session('?date')){
				$date=session('date');
				$w['date']=$date;
			}else{
				$date=date('y-m');
				$w['date']=$date;
			}
    		$w['state']=3;
			$list=M('hw003.money_budget',null)->where($w)->order('id desc')->select();
			$this->list=$list;
			$this->js_file='js/check';
			$this -> display('check');

    	}
    }

    public function check4(){//财务确认
        $this->sort();
        if($_POST['change'] && $this->change()){
                $this->success('更新成功！');
        }elseif($_POST['aax']){
            foreach ($_POST['id'] as $key => $value){
                $w['id']=$value;
                $m=M('hw003.money_budget',null)->where($w)->find();
                $d['state']=5;
                $d['time5']=date('Y-m-d h:i:s');
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'财务确认'));
            }
            if($rr){
                $this->success('审核完成！');
            }else{
                $this->success('选择要审核的条目！');
            }
        }elseif($_POST['lose']){
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $d['state']=-1;
                $rr=M('hw003.budget','money_')->where($w)->save($d);
                //审核记录
                R('Budget/record',array($value,'财务拒绝'));
            }
            if($rr){
                $this->success('项目已标记为失败！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif($_POST['bt']){
            foreach ($_POST['id'] as $v) {
                $w['id']=$v;
                $d['state']=0;
                $rr=M('hw003.money_budget',null)->where($w)->save($d);
                //审核记录
                R('Budget/record',array($v,'财务退回'));
            }
            if($rr){
                $this->success('数据已退回！');
            }else{
                $this->error('选择要退回的数据！');
            }
        }elseif ($_POST['dl']) {
            foreach ($_POST['id'] as $key => $value) {
                $w['id']=$value;
                $rr=M('hw003.budget','money_')->where($w)->delete();
            }
            if($rr)
                $this->success('删除成功！');
        }else{
            $w['date']=session('date');
            $w['state']=4;
            $list=M('hw003.money_budget',null)->where($w)->order('id desc')->select();
            $this->list=$list;
            $this->js_file='js/check';
            $this -> display('check');

        }
    }

    //数据导出
    public function all(){
        $this->sort();
        if($_POST['delt'])$this->delt();
        if($_POST['import'])$this->import();
        if($_POST['search']){
            if($_POST['state']!='全部')$w['state']=$this->_post('state');
            if($_POST['date'])$w['date']=$this->_post('date');
            if($_POST['school'])$w['school']=$this->_post('school');
            if($_POST['jsxq'])$w['jsxq']=$this->_post('jsxq');
            if($_POST['aa'])$w['aa']=$this->_post('aa');
            if($_POST['jsr'])$w['jsr']=$this->_post('jsr');
        }else{
            $w['state']=5;
            $w['date']=session('date');
        }

            $list=M('hw003.money_budget',null)->where($w)->order('state asc,id desc')->select();
            $this->list=$list;

        $this->display();
    }


    public function import(){

            if($_POST['state']!='全部')$w['state']=$this->_post('state');
            if($_POST['date'])$w['date']=$this->_post('date');
            if($_POST['school'])$w['school']=$this->_post('school');
            if($_POST['jsxq'])$w['jsxq']=$this->_post('jsxq');
            if($_POST['aa'])$w['aa']=$this->_post('aa');
            if($_POST['jsr'])$w['jsr']=$this->_post('jsr');
		$mm=M('hw003.budget','money_')->where($w)->order('id desc')->select();
        
		$output = "<HTML>";
		$output .= "<HEAD>";
		$output .= "<META http-equiv=Content-Type content=\"text/html; charset=utf-8\">";
		$output .= "</HEAD>";
		$output .= "<BODY>";
		$output .= "<TABLE BORDER=1>";
		$output .= "<tr><td>期次</td><td>状态</td><td>序号</td><td>申请校区</td><td>归属校区</td><td>审核部门</td><td>成本类型</td><td>二级科目</td><td>明细</td><td>单价（元）</td><td>数量</td><td>金额</td><td>期望审批日前</td><td>类型</td><td>预算周期</td><td>接收校区</td><td>接收人</td><td>卡号</td><td>财务</td><td>申请原因</td><td>备注</td><td>数据创建时间</td><td>数据通过时间</td></tr>";
			foreach ($mm as $m) {
				if($m['class']==1){
					$class='常规预算';
				}else{
					$class='临时预算';
				}
				switch ($m['state']) {
                    case '-1':
                        $state='审核失败';
                        break;
					case '0':
						$state='退回修改';
						break;
					case '1':
						$state='校区审核';
						break;
					case '2':
						$state='部门审核';
						break;
					case '3':
						$state='总裁审核';
						break;
					case '4':
						$state='财务审批';
						break;
					case '5':
						$state='审核通过';
						break;
				}
				$output .= "<tr><td>".$m['date']."</td><td>".$state."</td><td>".$m['id']."</td><td>".$m['school']."</td><td>".$m['gs']."</td><td>".$m['bm']."</td><td>".$m['aa']."</td><td>".$m['b']."</td><td>".$m['c']."</td><td>".$m['d']."</td><td>".$m['e']."</td><td>".$m['d']*$m['e']."</td><td>".$m['g']."</td><td>".$class."</td><td>".$m['week']."</td><td>".$m['jsxq']."</td><td>".$m['jsr']."</td><td>".$m['card']."</td><td>".$m['name']."</td><td>".$m['why']."</td><td>".$m['other']."</td><td>".$m['timestamp']."</td><td>".$m['time5']."</td></tr>";
			}
		$output .= "</TABLE>";
		$output .= "</BODY>";
		$output .= "</HTML>";
		$filename='财务系统预算明细导出表'.date('Y-m-d');
		header("Content-type:application/msexcel");
		header("Content-disposition: attachment; filename=$filename.xls");
		header("Cache-control: private");
		header("Pragma: private");
		print($output);
    }


    //修改
    public function change(){
        if($_POST['id']){
            $m=M('hw003.money_budget',null);
            $m->create();
            if($_POST['x'])$m->state=1;
            if($_POST['x'] && session('schooll')=='集团')$m->state=2;
            if($m->save()){
                session('jsr',$_POST['jsr']);
                session('card',$_POST['card']);
                session('why',$_POST['why']);
                session('week',$_POST['week']);
                return true;
            }
        }
    }


    //审核记录
    public function record($id,$info){
    	$w['id']=$id;
    	$inf=M('hw003.budget','money_')->where($w)->find();
    	$d['record']=$inf['record'].'<'.$info.date('Y-m-d H:i:s').session('user_name').'>';
    	M('hw003.budget','money_')->where($w)->save($d);
    }

}
?>