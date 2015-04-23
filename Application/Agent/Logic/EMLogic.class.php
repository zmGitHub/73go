<?php
namespace Agent\Logic;
use Think\Model;
use Common\Datasource\Datasource;
/**
 * TMC企业信息管理
 * 员工管理Staff
 * 企业管理Enterprise
 * 部门管理Branch
 * 创建者：董发勇
 * 创建时间：2014-11-5下午03:28:46
 *
 */
class EMLogic extends Model {
	/**
	 * 查询VIP客户信息
	 * 创建者：甘世凤
	 * 2014-11-27下午03:08:05
	 */
	function queryVip($cond,$Page){
	    if($Page == 0){
			$limit = "";
		}else{
			$limit = " limit $Page->firstRow , $Page->listRows";
		}
		
		
		$tmcid=LI('tmcId');

		if($cond['type']==1){
			if($cond['con']==1){
				$link="";
			}else{
				$link=" and emp.`name` like '%".$cond['con']."%'";
			}
		}else if($cond['type']==2){
			if($cond['coid']==-1){
				if($cond['level']==0){
					$link = "";
				}else{
					$link = " and vip.vip_level=".$cond['level'];
				}
			}else{
				if($cond['level']==0){
					$link = " and com.id=".$cond['coid'];
				}else{
					$link = " and com.id=".$cond['coid']." and vip.vip_level=".$cond['level'];
    			}
    		}
		}

		$vip=M('');
		/* 1.20更改vip客户gsf */
		$sql="select vip.*,emp.`name` as emp_name,com.`name` as co_name,link.date 
					FROM 73go_vip_table as vip 
					LEFT JOIN 73go_employee as emp ON vip.emp_id=emp.id
					LEFT JOIN 73go_company com ON emp.co_id=com.id
					LEFT JOIN 73go_co_tmc_link as link ON link.co_id=com.id
					WHERE vip.tmc_id=$tmcid AND link.tmc_id=$tmcid AND link.`status`=0 ".$link." ORDER BY link.date DESC".$limit;
		/* 1.20更改vip客户gsf */
		
		
		$result=$vip->query($sql);
//		echo '<pre>'.$sql."</pre><br/>\r";
		return $result;
	}
	
	public function queryEmp(){
		$tmcid=LI('tmcId');
		$emp=M('');
		$sql="select e.*,c.short_name,b.name brname 
				FROM 73go_employee e 
				LEFT JOIN 73go_company c ON e.co_id=c.id
				LEFT JOIN 73go_branch b ON e.br_id=b.id
				LEFT JOIN 73go_co_tmc_link as link ON link.co_id=c.id
				where link.`status`=0 AND e.id not in 
					(select emp_id from 73go_vip_table where tmc_id=$tmcid) ".$link;
		$result=$emp->query($sql);
		return $result;
	}	
}