<?php
namespace Agent\Controller;
use Think\Controller;
class InsurInfoController extends Controller {
	/**
	 * 添加保险产品信息
	 * Enter description here ...
	 */ 
	public function addInsurInfo(){
		$strdata=$_POST;
		$air_insur_product=M('air_insur_product');
		$data['tmc_id']=LI('tmcId');
		$data['p_name']=$strdata['p_name'];
		$data['insur_co']=$strdata['insur_co'];
		$data['price']=$strdata['price'];
		$data['cov_amount']=$strdata['cov_amount'];
		$data['descrip']=$strdata['descrip'];
		$data['field']=$strdata['field'];
		if($data['field']="国内"){
			$data['field']=0;
		}
		if($data['field']="国际"){
		$data['field']=1;
		}
		$data['status']=0;
		$request=$air_insur_product->data($data)->add();
		$this->ajaxReturn(($request)? 1:0);		
	}
	
	/**
	 * 删除产品保险信息
	 * Enter description here ...
	 */
	public function deleteInsurInfo(){
		$id=$_POST['id'];
		$air_insur_product=M('air_insur_product');
		$data['status']=99;
		$request=$air_insur_product->where('id='.$id)->save($data);
		$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 根据id查询出产品保险信息
	 * Enter description here ...
	 */
	public function showInsurInfoById(){
		$id=$_POST['id'];
		$air_insur_product=M('air_insur_product');
		$request=$air_insur_product->where('id='.$id)->find();
		$this->ajaxReturn($request);
	}
	/**
	 * 修改产品保险信息
	 * 
	 */
	public function updateInsurInfo(){
		$strdata=$_POST;
		$air_insur_product=M('air_insur_product');
		$id=$strdata['upp_id'];
		$data['p_name']=$strdata['upp_name'];
		$data['insur_co']=$strdata['upinsur_co'];
		$data['price']=$strdata['upprice'];
		$data['cov_amount']=$strdata['upcov_amount'];
		$data['descrip']=$strdata['updescrip'];
		$data['field']=$strdata['upfield'];
		$request=$air_insur_product->where('id='.$id)->save($data);
		$this->ajaxReturn(($request)? 1 : 0);
	}
}
