<?php
/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2015/4/3
 * Time: 10:40
 */
namespace Home\Controller;
use Home\Logic\UserLogic;
use System\LoginInfo;
use Think\Controller;
class LoginController extends Controller{


    //发送验证码
    public function verify_code()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $phone= $_POST['phone'];

        $newpassword = $this->generate_password(6);//生成六位随机验证码
        //$map2['password'] = md5($newpassword);
        //if ($type == 1) {//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
           // $emp = M('employee');
           // $emp_phone = $emp->where($map1)->getField('phone');
           // if ($emp_phone == $phone) {
           //     $user->where("id=" . $id)->save($map2);
                //发短信
                $send = D("Home/SendMessage", "Logic");
                $case = "PhoneGetVerifyCode";
                $datt['user_phone'] = $phone;
                $datt['newpassword'] = $newpassword;
                $send->SendDetails($case, $datt);
                $this->ajaxReturn(1,'json');
    }
    //邮件收到验证码
    public function verify_code_email()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $email = $user['email'];
        $newpassword = $this->generate_password(6);//生成六位随机验证码
        //$map2['password'] = md5($newpassword);
        //if ($type == 1) {//判断帐号的类型 1、普通用户  2、企业 3、tmc 4、op
        // $emp = M('employee');
        // $emp_phone = $emp->where($map1)->getField('phone');
        // if ($emp_phone == $phone) {
        //     $user->where("id=" . $id)->save($map2);
        //发短信
        $send = D("Home/SendMessage", "Logic");
        $case = "EmailGetNewPassword";
        $datt['user_email'] = $email;
        $datt['newpassword'] = $newpassword;
        $send->SendDetails($case, $datt);
        $this->ajaxReturn(1,'json');
    }

    public function add_user(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $map['account'] = $_POST['telNumber'];//必填项
        $map['password'] = md5($_POST['loginPassword']);//必填项
        $map['user_type']=1;        //'user_type' should be 0  reviewer:Yu Zhuoran
        //$map['status']=0;
        $m_user = M('user');
        $result= $m_user->data($map)->add();
        if($result){
            $this->ajaxreturn(1);
        }else{
            $this->ajaxreturn(0);
        }
    }
   //查询到账号，发送密码，返回1
    //查询不到账号，返回0；
    public function forget_password()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        //$account = $_POST['account'];
        $phone = $_POST['phone'];
        $newpassword = $this->generate_password(6);//生成六位随机密码
        //发短信
        $users = M('user');
        $map['password'] = md5($newpassword);
        $user = $users->where('account='.$phone)->select();
        $result = $users->where('account='.$phone)->save($map);
        if($user && ($user[0]['account'] == $phone)) {
            $send = D("Home/SendMessage", "Logic");
            $case = "PhoneGetNewPassword";
            $datt['user_phone'] = $phone;
            $datt['newpassword'] = $newpassword;
            $send->SendDetails($case, $datt);
            $this->ajaxReturn(1, 'json');
        }else {
            $this->ajaxReturn(0, 'json');
        }
    }
    /*生成随机密码类*/
    function generate_password($length = 6)
    {

        // 密码字符集，可任意添加你需要的字符
        $chars = '0123456789';

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $password;
    }
    public function doLogin(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $account = $_POST['userName'];
        $password =  md5($_POST['password']);
        $logic = new UserLogic();
        $user = $logic->findByNameAndPsw($account, $password);
        $type=1;

        if(!$user){
            $info = '帐号 ' . $account . ' 登录73go前台网页失败';
            LOGS($type, $info, $account);
            $this->ajaxReturn(0);
        }else {
            $info='帐号'.$account.'登录73go前台页面成功';
            session('user_id', $user['id']);
            $logic->userLogins('', $user);
            LOGS($type,$info);
            $this->ajaxReturn((int)$user['id']);
        }
    }



    //check if the user name has been registered   reviewer:Yu Zhuoran
    public function check_user(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $account = $user['account'];
        $user = M('user');
        $map['account'] = $account;
        $result= $user->where($map)->select();
        if($result){
            //echo 1;
            $this->ajaxReturn(1);
        }else{
            //echo 0;
            $this->ajaxReturn(0);
        }

    }

    //用户修改密码的操作

    public function mod_password()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $account = LI('account');
        $user = M('user');
        $map['password'] = md5($_POST['newpassword']);
        $res = $user->where("account=" . $account)->save($map);
        if ($res) {
            $this->ajaxreturn(1);//, U('Index/config_myinfo_acount'));
        } else {
            $this->ajaxreturn(0);//, U('Index/config_myinfo_acount'));
        }
    }

    //验证原有的密码

    public function check_password()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $map['account'] = LI('account');
        $map['password'] = md5($_POST['password']);
        $user = M('user');
        $result = $user->where($map)->select();
        //print_r($result);
        if ($result) {
            $this->ajaxreturn(1);
        } else {
            $this->ajaxreturn(0);
        }
    }
    //更新用户信息
    public function update_user(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $account = LI('account');//必填项
        $map['name'] = $user['name'];//必填项
        $map['sex'] = $user['sex'];
        $map['phone'] =$user['phone'];//必填项
        $map['card_type'] =$user['card_type'];//必填项
        $map['card_id'] =$user['card_id'];//必填项
        $map['email'] =$user['email'];
        $map['qq'] =$user['qq'];
        $map['user_type']=1;        //'user_type' should be 0  reviewer:Yu Zhuoran
        //$map['status']=0;
        $m_user = M('user');
        $result= $m_user->where('account='.$account)->save($map);
        if($result){
            $this->ajaxreturn(1);
        }else{
            $this->ajaxreturn(0);
        }
    }
}
