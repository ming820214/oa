<?php
/*---------------------------------------------------------------------------
 鸿文OA系统 - 信息管理系统

 Copyright (c) 2013 http://ihongwen.com All rights reserved.

 Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

 Author:  jinzhu.yin<smeoa@qq.com>

 Support: https://git.oschina.net/smeoa/smeoa
 -------------------------------------------------------------------------*/

class AuthCheckBehavior extends Behavior {
	protected $config;
	public function run(&$params) {
		//个人数据
		$this -> config = &$params;
		$app_type = $params['app_type'];

		switch($app_type) {
			case 'public' :
				$auth = array('admin' => false, 'write' => false, 'read' => true);
				$params['auth'] = $auth;
				return true;
				break;

			case 'asst' :
				$auth = array('admin' => true, 'write' => true, 'read' => true);
				$params['auth'] = $auth;
				return true;
				break;

			case 'personal' :
				$auth = array('admin' => true, 'write' => true, 'read' => true);
				$params['auth'] = $auth;
				return true;
				break;

			case 'common' :
				$action_auth = C('AUTH');
				if (!empty($params['action_auth'])) {
					$action_auth = array_merge(C('AUTH'), $params['action_auth']);
				}
				$auth = $this -> get_auth();
				break;

			case 'flow' :
				$action_auth = C('AUTH');
				if (!empty($params['action_auth'])) {
					$action_auth = array_merge(C('AUTH'), $params['action_auth']);
				}
				$auth = $this -> get_auth();
				break;

			case 'master' :
				$action_auth = C('AUTH');
				if (!empty($params['action_auth'])) {
					$action_auth = array_merge(C('AUTH'),$params['action_auth']);
				}
				$auth = $this -> get_auth();
				if ($auth['admin']) {
					return true;
				}
				break;

			case 'folder' :
				$action_auth = C('AUTH');
				if (!empty($params['action_auth'])) {
					$action_auth = array_merge(C('AUTH'), $params['action_auth']);
				}

				if (isset($_REQUEST['fid'])){
					$folder_id = $_REQUEST['fid'];
					$auth = D("SystemFolder") -> get_folder_auth($folder_id);
					break;
				}
				if (isset($_REQUEST['id'])){					
					$id = $_REQUEST['id'];
					if (is_array($id)){
						$where["id"] = array("in", array_filter($id));
					} else {
						$where["id"] = array('in', array_filter(explode(',', $id)));
					}
					$model = D(MODULE_NAME);
				
					$folder_id = $model -> where($where) -> getField('folder');	
					$auth = D("SystemFolder") -> get_folder_auth($folder_id);
					break;
				}
				$auth = $this -> get_auth();
				break;
			default :
				$action_auth = C('AUTH');
				$auth = $this -> get_auth();
				break;
		}
		 
		//die;
		if ($auth[$action_auth[ACTION_NAME]]) {
			$this -> config['auth'] = $auth;
			return true;
		} else {
			$auth_id = session(C('USER_AUTH_KEY'));
			if (!isset($auth_id)){
				//跳转到认证网关
				redirect(U(C('USER_AUTH_GATEWAY')));
			}
			$e['message'] = "没有权限";
			include    C('TMPL_NO_HAVE_AUTH');
			die ;
		};
	}

	function get_auth() {
		
		$access_list = D("Node") -> access_list();
		$access_list = array_filter($access_list, array($this,'filter_module'));
		$access_list = rotate($access_list);

		$module_list = $access_list['url'];
		$module_list = array_map(array($this,"get_module"),$module_list);
		$module_list = str_replace("_", "", $module_list);
		
		$access_list_admin = array_filter(array_combine($module_list, $access_list['admin']));
		$access_list_write = array_filter(array_combine($module_list, $access_list['write']));
		$access_list_read = array_filter(array_combine($module_list, $access_list['read']));

		$module_name=strtolower(MODULE_NAME);
		$auth['admin'] = array_key_exists($module_name,$access_list_admin) ||array_key_exists("##".$module_name,$access_list_admin);

		$auth['write'] = array_key_exists($module_name,$access_list_write) ||array_key_exists("##".$module_name,$access_list_write);
		
		$auth['read'] = array_key_exists($module_name,$access_list_read) ||array_key_exists("##".$module_name,$access_list_read);

		if ($auth['admin'] == true) {
			$auth['write'] = true;
		}
		if ($auth['write'] == true) {
			$auth['read'] = true;
		}
		return $auth;
	}

	function get_module($str) {
			$arr_str = explode("/", $str);
			return $arr_str[0];
	}

	function filter_module($str) {
		if (strpos($str['url'], '##') !== false) {
			return true;
		}
		if (empty($str['admin']) && empty($str['write']) && empty($str['read'])) {
			return false;
		}
		if (strpos($str['url'], 'index')) {
			return true;
		}
		return false;
	}
}
?>