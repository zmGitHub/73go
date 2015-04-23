<?php
/****
 * 文件内容说明
 * 作者：XXX
 *
 * 修改记录：
 * xxxx-xx-xx  创建
 * xxxx-xx-xx  (什么修改)
 *
 *
 * 2015/1/24		增加getTmcEmps, getEmpOfTmc，用于支持TMC部门员工管理, by Lanny Lee
 *
 */
namespace Common\Logic;

use Agent\Logic\TmcDepartmentStaffLogic;
use Common\Datasource\Datasource;
use Home\Logic\DepartmentStaffLogic;
use Think\Model;

class CommonLogic extends Model {
	/**
	 * 数据字典封装
	 * 创建者：董发勇
	 * 创建时间：2014-11-28下午01:36:47
	 */
	public function showDictionay($name) {
		$dictitabl = M('dictionary');
		$data['d_group']=$name;
		$result = $dictitabl->where($data)->select();
		return $result;
	}

	/**
	 * 按组名选择字典表内容，按odrType指定的形式进行排序。
	 * @param $group 组名
	 * @param mixed $odrType 排序方式：''或者0=>缺省的字符排序，1=>按数字值排序
	 * @return mixed
	 */
	public function getDictOfGroup($group, $odrType='') {
		$m = M('dictionary');
		$cond['d_group'] = $group;
		if (empty($odrType) || $odrType == 0) {
			//缺省排序方式
			//按字符排序，按字符排序，很多数字型的Key会有问题
			return $m->	where($cond)->order('d_key')->select();
		} else if ($odrType == 1) {
			//强制将d_key转换成数字后排序
			return $m->
			field('d_group, cast(d_key as unsigned integer) id_key, d_key, d_value, display_ex')->
			where($cond)->
			order('1, 2')->
			select();
		}

	}

	/**
	 * 获取企业的支付类型。支付类型：'0'=>现付，'1'=>月结
	 * @param $coId 客户企业的id，必须有
	 * @param $tmcId TMC企业的id，可为空，若为空则固定返回'0'
	 * @return string 支付类型：'0'=>现付，'1'=>月结
	 */
	public function getCoPayType($coId, $tmcId) {
		$payType = "0";
		if (!empty($tmcId)) {
			$model = M('co_tmc_link');
			$cond['co_id'] = $coId;
			$cond['tmc_id'] = $tmcId;
			$rec = $model->where($cond)->find();
			if ($rec) $payType = $rec['pay_type'];
		}
		return $payType;
	}

	public function getCompany($coId) {
		$m = M('company');
		$cond['id'] = $coId;
		return $m->where($cond)->find();
	}

	/**
	 * 抓取当前登录客户的协议客户列表，仅适用于TMC
	 * @return mixed 如果当前的登录是TMC用户或者TMC员工，则返回关联的协议客户id列表，字段名为co_id
	 */
	public function getLinkedCompanies() {
		$tmcId = LI('tmcId');
		if ($tmcId) {
			$m = M('co_tmc_link');
			$cond['tmc_id'] = $tmcId;
			$linkComs = $m->field('co_id')->where($cond)->select();
			//为了达到最高效率，组装id数组发送一次Query完成工作。
			$ids = array();
			foreach($linkComs as $com) {
				$ids[] = $com['co_id'];
			}
			if ($ids) {
				unset($cond);
				$m = M('company');
				$cond['id'] = array('in', $ids);
				return $m->where($cond)->select();
			}
		}
		return null;
	}

	/**
	 * @return array 员工列表
	 */
	public function getLinkedEmployees() {
		$emps = array();
		//获得本TMC的协议客户co_id列表
		$coIds = $this->getLinkedCompanies();
		foreach($coIds as $rec) {
			$coEmps = $this->getEmpOfCom($rec['id']);
			//将查到的员工合并在一个数据集中。
			if ($coEmps) $emps = array_merge($emps, $coEmps);
		}
		return $emps;
	}

	private $kvpCoBrIdNames = null;
	private $kvpIdTypeNames = null;

	private function buildBrIdNames($coId) {
		$m = M('branch');
		$cond['co_id'] = $coId;
		$this->kvpCoBrIdNames = genKeyValuePairs($m->where($cond)->select(), 'id', 'name');
	}

	private function buildIdTypeNames() {
		if ($this->kvpIdTypeNames == null)
			$this->kvpIdTypeNames = $this->genKeyValuePairsOfDict('id_type');
	}

	public function genKeyValuePairsOfDict($group) {
		return genKeyValuePairs($this->getDictOfGroup($group), 'd_key', 'd_value');
	}

	public function completeEmpInfo(&$recEmp) {
		if ($recEmp['id']) {
			$recEmp['br_name'] = $this->kvpCoBrIdNames[$recEmp['br_id']];
			$recEmp['id_name'] = $this->kvpIdTypeNames[$recEmp['id_type']];
		}
	}

	/**
	 * 获取指定企业的员工列表
	 * @param $coId 企业的id
	 * @return mixed 员工数据
	 */
	public function getEmpOfCom($coId) {
		$params['coId'] = $coId;
		return $this->getEmps($params);
	}

	/**
	 * 获取指定TMC企业的员工列表
	 * @param $tmcId tmc用户的id
	 * @return mixed 员工数据
	 */
	public function getEmpOfTmc($tmcId) {
		$params['tmcId'] = $tmcId;
		return $this->getTmcEmps($params);
	}

	/**
	 * 按条件获得员工列表
	 * @param $cond
	 * @return mixed
	 */
	public function getEmps($cond) {
		return Datasource::getData('General/empFull', $cond);
	}


	/**
	 * 获取企业指定的部门（给定Branch Id）底下（含子部门）的所有员工
	 * @param $brId
	 * @return mixed 员工数据
	 * @throws \Home\Logic\Exception
	 */
	public function getComBranchEmps($brId) {
		$m_brstuff = new DepartmentStaffLogic();
		$idList = $m_brstuff->getChildBranchIDs($brId);
		if ($idList) {
			$cond['brList'] = implode(',', $idList);
			return $this->getEmps($cond);
		}
	}


	/**
	 * 按条件获得TMC员工列表
	 * @param $cond
	 * @return mixed
	 */
	public function getTmcEmps($cond) {
		return Datasource::getData('General/tmcempFull', $cond);
	}

	/**
	 * 获取TMC企业指定的部门（给定Branch Id）底下（含子部门）的所有员工
	 * @param $brId
	 */
	public function getTmcBranchEmps($brId) {
		$m_brstuff = new TmcDepartmentStaffLogic();
		$idList = $m_brstuff->getChildBranchIDs($brId);
		if ($idList) {
			$cond['brList'] = implode(',', $idList);
			return $this->getTmcEmps($cond);
		}
	}



	/////---->旗舰店支持
	public function obtainHostedTmcInfo($tmc_code) {
		$cond['tmc_code'] = $tmc_code;
		$data = Datasource::getData('General/tmcSiteConfig', $cond);
		if ($data)
			$GLOBALS['hosted_tmc_siteinfo'] = $data[0];
	}

	/////<----旗舰店支持 END!!
}