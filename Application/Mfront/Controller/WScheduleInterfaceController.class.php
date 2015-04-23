<?php

namespace Mfront\Controller;

use Think\Controller;

/**
 * 微信响应系统
 * 董发勇
 * 语音请求，文本请求，网页请求，事件推送
 */
class WScheduleInterfaceController extends Controller {
	//*******************************************************//
	/*
	 * 生成关注事件接口(添加用户)
	*/
	public function addSubscribeEvent(){
		$user=M('user');
			$data['register_time']=date('Y-m-d H:i:s');
			$data['user_type']=1;
			$data['wx_openid']=$_REQUEST["openid"];
		$request=$user->add($data);
		$employee=M('employee');
			$emdata['u_id']=$request;
			$emdata['name']=$_REQUEST["nickname"];
			$emdata['sex']=$_REQUEST["sex"];
			$emdata['province']=$_REQUEST["province"];
			$emdata['city']=$_REQUEST["province"];
		$emrequest=$employee->add($emdata);	
	}
	//语音预定产生轻松行需求
	public function addVoiceQsxReq(){
		$fromusername= $_REQUEST ["fromusername"];
		$createtime= $_REQUEST["createtime"];
		$msgID= $_REQUEST ["msgID"];
		$msgtype= $_REQUEST ["msgtype"];
		$mediaID= $_REQUEST ["mediaID"];
		$formart= $_REQUEST ["formart"];
		$recognition= $_REQUEST ["recognition"];	
		//根据openid获取userid
		$user=M('user');
		$userdata['wx_openid']=$fromusername;
		$userdata['status']=0;
		$userrequest=$user->where($userdata)->find();
		//将语音内容存入到轻松行需求表中
		$qsx_reqe=M('qsx_req');
		
		$reqdata['u_id']=$userrequest['id'];
		$reqdata['qsx_rq_no']=VNumGen('qsx_req');
		
		$reqdata['submit_time']=date('Y-m-d H:i:s');
		$reqdata['req_from']=2;
		$reqdata['voice_txt']=$recognition;
		$reqdata['status']=0;
		$qsxrequest=$qsx_reqe->add($reqdata);
		
		$public=new WPublicController();
		$request=$public->opfen($qsxrequest);
		
	}
	
	
	/*
	 * 语音预定
	 */
	public function receiveVoice(){
		layout(false);
		$this->display("voice");
	}
	
	/*
	 * 响应语音订单回调函数
	 * 将代理公司制作的方案响应到微信用户中去
	 */
	public function callbackVoiceOrder($wx_openid,$req_id){
		
		$public=new WPublicController();
		$access_token =$public->initAccessToken();
		$openid=$wx_openid;
		$reqid=$req_id;
		$data = '{
    		"touser":"'.$openid.'",
   			"msgtype":"text",
    		"text":{"content":"感谢您使用轻松行预定,我们出行顾问已经为你私人制作了方案'."\n".'<a href=\"http://weixin.73go.cn/Mfront/WScheduleInterface/showSolutionOrder?reqid='.$reqid.'\">查看全文</a>"}
			 	 }';
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
		$result = $public->http_requestscheme($url,$data);
		
		
		if($result){
			$retArr = array("errno"=>0, "status"=>"success", "errmsg"=>"success");
			echo json_encode( $retArr );
		}
		else
		{
			$retArr = array("errno"=>1, "status"=>"success", "errmsg"=>"failed");
			echo json_encode( $retArr );
		}
	}
	/*
	 * 查询方案信息
	 */
	public function showSolutionOrder(){
		$reid=$_GET["reqid"];
		//查询出需求信息
		$requestt=M('qsx_req');
		$solutionre=$requestt->where('id='.$reid)->find();
		$this->assign("requestt", $solutionre);
		//查询出方案信息
		$solution=M('');
		$sql2="select * from 73go_qsx_solution where req_id=$reid";
		$showSolution=$solution->query($sql2);	
		foreach($showSolution as $key=>$val){
			$empuser=M('');
			$empusersql="select * from 73go_tmc_employee where u_id=".$val['u_id'];
			$emprequest=$empuser->query($empusersql);
			$showSolution[$key]['empname']=$emprequest[0]['name'];//保存方案制作人姓名
			$showSolution[$key]['empphone']=$emprequest[0]['phone'];//保存方案制作人电话
			$comid=$emprequest[0]['tmc_id'];
			$comsql="select * from 73go_tmc where id=$comid";
			$comrequest=$empuser->query($comsql);
			$showSolution[$key]['comname']=$comrequest[0]['name'];
		}
		//讲数组转换成json
		$showSolutionjson=json_encode($showSolution,true);
		dump($showSolutionjson);
		$con=count($showSolution); 
		$this->assign("con",$con);
		$this->assign("solution", $showSolutionjson);
		layout(false);
		$this->theme('default')->display("scheme");
	}
	/*
	 * 保存方案信息
	 */
	public function addSolutionOrder($id){
		$pid=$id;//获取id
		$solution=M('qsx_solution');
		$data['status']=1;
		$solution->where('psid='.$pid)->save($data);
		layout(false);
		$this->display("tsaddsolution");
	}
	//*******************************************************//
	/*
	 * 点击view菜单接口
	 * 轻松预定
	 * 获取openid
	 */
	public function receiveEasybooking(){
		$public = new WPublicController();
		$public->initOpenid();

		$emergencyM=D("Home/Emergency","Logic");
		$book_info=$emergencyM->emergency_book();

	    $this->assign("book_info",$book_info);//传值

		//加载布局文件
		layout(false);
		$this->theme('default')->display("reservation");
	}

	/*
	 * 将轻松预定的信息保存到数据库
	 */
	public function addEasybooking(){

		$qsx_reqe=M('qsx_req');
		$reqdata['qsx_rq_no']=VNumGen('qsx_req');
		$reqdata['submit_time']=date('Y-m-d H:i:s');
		$reqdata['req_from']=2;
		$reqdata['u_id']=LI('userId');//用户id

		$reqdata['tr_no']=I("post.trNumber");//tr号
		$reqdata['leave_date']=date('Y-m-d H:i:s');//出发时间
		$reqdata['back_date']=date('Y-m-d H:i:s');//返回时间
		$reqdata['from_city']=I("post.start");//出发城市
		$reqdata['to_city']=I("post.end");//到达城市
		$reqdata['other_content']=I("post.detail");//其它要
		if($reqdata['other_content'] != " " || $reqdata['other_content'] != null){
			$reqdata['other']=1;
		}
		$reqdata['status']=0;

		$qsxrequest=$qsx_reqe->add($reqdata);

		$public=new WPublicController();
		$request=$public->opfen($qsxrequest);
		if($request){
			echo 1;
		}else{
			echo 2;
		}
	}
	/*
	 * 点击view菜单接口
	* 自助预定
	* 获取openid
	*/
	public function receiveHelpbooking(){
		$public =new WPublicController();
		$code=$_GET["code"];
		$appid="wxe88f8959823e92a2";
		$appsecret="e69408e7a7237a285712bd9f0dba7770";
		$access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
		$access_token_json = $public->https_request($access_token_url);
		$access_token_array = json_decode($access_token_json, true);
		$openid = $access_token_array['openid'];
		$this->assign("openid", $openid);//注册id
		layout(false);
		$this->theme('default')->display("selfhelpbookingwx");
	}
	/*
	 * 将自助预订的信息显示出来
	 */
	public function shouwSelfbooking(){
		//加载book_self_result页面
		$roadtype = $_POST['roadtype'];//S 单程和D 往返
		$startcity = $_POST['departure_city'];
		$arrivalcity = $_POST['arrived_city'];
		$starttime   = $_POST['start_time'];
		//$airtype  = $_POST['airtype'];
		/* 调取机票接口*/
		$url = "http://localhost:8080/TicketQuery-0.0.1-SNAPSHOT/query?proto=2&FlightSearchType=S&startcity=".$startcity."&tocity=".$arrivalcity."&startdate=".$starttime."&returndate=2014-10-08";
		$strBack1 =file_get_contents($url);
		$data = json_decode($strBack1,true);
		$this->assign('data',$data);
		layout(false);
		$this->display("showhelpbooking");
	}
	
	/*
	 * 将自助预定的信息保存到数据库
	 */
	public function addHelpbooking(){
		$openid=$_POST["openid"];//用户openid
		$departure_city=$_POST["departure_city"];//出发城市
		$arrived_city=$_POST["arrived_city"];//到达城市
		$thak_time=$_POST["thak_time"];//起飞时段
		$starting_date=$_POST["starting_date"];//起飞日期
		$plan_request->add($requestdata);
		$this->display("success");
		var_dump("$openid");	
	}
	/*
	 * 查看订单信息
	 */
	public function selectOrder(){


		//判断用户是否绑定，或者登录；
		$public =new WPublicController();
		$openid = $public->initOpenid();
		
		//加载布局文件
		layout(false);
		$dictionary=M('dictionary');
		$id = LI('userId');
		$order = D('Home/OrderWx', 'Logic');
		$Page = 0;
		$status= I("get.Ostatus",1);
		$result = $order->queryOrder($id,$Page,$status);
		$count=count($result);
		$Page  = new \Think\Page($count,10);
		$show  = $Page->show();// 分页显示输出
		$result = $order->queryOrder($id,$Page,$status);

		//查询出订单详情表中的状态 与字典表中的状态值进行对应

		foreach($result as $key=>$val){
			$flight_status=$val['flight'][0]['status'];
			$hotel_status=$val['hotel'][0]['status'];
			$train_status=$val['train'][0]['status'];
			$other_status=$val['other'][0]['status'];

			//查询出是否有飞机订单，根据飞机订单状态查询好出订单状态对应的文字显示
			if($val['flight']){
				$map['d_group']="order_status";
				$map['d_key']=$flight_status;
				$solution_flight=$dictionary->where($map)->find();


			}
			//查询是否有酒店订单，根据酒店订单状态查询好出订单状态对应的文字显示
			if($val['flight']){
				$map['d_group']="order_status";
				$map['d_key']=$hotel_status;
				$solution_hotel=$dictionary->where($map)->find();


			}
			//查询是否有火车订单，根据火车订单状态查询好出订单状态对应的文字显示
			if($val['flight']){
				$map['d_group']="order_status";
				$map['d_key']=$train_status;
				$solution_train=$dictionary->where($map)->find();


			}
			//查询出是否有其他的订单，根据其他订单状态查询好出订单状态对应的文字显示
			if($val['other']){
				$map['d_group']="order_status";
				$map['d_key']=$other_status;
				$solution_other=$dictionary->where($map)->find();


			}

		}

		
		$this->assign('Page',$show);// 赋值分页输出
		$this->assign('order',$result);//将订单发送到页面进行遍历
		$this->assign('solution_flight',$solution_flight);//将飞机订单状态对应的字典表中的内容发送到页面上
		$this->assign('solution_hotel',$solution_hotel);//将酒店订单状态对应的字典表中的内容发送到页面上
		$this->assign('solution_train',$solution_train);//将火车订单状态对应的字典表中的内容发送到页面上
		$this->assign('solution_other',$solution_other);//将其他订单状态对应的字典表中的内容发送到页面上
		$this->assign('start_date',$_POST['start_date']);
		$this->assign('end_date',$_POST['end_date']);

		$this->assign('keywords',$_POST['keywords']);




		$this->theme('default')->display("showorderwx");
	}
	/*
	 * 点击菜单
	 * 账号绑定
	 * 获取openid
	 */
	public function receiveAccountBound(){
		//禁止加载布局文件
		layout(false);

		$public =new WPublicController();
		$openid = $public->initOpenid(true);
		$this->assign("openid", $openid);//注册id

		$u_id = li("userId");

		//david law 2015-2-28
		if(is_numeric($u_id) && $u_id > 0) {
			$employee = M("employee");
			$data['employee'] = $employee->where(array("u_id"=>$u_id))->find();
			$company = M("company");
			$data['company'] = $company->where(array("id"=>$data['employee']['co_id']))->find();

			$this->assign("data", $data);
			$this->theme('default')->display("index");
		}else{
			$this->theme('default')->display("accountbound");
		}
		

	}



	/*
	 * 账号绑定
	 */
	public function addAccountBound(){
		$user = M('user');
		$cond['username'] = I('post.username');
		$cond['password'] = md5(I('post.password'));
		$data['wx_openid'] = I('post.openid');

		$result = $user->where($cond)->save($data);
		if($result){
			echo 1;
		}else{
			echo 2;
		}
	}

	/*
	 * 取消账号绑定
	 */
	public function delAccountBound(){
		$config = C('THINK_WECHAT');

		$user = M('user');
		$cond['id'] = LI('userId');
		$data['wx_openid'] = '';
		$user->where($cond)->save($data);
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$config['APP_ID']}&redirect_uri={$config['ACCOUNT_BINDING_URL']}";
		header("Location: {$url}");
	}

	//底部菜单
	public function footerMenu(){
		//加载布局文件
		layout("layout");

		$config = C('THINK_WECHAT');
		$footermenu = json_decode($config['JSON_MENU'],true);

		$this->assign("footermenu", $footermenu);
		$this->theme('default')->display("footer");

	}

}
?>