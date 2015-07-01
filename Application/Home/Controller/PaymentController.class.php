<?php
namespace Home\Controller;

use Think\Controller;
use Org\Alipay;
require 'C:/wamp/www/73go/ThinkPHP/Library/Org/ping/init.php';
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
	public function pingpay(){

$input_data = json_decode(file_get_contents('php://input'), true);
		$input_data['channel'] = 'alipay_wap';
		$input_data['amount'] = '1';
if (empty($input_data['channel']) || empty($input_data['amount'])) {
	exit();
}
$channel = strtolower($input_data['channel']);
$amount = $input_data['amount'];
$orderNo = substr(md5(time()), 0, 12);

//$extra 在使用某些渠道的时候，需要填入相应的参数，其它渠道则是 array() .具体见以下代码或者官网中的文档。其他渠道时可以传空值也可以不传。
$extra = array();
switch ($channel) {
	//这里值列举了其中部分渠道的，具体的extra所需参数请参见官网中的 API 文档
	case 'alipay_wap':
		$extra = array(
			'success_url' => 'http://www.yourdomain.com/success',
			'cancel_url' => 'http://www.yourdomain.com/cancel'
		);
		break;
	case 'upmp_wap':
		$extra = array(
			'result_url' => 'http://www.yourdomain.com/result?code='
		);
		break;
	case 'bfb_wap':
		$extra = array(
			'result_url' => 'http://www.yourdomain.com/result?code='
		);
		break;
	case 'upacp_wap':
		$extra = array(
			'result_url' => 'http://www.yourdomain.com/result?code='
		);
		break;
	case 'wx_pub':
		$extra = array(
			'open_id' => 'Openid'
		);
		break;
	case 'wx_pub_qr':
		$extra = array(
			'product_id' => 'Productid'
		);
		break;
	case 'jdpay_wap':
		$extra = array(
			'success_url'=>'http://www.yourdomain.com',
			'fail_url'=>'http://www.yourdomain.com'
		);
		break;

}

\Pingpp\Pingpp::setApiKey('sk_live_CejnL8nHm5i5fvLa5Cu5SiLC');
try {
	$ch = \Pingpp\Charge::create(
		array(
			"subject"   => "Your Subject",
			"body"      => "Your Body",
			"amount"    => '1',
			"order_no"  => '123456',
			"currency"  => "cny",
			"extra"     => $extra,
			"channel"   => $channel,
			"client_ip" => '120.24.171.184',//$_SERVER["REMOTE_ADDR"],
			"app"       => array("id" => "app_SuLWfHyXH0CCX1uL")
		)
	);
	echo $ch;
} catch (\Pingpp\Error\Base $e) {
	header('Status: ' . $e->getHttpStatus());
	echo($e->getHttpBody());
}

	}
	public function payment(){
		$account = I('session.account');
		//find order infomation
		$order_num =  $_POST['order_num'];
		$map['account'] = $account;
		$map['order_num'] = $order_num;
		//查找订单信息
		$m_order = M('orders');
		$orderinfo= $m_order->where($map)->select();
		//filght ticket
		//查找机票信息
		$flight= M('flight');
		$flightinfo= $flight->where($map)->select();
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
		$order = array($orderinfo,$flightinfo) ;
		$this->ajaxreturn($order,'JSON');
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
		$notify_url = "http://www.dddafeiji.com/home/payment/notify";
		//需http://格式的完整路径，不能加?id=123这类自定义参数
		//页面跳转同步通知页面路径
		$return_url = "http://www.dddafeiji.com/home/payment/alireturn";
		//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
		//卖家支付宝帐户
		$seller_email = $_POST['alipay_id'];
		//必填
		//商户订单号
		$out_trade_no = $_POST['order_num'];
		//商户网站订单系统中唯一订单号，必填
		//订单名称
		$subject = $_POST['desc'];
		//必填
		//付款金额
		//$total_fee1 = $_POST['totalPay'];
        $total_fee = $_POST['total_price'];
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

    public function alireturn()
	{
		$tradeStatus = 1;
		$out_trade_no = $_GET['out_trade_no']; //订单号
		$order = M('orders');
		$tmc = D('tmc_config_tbl');

		$orderinfo = $order->where("order_num='%s'", $out_trade_no)->select();
		$tmc_count_info = $tmc->where('tmc_id=' . $orderinfo[0]['tmc_id'])->select();

		//合作者ID
		$alipay_config['partner'] = $tmc_count_info[0]['alipay_partner_id'];
		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['key'] = $tmc_count_info[0]['rsa_key'];

		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


		//签名方式 不需修改
		$alipay_config['sign_type'] = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset'] = strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert'] = getcwd() . '\\cacert.pem';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport'] = 'http';
		//
		$alipayNotify = new \Org\Alipay\AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		if ($verify_result) {//验证成功
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//支付宝交易号

			//$trade_no = $_GET['trade_no'];

			if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') { //交易成功
				$orderData['status'] = 1;     //已支付
				$flightData['status'] = 1;     //已支付
				$m_order = M('orders');
				$m_flight = M('flight');
				$o_result = $m_order->where("order_num='%s'", $out_trade_no)->save($orderData);
				$f_result = $m_flight->where("order_num='%s'", $out_trade_no)->save($flightData);
				$this->ajaxreturn(1);


			} else {
				//验证失败
				//如要调试，请看alipay_notify.php页面的verifyReturn函数
				//echo "验证失败"."verify_result:".$verify_result;
				$this->ajaxreturn(0);
			}
		}
	}
}