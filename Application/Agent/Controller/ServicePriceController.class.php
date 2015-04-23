<?php
namespace Agent\Controller;
use Think\Controller;
class ServicePriceController extends Controller {
	/**
	 * 添加服务费
	 * Enter description here ...
	 */
	public function addServicePrice(){
		$strdata=$_POST;
		$data['tmc_id']=LI('tmcId');
		$air_insur_product=M('serv_price_config');
		$data['co_id']=$strdata['co_id'];
		$data['val_opp']=$strdata['val_opp'];
		$data['val_abs']=$strdata['val_abs'];
		$data['time_start']=$strdata['time_start'];
		$data['time_end']=$strdata['time_end'];
		$data['status']=0;
		$request=$air_insur_product->data($data)->add();
		$this->ajaxReturn(($request)? 1:0);
		}
		/**
		 * 删除服务费
		 * Enter description here ...
		 */
		public function deleteService(){
			$id=$_POST['id'];
			$air_insur_product=M('serv_price_config');
			$data['status']=99;
			$request=$air_insur_product->where('id='.$id)->save($data);
			$this->ajaxReturn(($request)? 1 : 0);
		}
		/**
		 * 根据id查询服务费信息
		 * Enter description here ...
		 */
		public function showServiceById(){
			$id=$_POST['id'];
			$air_insur_product=M('serv_price_config');
			$request=$air_insur_product->where('id='.$id)->find();
			$this->ajaxReturn($request);
		}
		/**
		 * 修改服务费信息
		 * Enter description here ...
		 */
		public function updateService(){
			$strdata=$_POST;
			$data['tmc_id']=LI('tmcId');
			$air_insur_product=M('serv_price_config');
			$id=$strdata['upp_id'];
			$data['co_id']=$strdata['upco_id'];
			$data['val_opp']=$strdata['upval_opp'];
			$data['val_abs']=$strdata['upval_abs'];
			$data['time_start']=$strdata['uptime_start'];
			$data['time_end']=$strdata['uptime_end'];
			$data['status']=0;
			$request=$air_insur_product->where('id='.$id)->save($data);
			$this->ajaxReturn(($request)? 1 : 0);
		}
}
