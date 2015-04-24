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
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $phone = $user['phone'];
        $newpassword = $this->generate_password(6);//生成六位随机密码
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
   //查询到账号，发送密码，返回1
    //查询不到账号，返回0；
    public function forget_password()
    {
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $phone = $user['phone'];
        $newpassword = $this->generate_password(6);//生成六位随机密码
        //发短信
        $users = M('user');
        $map['account'] = $user['account'];
        $user = $users->where($map)->select();
        if($user) {
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
    public function check_login(){
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $account = $user['account'];
        $password =  md5($user['password']);
        $logic = new UserLogic();
        $user = $logic->findByNameAndPsw($account, $password);
        $type=1;

        if(!$user){
            $info = '帐号 ' . $account . ' 登录73go前台网页失败';
            LOGS($type, $info, $account);
            $this->ajaxReturn(0);
        }else {
            $info='帐号'.$account.'登录73go前台页面成功';
            session('user_type', $user['user_type']);
            $logic->userLogins('', $user);
            LOGS($type,$info);
            $this->ajaxReturn((int)$user['user_type']);
        }
    }



    //check if the user name has been registered   reviewer:Yu Zhuoran
    public function check_user(){
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

    public function add_user(){
        $data= $_POST['data'];
        $user = json_decode($data,true);
        $map['account'] = $user['account'];//必填项
        $map['password'] = md5($user['password']);//必填项
        $map['name'] = $user['name'];//必填项
        $map['sex'] = $user['sex'];
        $map['phone'] = $user['phone'];//必填项
        $map['card_type'] = $user['card_type'];//必填项
        $map['card_id'] = $user['card_id'];//必填项
        $map['email'] = $user['email'];
        $map['qq'] = $user['qq'];
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

    /*修改个人用户详细信息*/
    public function mod_user_detail(){

        $id = LI('account');
        $m_user = M('user');
        $data= $_POST['data'];
        $user = json_decode($data,true);
        //print_r($map);exit;
        $result = $m_user->where("account=".$id)->save($user);
        $this->ajaxReturn($result, "JSON");
    }

    /*修改用户名*/
   /* public function mod_username(){

        $id = LI('account');
        $m_user = M('user');

        //查找重复名
        $map['account'] = $_POST['account'];
        $map['id'] = array('neq',$id);
        $result = $m_user->where($map)->find();
        if($result){
            $this->error('修改失败,该用户名已存在！',U('Index/config_myinfo_acount'));
        }
        $map['username'] = $_POST['username'];

        $res = $m_user->where('id = '.$id)->save($map);
        LI('userName', $map['username']);
        if($res){
            $this->redirect('Index/config_myinfo_acount');
            //$this->success('修改成功');//, U('Index/config_myinfo_acount'));
        }else{
            $this->redirect('Index/config_myinfo_acount');
            //$this->error('修改失败');//, U('Index/config_myinfo_acount'));
        }


    }



   /* public function checkBaseInfo(){
        $employee = new \Home\Model\EmployeeMessageModel();
        $employeeMessage = $employee->employeeMessage();
        $this->ajaxReturn($employeeMessage, "JSON");
    }*/
}