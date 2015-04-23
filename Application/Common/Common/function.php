<?php

use Home\Logic\VNumGenerator;
use System\LoginInfo;
use Home\Logic\SysFuncLogic;

/**
 * 获取登录信息公用函数
 * 没有任何参数的调用，则返回Session中的LoginInfo对象；
 * 若给定$var不带$value参数，则返回LoginInfo中的$var属性对应的值
 * 若给定$var与$value，则为设定LoginInfo中的$var属性为$value
 * 
 * 登录成功后，可采用下面的代码实现信息登记
 * 
 *   LI('userName', $result['username']);
 *   LI('userId', $result['id']);
 *   
 *   网页中需要显示LoginInfo内部的属性，可采取下面的格式：
 *    {:LI('userName')}
 *
 * 退出登录要清理session，可采用：
 * LI()->clean();   
 *    
 * Enter description here ...
 * @param string $var   属性名称，区分大小写，需要与System\LoginInfo类对应
 * @param mixed $value
 * @return mixed
 */
function LI($var='', $value='') {
	$currLI = LoginInfo::currentLoginInfo(); 
	if (empty($var)) {
		//此时返回Session中的LoginInfo对象
		return $currLI;
	}
	if (is_string($var) && $currLI) {
		$claz = new \ReflectionClass($currLI);
		if ($claz->hasProperty($var)) {
			$prop = new \ReflectionProperty(get_class($currLI), $var);
			if ($prop && $prop->isPublic()) { 
				if (empty($value)) {
					//此时为获取属性值
					return $prop->getValue($currLI);
				} else {
					//此时为赋值
					$prop->setValue($currLI, $value);
					$_SESSION['LoginInfo'] = serialize($currLI);
					return;				
				}
			}
		} else if (empty($value)) return '';
	}
	return; 
}


/**公共日志记录函数
 * @param int $type   动作，操作类型：1.为登录操作 2.为添加数据操作 3.为修改数据操作 4.为删除数字操作 5.为文件上传操作
 * @param string $info 事件 记录操作的具体事项如：[xxx] 成功的登录后台。修改用户信息，操作成功(id:8373)
 * @param string $username 用户名称 用于登录操作
 * @return boolean
 */
function LOGS($type=1,$info='',$username=false){
	$log = M('logs');

	$data['type']=$type;
	$data['info']=$info;
	$data['pageurl']=get_url();
	if($username==false){
		$data['u_id']=LI('userId');
		$data['user_type']=LI('userType');
		$data['username']=	LI('userName');
	}else{
		$data['username']=$username;
	}
	$data['ip']=get_client_ip();
	$data['addtime']=time();

	return $log->add($data);
}

/**
 * 获取当前页面完整URL地址
 */
function get_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


/**
 * 统一Ajax调用返回封装。
 * 要求所有的Ajax调用都要按此格式封装。
 * 比如原先采用
 *   echo 1;
 * 现在需要采用：
 *   echo ajaxReturn(1);
 *   
 * 所有的Ajax调用，需要先检查返回的json对象中的err_code是否为0
 * 
 * Enter description here ...
 * @param unknown_type $data      返回值 
 * @param unknown_type $err_code  错误代码，缺省=0，0代表无错误
 */
function ajaxReturn_73go($data, $err_code=0) {
	return array(
		'err_code'=>$err_code,
		'result'=>$data
	);
}

function dbexec($sql) {
	$m = M();
	return $m->execute($sql);
}

function dbquery($sql) {
	$m = M();
	return $m->query($sql);
}

/**
 * 号码生成器。
 * 根据单据标识，自动产生单据号码。
 * $ident，单据标识是一个字符串的Key值，目前网站的单据编号是全站编号，同一种单据类型，所有
 * 的企业的单号都是不一样的。按照这个前提，单据编码规则模板可存放在字典表，字典表d_group为
 * ”vno_template”，已有的单据的key如下：
 * 		轻松行需求单=>”qsx_req”
 * 		轻松行方案单=>”qsx_sol”
 * 		订单=>”order”
 * 		汇总订单=>”uni_order”
 * $isCommit，是否真正获取，用在多人操作的环境中，需要在单据新增时显示一个临时的单号，
 * 使用$isCommit=false，此时会得到一个单号，但真正存盘的时候，需要使用$isCommit=true，
 * 来真正获取一个单号，同时采用这个单号进行写入。
 * 
 * 若单据号需要根据企业进行独立编制，编号的时候，还需要将企业类型（企业/TMC）以及该企业
 * 的ID送给编号器。目前全站编号，$coType和$coId两个参数留空。
 * 
 * @param string $ident  单据标识
 * @param bool $isCommit 是否真正获取
 * @param int $coType  0=>企业, 1=>TMC
 * @param int $coId    企业的ID   
 */
function VNumGen($ident, $isCommit='', $coType='', $coId='') {
	$generator = new VNumGenerator($coType, $coId);
	return $generator->genVNum($ident, $isCommit);
}

/**
 * 对一个已经生成的单号进行Double Check，若合格，则返回此单号；若此时不合格则返回重新生成的另一个单号
 * @param $ident
 * @param $currVNum
 * @param string $isCommit
 */
function VNumCheck($ident, $currVNum, $isCommit='') {
	$generator = new VNumGenerator();
	return $generator->checkOrRegenVNum($ident, $currVNum, $isCommit);
}

//第一个是原串,第二个是 部份串
function startsWith($str, $needle) {

    return strpos($str, $needle) === 0;

}

function endsWith($haystack, $needle)
{   
	$length = strlen($needle);  
    if($length == 0)
    {    
        return true;  
    }  
    return (substr($haystack, -$length) === $needle);
}

/**
 * 轻松行网站用户菜单自动生成器。
 * 可自动根据当前用户类型生成对应的菜单项。
 * @return mixed
 */
function UserMenu_73go() {
	$logic = new SysFuncLogic();
	return $logic->getMenu();
}

/**
 * 从来源数据集($src)中生成
 *   key=>value
 * 格式的数组，其中key是$src中对应的$kFld字段的值，value是$src对应的$vFld字段的值。
 * 要求来源中的key<->value pair是唯一的。
 * @param $src 数据库，一般来自某个select()的返回
 * @param $kFld key字段名
 * @param $vFld value字段名
 * @return array 格式为key=>value的数组。
 */
function genKeyValuePairs($src, $kFld, $vFld) {
	$result = array();
	foreach($src as $rec) {
		$result[$rec[$kFld]] = $rec[$vFld];
	}
	return $result;
}

//////----> FOR TMC Hosting
/**
 * 检查是否在 TMC Hosting模式下，暂定域名www.china-tmc.cn，可在config.php中用'TMC-HOSTING-SERVER'=>'xxxx.xxx.xx'配置
 * @return bool
 */

function isServerOfHosting() {
	return strtoupper($_SERVER['HTTP_HOST']) == strtoupper(C('TMC-HOSTING-SERVER'));
}

function isTMCHosting() {
	return C('TMC-HOSTING') == 'Yes';
}

/**
 * TMC Hosting模式下的当前TMC号。
 * 若非TMC Hosting模式，此处为空
 * @return string 当前TMC号
 */
function currTMCNum() {
	return C('CURR-TMC');
}

/**
 * 用于 Home/Resource/jsBase 调整js的基地址
 * @return string
 */
function PrefixTMCHosting() {
	return (isTMCHosting()?'/'.currTMCNum():"");
}

/**
 * 如果TMC号正确，则获取$GLOALS中保存的旗舰店信息，否则返回false
 * @return mixed
 */
function getHostedTmcInfo($key=null) {
	if($key)
		return $GLOBALS['hosted_tmc_siteinfo'][$key];
	return $GLOBALS['hosted_tmc_siteinfo'];
}
/////<---- FOR TMC Hosting End