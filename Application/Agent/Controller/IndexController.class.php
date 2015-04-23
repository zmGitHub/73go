<?php
namespace Agent\Controller;
use Think\Controller;
class IndexController extends Controller {
	/**
	 * 点击首页，退出登录状态并且跳转到登录页面
	 * 王月
	 * 2015-02-08
	 */
	public function index() {
		C('LAYOUT_ON',FALSE);
		LI()->clean();
		$this->theme('agent')->display('login_tmc');
	}

	/**
	 * 进入登录界面
	 *dfy
	 *2014-10-29下午06:42:48
	 */
	public function logout(){
		//清空session
		C('LAYOUT_ON',FALSE);
		LI()->clean();
		
		//todo:
		$this->theme('agent')->display('login_tmc');
	}
	
	public function Register_tmc_agreement(){
		$this->theme('agent')->display('Register_tmc_agreement');
	}
	public function login_tmc(){
		C('LAYOUT_ON',FALSE);
		$id=LI("userId");
		/*$Requesttable=M('');
		//语音类型
		$sql="select te.id,te.emp_code,te.`name` tename,tb.`name` tbname,te.phone,te.email,u.reg_name,u.id as uid,u.`password`,u.`status` from 73go_tmc_employee te,73go_user u,73go_tmc_branch tb
where te.u_id=u.id and te.tmcbr_id=tb.id ";

		$requestsion=$Requesttable->query($sql);*/
		
		//实例化M，查询user_type为 3------TMC企业       4-------TMC 员工
		$User=M("user");
		$data=$User->where("id=".$id)->field("user_type")->find();
		$this->assign("data",$data);
		
		$this->theme('agent')->display('login_tmc');

	}
    
    /**
     * 跳转到注册界面
     */
    public function register_tmc(){
    	C('LAYOUT_ON',FALSE);

        $this->theme('agent')->display("Register_tmc");
    }
    
    public function mypage_tmc(){
    	$tmcM = D('Agent/Index','Logic');
		$result=$tmcM->get_mypage_tmc();
		$result['tmc_cert'] = '';
		if($result['cert_val']=='0'){
			$result['tmc_cert'] = '未认证';
		} else if($result['cert_val']=='1'){
			$result['tmc_cert'] = '已认证';
		} else if($result['cert_val']=='2'){
			$result['tmc_cert'] = '待认证';
		} else if($result['cert_val']=='3'){
			$result['tmc_cert'] = '认证未通过';
		} 
		
		$bulletinM=D('Agent/Index','Logic');
		$bulletinList=$bulletinM->get_tmc_bulletin();
		
		$this->assign('pages',$result);
		$this->assign('bulletinList',$bulletinList);
        $this->theme('agent')->display("mypage_tmc");
    }
    
/**
 * 
 * 加载tmc 员工主页
 * 
 */
   
    public function mypage_op(){
    	
    	$tmcN=D('Agent/Index','Logic');
    	$result=$tmcN->get_mypage_op();
    	
		$vipM=D('Agent/Index','Logic');
		$vips=$vipM->get_vip();
		
		
    	$bulletinM=D('Agent/Index','Logic');
    	$bulletinList=$bulletinM->get_bulletin();
    	
    	$this->assign("data",$result);
    	$this->assign("vips",$vips);
    	$this->assign("bulletinList",$bulletinList);
       $this->theme('agent')->display('mypage_op');

    }
    
    
    
    
    
}