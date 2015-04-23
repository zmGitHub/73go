<?php
namespace Agent\Logic;

use Think\Model;

class TmcempUserLogic extends Model {
	//获取登录相关的id信息
	public function tmcEmpLogins($uid) {
		$mTmcEmp = M('tmc_employee');
		$cond['u_id'] = $uid;
		$tmcEmp = $mTmcEmp->where($cond)->find();
		if ($tmcEmp) {
			LI('isAgent', 0);
			LI('tmcId',$tmcEmp['tmc_id']);
			LI('tmcempId', $tmcEmp['id']);		
			Li('tmcemptmcId',$tmcEmp['tmc_id']);
			Li('email',$tmcEmp['email']);
			Li('phone',$tmcEmp['phone']);
			$user = M('user');
			$tmcEmpUserType = $user->where('id ='.$uid)->getField('user_type');
			LI('userType', $tmcEmpUserType);
		} 
	}
	
}