<?php
namespace Agent\Controller;
use Agent\Logic\NewDemandLogic;
use Org\Alipay\Notify;

use Think\Controller;
class NewDemandController extends Controller {
	/**
	 * 查询新需求单信息
	 * 控制器
	 * 创建者：董发勇
	 * 创建时间：2014-12-4下午06:56:54
	 */
	public function showNewDemandController(){
		C('LAYOUT_ON',TRUE);
		//加载布局文件
		layout("tmc");
		//增加客服信息
		$newDemandLogic = new NewDemandLogic();
		$request=$newDemandLogic->showNewDemandLogic1(0);
		$name = array();
		$phone = array();
		$qq = array();
		for($i=0;$i<count($request,2);$i++){
			$qsx_req = M("qsx_req");
			$qro = $request[$i]['qsx_rq_no'];
			$result1 = $qsx_req->where('qsx_rq_no='."'$qro'")->find();
			$id = $result1['u_id'];
			$employee = M("employee");
			$result2 = $employee->where('u_id='.$id)->find();
			$this->assign ( 'info', $result2 );
			array_push($name,$result2['name']);
			array_push($phone,$result2['phone']);
			array_push($qq,$result2['qq']);
			$this->assign ( 'u_name', $name );
			$this->assign ( 'u_phone', $phone );
			$this->assign ( 'u_qq', $qq );
		}

		$count=count($request);
		$Page  = new \Think\Page($count,4);
		$show  = $Page->show();// 分页显示输出
		$request=$newDemandLogic->showNewDemandLogic1($Page);
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('nedemand',$request);
		$this->theme('agent')->display('qsx_req_handle');
	}
	/**
	 * 抢单
	 * 将轻松行接单状态修改为1,将当前tmc登录用户id赋值给recemp_id
	 * 创建者：董发勇
	 * 创建时间：2014-12-4下午08:53:06
	 */

	/**
	 * 修改需求单过期后 ，TMC员工依然可以抢单成功的bug
	 * 修改者：王月
	 * 修改时间：2015-3-16
	 */
	public function grabNewDemandController(){
		$qsx_rec=M('tmc_qsx_rec');
		$datarec['status']=1;
		$datarec['rec_emp_id']=LI('tmcempId');
		$datarec['rec_time']= date('Y-m-d H:i:s',time());
		$recid=$_POST['recid'];
		$currentStatus = $qsx_rec->where( 'id='.$recid )->getField('status');

		//查询出过期的时间
		$cond['d_group']='qsx_set';
		$cond['d_key']='req_ot_min';
		$dicM=M('dictionary');
		$min=$dicM->where($cond)->getField('d_value');

		//判断是否过期
		$distrTime=$qsx_rec->where('id='.$recid)->field('distr_time')->select();
		$today=date('Y-m-d H:i:s',time());
		$submitTime = $distrTime[0]['distr_time'];
		$minute=((strtotime($submitTime)-strtotime($today))/60)+$min;
		$minute=floor($minute);

		if($minute>0) {
			$request=$qsx_rec->where('id='.$recid." AND status='0'")->data($datarec)->save();
			if($request){
				$this->ajaxReturn(1);
			} else{
				if($currentStatus == 3){
					$this->ajaxReturn(3);
				}else{
					$this->ajaxReturn(0);
				}
			}
		} else {
			$this->ajaxReturn(-1);
		}
		/*$request=$qsx_rec->where('id='.$recid." AND status='0'")->data($datarec)->save();
    	if($request){
    		$this->ajaxReturn(1);
    	} else{
    		if($currentStatus == 3){
    			$this->ajaxReturn(3);
    		}else{
    			$this->ajaxReturn(0);
    		}
    		
    	}*/
	}
	
}