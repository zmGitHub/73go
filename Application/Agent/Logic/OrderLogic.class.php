<?php
namespace Agent\Logic;
use Think\Exception;
use Think\Model;

/**
 * 订单
 * Enter description here ...
 * @author xiaogan
 *
 */
class OrderLogic extends Model {
	/**
	 * 查询订单
	 * 创建者：甘世凤
	 * 2014-12-18下午03:10:09
	 */
	public function querylistorder(){
		
		$u_id=LI('userId');
		$tmc_emp_id=LI('tmcempId');
		$tmc_id=LI('tmcId');
		$M = M('');
		
		$sql="SELECT qs.id as qid,qs.content as qcontent,qs.time as qtime,re.id as rid,re.*,vip.vip_level,
				qs.u_id as quid,us.*,em.*,em.name as emp_name FROM 73go_qsx_solution qs
				LEFT JOIN 73go_qsx_req as re ON qs.req_id = re.id 
				LEFT JOIN 73go_user as us ON re.u_id = us.id
				LEFT JOIN 73go_employee as em ON us.id = em.u_id
				LEFT JOIN 73go_vip_table as vip ON em.id = vip.emp_id
				WHERE qs.`status`=1 AND re.`status`=1 AND qs.u_id=".$u_id."
				ORDER BY vip.vip_level DESC,re.submit_time DESC";
		
//		echo $sql;
		$reult = $M->query($sql);
		foreach ($reult as $k=>$val){
//			$sql2 = "select count(id) as num from 73go_qsx_solution  AS  a 
//					WHERE  a.req_id = ".$val['rid'];
			$sql2 = "select count(qs.id) as num
				FROM 73go_qsx_solution qs 
    			LEFT JOIN 73go_user u  on qs.u_id =u.id 
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON tmc.id=temp.tmc_id
				WHERE tmc.id=$tmc_id AND qs.req_id = ".$val['rid'];
			$count = $M->query($sql2);
			$reult[$k]['count'] = $count[0]['num'];
			
			$sql3 = "select name  from 73go_tmc_employee  AS  a 
					WHERE  a.id = ".$tmc_emp_id;
			$names = $M->query($sql3);
			$reult[$k]['tename'] = $names[0]['name'];
			
			$sql4 = "select short_name  from 73go_company  AS  a 
					WHERE  a.id = ".$val['co_id'];
			$cnames = $M->query($sql4);
			$reult[$k]['short_name'] = $cnames[0]['short_name'];
			
		}
		return  $reult;
	}
	
	
	/**
	 * 添加
	 * (non-PHPdoc)
	 * @see Think.Model::add()
	 */
	public function add($table, $data) {
    	$M = M($table); //实例化模型类
    	$lastId = $M->add($data);
    	return $lastId;
	}
	
	public function fakeOrderData($orderId,$kind) {
	 	$str = '';
		$flight_ticket=M('flight_ticket_info');
		$res=$flight_ticket->where('o_id='.$orderId)->select();
		if($kind==1){
	 		foreach ($res as $k=>$v){
	 			$str .='{
	 				"kind":1,
	 				"recId":$v["id"],
        			"pnr":$v["pnr"],
        			"dep_date":substr($v["time_dep"],0,10),
        			"dep_time":substr($v["time_dep"],10,6),
        			"arv_time":substr($v["time_arv"],10,6),
        			"flight_no":$v["airline_num"],
        			"class":$v["class"],
        			"city_from":$v["city_from"],
        			"city_arv":$v["city_to"],
        			"price":$v["price"],
        			"service_price":$v["service_price"],
        			"baf":$v["baf"],
        			"acf":$v["acf"],
        			"tax":$v["tax"],
        			"can_return":$v["refund_enable"],
        			"can_change":$v["reschdule_enable"],
        			"can_sign":$v["resign_enable"],
        			"tmc_note":$v["tmc_note"],
        			"refund_policy":$v["refund_policy"],
	 			},';
	 		
	 		}
	 		return $str;
	 	}
	}
	
	/**
	 * 根据订单号，判断是独立的订单还是联合订单。
	 * 创建者：Lanny Lee
	 * 
	 * @return int 结果类型，0=>什么都不是，1=>订单，2=>联合订单。
	 * 2014-12-24下午02:01:04
	 */
	public function findOrderKind($orderNum) {
		$kind = 0;
		if ($this->m_order->isOrder($orderNum)) $kind = 1;
		else {
			if ($this->m_orderUnion->isUnionOrder($orderNum)) $kind = 2;
		}
		return $kind;
	}
	
	private $m_order = null; //OrderModel
	private $m_orderUnion; //OrderUnionModel
	private $m_orderUser;
	private $m_flightTicketInfo;
	private $m_hotelInfo;
	private $m_trainTicketInfo;
	private $m_otherProduInfo;
	private $m_airInsurInfo;

	private $orderInitStatus = "6"; //待确认

	/**
	 * 记录原先的有意义的数据。
	 * 比如：src(来源)，src_id(来源的id)，co_id(预订企业ID)，u_id(预订人的用户id)
	 * @param $prevOdrData
	 * @param $prevOrder
	 */
	private function savePrevMeaningData(&$prevOdrData, $prevOrder) {
		if ($prevOrder) {
			$prevOdrData['src'] = $prevOrder['src'];
			$prevOdrData['src_id'] = $prevOrder['src_id'];
			if ($prevOrder['co_id'])
				$prevOdrData['co_id'] = $prevOrder['co_id'];
			if ($prevOrder['u_id'])
				$prevOdrData['u_id'] = $prevOrder['u_id'];

		}
	}
	
	/**
	 * 为修改订单执行预处理
	 * 创建者：Lanny Lee
	 * 2014-12-25上午01:21:26
	 */
	private function handlePrevData(&$orderData) {
		if ($orderData['prevData']) {
			//如果存在prevData这一项，说明这是一次修改
			$prevOdrData = &$orderData['prevData'];
			
			$prevOrderNum = $prevOdrData['order_num'];
			if ($prevOrderNum) {
				//按给出的订单号，找出到底是独立订单，还是联合订单。
				$oKind = $this->findOrderKind($prevOrderNum);
				if ($oKind != 0) $orderData['prevData']['isOrderUnion'] = $oKind == 2;
				//0=>什么都不是，1=>订单，2=>联合订单
				switch ($oKind) {
				case 0:
					unset($prevOdrData['order_num']);
					//既然订单号是不存在的，那么数据中的recId也就应该无效了
					unset($orderData['recId']);
			 		break;
				case 1:
					$prevOrder = $this->m_order->getByOrderNumber($prevOdrData['order_num']);
					if (!isset($orderData['recId'])) $orderData['recId'] = $prevOrder['id'];					
					$this->savePrevMeaningData($prevOdrData, $prevOrder);
					break;
				case 2:		
					$prevOrderU = $this->m_orderUnion->getByOrderNumber($prevOdrData['order_num']);
					if (!isset($orderData['recId'])) $orderData['recId'] = $prevOrderU['id'];
					if ($prevOrderU) {
						$ouId = $prevOrderU['id'];
						$prevOrders = $this->m_order->getByOUId($ouId);
						if ($prevOrders) {
							$this->savePrevMeaningData($prevOdrData, $prevOrders[0]);
						} 
					}
					break;
				}
			} 
		}
	}

	/**
	 * 删除某个订单相关的详情以及其所有下级信息。（级联删除）
	 * @param $odrId
	 */
	public function deleteOrderRelatedRecords($odrId) {
		//这些关联Model删除的过程中还会删除其关联数据
		$this->m_otherProduInfo->deleteByOrderId($odrId);
		$this->m_hotelInfo->deleteByOrderId($odrId);
		$this->m_trainTicketInfo->deleteByOrderId($odrId);
		$this->m_flightTicketInfo->deleteByOrderId($odrId);
		//<--end
		$this->m_orderUser->deleteByOrderId($odrId);
	}
	
	/**
	 * 删除指定id的订单。
	 * 创建者：Lanny Lee
	 * 2014-12-25上午02:55:05
	 */
	public function deleteOrderById($id) {
		$this->deleteOrderRelatedRecords($id);
		//仅从订单表删除记录
		$this->m_order->deleteById($id);
		/**
		 * 我这里这么写，是不想在订单Model中重复创建各种关联数据的Model实例。
		 *                       -- 李浩源
		 */
	}

	/**
	 * 创建代用的Model
	 * 创建者：Lanny Lee
	 */
	private function createOrderModels() {
		if (!$this->m_order) {
			$this->m_order = D('Agent/Order');
			$this->m_orderUnion = D('Agent/OrderUnion');
			$this->m_orderUser = D('Agent/OrderUser');
			$this->m_flightTicketInfo = D('Agent/FlightTicketInfo');
			$this->m_hotelInfo = D('Agent/HotelInfo');
			$this->m_trainTicketInfo = D('Agent/TrainTicketInfo');
			$this->m_otherProduInfo = D('Agent/OtherProduInfo');
			$this->m_airInsurInfo = D('Agent/AirInsurInfo');
		}
	}

	//找到在前端编辑过程中被删除的订单并执行真正的删除
	private function delOrdersDeletedByUI($orderData) {
		//如果正在修改联合订单
		//找到在前端编辑过程中被删除的订单并执行真正的删除
		$recIds = array();

		//这里规定前端送来的数据，若$order['recId']为空或者=0，则是在前端编辑的时候新增的订单
		foreach($orderData['orders'] as $order) {
			if ($order['recId'] > 0) { //如果订单的recId有效，表示这是从数据库装入的订单
				$recIds[] = $order['recId'];
			}
		}
		$cond = array();
		$cond['uodr_id'] = $orderData['recId'];
		if (!empty($recIds))
			$cond['id'] = array('not in', $recIds);
		//get ids for delete
		$idRecs4del = $this->m_order->where($cond)->field('id')->select();
		foreach($idRecs4del as $idRec) {
			$this->deleteOrderById($idRec['id']);
		}
		//<--end
	}


	/**
	 * UI与DB之间的布尔类型数值转换。
	 * @param $val
	 * @param int $dir  1=>UI->DB, 2=>DB->UI
	 * @return null|void
	 */
	private function boolValueTransfer($val, $dir=1) {
		switch ($dir) {
			case 1: //UI->DB
				return ($val == 'true') ? '1' : '0';
			case 2; //DB->UI
				return ($val == '1') ? true : false;
		}
		return null;
	}



	/**
	 * 保存飞机票详情。
	 * 飞机票详情的$data大致结构如下：
	 *    pnr: xxx,
	 *    o_id: xxx,
	 *    flights: [
	 * 		{}  //===> $king=1时，应有1条；$kind=2时，应有两条；$king=3时应有三条
	 *    ],
	 * 	  insures: [
	 *    ]
	 * @param $data
	 * @param $kind
	 * @param $oIndex
	 */
	private function saveFlightTicketInfo($data, $kind, $oIndex) {
		$tickets = $data['flights'];
		$commData = array (
			"o_id"=>$data["o_id"],
			"pnr"=>$data["pnr"],
			"refund_policy"=>$data["refund_policy"],
			"tmc_note"=>$data["tmc_note"]
		);
		switch ((int) $kind) {
			case 1:
				$commData["seg_type"] = "10"; //单程
				break;
			case 2:
				if (count($data["flights"]) == 2) {
					if ($data["flights"][0]["city_from"] === $data["flights"][1]["city_to"] &&
						$data["flights"][0]["city_to"] === $data["flights"][1]["city_from"]) {
						$commData["seg_type"] = "21"; //往返
					} else if ($data["flights"][0]["city_from"] != $data["flights"][1]["city_to"] &&
						$data["flights"][0]["city_to"] === $data["flights"][1]["city_from"]) {
						$commData["seg_type"] = "22"; //中转
					} else
						$commData["seg_type"] = "20"; //无特征两航段
				} else //如果航段的数量不是2个，则当做单程的机票进行保存
					$commData["seg_type"] = "10"; //单程
				break;
			case 3:
				if (count($data["flights"]) == 3) {
					$commData["seg_type"] = "30"; //普通三航段
				} else //如果航段的数量不是3个，则当做单程的机票进行保存
					$commData["seg_type"] = "10"; //单程
				break;
		}
		$odIds = array();
		if ($tickets) {
			foreach ($tickets as $ticket) {
				$ticket = array_merge($ticket, $commData);
				$ticket['time_dep'] = $ticket['dep_date']." ".$ticket['dep_time'];
				//跨日的情况，这里会有问题！
				$ticket['time_arv'] = $ticket['dep_date']." ".$ticket['arv_time'];
				//执行布尔数值转换，UI->DB
				$ticket['refund_enable'] = $this->boolValueTransfer($ticket['refund_enable']);
				$ticket['reschdule_enable'] = $this->boolValueTransfer($ticket['reschdule_enable']);
				$ticket['resign_enable'] = $this->boolValueTransfer($ticket['resign_enable']);
				//保存机票信息，并将保存好的id按航班号码记录在$odIds数组中
				$odIds[$ticket['airline_num']] = $this->commonSaveOrderDetail(
					$this->m_flightTicketInfo, $ticket, $oIndex++);
			}
		}

		$insures = $data["insures"];
		if ($insures) {
			$m_insure = M('');
			foreach ($insures as $insure) {
				//如果这条记录的airline_num与机票的不对应，该如何处理？
				$insure['time'] = date('Y-m-d H:i:s',time());
				$insure['a_id'] = $odIds[$insure['airline_num']];
				$insure['o_id'] = $data["o_id"];
				$this->commonSaveOrderDetail($this->m_airInsurInfo, $insure);
			}
		}
	}

	/**
	 * 通用订单详情保存方法。
	 * 假定$data已经具有足够的数据，仅需要增加o_index(若$oIndex存在)和status两个通用的值即可保存。
	 * @param $model 执行保存的Model实例
	 * @param $data 组装好的数据
	 * @param $oIndex 详情o_index基线
	 * @return mixed 新增的订单详情的数据id
	 */
	private function commonSaveOrderDetail($model, $data, $oIndex='') {
		//按model的实际数据结构过滤获取数据
		$detail = $model->create($data, 3);
		//加入通用的字段内容
		if (!($oIndex === ''))
			$detail['o_index'] = $oIndex;
		$detail['status'] = $this->orderInitStatus;
		//保存，并返回新增的id
		return $model->data($detail)->add();
	}

	/**
	 * 保存酒店订单详情。
	 * @param $data 界面传来的数据，注意，此时里面的'o_id'应该已经被注入
	 * @param $oIndex 详情记录的o_index数据
	 */
	private function saveHotelInfo($data, $oIndex) {
		//此处采用通用保存方法即可。
		$this->commonSaveOrderDetail($this->m_hotelInfo, $data, $oIndex);
	}

	/**
	 * 保存火车票订单详情。
	 * @param $data 界面传来的数据，注意，此时里面的'o_id'应该已经被注入
	 * @param $oIndex 详情记录的o_index数据
	 */
	private function saveTrainTicketInfo($data, $oIndex) {
		$t = $data['boarding_time'];
		$data['boarding_time'] = $t.' '.$data['time'];
		$data['arrive_time'] = $t.' '.$data['time_arv'];

		$dtB = date_create_from_format('Y-m-d H:i', $data['boarding_time']);
		$dtA = date_create_from_format('Y-m-d H:i', $data['arrive_time']);

		if ($dtA < $dtB) {
			date_modify($dtA, '+1 day');
			$data['arrive_time'] = date('Y-m-d H:i', $dtA);
		}

		$this->commonSaveOrderDetail($this->m_trainTicketInfo, $data, $oIndex);
	}

	/**
	 * 保存其他订单详情。
	 * @param $data 界面传来的数据，注意，此时里面的'o_id'应该已经被注入
	 * @param $oIndex 详情记录的o_index数据
	 */
	private function saveOtherProduInfo($data, $oIndex) {
		//此处采用通用保存方法即可。
		$this->commonSaveOrderDetail($this->m_otherProduInfo, $data, $oIndex);
	}


	private function getPaytype($coId) {
		if (empty($coId)) return '0';
		$m = M('co_tmc_link');
		$cond['co_id'] = $coId;
		$cond['tmc_id'] = LI('tmcId');
		$rec = $m->where($cond)->find();
		if ($rec) return $rec['pay_type'];
		else return '0';
	}

	private function setOrderInitStatus($payType, &$d='') {
		if ($payType == '0') $this->orderInitStatus = '11';
		if ($payType == '1') $this->orderInitStatus = '6';

		if (!empty($d)) {
			$d['status'] = $this->orderInitStatus;
		}

	}

	private function getTmcEmpName($tmcempId) {
		$cond['id'] = $tmcempId;
		$m = M('tmc_employee');
		$rec = $m->where($cond)->find();
		if ($rec) return $rec['name'];
		return '';
	}

	/**
	 * 根据UI送来的数据保存订单数据。
	 * 支持若$uiData['recId']有效，则为修改，否则为新增。
	 *
	 * @param mixed $uiData
	 * @author Lanny Lee
	 */
	public function saveOrderByUIData($uiData) {
		//根据字段映射获取可用的内容。
		$data = $this->create($uiData, 3);
		$oId = null;
		$data['time'] = date('Y-m-d H:i:s',time());
		$data['tmc_id'] = LI('tmcId');
		$data['tmc_uid'] = LI('tmcempId');
		$data['tmc_uname'] = $this->getTmcEmpName($data['tmc_uid']);
		$data['pay_type'] = '0'; //缺省为直付
		if ($data['co_id']) {
			$payType = $this->getPaytype($data['co_id']);
			$data['pay_type'] = $payType;
		}
		$this->setOrderInitStatus($payType, $data);
		if ($uiData['recId'] && $uiData['recId'] > 0) {
			//如果recId有效，标识这是该修改的订单；
			$data['id'] = $uiData['recId'];
			if (empty($data['order_num']))
				$data['order_num'] = VNumGen('order');
			$this->m_order->data($data)->save();
			//清除这个订单相关的信息
			$this->deleteOrderRelatedRecords($data['id']);
		} else {
			//该增加的订单
			if (empty($data['order_num'])) {
				$data['order_num'] = VNumGen('order');
			} else { //Double Check订单号是否有效
				$data['order_num'] = VNumCheck('order', $data['order_num']);
			}
			$uiData['recId'] = $this->m_order->data($data)->add();
		}
		//获得保存好的订单记录id
		$oId = $uiData['recId'];

		//增加订单使用者信息
		foreach ($uiData['users'] as $userData) {
			$data = $this->m_orderUser->create($userData, 3);
			$data['o_id'] = $oId;
			$data['status'] = '0'; //这里的状态，是固定的状态，代表使用者当前没有具体的状态，是为“无效状态”
			$this->m_orderUser->data($data)->add();
		}

		$i = 0;
		foreach ($uiData['parts'] as $part) {
			$oIndex = $i++ * 10;
			$kind = $part['kind'];
			$part['o_id'] = $oId;
			switch ((int) $kind) {
				case 1: //单航段机票
				case 2: //两航段机票
				case 3: //三航段机票
					$this->saveFlightTicketInfo($part, $kind, $oIndex);
					break;
				case 5: //酒店
					$this->saveHotelInfo($part, $oIndex);
					break;
				case 6: //火车票
					$this->saveTrainTicketInfo($part, $oIndex);
					break;
				case 7: //其他
					$this->saveOtherProduInfo($part, $oIndex);
					break;
			}
		}
	}

	private function saveOrders(&$orderData, $ouId='') {
		$hasPrev = false;
		if ($orderData['prevData']) $hasPrev = true;
		//针对每个订单的数据，执行订单的增加或者修改
		foreach ($orderData['orders'] as $order) {
			//如果传入的联合订单id不为空，则给订单的数据赋值
			if (!empty($ouId)) $order['uodr_id'] = $ouId;
			//导入prev数据
			if ($hasPrev) {
				$order['src'] = $orderData['prevData']['src'];
				$order['src_id'] = $orderData['prevData']['src_id'];
			}
			//保存
			$this->saveOrderByUIData($order);
		}
	}

	private function saveOrderUnion(&$orderData) {
		$ouId = '';
		if (count($orderData['orders']) > 1) {
			$uOrderData = $this->m_orderUnion->create($orderData, 3);
			//TODO 处理amount
			if (!isset($uOrderData['order_num']))
				$uOrderData['order_num'] = VNumGen('order', true);
			if (!isset($uOrderData['amount']))
				$uOrderData['amount'] = 0;
			//增加联合订单
			$ouId = $this->m_orderUnion->data($uOrderData)->add();
		}
		$this->saveOrders($orderData, $ouId);
	}

	public function saveOrder($orderData) {
		$result = array (
			'error'=>0
		);

		/**
		//从登录信息中获取tmc以及员工
		$emp_id = LI('tmcempId');
		$tmc_id = LI('tmcId');
		 */
		
		//创建待用的Model
		$this->createOrderModels();
		//设定基准状态
		$this->orderInitStatus = "6"; //待确认
		if ($orderData['pay_type'] == "0")
			$this->orderInitStatus = "11"; //待支付
		//如果这是待修改的订单，则进行一些预处理
		$this->handlePrevData($orderData);

		//开启事务处理
		$this->startTrans();
		try {
			if (isset($orderData['prevData']) && $orderData['prevData']['isOrderUnion']) {
				//如果正在修改联合订单
				$prevOdrData = $orderData['prevData'];
				//找到在前端编辑过程中被删除的订单并执行真正的删除
				$this->delOrdersDeletedByUI($orderData);
				if (count($orderData['orders']) == 1) {
					//订单从联合订单 变化到 独立订单了
					$theOrder = $orderData['orders'][0];
					unset($theOrder['uodr_id']);
					//从保存的数据中提取'src'和'src_id'
					if ($prevOdrData['src'])
						$theOrder['src'] = $prevOdrData['src'];
					if ($prevOdrData['src_id'])
						$theOrder['src_id'] = $prevOdrData['src_id'];
					if ($prevOdrData['co_id'])
						$theOrder['co_id'] = $prevOdrData['co_id'];
					if ($prevOdrData['u_id'])
						$theOrder['u_id'] = $prevOdrData['u_id'];
					//如果是新增的数据，没有'status'
					if (!isset($theOrder['status'])) $theOrder['status'] = $this->orderInitStatus;
					$this->saveOrderByUIData($theOrder);
					//不需要此联合订单了。
					$this->m_orderUnion->deleteById($orderData['recId']);
				} else {
					//保存联合订单的修改
					$uOrderData = $this->m_orderUnion->create($orderData, 3);
					$uOrderData['id'] = $orderData['recId'];
					//TODO 处理amount
					$this->m_orderUnion->data($uOrderData)->save();
					//保存订单数据
					$this->saveOrders($orderData);
				}
			} else {
				//自动判断并保存成联合订单或者独立订单
				$this->saveOrderUnion($orderData);
			}
			//一切正常，则Commit
			$this->commit();
			
//			//取出订单号，查询出相关的信息；
//			$num=$orderData['orders'][0]['order_num'];
//			$co_id=$orderData['orders'][0]['co_id'];
//			$u_id=$orderData['orders'][0]['u_id'];
//			$employee=M("employee");
//			$emp_info=$employee->where("id=".$u_id)->find();
//
//			// 轻松预定生成订单后进行短信和邮箱的发送;
//			// 需要传的参数有：
//			// 提交人的邮箱 （$data['emp_phone']）, 提交人的电话（$data['emp_email']）;
//			// 1->提交人的姓名($data['emp_name']),2->TMC的公司($data['tmc_name']),
//			// 3->航班出发的时间($data['begin_time'])，4->航班的内容($data['flight_content']);
//			$send=D("Home/SendMessage","Logic");
//			$case="QsxBook";
//			$datt['emp_email']=$emp_info['email'];
//			$datt['emp_phone']=$emp_info['phone'];
//			$datt['emp_name']= $emp_info['name'];
//			$datt['begin_time'] = date ( 'Y-m-d H:i:s', time () );
//			$datt['flight_content']=$num;
//			$send->SendDetails($case,$datt);

			
		} catch (Exception $ex) {
			//若有错，执行rollback
			$this->rollback();
			$result['error'] = 500;            //保存不成功
			$result['errMsg'] = $ex->getMessage() ;  //错误信息
		}
		return $result; 
	}

	private function changeKeyTo(&$data, $key, $to) {
		$data[$to] = $data[$key];
		unset($data[$key]);
	}

	/**
	 * 为了给UI数据而做的调整。
	 * 主要是把'id'换成'recId'，并且拿掉一些不必要传输到前端的字段。
	 * @param $order 订单数据
	 */
	private function orderDataAlter4UI(&$order) {
		$this->changeKeyTo($order, 'id', 'recId');
		//拿掉非必要的字段
		//??疑问??
		//这些字段必须要拿掉吗？或许，从界面保存回来的时候，可能有用？？
		unset(
			$order['uodr_id'],
			$order['pay_type'],
			$order['tmc_id'],
			$order['tmc_uid'],
			$order['tmc_uname'],
			$order['status']
		);
	}

	/**
	 * 订单详情后台数据转化成前端所用的数据，通用转化方法。
	 * 从数据库中取得的数据，拿掉
	 *   "o_id", "p_id", "status"等记录；"id"转换成"recId"
	 * 按现有设计，此通用转化方法可支持“火车票”、“酒店”以及“其他”三种类型的转化。
	 * @param $king 详情类型：酒店=>5，火车票=>6，其他=>7
	 * @param $data
	 */
	private function commonTransferOrderDetail($kind, $data) {
		$result = $data;
		$result['kind'] = $kind;
		$result['recId'] = $data['id'];
		unset(
			$result['id'],
			$result['o_id'],
			$result['p_id'],
			$result['status']
		);
		return $result;
	}



	private function transferFlightTicketInfo($d) {
		$result = $d;
		$result['kind'] = 1;
		$result['recId'] = $d['id'];
		unset(
			$result['id'],
			$result['o_id'],
			$result['p_id'],
			$result['status']
		);
		return $result;
	}

	/**
	 * 数据库的数据转化为前端显示可用的数据。
	 * @param $detailData
	 * @param $kind
	 * @return 转换后的数组
	 */
	private function transferDetailData($detailData, $kind) {
		//目前所知，采用通用办法可以覆盖所有类型的转换。
		return $this->commonTransferOrderDetail($kind, $detailData);
	}


	/**
	 * 把详情数据丢进统一的数组。该数组以(int) (o_index / 10)为key。
	 * 可重组每张订单的part。
	 * @param $details 统一放置所有详情的数组。
	 * @param $detailData 指定类型的详情的所有数据记录
	 * @param $kind 详情数据的类型
	 */
	private function putOrderDetail(&$details, &$detailData, $kind) {
		foreach($detailData as $aDetail) {
			$d2put = $this->transferDetailData($aDetail, $kind);
			if ($d2put) {
				$d2put['kind'] = $kind;
				if (isset($d2put['o_index'])) {
					//按(int) (o_index / 10) 安排
					$aIndex = (int) (((int) ($d2put['o_index'])) / 10);
					if (!isset($details[$aIndex])) $details[$aIndex] = array();
					$details[$aIndex][] = $d2put;
				}
			}
		}
	}

	/**
	 * 组装机票类型的订单部分。包括单程机票/两航段机票/三航段机票等。
	 * 组装的原则是：
	 *   两航段机票，$partSource必须count==2，而且seg_type='2X'
	 *   三航段机票，$partSource必须count==3，而且seg_type='3X'
	 *   其他所有的情况，都会将$partSource中的一条记录组装成一条单程机票
	 * @param $orderParts 订单的parts数组容器
	 * @param $kind 订单part类型，机票分别是1,2和3，对应单程、两航段和三航段
	 * @param $partSource 包含多条机票信息的内容
	 */
	private function assembleFlightTicketPart(&$orderParts, $kind, $partSource) {
		//强制转换成整数
		$kind = (int) $kind;
		if (count($partSource) != $kind) { //这种情况正常不应该存在；
			//如果数量不等于$kind，则强制转换成N条单程机票
			for ($i = 0; $i < count($partSource); $i++) {
				$this->assembleFlightTicketPart($orderParts, 1, array($partSource[$i]));
			}
			return;
		}
		//将公用字段提取出来，只采用第一条数据即可
		//ftPart -> flight ticket part
		$ftPart = array (
			'kind'=>$kind,
			'pnr'=>	$partSource[0]['pnr'],
			'refund_policy'=>$partSource[0]['refund_policy'],
			'tmc_note'=>$partSource[0]['tmc_note']
		);
		//装进flight ticket part中，如果有保险数据，同时装入关联的保险数据。
		foreach($partSource as $flightInfo) {
			if ($kind === 1 && $flightInfo['seg_type'] != '10')
				$flightInfo['seg_type'] = '10';
			if ($flightInfo['time_dep']) {
				$flightInfo['dep_date'] = substr($flightInfo['time_dep'], 0, 10);
				$flightInfo['dep_time'] = substr($flightInfo['time_dep'], 11, 5);
				if ($flightInfo['time_arv'])
					$flightInfo['arv_time'] = substr($flightInfo['time_arv'], 11, 5);
			}
			//执行布尔数值转换，DB->UI
			$flightInfo['refund_enable'] = $this->boolValueTransfer($flightInfo['refund_enable'], 2);
			$flightInfo['reschdule_enable'] = $this->boolValueTransfer($flightInfo['reschdule_enable'], 2);
			$flightInfo['resign_enable'] = $this->boolValueTransfer($flightInfo['resign_enable'], 2);
			//去除不需要的字段
			unset(
				$flightInfo['kind'],
				$flightInfo['pnr'],
				$flightInfo['refund_policy'],
				$flightInfo['tmc_note'],
				$flightInfo['time_dep'],
				$flightInfo['time_arv']
			);
			$ftPart['flights'][] = $flightInfo;

			//组装相应的保险数据
			$insureData = $this->m_airInsurInfo->getByFlightTicketInfoId($flightInfo['recId']);
			if ($insureData) {
				$insureData['recId'] = $insureData['id'];
				$insureData['airline_num'] = $flightInfo['airline_num'];
				//去除不必要的字段
				unset(
					$insureData['id'],
					$insureData['a_id'],
					$insureData['o_id'],
					$insureData['ou_id'],
					$insureData['p_id'],
					$insureData["time"],
					$insureData['tmc_note'],
					$insureData['status']
				);
				if (!isset($ftPart['insures'])) $ftPart['insures'] = array();
				$ftPart['insures'][] = $insureData;
			}
		}
		//加入到order.parts中
		$orderParts[] = $ftPart;
	}

	public function getOrderEditingLevel($status) {
		if (empty($status)) return 0;
		if ($status == "6" || $status == "11" || $status == "12") {
			return 0;
		} else return 1;
	}


	/**
	 * 组装订单的用户数据
	 * @param $order
	 */
	private function assembleOrderUser(&$order) {
		$cond['o_id'] = $order['recId'];
		$hasUsers = $this->m_orderUser->where($cond)->select();
		if ($hasUsers) {
			$users = array();
			foreach ($hasUsers as $userRec) {
				$this->changeKeyTo($userRec, 'id', 'recId');
				//拿掉o_id和status
				//??疑问??
				//status是否应该拿掉？还是说，最终存盘时，若订单是装入的，则需要保存这个装入的'status'?
				unset(
					$userRec['o_id'],
					$userRec['status']
				);
				$users[] = $userRec;
			}
			$order['users'] = $users;
		}
	}

	/**
	 * 和详情数据。
	 * @param $order
	 */
	private function assembleOrderParts(&$order) {
		$odrId = $order['recId'];
		//抓取所有详情信息
		$flightTicketInfos = $this->m_flightTicketInfo->selectByOrderId($odrId);
		$tranTicketInfos = $this->m_trainTicketInfo->selectByOrderId($odrId);
		$hotelInfos = $this->m_hotelInfo->selectByOrderId($odrId);
		$otherInfos = $this->m_otherProduInfo->selectByOrderId($odrId);

		//按原始界面输入part重组详情数据
		$details = array();
		$this->putOrderDetail($details, $flightTicketInfos, 1);
		$this->putOrderDetail($details, $hotelInfos, 5);
		$this->putOrderDetail($details, $tranTicketInfos, 6);
		$this->putOrderDetail($details, $otherInfos, 7);

		if (count($details) > 0) { //具有详情
			ksort($details, SORT_NUMERIC); //按 partIndex 进行排序
			$orderParts = array();
			//此时已经按照订单的各原始部分重新整理好了。
			foreach ($details as $partIndex=>$partDetails) {
				$kind = $partDetails[0]['kind'];
				if ($kind == 1) {
					//飞机票需要组合成“单程”、“两航段”、“三航段”等三种形态
					$segType = $partDetails[0]['seg_type'];
					if ($segType === '10') { //单程机票或者
						$this->assembleFlightTicketPart($orderParts, 1, $partDetails);
					} else {
						//seg_type 必须是 "2X" 或者 "3X"
						$nmatches = preg_match_all('/^([2-3])[0-9]$/', $segType, $matches);
						if ($nmatches) {
							$nCount = (int) $matches[1][0];
							switch ($nCount) {
								case 2: //两航段飞机票
								case 3: //三航段飞机票
									$this->assembleFlightTicketPart($orderParts, $nCount, $partDetails);
									break;
							}
						} else //所有不满足上述条件的情况，全部编制成单程机票
							$this->assembleFlightTicketPart($orderParts, 1, $partDetails);
					}
				} else {
					//酒店、火车票、其他都只会具有1条详情记录
					if ($kind == 6) {
						//火车票需要组装时间
						$bt = $partDetails[0]['boarding_time'];
						$partDetails[0]['boarding_time'] = substr($bt, 0, 10);
						$partDetails[0]['time'] = substr($bt, 11, 5);
						$partDetails[0]['time_arv'] = substr($partDetails[0]['arrive_time'], 11, 5);
					}
					//($kind == 5 || $kind == 6 || $kind == 7)
					$orderParts[] = $partDetails[0];
				}
			}
			//写入到订单数据中
			$order['parts'] = $orderParts;
		}
	}

	/**
	 * 为传输到UI而打包数据。
	 * 这里规定传进来的是订单号。
	 * @param $orderNum 订单号
	 * @return array 生成UI可用的数据，本方法返回的是个array，传回给浏览器的数据应该采用json_encode处理成为json字符串。
	 */
	public function buildOrderUIData($orderNum) {
		$dataOut = array();
		//创建待用的Model
		$this->createOrderModels();
		$oKind = $this->findOrderKind($orderNum);
		//给到的订单号，什么都不是？不应该发生这种情况。
		if ($oKind == 0) return $dataOut;
		switch ($oKind) {
			case 1: //独立订单数据打包
				$order = $this->m_order->getByOrderNumber($orderNum);
				$dataOut['editingLevel'] = $this->getOrderEditingLevel($order['status']);
				$dataOut['canEdit'] = $dataOut['editingLevel'] < 2;
				//调整成适合UI所使用。
				$this->orderDataAlter4UI($order);

				//注入用户和详情数据。
				$this->assembleOrderUser($order);
				$this->assembleOrderParts($order);
				if ($order) $dataOut['orders'] = array($order);
				break;
			case 2: //联合订单数据打包
				$orderUnion = $this->m_orderUnion->getByOrderNumber($orderNum);
				if ($orderUnion) {
					$dataOut['recId'] = $orderUnion['id'];
					$dataOut['order_num'] = $orderNum;
					$dataOut['amount'] = $orderUnion['amount'];
					$orders = $this->m_order->getByOUId($orderUnion['id']);
					if ($orders) {
						$dataOut['editingLevel'] = $this->getOrderEditingLevel($orders[0]['status']);
						$dataOut['canEdit'] = $dataOut['editingLevel'] < 2;
						$dataOut['orders'] = array();
						foreach($orders as $order) {
							//调整成适合UI所使用。
							$this->orderDataAlter4UI($order);
							//注入用户和详情数据。
							$this->assembleOrderUser($order);
							$this->assembleOrderParts($order);
							$dataOut['orders'][] = $order;
						}
					}
				}
				break;
		}
		return $dataOut;
	}

	/**
	 * 获得订单显示所需的方案内容。
	 * 除了指定的solution内容，还增加了req数组，里面包含了co_id(预订企业)和u_id(预订的员工)
	 * @author Lanny Lee
	 * @param $soluId 方案id
	 * @return mixed 若查找有效，则返回数据，包含该id对应的方案记录，并增加了相应的需求来源内容
	 */
	public function getOrderSolutionSource($soluId) {
		$solu=M('qsx_solution');
		$solu_rec = $solu->where('id='.$soluId)->find();
		if ($solu_rec) {
			//获得提交人信息
			$m_emp = M('tmc_employee');
			$emp_rec = $m_emp->where('u_id=' . $solu_rec['u_id'])->find();
			if ($emp_rec)
				$solu_rec['emp_name'] = $emp_rec['name'];
			$solu_rec['sid'] = $soluId;
			//找回需求id
			$m_req = M('qsx_req');
			$req_rec = $m_req->where('id=' . $solu_rec['req_id'])->find();
			if ($req_rec && $req_rec['u_id']) {
				//获得提需求的人员的co_id和emp_id=>u_id
				$m_emp = M('employee');
				$emp_rec = $m_emp->where('u_id=' . $req_rec['u_id'])->find();
				if ($emp_rec) {
					$solu_rec['req'] = array(
						'co_id' => $emp_rec['co_id'],
						'u_id' => $emp_rec['id']
					);
				}
			}
			return $solu_rec;
		}
		return null;
	}

	/**
	 * 数据维护函数。
	 * 清除那些详情数据中o_index为空的数据。
	 */
	public function cleanDataWithoutOindex() {
		$this->createOrderModels();
		$ids = $this->query("
SELECT DISTINCT o_id FROM 73go_flight_ticket_info WHERE o_index IS NULL
UNION
SELECT o_id FROM 73go_hotel_info WHERE o_index IS NULL
UNION
SELECT o_id FROM 73go_train_ticket_info WHERE o_index IS NULL
UNION
SELECT o_id FROM 73go_other_produ_info WHERE o_index IS NULL");
		foreach ($ids as $oid) {
			$this->deleteOrderById($oid['o_id']);
			echo 'Deleted order id = '.$oid['o_id']."<br/>\r";
		}

		$ids = $this->query("
SELECT id FROM 73go_order WHERE order_num IN (
SELECT order_num FROM 73go_order
GROUP BY order_num
HAVING COUNT(order_num) > 1)");
		if ($ids) {
			foreach($ids as $idrec) {
				$this->deleteOrderById($idrec['id']);
				echo 'Deleted order id = '.$idrec['id']."<br/>\r";
			}
		}


	}

	
}