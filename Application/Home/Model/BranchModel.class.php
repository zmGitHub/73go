<?php
namespace Home\Model;

use Think\Exception;
use Think\Model;

class BranchModel extends Model {
	
	/**
	 * 用ID获取Tmc部门。
	 * 返回查找到的数据。
	 * @param int $id
	 */
	public function getById($id) {
		$cond['id'] = $id;
		return $this->where($cond)->find();
	}

	/**
	 * 用代码查找Tmc部门，必须带上tmcid。
	 * 返回查找到的数据。
	 * 同一个代码可能有多条数据。
	 * 返回数组。
	 * @param int $tmcId
	 * @param string $code
	 */
	public function getByCode($tmcId, $code) {
		$cond['co_id'] = $tmcId;
		$cond['br_code'] = $code;
		return $this->where($cond)->select();
	}

	/**
	 * 按给定的父部门，获取下级部门列表。 
	 * 若$pid=''而$tmcId不为空，则抓取这个tmc的顶层部门列表
	 * @param int $pid
	 */
	public function selectByPId($pid='', $tmcId='',$status='') {
		if (!isset($pid) || $pid==='') unset($pid);
		if ($tmcId==='') unset($tmcId);
		if (empty($pid) && empty($tmcId)) {
			throw new Exception("PId为空时，必须有tmc_id");
		}
		if ($pid) {
			$cond['p_id'] = $pid;
		} else {
			$cond['co_id'] = $tmcId;
			$cond['p_id'] = array('exp',' is NULL');
		}
		if (!empty($status) || $status == '0') $cond['status'] = $status;
		$data = $this->where($cond)->order('br_code')->select();
		foreach ($data as &$br) {
			$br['hasChildren'] = $this->hasChildren($br['id']);
		}
		return $data;
	}
	
	
	public function hasChildren($id) {
		$cond['p_id'] = $id;
		$list = $this->where($cond)->field('1')->select();
		return ($list && count($list) > 0);
	}
	
}