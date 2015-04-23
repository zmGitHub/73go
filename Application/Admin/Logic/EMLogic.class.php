<?php
namespace Admin\Logic;
use Think\Model;
use Common\Datasource\Datasource;

/**
 * TMC企业信息管理
 * 员工管理Staff
 * 企业管理Enterprise
 * 部门管理Branch
 * Enter description here ...
 * @author xiaogan
 *
 */
class EMLogic extends Model {
	/**
	 * 显示企业信息
	 * 创建者：甘世凤
	 * 2014-11-24下午07:18:40
	 */
	public function queryEnterprise(){
		$sql="select c.id,c.co_code,u.register_time,c.`name`,c.province,c.city,
		(select COUNT(e.id) from 73go_employee e where e.u_id=u.id) num,c.`status` 
		from 73go_company c LEFT JOIN 73go_user u ON c.u_id=u.id ";
		return $this->query($sql);
	}
	/**
	 * 根据企业id查询企业员工
	 * 创建者：甘世凤
	 * 2014-11-24下午07:27:14
	 */
	public function queryEmployee($cond,$Page){
		
	    if($Page == 0){
			$limit = "";
		}else{
			$limit = " limit $Page->firstRow , $Page->listRows";
		}
		
		if($cond['type']==1){
			$link=" and e.co_id = ".$cond['id'];
		}else if($cond['type']==2){
			if($cond['co_id']==-1){
				if($cond['status']==null||$cond['status']==''){
					$link = "";
				}else{
					$link = "and e.status=".$cond['status'];
				}
			}else{
				if($cond['status']==null||$cond['status']==''){
					$link = " and e.co_id=".$cond['co_id'] ;
				}else{
					$link = " and e.co_id=".$cond['co_id']." and e.status=".$cond['status'] ;
				}
			}
		}else if($cond['type']==3){
			if($cond['cname']=="所有企业"){
				if($cond['sta']==null||$cond['sta']==''){
					$link = "";
				}else{
					$link = "and e.status=".$cond['sta'];
				}
			}else{
				if($cond['sta']==null||$cond['sta']==''){
					$link=" and c.`name` like '%".$cond['cname']."%' ";
				}else{
					$link = "and e.status=".$cond['sta']." and c.`name` like '%".$cond['cname']."%' ";
    			}
			}
		}
		$sql="select e.id,e.co_id,c.co_code,e.`name` ename,b.`name` bname,e.role,e.province,e.city,e.`status`
			from 73go_employee e 
			LEFT JOIN 73go_company c ON e.co_id=c.id  
			LEFT JOIN 73go_branch b ON e.br_id=b.id 
			where 1=1 ".$link.$limit;
		//echo '<pre>'.$sql."</pre><br/>\r";
		return $this->query($sql);
	}
	/**
	 * 显示TMC企业信息
	 * 创建者：甘世凤
	 * 2014-11-26上午11:52:41
	 */
	public function queryTMCEnterprise($cond,$Page){
	    if($Page == 0){
			$limit = "";
		}else{
			$limit = " order by t.id desc limit $Page->firstRow , $Page->listRows";
		}
		
		if($cond['type']==1){
			$link=" order by t.cert_val";
		}
		$sql="select t.id,t.tmc_code,u.register_time,t.`name`,t.province,t.city,
				(select COUNT(te.id) from 73go_tmc_employee te where te.tmc_id=t.id) tenum,
				(select COUNT(ctl.id) from 73go_co_tmc_link ctl where ctl.tmc_id=t.id) ctlnum,
				t.cert_val,t.`status` from 73go_tmc t,73go_user u where t.u_id=u.id ".$link.$limit;
		//echo '<pre>'.$sql."</pre><br/>\r";
		return $this->query($sql);
	}
	/**
	 * TMC企业的员工信息
	 * 创建者：甘世凤
	 * 2014-11-26上午11:53:37
	 */
	public function queryTMCStaff($cond,$Page = 0){
		
	    if($Page == 0){
			$limit = "";
		}else{
			$limit = " limit $Page->firstRow , $Page->listRows";
		}
		
		if($cond['type']==1){
			if($cond['id']==-1){
				$link="";
			}else{
				$link=" and te.tmc_id=".$cond['id'];
			}
		}else if($cond['type']==2){
			if($cond['name']=='所有企业'){
				$link="";
			}else{
				$link=" and tmc.`name` like '%".$cond['name']."%'";
			}
		}
		$sql="select te.id,te.tmc_id,tmc.tmc_code,te.`name` tename,tb.`name` bname,te.province,te.city,
			te.`status` from 73go_tmc_employee te 
			LEFT JOIN 73go_tmc tmc ON te.tmc_id=tmc.id 
			LEFT JOIN 73go_tmc_branch tb ON te.tmcbr_id=tb.id 
			where 1=1  ".$link.$limit;
		//echo '<pre>'.$sql."</pre><br/>\r";
		return $this->query($sql);
	}
	/**
	 * TMC协议客户信息
	 * 创建者：甘世凤
	 * 2014-11-26下午01:55:19
	 */
	public function queryTMCLink($cond,$Page = 0){
	    if($Page == 0){
			$limit = "";
		}else{
			$limit = " limit $Page->firstRow , $Page->listRows";
		}
		
		if($cond['type']==1){
			$link=" and ctl.tmc_id = ".$cond['id'];
		}else if($cond['type']==2){
			if($cond['id']==-1){
    			if($cond['status']==null){
    				$link = "";
    			}else{
    				$link = "and ctl.status=".$cond['status'];
    			}
    		}else{
    			if($cond['status']==null){
    				$link = "and ctl.tmc_id =".$cond['id'];
    			}else{
    				$link = "and ctl.tmc_id =".$cond['id']." and ctl.status=".$cond['status'];
    			}
    		}
		}else if($cond['type']==3){
			if($cond['name']=='所有企业'){
				if($cond['status']==null){
					$link = "";
				}else{
					$link = "and ctl.status=".$cond['status'];
				}
			}else{
				if($cond['status']==null){
					$link = "and tmc.`name` like '%".$cond['name']."%'";
				}else{
					$link = "and tmc.`name` like '%".$cond['name']."%' and ctl.status=".$cond['status'];
    			}
    		}
		}
		
		$m = M('');
		$sql="select ctl.id, ctl.tmc_id, ctl.co_id,com.co_code, 
		      com.name, com.province, com.city, ctl.date, ctl.status 
		from 73go_co_tmc_link as ctl 
		LEFT JOIN 73go_company as com ON ctl.co_id=com.id 
		where 1=1 ".$link.$limit;
		//echo '<pre>'.$sql."</pre><br/>\r";
		return $m->query($sql);
	}

	
}


