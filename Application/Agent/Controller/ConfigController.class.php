<?php
namespace Agent\Controller;

use System\LoginInfo;
use Think\Controller;

class ConfigController extends Controller
{

    //加载TMC登陆用户的信息
    public function config_tmcinfo_account()
    {
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("tmc");
        //获取存TMC的id的值
        $id = Li('userId');
        //$id=173;
        $Tmc = M("tmc");
        $result = $Tmc->where("u_id=" . $id)->find();
        //print_r($result);
        $this->assign("result", $result);
        $this->theme("agent")->display("config_tmcinfo_account");

    }

    //显示TMC企业的相关信息
    public function showconfig_tmcinfo_basicinfo()
    {
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("tmc");
        //获取TMC存放的id值
        $id = Li("userId");
        $Tmc = M("tmc");


        /*查询出dictionary 表中的数据进行 id_type*/
        $dictionary = M("");
        $datt11 = $dictionary->query("select * from 73go_dictionary where d_group='co_scale'");
        $result = $Tmc->where("u_id=" . $id)->find();
        //将字典表中的id_type 类型 传入页面
        $this->assign("data11", $datt11);
        $this->assign("result", $result);
        $this->theme("agent")->display("showconfig_tmcinfo_basicinfo");

    }

    //进行config_tmcinfo_basicinfo 的处理
    public function doconfig_tmcinfo_basicinfo()
    {
        //print_r($_POST);exit;
        //C('LAYOUT_ON',TRUE);
        //加载布局文件
        //layout("tmc");
        //获取TMC存放的id值
        $id = Li("userId");
        //$Tmc=M("Tmc");
        //$result=$Tmc->where("u_id=".$id)->find();
        //$this->assign("result",$result);

        $Tmc = M("Tmc");
        $map['u_id'] = $id;
        //将post传来的值进行更新
        $map = $_POST;

        $res = $Tmc->where("u_id=" . $id)->save($map);

        if ($res) {
            $this->redirect('Config/showconfig_tmcinfo_basicinfo');
            //$this->success("修改成功！",U('Config/showconfig_tmcinfo_basicinfo'));
        } else {
            $this->redirect('Config/showconfig_tmcinfo_basicinfo');
            //$this->error("修改失败！",U('Config/showconfig_tmcinfo_basicinfo'));

        }

    }

    //加载TMC认证的相关的信息

    public function config_tmcinfo_certificated()
    {
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("tmc");
        $id = LI("userId");    //获取当前用户的id
        $tmc_id = LI("tmcId"); //获取当前用户的tmcid;
        $Tmc = M("tmc");
        $tmc_config = M("tmc_config_tbl"); //实例化tmc 功能配置表

        $datt = $tmc_config->where("tmc_id=" . $tmc_id)->find();
        $data = $Tmc->where("u_id=" . $id)->find();

        $this->assign("result", $data);
        $this->assign("datt", $datt);

        $this->theme("agent")->display("config_tmcinfo_certificated");

    }

    //进行TMC认证信息的处理

    public function doconfig_tmcinfo_certificated()
    {
        //print_r($_POST);exit;
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("tmc");
        $Tmc = M("Tmc");
        $tmc_config = M("tmc_config_tbl");
        $tmc_id = Li("tmcId"); //获取当前用户的tmcid;
        $id = Li("userId");
        //var_dump($_POST);
        //print_r($_POST);exit;
        //$map=$_POST;
        $map['cert_val'] = 2;
        $map['license_no'] = $_POST['license_no'];
        $map['license_date'] = $_POST['license_date'];
        $map['license_period'] = $_POST['license_period'];
        $map['legal_name'] = $_POST['legal_name'];
        $map['legal_phone'] = $_POST['legal_phone'];
        //新加的支付宝 接口内容
        $matt['alipay_partner_id'] = $_POST['alipay_partner_id'];
        $matt['alipay_id'] = $_POST['alipay_id'];
        $matt['rsa_key'] = $_POST['rsa_key'];
        $res22 = $tmc_config->where("tmc_id=" . $tmc_id)->save($matt);

        $res = $Tmc->where("u_id=" . $id)->save($map);

        if ($res) {

            //企业申请认证信息
            $Cert_request = M('tmc_cert_request');//实例化申请认证的信息
            //将企业申请认证信息，加入申请认证表中；
            //查看是否有查询的结果；
            $result = $Tmc->where("u_id=" . $id)->select();
            $strsql = $Cert_request->query("select a.tmc_id,b.id from 73go_tmc_cert_request as a left join 73go_tmc as b on a.tmc_id=b.id where a.tmc_id=" . $result[0]['id']);

            //print_r($strsql);exit;

            if ($strsql) {
                //进行申请记录数据的更新操作；
                $datt['tmc_id'] = $result[0]['id'];
                $datt['req_time'] = date('Y-m-d H:i:s', time());
                $req = $Cert_request->where("tmc_id=" . $result[0]['id'])->save($datt);

            } else {
                //进行申请记录数据的添加的操作；
                $datt['tmc_id'] = $result[0]['id'];
                $datt['req_time'] = date('Y-m-d H:i:s', time());

                $req = $Cert_request->add($datt);

            }

            $this->redirect('Config/config_tmcinfo_certificated');
            //$this->success("你的相关信息已经提交！",U('Config/config_tmcinfo_certificated'));
        } else {
            $this->redirect('Config/config_tmcinfo_certificated');
            //$this->error("你信息提交没有成功！",U('Config/config_tmcinfo_certificated'));

        }


    }


    //处理TMC已认证或者待认证的页面 cert_val状态值为 1 或者2
    public function doconfig_tmcinfo_certificated12()
    {
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("tmc");
        $id = Li("userId");
        $tmc_id = Li("tmcId"); //获取当前用户的tmcid;
        //echo $tmc_id;
        $Tmc = M("Tmc");
        $tmc_config = M("tmc_config_tbl");
        $map['u_id'] = $id;
        //$map=$_POST;
        //print_r($map);exit;

        $map['license_no'] = $_POST['license_no'];
        $map['license_date'] = $_POST['license_date'];
        $map['license_period'] = $_POST['license_period'];
        $map['legal_name'] = $_POST['legal_name'];
        $map['legal_phone'] = $_POST['legal_phone'];
        //新加的支付宝 接口内容
        $matt['alipay_partner_id'] = $_POST['alipay_partner_id'];
        $matt['alipay_id'] = $_POST['alipay_id'];
        $matt['rsa_key'] = $_POST['rsa_key'];
        $tmc_config->where("tmc_id=" . $tmc_id)->save($matt);

        $res = $Tmc->where("u_id=" . $id)->save($map);
        if ($res) {
            //企业申请认证信息
            $Cert_request = M('tmc_cert_request');//实例化申请认证的信息
            //将企业申请认证信息，加入申请认证表中；
            //查看是否有查询的结果；
            $result = $Tmc->where("u_id=" . $id)->select();
            $strsql = $Cert_request->query("select a.tmc_id,b.id from 73go_tmc_cert_request as a left join 73go_tmc as b on a.tmc_id=b.id where a.tmc_id=" . $result[0]['id']);

            //print_r($strsql);exit;

            if ($strsql) {
                //进行申请记录数据的更新操作；
                $datt['tmc_id'] = $result[0]['id'];
                $datt['req_time'] = date('Y-m-d H:i:s', time());
                $req = $Cert_request->where("tmc_id=" . $result[0]['id'])->save($datt);

            } else {
                //进行申请记录数据的添加的操作；
                $datt['tmc_id'] = $result[0]['id'];
                $datt['req_time'] = date('Y-m-d H:i:s', time());

                $req = $Cert_request->add($datt);

            }
            $this->redirect('Config/config_tmcinfo_certificated');
            //$this->success("",U('Config/config_tmcinfo_certificated'));
        } else {
            $this->redirect('Config/config_tmcinfo_certificated');
            //$this->error("",U('Config/config_tmcinfo_certificated'));

        }


    }


    //处理TMC认证没有通过的页面 cert_val状态值为 3
    public function doconfig_tmcinfo_certificated3()
    {
        //echo "123456";exit;
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("tmc");
        $id = Li("userId");
        $Tmc = M("Tmc");
        $tmc_config = M("tmc_config_tbl");
        $tmc_id = Li("tmcId"); //获取当前用户的tmcid;

        //$map=$_POST;
        $map['license_no'] = $_POST['license_no'];
        $map['license_date'] = $_POST['license_date'];
        $map['license_period'] = $_POST['license_period'];
        $map['legal_name'] = $_POST['legal_name'];
        $map['legal_phone'] = $_POST['legal_phone'];
        //新加的支付宝 接口内容
        $matt['alipay_partner_id'] = $_POST['alipay_partner_id'];
        $matt['alipay_id'] = $_POST['alipay_id'];
        $matt['rsa_key'] = $_POST['rsa_key'];


        $map['cert_val'] = 2;
        $res22 = $tmc_config->where("tmc_id=" . $tmc_id)->save($matt);
        $res = $Tmc->where("u_id=" . $id)->save($map);

        //print_r($res);exit;
        if ($res) {

            //企业申请认证信息
            $Cert_request = M('tmc_cert_request');//实例化申请认证的信息
            //将企业申请认证信息，加入申请认证表中；
            //查看是否有查询的结果；
            $result = $Tmc->where("u_id=" . $id)->select();
            $strsql = $Cert_request->query("select a.tmc_id,b.id from 73go_tmc_cert_request as a left join 73go_tmc as b on a.tmc_id=b.id where a.tmc_id=" . $result[0]['id']);

            //print_r($strsql);exit;

            if ($strsql) {
                //进行申请记录数据的更新操作；
                $datt['tmc_id'] = $result[0]['id'];
                $datt['req_time'] = date('Y-m-d H:i:s', time());
                $req = $Cert_request->where("tmc_id=" . $result[0]['id'])->save($datt);


            } else {
                //进行申请记录数据的添加的操作；
                $datt['tmc_id'] = $result[0]['id'];
                $datt['req_time'] = date('Y-m-d H:i:s', time());

                $req = $Cert_request->add($datt);

            }

            $this->redirect('Config/config_tmcinfo_certificated');
            //$this->success("你的相关信息已经提交！",U('Config/config_tmcinfo_certificated'));
        } else {
            $this->redirect('Config/config_tmcinfo_certificated');
            //$this->error("你信息提交没有成功！",U('Config/config_tmcinfo_certificated'));

        }

    }

    //用户修改密码的操作

    public function mod_password()
    {

        $id = LI('userId');
        $user = M('user');
        $map['password'] = md5($_POST['newpassword']);
        $res = $user->where("id=" . $id)->save($map);
        if ($res) {
            $this->success('修改成功');//, U('Index/config_myinfo_acount'));
        } else {
            $this->success('修改失败');//, U('Index/config_myinfo_acount'));
        }


    }

    //验证原有的密码

    public function check_password()
    {

        $map['id'] = LI('userId');
        $map['password'] = md5($_POST['password']);
        $user = M('user');
        $result = $user->where($map)->select();
        //print_r($result);
        if ($result) {
            echo 0;
        } else {
            echo 1;
        }


    }
    /**
     * upload函数用于上传文件 可检测文件的大小和类型 ,自动判断文件的路径
     * author: 钟明 2015-01-30
     */
    public function upload()
    {
        $imgTypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png');//允许上传的文件类型
        $imgSize = 1048576 * 2;//文件大小
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/Uploads/';// 设置附件上传目录
        $uploadName = $_FILES["uploadFile"]["name"];
        $uploadPostfix = explode('.', $uploadName);//文件后缀
        $uploadPostfix = $uploadPostfix[1];//文件后缀
        $uploadType = $_FILES['uploadFile']['type']; //文件类型
        $uploadError = $_FILES['uploadFile']['error']; //错误类型
        $uploadSize = $_FILES['uploadFile']['size']; //文件大小
        if ($uploadError > 0) { //上传系统出错时
            $result = "";
            switch ($_FILES['uploadFile']['error']) {
                case 1:
                    $result = '文件大小超过服务器限制';
                    break;
                case 2:
                    $result = '文件太大!';
                    break;
                case 3:
                    $result = '文件只加载了一部分!';
                    break;
                case 4:
                    $result = '文件加载失败!';
                    break;
            }
            $this->showError($uploadError, $result);
        }
        if (!in_array(strtolower($uploadType), $imgTypes)) {
            $this->showError(-1, "文件格式不正确!");
        }
        if ($uploadSize > $imgSize) {
            $this->showError(-1, "文件大小不能超过2M!!");
        }
        $today = date("YmdHis");
        $saveName = $today . '.' . $uploadPostfix;
        $finalName = $savePath . $saveName;
        if (is_uploaded_file($_FILES['uploadFile']['tmp_name'])) {
            if (!move_uploaded_file($_FILES['uploadFile']['tmp_name'], $finalName)) {
                $this->showError(-1, "文件保存失败!!");
            } else {
                $res = $this->updateTMCImg($saveName);
                if ($res) {
                    $this->showError(1, "图片上传成功!!");
                } else {
                    $this->showError(-1, "图片保存错误!!");
                };
            }
        } else {
            $this->showError(-1, "文件已经上传!!");
        }

    }

    private function showError($errorCode, $errorInfo)
    {
        $result['code'] = $errorCode;
        $result['info'] = $errorInfo;
        $this->ajaxReturn($result, "JSON");
    }

    private function updateTMCImg($licenseImage)
    {
        $id = Li("userId");
        $Tmc = M("Tmc");
        $map['u_id'] = $id;
        $map['license_image'] = $licenseImage;
        return $Tmc->where("u_id=" . $id)->save($map);
    }
}
	
