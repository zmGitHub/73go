<?php
namespace Home\Logic;
use Think\Model;
use Think\Think;

class SysFuncLogic extends Model {

	protected $tablePrefix = "73go_";
	protected $tableName = "sys_func";
	Protected $autoCheckFields = false;

	protected function _initialize() {
		//print "defines";
		if (!defined('SYSFUNC_DEFINES')) {
			define('SYSFUNC_DEFINES', 1);
			define('SYSFUNC_FUNCTION', 1);
			define('SYSFUNC_TYPE_SYSTEM', 2);
			define('SYSFUNC_TYPE_GROUP', 9);
		}
	}

	/**
	 * 向系统菜单表加入一条记录
	 * Enter description here ...
	 * @param int $itemType  记录类型
	 * 		SYSFUNC_FUNCTION     ->  功能菜单
	 * 		SYSFUNC_TYPE_SYSTEM  ->  系统
	 * 		SYSFUNC_TYPE_GROUP   ->  功能组
	 * @param string $code
	 * @param string $name
	 * @param int $pItem
	 * @param int $odr
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
	private function addItem($itemType, $code, $name, $pItem='', $odr,
		$module='', $controller='', $action='') {
		$map['item_type'] = $itemType;
		$map['odr'] = $odr;
		$map['func_key'] = $code;
		$map['caption'] = $name;
		if (!empty($pItem)) 	$map['p_item'] = $pItem;
		if (!empty($module)) 	$map['module'] = $module;
		if (!empty($controller)) $map['controller'] = $controller;
		if (!empty($action)) 	$map['action'] = $action;
		if (!empty($funcUrl)) 	$map['func_url'] = $funcUrl;

		$ret = $this->data($map)->add();
		return $ret;
	}

	private function addSystem($systemCode, $systemName, $odr=0) {
		return $this->addItem(SYSFUNC_TYPE_SYSTEM, $systemCode, $systemName, '', odr);
	}

	/**
	 * 生成企业用户的菜单
	 * Enter description here ...
	 * @param unknown_type $sysId
	 */
	private function addCompanyMenus($sysId) {
		//组序号
		$grpOdr = 1;

		//我的主页
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_co_my_homepage", "我的主页", $sysId,
			$grpOdr++, "Home", "Index", "mypage_co");
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_co_informations", "信息管理", $sysId, $grpOdr++);
		$odr = 1;
		$this->addItem(SYSFUNC_FUNCTION, "coinfo", "企业信息",
			$groupId, $odr++, "Home", "Index", "config_coinfo_basicinfo");
		$this->addItem(SYSFUNC_FUNCTION, "depstaff", "部门和员工信息",
			$groupId, $odr++, "Home", "DepartmentStaff", "showDepartment");
		$this->addItem(SYSFUNC_FUNCTION, "userinfo", "登录用户管理",
			$groupId, $odr++, "Home", "LogonUser", "co_userid_mgnt");
		$this->addItem(SYSFUNC_FUNCTION, "travel_policy", "差旅政策设置",
			$groupId, $odr++, "Home", "EP", "indexjb");
		$this->addItem(SYSFUNC_FUNCTION, "BulletinMgnt", "通告管理",
			$groupId, $odr++, "Home", "BulletinMgnt", "co_bulletin_mgnt");
		$this->addItem(SYSFUNC_FUNCTION, "travel_report", "差旅报告",
			$groupId, $odr++, "Home", "TravelReport", "inittrreport");
	}

	/**
	 * 生成TMC企业用户的菜单
	 * Enter description here ...
	 * @param unknown_type $sysId
	 */
	private function addTMCMenus($sysId) {
		//组序号
		$grpOdr = 1;

		//我的主页
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_tmc_my_homepage", "我的主页", $sysId,
			$grpOdr++, "Agent", "Index", "mypage_tmc");
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_management", "业务管理", $sysId, $grpOdr++);
		$odr = 1;
		$this->addItem(SYSFUNC_FUNCTION, "tmc_coinfo", "企业信息",
			$groupId, $odr++, "Agent", "Config", "showconfig_tmcinfo_basicinfo");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_emplst", "部门和员工信息",
			$groupId, $odr++, "Agent", "TmcDepartmentStaff", "showTmcDepartment");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_cou_info", "登录账户管理",
			$groupId, $odr++, "Agent", "TmcLogonUser", "showTmcLogonUser");
		$this->addItem(SYSFUNC_FUNCTION, "store", "旗舰店管理",
			$groupId, $odr++, "Agent", "EM", "tmc_store_mgnt");
		$this->addItem(SYSFUNC_FUNCTION, "cusmgnt", "客户管理",
			$groupId, $odr++, "Agent", "EM", "showcustomer");
		$this->addItem(SYSFUNC_FUNCTION, "prodmgnt", "产品管理",
			$groupId, $odr++, "Agent", "Product", "config_flight_price");
		$this->addItem(SYSFUNC_FUNCTION, "TmcBulletinMgnt", "通告管理",
			$groupId, $odr++, "Agent", "TmcBulletinMgnt", "tmc_bulletin_mgnt");
	}


	/**
	 * 生成员工&普通个人的菜单
	 * Enter description here ...
	 * @param unknown_type $sysId
	 */
	private function addEmployeeMenus($sysId) {
		//组序号
		$grpOdr = 1;
		//我的主页
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_my_homepage", "我的主页", $sysId,
			$grpOdr++, "Home", "Index", "mypage_user");

		//申请、审批
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_tr_appr", "申请、审批", $sysId, $grpOdr++);

		//功能序号
		$odr = 1;
		$funcId = $this->addItem(SYSFUNC_FUNCTION, "tr_request", "新建出差申请",
			$groupId, $odr++, "Home", "Approval", "tr_request");
		//$this->addItem(SYSFUNC_FUNCTION, "tr_req_draft_box", "草稿箱",
		//	$funcId, $odr++, "Home", "Travel", "req");
		$this->addItem(SYSFUNC_FUNCTION, "my_tr_request", "我的申请记录",
			$groupId, $odr++, "Home", "Approval", "my_tr_request_list");
		$this->addItem(SYSFUNC_FUNCTION, "my_approval", "待我审批",
			$groupId, $odr++, "Home", "Approval", "my_approval_list");

		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_trvl_query_booking", "查询、预订", $sysId, $grpOdr++);
		$odr = 1;
		$this->addItem(SYSFUNC_FUNCTION, "book_self", "自助预定", $groupId, $odr++, "home", "book","index");
		$this->addItem(SYSFUNC_FUNCTION, "book_73go", "轻松行预定", $groupId, $odr++, "Home", "Easygo", "qsx_req");
		$this->addItem(SYSFUNC_FUNCTION, "view_solutions", "&nbsp;&nbsp;└方案查看", $groupId, $odr++, "Home", "Easygo", "qsx_req_list");

		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_orders", "我的订单", $sysId, $grpOdr++);
		$odr = 1;
		$this->addItem(SYSFUNC_FUNCTION, "order_query", "订单查询", $groupId, $odr++, "Home", "Order", "order_list_6");
		$this->addItem(SYSFUNC_FUNCTION, "expired_flightickets", "已过期机票查询", $groupId, $odr++, "Home", "Order", "exp_flight_list");

		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_informations", "信息管理", $sysId, $grpOdr++);
		$odr = 1;
		$this->addItem(SYSFUNC_FUNCTION, "myinfo_personal", "我的信息", $groupId, $odr++, "Home", "Index", "config_myinfo_personal");
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "report", "差旅分析", $sysId, $grpOdr++);
		$odr = 1;
		$this->addItem(SYSFUNC_FUNCTION, "travel_report", "差旅报告", $groupId, $odr++, "Home", "TravelReport", "inittrreport");
//		$this->addItem(SYSFUNC_FUNCTION, "passengers", "常用乘客", $groupId, $odr++, "Home", "Index", "config_myinfo_card");
	}

	/**
	 * 生成TMC员工的菜单
	 * Enter description here ...
	 * @param unknown_type $sysId
	 */
	private function addTMCEmployeeMenus($sysId) {
		//组序号
		$grpOdr = 1;

		//我的主页
		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_tmcemp_my_homepage", "我的主页", $sysId,
			$grpOdr++, "Agent", "Index", "mypage_op");

		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_tmc_73go", "轻松行", $sysId, $grpOdr++);

		//功能序号
		$odr = 1;
		$funcId = $this->addItem(SYSFUNC_FUNCTION, "tmc_tr_reqs", "新需求单",
			$groupId, $odr++, "Agent", "NewDemand", "showNewDemandController");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_handling", "处理中",
			$groupId, $odr++, "Agent", "ES", "showEReq");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_handled", "已处理完",
			$groupId, $odr++, "Agent", "ES", "qsx_req_list_finish");

		$groupId = $this->addItem(SYSFUNC_TYPE_GROUP, "grp_tmc_odrmng", "订单管理", $sysId, $grpOdr++);

		//功能序号
		$odr = 1;
		$funcId = $this->addItem(SYSFUNC_FUNCTION, "tmc_orders", "所有订单", $groupId, $odr++, "Agent", "TmcOrder", "order_list_all");
		$funcId = $this->addItem(SYSFUNC_FUNCTION, "tmc_orders", "&nbsp;&nbsp;└新建订单",
			$groupId, $odr++, "Agent", "Order", "order_edit");
		$funcId = $this->addItem(SYSFUNC_FUNCTION, "tmc_orders", "配送管理",
			$groupId, $odr++, "Agent", "TmcOrder", "dispatch_mgnt");

		/**
		$this->addItem(SYSFUNC_FUNCTION, "tmc_flight_ticket", "机票",
			$groupId, $odr++, "Agent", "TmcOrder", "fli_ticket");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_hotel", "酒店",
			$groupId, $odr++, "Agent", "Travel", "hotel");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_transport", "接送",
			$groupId, $odr++, "Agent", "Travel", "transport");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_car_rent", "租车",
			$groupId, $odr++, "Agent", "Travel", "car_rent");
		$this->addItem(SYSFUNC_FUNCTION, "tmc_visa", "签证",
			$groupId, $odr++, "Agent", "Travel", "visa");
		 */



	}

	/**
	 * 获得指定func_key对应的菜单
	 * 返回系统菜单对象
	 * @param string $code 菜单的func_key
	 */
	public function findByFuncKey($code) {
		$cond["func_key"] = $code;
		return $this->where($cond)->find();
	}

	/**
	 * 指定id的系统菜单是否具有子（菜单）项
	 * Enter description here ...
	 * @param unknown_type $aItem
	 */
	public function hasChildren($aItem) {
		$cond["p_item"] = $aItem;
		return $this->where($cond)->count() > 0;
	}

	/**
	 * 获得指定的菜单项的子项
	 * 返回数组
	 * @param unknown_type $pItem
	 */
	public function selectByPItem($pItem) {
		$cond["p_item"] = $pItem;
		//按odr排序
		$items = $this->where($cond)->order('odr')->select();
		return $items;
	}

	/**
	 * 获得指定的系统的所有菜单数据
	 * Enter description here ...
	 * @param unknown_type $code
	 */
	public function selectBySystem($code) {
		$cond["item_type"] = SYSFUNC_TYPE_SYSTEM;
		$cond["func_key"] = $code;
		//类别是SYSTEM，func_key=$code
		$sysItem = $this->where($cond)->order('odr')->select();
		if ($sysItem) {
			$group_list = $this->selectByPItem($sysItem[0]['id']);
			if ($group_list) {
				foreach ($group_list as &$group) {
					$items = $this->selectByPItem($group['id']);
					$group['items'] = $items;
				}
			}
			return $group_list;
		}
		return '';
	}


	private function selectSystemMenus($sys) {
		$result = array();
		$list = $this->selectBySystem($sys);
		foreach ($list as $group) {
			$result[] = $group;

		}
	}

	private function addToMenu(&$menu, $pid) {
		$list = $this->selectByPItem($pid);
		foreach ($list as $item) {
			$aId = $item['id'];
			//将该项加到数组最后
			$menu[] = $item;
			if ($this->hasChildren($aId)) {
				//如果这个项具有子菜单
				//递归调用插入子菜单。
				$this->addToMenu($menu, $aId);
			}
		}
	}

	public function buildSystemMenu($sys) {
		$sysItem = $this->findByFuncKey($sys);
		if ($sysItem) {
			return $this->selectByPItem($sysItem['id']);
		}
		return '';
	}

	public function rebuildAll() {
		$result = false;
		$sql = "DELETE FROM ".$this->getTableName();
		//echo "SQL:".$sql."<br/>\r";
		$this->execute($sql);
		//echo "clear table OK!<br/>\r";

		/**
		$sql = "ALTER TABLE ".$this->getTableName().' AUTO_INCREMENT=0';
		echo "SQL:".$sql."<br/>\r";
		$this->execute($sql);
		echo "Reset AUTO_INCREMENT OK!<br/>\r";
		**/

		//插入3个系统
		$odr = 1;
		$sysId = $this->addSystem("CoUser", "企业用户", $odr++);
		if ($sysId) $this->addCompanyMenus($sysId);
		$sysId = $this->addSystem("TMCUser", "TMC用户", $odr++);
		if ($sysId) $this->addTMCMenus($sysId);
		$sysId = $this->addSystem("Common", "企业员工&个人", $odr++);
		if ($sysId) $this->addEmployeeMenus($sysId);
		$sysId = $this->addSystem("TMCEmp", "TMC员工", $odr++);
		if ($sysId) $this->addTMCEmployeeMenus($sysId);

		$result = true;
		return $result;
	}

	/**
	 * 两个字符串按大小写不敏感规则比较是否相等
	 * @param $s1
	 * @param $s2
	 * @return bool
	 */
	private function strEqs($s1, $s2) {
		return strtoupper($s1) === strtoupper($s2);
	}

	private function isMCAinMenuData(&$menudata, $m, $c, $a, $markIt=''){
		foreach($menudata as &$menuitem) {
			if ($this->strEqs($menuitem['module'], $m) &&
				$this->strEqs($menuitem['controller'], $c) &&
				$this->strEqs($menuitem['action'], $a)
			) {
				if ($markIt) $menuitem['is_curr'] = 1;
				return true;
			} else {
				if ($menuitem['items']) {
					$inSub = $this->isMCAinMenuData($menuitem['items'], $m, $c, $a, $markIt);
					if ($inSub) return true;
				}
			}
		}
		return false;
	}


	/**
	 * 在环境中，生成菜单
	 * Enter description here ...
	 */
	public function getMenu() {
		if ($this->count() == 0) $this->rebuildAll();
		$loginInfo = LI();
		$menuInfo = $loginInfo->currMenuInfo;
		$userId = $loginInfo->userId;
		if (!empty($userId)) {
			$user = M('user');
			$cond['id'] = $userId;
			$data = $user->where($cond)->find();
			//实例化一个View，作为模板解析用
			$view = Think::instance('Think\View');
			if ($data) {
				if ($data['user_type'] === C('SYSUSER_PERSON')) {
					//个人业务菜单
					$menudata = $this->selectBySystem("Common");
				} else
				if ($data['user_type'] === C('SYSUSER_COMPANY')) {
					//公司业务菜单
					$menudata = $this->selectBySystem("CoUser");
				} else
				if ($data['user_type'] === C('SYSUSER_TMC')) {
					//TMC业务菜单
					$menudata = $this->selectBySystem("TMCUser");
				} else
				if ($data['user_type'] === C('SYSUSER_TMCEMP')) {
					//TMC员工业务菜单
					$menudata = $this->selectBySystem("TMCEmp");
				}

				if ($menudata) {
					if ($this->strEqs($menuInfo['m'], MODULE_NAME) &&
						$this->strEqs($menuInfo['c'], CONTROLLER_NAME) &&
						$this->strEqs($menuInfo['a'], ACTION_NAME)) {
						//如果当前的action是记录好的那个action的话，不需要变化
					} else {
						if ($this->isMCAinMenuData($menudata, MODULE_NAME, CONTROLLER_NAME, ACTION_NAME)) {
							//如果这是在菜单中的m/c/a则标记在数据中
							$menuInfo['m'] = MODULE_NAME;
							$menuInfo['c'] = CONTROLLER_NAME;
							$menuInfo['a'] = ACTION_NAME;
							LI('currMenuInfo', $menuInfo);
						}
					}
					//使用LoginInfo中保存的m/c/a进行标记
					$this->isMCAinMenuData($menudata, $menuInfo['m'], $menuInfo['c'], $menuInfo['a'], TRUE);
					$view->assign('group_list', $menudata);
				}
				return $view->fetch(T("Home@Public/menu"));
			}
		}
	}

}