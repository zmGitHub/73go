<?php
//不需要授权就可以访问的页面，所有的M/C/A应在下面配置。
return array (
    array('m'=>'Home','c'=>'Resource','a'=>'*'),
    array('m'=>'Home','c'=>'Index','a'=>'index'),
    array('m'=>'Home','c'=>'Index','a'=>'know_more'),
    array('m'=>'Home','c'=>'Index','a'=>'login'),
    array('m'=>'Home','c'=>'Index','a'=>'register_user'),
    array('m'=>'Home','c'=>'Index','a'=>'config_coinfo_account'),
    array('m'=>'Home','c'=>'User','a'=>'check_company'),
    array('m'=>'Home','c'=>'User','a'=>'add_company'),
    array('m'=>'Home','c'=>'Index','a'=>'register_co'),
 	array('m'=>'Home','c'=>'Index','a'=>'Register_agreement'),
    array('m'=>'Home','c'=>'Index','a'=>'forget_password_email'),
    array('m'=>'Home','c'=>'Index','a'=>'forget_password_phone'),

    array('m'=>'Home','c'=>'User','a'=>'check_user'),
    array('m'=>'Home','c'=>'User','a'=>'check_login'),
    array('m'=>'Home','c'=>'User','a'=>'add_user'),
    array('m'=>'Home','c'=>'Index','a'=>'about_us'),
    array('m'=>'Agent','c'=>'Index','a'=>'Register_tmc_agreement'),
    array('m'=>'Agent','c'=>'Index','a'=>'login_tmc'),    
    array('m'=>'Agent','c'=>'Index','a'=>'register_tmc'),
    array('m'=>'Agent','c'=>'Index','a'=>'about_us'),
    array('m'=>'Agent','c'=>'Index','a'=>'login_tmc'),
    array('m'=>'Agent','c'=>'TMCUser','a'=>'check_login'),
	array('m'=>'Agent','c'=>'Config','a'=>'showconfig_tmcinfo_basicinfo'),	
    array('m'=>'Agent','c'=>'TMCUser','a'=>'add_tmc'),
    array('m'=>'Admin','c'=>'Index','a'=>'login'),
    array('m'=>'Admin','c'=>'Index','a'=>'index'),
    array('m'=>'Admin','c'=>'InterMng','a'=>'showUtilsPage'),
    
    //tmc旗舰店请求入口
    array('m'=>'Home','c'=>'*','a'=>'*'),

    //微信请求入口
    array('m'=>'Mfront','c'=>'*','a'=>'*'),
    array('m'=>'Test','c'=>'*','a'=>'*')


);