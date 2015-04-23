<?php
namespace Agent\Logic;
use Think\Model;
use Common\Datasource\Datasource;
/**
 * 抢单业务层
 * 创建者：董发勇
 * 创建时间：2014-12-4下午05:42:36
 *
 *
 */
class NewDemandLogic extends Model{
	/**
	 * 查询出新需求单
	 * 创建者：董发勇
	 * 创建时间：2014-12-4下午06:04:51
	 */
	public function showNewDemandLogic(){
		$qxs_rec=M('');
		$emp_id=LI('tmcempId');
		//查出相应的订单信息
	 	$sql="select a.id as aid,a.*,b.* from 73go_tmc_qsx_rec as a left join 73go_qsx_req as b on  a.req_id=b.id
				where a.emp_id=$emp_id and a.`status`=0 and b.`status`=0";
	 	$request=$qxs_rec->query($sql);			
		return $request;
	}
	
	
	public function showNewDemandLogic1($Page){
		/*
	    if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		*/
		$emp_id=LI('tmcempId');
		$tmc_id=LI('tmcId');

		$qxs_rec=M('');
		$tmc_employee=M('tmc_employee');

/**
		//$tmc_id=$tmc_employee->where("id=".$emp_id)->getField('tmc_id');

		//查出相应的订单信息
	 	$sql = "SELECT a.id as aid, a.*, b.*
FROM 73go_tmc_qsx_rec as a
	LEFT JOIN 73go_qsx_req as b ON a.req_id = b.id
WHERE a.`status`= '0' AND b.`status`= '0' AND
	((a.tmc_id = $tmc_id) OR
	 (a.emp_id = $emp_id) OR
	 (a.rec_emp_id = $emp_id) OR
	 (a.team_id IN (SELECT team_id FROM 73go_tmc_team_member WHERE emp_id = $emp_id))
	)
ORDER BY a.id desc
".$link;
	 	$request=$qxs_rec->query($sql);
 *
 */

		$cond_0['tmcId'] = $tmc_id;
		$cond_0['empId'] = $emp_id;
		$cond_0['Page'] = $Page;
		$request = Datasource::getData('General/newDemand', $cond_0);


	 	//1.20gsf
	 	//从字典表查出设定的过期时间
	 	$cond['d_group']='qsx_set';
	 	$cond['d_key']='req_ot_min';
	 	$dicM=M('dictionary');
	 	$min=$dicM->where($cond)->getField('d_value');
	 	//1.20gsf
		foreach ($request as $k=>$val){
			$minute=0;
			
			$sql4 = "select id,co_id,name from 73go_employee  AS  a 
					WHERE  a.u_id = ".$val['u_id'];
			$coids = $qxs_rec->query($sql4);
			//$request[$k]['co_id'] = $coids[0]['co_id'];
			$request[$k]['emp_name'] = $coids[0]['name'];
			
			$sql2 = "select vip_level from 73go_vip_table  AS  a 
					WHERE  a.tmc_id = ".$tmc_id. " AND a.emp_id=".$coids[0]['id'];
			$levels = $qxs_rec->query($sql2);
			$request[$k]['vip_level'] = $levels[0]['vip_level'];
			
			$sql3 = "select name  from 73go_tmc_employee  AS  a 
					WHERE  a.id = ".$emp_id;
			$names = $qxs_rec->query($sql3);
			$request[$k]['tename'] = $names[0]['name'];
			
			$sql5 = "select short_name  from 73go_company  AS  a 
					WHERE  a.id = ".$coids[0]['co_id'];
			$cnames = $qxs_rec->query($sql5);
			$request[$k]['short_name'] = $cnames[0]['short_name'];
			//计算过期时间1.20 gsf
			$today=date('Y-m-d H:i:s',time());
			$minute=((strtotime($val['submit_time'])-strtotime($today))/60)+$min;
			$minute=floor($minute);
			$request[$k]['minute']=$minute;
		}
		return $request;

	}

	/**
	 * 获取当前OP可处理的新需求列表
	 * @return mixed
	 */
	public function getNewDemandOfCurrEmp($opt='') {
		$cond['tmcId'] = LI('tmcId');
		$cond['empId'] = LI('tmcempId');
		if (empty($opt))
			return Datasource::getData('General/newDemand', $cond);
		else switch (strtolower($opt)) {
			case 'count':
				$cond['__GET_COUNT'] = '1';
				$data = Datasource::getData('General/newDemand', $cond);
				if ($data) {
					return (int) $data[0]['cnt'];
				} else
					return 0;
				break;
		}
	}


}