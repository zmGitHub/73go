<?php
namespace Agent\Logic;

use Think\Model;
/**
 * TMC登录账户业务处理实现层
 * @author dfy
 * @2014-12-6 下午03:01:48
 */
class TmcLogonUserLogic extends Model{
	/**
	 * 查询TMC登录
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午05:41:24
	 */
	public function showTmcLogonUserLogic($Page){
	    if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		$tmc_id=LI('tmcId');
		$tmc_employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
				u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te LEFT JOIN
				73go_tmc_branch tb on te.tmcbr_id=tb.id LEFT JOIN 73go_user  u ON
				te.u_id=u.id or te.u_id=null where te.tmc_id=$tmc_id".$link;
		$request=$tmc_employee->query($sql);
		return $request;
	}
	/**
	 * 查询有登陆名的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午06:19:45
	 */
	public function showTmcLogonUserYesLogic(){
		$tmc_id=LI('tmcId');
		$tmc_employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
				u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te LEFT JOIN
				73go_tmc_branch tb on te.tmcbr_id=tb.id LEFT JOIN 73go_user  u ON
				te.u_id=u.id where te.u_id=u.id and te.tmc_id=$tmc_id";
		$request=$tmc_employee->query($sql);
		return $request;
	}
	/**
	 * 查询没有登录名的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午06:20:07
	 */
	public function showTmcLogonUserNoLogic(){
		$tmc_id=LI('tmcId');
		$tmc_employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
				u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te LEFT JOIN
				73go_tmc_branch tb on te.tmcbr_id=tb.id LEFT JOIN 73go_user  u ON
				te.u_id=u.id where te.u_id is null and te.tmc_id=$tmc_id";
		$request=$tmc_employee->query($sql);
		return $request;
	}
	/**
	 * 全部用户模糊查询
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午07:08:33
	 */
	public function showTmcLogonUserLikeLogic($con){
		$tmc_id=LI('tmcId');
		$tmc_employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
				u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te LEFT JOIN
				73go_tmc_branch tb on te.tmcbr_id=tb.id LEFT JOIN 73go_user  u ON
				te.u_id=u.id or te.u_id=null where te.tmc_id=$tmc_id and 
(te.emp_code like '%$con%' or te.`name` like '%$con%' or te.phone like '%$con%' or te.id_num like '%$con%')
				";
		$request=$tmc_employee->query($sql);
		return $request;
		
	}
	/**
	 * 有登陆名用户模糊查询
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午07:08:46
	 */
	public function showTmcLogonUserYesLikeLogic($con){
		$tmc_id=LI('tmcId');
		$tmc_employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
				u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te LEFT JOIN
				73go_tmc_branch tb on te.tmcbr_id=tb.id LEFT JOIN 73go_user  u ON
				te.u_id=u.id where te.u_id=u.id and te.tmc_id=$tmc_id and 
(te.emp_code like '%$con%' or te.`name` like '%$con%' or te.phone like '%$con%' or te.id_num like '%$con%')
				";
		$request=$tmc_employee->query($sql);
		return $request;
	}
	/**
	 * 无登录用户模糊查询
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午07:08:57
	 */
	public function showTmcLogonUserNoLikeLogic($con){
		$tmc_id=LI('tmcId');
		$tmc_employee=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
				u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te LEFT JOIN
				73go_tmc_branch tb on te.tmcbr_id=tb.id LEFT JOIN 73go_user  u ON
				te.u_id=u.id where te.u_id is null and te.tmc_id=$tmc_id and 
(te.emp_code like '%$con%' or te.`name` like '%$con%' or te.phone like '%$con%' or te.id_num like '%$con%')
				";
		$request=$tmc_employee->query($sql);
		return $request;
	}
}