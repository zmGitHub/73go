<?php
namespace System;

//登录信息管理类。
//20141028, 当前可能是个过渡版本，主要是为了落实框架性的内容。 -- Lanny Lee.
//
//获得当前登录信息：
//  $currLI = LoginInfo::currentLoginInfo();
//
//检查是否有效的登录：
//  if ($currLI->isValid()) 
//     ...
//
//登录后，需要往$currLI中写入
//  userId, loginTime, regId, dispName, userRights等等信息
//
//退出登录，调用 clean()
//  $currLI = LoginInfo::currentLoginInfo();
//  if ($currLI) $currLI->clean();
//
class LoginInfo {
	private $lastTouch = '';
	//用户ID
	public $userId = '';
	//企业id
	public $comId='';
	//登录名
	public $loginTime = '';
	//用户类型
	public $userType = '';
	//用户名
	public $userName = '';
	//显示名
	public $displayName = '';
	//是否代理商
	public $isAgent = '';
	//代理商ID
	public $tmcId = '';
	//代理商员工id
	public $tmcempId='';
	//由代理商登录，获取代理企业的id
	public $tmcemptmcId='';
	
	//企业员工id
	public $empId='';
	
	//email
	public $email='';
	//phone
	public $phone='';

	// 微信端的OpenId
	public $userOpenid='';




	//用户权限集合，它是UserRight的集合(array)。
	public $userRights = NULL;

	public $menudata;
	public $currMenuInfo = array(
		'm'=>'', 'c'=>'', 'a'=>''
	);

	//其他信息以后再扩充 2014-10-28, Lanny Lee

	//检测到用户操作，则调用touch方法，更新lastTouch时间，后续若需要清除“死”会话时可用。
	public function touch() {
		$lastTouch = time();
	}

	//是否有效
	//  chkLevel 检查级别，0=>最低，简单检查一下
	//                    其他。。。暂时未想到
	public function isValid($chkLevel=0) {
		switch ($chkLevel) {
			case 0:
				//可以考虑$lastTouch不能大于nn分钟。
				return ($userId > 0) && ($loginTime) && ($lastTouch);
			default: 
				return FALSE;
		}
	}
	
	//获得权限 
	//三个参数是字符串，各自意思：$m=>module, $c=>controller, $a=>action
	//获得的返回值可为NULL，代表完全无权限；或者是一个UserRight
	public function getRightOfFunc($m, $c, $a) {
		$ur = NULL;
		if ($userRights) {
			foreach ($userRight as $userRight) {
				if ($userRight->sModule == $m &&
				    $userRight->sController == $c &&
				    $userRight->sAction == $a) {
					$ur = $userRight;
					break;    	
				}
			}
		}
		return $ur;
	}
	
	public function clean() {
		/*
		unset($lastTouch);
		unset($userId);
		unset($loginTime);
		unset($userName);
		unset($displayName);
		unset($userRights); */	
		unset($_SESSION['LoginInfo']);
	}
	
	//获取当前Login信息
	//返回的是一个LoginInfo，作为登录信息的容器，不能以检查这个LoginInfo是否NULL来判断
	//信息是否有效，需要调用isValid()方法。
	public static function currentLoginInfo() {
		$LI = NULL;
		if (isset($_SESSION['LoginInfo'])) {
			$LI = unserialize($_SESSION['LoginInfo']); 
		}
		if ($LI && ($LI instanceof LoginInfo)) {
			$LI->touch();
			return $LI;
		} else {
			$LI = new LoginInfo();
			$_SESSION['LoginInfo'] = serialize($LI);
			$LI->touch();
			return $LI;
		}
	}

	
	
	
}