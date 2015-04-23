<?php
namespace Agent\Logic;
use Think\Model;

class IndexLogic extends Model{

	//tmc主页
	public function get_mypage_tmc(){
		$tmc_id=LI('tmcId');
		$tmcM=M('tmc');
		$tmc=$tmcM->where('id='.$tmc_id)->find();
		//tmc员工数
		$sql="select COUNT(id) as num from 73go_tmc_employee where tmc_id=".$tmc['id'];
		$empcount=$tmcM->query($sql);
		$tmc['empcount']=$empcount[0]['num'];
		//工作组数
		$sql2="select COUNT(id) as num from 73go_tmc_team where tmc_id=".$tmc['id'];
		$teamcount=$tmcM->query($sql2);
		$tmc['teamcount']=$teamcount[0]['num'];
		//协议客户数
		$sql3="select COUNT(id) as num from 73go_co_tmc_link where tmc_id=".$tmc['id'];
		$linkcount=$tmcM->query($sql3);
		$tmc['linkcount']=$linkcount[0]['num'];
		//查询vip客户数
		$sql4="select COUNT(id) as num from 73go_vip_table where tmc_id=".$tmc['id'];
		$vipcount=$tmcM->query($sql4);
		$tmc['vipcount']=$vipcount[0]['num'];
		//查询tmc旗舰店配置网址
		$sql5="select sub_url from 73go_tmc_siteconfig where tmc_id=".$tmc['id'];
		$urls=$tmcM->query($sql5);
		$tmc['sub_url']=$urls[0]['sub_url'];
		
		return $tmc;
	}
	
	public function get_tmc_bulletin(){
		$tmc_id=LI('tmcId');
		$bulletinM=M('bulletin_mgnt');
		$cond['send_type']=1;
		$cond['send_id']=$tmc_id;
		/*1.20更改gsf*/
		return $bulletinM->where($cond)->order('CAST(LEVEL AS unsigned integer)  DESC,time DESC')->limit(0,30)->select();
	}
	
	//tmc 员工主页
	public function get_mypage_op(){
	//---------------tmc员工的基本信息-------------------------
	 //姓名的查询
	 $tmc_id=LI('tmcId');
	 $tmcempId=LI('tmcempId');
	 $tmc_emp=M('tmc_employee');
	 $tmcc=M("");
	 $data=$tmc_emp->where('id='.$tmcempId)->find();//查询出工号,姓名,电话
	//部门的查询
	$sql="select name from  73go_tmc_branch where id=".$data['tmcbr_id'];
	$branch=$tmcc->query($sql);
	$data['branch']=$branch['0']['name'];
	
	//--------------我的待办-----------------------------------
	//轻松行待接单的数量
		$m_newdemand = new NewDemandLogic();
		$count = $m_newdemand->getNewDemandOfCurrEmp('count');
		$data['rec_count']=$count;

		
	//-----------我的工作组和客户----------------------------
	$memberM=M('tmc_team_member');
	$team_coM=M('tmc_team_co');
	$empM = M('employee');
	$member=$memberM->where('emp_id='.$tmcempId)->select();
	$teamM=M('tmc_team');
		foreach ($member as $key=>$val){
			$Tname="";
			$team_id=$teamM->where('id='.$val['team_id'])->getField('id');
			$m =$memberM->where('team_id='.$team_id)->select();
			$mc =$team_coM->where('team_id='.$team_id)->select();

			$team_person="";
			//$cnt = 0;
			foreach ($m as $k=>$v){
				//if ($cnt++ > 0) $str .= ',';
					$sql31="select name from 73go_tmc_employee where id=".$v['emp_id'];
					$tnames=$memberM->query($sql31);	
					$m[$k]['te_name']=$tnames[0]['name'];
					$team_person.=$tnames[0]['name'].',';
			}
			foreach ($mc as $k=>$c){
					$sql2="select name from 73go_company where id=".$c['co_id'];
					$names=$team_coM->query($sql2);	
					$mc[$k]['c_name']=$names[0]['name'];
				
					//查询企业客户下的人
					$sql1="select id from 73go_employee where co_id=".$c['co_id'];
					$ids=$empM->query($sql1);	
					foreach ($ids as $i){
						$rcond['u_id']=$i['id'];
					}
					
			}
			$sql0="select name from 73go_tmc_team where id=".$val['team_id'];
			$team_names=$team_coM->query($sql0);	
			$member[$key]['team_name']=$team_names[0]['name'];
			
			
			$member[$key]['team_person']=$team_person;
			$member[$key]['tename']=$m;
			$member[$key]['tcname']=$mc;
		}
		
		
		//待处理退改签的数量
	$refundM=M('order_refund_req');
	
	$rcond['status']=0;
	$rcond['tmc_id']=$tmc_id;
		
	$refund=$refundM->where($rcond)->select();
	$count=count($refund);
	$data['refund_count']=$count;		
		
		
	$data['member']=$member;
	//print_r($data);
	return $data;
	
	}
	//查询我的vip 客户
	public function get_vip(){
		$tmc_id=LI('tmcId');
		$op_id=LI('tmcempId');
		$vipM=M('vip_table');
		$vips=$vipM->where('tmc_id='.$tmc_id.' and (op_id is null or op_id='.$op_id.')')->select();//1.26更改
		foreach ($vips as $key=>$val){
			$sql="select co_id,name from 73go_employee where id=".$val['emp_id'];
			$emps=$vipM->query($sql);	
			$vips[$key]['emp_name']=$emps[0]['name'];
			
			$sql2="select name,id from 73go_company where id=".$emps[0]['co_id'];
			$cos=$vipM->query($sql2);	
			$vips[$key]['co_name']=$cos[0]['name'];
			$vips[$key]['co_id']=$cos[0]['id'];

			$val['level']='';
			if($val['vip_level']==1){
				$val['level']='VIP1';
			}else if($val['vip_level']==2){
				$val['level']='VIP2';
			}
			$vips[$key]['level']=$val['level'];

		}
		return $vips;
	}
	
	//------------差旅公告--------------------------------------
	public function get_bulletin(){
		$tmc_id=LI('tmcId');
		$bulletinM=M('bulletin_mgnt');
		
		$cond1['send_type']=1;
		$cond1['send_id']=$tmc_id;
		$cond1['show_enable']=1;
		$cond[] = $cond1;
		
		$cond2['send_type']=0;
		$cond2['recv_type']=1;
		$cond2['recv_id']=0;
		$cond2['show_enable']=1;
		
		$cond[] = $cond2;
		
		$cond['_logic'] = 'OR';
		//dump($tmc_id);
		/*1.20更改gsf*/
		return $bulletinM->where($cond)->order('CAST(LEVEL AS unsigned integer)  DESC,time DESC')->limit(0,30)->select();
	
	}
	
	
	
	
	
}