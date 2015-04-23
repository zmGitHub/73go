<?php
namespace System;

//Lanny Lee, 2014-10-28
//扩展权限类，当前（20141028）的版本是不需要用到的。
//只是个设计，留待后续真正需要的时候再扩展。
class OtherURignt {
	//类型是Long Integer
	private $data;
	
    public function __construct($data) {
    	$this->data = $data;
    	
    	if (!defined('RIGHT_MASK_73GO')) {
			define('RIGHT_MASK_73GO', '1');
			//权限预先定义，这里只是DEMO，若真的需要扩展权限，则可以预先定义1到32(或者64？)
			//个具体的权限。
			define('RIGHT_MASK_DEMO_B1', 0x01);    		
			define('RIGHT_MASK_DEMO_B2', 0x02);    		
    	}
	}
	
	//检查是否具有预定义好的附加权限
	//预定义好的权限是预先写死的，参见__construct方法中的各define
	//比如说要检查是否具有RIGHT_MASK_DEMO_B1权限：
	//
	//$our = new OtherURight(0xabc0d);
	//$hasDemoB1Right = $our->hasRight(RIGHT_MASK_DEMO_B1);
	public function hasRight($rightMask) {
		return $data & $rightMask > 0; 
	}
	
		
}