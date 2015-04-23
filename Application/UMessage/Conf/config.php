<?php
return array (
		
		// '配置项'=>'配置值'
		/**
		 * *** 73GO 消息类型 ****
		 */
		"73GO_UM_SMS" => "1",
		"73GO_UM_EMAIL" => "2",
		"73GO_UM_WECHAT" => "3",
		/**
		 * *** 73GO 消息状态 ****
		 */
		"73GO_UM_STATUS_NEW" => '0',
		"73GO_UM_STATUS_SENDING" => '2',
		"73GO_UM_STATUS_SENT" => '9',
		/**
		 * *********************
		 */
		"73GO_MESSAGE_CHECK_INTERVAL" => '5', // <--消息发送检查间隔，单位为秒
		
		"THINK_EMAIL" => array (
				'SMTP_HOST' => 'smtp.exmail.qq.com', // SMTP服务器
				'SMTP_PORT' => '465', // SMTP服务器端口
				'SMTP_USER' => 'website@73go.cn', // SMTP服务器用户名
				'SMTP_PASS' => 'web73go', // SMTP服务器密码
				'FROM_EMAIL' => 'website@73go.cn', // 发件人EMAIL
				'FROM_NAME' => '轻松行科技', // 发件人名称
				'REPLY_EMAIL' => '', // 回复EMAIL（留空则为发件人EMAIL）
				'REPLY_NAME' => '' // 回复名称（留空则为发件人名称）
		) 

);