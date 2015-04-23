<?php
namespace Home\Controller;

use Think\Controller;

class TreeController extends Controller {
	
	private function buildNodesData($list) {
		$ret = '[';
		if ($list) {
			$cnt = 0;
			foreach ($list as $br) {
				if ($cnt++ > 0) $ret .= ','; 
		$ret .= '{
  "text": "'.$br['name'].'",
  "expanded": false,
  "classes": "branch_node",
  "id": '.$br['id'].',';
        if ($br['hasChildren']) {
        	$ret .= ' "hasChildren": true,';
        }
        $ret .= ' "code": "'.$br['br_code'].'"
}';
			}
		}
		$ret .= ']';
		return $ret;
	}
	
	/**
	 * 用于提供TMC部门树数据的Action。
	 * 每次调用会按request中的root参数作为父部门抓取一级子部门列表。
	 * 生成JSON格式的输出。
	 * 
	 * 此Action专用于jquery.treeview插件。
	 */	
	public function branches() {
		$com_id = $_REQUEST['com_id'];
		$br_id = $_REQUEST['root'];
		$m = D('Home/branch');  
		$list = $m->selectByPId($br_id, $com_id,'0');
		echo $this->buildNodesData($list);
	}
}