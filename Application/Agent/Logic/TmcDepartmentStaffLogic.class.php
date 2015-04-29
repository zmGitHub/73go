<?php
/***
 *
 * 修改历史
 *
 * 2015/1/24	增加getChildBranchIDs函数，用以支持TMC查找部门（含所有子部门）
 *
 */


namespace Agent\Logic;

use Think\Exception;
use Think\Model;
/**
 * TMC部门与员工业务处理实现层
 * @author dfy
 * @2014-12-6 下午03:01:48
 */
class TmcDepartmentStaffLogic extends Model{
	private $treelist = array();
	private $level=0, $key=0;

	/**
	 * 查询TMC企业部门员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:32:09
	 */
	public function showTmcDepartmentLogic(){
					
	}
	/**
	 * 添加TMC企业部门员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:46:52
	 *
	 * 修改：王月 2015-2-9 添加部门时做部门名称和部门号不能重复的验证
	 */
	public function addTmcDepartmentLogic($tmc_id,$p_id,$br_code,$name){
		$data['tmc_id'] =$tmc_id;
		if ($p_id && $p_id > 0)
	    	$data['p_id']=$p_id;
    	$data['br_code']=$br_code;
    	$data['name']=$name;
    	$tmc_branch=M('tmc_branch');
		$deptCode = $tmc_branch->field('br_code')->where("tmc_id={$tmc_id} and p_id={$p_id} and status=0")->select();
		foreach($deptCode as $i) {
			foreach($i as $dept_code) {
				if($dept_code == $br_code) {
					return -1;
				}
			}
		}
		$deptName = $tmc_branch->field('name')->where("tmc_id={$tmc_id} and p_id={$p_id} and status=0")->select();
		foreach($deptName as $j) {
			foreach($j as $dept_name) {
				if($dept_name == $name) {
					return -1;
				}
			}
		}
		$request=$tmc_branch->add($data);
		return 1;
	}
	/**
	 * 查询部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:59:37
	 */
	public function shouwTmcDepartmentStaffLogic($id){
    	$data['tmc_id']=LI('tmcId');//获取tmc的id值
    	$data['tmcbr_id']=$id;//获取当前所选择的部门id
    	$data['status']=0;
    	$tmc_employee=M('tmc_employee');
    	$request=$tmc_employee->where($data)->select();
    	return $request;
	}
	/**
	 * 模糊查询部门信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:31:34
	 */
	public function showTmcStaffLikeLogic($id,$like){
		$tmc_id=LI('tmcId');//获取tmc的id值
    	

    	$tmc_employee=M('');
			$sql="select * from 73go_operator where tmc_id=$tmc_id and
				 (op_id like '%$like%' or `op_name` LIKE '%$like%' or phone like '%$like%' or email like '%$like%')
				 ";
		$request=$tmc_employee->query($sql);
		return $request;
	}
	/**
	 * 删除员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:36:10
	 */
	public function deleteTmcStaffLogic($id){
		$map['tmc_id']=LI('tmcId');//获取tmc的id值
		$map['op_id'] = $id;
		$tmc_employee=M('operator');
    	$request=$tmc_employee->where($map)->delete();
    	return $request;
	}
	/**
	 * 添加员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:43:29
	 */
	public function addTmcStaffLogic(){
		
	}
	
	/**
	 * 删除部门信息
	 * Enter description here ...
	 * @param unknown_type $brid
	 */
	public function deleteBranch($brid) {
		if ($brid) {
			$m_branch = D('Agent/TmcBranch');
			$children = $m_branch->selectByPId($brid, '', '0');
			foreach ($children as $cbranch) {
				$this->deleteBranch($cbranch['id']);
			}
			//更新自己所属的员工的状态
			$this->updateEmpStatusOfBranch($brid);
			//更新自己的状态为'99'
			$cond['id'] = $brid;
			$data['status'] = '99';
			$result = $m_branch->where($cond)->data($data)->save();	
			return 1;		
		}
	}
	
	public function updateEmpStatusOfBranch($brid) {
		if ($brid) {
			$m_emp = M('tmc_employee');
			$m_emp->execute("
				UPDATE 73go_tmc_employee SET  tmcbr_id = null WHERE tmcbr_id = ".$brid);
		}
	}


	/**
	 * 抓取一层子部门数据
	 * @param $brId
	 * @param string $tmcId
	 * @return mixed
	 * @throws Exception
	 * @author Lanny Lee
	 */
	private function getMyChildBranches($brId, $tmcId='') {
		$m = M('tmc_branch');
		if (empty($brId) && empty($tmcId)) {
			throw new Exception("抓取TMC一级部门时，必须传入TMC ID！");
		}
		if (empty($brId)) {
			//一级部门列表
			$cond['p_id'] = array('exp', 'IS NULL');
			$cond['tmc_id'] = $tmcId;
		} else
			//非一级部门列表
			$cond['p_id'] = $brId;
		return $m->where($cond)->select();
	}

	/**
	 * 抓取TMC企业中给定的部门及其包含的下属子部门的ID列表，返回一个ID数组。
	 * @param $brId
	 * @param string $tmcId
	 * @return array
	 * @throws Exception
	 * @author Lanny Lee
	 */
	public function getChildBranchIDs($brId, $tmcId='') {
		if (empty($brId) && empty($tmcId)) {
			throw new Exception("抓取TMC一级部门时，必须传入TMC ID！");
		}

		$result = array();
		if (!empty($brId)) $result[] = $brId;
		$children = $this->getMyChildBranches($brId, $tmcId);
		if ($children) {
			foreach($children as $br) {
				//递归调用
				$c_result = $this->getChildBranchIDs($br['id']);
				$result = array_merge($result, $c_result);
			}
		}
		return $result;
	}


	/**
	 * 获取部门树状列表。
	 * @param $tmc_id
	 * @param $p_id
	 * @return array
	 * @author david law
	 */
	public function getDeprtmentTree($p_id = false) {
		$tmc_id = LI('tmcId');
		$Deprtment = M('tmc_branch');
		$this->level++;
		if($p_id){
			$result = $Deprtment->where("tmc_id=%d and p_id=%d and status=%d",array($tmc_id,$p_id,0))->select();
		}else{
			$result = $Deprtment->where("tmc_id=%d and status=%d and p_id is null ",array($tmc_id,0))->select();
		}
		foreach($result as $key=>$value){
			$value['level'] = $this->level;
			$value['levelhtml'] = "";
			for($i=1; $i< $this->level; $i++){
				$value['levelhtml'] .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			$this->treelist[$this->key] = $value;
			$this->key++;
			$this->getDeprtmentTree($value['id']);
		}
		$this->level--;
		return $this->treelist;
	}

	
	
	
	
}