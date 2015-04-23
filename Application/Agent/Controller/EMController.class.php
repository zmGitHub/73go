<?php
namespace Agent\Controller;
use Think\Controller;
/**
 * TMC企业信息管理
 * 员工管理Staff
 * 企业管理Enterprise
 * 部门管理Branch
 * 创建者：董发勇
 * 创建时间：2014-11-5下午03:28:46
 *
 *
 */
class EMController extends Controller {
	/**
	 * 查询登录账户管理
	 * 查询出当前登录企业下面的所有员工信息
	 * 创建者：甘世凤
	 * 创建时间：2014-11-5下午04:06:11
	 */
	public function showESAll(){
		
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		
		$Requesttable=M('');
		$sql="select te.id esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,
		te.email,u.username,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee as te LEFT JOIN 73go_tmc_branch as tb ON
	te.tmcbr_id=tb.id
	LEFT JOIN 73go_user as u ON
	te.u_id=u.id;";
		$requestsion=$Requesttable->query($sql);
		$this->assign('emp',$requestsion);		
		$this->theme('agent')->display('tmc_userid_mgnt');
			 	
	}
	/**
	 * 根据员工ID查询信息
	 * 创建者：甘世凤
	 * $esid 当前启用下员工id
	 * 创建时间：2014-11-5下午04:11:17
	 */
	public function showESByESId(){
		//$esid 当前启用下员工id
		$esid=$_POST['esid'];
		
		$Requesttable=M('');
	
		$sql="select te.id as esid,u.username as regname,u.`password`,te.`status`,u.id as uid from
		 73go_tmc_employee te,73go_user u where te.u_id=u.id and te.id=".$esid;
							
		$user=$Requesttable->query($sql);

    	echo $this->ajaxReturn($user);
	
	}
	/**
	 * 修改系统用户登录信息
	 * 系统用户(企业下面的员工)
	 * 创建者：甘世凤
	 * 创建时间：2014-11-5下午04:13:44
	 */
	public function updateUser(){
		$user = M('user'); // 实例化tmc_employee对象
		// 要修改的数据对象属性赋值
		$id = $_POST['id'];		
		$data['password']=$_POST['password'];
		$re=$user->where($data)->find();
		if($re){
			echo 2;
		}
		else{
		$result=$user->where("id =".$id)->save($data); 		
		if($result){
			echo 1;
		}else {
			echo 0;
		}
		}
	}
	
	/**
	 * 添加系统用户登录信息
	 * 系统用户(企业下面的员工)
	 * 创建者：甘世凤
	 * 创建时间：2014-11-5下午04:13:44
	 */
	public function addUser(){
		$user = M('user'); // 实例化user对象
		// 要创建的数据对象属性赋值
		$id = $_POST['id'];		
		$regname=$_POST['regname'];
		$password=$_POST['password'];
		
		if($id!=null){
			$user->username = $regname;
			$user->password = $password;
			// 把数据对象添加到数据库
			$result=$user->add();
			if($result){
				echo 1;
			}else {
				echo 0;
			}
		}
		
	}
	/**
	 * 企业员工操作信息(启用和停用)
	 * $esid 企业员工id
	 * $status 状态 0代表启用，99代表停用
	 * 创建者：甘世凤
	 * 创建时间：2014-11-5下午04:16:07
	 */
	public function updateStatus($id,$status){
	
		$esid=$id;
		
		$status=$status;
		
		$emp = M('tmc_employee');

		if($status==0){
			
		 $sql="update 73go_tmc_employee te set te.`status`=99 where te.id=".$esid;
		
		}else{
			
		 $sql="update 73go_tmc_employee te set te.`status`=0 where te.id=".$esid;
		}
		
		$requestsion=$emp->execute($sql);
		
		$this->theme('agent')->showESAll();
	}
	/**
	 * 企业员工多条件查询
	 * $like 条件属性值（工号/姓名/电话/邮箱/证件号码）
	 * 创建者：甘世凤
	 * 创建时间：2014-11-5下午04:20:08
	 */
	public function showlikeESAll(){
	
		$con=$_POST['con'];

		$Requesttable=M('');
		
		$sql="select te.id as esid,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,te.email,
		u.username as regname,u.id as uid,u.`password`,te.`status` from 73go_tmc_employee te,73go_user u,
		73go_tmc_branch tb where te.u_id=u.id and te.tmcbr_id=tb.id and 
		(te.emp_code like '%".$con."%' or te.`name` like '%".$con."%' or te.phone like '%".$con."%' or te.id_num like '%".$con."%')";

		$emps=$Requesttable->query($sql);
		

		$this->assign('emp',$requestsion);
		//dump($requestsion);
		C('LAYOUT_ON',FALSE);

		echo $this->ajaxReturn($emps);
		
	}


	//---->2015/1/26, 被浩哥废弃，使用 TmcDepartmentStaff/showTmcDepartment
	/**
	 * 查询部门和员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-11-5下午04:46:23
	 */

	/**
	public function showBSAll(){
		C('LAYOUT_ON',TRUE);
		$u_id = LI('userId');
		$tmcid = LI('tmcId');
		
		$data1['tmc_id']=$tmcid;
		$tmc_branch=M('tmc_branch');
		$branchList=$tmc_branch->where($data1)->select(); 
		$this->assign('branchList',$branchList);
		
		$tmc=M('tmc');
		$tmclist=$tmc->where('id='.$tmcid)->find(); 
		$this->assign('tmclist',$tmclist);
		
		$data2['tmc_id']=$branchList['tmc_id'];
		$data2['p_id']=$branchList['p_id'];

		if($data2['p_id']!=null){
		$result=$tmc_branch->where($data2)->select();  
		$this->assign('branchList2',$result);
		}	
		//加载布局文件
		layout("tmc");
        $this->theme('agent')->display("tmc_employee_list");
	
	}
	 * ///<---- 2015/1/26, 被浩哥废弃，使用 TmcDepartmentStaff/showTmcDepartment
	
		
	/**
	 * 根据部门id和企业id号来查询出相关企业的企业员工信息
	 * 创建者：董发勇
	 * 创建时间：2014-11-5下午07:44:12
	 */
	public function showBSByAll($id,$tmc_id,$p_id){
		echo $p_id;
		$BSdate=M('');
		if($p_id==0){
				$sql="select e.emp_code,e.`name`,e.phone,e.email from 73go_tmc as t,73go_tmc_branch as b,73go_tmc_employee as e  where t.id=b.tmc_id and t.id=e.tmc_id and b.id=e.tmcbr_id";
				$BSrequest=$BSdate->query($sql);
				$this->assign('bs',$BSrequest);
				C('LAYOUT_ON',FALSE);
       			$this->theme('agent')->display("tmc_employee_list");
		}
		else{
				$sql="select e.emp_code,e.`name`,e.phone,e.email from 73go_tmc_employee as e where e.tmcbr_id=$id and e.tmc_id=$tmc_id";
				$BSrequest=$BSdate->query($sql);
				$this->assign('bs',$BSrequest);
				C('LAYOUT_ON',FALSE);
       			$this->theme('agent')->display("tmc_employee_list");
		}
	}
	/**
	 * 根据当前目录的id，添加下级目录信息
	 * 创建者：董发勇
	 * 创建时间：2014-11-6上午10:14:49
	 */
	public function showaddBSByid(){
		//session('id',$id);
		//session('tmc_id',$tmc_id);
		
		$id = $_REQUEST['id'];
		$tmc_id = $_REQUEST['tmc_id'];
		
		if (isset($id))
		$this->assign("pid", $id);
		echo $tmc_id;
		$this->assign("tmc_id", $tmc_id);
		
		C('LAYOUT_ON',FALSE);
        $this->theme('agent')->display("addbumen");
	}
	
	public function addBS(){
		$pid = (int) $_POST['pid'];
		if ($pid > 0)
		$branchdata['p_id']=$pid;
		$branchdata['tmc_id']=$_POST['tmc_id'];
		$branchdata['name']=$_POST['name'];
		$branchdata['br_code']=$_POST['code'];
		$branchdata['status']="0";		
		$branch = M('tmc_branch');
		$ret = $branch->data($branchdata)->add();
		$this->showBSAll();
	}
	
	/**
 	* 跳转到协议企业界面
 	* 创建者：甘世凤
 	* 2014-11-12下午06:59:56
 	*/
	public function showcustomer(){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		$tmcid=LI('tmcId');
		
		$tmclink=M('co_tmc_link');
		$sql="select ctl.id,ctl.date,ctl.`status`,c.co_code,c.`name`,c.short_name,ctl.co_id,ctl.tmc_id from
		73go_co_tmc_link ctl LEFT JOIN 73go_company c on ctl.co_id=c.id where ctl.tmc_id=".$tmcid;
		$result=$tmclink->query($sql);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$link = " limit $Page->firstRow , $Page->listRows";
		$result=$tmclink->query($sql.$link);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('tmclinks',$result);	

		
		$company=M('');
		$sqladd="select id,co_code,name,short_name from 73go_company 
				where id not in (select co_id from 73go_co_tmc_link  where tmc_id=$tmcid)";
		$resultadd=$company->query($sqladd);
		$this->assign('conameadd',$resultadd);
		
        $this->theme('agent')->display("tmc_customer_mgnt");
	}
	
	
	/**
	 * 模糊查询协议企业
	 * 创建者：甘世凤
	 * 2014-11-12下午08:46:55
	 */
	public function showcustomerLike(){
		$tmcid=LI('tmcId');
		
		$con=$_POST['con'];
		
		$tmclink=M('co_tmc_link');
		
		$sql="select ctl.id,ctl.date,ctl.`status`,c.co_code,c.short_name,ctl.co_id,ctl.tmc_id from 
		73go_co_tmc_link ctl LEFT JOIN 73go_company c on ctl.co_id=c.id  where ctl.tmc_id=".$tmcid." and
		(c.co_code like '%".$con."%' or c.short_name like '%".$con."%')";
		
		$result=$tmclink->query($sql);
		
		$this->ajaxReturn($result);
	}
	/**
	 * 根据状态查询协议企业
	 * 创建者：甘世凤
	 * 2014-11-12下午09:20:11
	 */
	public function showcustomerStatus(){
		$tmcid=LI('tmcId');
		
		$con=$_POST['con'];
		
		$tmclink=M('co_tmc_link');
		if($con==1){
			$link="";
		}else{
			$link=" and ctl.`status`=".$con;
		}
		
		$sql="select ctl.id,ctl.date,ctl.`status`,c.co_code,c.short_name,ctl.co_id from 
		73go_co_tmc_link ctl LEFT JOIN 73go_company c on ctl.co_id=c.id where ctl.tmc_id=".$tmcid.$link;
		
		$result=$tmclink->query($sql);
		
		$this->ajaxReturn($result);
	}
	/**
	 * 查看企业详情根据协议
	 * 创建者：甘世凤
	 * 2014-11-26下午06:25:15
	 */
	public function showEnterpriseDetail($co_id,$tmcid){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		$company=M('company');
		$result=$company->where('id='.$co_id)->find();
		$dictionary=M('dictionary');
		$result_d=$dictionary->where("d_group='industry' AND d_key=".$result['industry'])->find();
		$result['industry']=$result_d['d_value'];
		
		$this->assign('company',$result);	
		
		/* 1.19 更改 gsf*/
		$cond['co_id']=$co_id;
		$cond['tmc_id']=$tmcid;
		$tmclink=M('co_tmc_link');
		$result2=$tmclink->where($cond)->find();
		$this->assign('tmclink',$result2);	
		
        $this->theme('agent')->display("co_basicinfo");
	}
	
	/**
	 * 弹框协议客户模糊查询
	 * 创建者：甘世凤
	 * 2014-11-27上午10:16:36
	 */
	public function inputLink(){
		$tmcid=LI('tmcId');
		$con=$_POST['con'];
		$company=M('');
		$sql="select id,co_code,name,short_name from 73go_company 
				where id not in (select co_id from 73go_co_tmc_link  where tmc_id=$tmcid) and
		(co_code like '%".$con."%' or name like '%".$con."%' or short_name like '%".$con."%')";
		$result=$company->query($sql);
		$this->ajaxReturn($result);
	}
	
	/**
	 * 添加协议客户
	 * 创建者：甘世凤
	 * 2014-11-26下午06:07:31
	 */
	public function addLinkCustomer(){
		$tmcid=LI('tmcId');
		
		$strlist=$_POST['str'];
		
		$array = explode(",",$strlist);
		
		$arrayconunt = count($array);
		//print_r($arrayconunt);
		
		$tmclink=M('co_tmc_link');
		$coM=M('company');
		for($i=0;$i<$arrayconunt;$i++){
			
			/* 1.20更改gsf*/
			$cond['co_id']=$array[$i];
			$cond['tmc_id']=$tmcid;
			$result = $tmclink->where($cond)->find();
			$cos=$coM->where('id='.$result['co_id'])->find();
			$co_name .= $cos['name']." ";
			
			if(!$result){
				$Ajaxdate['tmc_id']=$tmcid;
				$Ajaxdate['co_id']=$array[$i];
				$Ajaxdate['status']=8;
				$Ajaxdate['date']=date("Y-m-d H:i:s",time());

				$requets=$tmclink->data($Ajaxdate)->add();
			}
			/* 1.19更改gsf*/
		}
		if($requets){
			$this->ajaxReturn(1);
		}else{
//			print_r($co_name);exit;
			$this->ajaxReturn($co_name,'json');
		}
	}
	
	/**
	 * 更改协议状态
	 * 创建者：甘世凤
	 * 2014-11-14上午10:17:02
	 */
	public function updateLinkStatus(){
		$linkid=$_POST['id'];
		$linkstatus=$_POST['status'];
		$tmclink=M('co_tmc_link');

		//修改协议必须双方签署才能生效
		switch($linkstatus){
			case 9:
				$data['status'] = 6;
				break;
			case 8:
				$data['status'] = 6;
				break;
			case 7:
				$data['status'] = 0;
				break;
			case 6:
				$data['status'] = 9;
				break;
			default:
				$data['status'] = 9;
		}
		$result=$tmclink->where('id='.$linkid)->data($data)->save();
		
		$empM=M('employee');
		$vipM=M('vip_table');
	
		if($result){
			$resu=$tmclink->where('id='.$linkid)->find();
			$co_id=$resu['co_id'];
			$status=$resu['status'];
			if($status!=0){
				$result2=$empM->where('co_id='.$co_id)->select();
				foreach ($result2 as $val){
					$res=$vipM->where('emp_id='.$val['id'])->delete();
				}
			}
			$this->ajaxReturn($resu);
		}
		
	}
	/**
	 * 修改协议客户信息
	 * 创建者：甘世凤
	 * 2014-11-28上午10:12:54
	 */
	public function updateLink(){
//		$u_id=LI('userId');
//		//查出tmcid
//		$data['u_id']=$u_id;
//		$datatmc=M('tmc');
//		$resulttmc=$datatmc->where($data)->find();
//		$tmcid=$resulttmc['id'];
		
		$tmcid=LI('tmcId');
		
		$link_id=$_POST['id'];
    	$Ajaxdate['tmc_id']=$tmcid;
		$Ajaxdate['co_id']=$_POST['co_id'];
		$Ajaxdate['pay_type']=$_POST['pay_type'];
		$Ajaxdate['co_name']=$_POST['co_name'];
		$Ajaxdate['co_phone']=$_POST['co_phone'];
		$Ajaxdate['co_email']=$_POST['co_email'];
		$Ajaxdate['co_addr']=$_POST['co_addr'];
		$Ajaxdate['note']=$_POST['note'];
		$Ajaxdate['status']=0;
		 
		//print_r($Ajaxdate);exit;
		$co_link=M('co_tmc_link');
		$requets=$co_link->where("id=".$link_id)->save($Ajaxdate);
		if($requets){
			$this->redirect('Agent/EM/showcustomer');
			//$this->success('修改成功',U('Agent/EM/showcustomer'));
		}else{
			$this->redirect('Agent/EM/showcustomer');
			//$this->error('修改失败',U('Agent/EM/showcustomer'));
		}
	
	}
	
	/**
	 * 删除协议
	 * 创建者：甘世凤
	 * 2014-11-14下午01:54:21
	 */
	public function deleteLink(){
		
		$linkid=$_POST['id'];
		
		$tmclink=M('co_tmc_link');
		
		$result=$tmclink->where('id='.$linkid)->delete();
		
		$empM=M('employee');
		$vipM=M('vip_table');
	
		if($result){
			$resu=$tmclink->where('id='.$linkid)->find();
			$co_id=$resu['co_id'];
			$status=$resu['status'];
			if($status!=0){
				$result2=$empM->where('co_id='.$co_id)->select();
				foreach ($result2 as $val){
					$res=$vipM->where('emp_id='.$val['id'])->delete();
				}
			}
			$this->ajaxReturn($resu);
		}
		
	}
	
	/**
	 * 查询VIP客户信息
	 * 创建者：甘世凤
	 * 2014-11-13上午09:37:11
	 */
	public function showvipuser(){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		$tmcid=LI('tmcId');
		
		$emp=D('Agent/EM','Logic');
//		$sql="select e.*,c.short_name,b.name brname 
//				FROM 73go_employee e 
//				LEFT JOIN 73go_company c ON e.co_id=c.id
//				LEFT JOIN 73go_branch b ON e.br_id=b.id
//				LEFT JOIN 73go_co_tmc_link as link ON link.co_id=c.id
//				where link.`status`=0 AND e.id not in 
//					(select emp_id from 73go_vip_table where tmc_id=$tmcid)";
		$result2=$emp->queryEmp();
		$this->assign('vip_emp',$result2);

//		$cond['tmcid']=$tmcid;
		$vip=D('Agent/EM','Logic');
		$result=$vip->queryVip('',0);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result=$vip->queryVip('',$Page);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('vip',$result);
		$this->theme('agent')->findComName();
        $this->theme('agent')->display("vip_mgnt");
	}
	/**
	 * 模糊查询VIP客户
	 * 创建者：甘世凤
	 * 2014-11-13上午10:28:54
	 */
	public function showvipLike(){
		
		$con=$_POST['con'];
		
		$cond['con']=$con;
		$cond['type']=1;
		$vip=D('Agent/EM','Logic');
		$result=$vip->queryVip($cond,0);
		$this->ajaxReturn($result);
	}
	/**
	 * 查询协议企业
	 * 创建者：甘世凤
	 * 2014-11-28上午10:42:40
	 */
	public function findComName(){
		$tmcid=LI('tmcId');
		
    	$com=M('');
		
		$sql2="select id,`name`,short_name from 73go_company where id in 
		(select co_id from 73go_co_tmc_link where status=0 and tmc_id=$tmcid)";
		
		$result2=$com->query($sql2);
		
		$this->assign('link_coms',$result2);
		$this->assign('cnames',$result2);
		
    }
    
    /**
     * 查询vip客户根据企业
     * 创建者：甘世凤
     * 2014-11-28上午10:46:22
     */
    public function showVipEmpByco(){
    	$tmcid=LI('tmcId');
		
    	$coid=$_POST['coid'];
    	
    	if($coid==-1){
    		$link="";
    	}else{
    		$link=" and c.id=".$coid;
    	}
    	$emp=M('');
    	$sql="select e.*,c.short_name,b.name brname 
				FROM 73go_employee e 
				LEFT JOIN 73go_company c ON e.co_id=c.id
				LEFT JOIN 73go_branch b ON e.br_id=b.id
				where e.id not in 
					(select emp_id from 73go_vip_table where tmc_id=$tmcid)".$link;
		$result=$emp->query($sql);

//    	$emp=D('Agent/EM','Logic');
//		$result=$emp->queryEmp($cond);
		$this->ajaxReturn($result);    	
    }
	/**
     * 模糊查询vip客户
     * 创建者：甘世凤
     * 2014-11-28上午10:46:22
     */
    public function showVipEmpBylike(){
    	$tmcid=LI('tmcId');
		
    	$like=$_POST['like'];
    	if($like==''){
    		$link="";
    	}else{
    		$link=" and (e.emp_code like '%".$like."%' or 
    				e.`name` like '%".$like."%' or e.phone like '%".$like."%' 
    				or e.email like '%.".$like."%' or e.id_num like '%".$like."%')";
    	}
    	$emp=M('');
    	$sql="select e.*,c.short_name,b.name brname 
				FROM 73go_employee e 
				LEFT JOIN 73go_company c ON e.co_id=c.id
				LEFT JOIN 73go_branch b ON e.br_id=b.id
				where e.id not in 
					(select emp_id from 73go_vip_table where tmc_id=$tmcid)".$link;
		$result=$emp->query($sql);
//		$emp=D('Agent/EM','Logic');
//		$result=$emp->queryEmp($cond);
		$this->ajaxReturn($result);    	
    }
    /**
     * 根据条件查询
     * 创建者：甘世凤
     * 2014-11-13上午11:26:27
     */
    public function showVipBycon(){
    	
    	$type=$_POST['type'];
    	$coid=$_POST['coid'];
    	
    	$cond['level']=$type;
		$cond['coid']=$coid;
		$cond['type']=2;
		$vip=D('Agent/EM','Logic');
		$result=$vip->queryVip($cond,0);
		
		$this->ajaxReturn($result);
    }
    
    /**
     * 添加VIP
     * 创建者：甘世凤
     * 2014-11-27下午04:26:54
     */
    public function addVip(){
    	$tmcid=LI('tmcId');
		
		$strlist=$_POST['str'];
		
		$array = explode(",",$strlist);
		
		$arrayconunt = count($array);
		$vip=M('vip_table');
		$empM=M('employee');
		for($i=0;$i<$arrayconunt;$i++){
			/*1.19更改gsf*/
//			$Ajaxdate['tmc_id']=$tmcid;
//			$Ajaxdate['emp_id']=$array[$i];
			/*1.20更改gsf*/
			$cond['emp_id']=$array[$i];
			$cond['tmc_id']=$tmcid;
			$res=$vip->where($cond)->find();
			$emps=$empM->where('id='.$res['emp_id'])->find();
			$emp_name.=$emps['name']." ";
			if(!$res){
				$Ajaxdate['tmc_id']=$tmcid;
				$Ajaxdate['emp_id']=$array[$i];
				$requets=$vip->data($Ajaxdate)->add();
			}
			/*1.19更改gsf*/
		}
    	if($requets){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn($emp_name,'json');
		}
    }
    
    /**
     * 查询员工根据VIP
     * $emp_id(VIP所对应的企业员工id)
     * 创建者：甘世凤
     * 2014-11-27下午03:25:35
     */
    public function findEmp($id,$emp_id){
    	C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
    	$employee=M('employee');
		$branch = M('branch');
		$vip=M('vip_table');
		$tmc_employee=M('tmc_employee');
    	$result=$employee->where('id='.$emp_id)->find();//员工信息
		$emp_br=$result['br_id'];
		$empbranch=$branch->where('id='.$emp_br)->find();//员工的部门信息
    	$result2=$vip->where('id='.$id)->find();//查询员工vip信息
    	$op_id=$result2['op_id'];
    	$result3=$tmc_employee->where('id='.$op_id)->find();//查询tmc员工信息
    	$tmc_id=LI('tmcId');
    	$res=$tmc_employee->where('tmc_id='.$tmc_id)->select();//查询tmc员工列表

		$this->assign('empbrand',$empbranch);
		$this->assign('emp',$result);
		$this->assign('vip',$result2);
		$this->assign('tmc_emp',$result3);
    	$this->assign('ops',$res);
    	
    	$this->theme('agent')->display("vip_info");
    	
    }
    /**
     * 修改VIP
     * 创建者：甘世凤
     * 2014-11-27下午10:22:07
     */
    public function updateVip(){
    	
//    	print_r($_POST);exit;
    	
//    	$u_id=LI('userId');
//		//查出tmcid
//		$data['u_id']=$u_id;
//		$datatmc=M('tmc');
//		$resulttmc=$datatmc->where($data)->find();
//		$tmcid=$resulttmc['id'];
		$tmcid=LI('tmcId');
		
		if($_POST['op_id']=='' || $_POST['op_id']==null){
			$op_id=null;
		}else{
			$op_id=$_POST['op_id'];
		}
		
		$Ajaxdate['id']=$_POST['id'];
    	$Ajaxdate['tmc_id']=$tmcid;
		$Ajaxdate['emp_id']=$_POST['emp_id'];
		$Ajaxdate['vip_level']=$_POST['vip_level'];
		$Ajaxdate['phone']=$_POST['phone'];
		$Ajaxdate['email']=$_POST['email'];
		$Ajaxdate['work_addr']=$_POST['work_addr'];
		$Ajaxdate['home_addr']=$_POST['home_addr'];
		$Ajaxdate['op_id']=$op_id;
		$Ajaxdate['note']=$_POST['note'];
		 
		$vip=M('vip_table');
		$requets=$vip->save($Ajaxdate);
		if($requets){
			$this->redirect('Agent/EM/showvipuser');
			//$this->success('修改成功',U('Agent/EM/showvipuser'));
		}else{
			$this->redirect('Agent/EM/showvipuser');
			//$this->error('修改失败',U('Agent/EM/showvipuser'));
		}
    }
    /**
     * 删除VIP客户
     * 创建者：甘世凤
     * 2014-11-28上午09:50:40
     */
    public function delVip($id){
    	
		$vip=M('vip_table');
		
		$result=$vip->where('id='.$id)->delete();
		
		if($result){
			$this->theme('agent')->showvipuser();
		}else{
			$this->redirect('Agent/EM/showvipuser');
			//$this->error('删除失败',U('Agent/EM/showvipuser'));
		}
    }
    
    


    
    
    /**
     * 查询
     * 创建者：董发勇
     * 创建时间：2014-11-27下午09:29:50
     */
    public function showbranchemp(){
    	$tmc['u_id']=LI("userId");//判断当前企业登陆的id值
    	$tmctable=M('tmc');
    	$requesttmc=$tmctable->where($tmc)->find();  	
    	$data['tmc_id']=$requesttmc[id];//获取tmc的id值
    	$data['tmcbr_id']=$_POST['id'];//获取当前所选择的部门id
    	$data['status']=0;
    	$tmcemployeetable=M('tmc_employee');
    	$request=$tmcemployeetable->where($data)->select();
    	$this->ajaxReturn($request);
    	
    	
    }
    /**
     * 部门员工模糊查询
     * 创建者：董发勇
     * 创建时间：2014-11-27下午10:07:35
     */
    public function shouwbranchempLike(){
    	
    	$tmc_id=LI('tmcId');//获取tmc的id值
    	$tmcbarid=$_POST['tmcbarid'];//获取部门值
    	$eplike=$_POST['eplike'];//获取模糊搜索值
    	

    	//遍历获取该部门下全部子类ID
    	$p_id = $this->getChildBranchIDs($tmcbarid);
    	$p_id = implode(',', $p_id);
    	 
    	$eplike=$like;//获取模糊搜索值
    	if($tmcbarid)
    		$where = "and tmcbr_id in (".$p_id.")";
    	
    	$tmcemployeetable=M('');
			$sql="select * from 73go_tmc_employee where
			 		tmc_id=$tmc_id $where and status=0 and 
				 (emp_code like '%$eplike%' or `name` LIKE '%$eplike%' or phone like '%$eplike%' or email like '%$eplike%')
					";
			$request=$tmcemployeetable->query($sql);
			echo $this->ajaxReturn($request);
			
    }
    /**
     * 删除员工信息
     * 创建者：董发勇
     * 创建时间：2014-11-28上午10:55:55
     */
    public function delebranchempuser(){
    	$id=$_POST['str'];
    	$tmc=M('tmc_employee');
    	$request=$tmc->where('id='.$id)->setField('status',99);
    	echo $this->ajaxReturn($request);
    }
    /**
     * 添加员工界面
     * 创建者：董发勇
     * 创建时间：2014-11-28上午11:09:35
     */
    public function showaddbranchempuser(){
    	C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		
		$tmc['u_id']=LI("userId");//判断当前企业登陆的id值
    	$tmctable=M('tmc');
    	$requesttmc=$tmctable->where($tmc)->find();
    	
    	$data['tmc_id']=$requesttmc[id];//获取tmc的id值
    	
    	$tmcemployeetable=M('tmc_branch');
    	$request=$tmcemployeetable->where($data)->select();//获取当前企业下的所有部门信息
    	$this->assign('branchList',$request);//保存部门信息
    	
    	$es = D('Common/Common', 'Logic');
    	
    	$group=$es->shouwDictionay(id_type);
    	
    	//$this->assign('grcount',count($group, true));//保存证件类型信息
    	
    	$this->assign('grouplist',$group);//保存证件类型信息
    	
    
    	$this->theme('agent')->display('tmc_employee_detail');
    }
    /**
     * 添加员工
     * 创建者：董发勇
     * 创建时间：2014-11-28下午01:08:31
     */
    public function addbranchempuser(){
    	$data['tmc_id'] = LI('tmcId');//tmc企业id
	 	$data['tmcbr_id']=$_POST['barch'];
	 	$data['emp_code']=$_POST['emp_code'];
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
	 	$data['email']=$_POST['email'];
	 	$data['id_type']=$_POST['id_type'];
	 	$data['id_num']=$_POST['id_num'];
	 	$tmcemtable=M('tmc_employee');
	 	$request=$tmcemtable->add($data);
    	echo $this->ajaxReturn(($request)? 1 : 0);
    }
    /**
     * 添加下级部门
     * 创建者：董发勇
     * 创建时间：2014-11-28下午04:51:57
     */
    public function addbran(){
    	$data['tmc_id'] = LI('tmcId');
    	$data['p_id']=$_POST['p_id'];
    	$data['br_code']=$_POST['ddbarchcoid'];
    	$data['name']=$_POST['ddbarchname'];
    	$data['status']="0";
    	$bartable=M('tmc_branch');
    	$request=$bartable->add($data);
    	echo $this->ajaxReturn(($request)? 1 : 0);
    	
    }
    /**
     * 根据id查出信息
     * 创建者：董发勇
     * 创建时间：2014-12-1上午10:28:08
     */
    public function showupdatebranchempuser($emps_id){
    	C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
    	$tmcemptable=M('tmc_employee');
    	$request=$tmcemptable->where('id='.$emps_id)->find();
		$this->assign('tmcemp',$request);//保存员工信息
		$this->theme('agent')->display('tmc_employee_update');
    }
    /** 
     * 修改信息
     * 创建者：董发勇
     * 创建时间：2014-12-1上午10:28:34
     */
    public function updatebranchempuser(){
    	$id=$_POST['empid'];
	 	$data['emp_code']=$_POST['emp_code'];
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
	 	$data['email']=$_POST['email'];
	 	$data['id_type']=$_POST['id_type'];
	 	$data['id_num']=$_POST['id_num'];
    	$tmcemtable=M('tmc_employee');
	 	$request=$tmcemtable->where('id='.$id)->save($data);
    	echo $this->ajaxReturn(($request)? 1 : 0);
    }

	// --------------------tmc旗舰店管理的配置管理------------------------------
	// 显示出旗舰店的相关的信息；
	// by wangyue
	public function tmc_store_mgnt() {
		$tmcStore = new \Agent\Model\TmcStoreModel();
		$array = $tmcStore->whetherToCreate();
		// 判断该TMC公司是否已创建旗舰店
		// 未创建的
		if (! $array['result']) {
			$data ['name'] = $array['tmcName'];
			$data ['sub_url'] = "http://" . C ( 'TMC-HOSTING-SERVER' ) . "/" . $array['code'];
			$this->assign ( 'data', $data );
			$this->theme ( 'agent' )->display ( "tmc_store_create" );
			// 已经创建的
		} else {
			$data ['name'] = $array['tmcName'];
			//$data ['sub_url'] = "http://" . C ( 'TMC-HOSTING-SERVER' ) . "/" . $array['code'];
			$data ['sub_url'] = $array['sub_url'];
			$data ['reg_agreement'] = $array['reg_agreement'];
			$this->assign ( 'data', $data );
			$this->theme ( 'agent' )->display ( "tmc_store_update" );
		}
	}

	// 旗舰店创建，数据库添加一个旗舰店的操作
	// by wangyue
	public function tmc_store_create() {
		$tmcStore = new \Agent\Model\TmcStoreModel();
		$tmcStore->create();
		$this->tmc_store_mgnt ();
	}
    
    //进行旗舰店配置的相关处理  如果该旗舰店没有进行相关配置时进行添加，如果进行了相关的配置进行更新处理；
    public function dotmc_store_mgnt(){
    	$tmc_config=M('tmc_siteconfig');
    	$Tmc=M("tmc");
    	$tmcId=LI("tmcId");
    	$datt1=$tmc_config->where("tmc_id=".$tmcId)->select();
    	if($datt1){
			//将旗舰店相关的配置进行更新处理
			$reg_agreement=$_POST['reg_agreement'];
			$dat['reg_agreement']=str_replace("\r\n","<br>",$reg_agreement);



			$datt2=$tmc_config->where("tmc_id=".$tmcId)->save($dat);
			if($datt2){
				$reg_agreement=$_POST['reg_agreement'];
				$dat['reg_agreement']=str_replace("\r\n","<br>",$reg_agreement);			
				$datt2=$tmc_config->where("tmc_id=".$tmcId)->save($dat);
				
				$this->tmc_store_mgnt();
				
			
			}else{

				$this->tmc_store_mgnt();
			}
			
			
		}else {
			//获取本机的ip
    		//$ip=$_SERVER['REMOTE_ADDR'];
   			//获取tmc的企业编号     http://ip/tmc_code/
   			$data=$Tmc->where("id=".$tmcId)->getField('tmc_code');
    		//将旗舰店相关的配置进行添加处理
			$datt['sub_url']="http://".C('TMC-HOSTING-SERVER')."/".$data;
			$reg_agreement=$_POST['reg_agreement'];
			$datt['reg_agreement']=str_replace("\r\n","<br>",$reg_agreement);
			$datt['tmc_id']=$tmcId;
			$datt11=$tmc_config->data($datt)->add();
			$this->tmc_store_mgnt();
			
		}
			$this->tmc_store_mgnt();
			
    }

    
}