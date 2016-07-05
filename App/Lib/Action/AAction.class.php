<?php

class AAction extends CommonAction {
    protected $config = array('app_type' => 'personal');

    public function index(){
        $this -> display();
    }

/**
 排课系统数据管理
*/
    public function p(){

        if (!empty($_POST['keyword'])) {
            $w['school|user|position'] = array('like', "%" . $_POST['keyword'] . "%");
        }

        $s=M('hw001.school',null)->select();
        $this->school=$s;
        $User =M('hw001.user',null); // 实例化User对象
        import('ORG.Util.Page');// 导入分页类
        $count      = $User->where($w)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($w)->order('school asc,position desc,user asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('item',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }

    public function delt(){
        if($_GET['id'])
            $w['id']=$_GET['id'];
        $m=M('hw001.user',null)->where($w)->delete();
        $this->success('删除成功！');
    }
    public function reset(){
        if($_GET['id'])
            $w['id']=$_GET['id'];
            $d['pw']='444bcb3a3fcf8389296c49467f27e1d6';
        $m=M('hw001.user',null)->where($w)->save($d);
        $this->success('密码已重置为ok！');
    }
    public function add(){
      if($_POST){
        $m=M('hw001.user',null);
        $m->create();
        $m->pw='444bcb3a3fcf8389296c49467f27e1d6';
        if($m->add())
        $this->success('添加成功！');
      }
    }

// 微信帐号信息数据
    public function user(){
        if (!empty($_POST['keyword'])) {
            $w['school|name|userid|position'] = array('like', "%" . $_POST['keyword'] . "%");
        }

        if($_GET['id']){
            $w['id']=$_GET['id'];
            $m=M('hw003.person_all',null)->where($w)->delete();
            $this->success('删除成功！');
        }elseif($_POST['userid']){
            $m=M('hw003.person_all',null);
            $m->create();
            if($m->add())$this->success('添加成功！');
        }else{
            $s=M('hw001.school',null)->select();
            $this->school=$s;

            $User =M('hw003.person_all',null); // 实例化User对象
            import('ORG.Util.Page');// 导入分页类
            $count      = $User->where($w)->count();// 查询满足要求的总记录数
            $Page       = new Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数
            $show       = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = $User->where($w)->order('school asc,position desc,name asc')->limit($Page->firstRow.','.$Page->listRows)->select();
            $this->assign('item',$list);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
            $this->display(); // 输出模板

        }
    }

    //排课系统删除记录
    public function precord(){

        $w['school']=session('schooll');
        if($_POST['school'])$w['school']=$_POST['school'];
        $m=M('hw001.school',null)->where($w)->getField('record');
        $m2=explode('#',$m,300);
        foreach ($m2 as $v2) {
            $data[]=explode(',',$v2);
        }
        foreach ($data as $k => $val) {
            if($i<300){
                $dat[$k]=$val;
                if($val[4]){
                    $dat[$k]['name']=M('hw001.school_grade',null)->field('name')->find($val[4]);
                }else{
                    $dat[$k]['name']=M('hw001.student',null)->field('name')->find($val[3]);
                }
                //查询过滤
                if($_POST['type']&&$dat[$k][0]==$_POST['type'])$a[]=$dat[$k];
                if($_POST['student']&&$dat[$k]['name']['name']==$_POST['student'])$b[]=$dat[$k];
                if($_POST['teacher']&&$dat[$k][6]==$_POST['teacher'])$c[]=$dat[$k];
                if($_POST['cs']&&$dat[$k][22]==$_POST['cs'])$d[]=$dat[$k];
                if($_POST['date']&&$dat[$k][12]==$_POST['date'])$e[]=$dat[$k];
            }
            $i++;
        }

        //查询过滤
        if($a)$dat=self::array_qc($dat,$a);
        if($b)$dat=self::array_qc($dat,$b);
        if($c)$dat=self::array_qc($dat,$c);
        if($d)$dat=self::array_qc($dat,$d);
        if($e)$dat=self::array_qc($dat,$e);


        $this->data=$dat;
        $this -> display();
    }


//==================================
    //数组取交集
    function array_qc($a,$b){
        foreach ($a as $v1) {
            foreach ($b as $v2) {
                if($v1==$v2)$data[]=$v1;
            }
        }
        return $data;
    }

/**
记录系统操作
*/
    public function record($part){
        if($part==1){//人事系统操作
            $m=M('hw003.record',null)->where(array('part'=>'员工管理'))->getField('record');
            $info=explode('|',$m,300);
            foreach ($info as $k=>$v) {
                $list[$k]=explode(',',$v);
                if($_POST['type']&&$list[$k][0]!=$_POST['type'])unset($list[$k]);
                if($_POST['name']&&$list[$k][2]!=$_POST['name'])unset($list[$k]);
                if($_POST['date1']&&$_POST['date2']){
                    $t1=$_POST['date1'].' 00:00:00';
                    $t2=$_POST['date2'].' 23:00:00';
                    if(!($list[$k][4]>=$t1&&$list[$k][4]<=$t2))unset($list[$k]);
                }
            }
            $this->list=$list;
        }
        $this->display();
    }
}
?>