<?php
namespace Home\Model;
use Think\Model;

/**
 * 73go通用的基础Model类。
 * 覆盖的基本要求：
 * 1、可用findById($id)获取
 * Enter description here ...
 * @author Lanny Lee
 *
 */
class Model73go extends Model {
	
	
	public function __construct($tableName) {
		$this->tableName = $tableName;
		parent::__construct();
	}
	
	/**
	 * 使用id获得数据
	 * @param Integer $id
	 */
	public function findById($id) {
		$cond['id'] = $id;
		return $this->where($cond)->find();		
	}
	
}