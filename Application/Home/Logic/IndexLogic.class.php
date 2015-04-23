<?php
namespace Home\Logic;
use Think\Model;


class IndexLogic extends Model {
	
	//企业主页
	public function get_mypage_co(){
		$u_id = LI('userId');
		
		$companyM=M('company');
		$company=$companyM->where("u_id=".$u_id)->find();
		
		//部门数
		$sql="select COUNT(id) as num from 73go_branch where co_id=".$company['id']." AND status =0";
		$brcount=$companyM->query($sql);
		$company['brcount']=$brcount[0]['num'];
		
		//员工数
		$sql2="select COUNT(id) as num from 73go_employee where co_id=".$company['id'];
		$empcount=$companyM->query($sql2);
		$company['empcount']=$empcount[0]['num'];
		
		//协议客户数
		$sql3="select COUNT(id) as num from 73go_co_tmc_link where co_id=".$company['id'];
		$linkcount=$companyM->query($sql3);
		$company['linkcount']=$linkcount[0]['num'];
		
		//差旅政策
		$sql4="select co_id  from 73go_travel_policy where co_id=".$company['id'];
		$tr_co_id=$companyM->query($sql4);
		$company['tr_co_id']=$tr_co_id[0]['co_id'];
		
		//申请加入员工
		$sql5="select COUNT(id) as num from 73go_join_co_request where status=0 and co_id=".$company['id'];
		$joincount=$companyM->query($sql5);
		$company['joincount']=$joincount[0]['num'];
		
		//待创建系统登录用户
		$sql6="SELECT COUNT(emp.id) as num FROM 73go_employee emp 
				LEFT JOIN 73go_user as u ON emp.u_id=u.id 
				WHERE emp.`status`=1 and u.username = '' and co_id=".$company['id'];
		$nempcount=$companyM->query($sql6);
		$company['nempcount']=$nempcount[0]['num'];
		
		
		return $company;
		
	}
	
	public function get_co_bulletin(){
		$co_id=LI('comId');
		$bulletinM=M('bulletin_mgnt');
		$cond['send_type']=2;
		$cond['send_id']=$co_id;
		/*1.20更改gsf*/
		$result = $bulletinM->where($cond)->order('CAST(LEVEL AS unsigned integer)  DESC,time DESC')->limit(0,30)->select();
		
		return $result;
	}
	
	//企业员工主页
	public function get_mypage_user(){
		
		$emp_id=LI('empId');
		$empM=M('employee');
		$emp=$empM->where("id=".$emp_id)->find();
		
		$ouM=M('order_user');
		$order_user=$ouM->where("emp_id=".$emp_id)->select();
//		
		$orderM=M('order');
		
		$flight_ticketM=M('flight_ticket_info');
		$hotelM=M('hotel_info');
		$train_ticketM=M('train_ticket_info');
		$otherM=M('other_produ_info');
		
//		if($_POST['date']){
//			$day=$_POST['date'];
//		}
//		else{
//			$day=date('Y-m-d');//当天
//		}
		
		
		//$time = array('egt',$day);

		foreach ($order_user as $key=>$val){
			$order=$orderM->where('id='.$val['o_id'])->find();
			//机票
//			if($day){
//				$data1['time_dep']=$time;
//			}
			$data1['o_id']=$val['o_id'];
			$data1['status']=20;
			$flight_ticket=$flight_ticketM->where($data1)->select();
			//酒店
//			$data2['date_ckin']=$time;
			$data2['o_id']=$val['o_id'];
			$data2['status']=20;
			$hotel=$hotelM->where($data2)->select();
			
			foreach ($hotel as &$v){
				$v['room']='';
				switch ($v['room_type']){
					case 0:
						$v['room']='标准大床';
						break;
					case 1:
						$v['room']='标准双床';
						break;
					case 2:
						$v['room']='豪华标间';
						break;
					case 3:
						$v['room']='商务大床';
						break;
					case 4:
						$v['room']='商务双床';
						break;
					case 5:
						$v['room']='高级大床';
						break;
					case 6:
						$v['room']='豪华房';
						break;
					case 7:
						$v['room']='商务套间';
						break;
					case 8:
						$v['room']='家庭套间';
						break;
					case 9:
						$v['room']='商务公寓';
						break;
				}
				
				$v['day']=((strtotime($v['date_ckout'])-strtotime($v['date_ckin']))/3600)/24;
				
			}
			//火车票
			//$data3['boarding_time']=$time;
			$data3['o_id']=$val['o_id'];
			$data3['status']=20;
			$train_ticket=$train_ticketM->where($data3)->select();
			
			foreach ($train_ticket as &$t){
				$t['class']='';
				switch ($t['class_level']){
					case 0:
						$t['class']='无座';
						break;
					case 1:
						$t['class']='硬座';
						break;
					case 2:
						$t['class']='硬卧';
						break;
					case 3:
						$t['class']='软卧';
						break;
					case 4:
						$t['class']='高级软卧';
						break;
					case 5:
						$t['class']='二等座';
						break;
					case 6:
						$t['class']='一等座';
						break;
					case 7:
						$t['class']='特等座';
						break;
					case 8:
						$t['class']='商务座';
						break;
				}
			}
			//其他
			//$data4['time_start']=$time;
			$data4['o_id']=$val['o_id'];
			$data4['status']=20;
			$other=$otherM->where($data4)->select();
			
			$order_user[$key]['date']=substr($order['time'],0, 10);
			$order_user[$key]['flight']=$flight_ticket;
			$order_user[$key]['other']=$other;
			$order_user[$key]['train']=$train_ticket;
			$order_user[$key]['hotel']=$hotel;
			
			//print_r($hotel);
		}
		$emp['orders']=$order_user;
		
		
//		//待确认数
//		$map1['status']=6;
//		$map1['u_id']=$emp_id;//预定人
//		$orCount1=$orderM->where($map1)->count();
//		//待支付数
//		$map2['status']=11;
//		$map2['u_id']=$emp_id;//预定人
//		$orCount2=$orderM->where($map2)->count();
//		
//		//待审批数
//		$tr_apprM=M('tr_approval');
//		$cond['status']=0;
//		$cond['appv_id']=$emp_id;
//		$tr_apprCount = $tr_apprM->where($cond)->count();
//		
//		$emp['or_num1']=$orCount1;
//		$emp['or_num2']=$orCount2;
//		$emp['appr_num']=$tr_apprCount;
		
		//print_r($emp);
		//print_r($emp['orders']);
		return $emp;
	}
	
	public function get_count(){
		$emp_id=LI('empId');
		$orderM=M('order');
		//待确认数
		$map1['status']=6;
		$map1['u_id']=$emp_id;//预定人
		$orCount1=$orderM->where($map1)->count();
		//待支付数
		$map2['status']=11;
		$map2['u_id']=$emp_id;//预定人
		$orCount2=$orderM->where($map2)->count();
		
		//待审批数
		$tr_apprM=M('tr_approval');
		$cond['status']=0;
		$cond['appv_id']=$emp_id;
		$tr_apprCount = $tr_apprM->where($cond)->count();
		
		$count['or_num1']=$orCount1;
		$count['or_num2']=$orCount2;
		$count['appr_num']=$tr_apprCount;
		
		return $count;
	}
	//获取差旅公告
	public function get_all_bulletin(){
		$co_id=LI('comId'); 
		$bulletinM=M('bulletin_mgnt');
		
		$cond1['send_type']=2;
		$cond1['send_id']=$co_id;
		$cond1['show_enable']=1;

		$cond[] = $cond1;
		
		
		$linkM=M('co_tmc_link');
		if (isTMCHosting ()) {
			$where['tmc_id']=getHostedTmcInfo ("tmc_id");
		}
		$where['co_id']=$co_id;
		$links=$linkM->where($where)->select();
		foreach ($links as $val){
			$cond2['send_type']=1;
			$cond2['send_id']=$val['tmc_id'];
			$cond2['recv_type']=2;
			$cond2['recv_id']=array('exp','in(0,'.$co_id.')');
			$cond2['show_enable']=1;
			$cond[] = $cond2;
		}
		
		$cond3['send_type']=0;
		$cond3['recv_type']=2;
		$cond3['recv_id']=0;
		$cond3['show_enable']=1;
		
		$cond[] = $cond3;
		$cond['_logic'] = 'OR';
		/*1.20更改gsf*/
			$result = $bulletinM->where($cond)->order('CAST(LEVEL AS unsigned integer)  DESC,time DESC')->limit(0,30)->select();
		
		return $result;
		
	}
}