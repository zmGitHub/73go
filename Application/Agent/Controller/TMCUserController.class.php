<?php

/***
 *
 * 修改记录
 *
 * 2015/1/24		李浩源，统一整改ajax返回需要使用的ajaxReturn
 *
 */



namespace Agent\Controller;
use Agent\Logic\TmcempUserLogic;

use Agent\Logic\TmcUserLogic;
use Home\Logic\UserLogic;
use System\LoginInfo;
use Think\Controller;

class TMCUserController extends Controller {

	//代理商的登录
	//查看是否具有该用户 
	public function check_login(){
		$map['username']=$_POST['username'];
		$map['password']= md5($_POST['password']);
		$map['user_type']=array('exp','in(3,4)');
		$user = M('user');
		$result = $user->where($map)->find();//查询验证用户
		//保存企业信息
		if(!$result){
			$info = '帐号 ' . $map['username'] . ' 登录73goTMC网页失败';
			LOGS($type=1, $info, $map['username']);
			$this->ajaxReturn(0);
		}
		if ($result['status']==99) {
			$info = '帐号 ' . $map['username'] . ' 被禁用登录73goTMC网页失败';
			LOGS($type=1, $info, $map['username']);
			$this->ajaxReturn(-1);
		}
		$m = new UserLogic();
		$m->userLogins('', $result);
		session('user_type',$result['user_type']);
		if($result['user_type']== 3){
			$info = '帐号 ' . $map['username'] . ' 登录73goTMC网页成功';
			LOGS($type=1, $info);
			$this->ajaxReturn(1);       //TMC公司登陆
		}
		else{
			$info = '帐号 ' . $map['username'] . ' 登录73goOP网页成功';
			LOGS($type=1, $info);
			$this->ajaxReturn(2);       //TMC员工登陆
		}
		/* 
		
		if($result){
			$m = new UserLogic();
			$m->userLogins('', $result);
            if($result['user_type']== 3){
				$this->ajaxReturn(1);       //TMC公司登陆
            }
			else{
				$this->ajaxReturn(2);       //TMC员工登陆
			}

		}else{
			$this->ajaxReturn(0);
		} */
	}
	//代理商的注册信息
	
	//进行校验是否存在该代理商用户
	public function check_tmc(){
		$username = $_POST['username'];
		$user = M('user');
		$map['username'] = $username;
		$result= $user->where($map)->select();
		if($result){
			$this->ajaxReturn(1);
		}else{
			$this->ajaxReturn(0);
		}

	}
		
	public function add_tmc(){
		$map['register_time']=date('Y-m-d H:i:s',time());	
		$map['username']=$_POST['username'];
		$map['password'] = md5($_POST['password']);
		$map['user_type']=3;
		$map['status']=0;
		$m_user=M("user");
		$newUserId=$m_user->data($map)->add();
		if($newUserId){
			$data['u_id']=$newUserId;//获取刚才注册的公司的id；
			$data['name']=$_POST['username'];
			//获取5位不同的散列数作为TMC企业的编号
			$tmcCode = VNumGen('tmc_code');//公司的编号；
			$data['tmc_code'] = $tmcCode;
			$m_tmc = M("tmc");
			$newTmcId = $m_tmc->data($data)->add();
			//根据tmc_code查询当前用户id
			$tmcId = $m_tmc->where($data)->getField('id');



			$m_tmc_id['tmc_id']= $tmcId;
			$m_tmc_site=M("tmc_siteconfig");
			//添加tmcCode值site表
			$m_tmc_id['sub_url'] = 'http://'.C("TMC-HOSTING-SERVER")."/".$tmcCode;
			$m_tmc_site->data($m_tmc_id)->add();
			//查询最新数据的id
			$storeId = $m_tmc_site->where($m_tmc_id)->getField('id');
			$m_tmc_con=M("tmc_store_connection");
			$m_tmc_con->data('store_id='.$storeId)->add();

			$m_tmc_foot=M("tmc_store_foot");
			$m_tmc_foot->data('store_id='.$storeId)->add();
			$m_tmc_head=M("tmc_store_head_photo");
			$m_tmc_head->data('store_id='.$storeId)->add();
			$m_tmc_intro=M("tmc_store_introduction");
			$m_tmc_intro->data('store_id='.$storeId)->add();
			$m_tmc_lunbo_p=M("tmc_store_lunbo_photo");
			$m_tmc_lunbo_p->data('store_id='.$storeId)->add();
			$m_tmc_lunbo_w=M("tmc_store_lunbo_word");
			$m_tmc_lunbo_w->data('store_id='.$storeId)->add();
			$m_tmc_product_p=M("tmc_store_product_photo");
			$m_tmc_product_p->data('store_id='.$storeId)->add();
			$m_tmc_product_w=M("tmc_store_product_word");
			$m_tmc_product_w->data('store_id='.$storeId)->add();
			$m_tmc_web=M("tmc_store_web_word");
			$m_tmc_web->data('store_id='.$storeId)->add();
			$m_tmc_window=M("tmc_store_window");
			$m_tmc_window->data('store_id='.$storeId)->add();





			if($newTmcId){
				$m = new UserLogic();
				//TMC登陆后处理
				$m->userLogins($newUserId);

				//实例化 TMC 功能配置表
				$tmc_config=M('tmc_config_tbl');
				$Matt['tmc_id']= $newTmcId; //LI('tmcId');  --> 没必要再调用LI

				//在功能配置表中添加一条数据
				$datt=$tmc_config->data($Matt)->add();
				
				$this->ajaxReturn(1);
			}else{
				$this->ajaxReturn(0);
			}
		}	
	}
	
	
	
	
	
	}
	







