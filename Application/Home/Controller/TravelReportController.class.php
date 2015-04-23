<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 差旅报告
 * 
 * 
 * @author 余卓燃
 *
 */


class TravelReportController extends Controller {
	
	/**
	 * 用来封装以下所有方法的返回值，然后把全部值封装为数组传给前端
	 * 余卓燃
	 */

	public function inittrreport(){                    //差旅报告初始化查询
		$br_id = -1;
		$today = getdate();
		$endTime =$today['year'].'-'.$today['mon'].'-'.$today['mday'];
		$startTime=date("Y-m-d",strtotime("$endTime   -30   day"));
		if($_POST['barch']){
			$br_id = intval($_POST['barch']);
			$endTime = $_POST['end_date'];
			$startTime = $_POST['start_date'];
		}
    	//$startTime = '2015-01-10';
		//查询近一个月的数据
		$this->assign('br_id',$br_id);
		$branch=M('branch');
		$br_name = $branch->where('id='.$br_id)->field('name')->find();
		$this->assign('br_name',$br_name['name']);
		$this->assign('endTime',$endTime);
		$this->assign('startTime',$startTime);
		//输出部门信息
    	$result = array();
		$user_id=LI('userId');
		$employee= D('employee');
		$co_id= $employee->where('u_id='.$user_id)->getField('co_id');
		
		$branch=D('branch');
		$br_info= $branch->where('co_id='.$co_id)->select();
		$this->assign('brinfo',$br_info);
    	//$result['br_info'] = $br_info;
		//输出费用清单
		$travelFee_total = $this->travelFee_total($co_id,$br_id,$startTime,$endTime);
		//$travelFee_total = $this->travelFee_total();
		$result['total_travel_fee'] = $travelFee_total['total_travel_fee'];
		$this->assign('travelFee',$result['total_travel_fee']);
		
		$o_ids = $travelFee_total['orderIds'];          //这里的$o_ids是数组，符合条件的所有订单o_id
		$result['flight'] = $this->travelFlightFee ($o_ids,$startTime,$endTime);
		
		$this->assign('flightFee',$result['flight']);
		//$this->ajaxReturn ( $result, "JSON" );
		$result['hotel'] = $this->travelHotelFee ($o_ids,$startTime,$endTime);
		$this->assign('hotelFee',$result['hotel']);
		
		$result['other'] = $result['total_travel_fee']-$result['flight']['total_flight_fee']['all']-$result['hotel']['total_hotel_fee'];
		$this->assign('otherFee',$result['other']);
		
		//print_r($result);
		$this->theme('default')->display('tr_report');
	}
	public function trreport(){                    //调用以下方法，封装所有搜索结果
		/*$co_id = $_POST['co_id'];
		$br_id = $_POST['br_id'];
    	$startTime = $_POST['startTime'];
    	$endTime = $_POST['endTime'];*/
		//$co_id = 26;
		//$br_id = 0;
		//$startTime = '2015-01-10';
		//获取部门信息和查询时间
		$endTime =$_POST['end_date'];
		$startTime=$_POST['start_date'];
		$br_id =$_POST['barch'];
		print_r($br_id);
		//输出信息用于部门选择
		$result = array();
		$user_id=LI('userId');
		$employee= D('employee');
		$co_id= $employee->where('u_id='.$user_id)->getField('co_id');
		$branch=D('branch');
		$br_info= $branch->where('co_id='.$co_id)->find();
		$result['br_info'] = $br_info;
		//输出费用表单
		$travelFee_total = $this->travelFee_total($co_id,$br_id,$startTime,$endTime);
		//$travelFee_total = $this->travelFee_total();
		$result['total_travel_fee'] = $travelFee_total['total_travel_fee'];
		$o_ids = $travelFee_total['orderIds'];          //这里的$o_ids是数组，符合条件的所有订单o_id

		$result['flight'] = $this->travelFlightFee ($o_ids,$startTime,$endTime);

		//$this->travelFlightExceptionFee($o_ids,$startTime,$endTime);

		//$this->travelFlightSaveFee ($o_ids,$startTime,$endTime);

		//$result['hotel'] = $this->travelHotelFee ($o_ids,$startTime,$endTime);

		//$this->travelHotelSaveFee ($o_ids,$startTime,$endTime);

		//return $result;
		$this->ajaxReturn($result);
	}

	/**
	 * 某企业的差旅总费用
	 * 可查询整个企业的总费用，或按照所选择的部门来查询
	 * 余卓燃
	 * 
	 */
    /*差旅总费用*/
    public function travelFee_total($co_id,$br_id,$startTime,$endTime){ 
    	//public function travelFee_total(){ 
    	/*$co_id = 1;
    	$br_id = 12;
    	$startTime = '2015-01-22';
    	$endTime = '2015-01-24';*/
    	
    	$result = array();
    	$order = M('order');
    	
    	if($br_id === -1){                                 //$br_id为0表示查看整个企业，没有选择部门
    		$map['co_id'] = $co_id;
    		$period = array(date($startTime),date($endTime));
    		$map['time']  = array('between',$period);
			$map['status'] = array('not in','99,0,6,11,12,19,24');
    		$total_travel_fee = $order->where($map)->sum('amount + service_price');
    		$o_id = $order->where($map)->getField('id',true);
    	}
    	
    	else if($br_id != -1){                                            //$br_id不为0表示查看某个部门，包括下级部门
    		$branchs=array($br_id);                      //存放所选择的部门及所有下级部门br_id的数组
    		$pre_branchs = array($br_id);                //存放上级部门p_id的数组
    		$low_branch = true;
    		$branch = M('branch');
    		
    		while($low_branch != false){                //遍历出所选择的部门及所有下级部门br_id
    		       $map1['p_id'] = array('in',$pre_branchs);
    		       $map1['co_id'] = $co_id;
				   $map1['status'] = array('not in','99,0,6,11,12,19,24');
    			   $low_branch = $branch->where($map1)->getField('id',true);
    			   if($low_branch){
    			   	  foreach($low_branch as $key=>$val){
    			   		  array_push($branchs,$val);
    			   	  }
    			   	  $pre_branchs = $low_branch;
    			   }
    		}
    		
    		//根据所有部门br_id，查询出所有这些部门下的员工
    		$emps=array();                              //存放该部门下的所有员工emp_id的数组
    		$employee = M('employee');
    		
    			$map2['br_id'] = array('in',$branchs);
    		    $map2['co_id'] = $co_id;
    			$employeeIds = $employee->where($map2)->getField('id',true);
    			foreach($employeeIds as $key2=>$employeeId){
    				array_push($emps, $employeeId);
    			}
    		
    		$map['u_id'] = array('in',$emps);
    		$map['co_id'] = $co_id;
    		$period = array(date($startTime),date($endTime));
    		$map['time']  = array('between',$period);
    		$map['status'] = array('not in','99,0,6,11,12,19,24');
    		$total_travel_fee = $order->where($map)->sum('amount + service_price');
    		$o_id = $order->where($map)->getField('id',true);
    	}
    	
    	$result['total_travel_fee'] = $total_travel_fee;
    	$result['orderIds'] = $o_id;
    	return $result;
    	
	}
	
	/**
	 * 查询机票总费用，及各月机票费用
	 * 余卓燃
	 */
	/*机票费用*/
	public function travelFlightFee ($o_ids,$startTime,$endTime){  
	//public function travelFlightFee (){
		/*$o_ids = array(1,4);
		$startTime = '2015-02-11';
    	$endTime = '2015-03-24';*/
		
		$result = array();
		
		//在选定时间内的所有机票总费用
		$flight_ticket_info = M('flight_ticket_info');
		$data['o_id'] = array('in',$o_ids);
		$data['status'] = array('not in','99,0,6,11,12,19,24');
		//$result['total_flight_fee']['all'] = $flight_ticket_info->where($data)->sum('price+baf+acf+tax+service_price');
		$result['total_flight_fee']['ticket'] = $flight_ticket_info->where($data)->sum('price');
		$result['total_flight_fee']['acf'] = $flight_ticket_info->where($data)->sum('acf');
		$result['total_flight_fee']['baf'] = $flight_ticket_info->where($data)->sum('baf');
		$result['total_flight_fee']['package'] = $flight_ticket_info->where($data)->sum('packing_cost');
		$result['total_flight_fee']['service_price'] =$flight_ticket_info->where($data)->sum('service_price');
		$result['total_flight_fee']['change_price'] =$flight_ticket_info->where($data)->sum('change_price');
		$result['total_flight_fee']['all']=$result['total_flight_fee']['ticket']+$result['total_flight_fee']['acf']+$result['total_flight_fee']['baf']+$result['total_flight_fee']['package']+$result['total_flight_fee']['service_price'];
		$air_insur_info = M('air_insur_info');
		$result['total_flight_fee']['insurance'] = $air_insur_info->where($data)->sum('price');
		
		//计算选择查询时间跨越了多少个月
		$start = getdate( strtotime($startTime) );
		$end = getdate( strtotime($endTime) );
		$result['monthGap'] = $this->monthNum_calculation($start['year'],$start['mon'],$end['year'],$end['mon']);

		/**
		 * 计算各月的机票费用详情
		 * 例如选择时间为2015-01-16到2015-3-15
		 * 则计算的是2015-01-16到2015-01-31 && 2015-2-1到2015-02-28 && 2015-03-01到2015-3-15
		 * 
		 */
		for ($k=1;$k<=$result['monthGap'];$k++){                         //$k是指第几个月
			
			if( $k == 1 ){
				if( $result['monthGap'] == 1 ){
					$currentMonthBegin = $startTime;
					$currentMonthEnd = $endTime;
				}
				else{
					$currentMonthBegin = $startTime;
				    $currentMonthFirstDay = date("Y-m-01",strtotime($startTime));
				    $currentMonthEnd = date("Y-m-d", strtotime(" $currentMonthFirstDay + 1 month - 1 day"));	  //计算该月的最后一天是几号
				}
			}
			else if( $k == $result['monthGap'] ){
				$currentMonthBegin = date("Y-m-01",strtotime($endTime));
			    $currentMonthEnd = $endTime;
			}
			else{
				$currentMonthBegin = date("Y-m-01", strtotime(" $currentMonthBegin + 1 month"));
				$currentMonthEnd = date("Y-m-d", strtotime(" $currentMonthBegin + 1 month - 1 day"));
			}
			
			$y_m = date("Y-m",strtotime($currentMonthBegin));
			$map['o_id'] = array('in',$o_ids);
			$map['status'] = array('in','2,14,4,16,9,98,22,18,1,13');
			//$currentMonthBegin = $endYear.'-'.$endMonth.'-01';
			//$currentMonthEnd = $startYear.'-'.$startMonth.'-31';
			//$map1['time_dep']  = array("exp","  BETWEEN $begin AND DATE_SUB($startTime, INTERVAL 1 day)  ");
			//$map['time_dep']  = array('exp',"  BETWEEN '$currentMonthBegin' AND '$currentMonthEnd'  ");	
			$map['time_dep']  = array('exp',"  BETWEEN '$currentMonthBegin' AND '$currentMonthEnd'  ");	
			
			$result['monthly_flight_fee'][$y_m]['ticket'] = $flight_ticket_info->where($map)->sum('price');
			$result['monthly_flight_fee'][$y_m]['acf'] = $flight_ticket_info->where($map)->sum('acf');
			$result['monthly_flight_fee'][$y_m]['baf'] = $flight_ticket_info->where($map)->sum('baf');
			$result['monthly_flight_fee'][$y_m]['package'] = 0;
			$result['monthly_flight_fee'][$y_m]['service_price'] = $flight_ticket_info->where($map)->sum('service_price');
			$result['monthly_flight_fee'][$y_m]['change_price'] = $flight_ticket_info->where($map)->sum('change_price');
			$tag = implode(',',$flight_ticket_info->where($map)->getField('id',true));
			$result['monthly_flight_fee'][$y_m]['insurance'] = $air_insur_info->where("a_id in ( $tag ) ")->sum('price');
			$result['monthly_flight_fee'][$y_m]['all']= $result['monthly_flight_fee'][$y_m]['ticket']+ $result['monthly_flight_fee'][$y_m]['acf']+$result['monthly_flight_fee'][$y_m]['baf']+$result['monthly_flight_fee'][$y_m]['package']+$result['monthly_flight_fee'][$y_m]['service_price']+$result['monthly_flight_fee'][$y_m]['insurance'];
			//$test = $flight_ticket_info->where("date(time_dep)>=DATE_SUB('2015-09-1', INTERVAL 1 MONTH)")->getField('acf',true);
			//$test = $flight_ticket_info->where("date(time_dep)<='2015-09-1'")->getField('acf',true);
			
			$rfmap['o_id'] = array('in',$o_ids);
			$rfmap['status'] = 26;	
			$rfmap['time_dep']  = array('exp',"  BETWEEN '$currentMonthBegin' AND '$currentMonthEnd'  ");	
			$result['monthly_flight_fee'][$y_m]['refund_price'] = $flight_ticket_info->where($rfmap)->sum('refund_price');
			
		}	
		
		//给直方图的月份下标
		$monthStr = array();
		while($key = key($result['monthly_flight_fee'])){
			//printf("%s <br />", $key);
			$key = '\''.$key.'\'';
    		next($result['monthly_flight_fee']);
			array_push($monthStr,$key);
		}
		$monthStr = implode(",",$monthStr);
		$monthStr = "[".$monthStr."]";
		$result['monthStr'] = $monthStr; 
		   
		$flightTicketStr = array();
		$flightAcfStr = array();
		$flightBafStr = array();
		$flightPackageStr = array();
		$flightServiceStr = array();
		$flightChangeStr = array();
		$flightRefundStr = array();
		$flightInsuranceStr = array();
		foreach($result['monthly_flight_fee'] as $mkey =>$val){
			array_push($flightTicketStr, '\''.$val['ticket'].'\'' );
			array_push($flightAcfStr, '\''.$val['acf'].'\'' );
			array_push($flightBafStr, '\''.$val['baf'].'\'' );
			array_push($flightPackageStr, '\''.$val['package'].'\'' );
			array_push($flightServiceStr, '\''.$val['service_price'].'\'' );
			array_push($flightChangeStr, '\''.$val['change_price'].'\'' );
			array_push($flightRefundStr, '\''.$val['refund_price'].'\'' );
			array_push($flightInsuranceStr, '\''.$val['insurance'].'\'' );
		}
		//给直方图的数据
		$flightTicketStr= implode(",",$flightTicketStr);
		$flightAcfStr = implode(",",$flightAcfStr);
		$flightBafStr = implode(",",$flightBafStr);
		$flightPackageStr = implode(",",$flightPackageStr);
		$flightServiceStr = implode(",",$flightServiceStr);
		$flightChangeStr = implode(",",$flightChangeStr);
		$flightRefundStr = implode(",",$flightRefundStr);
		$flightInsuranceStr = implode(",",$flightInsuranceStr);
		$flightTicketStr = "[".$flightTicketStr."]";
		$flightAcfStr = "[".$flightAcfStr."]";
		$flightBafStr = "[".$flightBafStr."]";
		$flightPackageStr = "[".$flightPackageStr."]";
		$flightServiceStr = "[".$flightServiceStr."]";
		$flightChangeStr = "[".$flightChangeStr."]";
		$flightRefundStr = "[".$flightRefundStr."]";
		$flightInsuranceStr = "[".$flightInsuranceStr."]";
		
		
		$result['flightTicketStr'] =$flightTicketStr;
		$result['flightAcfStr'] =$flightAcfStr;
		$result['flightBafStr'] =$flightBafStr;
		$result['flightPackageStr'] =$flightPackageStr;
		$result['flightServiceStr'] =$flightServiceStr;
		$result['flightChangeStr'] =$flightChangeStr;
		$result['flightRefundStr'] =$flightRefundStr;
		$result['flightInsuranceStr'] =$flightInsuranceStr;
		
	
		//dump($result);
		return $result;
    }
	
    
    /*机票异常*/
	public function travelFlightExceptionFee($o_ids,$startTime,$endTime){   //机票异常
		
	}
	
	
	/*机票节约*/
	public function travelFlightSaveFee ($o_ids,$startTime,$endTime){        //机票节约总费用
		
	}

	
	/*酒店费用*/
	//public function travelHotelFee ($o_ids,$startTime,$endTime){  
		  
	public function travelHotelFee (){
		$o_ids = array(1,4);
		$startTime = '2015-02-11';
    	$endTime = '2015-03-24';
		
		$result = array();
		
		//在选定时间内的所有机票总费用
		$hotel_info = M('hotel_info');
		$data['o_id'] = array('in',$o_ids);
		$data['status'] = array('in','98,10,25,3,15,5,8,19');
		//$result['total_flight_fee']['all'] = $flight_ticket_info->where($data)->sum('price+baf+acf+tax+service_price');
		$htresult = $hotel_info->where($data)->field('date_ckin,date_ckout,count,price,service_price,prepay_val,status')->select();
		$allPrice = 0;
		foreach($htresult as $k => $val){
			
			if( $val['status'] == 19 ){
				$onePrice = $val['prepay_val'];
		    	$allPrice +=  $onePrice;
			}else{
				$onePrice = (abs( strtotime($val['date_ckout']) - strtotime($val['date_ckin']) )/86400)*$val['count']*$val['price']+$val['service_price'];
			    $allPrice +=  $onePrice;
			}
		}
		$result['total_hotel_fee']=$allPrice;
		
		return $result;
    
	}
	
	/*酒店节约费用*/
	public function travelHotelSaveFee ($o_ids,$startTime,$endTime){ 
		
	}
	
	
	
	/*******************下面是辅助方法********************/
	
	
	/*选择的查询时间跨越的多少个月*/
	public function monthNum_calculation($startYear,$startMonth,$endYear,$endMonth){
	
		
		if( $endYear - $startYear==0 ){
			$monthGap = $endMonth - $startMonth + 1;
		}
		else if($endYear - $startYear >= 1){
			$monthGap = ( ($endYear - $startYear-1)*12 )+ (12-$startMonth+1)+$endMonth;
		}
		
		return $monthGap;
		
	}
	
	
	public function test(){
		
	}


	
}