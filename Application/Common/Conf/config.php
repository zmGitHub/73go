<?php
return array(
	//'配置项'=>'配置值'
//	'URL_PATHINFO_DEPR'=>'/', //修改url的分隔符
//	//修改定界符 
//	'TMPL_L_DELIM'=>'{',//修改左定界符
//	'TMPL_R_DELIM'=>'}',//修改右定界符
//	'SHOW_PAGE_TRACE'=>false,//开启页面trace
	'URL_MODEL'=>2,
//	'DEFAULT_THEME'    =>    'default',//模板主题
//	 'SHOW_ERROR_MSG'  => true,    // 显示错误信息

	'DB_TYPE'=>'mysql',   //设置数据库类型
	'DB_HOST'=>'localhost',//设置主机
	'DB_NAME' => '73go', // 设置数据库名
		'DB_USER' => 'root', // 设置用户名
		'DB_PWD' => 'fb1e43d94a', // 设置密码
	'DB_PORT'=>'3306',   //设置端口号
	'DB_PREFIX'=>'73go_',  //设置表前缀
	'DB_CHARSET'=> 'utf8',//设置字符集
	
	//开启路由
 	'URL_ROUTER_ON' => true, 
	//开启模板布局
	'LAYOUT_ON'=>true,
	'LAYOUT_NAME'=>'home',	

	'Apply_number' => '100000000',
	'APP_GROUP_LIST' => 'Home,Admin,Agent','Mfront',//项目分组设定
    'DEFAULT_GROUP'  => 'Home', //默认分组
	
	"SYSUSER_PERSON" => "1",
	"SYSUSER_COMPANY" => "2",
	"SYSUSER_TMC" => "3",
	"SYSUSER_TMCEMP" => "4",
	"SYSUSER_ADMINISTRATOR" => "99",

	"DEFAULT_PASSWORD"=>"112233go",

	//TMC Hosting场景的Domain Name
	"TMC-HOSTING-SERVER" => "www.tmc-china.com",

		/***** 73GO 消息类型  *****/
	"73GO_UM_SMS" => "1",
	"73GO_UM_EMAIL" => "2",
	"73GO_UM_WECHAT" => "3",
	/***** 73GO 消息状态  *****/
	"73GO_UM_STATUS_NEW"     => '0',
	"73GO_UM_STATUS_SENDING" => '2',
	"73GO_UM_STATUS_SENT"    => '9',
	/************************/
	"THINK_EMAIL" => array (
		'SMTP_HOST' => 'smtp.exmail.qq.com', // SMTP服务器
		'SMTP_PORT' => '465', // SMTP服务器端口
		'SMTP_USER' => 'website@73go.cn', // SMTP服务器用户名
		'SMTP_PASS' => 'web73go', // SMTP服务器密码
		'FROM_EMAIL' => '691209942@qq.com', // 发件人EMAIL
		'FROM_NAME' => 'turbo', // 发件人名称
		'REPLY_EMAIL' => '', // 回复EMAIL（留空则为发件人EMAIL）
		'REPLY_NAME' => '' // 回复名称（留空则为发件人名称）
	) 
	
		
);



?>
