<?php
namespace Agent\Logic;

use Think\Model;

class TmcUserLogic extends Model {

	public function agentLogins($uid) {
		$mTmc = M('tmc');
		$cond['u_id'] = $uid;
		$tmc = $mTmc->where($cond)->find();
		if ($tmc) {
			LI('isAgent', 1);
			LI('tmcId', $tmc['id']);
			$user = M('user');
			$tmcUserType = $user->where('id ='.$uid)->getField('user_type');
			LI('userType', $tmcUserType);
		} 
	}
	
}