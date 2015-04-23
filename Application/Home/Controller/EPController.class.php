<?php
namespace Home\Controller;

use System\LoginInfo;
use Think\Controller;
use Think\Exception;

/**
 * 企业政策配置
 * 企业：Enterprise
 * 政策：Policy
 * 创建者：董发勇
 * 创建时间：2014-11-10上午10:56:27
 *
 *
 */
class EPController extends Controller
{
    /**
     * 选中企业配置菜单，进入到企业配置界面
     * 基本配置：indexjb
     * 特殊配置：indexts
     * 政策文件：indexwj
     * 创建者：董发勇
     * 创建时间：2014-11-10上午11:02:31
     */
    public function indexjb()
    {
        $comId = LI('comId');
        $travel_policy = M('travel_policy');
        $data['co_id'] = $comId;
        $data['emp_id'] = array('exp', 'IS NULL');
        $request = $travel_policy->where($data)->find();
        $this->assign('travel', $request);
        $this->theme('default')->display("co_travel_policy_set");
    }

    // 查询特殊企业政策配置人员信息
    public function indexts()
    {
        session('check', null);
        $sessionid = LI("userId");//判断当前企业登陆的id值
        //获取当前企业id
        $travel = M('');
        $sql = "select * from 73go_company where u_id =$sessionid";
        $request = $travel->query($sql);
        $comid = $request[0]['id'];
        $Specialuser = M('');
        $sql1 = "select c.id as cid,y.id,y.`name`,y.id as yid from 73go_company as c,73go_travel_policy as t,73go_employee as y
							where
						  	c.id = y.co_id and 
						  	c.id = t.co_id and 
						  	y.id = t.emp_id and
						  	c.u_id=$sessionid";

        $result = $Specialuser->query($sql1);
        $showActive['emp_id'] = 0;
        $this->assign('tsname', $result);
        $this->assign('travel', $showActive);
        $sqladd = "select e.id,e.emp_code,e.name,b.`name` as bname,e.phone,e.email,e.role FROM 73go_employee as e
						LEFT OUTER JOIN 73go_company as c ON 
						c.id = e.co_id LEFT OUTER JOIN 73go_branch as b ON
						e.br_id = b.id 
						LEFT OUTER JOIN 73go_travel_policy as t ON 
						t.co_id = e.co_id AND 
						t.emp_id = e.id AND 
						t.emp_id <> 0 WHERE 
						t.emp_id IS NULL and c.id=$comid";
        $resultadd = $Specialuser->query($sqladd);

        $this->assign('tsnameadd', $resultadd);

        layout("home");
        $this->theme("default")->display('co_travel_policy_set2');
    }

    public function indexwj()
    {
        $sessionid = LI("userId");//判断当前企业登陆的id值
        //获取当前企业id
        $travel = M('');
        $sql = "select * from 73go_company where u_id =$sessionid";
        $request = $travel->query($sql);
        $comid = $request[0]['id'];
        $travel = M('');
        $sql = "select * from 73go_travel_policy where emp_id  is NULL and co_id=$comid";
        $request = $travel->query($sql);
        session('check', $request[0]);
        layout("home");
        $this->theme("default")->display('co_travel_policy_text');
    }

    /**
     * 修改基本差旅政策配置信息
     * 创建者：董发勇
     * 创建时间：2014-11-10下午02:38:16
     */
    public function updatebasicEP()
    {
        $strdata = $_POST;
        $travel = M('travel_policy');
        $id = $strdata['id'];
        $Ajaxdate['co_id'] = $strdata['co_id'];
        $Ajaxdate['need_approval'] = $strdata['need_approval'] ? 1 : 0;
        $Ajaxdate['without_app'] = $strdata['without_app'] ? 1 : 0;
        $Ajaxdate['emergency_booking'] = $strdata['emergency_booking'] ? 1 : 0;
        $Ajaxdate['f_class'] = $strdata['f_class'] ? 1 : 0;
        $Ajaxdate['b_class'] = $strdata['b_class'] ? 1 : 0;
        $Ajaxdate['e_class'] = $strdata['e_class'] ? 1 : 0;
        $Ajaxdate['soft_sleeper'] = $strdata['soft_sleeper'] ? 1 : 0;
        $Ajaxdate['hard_sleeper'] = $strdata['hard_sleeper'] ? 1 : 0;
        $Ajaxdate['hard_sleeper_limit'] = $strdata['hard_sleeper_limit'];
        $Ajaxdate['high_train'] = $strdata['high_train'] ? 1 : 0;
        $Ajaxdate['ht_b_class'] = $strdata['ht_b_class'] ? 1 : 0;
        $Ajaxdate['ht_class_1'] = $strdata['ht_class_1'] ? 1 : 0;
        $Ajaxdate['ht_class_2'] = $strdata['ht_class_2'] ? 1 : 0;
        $Ajaxdate['limit_hotel_price'] = $strdata['limit_hotel_price'] ? 1 : 0;
        $Ajaxdate['high_price_1'] = $strdata['high_price_1'];
        $Ajaxdate['high_price_2'] = $strdata['high_price_2'];
        $Ajaxdate['high_price_3'] = $strdata['high_price_3'];
        $Ajaxdate['limit_hotel_class'] = $strdata['limit_hotel_class'] ? 1 : 0;
        $Ajaxdate['hotel_class_5'] = $strdata['hotel_class_5'] ? 1 : 0;
        $Ajaxdate['hotel_class_4'] = $strdata['hotel_class_4'] ? 1 : 0;
        $Ajaxdate['hotel_class_3'] = $strdata['hotel_class_3'] ? 1 : 0;
        $Ajaxdate['hotel_class_2'] = $strdata['hotel_class_2'] ? 1 : 0;
        $requet = $travel->where('id=' . $id)->data($Ajaxdate)->save();
        $this->ajaxReturn(($requet) ? 1 : 0);
    }

    /**
     * 保存特殊用户差旅配置
     * Enter description here ...
     */
    public function addtsEP()
    {
        $strdata = $_POST;
        $travel = M('travel_policy');
        $Ajaxdate['co_id'] = LI('comId');
        $Ajaxdate['emp_id'] = $strdata('emp_id');
        $Ajaxdate['need_approval'] = $strdata['need_approval'];
        $Ajaxdate['without_app'] = $strdata['without_app'];
        $Ajaxdate['emergency_booking'] = $strdata['emergency_booking'];
        $Ajaxdate['f_class'] = $strdata['f_class'];
        $Ajaxdate['b_class'] = $strdata['b_class'];
        $Ajaxdate['e_class'] = $strdata['e_class'];
        $Ajaxdate['soft_sleeper'] = $strdata['soft_sleeper'];
        $Ajaxdate['hard_sleeper'] = $strdata['hard_sleeper'];
        $Ajaxdate['hard_sleeper_limit'] = $strdata['hard_sleeper_limit'];
        $Ajaxdate['high_train'] = $strdata['high_train'];
        $Ajaxdate['ht_b_class'] = $strdata['ht_b_class'];
        $Ajaxdate['ht_class_1'] = $strdata['ht_class_1'];
        $Ajaxdate['ht_class_2'] = $strdata['ht_class_2'];
        $Ajaxdate['limit_hotel_price'] = $strdata['limit_hotel_price'];
        $Ajaxdate['high_price_1'] = $strdata['high_price_1'];
        $Ajaxdate['high_price_2'] = $strdata['high_price_2'];
        $Ajaxdate['high_price_3'] = $strdata['high_price_3'];
        $Ajaxdate['limit_hotel_class'] = $strdata['limit_hotel_class'];
        $Ajaxdate['hotel_class_5'] = $strdata['hotel_class_5'];
        $Ajaxdate['hotel_class_4'] = $strdata['hotel_class_4'];
        $Ajaxdate['hotel_class_3'] = $strdata['hotel_class_3'];
        $Ajaxdate['hotel_class_2'] = $strdata['hotel_class_2'];
        $requet = $travel->data($Ajaxdate)->add();
        $this->ajaxReturn(($requet) ? 1 : 0);
    }

    /**
     * 更新特殊用户差旅配置
     * Enter description here ...
     */
    public function updatetsEP()
    {
        $strdata = $_POST;
        $travel = M('travel_policy');
        $id = $strdata['id'];
        $Ajaxdate['need_approval'] = $strdata['need_approval'];
        $Ajaxdate['without_app'] = $strdata['without_app'];
        $Ajaxdate['emergency_booking'] = $strdata['emergency_booking'];
        $Ajaxdate['f_class'] = $strdata['f_class'];
        $Ajaxdate['b_class'] = $strdata['b_class'];
        $Ajaxdate['e_class'] = $strdata['e_class'];
        $Ajaxdate['soft_sleeper'] = $strdata['soft_sleeper'];
        $Ajaxdate['hard_sleeper'] = $strdata['hard_sleeper'];
        $Ajaxdate['hard_sleeper_limit'] = $strdata['hard_sleeper_limit'];
        $Ajaxdate['high_train'] = $strdata['high_train'];
        $Ajaxdate['ht_b_class'] = $strdata['ht_b_class'];
        $Ajaxdate['ht_class_1'] = $strdata['ht_class_1'];
        $Ajaxdate['ht_class_2'] = $strdata['ht_class_2'];
        $Ajaxdate['limit_hotel_price'] = $strdata['limit_hotel_price'];
        $Ajaxdate['high_price_1'] = $strdata['high_price_1'];
        $Ajaxdate['high_price_2'] = $strdata['high_price_2'];
        $Ajaxdate['high_price_3'] = $strdata['high_price_3'];
        $Ajaxdate['limit_hotel_class'] = $strdata['limit_hotel_class'];
        $Ajaxdate['hotel_class_5'] = $strdata['hotel_class_5'];
        $Ajaxdate['hotel_class_4'] = $strdata['hotel_class_4'];
        $Ajaxdate['hotel_class_3'] = $strdata['hotel_class_3'];
        $Ajaxdate['hotel_class_2'] = $strdata['hotel_class_2'];
        $requet = $travel->where('id=' . $id)->data($Ajaxdate)->add();
        $this->ajaxReturn(($requet) ? 1 : 0);
    }

    /**
     * 根据id查询特殊用户差旅配置
     * Enter description here ...
     */
    public function findBytsId()
    {
        session('check', null);
        $sessionid = LI("userId");//判断当前企业登陆的id值
        $comId = LI('comId');
        $travel_policy = M('travel_policy');
        $Specialuser = M('');
        $data['co_id'] = $comId;
        $data['emp_id'] = $_GET['emp_id'];
        $request = $travel_policy->where($data)->find();

        $sql1 = "select c.id as cid,y.id,y.`name`,y.id as yid from 73go_company as c,73go_travel_policy as t,73go_employee as y
							where
						  	c.id = y.co_id and
						  	c.id = t.co_id and
						  	y.id = t.emp_id and
						  	c.u_id=$sessionid";
        /*添加的用户列表*/
        $result = $Specialuser->query($sql1);
        $this->assign('tsname', $result);
        /*对应用户的差旅配置*/
        $this->assign('travel', $request);

        layout("home");
        $this->theme("default")->display('co_travel_policy_set2');
    }


    public function shouwtsEPLike()
    {
        $sessionid = LI("userId");//判断当前企业登陆的id值
        //获取当前企业id
        $travel = M('');
        $sql = "select * from 73go_company where u_id =$sessionid";
        $request = $travel->query($sql);
        $comid = $request[0]['id'];
        $Specialuser = M('');

        $sqladd = "select e.id,e.emp_code,e.name,b.`name` as bname,e.phone,e.email,e.role FROM 73go_employee as e
						LEFT OUTER JOIN 73go_company as c ON
						c.id = e.co_id LEFT OUTER JOIN 73go_branch as b ON
						e.br_id = b.id
						LEFT OUTER JOIN 73go_travel_policy as t ON
						t.co_id = e.co_id AND
						t.emp_id = e.id AND
						t.emp_id <> 0 WHERE
						t.emp_id IS NULL and c.id=$comid";
        $resultadd = $Specialuser->query($sqladd);

        $this->ajaxReturn($resultadd);

    }

    /**
     * 向政策表中插入选择的企业员工
     * 创建者：董发勇
     * 创建时间：2014-11-11下午05:33:54
     */
    public function addtsEPUser()
    {
        //获取当前登陆公司的id
        $comuser['u_id'] = LI("userId");//判断当前企业登陆的id值
        $companydata = M('company');
        $result = $companydata->where($comuser)->find();
        $id = $result['id'];
        $travel_policy = M('travel_policy');
        try{
            foreach($_POST['ids'] as $item){
                $Ajaxdate['co_id'] = $id;
                $Ajaxdate['emp_id'] = $item['id'];
                $Ajaxdate['policy_text'] = "";
                $requets = $travel_policy->data($Ajaxdate)->add();
            }
            $this->ajaxReturn($requets);
        }catch (Exception $e){
            $this->ajaxReturn(-1);
        }


    }

    //选择用户名，显示出所有信息
    public function showtsuser()
    {
        $comuser['u_id'] = LI("userId");//判断当前企业登陆的id值
        $companydata = M('company');
        $result = $companydata->where($comuser)->find();
        $id = $result['id'];
        $ajaxuser['co_id'] = $id;
        $ajaxuser['emp_id'] = $_POST['usercheck'];
        $traeluserdata = M('travel_policy');
        $requestusercheck = $traeluserdata->where($ajaxuser)->find();
        //echo $this->ajaxReturn($requestusercheck);
        session('usercheck', $requestusercheck);
        if ($requestusercheck) {
            echo 1;
        } else {
            echo 2;
        }

    }

    //第二次点击用户名，清楚当前所有用户信息
    public function destroytsuser()
    {
        session('usercheck', null); // 销毁session
    }

    //删除特殊用户信息
    public function deletetsuser()
    {

        $datajaxa['co_id'] = $_POST['companyId'];//公司id
        $datajaxa['emp_id'] = $_POST['userId'];//员工id
        $traver = M('travel_policy');
        $request = $traver->where($datajaxa)->delete();
        $this->ajaxReturn($request);
    }

    //添加或者修改文件信息
    public function addupdatewjEP()
    {
        //获取当前登陆公司的id
        $comuser['u_id'] = LI("userId");//判断当前企业登陆的id值
        $companydata = M('company');
        $result = $companydata->where($comuser)->find();
        $id = $result['id'];
        $Ajaxdate['co_id'] = $id;
        $travel_policy = M('travel_policy');
        $trastuta = $travel_policy->where("emp_id is null")->where($Ajaxdate)->find();
        if ($trastuta) {//修改
            $policy_text = $_POST['policy_text'];
            $travel_policyupdate = M('');
            $sql = "update 73go_travel_policy  set
			 				policy_text='".$policy_text."'
			 				where co_id=".$id." and emp_id is null";
            $requestsion = $travel_policyupdate->execute($sql);
            if ($requestsion) {
                echo 2;
            }
        } else {//添加
            $travel_policyadd = M('travel_policy');
            $Ajaxd['co_id'] = $id;
            $Ajaxd['emp_id'] = null;
            $Ajaxd['policy_text'] = $_POST['policy_text'];
            $requets = $travel_policyadd->data($Ajaxd)->add();
            if ($requets) {
                echo 1;
            } else {
                echo 0;
            }
        }
    }


    /**
     * 添加特殊用户差旅配置
     * 创建者：董发勇
     * 创建时间：2014-11-27上午11:19:02
     */
    public function addupdatetsEP()
    {
        //获取当前登陆公司的id
        $strdata = $_POST;
        $Ajax['co_id'] = $strdata['co_id'];
        $Ajax['emp_id'] = $strdata['emp_id'];
        $Ajaxdate['need_approval'] = $strdata['need_approval'] ? 1 : 0;
        $Ajaxdate['without_app'] = $strdata['without_app'] ? 1 : 0;
        $Ajaxdate['emergency_booking'] = $strdata['emergency_booking'] ? 1 : 0;
        $Ajaxdate['f_class'] = $strdata['f_class'] ? 1 : 0;
        $Ajaxdate['b_class'] = $strdata['b_class'] ? 1 : 0;
        $Ajaxdate['e_class'] = $strdata['e_class'] ? 1 : 0;
        $Ajaxdate['soft_sleeper'] = $strdata['soft_sleeper'] ? 1 : 0;
        $Ajaxdate['hard_sleeper'] = $strdata['hard_sleeper'] ? 1 : 0;
        $Ajaxdate['hard_sleeper_limit'] = $strdata['hard_sleeper_limit'];
        $Ajaxdate['high_train'] = $strdata['high_train'] ? 1 : 0;
        $Ajaxdate['ht_b_class'] = $strdata['ht_b_class'] ? 1 : 0;
        $Ajaxdate['ht_class_1'] = $strdata['ht_class_1'] ? 1 : 0;
        $Ajaxdate['ht_class_2'] = $strdata['ht_class_2'] ? 1 : 0;
        $Ajaxdate['limit_hotel_price'] = $strdata['limit_hotel_price'] ? 1 : 0;
        $Ajaxdate['high_price_1'] = $strdata['high_price_1'];
        $Ajaxdate['high_price_2'] = $strdata['high_price_2'];
        $Ajaxdate['high_price_3'] = $strdata['high_price_3'];
        $Ajaxdate['limit_hotel_class'] = $strdata['limit_hotel_class'] ? 1 : 0;
        $Ajaxdate['hotel_class_5'] = $strdata['hotel_class_5'] ? 1 : 0;
        $Ajaxdate['hotel_class_4'] = $strdata['hotel_class_4'] ? 1 : 0;
        $Ajaxdate['hotel_class_3'] = $strdata['hotel_class_3'] ? 1 : 0;
        $Ajaxdate['hotel_class_2'] = $strdata['hotel_class_2'] ? 1 : 0;
        $Ajaxdate['policy_text'] = "";
        $travel = M('travel_policy');
        $requestsion = $travel->where($Ajax)->data($Ajaxdate)->save();

        $this->ajaxReturn($requestsion);
    }

    public function co_tmc_mgnt()
    {
        C('LAYOUT_ON', TRUE);
        //加载布局文件
        layout("home");

        $u_id = LI('userId');
        //查出co_id
        //$companyM=M('company');
        //$company=$companyM->where("u_id=".$u_id)->find();
        //$co_id=$company['id'];
        $co_id = LI('comId');
        $co_link = M('co_tmc_link');
        if (isTMCHosting ()) {
        	$where = " and ctl.tmc_id = ".getHostedTmcInfo ("tmc_id");
        }
        
        $sql = "select ctl.id,ctl.date,ctl.pay_type,ctl.`status`,tmc.tmc_code,tmc.name,tmc.short_name,ctl.co_id,ctl.tmc_id from
		73go_co_tmc_link ctl LEFT JOIN 73go_tmc tmc on ctl.tmc_id=tmc.id  where  ctl.co_id=" . $co_id.$where;
        $result = $co_link->query($sql);
        $count = count($result);
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $link = " limit $Page->firstRow , $Page->listRows";
        $result = $co_link->query($sql . $link);
        $this->assign('Page', $show);// 赋值分页输出
        $this->assign('co_link', $result);

        $this->theme('default')->display("co_tmc_mgnt");
    }

    //状态操作
    public function upd_link_status()
    {
        $linkid = $_POST['id'];
        $linkstatus = $_POST['status'];
        $co_link = M('co_tmc_link');
        //修改协议必须双方签署才能生效
        switch($linkstatus){
            case 9:
                $data['status'] = 7;
                break;
            case 8:
                $data['status'] = 7;
                break;
            case 7:
                $data['status'] = 9;
                break;
            case 6:
                $data['status'] = 0;
                break;
            default:
                $data['status'] = 9;
        }
        $result = $co_link->where('id=' . $linkid)->data($data)->save();
        /* 1.20删除vip客户gsf */
        $empM = M('employee');
        $vipM = M('vip_table');

        $co_id = LI('comId');
        if ($result) {
            $resu = $co_link->where('id=' . $linkid)->find();
            $status = $resu['status'];
            if ($status != 0) {
                $result2 = $empM->where('co_id=' . $co_id)->select();
                foreach ($result2 as $val) {
                    $res = $vipM->where('emp_id=' . $val['id'])->delete();
                }
            }
            $this->ajaxReturn($resu);
        }

    }

    // 删除协议
    public function deleteLink()
    {
        $linkid = $_POST['id'];
        $co_link = M('co_tmc_link');
        $result = $co_link->where('id=' . $linkid)->delete();
        /* 1.20删除vip客户gsf */
        $empM = M('employee');
        $vipM = M('vip_table');

        $co_id = LI('comId');
        if ($result) {
            $resu = $co_link->where('id=' . $linkid)->find();
            $status = $resu['status'];

            if ($status != 0) {
                $result2 = $empM->where('co_id=' . $co_id)->select();
                foreach ($result2 as $val) {
                    $res = $vipM->where('emp_id=' . $val['id'])->delete();
                }
            }
            $this->ajaxReturn($resu);
        }
    }

    public function addLinkCustomer()
    {
        $co_id = LI('comId');

        $strlist = $_POST['str'];

        $array = explode(",", $strlist);

        $arrayconunt = count($array);
        //print_r($arrayconunt);
        //1.19gsf更改
        $co_link = M('co_tmc_link');
        $tmcM = M('tmc');
        for ($i = 0; $i < $arrayconunt; $i++) {
            /* 1.20更改gsf*/
            $cond['tmc_id'] = $array[$i];
            $cond['co_id'] = $co_id;
            $res = $co_link->where($cond)->find();
            $tmcs = $tmcM->where('id=' . $res['tmc_id'])->find();
            $tmc_name .= $tmcs['name'] . " ";
            if (!$res) {
                $Ajaxdate['co_id'] = $co_id;
                $Ajaxdate['tmc_id'] = $array[$i];
                $Ajaxdate['date'] = date("Y-m-d H:i:s", time());
                $Ajaxdate['status'] = '7';

                $requets = $co_link->data($Ajaxdate)->add();
            }

        }
        if ($requets) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn($tmc_name, 'json');
        }
    }

    public function inputLink()
    {
        $co_id = LI('comId');
        $con = $_POST['con'];
        $tmc = M('');
        $sql = "select id,tmc_code,name,short_name from 73go_tmc
				where id not in (select co_id from 73go_co_tmc_link  where co_id=$co_id) and
		(tmc_code like '%" . $con . "%' or name like '%" . $con . "%' or short_name like '%" . $con . "%')";
        $result = $tmc->query($sql);
        $this->ajaxReturn($result);
    }

    //1.19gsf更改
    //查看tmc信息
    public function ajax_tmc_basicinfo()
    {
        $tmc_id = $_POST['tmc_id'];
        $tmcM = M('tmc');
        $result = $tmcM->where('id=' . $tmc_id)->find();
        $this->ajaxReturn($result, 'json');
    }
}
