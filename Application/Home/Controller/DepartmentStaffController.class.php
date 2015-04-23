<?php
namespace Home\Controller;
use Common\Logic\CommonLogic;
use Home\Logic\DepartmentStaffLogic;
use Think\Controller;
/**
 * TMC部门与员工业务层
 * @author dfy
 * @2014-12-6 下午03:32:51
 */
class DepartmentStaffController extends Controller{
	
	/**
	 * 查看tmc部门信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:27:45
	 */
	public function showDepartment(){
		$u_id = LI('userId');
		$com_id = LI('comId');
		$data1['co_id']=$com_id;
		$tmc_branch=M('branch');
		$branchList=$tmc_branch->where($data1)->select(); 
		$this->assign('branchList',$branchList);
		
		$tmc=M('company');
		$tmclist=$tmc->where('id='.$com_id)->find(); 
		$this->assign('tmclist',$tmclist);
		
		$data2['co_id']=$branchList['com_id'];
		$data2['p_id']=$branchList['p_id'];
			if($data2['p_id']!=null){
				$result=$tmc_branch->where($data2)->select();  
				$this->assign('branchList2',$result);
			}	
		//加载布局文件
		C('LAYOUT_ON',TRUE);
		layout("home");
        $this->theme('default')->display("employee_list");
	}
	/**
	 * 根据id查询tmc部门信息
	 * Enter description here ...
	 */
	public function showDepartmentById(){
		$id=$_POST['p_id'];
		$Branch=M('branch');
		$request=$Branch->where('id='.$id)->find();
		//$this->assign('tmcbarlist',$tmcBranch);
		$this->ajaxReturn($request);
	}
	/**
	 * 修改部门
	 * Enter description here ...
	 */
	public function updateDepartment(){
		$id=$_POST['id'];
		$data['br_code']=$_POST['br_code'];
		$data['name']=$_POST['br_name'];
		$Branch=M('branch');
		$comId= LI('comId');
		$dept_code = $Branch->field('br_code')->where('co_id='.$comId)->select();
		foreach($dept_code as $i) {
			foreach($i as $code) {
				if($code == $data['br_code']) {
					$this->ajaxReturn(-1);
				}
			}
		}
		$dept_name = $Branch->field('name')->where('co_id='.$comId)->select();
		foreach($dept_name as $j) {
			foreach($j as $name) {
				if($name == $data['name']) {
					$this->ajaxReturn(-2);
				}
			}
		}
		$request=$Branch->where('id='.$id)->save($data);
		$this->ajaxReturn(1);
	}
	/**
	 * 添加企业部门信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:50:11
	 */
	public function addDeparment(){
		$com_id = LI('comId');
    	$p_id=$_POST['p_id'];
    	$br_code=$_POST['ddbarchcoid'];
    	$name=$_POST['ddbarchname'];
		$DeparmentStaff=D('Home/DepartmentStaff','Logic');//调部门员工业务处理层
		$request=$DeparmentStaff->addDepartmentLogic($com_id,$p_id,$br_code,$name);//添加部门
		$this->ajaxReturn(($request==1)? 1 : -1);
	}
	/**
	 * 查询部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:00:10
	 */
	public function shouwDepartmentStaff(){
		$id=$_POST['id'];
		$Page = 0;
		$DeparmentStaff=D('Home/DepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$DeparmentStaff->shouwDepartmentStaffLogic($id,$Page);//添加部门
		$count=count($request);
		$Page  = new \Think\Page($count,2);
		$show  = $Page->show();// 分页显示输出
		//$request=$DeparmentStaff->shouwDepartmentStaffLogic($id,$Page);//添加部门
		
    	$this->ajaxReturn($request);
	}


	/**
	 * 获取指定部门（含子部门）的员工。
	 * 要求传入brId参数指定部门的id
	 * @author Lanny Lee
	 */
	public function branch_emps() {
		$brId = $_REQUEST['br_id'];
		$m = new CommonLogic();
		$this->ajaxReturn($m->getComBranchEmps($brId), 'JSON');
	}

	/**
	 *
	 */
	public function curr_com_emps() {
		$coId = LI('comId');
		$m = new CommonLogic();
		$this->ajaxReturn($m->getEmpOfCom($coId), 'JSON');
	}



	/**
	 * 模糊查询部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:33:39
	 */
	public function showStaffLike(){
		$tmcbarid=$_POST['tmcbarid'];//获取部门值
    	$eplike=$_POST['eplike'];//获取模糊搜索值
    	$DeparmentStaff=D('Home/DepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$DeparmentStaff->showStaffLikeLogic($tmcbarid,$eplike);//模糊查询员工
    	$this->ajaxReturn($request);
	}
	/**
	 * 删除部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:37:08
	 */
	public function deleteStaff(){
		$id=$_POST['str'];
		$DeparmentStaff=D('Home/DepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$DeparmentStaff->deleteStaffLogic($id);//模糊查询员工
		$this->ajaxReturn($request);
	}
	/**
	 * 添加员工界面
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:40:24
	 */
	public function showaddStaff(){
    	$data['co_id']=LI('comId');//获取tmc的id值
    	//$data['status']=0;
    	//1.21更改员工状态 1属于企业，0不属于企业
    	$data['status']=0;
    	$employeetable=M('branch');
    	$request=$employeetable->where($data)->select();//获取当前企业下的所有部门信息
    	$this->assign('branchList',$request);//保存部门信息
    	$es = D('Common/Common', 'Logic');
    	$group=$es->showDictionay(id_type);
    	//加载布局文件
    	C('LAYOUT_ON',TRUE);
		layout("home");
    	$this->assign('grouplist',$group);//保存证件类型信息
    	$this->theme('default')->display('employee_detail');
	}

	/**
	 * 判断员工号是否已经存在
	 * 创建者：王月
	 * 创建时间：2015-2-8
	 */
	public function check_employee() {
		$co_id = LI('comId');
		$employee = M('employee');
		$empNumAll = $employee->field('emp_code')->where('co_id='.$co_id)->select();
		$empNum = $_POST['userNumber'];
		foreach($empNumAll as $num) {
			foreach($num as $i) {
				if($i == $empNum) {
					$this->ajaxReturn(-1);
				}
			}
		}
		$this->ajaxReturn(1);
	}

	/**
	 * 添加员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:43:56
	 */
	public function addStaff(){
		$data['co_id'] = LI('comId');//tmc企业id
	 	$data['br_id']=$_POST['barch'];
	 	$data['emp_code']=$_POST['emp_code'];
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
		$data['qq']=$_POST['qq'];
	 	$data['email']=$_POST['email'];
	 	$data['id_type']=$_POST['id_type'];
	 	$data['id_num']=$_POST['id_num'];
	 	$data['province']=$_POST['province'];
	 	$data['city']=$_POST['city'];
	 	//1.21更改员工状态 1属于企业，0不属于企业
    	$data['status']=1;
	 	$employee=M('employee');
	 	$request=$employee->add($data);
    	$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 修改员工
	 * 根据id查询员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:47:24
	 */
	public function showStaffById($emps_id){
		$branche=M('');
		$co_id=LI('comId');
		$dictionary=M("");
		$grouplist=$dictionary->query("select * from 73go_dictionary where d_group='id_type'");
		
		$branchesql="select id,name from 73go_branch where co_id=$co_id and status=0";
		$branrequest=$branche->query($branchesql);
		$this->assign('brupdate',$branrequest);
		$branchs = D('Home/DepartmentStaff','Logic');;
		$branch = $branchs->getDeprtmentTree();
		$this->assign('branchtree',$branch);
    	$emptable=M('employee');
    	$map['id'] = $emps_id;
    	$map['co_id'] = $co_id;
    	$map['status'] = 1;
    	$request=$emptable->where($map)->find();
    	//dump($request);
    	if(!$request)
    		$this->error('该员工不属于本企业！', U('departmentStaff/showdepartment'));
		$this->assign('tmcemp',$request);//保存员工信息
		$this->assign('grouplist',$grouplist);
		
		C('LAYOUT_ON',TRUE);//加载布局文件
		layout("home");
		$this->theme('default')->display('employee_update');
	}
	
	/**
	 * 根据id查询分配部门信息
	 *dfy  2014-12-26  上午01:00:13
	 */
	public function showStaffByfenId($emps_id){

		$branche=M('');
		$co_id=LI('comId');
		$dictionary=M("");
		$grouplist=$dictionary->query("select * from 73go_dictionary where d_group='id_type'");
		
		$branchesql="select id,name from 73go_branch where co_id=$co_id and status=0";
		$branrequest=$branche->query($branchesql);
		$this->assign('brupdate',$branrequest);
    	$emptable=M('employee');
    	$map['id'] = $emps_id;
    	$map['co_id'] = $co_id;
    	$map['status'] = 1;
    	$request=$emptable->where($map)->find();
    	//dump($request);
    	if(!$request)
    		$this->error('该员工不属于本企业！', U('departmentStaff/showdepartment'));
		$this->assign('tmcemp',$request);//保存员工信息
		$this->assign('grouplist',$grouplist);
		C('LAYOUT_ON',TRUE);//加载布局文件
		layout("home");
		$this->theme('default')->display('employee_fenupdate');
	
	}
	
	
	
	/**
	 * 修改员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:51:42
	 */
	public function updateStaff(){
		$id=$_POST['empid'];
	 	$data['emp_code']=$_POST['emp_code'];
	 	$data['br_id']=$_POST['barch'];
		$data['power']=$_POST['power'];
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
		$data['qq']=$_POST['qq'];
	 	$data['email']=$_POST['email'];
	 	$data['id_type']=$_POST['id_type'];
	 	$data['id_num']=$_POST['id_num'];
	 	$data['province']=$_POST['province'];
	 	$data['city']=$_POST['city'];
		$tmcemtable=M('employee');
	 	$request=$tmcemtable->where('id='.$id)->save($data);
    	$this->ajaxReturn($request? 1 : 0);
	}
	public function updateStafffen(){
		$id=$_POST['empid'];
	 	$data['emp_code']=$_POST['emp_code'];
	 	$data['br_id']=$_POST['barch'];
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
	 	$data['email']=$_POST['email'];
	 	$data['id_type']=$_POST['id_type'];
	 	$data['id_num']=$_POST['id_num'];
	 	$data['province']=$_POST['province'];
	 	$data['city']=$_POST['city'];
    	$emtable=M('employee');
	 	$request=$emtable->where('id='.$id)->save($data);
    	$this->ajaxReturn($request? 1 : 0);
	}
	
	//加载待审核员工的界面（emp_reg_apprv.html）；
	public function emp_reg_apprv(){
	//加载布局文件
	layout("home");
	$M = M('');
	$id = LI('userId');
	$employee = M('employee');
	$company = M('company');
	$user_type = session('user_type');
		$com = $company->where('u_id='.$id)->select();
		$co_id = $com[0]['id'];

	//73go_join_co_request
	//查询出相关的数据（员工加入企业申请表[ 时间  ] ，企业员工信息 [ 姓名，性别，电话，邮编 ]）;
	$id=Li("userId"); //获取当前用户的id;	
	$sql = "SELECT  j.u_id,j.id,j.co_id,j.req_time,j.status,e.name,e.sex,e.phone,e.email,b.*
				FROM 73go_join_co_request as j
				LEFT JOIN 73go_employee as e ON e.u_id = j.u_id
				LEFT JOIN 73go_dictionary as b ON e.sex = b.d_key 
				WHERE  b.d_group = 'sex' AND j.status = '0'  AND j.co_id = ".$co_id." order by j.id asc ";
	$info  = $M->query($sql);
	$count = count($info);
	$Page  = new \Think\Page($count,10);
	$show  = $Page->show();// 分页显示输出
	$limit = " limit $Page->firstRow , $Page->listRows";
	$info  = $M->query($sql.$limit);
	$this->assign('Page',$show);// 赋值分页输出
	$this->assign('info',$info);
	$this->theme("default")->display("emp_reg_apprv");

	}
	
	//同意员工加入公司
	public function join_company(){
		$id = LI('userId');
		$company = M('company');
		$emp = $company->where('u_id='.$id)->select();
		$co_id = $emp[0]['id'];

		$em_id=$_GET['id'];

		$join = M('join_co_request');
		$jion_emp = $join->field('u_id')->where('co_id='.$co_id)->select();
		if( $jion_emp ) {
			$map['status']=1; //属于企业员工
			$map['co_id']=$co_id; //企业id
			$employee=M('employee');

			$result=$employee->where("u_id=".$em_id)->save($map);
			if($result){
				$mat['status']=1;
				$datt=$join->where("u_id=".$em_id)->save($mat);
				if($datt){
					$this->ajaxReturn("1", "JSON");
				} else {
					$this->ajaxReturn("-1", "JSON");
				}
			} else {
				$this->ajaxReturn("-1", "JSON");
			}
		} else {
			$this->ajaxReturn("-1", "JSON");
		}
	}

	//拒绝员工加入公司 
	public function refuse_employee(){
		$employee = M('employee');
		$em_id=$_GET['id'];
		$map['status']=15; //被驳回申请，不属于企业员工
		$map['co_id']=0; //企业id
		
		$result=$employee->where("u_id=".$em_id)->save($map);		
		if($result){
			$mat['status']=1;
			$join=M('join_co_request');
			$datt=$join->where("u_id=".$em_id)->save($mat);
			
			if($datt){
			
				$this->ajaxReturn(1);
			}else{
				$this->ajaxReturn(-1);
			}
		}else{

			$this->ajaxReturn(-1);
		}	

	}
	/**
	 * 分配未关联部门的信息
	 *dfy  2014-12-26  上午12:24:58
	 */
	public  function fenDepartmentStaff(){
		$co_id=LI('comId');
		$emplay=M('employee');
		$emplaysql="select id,emp_code,`name`,phone ,email  from 73go_employee where co_id=$co_id  and br_id is null and `status` = 1";
		$request=$emplay->query($emplaysql);
		$this->ajaxReturn($request);
	}	
	/**
	 * 删除部门信息
	 * Enter description here ...
	 */
	public function deleteDepartment(){
		$tmcbarid=$_POST['p_id'];//获取部门值
    	$DeparmentStaff=D('Home/DepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$DeparmentStaff->deleteBranch($tmcbarid);//模糊查询员工\
		$this->ajaxReturn(1);
	}
	/**
	 * 导入部门
	 * Enter description here ...
	 */
	public function ExeclDepartment(){
//		C('LAYOUT_ON',TRUE);//加载布局文件
//		layout("home");
		$this->theme('default')->display('import_branch');
	
	}
	
	public function ExeclDepartments($res){
//		C('LAYOUT_ON',TRUE);//加载布局文件
//		layout("home");
		$this->assign('bar',$res);
		
		$this->theme('default')->display('DepartmentStaff:import_branch');
	}
	
	//每一列的数值是否有空值，如果不是空值则放回true
	private function isValidStr($v) {
		return $v && is_string($v) && count($v) > 0;
	}
	
	private function findDepByName($m, $pid, $name, $coId) {
		if ($pid == -1) {
			$cond['p_id'] = array('exp', 'IS NULL');
			$cond['co_id'] = $coId;
		} else
			$cond['p_id'] = $pid;
			$cond['name'] = $name;
		return $m->field('id')->where($cond)->find();
	}
	
	private function addDep($m, $pid, $name, $coId) {
		if ($pid > 0) $data['p_id'] = $pid;
		$data['name'] = $name;
		$data['co_id'] = $coId;
		return $m->data($data)->add();
	}
	
	public function addExeclDepartment() {
		$var = $_POST;
		$obj = array();
		//将json数据封装成数组
		foreach($var['list'] as $key=>$val){
			$arr = array();
			$arr[0] =$val['first'];
			$arr[1] =$val['second'];
			$arr[2] =$val['third'];
			$arr[3] =$val['four'];
			$arr[4] =$val['five'];
			array_push($obj, $arr);
		}
		$coId = LI('comId');
		try {
			$m_br = M('branch');
			foreach ($obj as $deps) {//循环数组的个数
				$pid = -1;
				for ($i = 0; $i < 5; $i++) {
					$depName = $deps[$i];
					if (!$this->isValidStr($depName)) break;
					$rec = $this->findDepByName($m_br, $pid, $depName, $coId);
					if ($rec) {
						$pid = $rec['id'];
					} else {
						$pid = $this->addDep($m_br, $pid, $depName, $coId);
					}
					if (!$pid) break;
				}
			}
			$this->ajaxReturn(1);
		} catch (\Exception $ex) {
			$this->ajaxReturn(0);
		}
	}

	
	/**
	 * 导入员工
	 * Enter description here ...
	 */
	public function ExeclStaff(){
		C('LAYOUT_ON',TRUE);//加载布局文件
		layout("home");
		$this->theme('default')->display('import_employee');
	}
	
	public function ExeclStaffs($res){
		$this->assign('sta',$res);
		$this->theme('default')->display('DepartmentStaff:import_employee');
	}
	
	public function addExeclStaff(){
		$var = $_POST;
		$obj = array();
		$comId=LI('comId');
		$m_emp=M('employee');
		$emprequest=$this->selectEmp($m_emp, $comId);
		//将json数据封装成数组
		foreach($var['list'] as $key=>$val){
			$arr = array();
			$arr[0] =$val['code'];
			$arr[1] =$val['name'];
			$arr[2] =$val['bar'];
			$arr[3] =$val['sex'];
			$arr[4] =$val['phone'];
			$arr[5] =$val['email'];
			$arr[6] =$val['type'];
			$arr[7] =$val['num'];
			$arr[8] =$val['province'];
			$arr[9] =$val['city'];
			array_push($obj, $arr);
		}		
		foreach ($obj as $emp){
				$iscode=$this->is_check($emp[0]);
				$isnum=$this->is_check($emp[7]);
				$isname=$this->is_check($emp[1]);			
				//根据工号去查询数据
				if($this->selectBycode($m_emp, $comId, $emp[0])){
					
					if($this->selectBynum($m_emp, $comId, $emp[6], $emp[7])){
						
						if($this->selectByname($m_emp, $comId, $emp[1], $emp[4], $emp[2]) || $this->selectByemail($m_emp, $comId, $emp[1], $emp[5], $emp[2])){
							//修改用户信息
								if($this->selectByname($m_emp, $comId, $emp[1], $emp[4], $emp[2])){
									$empid=$this->selectByname($m_emp, $comId, $emp[1], $emp[4], $emp[2]);
								}
								if($this->selectByemail($m_emp, $comId, $emp[1], $emp[5], $emp[2])){
									$empid=$this->selectByemail($m_emp, $comId, $emp[1], $emp[5], $emp[2]);
								}
								$this->upemp($m_emp, $empid, $comId, $emp[2], $emp[0], $emp[1], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9]);
							continue;
						}
						else{
							//添加用户信息
							$request=$this->addemp($m_emp, $comId, $emp[2], $emp[0], $emp[1], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9]);
							continue;

						}
					}
					else{
						//添加用户信息
						$request=$this->addemp($m_emp, $comId, $emp[2], $emp[0], $emp[1], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9]);
						continue;
					}

				}
				else{
						//添加用户信息
						$request=$this->addemp($m_emp, $comId, $emp[2], $emp[0], $emp[1], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9]);
						continue;
				}
				
				//根据身份证号去查询数据
				//根据用户名+手机号+部门去查询数据
				//根据用户+邮箱+部门去查询数据
	
		}
		
	}
	//添加员工方法
	public function addemp($m,$comId,$brId,$emp_code,$name,$sex,$phone,$email,$type,$num,$province,$city){
		$addbar=$this->selectBybar($comId, $brId);
		$addtype=$this->selectBytypeId($type);
		$data['co_id']=$comId;
		$data['br_id']=$addbar;
		$data['emp_code']=$emp_code;
		$data['name']=$name;
		$data['sex']=$sex;
		if($sex =="男" ){
			$data['sex']=M;
		}
		if($sex== "女"){
			$data['sex']=F;
		}
		$data['phone']=$phone;
		$data['email']=$email;
		$data['id_type']=$addtype;
		$data['id_num']=$num;
		$data['province']=$province;
		$data['city']=$city;
		$data['status']=1; //导入企业员工的时候，将status改为1 ，1.28
		return $m->data($data)->add();			
	}
	
	//修改员工方法
	public function upemp($m,$empid,$comId,$brId,$emp_code,$name,$sex,$phone,$email,$type,$num,$province,$city){
		$addbar=$this->selectBybar($comId, $brId);
		$addtype=$this->selectBytypeId($type);
		$id=$empid;
		$data['co_id']=$comId;
		$data['br_id']=$addbar;
		$data['emp_code']=$emp_code;
		$data['name']=$name;
		
		$data['sex']=$sex;
		if($sex =="男" ){
			$data['sex']=M;
		}
		if($sex== "女"){
			$data['sex']=F;
		}
		
		$data['phone']=$phone;
		$data['email']=$email;
		$data['id_type']=$addtype;
		$data['id_num']=$num;
		$data['province']=$province;
		$data['city']=$city;
		return $m->where('id='.$id)->save($data);			
	}
	//查询员工所有信息
	public function selectEmp($m,$co_id){
		$data['co_id']=$co_id;
		$data['status']=0;
		return $m->where($data)->select();
	}
	//校验表格中的数据不为空
	public function is_check($key){
		return $key && is_string($key) && strlen($key)>0;
	}
	//校验表格中的数据是否合法
	public function is_checkarray($emp){
			if($this->is_check($emp[0])){
				return 1;
			}else if($this->is_check($emp[7])){
				return 2;
			}else if($this->is_check($emp[1])){
				return 3;
			}else{
				return 0;
			}
	}
	//带工号的查询方法
	public function selectBycode($m,$coId,$code){
		$data['co_id']=$coId;
		$data['emp_code']=$code;
		return $m->where($data)->find();
	}
	//带证件号的查询方法
	public function selectBynum($m,$coId,$type,$num){
		//根据Type查出证件类型的id
		$dictionary=M('dictionary');
		if($type=="大陆居民身份证" || $type=="身份证"){
			$type="大陆居民身份证";
		}
		$dict['d_group']="id_type";
		$dict['d_value']=$type;
		$request=$dictionary->where($dict)->find();
		$dictId=$request['id'];
		$data['co_id']=$coId;
		$data['id_type']=$dictId;
		$data['id_num']=$num;
		return $m->where($data)->find();
	}
	//带姓名+手机+部门的方法
	public function selectByname($m,$coId,$name,$phone,$brId){
		//根据$brId查询出部门id
		$branch=M('branch');
		$br['co_id']=$coId;
		$br['name']=$brId;
		$brrequest=$branch->where($br)->find();
		$br_id=$brrequest['id'];
		
		$data['co_id']=$coId;
		$data['name']=$name;
		$data['phone']=$phone;
		$data['br_id']=$br_id;
		
		$request= $m->where($data)->find();
		return $request['id'];
	}
	//带姓名+邮箱+部门的方法
	public function selectByemail($m,$coId,$name,$email,$brId){
		//根据$brId查询出部门id
		$branch=M('branch');
		$br['co_id']=$coId;
		$br['name']=$brId;
		$brrequest=$branch->where($br)->find();
		$br_id=$brrequest['id'];
		
		$data['co_id']=$coId;
		$data['name']=$name;
		$data['email']=$email;
		$data['br_id']=$br_id;
		
		$request= $m->where($data)->find();
		return $request['id'];
	}
	//获取部门id
	public function selectBybar($coId,$brId){
		$branch=M('branch');
		$br['co_id']=$coId;
		$br['name']=$brId;
		$brrequest=$branch->where($br)->find();
		return $brrequest['id'];
	}
	//获取证件id
	public function selectBytypeId($type){
		$dictionary=M('dictionary');
		$dict['d_group']="id_type";
		if($type=="大陆居民身份证" || $type=="身份证"){
			$dict['d_value']="大陆居民身份证";
		}else{
			$dict['d_value']=$type;
		}
		$request= $dictionary->where($dict)->find();
		dump($request);
		return $request['d_key'];
	}
	
}