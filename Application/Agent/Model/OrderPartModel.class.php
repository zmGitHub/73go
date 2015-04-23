<?php
namespace Agent\Model;

use Think\Model;

/**
 * 订单的各种详情的公共操作
 * @author haoge
 *
 */
abstract class OrderPartModel extends Model {
	
	/**
	 * 在删除自己之前，先删除相关记录，继承类必须实现此函数。
	 * 比如，机票详情要先删除“机票使用者详情”、“机票保险单”等。
	 * 创建者：Lanny Lee
	 * 2014-12-25上午03:21:52
	 */
	abstract protected function deleteParentRelated($orderId);

	/**
	 * 删除自身的时候,先删除自己的id所对应的下级数据。
	 * @param $id
	 * @return mixed
	 */
	abstract protected function deleteSelfRelated($id);
	
	/**
	 * 已经抓取到订单相关的详情id列表，删除这些id对应指定的$table.$field的记录
	 * 创建者：Lanny Lee
	 * 2014-12-25上午03:54:05
	 * @param $table 关联的表
	 * @param $field 关联的字段
	 * @param mixed $ids 可以是单个id，也可以是id数组
	 */
	protected function doDeleteRelated($table, $field, $ids) {
		if (is_array($ids)) {
			$cond[$field] = array('in', $ids);
		} else {
			$cond[$field] = $ids;
		}
		$m = M($table);
		$m->where($cond)->delete();
	}
	
	
	/**
	 * 按给定的id获得数据
	 * 创建者：Lanny Lee
	 * 2014-12-25上午02:28:52
	 */
	public function getById($id) {
		$cond['id'] = $id;
		return $this->where($cond)->find();
	}
	
	
	/**
	 * 仅抓出关联指定订单id的记录id, 输出到一个aray中。
	 * 例如抓出1,2,3三条数据，则返回array(1,2,3) 
	 * 创建者：Lanny Lee
	 * 2014-12-25上午02:26:33
	 */
	public function getIdsByOrderId($odrId) {
		$cond['o_id'] = $odrId;
		$recList = $this->where($cond)->field('id')->select();
		$ids = array();
		if ($recList) {
			foreach ($recList as $rec) $ids[] = $rec[id];
		} 
		return $ids;
	}
	
	
	/**
	 * 删除关联指定订单id的记录
	 * 创建者：Lanny Lee
	 * 2014-12-25上午02:12:32
	 */
	public function deleteByOrderId($odrId) {
		//先删除关联数据
		$this->deleteParentRelated($odrId);
		$cond['o_id'] = $odrId;
		$this->where($cond)->delete();
	}
	
	/**
	 * 抓出关联指定订单id的记录
	 * 创建者：Lanny Lee
	 * 2014-12-25上午02:26:33
	 */
	public function selectByOrderId($odrId) {
		$cond['o_id'] = $odrId;
		return $this->where($cond)->order('o_index')->select();
	}

	
	/**
	 * 固定的转化过程后，需要附加的处理，继承的类需要重载此方法。
	 * @param 形成的数据库数据 $data
	 * @param 原始数据 $uiData
	 */
	protected function fixData(&$data, $uiData) {
		//Nothing to do.
	}

	public function deleteById($id) {
		$this->deleteSelfRelated($id);
		$cond['id'] = $id;
		$this->where($cond)->delete();
	}


}