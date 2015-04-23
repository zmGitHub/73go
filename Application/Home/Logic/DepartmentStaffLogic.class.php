<?php
namespace Home\Logic;

use Think\Model;
/**
 * 企业部门与员工业务处理实现层
 * @author dfy
 * @2014-12-6 下午03:01:48
 */
class DepartmentStaffLogic extends Model{
	private $treelist = array();
	private $level=0, $key=0;

	/**
	 * 查询TMC企业部门员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:32:09
	 */
	public function showDepartmentLogic(){
					
	}
	/**
	 * 添加TMC企业部门员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:46:52
	 */
	public function addDepartmentLogic($com_id,$p_id,$br_code,$name){
		$data['co_id'] =$com_id;
		if ($p_id && $p_id > 0)
    	$data['p_id']=$p_id;
    	$data['br_code']=$br_code;
    	$data['name']=$name;
    	$data['status']=0;
    	$branch=M('branch');
		$deptCode = $branch->field('br_code')->where("co_id={$com_id} and p_id={$p_id} and status=0")->select();
		foreach($deptCode as $i) {
			foreach($i as $dept_code) {
				if($dept_code == $br_code) {
					return -1;
				}
			}
		}
		$deptName = $branch->field('name')->where("co_id={$com_id} and p_id={$p_id} and status=0")->select();
		foreach($deptName as $j) {
			foreach($j as $dept_name) {
				if($dept_name == $name) {
					return -1;
				}
			}
		}
		$request=$branch->add($data);
		return 1;

	}
	/**
	 * 查询部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:59:37
	 */
	public function shouwDepartmentStaffLogic($id){
    	$co_id=LI('comId');//获取tmc的id值
    	$br_id=$id;//获取当前所选择的部门id
    	
	    if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
    	
    	$employee=M('');
    	//$employeesql="select * from 73go_employee where co_id=$co_id  and  br_id = $br_id  and (`status` = 0 or `status`=2)";
    	//1.21更改  员工状态 1属于企业，0不属于企业，2待审核 
    	//待审核 的时候还不属于企业
    	$employeesql="select * from 73go_employee where co_id=$co_id  and  br_id = $br_id  and `status` = 1 ";
    	$request=$employee->query($employeesql);
    	return $request;
	}
	/**
	 * 模糊查询部门信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:31:34
	 */
	public function showStaffLikeLogic($id,$like){
		$co_id=LI('comId');//获取tmc的id值
    	//遍历获取该部门下全部子类ID
    	if($id==0){
    		$p_id = $this->getChildBranchIDs($id,$co_id);
    	}else {
    		$p_id = $this->getChildBranchIDs($id);
    	}
     	
    	$p_id = implode(',', $p_id); 
    	if($id)
    		$where = "and br_id in (".$p_id.")";
    	$employee=M('');
			$sql="select * from 73go_employee where co_id=$co_id $where and status=1 and 
				 (emp_code like '%$like%' or `name` LIKE '%$like%' or phone like '%$like%' or email like '%$like%')
				 ";
		$request=$employee->query($sql);
		return $request;
	}
	/**
	 * 删除员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:36:10
	 */
	public function deleteStaffLogic($id){
		$employee=M('employee');
		$data['status'] = 0;
		$data['co_id'] = null;
		$data['br_id'] = null;
    	$request=$employee->where('id='.$id)->data($data)->save();
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
			$m_branch = D('Home/Branch');
			$children = $m_branch->selectByPId($brid, '', '0');
			foreach ($children as $cbranch) {
				$this->deleteBranch($cbranch['id']);
			}
			//更新自己所属的员工的状态
			$this->updateEmpStatusOfBranch($brid);
			//更新自己的状态为'99'
			$cond['id'] = $brid;
			$data['status'] = '99';
			$m_branch->where($cond)->data($data)->save();			
		}
	}
	
	public function updateEmpStatusOfBranch($brid) {
		if ($brid) {
			$m_emp = M('employee');
			$m_emp->execute("
UPDATE 73go_employee SET
  br_id = null,
  status = '1'
WHERE br_id = ".$brid);
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
	private function getMyChildBranches($brId, $coId='') {
		$m = M('branch');
		if (empty($brId) && empty($coId)) {
			throw new Exception("抓取企业一级部门时，必须传入企业ID！");
		}
		if (empty($brId)) {
			//一级部门列表
			$cond['p_id'] = array('exp', 'IS NULL');
			$cond['co_id'] = $coId;
		} else
			//非一级部门列表
			$cond['p_id'] = $brId;
		return $m->where($cond)->select();
	}

	/**
	 * 抓取企业中给定的部门及其包含的下属子部门的ID列表，返回一个ID数组。
	 * @param $brId
	 * @param string $coId
	 * @return array
	 * @throws Exception
	 * @author Lanny Lee
	 */
	public function getChildBranchIDs($brId, $coId='') {
		if (empty($brId) && empty($coId)) {
			throw new Exception("抓取企业一级部门时，必须传入企业ID！");
		}

		$result = array();
		if (!empty($brId)) $result[] = $brId;
		$children = $this->getMyChildBranches($brId, $coId);
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
		$co_id = LI('comId');
		$Deprtment = M('branch');
		$this->level++;
		if($p_id){
			$result = $Deprtment->where("co_id=%d and p_id=%d and status=%d",array($co_id,$p_id,0))->select();
		}else{
			$result = $Deprtment->where("co_id=%d and status=%d and p_id is null ",array($co_id,0))->select();
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