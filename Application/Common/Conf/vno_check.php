<?php
/**
 * 散列的重复检查配置。
 * 格式是：
 *   $ident=>array($table, $field)
 * **/
return array (
	'qsx_req'=>array('qsx_req', 'qsx_rq_no'),	
	'qsx_sol'=>array('qsx_solution', 'qsx_sol_no'),	
	'travel_req'=>array('travel_request', 'tr_no'),	
	'order'=>array(
		array('order', 'order_num'),
		array('order_union', 'order_num')
	),
	'union_order'=>array('union_order', 'uni_odr_no'),	
	'co_code'=>array('company', 'co_code'),	
	'tmc_code'=>array('company', 'co_code')	
);