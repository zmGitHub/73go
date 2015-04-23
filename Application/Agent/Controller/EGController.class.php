<?php
namespace Agent\Controller;
use Think\Controller;
/**
 * TMC
 * 企业：Enterprise
 * 工作组：Group
 * 创建者：董发勇
 * 创建时间：2014-11-19上午09:51:50
 *
 *
 */
class EGController extends Controller {    
	
    public function index(){
    	
    	$tmc['u_id']=LI("userId");//判断当前企业登陆的id值
    	$tmctable=M('tmc');
    	$requesttmc=$tmctable->where($tmc)->find();
    	$tmcid=$requesttmc[id];//获取tmc的id值
    	$tmcname=M('');
    	$sql="select a.id,a.`name`,a.tmc_id from  73go_tmc_team as  a where a.status=0 and a.tmc_id=$tmcid";
    	$resultname=$tmcname->query($sql);
		
		foreach($resultname as $key=>$val){
			
		$sqllistname="select a.id,e.`name` as ename,b.id as bid from 73go_tmc_team as a LEFT JOIN 73go_tmc_team_member as b on
							a.id=b.team_id LEFT JOIN 73go_tmc_employee as e ON
							e.id=b.emp_id where a.tmc_id=$tmcid and a.id=".$val['id'];
		$resultnamelist=$tmcname->query($sqllistname);
		$resultname[$key]['gro'] = $resultnamelist;
		
		$sqllistcom="select a.id,cc.`name` as ccname,c.id as cid from 73go_tmc_team as a LEFT JOIN 73go_tmc_team_co as c ON
							a.id=c.team_id LEFT JOIN 73go_company as cc ON
							cc.id=c.co_id where a.tmc_id=$tmcid and a.id=".$val['id'];
		$resultcomlist=$tmcname->query($sqllistcom);
		$resultname[$key]['com'] = $resultcomlist;
		}
		$this->assign('tsnamelist',$resultname);
    	C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		$this->theme('agent')->display('tmc_team_mgnt');
    }
    /**
     * 添加工作组
     * 创建者：董发勇
     * 创建时间：2014-11-20下午03:21:10
     */
    public function addteam(){
    	$tmc['u_id']=LI("userId");//判断当前企业登陆的id值
    	$tmctable=M('tmc');
    	$requesttmc=$tmctable->where($tmc)->find();
    	
    	$data['tmc_id']=$requesttmc[id];
    	$data['time']=date('Y-m-d H:i:s',time());
    	$data['name']=$_POST['teamname'];
    	$data['status']=0;
    	$tmc_tema=M('tmc_team');
    	
    	$request=$tmc_tema->data($data)->add();
    	
    	if($request){
    	echo $this->ajaxReturn(1);
    	}
    	else{
    	echo $this->ajaxReturn(0);
    	}
    }
    /**
     * 修改工作组
     * 创建者：董发勇
     * 创建时间：2014-11-20下午03:25:50
     */
    public function updateteam(){
    	$tmc_teamid=$_POST['id'];
    	$data['status']=99;
    	$tmc_tema=M('tmc_team');
    	$request=$tmc_tema->where('id='.$tmc_teamid)->save($data);
    	if($request){
    	echo $this->ajaxReturn(1);
    	}
    	else{
    	echo $this->ajaxReturn(0);
    	}
    }
    /**
     * 显示组下面的用户
     * 创建者：董发勇
     * 创建时间：2014-11-21下午05:33:12
     */
    public function showteamnumber(){
//    	$tmc['u_id']=LI("userId");//判断当前企业登陆的id值
//    	$tmctable=M('tmc');
//    	$requesttmc=$tmctable->where($tmc)->find();
//    	$tmc_id=$requesttmc[id];
//    	
    	$team_id=$_POST['id'];
    	$tmc_id=$_POST['tmc_id'];
    	
    	session('addteam_id',$team_id);
    	
    	
		$mnumbertable=M('');
    	$sql="select id,emp_code,name,phone,email  from 73go_tmc_employee where tmc_id = $tmc_id and id not IN
 				(select emp_id from 73go_tmc_team_member where team_id = $team_id)";
    	$request=$mnumbertable->query($sql);
    	echo $this->ajaxReturn($request);
//    	$this->assign('temaname',$request);
//    	if($request){
//    	echo $this->ajaxReturn(1);
//    	}
//    	else{
//    	echo $this->ajaxReturn(0);
//    	}
    }
    
    /**
     * 添加组成员
     * 创建者：董发勇
     * 创建时间：2014-11-20下午03:22:06
     */
    public function addteamnumber(){
    	$Ajaxdate['team_id']=session(addteam_id);
		$Ajaxdate['emp_id']=$_POST['str'];
		$Ajaxdate['time']=date('Y-m-d H:i:s',time());
		$travel_policy=M('tmc_team_member');
		$requets=$travel_policy->data($Ajaxdate)->add();
    	if($requets){
    		echo $this->ajaxReturn(1);
    	}
    	else{
    		echo $this->ajaxReturn(0);
    	}
    	destory(addteam_id);
    }
    /**
     * 显示组企业
     * 创建者：董发勇
     * 创建时间：2014-11-24上午10:22:15
     */
    public function showteamco(){
    	$team_id=$_POST['id'];
    	session('team_id_co',$team_id);
		$mnumbertable=M('');
    	$sql="select * from 73go_company where id not IN(select co_id from 73go_tmc_team_co where team_id =$team_id)";
    	$request=$mnumbertable->query($sql);
    	echo $this->ajaxReturn($request);
    }
    /**
     * 添加组企业
     * 创建者：董发勇
     * 创建时间：2014-11-20下午03:24:02
     */
    public function addteamco(){
    	$Ajaxdate['team_id']=session(team_id_co);
		$Ajaxdate['co_id']=$_POST['str'];
		$Ajaxdate['time']=date('Y-m-d H:i:s',time());
		$travel_policy=M('tmc_team_co');
		$requets=$travel_policy->data($Ajaxdate)->add();
    	if($requets){
    		echo $this->ajaxReturn(1);
    	}
    	else{
    		echo $this->ajaxReturn(0);
    	}
    	destory(team_id_co);
    }
    /**
     * 修改重命名
     * 创建者：董发勇
     * 创建时间：2014-11-24上午11:04:10
     */
    public function unpdateteam(){
    	$tmc_teamid=$_POST['id'];
    	$data['name']=$_POST['teamname'];
    	$tmc_tema=M('tmc_team');
    	$request=$tmc_tema->where('id='.$tmc_teamid)->save($data);
    	if($request){
    	echo $this->ajaxReturn(1);
    	}
    	else{
    	echo $this->ajaxReturn(0);
    	}
    }
		/**
		 * 删除组成员
		 * 创建者：董发勇
		 * 创建时间：2014-11-25上午11:41:36
		 */
		public function deletemember(){
			$id=$_POST['userid'];
			$tmc_temembertal=M('tmc_team_member');
			$request=$tmc_temembertal->delete($id);
			echo $this->ajaxReturn($request);
		}
		/**
		 * 删除组企业
		 * 创建者：董发勇
		 * 创建时间：2014-11-25上午11:42:03
		 */
		public function deleteco(){
			$id=$_POST['userid'];
			$tmc_temecotal=M('tmc_team_co');
			$request=$tmc_temecotal->delete($id);
			echo $this->ajaxReturn($request);
		}
		
}