<?php
namespace Home\Logic;
use Agent\Logic\TmcempUserLogic;
use Agent\Logic\TmcUserLogic;
use Think\Model;

class UserLogic extends Model {

	/**
	 * 使用登录名和密码找到系统用户
	 * @param string $userName
	 * @param string $password
	 */
	public function findByNameAndPsw($userName, $password) {
		$m = M('user');
		$cond['account'] = $userName;
		$cond['password'] = $password;
		return $m->where($cond)->find();
	}
	
	/**
	 * 用户登录后处理
	 * @param int $uid
	 * @param mixed $data
	 */
	public function userLogins($uid, $data='') {
		if (empty($uid) && $data) {
			$uid = $data['id'];
		}
		if (empty($data)) {
			$mUser = M('user');
			$cond['id'] = $uid;
			$data = $mUser->where($cond)->find();
		}
		if ($data) {
			//将用户Id,username,user_type，相关的comId, empId都记录在LoginInfo中供后续取用
			LI('userId', $uid);
			LI('userName', $data['username']);
			LI('userType', $data['user_type']);
			
			$uType = (int) $data['user_type'];
						
			switch ($uType) {
				case 1:
					$m_emp=M('employee');
					$emp = $m_emp->where('u_id='.$uid)->find();
					if ($emp) {
						LI('empId', $emp['id']);
						LI('comId', $emp['co_id']);
						LI('displayName', $emp['name']);
						LI('email', $emp['email']);
						Li('phone',$emp['phone']);
					}
					break;
				case 2:
						$m_company=M('company');
						$com = $m_company->where('u_id='.$uid)->find();
						if ($com) {
							LI('comId', $com['id']);
							LI('displayName', $emp['name']);
						}
						break;
				case 3:
					$m_tmcuser = new TmcUserLogic();
					$m_tmcuser->agentLogins($data['id']);
					break;
				case 4:
					$m_tmcempUser = new TmcempUserLogic();
					$m_tmcempUser->tmcEmpLogins($data['id']);
					break;
			}
		}
	}
	
}