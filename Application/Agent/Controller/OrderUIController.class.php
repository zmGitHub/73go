<?php
namespace Agent\Controller;

use Think\Controller;

class OrderUIController extends Controller {

	/**
	 * 联合订单（含独立订单）界面 
	 */
	public function union_order() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('union_order');
	}

	/**
	 * 空订单界面 
	 */
	public function order() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('order');
	}
	
	/**
	 * 用户tr内容 
	 */
	public function order_user() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('order_user');
	}


	public function flight_rec() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('flight_rec');
	}

	/**
	 * 单程机票内容 
	 */
	public function flight_oneway() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('flight_one');
	}

	/**
	 * 两航段机票内容 
	 */
	public function flight_twoway() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('flight_two');
	}
	
	/**
	 * 三航段机票内容 
	 */
	public function flight_threeway() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('flight_three');
	}
	/**
	 * 航空保险内容 
	 */
	public function air_insur() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('air_insur');
	}
	/**
	 * 酒店内容 
	 */
	public function hotel_info() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('hotel');
	}
	/**
	 * 火车票内容 
	 */
	public function train_info() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('train');
	}
	/**
	 * 其他内容 
	 */
	public function other_info() {
		C('LAYOUT_ON', FALSE);
		$this->theme('agent')->display('other');
	}
	
	
	//生成订单号
	public function addOrderNum(){
		//生成订单号
		$data=VNumGen('order');
		$this->ajaxReturn($data);
	}
}