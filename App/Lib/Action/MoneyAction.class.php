<?php

class MoneyAction extends CommonAction {
	protected $config = array('app_type' => 'personal');
    public function date(){
    	if($_POST['date']){
    		session('date',$_POST['date']);
	    	$this->success('所在期次设置成功！');
    	}
    	// var_dump(session('date'));
    }

	public function home(){
		if(!session('?date')){
			$date=date('Y-m');
			session('date',"$date");
		}
		$this->display();
	}

	//新增
	public function add(){
		if($_POST){
			$m=M('hw003.money_add',null);
            $m->kk=round($_POST['d'],2);
			$m->create();
			$m->r=session('user_name');
			$m->b=session('schooll');
			if($_POST['mm']=='')$m->mm=1;
			if(session('schooll')=='集团')$m->state=2;
			if($m->add()){
				$this->redirect('index');
			}else{
				$this->success('有错误,录入失败');
			}
		}
	}

	//修改
	public function change(){
		if($_POST['id']){
			$m=M('hw003.money_add',null);
            $m->kk=round($_POST['d'],2);
			$m->create();
			if($_POST['mm']=='')$m->mm=1;
			$m->r=session('user_name');
			if($_POST['x'])$m->state=1;//退回修改的处理
			$m->save();
		}
	}
	//删除
	public function delt(){
		if($_POST['id'])
			foreach ($_POST['id'] as $key => $value) {
				$r=M('hw003.money_add',null)->where($where)->delete($value);
			}
			if($r)return true;
	}

	public function index(){

		if($_POST['change'])$this->change();//修改按钮
    	$this->sort();//导出校区
		$w['b']=session('schooll');

		$w['date']=session('date');
		$w['state']=array('lt','2');
		if(session('schooll')=='集团'){
			$w['r']=session('user_name');
			$w['state']=array('lt','3');
		}
		$list=M('hw003.money_add',null)->where($w)->order('id desc')->select();
		$this->list=$list;

		$w['state']=array('egt','2');
		if(session('schooll')=='集团')$w['state']=array('egt','3');
		$list2=M('hw003.money_add',null)->where($w)->order('id desc')->select();
		$this->list2=$list2;
		$this -> display();
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
        $shuchu=M('hw003.money_add',null)->where($where)->find();
        print(json_encode($shuchu));//将信息发送给浏览器
      }	
    }

    public function check1(){
			$w['date']=session('date');
			$w['b']=session('schooll');
			if(session('schooll')=='集团')$w['r']=session('user_name');
			$this->sort();
		if($_POST['change'])$this->change();//修改按钮

    	if($_POST['aax']){
    		foreach ($_POST['id'] as $key => $value) {
    			$w['id']=$value;
    			$d['state']=2;
    			$rr=M('hw003.add','money_')->where($w)->save($d);
    		}
    		if($rr){
    			$this->success('审核完成！');
    		}else{
    			$this->success('选择要审核的条目！');
    		}
    	}elseif($_POST['bt']){
    		foreach ($_POST['id'] as $key => $value) {
    			$w['id']=$value;
    			$d['state']=0;
    			$rr=M('hw003.add','money_')->where($w)->save($d);
    		}
    		if($rr){
    			$this->success('数据已退回！');
    		}else{
    			$this->error('选择要退回的数据！');
    		}
    	}elseif ($_POST['dl']){
    			if($this->delt()){
					$this->redirect('check1');
    			}else{
    				$this->success('删除失败……');
    			}
    	}else{

    		$w['state']=1;//
			$list=M('hw003.money_add',null)->where($w)->order('id desc')->select();
			$this->list1=$list;

    		$w['state']=array('gt','1');//
			$list=M('hw003.money_add',null)->where($w)->order('id desc')->select();
			$this->list2=$list;

			$this->js_file='js/check';
			$this -> display('check');

    	}
    }

    public function check2(){
			$w['date']=session('date');
			$this->sort();
		if($_POST['change'])$this->change();//修改按钮
    	if($_POST['aax']){
    		foreach ($_POST['id'] as $key => $value) {
    			$w['id']=$value;
    			$d['state']=3;
    			$rr=M('hw003.add','money_')->where($w)->save($d);
    		}
    		if($rr){
    			$this->success('审核完成！');
    		}else{
    			$this->success('选择要审核的条目！');
    		}
    	}elseif($_POST['bt']){
    		foreach ($_POST['id'] as $key => $value) {
    			$w['id']=$value;
    			$d['state']=0;
    			$rr=M('hw003.add','money_')->where($w)->save($d);
    		}
    		if($rr){
    			$this->success('数据已退回！');
    		}else{
    			$this->error('选择要退回的数据！');
    		}
    	}elseif ($_POST['dl']) {
			if($this->delt()){
				$this->redirect('check2');
			}else{
				$this->success('删除失败……');
			}
    	}else{
    		$w['state']=2;//
			$list=M('hw003.money_add',null)->where($w)->order('id desc')->select();
			$this->list1=$list;

    		$w['state']=array('gt','2');//
			$list=M('hw003.money_add',null)->where($w)->order('id desc')->select();
			$this->list2=$list;

			$this->js_file='js/check';
			$this -> display('check');

    	}
    }

    public function all(){
    	if($_POST['import'])$this->import();//数据导出
    	if($_POST['change'])$this->change();

    	$this->sort();//导出校区
    	$w['date']=session('date');
    	$w['state']=3;
    	if($_POST['search']){
	    	if($_POST['state']!='')$w['state']=$this->_POST('state');//状态
	    	if($_POST['state']=='全部')unset($w['state']);//
	    	if($_POST['date']!='')$w['date']=$this->_POST('date');//花销
	    	if($_POST['b']!='')$w['b']=$this->_POST('b');//花销
	    	if($_POST['c']!='')$w['c']=$this->_POST('c');//归属
	    	if($_POST['g']!='')$w['g']=$this->_POST('g');//成本类型
	    	if($_POST['r']!='')$w['r']=$this->_POST('r');//校区财务
    	}
		$list=M('hw003.money_add',null)->where($w)->order('state asc,id desc')->select();
		$this->list=$list;
		$this->display();
    	if($_POST['delt'])$this->delt();//删除数据
    }

    //输出校区
    public function sort(){
		$ww['class']='school';
		$selt=M('hw003.money_sort',null)->where($ww)->select(); 
		$this->selt=$selt;
    }

    public function import(){

    	if($_POST['date']!='')$w['date']=$_POST['date'];
    	if($_POST['b']!='')$w['b']=$_POST['b'];
    	if($_POST['c']!='')$w['c']=$_POST['c'];
    	if($_POST['g'])$w['g']=$_POST['g'];
    	if($_POST['o']!='')$w['o']=$_POST['o'];
    	if($_POST['state']!='全部')$w['state']=(int)$_POST['state'];

		$mm=M('hw003.add','money_')->where($w)->order('id desc')->select();

		$output = "<HTML>";
		$output .= "<HEAD>";
		$output .= "<META http-equiv=Content-Type content=\"text/html; charset=utf-8\">";
		$output .= "</HEAD>";
		$output .= "<BODY>";
		$output .= "<TABLE BORDER=1>";
		$output .= "<tr><td>序号</td><td>状态</td><td>凭证号</td><td>期次</td><td>花销</td><td>归属</td><td>报销日期</td><td>发生日期</td><td>成本类型</td><td>二级科目</td><td>支出项目</td><td>明细</td><td>单价</td><td>数量</td><td>合计</td><td>经手人</td><td>报销人</td><td>审批人</td><td>所属部门</td><td>校区财务</td><td>发票数量</td><td>摊销起点</td><td>摊销期次</td><td>备注</td><td>数据创建时间</td></tr>";
			foreach ($mm as $m) {
				if($m['mm']>1){
					$f=$m['f'];
					$l=$m['mm'];
				}else{
					$f='';
					$l=1;
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
						$state='集团审核';
						break;
					case '3':
						$state='审核通过';
						break;
					default:
						# code...
						break;
				}
				$output .= "<tr><td>".$m['id']."</td><td>".$state."</td><td>".$m['aa']."</td><td>".$m['date']."</td><td>".$m['b']."</td><td>".$m['c']."</td><td>".$m['d']."</td><td>".$m['e']."</td><td>".$m['g']."</td><td>".$m['h']."</td><td>".$m['i']."</td><td>".$m['j']."</td><td>".$m['kk']."</td><td>".$m['l']."</td><td>".$m['kk']*$m['l']."</td><td>".$m['n']."</td><td>".$m['o']."</td><td>".$m['p']."</td><td>".$m['q']."</td><td>".$m['r']."</td><td>".$m['t']."</td><td>".$f."</td><td>".$l."</td><td>".$m['other']."</td><td>".$m['timestamp']."</td></tr>";
			}
		$output .= "</TABLE>";
		$output .= "</BODY>";
		$output .= "</HTML>";

		$filename='财务系统花销明细导出表'.date('Y-m-d');
		header("Content-type:application/msexcel");
		header("Content-disposition: attachment; filename=$filename.xls");
		header("Cache-control: private");
		header("Pragma: private");
		print($output);
    }

    public function cc(){
    	$w['r']='财务助理';
    	$w['b']='集团';
    	$w['state']=1;
    	$d['state']=2;
    	M('hw003.money_add',null)->where($w)->save($d); 
    }

	
	
}
?>