<?php
namespace Home\Controller;

use Think\Controller;
class IndexController extends Controller {
	/**
	 * 首页
	 *dfy
	 *2014-10-28下午08:52:39
	 *
	 * 2015/1/15 modified by Lanny Lee, 增加旗舰店支持。
	 */
    public function index()
	{
		C('LAYOUT_ON', FALSE);
		if (isTMCHosting()) {
			//旗舰店模式
			$siteinfo = getHostedTmcInfo();
			if ($siteinfo) {
				$FUrl = $_SERVER ['HTTP_REFERER'];
				//字符串处理 取url最后个 ? 后面的值
				$code = currTMCNum(); //substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
				redirect('/' . $code . "/FE/html/index.html?" . $code, 0, '页面跳转中...');
				//$this->theme('default')->display("tmc_index");
			} else {
				$this->theme('default')->display("tmc_under_constructing");
			}
		} else
			//73GO模式
			$this->theme('default')->display("73go_index");
	}
    
   /**
    * 登录页面 
    *dfy
    *2014-10-28下午08:52:47
    *2015-01-17 余卓燃 修改
    */
   public function login(){	
   		C('LAYOUT_ON',FALSE);
   		/*$id = LI('userId');
   		$tmc_id = session('tmc_id');
   		if($id && $tmc_id){		
   		LI()->clean();
    	session('user_type',null); 
    	$this->redirect('Index/TMC_index');
   		}else{
   		LI()->clean();
    	session('user_type',null); 
   		$User=M("user");
		$data=$User->where("id=".$id)->field("user_type")->find();
		$this->assign("data",$data);*/
    	$this->theme('default')->display("login");

   		
   }
   
   public function Register_agreement(){
   		$this->theme('default')->display("Register_agreement");
   }
   /**
	 * 退出后进入登录界面
	 * 余卓燃
	 */
	public function logout(){
		//清空session
		//C('LAYOUT_ON',FALSE);
		LI()->clean();
		$this->ajaxReturn(1);
		//$this->theme('default')->display('login');
	}

	/**
	 * 关于我们企业信息的链接页面
	 *姚文君
	 *2015-03-25 上午11:31:12
	 */
	public function about_us($key){
		C('LAYOUT_ON',FALSE);
		if($key == 1){
			$this->theme('default')->display("73go_about_us");
		} else if($key == 2){
			$this->theme('default')->display("73go_recruitment_info");
		} else if($key == 3){
			$this->theme('default')->display("73go_contact_us");
		} else if($key == 4){
			$this->theme('default')->display("73go_partner");
		}


	}
   
   /**
    * 注册个人员工页面
    *dfy
    *2014-10-28下午08:52:55
    */
  
   public function register_user(){
   		C('LAYOUT_ON',FALSE);
   		$this->theme('default')->display("Register_user");
   }
   /**   
    * 注册企业页面
    *dfy
    *2014-10-28下午08:53:08
    */
   public function register_co(){
  	 	C('LAYOUT_ON',FALSE);
   		$this->theme('default')->display("Register_co");
   }

	/**
	 * 用邮箱找回密码
	 *xbs
	 *2015-03-13上午11:26:08
	 */
	public function forget_password_email(){
		C('LAYOUT_ON',FALSE);
		$this->theme('default')->display("forget_password_email");
	}

	/**
	 * 用短信找回密码
	 *xbs
	 *2015-03-13上午11:26:08
	 */
	public function forget_password_phone(){
		C('LAYOUT_ON',FALSE);
		$this->theme('default')->display("forget_password_phone");
	}

   /**
    * 登录后跳转到修改企业信息页面
    *dfy
    *2014-10-28下午08:53:15
    */
   public function config_coinfo_basicinfo(){
   		$co_id=LI('comId');
   		$company = M('company');
   		$co_info = $company->where("id = ".$co_id)->select();
   		
   		
   		/*查询出dictionary 表中的数据进行 id_type*/
   		$dictionary=M("");  		
   		$datt=$dictionary->query("select * from 73go_dictionary where d_group='industry'");
		$datt11=$dictionary->query("select * from 73go_dictionary where d_group='co_scale'");
   		
   		//将字典表中的id_type 类型 传入页面   		
   		$this->assign("data",$datt);
   		$this->assign("data11",$datt11);
   		
   		//print_r($co_info);
   		$this->assign(co_info,$co_info);
   		//$xhprof_data = xhprof_disable();
		//print_r($xhprof_data);
		$this->theme('default')->display("config_coinfo_basicinfo");
		
   }
   
   /*
   修改企业登录账户信息页面

    */
   public function config_coinfo_account(){
   		//C('LAYOUT_ON',FALSE);
   		$id = LI('userId');
   		$company = M('company');
   		$co_info = $company->where("u_id = ".$id)->select();
   		$this->assign(co_info,$co_info);
   		$this->theme('default')->display("config_coinfo_account");
   }
   
   /*网站用户登陆信息*/
 	public function config_myinfo_personal(){
   		//C('LAYOUT_ON',FALSE);
   		//$a = LI();
   		//ECHO $a;exit;
   		$id = LI('userId');
   		$employee = M('employee');
   		$map['u_id'] = $id;
   		$result = $employee->where($map)->select();
   		
   		/*查询出dictionary 表中的数据进行 id_type*/
   		$dictionary=M("");  		
   		$datt=$dictionary->query("select * from 73go_dictionary where d_group='id_type'");
		$datt11=$dictionary->query("select * from 73go_dictionary where d_group='sex'");
   		
   		if($result[0]['status'] > 0){
   			
   			$company = M('company');
   			$branch = M(branch);
   			//$co_info = $company	->where("id = ".$result[0]['co_id'])->select();
   			$co_branch = $branch->where("status = 0 AND co_id = ".$result[0]['co_id'])->select();
			$join_co_request = M('join_co_request');
			$company_id = $join_co_request->field('co_id')->order('id desc')->limit(2)->where('u_id='.$id)->select();
			$co_info = $company->field('name,co_code')->where('id='.$company_id[0]['co_id'])->select();
			if(count($company_id) > 1) {
				$join_co_request->where('co_id = ' . $company_id[1]['co_id'])->delete();
			}
   		};
   		$this->assign(result,$result);
   		//print_r($result);
   		//将字典表中的id_type 类型 传入页面   		
   		$this->assign("data",$datt);
   		$this->assign("data11",$datt11);
   		
   		$this->assign(co_info,$co_info);
   		$this->assign(co_branch,$co_branch);
   		$this->theme('default')->display("config_myinfo_personal");
  	 }
  	 
   /*网站用户登陆信息*/
 	public function config_myinfo_acount(){
   		//C('LAYOUT_ON',FALSE);
   		//$a = LI();
   		//ECHO $a;exit;
   		$id =  LI('userId');//获取用户id
   		$user = M('user');
   		$result = $user->where('id = '.$id)->select();
   		$this->assign(result,$result);
   		$this->theme('default')->display("config_myinfo_acount");
   	}

	///---->此方法取消！ by Lanny Lee, 2015/1/15
	/*TMC主页 
	 *  通过url路由设置
	 * 判断如果为通过特殊链接则到这个方法
	 * 并保存公司信息
	 */
	/*
	public function  TMC_index(){
		C('LAYOUT_ON',FALSE);
		
		$sub_url = '984545' ;
		$M = M('');
		$sql = "select  t.id,t.name,t.short_name,t.legal_phone,t.address,s.style_sheet,s.sub_url,s.large_logo,s.small_logo    
					FROM 73go_tmc AS t
					LEFT JOIN 	73go_tmc_siteconfig AS s ON t.id = s.tmc_id
					WHERE s.sub_url = ".$sub_url;
		$tmc = $M->query($sql);
		session('tmc_id',$tmc[0]['id']);
		
		session('logo',$tmc[0]['small_logo']);
		session('style',$tmc[0]['style_sheet']);
		$this->assign('data',$tmc);
		$this->theme('default')->display("tmc_index");
		

	}*/
	///<----此方法取消！END
	
	
	//加载常旅客卡 页面
	public function config_myinfo_card(){
		$dictionary=M("dictionary");  
		$map['d_group'] = 'flight_card';		
   		$info=$dictionary->where($map)->select();
   		$id = LI('userId');
   		$employee = M('employee');
   		$em = $employee->where('u_id ='.$id)->select();
		$emp_id = $em[0]['id'];
   		$M = M('');
   		$sql = "SELECT  c.*,b.display_ex
   					FROM 73go_freq_traveller_card as c 
   					LEFT JOIN 73go_dictionary as b ON c.card_type = b.d_key 
   					WHERE b.d_group = 'flight_card' AND emp_id = ".$emp_id;
   		$result = $M->query($sql);
   		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
   		$link = " limit $Page->firstRow , $Page->listRows";
   		$result = $M->query($sql.$link);
   		
   		$this->assign('Page',$show);// 赋值分页输出
   		$this->assign('info',$info);
   		$this->assign('result',$result);
		$this->theme("default")->display("config_myinfo_card");

	}
	
	
	//加载配送地址页面( config_myinfo_dispaddr.html )
	public function config_myinfo_dispaddr(){
		$id = LI('userId');//获取用户id 
		$map['ow_type'] = session('user_type');
		$map['ow_id'] = $id;
		$dispatch_addr_mgnt = M('dispatch_addr_mgnt');
		$info = $dispatch_addr_mgnt->where($map)->select();
		$count=count($info);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$info = $dispatch_addr_mgnt->where($map)->limit($Page->firstRow,$Page->listRows)->select();
		
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('info',$info);
		$this->theme("default")->display("config_myinfo_dispaddr");

	}	
	//加配送地址
	public function add_dispaddr(){
		$id = LI('userId');
		$map['ow_type'] = session('user_type');
		$map['ow_id'] = $id;
		$map['name'] = $_POST['name'];
		$map['phone'] = $_POST['phone'];
		$map['province'] = $_POST['province'];
		$map['city'] = $_POST['city'];
		$map['address'] = $_POST['address'];
		$map['postcode'] = $_POST['postcode'];
		$dispatch_addr_mgnt = M('dispatch_addr_mgnt');
		$result = $dispatch_addr_mgnt->data($map)->add();
		if($result){
			$this->ajaxReturn(1);
		
		}else{
		
			$this->ajaxReturn(0);
		}
		
	
	
	
	}
	//删除配送地址
	public function del_dispaddr(){
		$id = $_POST['id'];
		$map['id'] = $id;
		$dispatch_addr_mgnt = M('dispatch_addr_mgnt');
		$result = $dispatch_addr_mgnt->where($map)->delete();
		if($result){
			$this->ajaxReturn(1);
		
			}else{
		
			$this->ajaxReturn(0);
		}
		
		
	
	
	
	}
	//加卡信息
	public function add_card(){
		
		$map['card_type'] = $_POST['card_type'];
		$map['card_num'] = $_POST['card_num'];
		
		$id = LI('userId');

		$employee = M('employee');
		$freq_traveller_card = M('freq_traveller_card');	
		$em = $employee->where('u_id ='.$id)->select();
		$map['emp_id'] = $em[0]['id'];
	
		$result = $freq_traveller_card->data($map)->add();

		if($result){
			$this->ajaxReturn(1);
		
		}else{
		
			$this->ajaxReturn(0);
		}
		
	
	
	}
	//删除常旅客卡
	public function del_card(){
		$id = $_POST['id'];
		$map['id'] = $id;
		$freq_traveller_card = M('freq_traveller_card');
		$result = $freq_traveller_card->where($map)->delete();
		if($result){
			$this->ajaxReturn(1);
		
		}else{
		
			$this->ajaxReturn(0);
		}
		
	
	}
	
	//企业主页
	public function mypage_co(){
		$companyM = D('Home/Index','Logic');
		$result=$companyM->get_mypage_co();
		$noticeM=D('Home/Index','Logic');
		$noticeList=$noticeM->get_co_bulletin();
		$this->assign('pages',$result);
		$this->assign('noticeList',$noticeList);
		$this->theme('default')->display('mypage_co');
	}
	
	public function ajax_mypage_user(){
		$empM = D('Home/Index','Logic');
		$result=$empM->get_mypage_user();
		if($result){
			$this->ajaxReturn($result,json);
		}
	}
	
	//企业员工主页
	public function mypage_user(){
//		$empM = D('Home/Index','Logic');
//		$result=$empM->get_mypage_user();
//		if($result){
//			$this->ajaxReturn($result,json);
//		}
		$count=D('Home/Index','Logic');
		$count=$count->get_count();
		$bulletinM=D('Home/Index','Logic');
		$bulletinList=$bulletinM->get_all_bulletin();
		
		$this->assign('pages',$count);
		$this->assign('bulletinList',$bulletinList);
		$this->theme('default')->display('mypage_user');
	}

	public function know_more(){
		C('LAYOUT_ON',FALSE);

		$this->theme('default')->display('73go_index_2nd');

	}
	/*验证码生成*/
	Public function verify (){
		ob_clean();
		$config = array(
			'fontSize' =>20, // 验证码字体大小
			'length' =>4, // 验证码位数
			'useNoise' => false, // 关闭验证码杂点
			'reset' => false // 验证成功后是否重置，————这里是无效的。
		);
		$Verify = new \Think\Verify($config);
		$Verify->codeSet = '0123456789';
		$Verify->entry();
	}
	/*验证码验证*/
	function check_verify(){
		$varify1 =$_POST['verify'];
		$config = array(
			'reset' => false // 验证成功后是否重置，—————这里才是有效的。
		);
		$verify = new \Think\Verify($config);
		echo $verify->check($varify1, "");
	}

}