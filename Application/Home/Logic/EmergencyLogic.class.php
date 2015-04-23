<?php

namespace Home\Logic;

use Think\Model;

class EmergencyLogic extends Model{

	public function emergency_book(){
	
			$Policy=M("travel_policy");			
			$data['co_id']=LI('comId');
			//$data['emp_id']=LI('empId');
			$request=$Policy->where($data)->find();
			/*if($request){
				$booking=$request['emergemcu_boking'];
			}else{
				$datate['co_id']=LI('comId');
				$datate['emp_id']=array('exp','IS NULL');
				$request=$Policy->where($datate)->find();
				$booking=$request['emergency_booking'];
			}*/
			return  $request;
	
	}

}