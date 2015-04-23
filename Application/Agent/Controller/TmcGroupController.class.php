<?php
namespace Agent\Controller;
use Agent\Logic\TmcGroupLogic;
use Think\Controller;
/**
 * TMC工作组业务层
 * @author dfy
 * @2014-12-6 下午03:32:51
 */
class TmcGroupController extends Controller{
	/**
	 * 查看TMC工作组
	 *dfy  2014-12-6  下午03:33:56
	 */
	public function showTmcGroup(){
		C('LAYOUT_ON',TRUE);//允许布局
		layout("tmc");//加载布局文件
		$logic = new TmcGroupLogic(); //调用工作组业务处理层
		$data = $logic->showTmcGroupLogic();//查看工作组
		$this->assign('tsnamelist',$data);
		$this->theme('agent')->display('tmc_team_mgnt');
	}
	/**
	 * 添加TMC工作组
	 *dfy  2014-12-6  下午04:15:57
	 */
	public function addTmcGroup(){
		$teamname=$_POST['teamname'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->addTmcGroupLogic($teamname);//添加工作组
		$this->ajaxReturn(($request==1)? 1 : 0);
	}
	/**
	 * 删除TMC工作组
	 *dfy  2014-12-6  下午09:51:04
	 */
	public function deleteTmcGroup(){
		$tmc_teamid=$_POST['id'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->deleTmcGroupLogic($tmc_teamid);//删除工作组
		$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 修改工作组
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午01:10:39
	 */
	public function updateTmcGroup(){
		$tmc_teamid=$_POST['id'];
		$tmc_teamname=$_POST['teamname'];
		$logic = new TmcGroupLogic(); //调用工作组业务处理层
		$this->ajaxReturn($logic->updateaTmcGroup($tmc_teamid, $tmc_teamname));
	}
	/**
	 * 查看工作组下面的成员
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午01:37:46
	 */
	public function showTmcGroupNumber(){
		$team_id=$_POST['id'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->showTmcGroupNumberLogic($team_id);//查看工作组下面的成员
		$this->ajaxReturn($request);
	}
	/**
	 * 添加工作组成员
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午01:56:41
	 */
	public function addTmcGroupNumber(){
		$emp_id=$_POST['str'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->addTmcGroupNumberLogic($emp_id);//添加工作组下面的成员
		$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 删除工作组成员
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:05:15
	 */
	public function deleteTmcGroupNumber(){
		$userid=$_POST['userid'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->deleteTmcGroupNumberLogic($userid);//删除工作组下面的成员
		$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 显示工作组企业
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:28:13
	 */
	public function showTmcGroupCompany(){
		$team_id=$_POST['id'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->showTmcGroupCompanyLogic($team_id);//显示工作组企业
		$this->ajaxReturn($request);
	}
	/**
	 * 添加工作组企业
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:33:57
	 */
	public function addTmcGroupCompany(){
		$str=$_POST['str'];
		$team_id = $_POST['team_id'];
		$str = substr($str, 0,-1);
		$id_array = explode(',', $str);
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->addTmcGroupCompanyLogic($team_id,$id_array);//添加工作组企业
		//$this->ajaxReturn($request);
		$this->ajaxReturn(($request)? 1 : 0);
	}
	/**
	 * 删除工作组企业
	 * 创建者：董发勇
	 * 创建时间：2014-12-8下午02:39:19
	 */
	public function deleteTmcGroupCompany(){
		$userid=$_POST['userid'];
		$tmcGroup=D('Agent/TmcGroup','Logic');//调用工作组业务处理层
		$request=$tmcGroup->deleteTmcGroupCompanyLogic($userid);//删除工作组企业
		$this->ajaxReturn(($request)? 1 : 0);
	}
}