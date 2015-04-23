<?php
namespace Agent\Logic;
use Think\Model;
use Common\Datasource\Datasource;

class ESLogic extends Model {
	
	/**
	 * 查询轻松行需求单
	 * 创建者：甘世凤
	 * 2014-11-24下午04:37:44
	 */
	public function queryQSXRequest($cond,$Page) {
	    if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		$qs=M('');
		$sql = Datasource::getSQL("ES/esreq", $cond);
		//echo '<pre>'.$sql."</pre><br/>\r";
		$tmc_id = LI('tmcId');
		
		$result=$qs->query($sql.$link);
		foreach ($result as $key=>$val){
			$sql2 = "select count(qs.id) as num  
				FROM 73go_qsx_solution qs 
    			LEFT JOIN 73go_user u  on qs.u_id =u.id 
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON tmc.id=temp.tmc_id
				WHERE tmc.id=$tmc_id AND qs.`status`=0 AND qs.req_id = ".$val['qrid'];
			$count = $qs->query($sql2);
			$result[$key]['num'] = $count[0]['num'];
		}
		//print_r($result);
		return $result; 
	}

	/**
	 * 查询所有轻松行需求单，包括用户已经采纳需要创建订单的需求单和待选择的需求单
	 * 创建者：王月
	 * 2015-3-17
	 */
	public function queryQSXRequestAll($cond,$Page) {
		if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		$qs=M('');
		$userId=LI('userId');
		$tmc_employee=M('tmc_employee');
		$employeeId=$tmc_employee->where('u_id='.$userId)->select();
		$sql = "select tq.id AS tqrid,tq.rec_emp_id,te.`name`,tmc.id AS tmc_id,tmc.`name` AS tmc_name,u.id AS user_id,u.username,com.id AS com_id,com.`name` AS com_name,vt.vip_level,re.*
			FROM 73go_tmc_qsx_rec as tq
			LEFT join 73go_qsx_req as re on tq.req_id=re.id
			LEFT join 73go_tmc_employee te on tq.rec_emp_id=te.id
			LEFT JOIN 73go_tmc tmc ON te.tmc_id=tmc.id
			LEFT JOIN 73go_user u ON re.u_id=u.id
			LEFT JOIN 73go_employee emp ON emp.u_id=re.u_id
			LEFT JOIN 73go_company com ON emp.co_id=com.id
			LEFT JOIN 73go_vip_table vt ON vt.tmc_id = tmc.id and vt.emp_id=emp.id
			WHERE tq.rec_emp_id=".$employeeId[0]['id']." AND tq.`status`=1 AND re.`status` in(1,0)";
		//echo '<pre>'.$sql."</pre><br/>\r";
		$tmc_id = LI('tmcId');

		$result=$qs->query($sql.$link);
		foreach ($result as $key=>$val){
			$sql2 = "select count(qs.id) as num
				FROM 73go_qsx_solution qs
    			LEFT JOIN 73go_user u  on qs.u_id =u.id
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON tmc.id=temp.tmc_id
				WHERE tmc.id=$tmc_id AND qs.req_id = ".$val['id'];
			$count = $qs->query($sql2);
			$result[$key]['num'] = $count[0]['num'];
		}
		//print_r($result);
		return $result;
	}
	/**
	 * 准备添加方案（根据需求单id查询）
	 * 创建者：甘世凤
	 * 2014-11-24下午05:48:25
	 * 修改：David law
	 * 兼容处理已经完成需求处理单的查询（添加参数 $type 默认为真）
	 * 2015-2-4 15:06:32
	 */
	public function preAddQSXScheme($cond,$type=true) {
//		session("req_id",$cond['req_id']);
		$tmc_id = LI('tmcId');
		$qs=M('');
		$sql="SELECT tqr.*,qr.*,te.`name`,u.username,qr.u_id
				FROM 73go_tmc_qsx_rec tqr
				LEFT JOIN 73go_qsx_req qr ON tqr.req_id=qr.id
				LEFT JOIN 73go_tmc_employee te ON tqr.rec_emp_id=te.id
				LEFT JOIN 73go_tmc tmc ON te.tmc_id=tmc.id  
				LEFT JOIN 73go_user u ON qr.u_id=u.id where ".($type ? "qr.`status`=0 and" : "")." tqr.id=".$cond['tqrid'];
		
		$result=$qs->query($sql);
		foreach ($result as $key=>$val){
			$sql = "select count(qs.id) as num  
				FROM 73go_qsx_solution qs 
    			LEFT JOIN 73go_user u  on qs.u_id =u.id 
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON tmc.id=temp.tmc_id
				WHERE tmc.id=$tmc_id AND ".($type ? "qs.`status`=0 AND" : "")." qs.req_id = ".$cond['req_id'];
			$count = $qs->query($sql);
			$result[$key]['num'] = $count[0]['num'];
			
			$sql2 = "select vip_level from 73go_vip_table  AS  a 
					WHERE  a.tmc_id = ".$val['tmc_id'];
			$levels = $qs->query($sql2);
			$result[$key]['vip_level'] = $levels[0]['vip_level'];
			
			$sql3 = "select co_id,name from 73go_employee  AS  a
					WHERE  a.u_id = ".$val['u_id'];
			$names = $qs->query($sql3);
			$result[$key]['emp_name'] = $names[0]['name'];

			$sql4 = "select short_name from 73go_company  AS  a
					WHERE  a.id = ".$names[0]['co_id'];
			$snames = $qs->query($sql4);
			$result[$key]['short_name'] = $snames[0]['short_name'];
		}
		//$sql = Datasource::getSQL("ES/esreq", $cond);
		//echo '<pre>'.$sql."</pre><br/>\r";
		return $result; 
	}
	
	/**
	 * 查询方案信息
	 * 创建者：甘世凤
	 * 2014-11-24下午06:33:54
	 * *修改：David law
	 * 兼容处理已经完成需求处理单的查询（添加参数 $type 默认为真）
	 * 2015-2-4 15:06:32
	 */
	public function queryQSXcheme($Page,$type=true){
//		$req_id=session("req_id");
	    if($Page == 0){
			$link = "";
		}else{
			$link = " order by id desc limit $Page->firstRow , $Page->listRows";
		}
		
		$rec=M('tmc_qsx_rec');
		$rec_id=$_GET['id'];
		$req_id=$rec->where("id=".$rec_id)->getField('req_id');
		
		$tmc_id=LI('tmcId');
		
		$chemeM=M('');
		//echo  $req_id;
//		$sql="select qs.id,qs.content,qs.time,qs.u_id,qs.req_id from 73go_qsx_solution qs 
//    		LEFT JOIN 73go_user u  on qs.u_id =u.id where qs.`status`=0 and qs.req_id=".$req_id;
		$sql="select qs.id,qs.content,qs.time,qs.u_id,qs.req_id 
				FROM 73go_qsx_solution qs 
    			LEFT JOIN 73go_user u  on qs.u_id =u.id 
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON tmc.id=temp.tmc_id
				WHERE tmc.id=$tmc_id and ".($type ? "qs.`status`=0 and" : "")." qs.req_id=$req_id".$link;
		
		$result=$chemeM->query($sql);
		foreach ($result as $key=>$val){
			$sql2 = "select name from 73go_tmc_employee  AS  a 
					WHERE  a.u_id = ".$val['u_id'];
			$names = $chemeM->query($sql2);
			$result[$key]['tmc_emp_name'] = $names[0]['name'];
		};
		return $result; 
	}
	

	/**
	 * 
	 * 创建者：甘世凤
	 * $kind = 1, 返回emp_id列表；=2返回u_id列表
	 * 2015-1-16下午04:31:33
	 */
	public function getEmpOfSameGroup($empId, $kind=1) {
		$cond['emp_id'] = $empId;
		$cond['u_id'] = LI('userId');
		$d = Datasource::getData("ES/samegroup", $cond);
		
		$fld = 'emp_id';
		if ($kind == 2) $fld = 'u_id';
		
		$result = '';
		$idx = 0;
		foreach($d as $rec) {
			if ($idx++ > 0) $result .= ',';
			$result .= $rec[$fld];
		}
		return $result;
	}
	
	//处理完
	public function qsx_req_list_finish($cond,$Page){
	    if($Page == 0){
			$link = "";
		}else{
			$link = " limit $Page->firstRow , $Page->listRows";
		}
		$empId = LI('tmcempId');
		$userId = LI('userId');
		$emp_idlist = $this->getEmpOfSameGroup($empId);
		$emp_idlist = strlen($emp_idlist) == 0 ? $empId :
			$empId.','.$emp_idlist;
		$u_idlist = $this->getEmpOfSameGroup($userId, 2);
		$u_idlist = strlen($u_idlist) == 0 ? $userId :
			$userId.','.$u_idlist; 
		
		$cond['tmcempId']=$empId;
		$cond['u_id'] = $userId;
		
		$cond['qr_searching']=1;
		$cond['sol.`status`']=0;

		
		$cond['u_idlist'] = $u_idlist;
		$cond['emp_idlist'] = $emp_idlist;
		
		$tmc_id = LI('tmcId');
		$qs=M('');
		$sql = Datasource::getSQL("ES/req_finish", $cond);
		
		$result=$qs->query($sql.$link);

		
		foreach ($result as $key=>$val){
			$sql2 = "select count(qs.id) as num  
				FROM 73go_qsx_solution qs 
    			LEFT JOIN 73go_user u  on qs.u_id =u.id 
				LEFT JOIN 73go_tmc_employee as temp ON temp.u_id=u.id
				LEFT JOIN 73go_tmc as tmc ON tmc.id=temp.tmc_id
				WHERE tmc.id=$tmc_id AND qs.req_id = ".$val['qrid'];
			$count = $qs->query($sql2);
			$result[$key]['num'] = $count[0]['num'];
		}
		//echo '<pre>'.$sql."</pre><br/>\r";
		return $result; 
	}
	
}