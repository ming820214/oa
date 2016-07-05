<?php
class WechatAction extends Action {
	protected $config = array('app_type' => 'public');

	public function index(){		
		/* 加载微信SDK */
		import ( "@.ORG.Util.ThinkWechat" );
		$weixin = new ThinkWechat ();
		
		/* 获取请求信息 */
		$data = $weixin->request ();

		/* 获取回复信息 */
		list ( $content, $type ) = $this->reply ( $data );
		
		// 接收到的信息入不同的库
		$this->weichatlog ( $data );

		/* 响应当前请求 */
		$weixin->response ( $content, $type );
	}
	
	 /**
	 * 定制响应信息
	 * @param array $data 接收的数据
	 * @return array; 响应的数据
	 */
	private function reply($data) {	
		// 消息类型
		switch ($data ['MsgType']) {
			case 'text': // 类型是文本的
				switch ($data ['Content']) {
					case '解除绑定' : // 接触绑定
						$reply = $this->getUnOauth ( $data ['FromUserName'] );
						break;
					case '1' : // 任务提醒
						$reply = $this->getTaskEvent ( 'ites_set', $data ['FromUserName'] );
						break;
					case '2' : // 我申请的任务
						$reply = $this->getTaskEvent ( 'apply_task', $data ['FromUserName'] );
						break;
					case '3' : // 邀请我的任务
						$reply = $this->getTaskEvent ( 'invite_task', $data ['FromUserName'] );
						break;
					case '4' : // 待评价的任务
						$reply = $this->getTaskEvent ( 'comment_task', $data ['FromUserName'] );
						break;
					case '5' : // 适合我的任务
						$reply = $this->getTaskEvent ( 'fit_task', $data ['FromUserName'] );
						break;
					case '6' : // 任务检索
						$reply = array ( "任务检索，<a href='".C("SITE_URL")."/wechat/search/?openid={$data ['FromUserName']}'>请点击这里</a>", 'text' );
						break;
					case '7' : // 绑定帐号
						$reply = array ( "您还没有绑定帐号。<a href='".C("SITE_URL")."/wechat/oauth/?openid={$data ['FromUserName']}'>点击立即进行绑定</a>", 'text' );
						break;
					default :
						
						/* 加载分词SDK */
						//import ( "@.ORG.Util.SplitWord" );
						//$word = new SplitWord ();
						//$reply = $word->getWord($data ['Content']);
						
						if (strpos ( $data ['Content'], '任务' ) !== false) {
							$html = $this->getTaskHtml();
							$reply = array ( $html, 'text' );
						} else {
							$reply = array ( '欢迎使用小微OA服务号', 'text'  );
						}
						break;
				}

				break;
			case 'event' : // 类型是事件的			              
				// 事件类型
				switch ($data ['Event']) {					
					case 'subscribe': // 刚刚关注的
						$reply = $this->getSubscribe ( $data ['FromUserName'] );
						break;
					case 'CLICK': // 点击的事件
						$reply = $this->getTaskEvent ( $data ['EventKey'], $data ['FromUserName'] );
						break;
					default :
						$reply = array ( '没有相关事件',	'text' );	
						break;
				}
				
				break;
			default :
				$reply = array ( '没有相关消息类型',	'text' );	
				break;
		}
		return $reply;
	}
		
	 /**
	 * 信息入库
	 * @param array $data 接收的数据
	 */
	private function weichatlog($data) {
		if ($data ['MsgType'] == 'event') {
			M ( 'wechat_event' )->data ( $data )->add ();
		} else {
			M ( 'wechat_info' )->data ( $data )->add ();
		}
	}
	
	 /**
	 * 生成菜单
	 */	

	public function setmenu(){
		
		$sub =array();
		$data = array();
		$subs = array();

		$app_id=C("WECHAT_APPID");
		$redirect_uri=U('home/index');
		$site_url=C("SITE_URL");

	
		$subs3[] = array('type'=>'view','name'=>'绑定','url'=>$site_url.U('wechat/oauth'));
		$subs3[]= array('type'=>'click','name'=>'解除绑定','key'=>'unauth');
		$subs3[]= array('type'=>'click','name'=>'推送设置','key'=>'ites_set');

		$oauth_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$app_id&redirect_uri={$site_url}{$redirect_uri}&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
			
		$sub1 = array('type'=>'view','name'=>'小微OA','url'=>$oauth_url);
		$sub2 = array('type'=>'click','name'=>'签到','key'=>'sign_up');
		$sub3 =  array('name'=>'帮助','sub_button'=>$subs3);

		$data['button'][] = $sub1;	
		$data['button'][] = $sub2;
		$data['button'][] = $sub3;

		$data = jsencode($data);
 
		/* 加载微信SDK */
		import ( "@.ORG.Util.ThinkWechat" );
		$weixin = new ThinkWechat ();
		
		echo $weixin->setMenu($data);				
	}

	 /**
	 * 关注成功
	 * @param string $openid 用户openid
	 * @return array; 响应的数据
	 */
	private function getSubscribe($openid = ''){
		$re = "";
		$re .= "您好，欢迎关注小微企业OA微信公众号。为了让您能方便快捷的使用小微企业OA";
		$re .= "请先确认您在小微OA系统中有帐号，并进行微信号码绑定，绑定后后可以直接进入小微OA";
		$re .= "<a href='".C("SITE_URL")."/wechat/oauth/?openid={$openid}'>点击立即进行绑定</a>";
		return array ( $re, 'text' );
	}
	
	/**
	 * 任务事件
	 * @param string $taskevent 事件
	 * @param string $openid   	用户openid
	 * @return array; 响应的数据
	 */
	private function getTaskEvent($taskevent = '', $openid = '') {
		$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
		if ($openid && $userid > 0) {
			switch ($taskevent) {
				case 'sign_up' : // 签到
					$reply = array ( "签到成功", 'text' );
					break;
				case 'unauth' : // 解除绑定
					$reply=$this->getUnOauth($openid);
					break;
				case 'apply_task' : // 我申请的任务
					$reply = array ( "查看我申请的任务信息，<a href='".C("SITE_URL")."/wechat/tasklist/?action=apply&openid={$openid}'>请点击这里</a>", 'text' );
					break;
				case 'invite_task' : // 邀请我的任务
					$reply = array ( "查看邀请我的任务信息，<a href='".C("SITE_URL")."/wechat/tasklist/?action=invite&openid={$openid}'>请点击这里</a>", 'text' );
					break;
				case 'comment_task' : // 待评价的任务
					$reply = array ( "查看待评价的任务信息，<a href='".C("SITE_URL")."/wechat/tasklist/?action=comment&openid={$openid}'>请点击这里</a>", 'text' );
					break;
				case 'fit_task' : // 适合我的任务
					$reply = array ( "查看适合我的任务信息，<a href='".C("SITE_URL")."/wechat/tasklist/?action=fit&openid={$openid}'>请点击这里</a>", 'text' );
					break;
				case 'ites_intro' : // 功能介绍
					$re = "";
					$re .= "您好，欢迎关注小微OA微信公众服务号。您可以回复以下数字了解您想了解的信息：\n";
					$re .= "1: 任务提醒\n";
					$re .= "2: 查看我申请的任务\n";
					$re .= "3: 查看邀请我的任务\n";
					$re .= "4: 查看待评价的任务\n";
					$re .= "5: 快速浏览适合我的任务\n";
					$re .= "6: 任务检索\n";
					$re .= "7: 绑定帐号\n";
					$reply = array ( $re, 'text' );
					break;
				case 'ites_verify' : // 工程师联盟认证
					$re = "";
					$re .= "您好，欢迎关注XXXXX微信公众号。您可以申请加入工程师联盟成为联盟成员。成为联盟成员后您可以享受以下特权：\n";
					$re .= "• 任务提醒\n";
					$re .= "• 查看我申请的任务\n";
					$re .= "• 查看邀请我的任务\n";
					$re .= "• 查看待评价的任务\n";
					$re .= "• 快速浏览适合我的任务\n";
					$re .= "• 任务检索\n";
					$re .= "加入工程师联盟您需要准备以下材料内容，我们会尽快与您联系。";
					$reply = array ( $re, 'text' );					
					break;
				case 'ites_set' : // 信息推送设置
					$reply = array ( "信息推送设置，<a href='".C("SITE_URL")."/wechat/userset/?openid={$openid}'>请点击这里</a>", 'text' );
					break;
				default :
					$reply = array ( '没有相关指令', 'text' );
					break;
			}
		} else {
			$reply = array ( "您还没有绑定帐号。<a href='".C("SITE_URL")."/wechat/oauth/?openid={$openid}'>点击立即进行绑定</a>", 'text' );
		}
		return $reply;
	}
			
	// 字段：jointype 1，申请，2邀请
	// 字段：status 任务状态 0申请中1接受2忽略3完成
	
	// 申请：
	// 全部= 申请中+接受+忽略
	// 等待回复 = 申请中+忽略
	// 申请通过 = 接受
	// 邀请 ：
	// 全部 = 申请中+接受+忽略
	// 已同意 = 接受
	// 尚未操作 = 申请中
		
	/**
	 * 跳出页面-任务列表
	 */

	public function tasklist() {
		$openid = I ( 'get.openid' );
		$action = I ( 'get.action' );
		$tab = I ( 'get.tab', 'all' );
		if (empty ( $action ))	$this->message ( '错误，没有找到相关信息' );
					
		$this->assign ( 'openid', $openid );
		$this->assign ( 'action', $action );
		$this->assign ( 'tab', $tab );
				
		$map = array (); // 查询条件
		
		switch ($action) { // 判断哪个类型
			case 'apply' ://申请				
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
				$map ['userid'] = $userid;
				$map ['jointype'] = 1;				
				switch ($tab) {
					case 'all' : // 全部
						$map ['status'] = array('neq',3);
						$taskcount = M ( 'Task_apply' )->where ( $map )->count ();
						$this->meta_title = '全部-我申请的任务';
						break;
					case 'wait' : // 等待
						$map ['status'] = array(array('eq',0),array('eq',2), 'OR') ;
						$taskcount = M ( 'Task_apply' )->where ( $map )->count ();
						$this->meta_title = '等待回复-我申请的任务';
						break;
					case 'pass' : // 通过
						$map ['status'] = 1;
						$taskcount = M ( 'Task_apply' )->where ( $map )->count ();
						$this->meta_title = '申请通过-我申请的任务';
						break;
				}
				break;
			case 'invite' :// 邀请			
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
				$map ['userid'] = $userid;
				$map ['jointype'] = 2; 				
				switch ($tab) {
					case 'all' : // 全部
						$map ['status'] = array('neq',3);
						$taskcount = M ( 'Task_apply' )->where ( $map )->count ();
						$this->meta_title = '全部-邀请我的任务';
						break;
					case 'wait' : // 等待
						$map ['status'] = 0;
						$taskcount = M ( 'Task_apply' )->where ( $map )->count ();
						$this->meta_title = '等待回复-邀请我的任务';
						break;
					case 'pass' : // 已同意
						$map ['status'] = 1;
						$taskcount = M ( 'Task_apply' )->where ( $map )->count ();
						$this->meta_title = '已同意-邀请我的任务';
						break;
				}
				break;
			case 'comment' ://待评价		
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
				$map ['userid'] = $userid;
				$map ['usercontent'] = array('eq','');
				
				$taskcount = M ( 'Comment' )->where ( $map )->count ();
				$this->meta_title = '待评价的任务';
				break;
			case 'fit' ://适合我的任务
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
				$user = M('Member')->field('itemid,areaid,skill,ccies')->where("itemid = {$userid}")->find();//用户资料
				$taskcount = $this->getRecomTaskCount($user);
				$this->meta_title = '适合我的任务';
				break;
			case 'search' ://任务检索
				$keyword = I ( 'get.keyword' );
				$map['title']  = array('like', '%'.$keyword.'%');
				$map['content']  = array('like','%'.$keyword.'%');
				$map['_logic'] = 'or';
				$taskcount = M ( 'Task' )->where ( $map )->count ();
				$this->meta_title = '任务检索';
				break;		
		}
		$totalPages = ceil ( $taskcount / 3 ); // 总页数
		$this->assign ( 'total', $totalPages );
		$this->assign ( 'taskcount', $taskcount );
		$this->display ('tasklist');
	}
	
	/**
	 * 跳出页面-瀑布流的获取数据
	 */
	public function gettask(){
		$action = I ( 'get.action' );
		$openid = I ( 'get.openid' );
		$tab = I ( 'get.tab' );
		$taskcount = I ( 'get.taskcount' , 0 );
		$taskrow = I ( 'get.taskrow' );
		$nowPage = I ( 'get.p', 1 );
		if (empty ( $action ) || empty ( $tab )) $this->ajaxReturn ( - 1 );
		if (empty ( $taskrow ))	$taskrow = 3;
		if (empty ( $taskcount )) $this->ajaxReturn ( 0 );
		
		$areas = getArea(false);
		$this->assign ( 'areas', $areas );
			
		// 算分页
		$totalPages = ceil ( $taskcount / $taskrow ); // 总页数
		if ($nowPage < 1) {
			$nowPage = 1;
		} elseif (! empty ( $totalPages ) && $nowPage > $totalPages) {
			$nowPage = $totalPages;
		}
		$firstRow = $taskrow * ($nowPage - 1);
		
		$map = array (); // 查询条件

		switch ($action) { // 判断哪个类型
			case 'apply' : // 申请				
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->ajaxReturn ( -2 );
				$map ['userid'] = $userid;
				$map ['jointype'] = 1;
				switch ($tab) {
					case 'all' : // 全部
						$map ['status'] = array('neq',3);
						$tasklist = D ( 'Task_apply' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
						break;
					case 'wait' : // 等待
						$map ['status'] = array(array('eq',0),array('eq',2), 'OR') ;
						$tasklist = D ( 'Task_apply' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
						break;
					case 'pass' : // 通过
						$map ['status'] = 1;
						$tasklist = D ( 'Task_apply' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
						break;
				}
				break;
			case 'invite' : // 邀请
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->ajaxReturn ( -2 );
				$map ['userid'] = $userid;
				$map ['jointype'] = 2;
				switch ($tab) {
					case 'all' : // 全部
						$map ['status'] = array('neq',3);
						$tasklist = D ( 'Task_apply' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
						break;
					case 'wait' : // 等待
						$map ['status'] = 0;
						$tasklist = D ( 'Task_apply' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
						break;
					case 'pass' : // 已同意
						$map ['status'] = 1;
						$tasklist = D ( 'Task_apply' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
						break;
				}
				break;
			case 'comment' ://待评价
				$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
				if ($userid < 0) $this->ajaxReturn ( -2 );
				$map ['userid'] = $userid;
				$map ['usercontent'] = array (	'eq', '' );
				$tasklist = D ( 'Comment' )->relation ( true )->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
				break;
			case 'fit' : // 适合我的任务
				$userid = $this->getCookieUserId ( $openid ); // 绑定的用户ID
				if ($userid < 0) $this->ajaxReturn ( -2 );
				$user = M('Member')->field('itemid,areaid,skill,ccies')->where("itemid = {$userid}")->find();//用户资料
				$tasklist = $this->getRecomTask ( $user, $firstRow, $taskrow );
				break;
			case 'search' : // 任务检索
				$ot = I ( 'get.ot' );//实施时间排序
				$om = I ( 'get.om' );//报酬排序
				$keyword = I ( 'get.keyword' );
				$map['title']  = array('like', '%'.$keyword.'%');
				$map['content']  = array('like','%'.$keyword.'%');
				$map['_logic'] = 'or';
				$order .= $ot ? 'starttime DESC' : 'starttime ASC' ;	
				$order .= $om ? ',reward DESC' : ',reward ASC' ;
				$tlist = D ( 'Task' )->where ( $map )->order ( $order )->limit ( $firstRow . ',' . $taskrow )->select ();
				
				$userid = $this->getCookieUserId ( $openid ); // 绑定的用户ID
				
				$applyModel = D('Task_apply');
				foreach ($tlist as $k=>$v){
					$tasklist[$k]['taskid'] = $v['itemid'];
					$tasklist[$k]['tktitle'] = $v['title'];
					$tasklist[$k]['tkareaid'] = $v['areaid'];
					$tasklist[$k]['tkstarttime'] = $v['starttime'];
					$tasklist[$k]['tkendtime'] = $v['endtime'];
					$tasklist[$k]['tkclosetime'] = $v['closetime'];
					$tasklist[$k]['tkreward'] = $v['reward'];
					if ($userid > 0) {
						$apply = $applyModel->relation ( true )->where("userid = {$userid} AND taskid = {$v['itemid']}")->find();
						if ($apply) {
							$tasklist[$k]['status'] = $apply['status'];
							$tasklist[$k]['company'] = $apply['company'];
						}
					}
				}
				break;
		}
		$this->assign ( 'openid', $openid );
		$this->assign ( 'action', $action );
		$this->assign ( 'tasklist', $tasklist );
		if ($action == 'fit') {
			$this->display ( 'taskli_fit' );
		} else {
			$this->display ( 'taskli' );
		}		
	}
	
	/**
	 * 跳出页面-任务详情
	 */
	public function detail() {
		$openid = I ( 'get.openid' );
		$action = I ( 'get.action' );
		$itemid = I ( 'get.id', 0 );
		if (empty ( $action ) || empty ( $itemid ))	$this->message ( '错误，没有找到相关信息' );
			
		
		$userid = $this->getCookieUserId ( $openid );// 绑定的用户ID
	
		 //根据任务id获取任务信息
		$data = D('Task')->relation(true)->where(" itemid= {$itemid} ")->find();
		$this->assign ( 'data', $data );

		 //分类的数组s
		$areas = getArea(false);
		$skills = getMemberSkill(false);
		$this->assign ( 'areas', $areas );
		$this->assign ( 'skills', $skills );
		
		//获取技术要求
		$techList =  explode(',',$data['skill']);
		$this->assign ( 'techList', $techList );
		//附件
		$fileList = D('Task_attach')->where(" taskid= {$data['itemid']} AND status = 3")->select();
		$this->assign ( 'fileList', $fileList );
			
			// 用户对任务的状态
		if ($userid > 0) {
			$taskstatus = M('Task_apply')->where(" taskid = {$data['itemid']} AND userid = {$userid} AND companyid = {$data['userid']}")->find();
			$this->assign ( 'taskstatus', $taskstatus );
		}

		
		$this->meta_title = $data['title'];		
		$this->assign ( 'openid', $openid );
		$this->assign ( 'action', $action );
		$this->display('detail');
	}
	
	
	/**
	 * 跳出页面-同意企业邀请
	 */
	public function setinvite() {	
		$openid = I ( 'get.openid' );
		$taskid = I ( 'get.id', 0 );
		$itemid =  I ( 'get.taid', 0 );
		$companyid = I ( 'get.cid', 0 );
		
		if (empty ( $itemid ) || empty ( $companyid ) || empty ( $taskid ))	$this->message ( '错误，没有找到相关信息' );
			
		// 绑定的用户ID
		$userid = $this->getCookieUserId ( $openid );
		if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
		
		//用户对任务的状态
		$taskstatus = M('Task_apply')->where("itemid = {$itemid}")->find();
		
		if (empty ( $taskstatus )) $this->message ( '错误，没有找到相关信息' );
		if ( $taskstatus ['status'] != '0' ) $this->message ( '错误，请勿重复操作' );
		
		//安全判断，满足下面
		if ($taskstatus ['taskid'] == $taskid && $taskstatus ['userid'] == $userid && $taskstatus ['companyid'] == $companyid) {
			$taskstatus ['status'] = 1;//状态改成接受
			$re = D ( 'Task_apply' )->update ( $taskstatus );//更新
			if ($re) {
				//建立评价关系
				$comment = D ( 'Comment' )->addComment ( $taskstatus ['itemid'], $taskstatus ['taskid'], $taskstatus ['userid'], $taskstatus ['companyid'] );
				$this->message ( '操作成功，您已经同意企业邀请。' );
			} else {
				$this->message ( '操作失败。' );
			}
		} else {
			$this->message ( '错误，您没有权限' );
		}
	}

	/**
	 * 跳出页面-评价企业
	 */
	public function comment() {
				
		if (IS_POST) {
			$itemid = I ( 'itemid' );
			$taskid = I ( 'taskid' );
			$openid = I ( 'openid' );
			$star1 = ( int ) I ( 'star1', 0 );
			$star2 = ( int ) I ( 'star2', 0 );
			$star3 = ( int ) I ( 'star3', 0 );
			$content = I ( 'content' );
			
			if (empty ( $itemid ) || empty ( $taskid )) $this->message ( '错误，没有找到相关信息' );
			// 绑定的用户ID
			$userid = $this->getCookieUserId ( $openid );
			if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
	
			if ($star1 > 5 || $star1 <= 0) $this->message ( '错误1' );
			if ($star2 > 5 || $star2 <= 0) $this->message ( '错误2' );
			if ($star3 > 5 || $star3 <= 0) $this->message ( '错误3' );

			$commentModel = D ( 'Comment' );
			$comment = $commentModel->where ( "itemid = {$itemid} AND userid = {$userid}" )->find ();
			
			if ($comment) {
				$data = array ();
				$data ['itemid'] = $itemid;
				$data ['companyavg'] = ($star1 + $star2 + $star3) / 3;
				$data ['companystar1'] = $star1;
				$data ['companystar2'] = $star2;
				$data ['companystar3'] = $star3;
				$data ['usercontent'] = $content;
				$data ['useraddtime'] = time ();
				$re = $commentModel->create ( $data );
				if ($re) {
					$re = $commentModel->save ( $data );
					$grade = D ( 'Grade' )->grade ( $comment ['companyid'], $star1, $star2, $star3 );//更新评分
					$user = D ( 'Member' )->upcomment ( $comment ['companyid'] );//更新评论次数
					$replay = D ( 'Member' )->upreplay ( $comment ['userid'] );//更新回复次数
					$return = array ();
					$return ['itemid'] = $data ['itemid'];
					$return ['avg'] = $data ['companyavg'];
					$return ['star1'] = $data ['companystar1'];
					$return ['star2'] = $data ['companystar2'];
					$return ['star3'] = $data ['companystar3'];
					$return ['content'] = $data ['usercontent'];
					$return ['addtime'] = $data ['useraddtime'];
					$this->message ( '评价成功' );
				}
			} else {
				$this->message ( '错误，没有找到相关信息' );
			}
		} else {
			$openid = I ( 'get.openid' );
			$taskid = I ( 'get.id', 0 );
			$itemid =  I ( 'get.taid', 0 );
			if (empty ( $itemid ) || empty ( $taskid ))	$this->message ( '错误，没有找到相关信息' );
			//根据任务id获取任务信息
			$data = D('Task')->field('itemid,title')->where(" itemid= {$taskid} ")->find();
			$this->assign ( 'data', $data );
			$this->assign ( 'openid', $openid );
			$this->assign ( 'itemid', $itemid );
			$this->meta_title = '评价企业';
			$this->display('comment');
		}
	}
	
	/**
	 * 跳出页面-用户详情
	 */
	public function user() {
		$openid = I ( 'get.openid' );
		$itemid = I ( 'get.id', 0 );
	
		if (empty ( $itemid ))	$this->message ( '错误，没有找到相关信息' );
				
		//用户资料
		$user = M('Member')->where("itemid = {$itemid}")->find();
		if (empty ( $user )) $this->message ('错误，没有找到相关信息');	
		
		// 绑定的用户ID 查看用户是否绑定
		$userid = $this->getCookieUserId ( $openid );		

		//与企业合作的
		if ($userid > 0) {
			$listCount ['user'] = M ( 'Task_apply' )->where ( "userid = {$userid} AND companyid = {$user['itemid']} AND ( status = 1 OR status = 3 )" )->count ();
		} else {
			$listCount ['user'] = 0;
		}
		$task = M ( 'Task' );
		// 进行中的任务
		$listCount ['ing'] = $task->where ( "userid = {$user['itemid']} AND status = 3" )->count ();
		
		// 已完成的任务
		$listCount ['end'] = $task->where ( "userid = {$user['itemid']} AND status = 1" )->count ();
		
		// 即将到期任务
		$nextTime = time () + 60 * 60 * 24 * 2;
		$listCount ['out'] = $task->where ( "{$nextTime} > endtime AND status = 3 AND userid = {$user['itemid']}" )->count ();
		
		// 发布的任务
		$listCount ['total'] = $task->where ( "userid = {$user['itemid']}" )->count ();
		
		//合作过的工程师
		$map['status'] = array(array('eq',3),array('eq',1),'OR');
		$map['companyid'] = $user['itemid'];
		
		//获取黑名单
		$balckList = getPublicFileds(array('companyid'=>$user['itemid'],'status'=>3),'Member_blacklist','userid');
		$patener = M ( "Task_apply" )->where ( $map )->where ( " userid not in ({$balckList}) " )->group ( 'userid' )->field ( 'userid' )->select ();
		$listCount ['patener'] = count ( $patener );
		
		$this->assign('count',$listCount);
		$this->assign('userid',$userid);
		$this->assign('user',$user);
		$this->meta_title = $user['company'];
		$this->display();
	}
	
	/**
	 * 解除绑定帐号
	 * @param string $openid 用户openid
	 * @return array; 响应的数据
	 */
	private function getUnOauth($openid = ''){
		$weModel = M('User');
		$user = $weModel->where("openid = '{$openid}' AND westatus = 1")->find();
		if ($openid && $user) {
			$we = $weModel->where("openid = '{$openid}'")->save(array('westatus'=>0));
			if ($we) {
				$re = array ( "解除绑定成功", 'text' );
			}else {
				$re = array ( "解除绑定失败，请重新发送：解除绑定", 'text' );
			}
		}
		else {
			$re = array ( "您还没有绑定帐号。<a href='".C("SITE_URL")."/wechat/oauth/?openid={$openid}'>点击立即进行绑定</a>", 'text' );
		}
		return $re;
	}
	
	/**
	 * 跳出页面-绑定帐号
	 */
	public function oauth(){
		if (IS_POST) {
			if (empty($_POST['emp_no'])) {
				$this -> error('帐号必须！');
			} elseif (empty($_POST['password'])) {
				$this -> error('密码必须！');
			}
			//生成认证条件
			$map = array();
			// 支持使用绑定帐号登录
			$map['emp_no'] = $_POST['emp_no'];
			$map["is_del"] = array('eq', 0);
			$model = D("User");
			$authInfo = $model -> where($map) -> find();

			//使用用户名、密码和状态的方式进行认证
			if (false === $authInfo) {
				$this -> error('帐号或密码错误！');
			} else {
				if ($authInfo['password'] != md5($_POST['password'])) {
					$this -> error('帐号或密码错误！');
				}
				session(C('USER_AUTH_KEY'), $authInfo['id']);
				session('emp_no', $authInfo['emp_no']);
				session('email', $authInfo['email']);
				session('user_name', $authInfo['name']);
				session('user_pic', $authInfo['pic']);
				session('dept_id', $authInfo['dept_id']);
				
				if ($authInfo['emp_no'] == 'admin') {
					session(C('ADMIN_AUTH_KEY'), true);
				}

				//保存登录信息
				$User = M('User');
				$ip = get_client_ip();
				$data = array();
				$data['id'] = $authInfo['id'];
				$data['last_login_time'] = time();
				$data['login_count'] = array('exp', 'login_count+1');
				$data['last_login_ip'] = $ip;
				$data['openid']=I ('post.openid');
				$data['westatus']=1;
				$result=$User -> save($data);
				if ($result){
					// 这里微信响应一条（恭喜您验证成功！您可以使用自定义菜单或直接回复信息查询想要了解的内容）信息；
					$this->send ( "恭喜您验证成功！您可以使用自定义菜单或直接回复信息查询想要了解的内容!", $openid, 'text' );
											
					$tmp = explode ( '@', $username );
					$username = substr ( $tmp [0], 0, strlen ( $tmp [0] ) - 4 ) . '****@' . $tmp [1];
					$msg = '';
					$msg .= '恭喜，绑定成功！你可以直接在微信回复“解除绑定”解除微信绑定。';
					$msg .= '当前绑定的账户为“' . $authInfo['emp_no'] . '”';
					$msg .= '如果您不希望收到微信提醒信息，可在帮助》信息推送设置中进行设置。';
					$this->message ( $msg );
				} else {
					$this->message ( '绑定失败' );
				}
			}
		} else {
				$this->assign('openid',I('request.openid'));
				$this->meta_title = "帐号绑定";
				$this->display ();
		}
	}
	
	/**
	 * 跳出页面-任务检索
	 */
	public function search() {
		$this->meta_title = "任务检索";
		$this->display ( 'search' );
	}
	
	/**
	 * 跳出页面-意见反馈
	 */
	public function feedback() {
		if (IS_POST) {
			$nickname = I ( 'nickname' );
			$contact = I ( 'contact' );
			$content = I ( 'content' );
			if (empty ( $nickname )) 	$this->message ( '请填写昵称' );
			if (empty ( $contact )) 	$this->message ( '请填写联系方式' );
			if (empty ( $content ))		$this->message ( '请填写意见内容' );
						
			$data = array ();
			$data ['nickname'] = $contact;
			$data ['contact'] = $contact;
			$data ['content'] = $content;
			$data ['addtime'] = time ();
			$re = M ( 'Wechat_feedback' )->data ( $data )->add ();
			if ($re) {
				$this->meta_title = "意见反馈";
				$this->display ( 'feedback_ok' );
			} else {
				$this->message ( '错误，失败！' );
			}
		} else {
			$this->meta_title = "意见反馈";
			$this->display ( 'feedback' );
		}
	}
	
	/**
	 * 跳出页面-推送设置
	 */

	public function userset() {
		if (IS_POST) {
			$setpush = I ( 'setpush', 0 );
			$openid = I ( 'openid' );
			if (empty ( $openid ))	$this->message ( '错误，没有找到相关信息' );
			$userid = $this->getCookieUserId ( $openid ); // 绑定的用户ID
			if ($userid < 0) $this->message ( '错误，您还没有绑定帐号' );
			
			$map = array ();
			$map ['openid'] = $openid;
			$map ['oauth'] = 3;
			$map ['userid'] = $userid;
			$re = M ( 'Wechat_user' )->where ( $map )->save ( array ( 'setpush' => $setpush ));
			//data2file(IT_ROOT.'/cache/sql.php',M ( 'Wechat_user' )->_sql());
			if ($re) {
				$this->message ( '设置成功' );
			} else {
				$this->message ( '设置失败' );
			}
		} else {
			$this->meta_title = "推送设置";
			$this->display ();
		}
	}
	
	/**
	 * 跳出页面-信息提示页面
	 */
	public function message($msg = '') {
		$this->assign ( 'msg', $msg );
		$this->display ( 'message' );
		exit ();
	}
	
	/**
	 * 获取微信用户基础资料
	 * 
	 * @param string $openid
	 *        	用户openid
	 * @return array; 响应的数据
	 */
	private function getuser($openid = '') {
		import ( "@.ORG.Util.ThinkWechat" );
		$weixin = new ThinkWechat ();
		// $openid = 'o0ehLt1pOAIEFZtPD4ghluvjamf0';
		$weuser = $weixin->user ( $openid );
		// dump($weuser);
		return $weuser;
	}

	public function test(){
		$this->send("test","oPq8Btwkfs8zMvAHxjmruSiaiIr0");
	}
	
	/**
	 * 主动发送消息
	 * 
	 * @param string $content
	 *        	内容
	 * @param string $openid
	 *        	发送者用户名
	 * @param string $type
	 *        	类型
	 * @return array 返回的信息
	 */
	private function send($content, $openid = '', $type = 'text') {
		import ( "@.ORG.Util.ThinkWechat" );
		$weixin = new ThinkWechat ();
		// $openid = 'o0ehLt1pOAIEFZtPD4ghluvjamf0';
		$restr = $weixin->sendMsg ($content, $openid, $type );
		return $restr;
	}

		
	/**
	 * 生成COOKIE或从COOKIE获得用户ID
	 */	
	private function getCookieUserId($openid = NULL) {
		if (empty ( $openid ))	return - 3;
		$auth = cookie ( 'user_auth' ); // 读取COOKIE
		if ($auth) { // 存在cookie
			$auth = decrypt ( $auth ); // 解密cookie
			if (strpos ( $auth, '{@}' ) === FALSE) { // 如果是假冒的cookie
				$uid = - 2;
			} else { // 真实，然后分解cookie
				$autharr = explode ( '{@}', $auth );
				$uid = $autharr [1];
			}
		} else { // 不存在cookie，则创建cookie
			$model = M("User");
			$weuser = $model -> where ( "openid = '{$openid}' AND westatus = 1" )->find (); // 查到userid
			if (empty ( $weuser )) { // 查到没有绑定返回-1
				$uid = - 1;
				$auth = encrypt ( $openid . '{@}' . $uid . '{@}' . time () );
				cookie ( 'user_auth', $auth );
			} else { // 绑定了的创建COOKIE；
				$uid = $weuser ['id'];
				$auth = encrypt ( $openid . '{@}' . $uid . '{@}' . time () );
				cookie ( 'user_auth', $auth );
			}
		}
		return $uid; // 返回USERID
	}

	/**
	 * 根据工程师得到推荐任务总数
	 *
	 * @param array $data  	会员信息
	 * @return int $count 查询的数据
	 */
	private function getRecomTaskCount($data = array()) {
	
		// 推荐条件
		$map = array ();
		$taskModel = D ( 'Task' );
	
		// 地区
		if ($data ['areaid']) {
			// 查这个条件的数量，空不加条件
			$num = $taskModel->where ( array ( 'areaid' => $data ['areaid'], 'status' => 3 , 'public_status' => 0 ) )->count ();
			if ($num) $map ['areaid'] = $data ['areaid'];
		}
	
		// 任务分类查询条件
		if ($data ['skill']) {
			$skills = explode ( ',', $data ['skill'] );
			foreach ( $skills as $kk ) {
				$sl [] = array ( 'like', "%,{$kk},%" );
				$sl [] = array ( 'like', "%{$kk},%" );
				$sl [] = array ( 'like', "%,{$kk}%" );
				$sl [] = array ( 'eq', $kk );
			}
			$sl [] = 'or';
			// 查这个条件的数量，空不加条件
			$num = $taskModel->where ( array ( 'tasktype' => $sl, 'status' => 3 , 'public_status' => 0 ) )->count ();
			if ($num) $map ['tasktype'] = $sl;
		}
	
		// 技术分类查询条件
		if ($data ['ccies']) {
			$ccies = explode ( ',', $data ['ccies'] );
			foreach ( $ccies as $kk ) {
				$cs [] = array ( 'like', "%,{$kk},%" );
				$cs [] = array ( 'like', "%{$kk},%" );
				$cs [] = array ( 'like', "%,{$kk}%" );
				$cs [] = array ( 'eq', $kk );
			}
			$cs [] = 'or';
			// 查这个条件的数量，空不加条件
			$num = $taskModel->where ( array ( 'skill' => $cs, 'status' => 3 , 'public_status' => 0 ) )->count ();
			if ($num) $map ['skill'] = $cs;
		}
			
		$map ['public_status'] = 0;
		$map ['status'] = 3;
		$subQuery = M('Task_apply')->field('taskid')->where("userid = {$data['itemid']}")->select(false);//不推荐我已经申请的
		$map['_string'] = " `itemid` NOT IN {$subQuery}";//不推荐我已经申请的
		$taskcount = $taskModel->where($map)->count ();
		//data2file(IT_ROOT.'/cache/sql.php',$taskModel->_sql());
		return $taskcount;
	}
	
	/**
	 * 根据工程师得到推荐任务
	 *
	 * @param array $data  	任务信息
	 * @param int $firstRow	页数
	 * @param int $taskrow	条数
	 * @return array $list 查询的数据
	 */
	private function getRecomTask($data = array(), $firstRow = 5, $taskrow = 0) {
	
		// 推荐条件
		$map = array ();
		$taskModel = D ( 'Task' );
	
		// 地区
		if ($data ['areaid']) {
			// 查这个条件的数量，空不加条件
			$num = $taskModel->where ( array ( 'areaid' => $data ['areaid'], 'status' => 3 , 'public_status' => 0 ) )->count ();
			if ($num) $map ['areaid'] = $data ['areaid'];
		}
		
		// 任务分类查询条件
		if ($data ['skill']) {
			$skills = explode ( ',', $data ['skill'] );
			foreach ( $skills as $kk ) {
				$sl [] = array ( 'like', "%,{$kk},%" );
				$sl [] = array ( 'like', "%{$kk},%" );
				$sl [] = array ( 'like', "%,{$kk}%" );
				$sl [] = array ( 'eq', $kk );
			}
			$sl [] = 'or';
			// 查这个条件的数量，空不加条件
			$num = $taskModel->where ( array ( 'tasktype' => $sl, 'status' => 3 , 'public_status' => 0 ) )->count ();
			if ($num) $map ['tasktype'] = $sl;
		}		
	
		// 技术分类查询条件
		if ($data ['ccies']) {
			$ccies = explode ( ',', $data ['ccies'] );
			foreach ( $ccies as $kk ) {
				$cs [] = array ( 'like', "%,{$kk},%" );
				$cs [] = array ( 'like', "%{$kk},%" );
				$cs [] = array ( 'like', "%,{$kk}%" );
				$cs [] = array ( 'eq', $kk );
			}
			$cs [] = 'or';
			// 查这个条件的数量，空不加条件
			$num = $taskModel->where ( array ( 'skill' => $cs, 'status' => 3 , 'public_status' => 0 ) )->count ();
			if ($num) $map ['skill'] = $cs;
		}
			
		$map ['public_status'] = 0;
		$map ['status'] = 3;
		$subQuery = M('Task_apply')->field('taskid')->where("userid = {$data['itemid']}")->select(false);//不推荐我已经申请的
		$map['_string'] = " `itemid` NOT IN {$subQuery}";//不推荐我已经申请的
		$tasklist = $taskModel->where ( $map )->order ( 'itemid DESC' )->limit ( $firstRow . ',' . $taskrow )->select ();
		//data2file(IT_ROOT.'/cache/sql.php',$taskModel->_sql());
		return $tasklist;
	}
}
