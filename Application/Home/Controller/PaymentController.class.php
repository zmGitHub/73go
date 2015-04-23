<?php
namespace Home\Controller;

use Think\Controller;
use Org\Alipay;
/**
 * 支付宝处理
 * 创建者：董发勇
 * 创建时间：2014-12-15下午04:35:46
 *
 *
 */
class PaymentController extends Controller {

	/**
	 * 支付宝响应
	 * 创建者：董发勇
	 * 创建时间：2014-12-15下午04:36:22
	 */
	/**
	 * 修改仓位信息
	 * 修改者：王月
	 * 修改时间：2015-3-25
	 */
	public function payment(){
		layout("home");
		$u_id = LI('userId');
		//find order infomation
		$orderid= $_GET['id'];
		$order = D('order');
		$orderinfo= $order->where("order_num='%s'",$orderid)->select();
		//filght ticket
		$flight= D('flight_ticket_info');
		$flightinfo= $flight->where('o_id='.$orderinfo[0]['id'])->select();
		$class = $flightinfo[0]['class'];
		if($class==' F'||$class == ' A') {
			$flightinfo[0]['class'] = '头等舱';
		} else if($class==' C'||$class==' D'||$class==' J') {
			$flightinfo[0]['class'] = '商务舱';
		} else if($class==' Y'||$class==' B'||$class==' H'||$class==' K'||$class==' L'||$class==' M'||$class==' N'
			||$class==' Q'||$class==' T'||$class==' X'||$class==' U'||$class==' E'||$class==' R'||$class==' O'
			||$class==' Z'||$class==' V'||$class==' G'||$class==' S') {
			$flightinfo[0]['class'] = '经济舱';
		}
		// traveler infomation
		$orderuser= D('order_user');
        $travelerinfo = $orderuser->where('o_id='.$orderinfo[0]['id'])->select();
		//tmc infomation
		$tmc = D('tmc_config_tbl');
		$tmc_count_info = $tmc->where('tmc_id='.$orderinfo[0]['tmc_id'])->select();

		$this->assign('orderinfo',$orderinfo[0]);
		$this->assign('flightinfo',$flightinfo[0]);
		$this->assign('travelerinfo',$travelerinfo);
		$this->assign('tmccount',$tmc_count_info[0]);
        $this->assign('tradeStatus',3);
		$this->theme("default")->display("pay");
	}

	public function alipay(){
		//合作者ID
		$alipay_config['partner']		= $_POST['tmc_partner_id'];
		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['key']			= $_POST['rsa_key'];

		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().'\\cacert.pem';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
		//支付类型
		$payment_type = "1";
		//必填，不能修改
		//服务器异步通知页面路径
		$notify_url = "http://www.73go.cn/home/payment/notify";
		//需http://格式的完整路径，不能加?id=123这类自定义参数
		//页面跳转同步通知页面路径
		$return_url = "http://www.73go.cn/home/payment/alireturn";
		//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
		//卖家支付宝帐户
		$seller_email = $_POST['alipay_id'];
		//必填
		//商户订单号
		$out_trade_no = $_POST['orderNumber'];
		//商户网站订单系统中唯一订单号，必填
		//订单名称
		$subject = $_POST['desc'];
		//必填
		//付款金额
		//$total_fee1 = $_POST['totalPay'];
        $total_fee = $_POST['totalPay'];
		//必填
		//订单描述
		$body = "";
		//商品展示地址
		$show_url ="";
		//需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
		//防钓鱼时间戳
		$anti_phishing_key = "";
		//若要使用请调用类文件submit中的query_timestamp函数
		//客户端的IP地址
		$exter_invoke_ip = "";
		//非局域网的外网IP地址，如：221.0.0.1


		/*************************************************************/

		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service" => "create_direct_pay_by_user",
			"partner" => trim($alipay_config['partner']),
			"payment_type" => $payment_type,
			"notify_url" => $notify_url,
			"return_url" => $return_url,
			"seller_email" => $seller_email,
			"out_trade_no" => $out_trade_no,
			"subject" => $subject,
			"total_fee" => $total_fee,
			"body" => $body,
			"show_url" => $show_url,
			"anti_phishing_key" => $anti_phishing_key,
			"exter_invoke_ip" => $exter_invoke_ip,
			"_input_charset" => trim(strtolower($alipay_config['input_charset']))
		);

		//建立请求
		$alipaySubmit = new \Org\Alipay\AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
		echo $html_text;
	}

    public function notify(){
        echo "提示信息！";
    }

    public function alireturn(){
        $tradeStatus = 1;
        $out_trade_no = $_GET['out_trade_no']; //订单号
        $order = D('order');
        $tmc = D('tmc_config_tbl');

        $orderinfo= $order->where("order_num='%s'",$out_trade_no)->select();
        $tmc_count_info = $tmc->where('tmc_id='.$orderinfo[0]['tmc_id'])->select();

		//合作者ID
		$alipay_config['partner']		= $tmc_count_info[0]['alipay_partner_id'];
		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['key']			= $tmc_count_info[0]['rsa_key'];

		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().'\\cacert.pem';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
		//
		$alipayNotify = new \Org\Alipay\AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {//验证成功
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//支付宝交易号

			//$trade_no = $_GET['trade_no'];

			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') { //交易成功
                $orderData['status'] =20 ;     //已确认
                $flightData['status'] =2 ;     //待出票
                $order = M('order');
                $flight = M('flight_ticket_info');
                $orderinfo= $order->where("order_num='%s'",$out_trade_no)->select();
                $o_result = $order->where("order_num='%s'",$out_trade_no)->save($orderData);
                $f_result = $flight->where('o_id='.$orderinfo[0]['id'])->save($flightData);
                if($o_result && $f_result){
                    $tradeStatus = 2;
                }else{
                    $tradeStatus = 1;
                }
			}

			$flight_ticket_info=M('flight_ticket_info');
			$employee=M('employee');
			$user=M('user');
			$tmc_employee=M('tmc_employee');
			$flightInfo = $flight_ticket_info->where('o_id='.$orderinfo[0]['id'])->find();
			$employeeName = $employee->where('id='.$orderinfo[0]['u_id'])->getField('name');
			$tmcEmployee = $tmc_employee->where('id='.$orderinfo[0]['tmc_uid'])->find();
			$userWx = $user->where('id='.$tmcEmployee['u_id'])->getField('wx_openid');
			
			//触发消息发送
			$send_1=D("Home/SendMessage","Logic");
			$case_1="OrderOP";
			$ress['op_email']= $tmcEmployee['email'];
			$ress['op_phone']= $tmcEmployee['phone'];
			$ress['order_name']= $employeeName;
			$ress['begin_time']= $flightInfo['time_dep'];
			$ress['start_city']= $flightInfo['city_from'];
			$ress['arrive_city']= $flightInfo['city_to'];
			$ress['order_class']= $flightInfo['class'];
			$ress['wx_openid']= $userWx;//传用户的wx_openid;
			$send_1->SendDetails($case_1,$ress);
			
			//echo "验证成功<br />"."verify_result:".$verify_result;
		}else {
			//验证失败
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			//echo "验证失败"."verify_result:".$verify_result;
            $tradeStatus = 1;
		}
        $this->showOrder($out_trade_no, $tradeStatus); //返回订单列表
	}

    private function  showOrder($orderID,$tradeStatus){
        //加载布局文件
        layout("home");
        $order = D('order');
        $flight= D('flight_ticket_info');

        $orderInfo= $order->where("order_num='%s'",$orderID)->select();

        $flightInfo= $flight->where('o_id='.$orderInfo[0]['id'])->select();
        // traveler infomation
        $orderuser= D('order_user');
        $travelerinfo = $orderuser->where('o_id='.$orderInfo[0]['id'])->select();
        //tmc infomation
        $tmc = D('tmc_config_tbl');
        $tmc_count_info = $tmc->where('tmc_id='.$orderInfo[0]['tmc_id'])->select();

        $this->assign('orderinfo',$orderInfo[0]);
        $this->assign('flightinfo',$flightInfo[0]);
        $this->assign('travelerinfo',$travelerinfo);
        $this->assign('tmccount',$tmc_count_info[0]);
        $this->assign('tradeStatus',$tradeStatus);
        $this->theme("default")->display("pay");
    }
}