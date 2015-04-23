<?php
namespace Home\Model;

class DictionaryModel extends Model73go {
	
	public function __construct() {
		parent::__construct("dictinary");
	}
	
	/**
	 * 按给定的Group和Key获得值。
	 * @param string $group
	 * @param string $key
	 */
	public function findByIdent($group, $key) {
		$cond['d_group'] = $group;
		$cond['d_key'] = $key; 
		$item = $this->where($cond)->find();
		if ($item) return $item['d_value'];	
		return '';	
	}
	
	/**
	 * 抓取给定的group的数据列表
	 * @param string $group
	 */
	public function selectByGroup($group) {
		$cond['d_group'] = $group;
		return $this->where($cond)->select();
	}
	
}

