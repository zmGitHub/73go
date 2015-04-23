<?php
namespace Agent\Logic;

use Think\Model;
/**
 * TMC工作组业务处理实现层
 * @author dfy
 * @2014-12-6 下午03:01:48
 */
class TmcGroupLogic extends Model{
	/**
	 * 查看TMC工作组
	 *dfy  2014-12-6  下午03:04:17
	 */
	public function showTmcGroupLogic(){
		$tmcid=LI('tmcId');//获取当前tmc企业id

		$tmc_table=M('');
		//获取当前tmc企业下的工作组名
		$sql="select a.id,a.`name`,a.tmc_id from  73go_tmc_team as  a where a.status=0 and a.tmc_id=$tmcid";
    	$resultname=$tmc_table->query($sql);
		
    	foreach($resultname as $key=>$val){
			//获取当前企业下的工作组成员
			$sqllistname=
				"select a.id,e.`name` as ename,b.id as bid
FROM 73go_tmc_team as a, 73go_tmc_team_member as b, 73go_tmc_employee as e
WHERE a.id=b.team_id AND e.id=b.emp_id AND a.tmc_id=$tmcid and a.id=".$val['id'];
			$resultnamelist=$tmc_table->query($sqllistname);
			$resultname[$key]['gro'] = $resultnamelist;//将组成员拼接成数组字段
			//获取企业下的服务企业
			$sqllistcom=
				"select a.id,cc.`name` as ccname,c.id as cid
FROM 73go_tmc_team as a, 73go_tmc_team_co as c, 73go_company as cc
WHERE a.id=c.team_id AND cc.id=c.co_id AND a.tmc_id=$tmcid and a.id=".$val['id'];
			$resultcomlist=$tmc_table->query($sqllistcom);
			$resultname[$key]['com'] = $resultcomlist;//将服务企业拼接成数组字段
		}
		return $resultname;
	}
	/**
	 * 添加工作组
	 *dfy  2014-12-6  下午04:21:40
	 *
	 * 修改：王月 2015-2-10
	 * 判断工作组是否重名
	 */
	public function addTmcGroupLogic($name){
		$data['tmc_id']=LI('tmcId');
    	$data['time']=date('Y-m-d H:i:s',time());
    	$data['name']=$name;
    	$tmc_team=M('tmc_team');
		$tmc_group = $tmc_team->where('tmc_id='.$data['tmc_id'])->select();
		foreach($tmc_group as $i) {
			foreach($i as $group) {
				if($group == $name) {
					return -1;
				}
			}
		}
    	$tmc_team->data($data)->add();
    	return 1;
	}
	/**
	 * 删除工作组
	 *dfy  2014-12-6  下午09:49:22
	 */
	public function deleTmcGroupLogic($id){
		$tmc_teamid=$id;
		$data['status']=99;
    	$tmc_tema=M('tmc_team');
    	$request=$tmc_tema->where('id='.$tmc_teamid)->save($data);
    	return $request;
	}
	/**
	 * 修改工作组
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午01:10:16
	 */
	public function updateaTmcGroup($id,$name){
		$tmc_teamid=$id;
		$data['name']=$name;
		$m = M('tmc_team');
    		return $m->where('id='.$tmc_teamid)->save($data);
	}

	/**
	 * 显示工作组员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午01:54:02
	 */
	public function showTmcGroupNumberLogic($id){
		$team_id=$id;
		session('team_id',$team_id);
    	$tmc_id=LI('tmcId');
    	$tmc_tema=M('');
    	$sql="select id,emp_code,name,phone,email  from 73go_tmc_employee where tmc_id = $tmc_id and id not IN
 				(select emp_id from 73go_tmc_team_member where team_id = $team_id)";
    	$request=$tmc_tema->query($sql);
    	return $request;
	}
	/**
	 * 添加工作组员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:03:51
	 */
	public function addTmcGroupNumberLogic(){
		//$data['team_id']=session(team_id);

		$addMember = function($teamId, $empId) {
			$m = M('tmc_team_member');
			$data['team_id'] = $teamId;
			$data['emp_id'] = $empId;
			$data['time']=date('Y-m-d H:i:s',time());
			$m->data($data)->add();
		};

		$gId = $_REQUEST['g'];
		$emps = $_REQUEST['emps'];

		if (strlen($emps) > 0) {
			$e_ary = explode(',', $emps);
			foreach($e_ary as $empId) {
				$addMember($gId, $empId);
			}
			return strlen($emps);
		}

		return 0;
	}
	/**
	 * 删除工作组选中的员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:07:20
	 */
	public function deleteTmcGroupNumberLogic($id){
		$tmc_team_member=M('tmc_team_member');
		$request=$tmc_team_member->delete($id);
		return $request;
	}
	/**
	 * 显示工作组企业信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:26:10
	 */
	public function showTmcGroupCompanyLogic($id){
		$team_id=$id;
    	session('team_id_co',$team_id);
		$tmc_tema=M('');
    	$sql="select * from 73go_company where id not IN(select co_id from 73go_tmc_team_co where team_id =$team_id)";
    	$request=$tmc_tema->query($sql);
    	return $request;
	}
	/**
	 * 添加工作组企业信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:33:16
	 */
	public function addTmcGroupCompanyLogic($team_id,$id_array){
		foreach ($id_array as $id){
			$data['team_id']=$team_id;
			$data['co_id']=$id;
			$data['time']=date('Y-m-d H:i:s',time());
			$dataList[] = $data;
		}
		$tmc_team_co=M('tmc_team_co');
		$request=$tmc_team_co->addAll($dataList);
		return $request;
	}
	/**
	 * 删除工作组企业信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:37:18
	 */
	public function deleteTmcGroupCompanyLogic($id){
		$tmc_team_co=M('tmc_team_co');
		$request=$tmc_team_co->delete($id);
		return $request;
	}
}