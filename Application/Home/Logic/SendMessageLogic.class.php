<?php
namespace Home\Logic;
use Think\Model;
use UMessage\Logic\UnifyMessage;
use UMessage\Logic\UnifyMessageSender;
/**
 * 制作发送短信和邮箱的模板文件
 * 创建者：郭攀
 * 创建时间：2014-03-05
 *
 */
	
	//定义SendMessageLogic继承底层的model类
	class SendMessagelogic extends Model{
		
		//定义SendDetail方法
		public function SendDetails($case,$data){
			
			//判断预定是通过主网站预定，还是TMC旗舰店网站判断
			//如果是主网站的时候TMC公司显示轻松行科技，如果是TMC旗舰店网站的时候显示TMC旗舰店的名字
			if(getHostedTmcInfo('name')){
				$show_name=	getHostedTmcInfo('name');
			}else{
				$show_name="轻松行科技";
			}
			//获取发送的时间；
			$data['time'] = date ( 'Y-m-d H:i:s', time () );
			
			switch ($case)
			{					
				// 自助预定生成 订单后进行发送 短信和邮箱的发送
				// 需要传的参数有： 
				// 提交人的邮箱($data['emp_email'])，提交人的电话($data['emp_phone']), 提交人的 $data['wx_openid'] ;
				// 1->提交人的姓名($data['emp_name']),2->TMC的公司($data['tmc_name']),
				// 3->航班出发的时间($data['begin_time'])，4->航班的内容($data['flight_content']);
				case  "BookSelf":

				$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
				$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['emp_phone'], 2 =>$data['emp_email']) : array (1 => $data['emp_phone'], 2 =>$data['emp_email'], 3 => $data['wx_openid']);
				//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
				//$targets = array (	1 => $data['emp_phone'],2 =>$data['emp_email']);
				$title =  '自助预定--订单产生后通知提交人';
				$text =  $data['emp_name'].':您已购买'.$show_name.$data['begin_time']."制作的航班号为".$data['flight_content']."航班。祝您旅途愉快 ！(".$show_name.".)." ;
				$html =  $data['emp_name'].'：<br />.'.
									'您已购买'.$show_name.$data['begin_time']."制作的".$data['flight_content']."航班。祝您旅途愉快 ！(".$show_name.".).'<br/>'.
									".$data ['time']."<br><此为系统发送邮件，请勿回复>"; 
				
				break;

				// 轻松预定生成订单后进行短信和邮箱的发送;
				// 需要传的参数有：
				// 提交人的邮箱 （$data['emp_phone']）, 提交人的电话（$data['emp_email']）,提交人的 $data['wx_openid'] ;
				// 1->提交人的姓名($data['emp_name']),2->TMC的公司($data['tmc_name']),
				// 3->航班出发的时间($data['begin_time'])，4->航班的内容($data['flight_content']);
				case  "QsxBook":
				$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
				$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['emp_phone'], 2 =>$data['emp_email']) : array (1 => $data['emp_phone'], 2 =>$data['emp_email'], 3 => $data['wx_openid']);
				//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
				//$targets = array (	1 => $data['emp_phone'],2 =>$data['emp_email']);
				$title =  '轻松预定--订单产生后通知提交人';
				$text =  $data['emp_name'].':您已购买'.$show_name.$data['begin_time']."制作的,订单号为".$data['flight_content']."订单。祝您旅途愉快 ！(".$show_name.".)." ;
				$html =  $data['emp_name'].'：<br />.'.
									'您已购买'.$show_name.$data['begin_time']."制作的".$data['flight_content']."航班。祝您旅途愉快 ！(".$show_name.".).
									".$data ['time']."<br><此为系统发送邮件，请勿回复>"; 
				
				break;	
				
				// 轻松预定生成订单后，通知TMC 公司
				// 需要传的参数：
				// OP的邮箱（$data['tmc_email']） , OP的邮箱电话（$data['tmc_phone']）,所有OP的$data['wx_openid'] ;
				// 1->OP的姓名($data['op_name'])，2->预定人公司名称($data['co_name']),3->预定人的姓名($data['emp_name']),
				// 4->出发时间($data['begin_time']),5->航班信息($data['flight_content']);
				

				case  "TmcOrder":
				$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
				$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['tmc_phone'], 2 =>$data['tmc_email']) : array (1 => $data['tmc_phone'], 2 =>$data['tmc_email'], 3 => $data['wx_openid']);
				//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
				//$targets = array (	1 => $data['tmc_phone'],2 =>$data['tmc_email']);
				$title =  '订单产生后通知TMC';
				$text =  $data['op_name'].':您帮'.$data['co_name'].$data['emp_name'].'在'.$data['begin_time']."时，已成功预定 。订单号为（".$data['flight_content']."）";
				$html =  $data['emp_name'].'：<br />.'.
									'您帮'.$data['co_name'].$data['emp_name'].$data['begin_time'].$data['flight_content']."航班已经预定成功！
									".$data ['time']."<br><此为系统发送邮件，请勿回复>"; 
				

				break;

				//TMC 公司认证通过的消息通知
				//需要传的参数
				//TMC 邮箱$data['tmc_email'], TMC 电话 $data['tmc_phone']，TMC公司的 $data['wx_openid'];
				//TMC 公司的名字 $data['tmc_name'];
				case  "TmcCertOk":
					$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
					$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['tmc_phone'], 2 =>$data['tmc_email']) : array (1 => $data['tmc_phone'], 2 =>$data['tmc_email'], 3 => $data['wx_openid']);
					//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
					//$targets = array (	1 => $data['tmc_phone'],2 =>$data['tmc_email']);
					$title =  '认证成功通知TMC';
					$text =  $data['tmc_name'].':您提交的认证信息已经通过了审核，您可以通过平台对外提供服务了。';
					$html =  $data['tmc_name'].'：<br />.'.
						'您提交的认证信息已经通过了审核，您可以通过平台对外提供服务了
									'.$data ['time']."<br><此为系统发送邮件，请勿回复>";

					break;

				//TMC 公司认证未通过的消息通知
				//需要传的参数
				//TMC 邮箱$data['tmc_email'], TMC 电话 $data['tmc_phone']，TMC公司的 $data['wx_openid'];
				//TMC 公司的名字 $data['tmc_name'];
				case  "TmcCertNo":
					$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
					$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['tmc_phone'], 2 =>$data['tmc_email']) : array (1 => $data['tmc_phone'], 2 =>$data['tmc_email'], 3 => $data['wx_openid']);
					//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
					//$targets = array (	1 => $data['tmc_phone'],2 =>$data['tmc_email']);
					$title =  '认证失败通知TMC';
					$text =  $data['tmc_name'].':您提交的认证信息未通过审核，请联系客服人员，确认您的资料是否正确。';
					$html =  $data['tmc_name'].'：<br />.'.
						'您提交的认证信息未通过审核，请联系客服人员，确认您的资料是否正确。
									'.$data ['time']."<br><此为系统发送邮件，请勿回复>";

					break;



				case "EmailGetNewPassword":
					$types = array(2);
					$targets = array (	2 => $data['user_email']);
					$title = '账户使用邮箱获得新的密码';
					$html =  '亲爱的'.$data['username'].'：<br />.'. '您在www.73go.cn 的新密码为:'.$data ['newpassword'].'，请及时修改密码！'.$data ['time']."<br><此为系统发送邮件，请勿回复>";
				break;

				case "PhoneGetNewPassword":
					$types=array(1);
					$targets = array (	1 => $data['user_phone']);
					$title = '账户使用短信获得新的密码';
					$text = '亲爱的'.$data['username'].':您在www.73go.cn的新密码为 '.$data['newpassword'].'，请及时修改密码！';
				break;

				//自助预定，当预订人付款以后，订单信息传递给对应TMC的OP
				//需要传的参数
				//OP邮箱 $data['op_email'], OP短信 $data['op_phone'],OP 微信 $data['wx_openid'];
				//OP姓名 $data['op_name']   订票人姓名 $data['order_name'];
				//出发时间 $data['begin_time'], 出发地点 $data['start_city']   到达地点 $data['arrive_city'];
				//所定仓位$data['order_class']
				case  "OrderOP":
					$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
					$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['op_phone'], 2 =>$data['op_email']) : array (1 => $data['op_phone'], 2 =>$data['op_email'], 3 => $data['wx_openid']);
					//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
					//$targets = array (	1 => $data['tmc_phone'],2 =>$data['tmc_email']);
					$title =  '自助预定--订单信息传递给对应TMC的OP';
					$text =  $data['op_name'].':'.$data['order_name'].'发出时间为'.$data['begin_time'].'从'.$data['start_city'].'-'.$data['arrive_city'].' '.  仓位是.$data['order_class'].'需求请尽快出票。（请在待出票菜单中查看订单详情）';
					$html=$data['op_name'].':<br />'.
								$data['order_name'].'发出时间为'.$data['begin_time'].'从'.$data['start_city'].'-'.$data['arrive_city'].' '.  仓位是.$data['order_class'].'需求请尽快出票。（请在待出票菜单中查看订单详情）'
								.$data ['time']."<br><此为系统发送邮件，请勿回复>";
				break;

			//点击已采购（出票）时发送已出票消息给预定人
			//需要传的参数
			//预定者的邮箱$data['emp_email'],预订者的电话$data['emp_phone'],预定者的$data['wx_openid'];
			// 预订者的姓名 $data['emp_name'],订单号 $data['order_num'],

				case "TicketInfo";
					$types =  strlen($data['wx_openid']) != 28 ? array(1,2) : array(1,2,3); //发送类型发送类型 1=>需要发送的电话    2=>需要发送的邮箱   3=>微信发送；
					$targets = strlen($data['wx_openid']) != 28 ? array (1 => $data['emp_phone'], 2 =>$data['emp_email']) : array (1 => $data['emp_phone'], 2 =>$data['emp_email'], 3 => $data['wx_openid']);
					//$types = array(1,2);//发送类型 1=>需要发送的电话    2=>需要发送的邮箱
					//$targets = array (	1 => $data['tmc_phone'],2 =>$data['tmc_email']);
					$title =  '自助预定已出票时--通知预订者';
					$text =  $data['emp_name'].':您的订单.'.'【'.$data['order_num'].'.】已经预定成功。';
					$html =  $data['emp_name'].'：<br />.'.
						':您的订单.'.'【'.$data['order_num'].'.】已经预定成功。
									'.$data ['time']."<br><此为系统发送邮件，请勿回复>";


				break;
			}
		
		$sender = new UnifyMessageSender ();
		$um = UnifyMessage::NewUnionMessage ( $types,$targets,$title,$text, $html );
		$sender->sendUMessage ($um);
		
		
		}
			


	}