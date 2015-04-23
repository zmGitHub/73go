<?php

namespace Home\Logic;

use Think\Model;

class FlightLogic extends Model {
	
	/**
	 * 获取航班信息 添加代理商报价信息
	 *
	 * @param $startcity 出发城市        	
	 * @param $tocity 达到城市        	
	 * @param $startdate 出发日期        	
	 * @param $starttime 起飞时段        	
	 * @return json
	 */
	public function getFlightInfos($startcity, $tocity, $startdate, $depTimes = null, $cabin = 'unlimited') {
		/* 调取机票接口 */
		$url = "http://120.24.103.240:8080/73goDataCenter/other/spider_getInfo.do?datasource=unlimited&startdate=" . $startdate . "&returndate=&depTimes=" . $depTimes . "&retTimes=&startcity=" . $startcity . "&tocity=" . $tocity . "&FlightSearchType=oneWay&FlightName=&FlightNo=&cabin=" . $cabin . "&PassengerType=";
		$strBack = file_get_contents ( $url );
		// $strBack = file_get_contents ( "API.json" );
		$data = json_decode ( $strBack );
		$userId = LI ( 'userId' ); // 获取用户id
		$flightArray ['code'] = $data->code;
		$flightArray ['depdate'] = $startdate;
		$search_result = $data->sertch_result;
		$num = count ( $search_result );
 		
		// 航班班次循环
		for($i = 0; $i < $num; $i ++) {
			$tmcList = array ();
			$others = array ();
			$tmcListRevise = array ();
			$resultTmp = array ();
			
			$resultTmp ['FlightNo'] = $search_result [$i]->flightNo;
			$tickets = $search_result [$i]->tickets;
			$count_of_tickets = count ( $search_result [$i]->tickets );
			
			if ($count_of_tickets > 0) {
				$tickets_0 = $search_result [$i]->tickets [0];
				
				$dictionary = M ( 'dictionary' );
				$co_name = $dictionary->where ( "(d_group='cn_airline' OR d_group='un_airline') AND d_key='" . $tickets_0->FlightName . "'" )->find ();
				$airline_co = $co_name ['display_ex']; // 航空公司
				$airline = $tickets_0->FromAirport . '-' . $tickets_0->ToAirport; // 航线
				$flight_num = substr ( $search_result [$i]->flightNo, 2 ); // 航班
				$airport_info = M ( 'airport_info' );
				$airpost_from_name = $airport_info->where ( "t_code='" . $tickets_0->FromAirport . "'" )->find ();
				$airpost_to_name = $airport_info->where ( "t_code='" . $tickets_0->ToAirport . "'" )->find ();
				
				$FromAirport = $airpost_from_name ['name_cn'];
				$ToAirport = $airpost_to_name ['name_cn'];
				
				$resultTmp ['FlightName'] = $airline_co;
				$resultTmp ['dt'] = $tickets_0->dt;
				$resultTmp ['at'] = $tickets_0->at;
				$resultTmp ['FromAirport'] = $FromAirport;
				$resultTmp ['startcity'] = $tickets_0->FromAirport;
				
				$resultTmp ['departureAirportTerminal'] = $tickets_0->departureAirportTerminal;
				$resultTmp ['ToAirport'] = $ToAirport;
				$resultTmp ['tocity'] = $tickets_0->ToAirport;
				
				$resultTmp ['arrivalAirportTerminal'] = $tickets_0->arrivalAirportTerminal;
				$resultTmp ['flightTime'] = $tickets_0->flightTime;
				$resultTmp ['codeshareInd'] = $tickets_0->codeshareInd;
				$resultTmp ['CraftType'] = $tickets_0->CraftType;
				$resultTmp ['operatingAirlineCod'] = $tickets_0->operatingAirlineCod;
				$resultTmp ['operatingAirlineFlightNumber'] = $tickets_0->operatingAirlineFlightNumber;
				$resultTmp ['mealCode'] = $tickets_0->mealCode;
				$resultTmp ['airraxFloat'] = ($tickets_0->airraxFloat !== 'null') ? $tickets_0->airraxFloat : 0;
				$resultTmp ['fuelCosts'] = ($tickets_0->fuelCosts !== 'null') ? $tickets_0->fuelCosts : 0;
				$resultTmp ['stop'] = $tickets_0->stop;
				$resultTmp ['OtherCabin'] = $tickets_0->OtherCabin;
				
			}
			// 数据来源循环
			for($j = 0; $j < $count_of_tickets; $j ++) {
				
				$count_of_priceInfos = count ( $search_result [$i]->tickets [$j]->priceInfos );
				// 各报价循环
				for($k = 0; $k < $count_of_priceInfos; $k ++) {
					
					reset ( $tmcList_tmp );
					reset ( $others_tmp );
					$priceInfos = $search_result [$i]->tickets [$j]->priceInfos [$k];
					
					if ($priceInfos->agent == 'ibe') {
						$key_tmc = $this->array_search_value ( $priceInfos->cabin, $tmcList, "cabin" );
						
						if ($key_tmc === false) {
							$tmcList_tmp ['id'] = null;
							$tmcList_tmp ['name'] = null;
							$tmcList_tmp ['identification'] = null;
							$tmcList_tmp ['starts'] = null;
							$tmcList_tmp ['serviceCost'] = 0;
							$tmcList_tmp ['cabin'] = $priceInfos->cabin;
							$tmcList_tmp ['resBookDesigQuantity'] = $priceInfos->resBookDesigQuantity;
							$tmcList_tmp ['discount'] = $priceInfos->discount;
							$tmcList_tmp ['rfn'] = $priceInfos->rfn != 'null' ? $priceInfos->rfn : '';
							$tmcList_tmp ['rrn'] = $priceInfos->rrn != 'null' ? $priceInfos->rrn : '';
							$tmcList_tmp ['edn'] = $priceInfos->edn != 'null' ? $priceInfos->edn : '';
							$tmcList_tmp ['price'] = $priceInfos->price;
							$tmcList_tmp ['agent'] = $priceInfos->agent;
							$tmcList [] = $tmcList_tmp;
						} else if ($priceInfos->price < $tmcList [$key_tmc] ['price']) {
							$tmcList [$key_tmc] ['resBookDesigQuantity'] = $priceInfos->resBookDesigQuantity;
							$tmcList [$key_tmc] ['discount'] = $priceInfos->discount;
							$tmcList [$key_tmc] ['rfn'] = $priceInfos->rfn != 'null' ? $priceInfos->rfn : '';
							$tmcList [$key_tmc] ['rrn'] = $priceInfos->rrn != 'null' ? $priceInfos->rrn : '';
							$tmcList [$key_tmc] ['edn'] = $priceInfos->edn != 'null' ? $priceInfos->edn : '';
							$tmcList [$key_tmc] ['price'] = $priceInfos->price;
						}
					} elseif ($priceInfos->agent == 'ctrip') {
						
						$key_other = $this->array_search_value ( $priceInfos->cabin, $others, "cabin" );
						if ($key_other === false) {
							$others_tmp ['type'] = '携程旅行';
							$others_tmp ['cabin'] = $priceInfos->cabin;
							$others_tmp ['discount'] = $priceInfos->discount;
							$others_tmp ['rfn'] = $priceInfos->rfn != 'null' ? $priceInfos->rfn : '';
							$others_tmp ['rrn'] = $priceInfos->rrn != 'null' ? $priceInfos->rrn : '';
							$others_tmp ['edn'] = $priceInfos->edn != 'null' ? $priceInfos->edn : '';
							// 退改签政策
							$others_tmp ['price'] = $priceInfos->price;
							$others_tmp ['url'] = 'http://www.ctrip.com/';
							$others [] = $others_tmp;
						} else if ($priceInfos->price < $others [$key_other] ['price']) {
							$others [$key_other] ['discount'] = $priceInfos->discount;
							$others [$key_other] ['rfn'] = $priceInfos->rfn;
							$others [$key_other] ['rrn'] = $priceInfos->rrn;
							$others [$key_other] ['edn'] = $priceInfos->edn;
							$others [$key_other] ['price'] = $priceInfos->price;
						}
						
					}
				} // 各报价循环
			} // 数据来源循环
			  
			// 根据参照数据和TMC设置，重组TMC信息
			$count_of_tmclist = count ( $tmcList );
			//获取TMC商家
			$request_tmc_link = $this->get_tmc_infos($userId);
			
			for($m = 0; $m < $count_of_tmclist; $m ++) {
				foreach ( $request_tmc_link as $key => $value ) {
					unset ( $get_data_map );
					$tmcListReviseTmp = $tmcList [$m];
					$tmcListReviseTmp ['id'] = $value ['id'];
					$tmcListReviseTmp ['name'] = $value ['name'];
					$tmcListReviseTmp ['identification'] = $value ['cert_val'];
					
					// 服务费
					$get_data_map ['tmc_id'] = $value ['id'];
					$get_data_map ['co_id'] = $co_id;
					$get_data_map ['price'] = $tmcListReviseTmp ['price'];
					
					$get_data_map ['airline_co'] = $airline_co; // 航空公司
					$get_data_map ['airline'] = $airline; // 航线
					$get_data_map ['flight_num'] = $flight_num; // 航班
					$get_data_map ['class'] = $tmcListReviseTmp ['cabin'];
					$get_data_map ['type'] = 0;
					
					$tmcListReviseTmp ['serviceCost'] = $this->get_serv_price ( $get_data_map );
					
					// 退改签政策
					$content = $this->get_refund_policy ( $get_data_map );
					$tmcListReviseTmp ['content'] = $content ['content'];
					
					// 机票价格
					$tmcListReviseTmp ['price'] = $this->get_ft_policy ( $get_data_map );
				}
				
				// 本次航班最低票价
				if ($tmcListReviseTmp ['price'] < $resultTmp ['price'] || $resultTmp ['price'] == 0) {
					$resultTmp ['price'] = $tmcListReviseTmp ['price'];
					$resultTmp ['serviceCost'] = $tmcListReviseTmp ['serviceCost'];
				}
				$tmcListRevise [] = $tmcListReviseTmp;
			}
			
			$resultTmp ['tmcList'] = $tmcListRevise;
			$resultTmp ['otherList'] = $others;
			// 如果没有ibe数据则不显示
			if ($tmcListRevise){
				$result [] = $resultTmp;
				
			}
		} // 航班班次循环
		$flightArray ['result'] = $result;
		
		// $flightJson = json_encode ( $flightArray );
// print_r($flightArray);
		return $flightArray;
	}
	
	/**
	 * 获取某航班其他仓位的报价并格式化对应的TMC报价
	 * 
	 * @param $startcity 出发机场三字码      	
	 * @param $tocity 到达机场三字码  
	 * @param $dt 出发时间
	 * @param $at 到达时间    	
	 * @param $flightnum 航班号      	
	 * @param $depdate 出发日期        	
	 * @param $cabins 仓位        	
	 * @return json
	 */
	public function getOtherCabinPrice($startcity,$tocity,$dt,$at,$flightNum, $depDate, $cabins) {
		/* 调取机票接口 */
		
		$head = " DIRECT ONLY";
		$depDate = explode("-",$depDate);
		$time = mktime(0, 0, 0, $depDate[1], $depDate[2], $depDate[0]);
		$head = date("dM(D)",$time)." ".$startcity.$tocity.$head;
		
		$jsonInfo ['Head'] = $head;
		$jsonInfo ['FlightNo'] = $flightNum;
		$jsonInfo ['Cabins'] = $cabins;
		$jsonInfo ['Voyage'] = $startcity.$tocity;
		$jsonInfo ['DepartureTime'] = $dt;
		$jsonInfo ['ArrivalTime'] = $at;
		$jsonParm = urlencode(json_encode ( $jsonInfo ));
		$url = "http://120.24.103.240:8080/73goDataCenter/data/spider_moreCabins.do?jsonParm=".$jsonParm;
		$strBack = file_get_contents ( $url );
		//$strBack = file_get_contents ( "Cabins.json" );
		$data = json_decode ( $strBack,true );
		$priceInfos = $data['sertch_result']['priceInfos'];
		$FlightNo = $data['sertch_result']['FlightNo'];
		$count = count($priceInfos);
		//获取TMC商家
		$request_tmc_link = $this->get_tmc_infos(LI ( 'userId' ));
		
		for($i=0;$i<$count;$i++){
			foreach ( $request_tmc_link as $key => $value ) {
				unset ( $get_data_map );
				$tmcListReviseTmp = $priceInfos [$i];
				$tmcListReviseTmp ['id'] = $value ['id'];
				$tmcListReviseTmp ['name'] = $value ['name'];
				$tmcListReviseTmp ['identification'] = $value ['cert_val'];
                $tmcListReviseTmp ['FlightNo'] = $flightNum; // 航班
				// 服务费
				$get_data_map ['FlightNo'] = $FlightNo;
				$get_data_map ['tmc_id'] = $value ['id'];
				$get_data_map ['co_id'] = $co_id;
				$get_data_map ['price'] = $tmcListReviseTmp ['price'];
					
				$get_data_map ['airline_co'] = $airline_co; // 航空公司
				$get_data_map ['airline'] = $airline; // 航线

				$get_data_map ['class'] = $tmcListReviseTmp ['cabin'];
				$get_data_map ['type'] = 0;
					
				$tmcListReviseTmp ['serviceCost'] = $this->get_serv_price ( $get_data_map );
					
				// 退改签政策
				$content = $this->get_refund_policy ( $get_data_map );
				$tmcListReviseTmp ['content'] = $content ['content'];
					
				// 机票价格
				$tmcListReviseTmp ['price'] = $this->get_ft_policy ( $get_data_map );
			}
			$tmcListRevise [] = $tmcListReviseTmp;
		}
		
		return $tmcListRevise;
	}
	/**
	 * 获取某航班具体信息
	 *
	 * @param $flightnum 航班号
	 * @param $depdate 出发日期
	 * @param $cabin 仓位
	 * @return json
	 */
	public function getFlightInfo($flightNum, $depDate = null, $cabin = null) {
		/* 调取机票接口 */
		$jsonInfo ['FlightNum'] = $flightNum;
		$jsonInfo ['depDate'] = $depDate;
		$jsonInfo ['cabin'] = $cabin;
		$jsonInfo = json_encode ( $jsonInfo );
	
		$url = "http://120.24.103.240:8080/73goDataCenter/other/spider_flightInfo.do?jsonInfo=" . $jsonInfo;
		$strBack = file_get_contents ( $url );
		$data = json_decode ( $strBack );
	
		$airport_info = M ( 'airport_info' );
		$airpost_from_name = $airport_info->where ( "t_code='" . $data->FromAirport . "'" )->find ();
		$airpost_to_name = $airport_info->where ( "t_code='" . $data->ToAirport . "'" )->find ();
		$data->FromAirport = $airpost_from_name['name_cn'];
		$data->ToAirport = $airpost_to_name['name_cn'];
		return $data;
	}
	
	function get_tmc_infos($userId){
		
		// 如果通过旗舰店进入，则指定该旗舰店企业为唯一TMC企业
		if (isTMCHosting ()) {
			$siteinfo = getHostedTmcInfo ();
			$request_tmc_link [0] ['id'] = $siteinfo ['tmc_id'];
			$request_tmc_link [0] ['name'] = $siteinfo ['name'];
		} else {
			// 有协议TMC，则取协议TMC
			$join_co_request = M ( "join_co_request" );
			$where ['u_id'] = $userId;
			$where ['status'] = 1;
			$find_join_co = $join_co_request->where ( $where )->find ();
			$co_id = $find_join_co ['co_id'];
		
			$co_tmc_link = M ( "co_tmc_link" );
			$request_tmc_link = $co_tmc_link->query ( "select c.id,c.name,c.cert_val from 73go_co_tmc_link t,73go_tmc c where t.co_id=" . $co_id . " and t.status=0 and c.status=0 and t.tmc_id=c.id" );
		
			// 没有协议TMC，则取已认证的TMC商家信息
			if (empty ( $request_tmc_link )) {
				$user = M ( "" );
				$request_user = $user->query ( "select id,name,cert_val from  73go_tmc  where  status=0 and cert_val" );
				$request_tmc_link = $request_user;
			}
		}
		return $request_tmc_link;
	}
	// 匹配指定条件的退改签政策
	function get_refund_policy($data) {
		// 设置字段优先级
		$priority = array (
				'co_id' => 10000,
				'airline_co' => 1,
				'airline' => 10,
				'flight_num' => 1000,
				'class' => 100 
		);
		
		$time = date ( "Y-m-d H:i:s" );
		$price = $data ['price'];
		$serv_price = 0;
		$map ['tmc_id'] = $data ['tmc_id'];
		$where ['co_id'] = array (
				'exp',
				' IS NULL OR co_id=' . $data ['co_id'] 
		);
		$where ['airline_co'] = array (
				'exp',
				' IS NULL OR airline_co=' . $data ['airline_co'] 
		);
		$where ['airline'] = array (
				'exp',
				' IS NULL OR airline=' . $data ['airline'] 
		);
		$where ['flight_num'] = array (
				'exp',
				' IS NULL OR flight_num=' . $data ['flight_num'] 
		);
		$where ['class'] = array (
				'exp',
				' IS NULL OR class=' . $data ['class'] 
		);
		
		$where ['_logic'] = 'or';
		$map ['_complex'] = $where;
		$map ['time_start'] = array (
				'ELT',
				$time 
		);
		$map ['time_end'] = array (
				'EGT',
				$time 
		);
		$where ['type'] = $data ['type'];
		$map ['status'] = 0;
		$tmc_refund_policy = M ( 'tmc_refund_policy' );
		$result = $tmc_refund_policy->field ( 'co_id,airline_co,airline,flight_num,class,content' )->where ( $map )->select ();
		$index = $this->hit_from_array ( $result );
		
		$content = $result [$index] ['content'];
		
		return $content;
	}
	
	// 匹配指定条件的机票价格
	function get_ft_policy($data) {
		// 设置字段优先级
		$priority = array (
				'co_id' => 10000,
				'airline_co' => 1,
				'airline' => 10,
				'flight_num' => 1000,
				'class' => 100 
		);
		
		$time = date ( "Y-m-d H:i:s" );
		$price = $data ['price'];
		$serv_price = 0;
		$map ['tmc_id'] = $data ['tmc_id'];
		$where ['co_id'] = array (
				'exp',
				' IS NULL OR co_id=' . $data ['co_id'] 
		);
		$where ['airline_co'] = array (
				'exp',
				' IS NULL OR airline_co=' . $data ['airline_co'] 
		);
		$where ['airline'] = array (
				'exp',
				' IS NULL OR airline=' . $data ['airline'] 
		);
		$where ['flight_num'] = array (
				'exp',
				' IS NULL OR flight_num=' . $data ['flight_num'] 
		);
		$where ['class'] = array (
				'exp',
				' IS NULL OR class=' . $data ['class'] 
		);
		
		$where ['_logic'] = 'or';
		$map ['_complex'] = $where;
		$map ['time_start'] = array (
				'ELT',
				$time 
		);
		$map ['time_end'] = array (
				'EGT',
				$time 
		);
		$where ['type'] = $data ['type'];
		$map ['status'] = 0;
		$tmc_ft_policy = M ( 'tmc_ft_policy' );
		$result = $tmc_ft_policy->field ( 'co_id,airline_co,airline,flight_num,class,val_opp,val_abs' )->where ( $map )->select ();
		$index = $this->hit_from_array ( $result );
		
		$val_opp = $result [$index] ['val_opp'];
		$val_abs = $result [$index] ['val_abs'];
		if ($val_opp) {
			$price = $price - $price * $val_opp;
		} else {
			$price = $price - $val_abs;
		}
		return $price;
	}
	
	// 匹配指定条件的服务费
	function get_serv_price($data) {
		// 设置字段优先级
		$priority = array (
				'co_id' => 10 
		);
		
		$time = date ( "Y-m-d H:i:s" );
		$price = $data ['price'];
		$serv_price = 0;
		$map ['tmc_id'] = $data ['tmc_id'];
		$where ['co_id'] = array (
				'exp',
				' IS NULL OR co_id=' . $data ['co_id'] 
		);
		$where ['_logic'] = 'or';
		$map ['_complex'] = $where;
		$map ['time_start'] = array (
				'ELT',
				$time 
		);
		$map ['time_end'] = array (
				'EGT',
				$time 
		);
		$map ['status'] = 0;
		$serv_price_config = M ( 'serv_price_config' );
		$result = $serv_price_config->field ( 'co_id,val_opp,val_abs' )->where ( $map )->select ();
		$index = $this->hit_from_array ( $result, $priority );
		
		$val_opp = $result [$index] ['val_opp'];
		$val_abs = $result [$index] ['val_abs'];
		if ($val_opp) {
			$serv_price = $price * $val_opp;
		} else {
			$serv_price = $val_abs;
		}
		
		return $serv_price;
	}
	
	// 查找二维数组$array中的$key下标值为$value的元素
	function array_search_value($value, $array, $key) {
		$count = count ( $array );
		for($i = 0; $i < $count; $i ++) {
			if ($value == $array [$i] [$key]) {
				return $i;
			}
		}
		return false;
	}
	
	// 从二维数组中查找数据优先级最高的数据，返回下标值
	function hit_from_array($array, $priority) {
		$index = 0;
		$mate = 0;
		$count = count ( $array );
		for($i = 0; $i < $count; $i ++) {
			$m = 0;
			foreach ( $array [$i] as $key => $value ) {
				if ($value) {
					$m += $priority [$key];
				}
			}
			if ($m > $mate) {
				$mate = $m;
				$index = $i;
			}
		}
		return $index;
	}
}