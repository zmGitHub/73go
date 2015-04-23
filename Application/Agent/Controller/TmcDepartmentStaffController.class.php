<?php
namespace Agent\Controller;
use Agent\Logic\TmcDepartmentStaffLogic;
use Common\Logic\CommonLogic;
use Think\Controller;
/**
 * TMC部门与员工业务层
 * @author dfy
 * @2014-12-6 下午03:32:51
 */
class TmcDepartmentStaffController extends Controller{
	/**
	 * 查看tmc部门信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:27:45
	 */
	public function showTmcDepartment(){
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
		C('LAYOUT_ON',TRUE);
		layout("tmc");
        $this->theme('agent')->display("tmc_employee_list");
	}
	
	/**
	 * 根据id查询tmc部门信息
	 * Enter description here ...
	 */
	public function showDepartmentById(){
		$id=$_POST['p_id'];
		$Branch=M('tmc_branch');
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
		$data['br_code']=$_POST['coid'];
		$data['name']=$_POST['coname'];
		$Branch=M('tmc_branch');
		$request=$Branch->where('id='.$id)->save($data);
		$this->ajaxReturn(($request)? 1 : 0);
	}
	
	/**
	 * 添加TMC部门信息
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午03:50:11
	 */
	public function addTmcDeparment(){
		$tmc_id = LI('tmcId');
    	$p_id=$_POST['p_id'];
    	$br_code=$_POST['ddbarchcoid'];
    	$name=$_POST['ddbarchname'];
		$tmcDeparmentStaff=D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$tmcDeparmentStaff->addTmcDepartmentLogic($tmc_id,$p_id,$br_code,$name);//添加部门
		$this->ajaxReturn(($request==1)? 1 : 0);
	}


	/**
	 * 获取指定的部门的员工列表，带子部门。
	 *
	 */
	public function showTmcDepartmentStaff() {
		$brId = $_POST['br_id'];
		$m = new CommonLogic();
		$this->ajaxReturn($m->getTmcBranchEmps($brId), 'JSON');
	}

	/**
	 * 获取当前登录的TMC所有员工列表。
	 */
	public function showCurrTmcStaff() {
		$tmcId = LI('tmcId');
		$m = new CommonLogic();
		$this->ajaxReturn($m->getEmpOfTmc($tmcId), 'JSON');
	}


	/**
	 * 查询未分配部门的员工
	 * Enter description here ...
	 */
	public function fenDepartmentStaff(){
		$tmc_id=LI('tmcId');
		$emplay=M('tmc_employee');
		$emplaysql="select id,emp_code,`name`,phone ,email  from 73go_tmc_employee where tmc_id=$tmc_id  and tmcbr_id is null and `status` = 0";
		$request=$emplay->query($emplaysql);
		$this->ajaxReturn($request);
	}
	/**
	 * 模糊查询部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:33:39
	 */
	public function showTmcStaffLike(){
		$tmcbarid=$_POST['tmcbarid'];//获取部门值
    	$eplike=$_POST['eplike'];//获取模糊搜索值
    	$tmcDeparmentStaff=D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$tmcDeparmentStaff->showTmcStaffLikeLogic($tmcbarid,$eplike);//模糊查询员工
    	$this->ajaxReturn($request);
	}
	/**
	 * 删除部门下的员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:37:08
	 */
	public function deleteTmcStaff(){
		$id=$_POST['str'];
		$tmcDeparmentStaff=D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$request=$tmcDeparmentStaff->deleteTmcStaffLogic($id);//模糊查询员工
		$this->ajaxReturn($request);
	}


	private function prepareStaffView() {
		$data['tmc_id']=LI('tmcId');//获取tmc的id值
		$data['status']=0;
		$tmcemployeetable=M('tmc_branch');
		$request=$tmcemployeetable->where($data)->select();//获取当前企业下的所有部门信息
		$this->assign('branchList',$request);//保存部门信息
		$es = D('Common/Common', 'Logic');
		$group=$es->showDictionay(id_type);
		$this->assign('grouplist',$group);//保存证件类型信息
		$this->assign('groupjson',json_encode($group));
		//加载布局文件
		C('LAYOUT_ON',TRUE);
		layout("tmc");
	}




	/**
	 * 添加员工界面
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:40:24
	 */
	public function showaddTmcStaff(){
		$this->prepareStaffView();
    	$this->theme('agent')->display('tmc_employee_detail');
	}
	/**
	 * 添加员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:43:56
	 */
	public function addTmcStaff(){
		$data['tmc_id'] = LI('tmcId');//tmc企业id
	 	$data['tmcbr_id']=$_POST['barch'];
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
	 	$tmc_employee=M('tmc_employee');
	 	$request=$tmc_employee->add($data);
    	$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 修改员工
	 * 根据id查询员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:47:24
	 */
	public function showTmcStaffById($emps_id){
		$this->prepareStaffView();

		$branche=M('');
		$co_idbr=LI('tmcId');
		$branchs = D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$branch = $branchs->getDeprtmentTree();
		$this->assign('branchtree',$branch);

		$branchesql="select id,name from 73go_tmc_branch where tmc_id=$co_idbr and status=0";
		$branrequest=$branche->query($branchesql);
		$this->assign('brupdate',$branrequest);
    	$tmcemptable=M('tmc_employee');
    	$request=$tmcemptable->where('id='.$emps_id)->find();
		$this->assign('tmcemp',$request);//保存员工信息
		//得到身份类型和身份号码 dx 15-3-19
		$id_info = M('dictionary');//实例化字典表
		$id_type = $request['id_type'];
		$result = $id_info->where('id='."'$id_type'")->select();
		$this->assign('tmcempId',$result[0]);//二位数组
		if(LI('userType') == 3){
			$this->theme('agent')->display('tmc_employee_update');
		}else{
			$this->theme('agent')->display('tmc_employee_edit');
		}

	}
	/**
	 * 修改员工
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午04:51:42
	 */
	public function updateTmcStaff(){
		$id=$_POST['empid'];
	 	$data['emp_code']=$_POST['emp_code'];
	 	$data['tmcbr_id']=$_POST['barch'];
	 	$data['name']=$_POST['name'];
	 	$data['sex']=$_POST['sex'];
	 	$data['phone']=$_POST['phone'];
	 	$data['qq']=$_POST['qq'];
	 	$data['email']=$_POST['email'];
	 	$data['id_type']=$_POST['id_type'];
	 	$data['id_num']=$_POST['id_num'];
	 	$data['province']=$_POST['province'];
	 	$data['city']=$_POST['city'];
    	$tmcemtable=M('tmc_employee');
	 	$request=$tmcemtable->where('id='.$id)->save($data);
    	$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 删除部门
	 * Enter description here ...
	 */
	public function deleteDepartment(){
		$id=$_POST['p_id'];
		$tmcDeparmentStaff=D('Agent/TmcDepartmentStaff','Logic');//调用TMC部门员工业务处理层
		$tmcDeparmentStaff->deleteBranch($id);//模糊查询员工
		$this->ajaxReturn(1);
	}
	//导入部门
	public function TMCExeclDepartment(){
		$this->theme('agent')->display('import_branch');
	}
	public function TMCExeclDepartments($res){
		$this->assign('bar',$res);
		$this->theme('agent')->display('TmcDepartmentStaff:import_branch');
	}
	//每一列的数值是否有空值，如果不是空值则放回true
	private function isValidStr($v) {
		return $v && is_string($v) && count($v) > 0;
	}
	
	private function findDepByName($m, $pid, $name, $coId) {
		if ($pid == -1) {
			$cond['p_id'] = array('exp', 'IS NULL');
			$cond['tmc_id'] = $coId;
		} else
			$cond['p_id'] = $pid;
			$cond['name'] = $name;
		return $m->field('id')->where($cond)->find();
	}
	
	private function addDep($m, $pid, $name, $coId) {
		if ($pid > 0) $data['p_id'] = $pid;
		$data['name'] = $name;
		$data['tmc_id'] = $coId;
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
		$coId = LI('tmcId');
		try {
			$m_br = M('tmc_branch');
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
	//导入员工
	public function TMCExeclStaff(){
		$this->theme('agent')->display('import_employee');
	}
	public function TMCExeclStaffs($res){
		$this->assign('sta',$res);
		$this->theme('agent')->display('TmcDepartmentStaff:import_employee');
	}
	
	public function addExeclStaff(){
		$var = $_POST;
		$obj = array();
		$comId=LI('tmcId');
		$m_emp=M('tmc_employee');
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
		$data['tmc_id']=$comId;
		$data['tmcbr_id']=$addbar;
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
		$data['status']=1;//导入tmc员工的时候，将status改为1 ，1.28
		return $m->data($data)->add();			
	}
	
	//修改员工方法
	public function upemp($m,$empid,$comId,$brId,$emp_code,$name,$sex,$phone,$email,$type,$num,$province,$city){
		$addbar=$this->selectBybar($comId, $brId);
		$addtype=$this->selectBytypeId($type);
		$id=$empid;
		$data['tmc_id']=$comId;
		$data['tmcbr_id']=$addbar;
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
		$data['tmc_id']=$co_id;
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
		$data['tmc_id']=$coId;
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
		$data['tmc_id']=$coId;
		$data['id_type']=$dictId;
		$data['id_num']=$num;
		return $m->where($data)->find();
	}
	//带姓名+手机+部门的方法
	public function selectByname($m,$coId,$name,$phone,$brId){
		//根据$brId查询出部门id
		$branch=M('tmc_branch');
		$br['tmc_id']=$coId;
		$br['name']=$brId;
		$brrequest=$branch->where($br)->find();
		$br_id=$brrequest['id'];
		
		$data['tmc_id']=$coId;
		$data['name']=$name;
		$data['phone']=$phone;
		$data['br_id']=$br_id;
		
		$request= $m->where($data)->find();
		return $request['id'];
	}
	//带姓名+邮箱+部门的方法
	public function selectByemail($m,$coId,$name,$email,$brId){
		//根据$brId查询出部门id
		$branch=M('tmc_branch');
		$br['tmc_id']=$coId;
		$br['name']=$brId;
		$brrequest=$branch->where($br)->find();
		$br_id=$brrequest['id'];
		
		$data['tmc_id']=$coId;
		$data['name']=$name;
		$data['email']=$email;
		$data['br_id']=$br_id;
		
		$request= $m->where($data)->find();
		return $request['id'];
	}
	//获取部门id
	public function selectBybar($coId,$brId){
		$branch=M('tmc_branch');
		$br['tmc_id']=$coId;
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
		return $request['id'];
	}
}