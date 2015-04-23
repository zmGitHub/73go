<?php
namespace Agent\Controller;

use Think\Controller;

class ProductController extends Controller {

	//加载机票政策页面（config_flight_price）；
	public function  config_flight_price(){
		//加载布局 
	layout("tmc");
	
	//进行显示是否进行配置淘宝 和携程价格的显示
	$tmc_id=LI("tmcId");
	$tmc_config=M('tmc_config_tbl');
	$datt=$tmc_config->where("tmc_id=".$tmc_id)->find();
	$tmc_ft_policy=M("");
	$upload_filename=M('upload_filename');
	$sql="select max(file_id) from 73go_tmc_ft_policy where tmc_id=".$tmc_id;
	$data=$tmc_ft_policy->query($sql);
	$fileId=$data[0]['max(file_id)'];
	//查询出该tmc最新的机票政策
	$xls=$upload_filename->where('id='.$fileId)->find();
	$this->assign('xls',$xls);
	$this->assign("datt",$datt);
	$this->theme("agent")->display("product:config_flight_price");
	}
	//加载机票政策页面（config_flight_price）；
	public function  config_flight_prices($res,$fileId=false){
		$this->assign("res",$res);
		$this->assign("fileId",$fileId);
		$this->theme("agent")->display("product:config_flight_price");
	}

	//加载机票退改签政策页面（config_flight_refund）；
	public function  config_flight_refund(){
		$tmcId=LI('tmcId');
		$tmc_refund_policy=M("");
		$upload_filename=M('upload_filename');
		$sql="select max(file_id) from 73go_tmc_refund_policy where tmc_id=".$tmcId;
		$data=$tmc_refund_policy->query($sql);
		$fileId=$data[0]['max(file_id)'];
		//查询出该tmc最新的 机票退改签机票政策
		$xls=$upload_filename->where('id='.$fileId)->find();
		$this->assign('xls',$xls);
		$this->theme("agent")->display("config_flight_refund");
	}
	public function config_flight_refunds($res,$fileId=false){

		$this->assign("res",$res);
		$this->assign("fileId",$fileId);
		$this->theme("agent")->display("product:config_flight_refund");
	}
	
	//加载保险产品页面（config_insur_info）；
	public function  config_insur_info(){
		$tmc_id=LI('tmcId');
		$air_insur_product=M('air_insur_product');
		$air_insur_productsql="select * from 73go_air_insur_product where tmc_id=$tmc_id and  `status` =0";
		$request=$air_insur_product->query($air_insur_productsql);
		$this->assign('insur_info',$request);
		$this->theme("agent")->display("config_insur_info");
	
	}	
	
	//加载服务费页面（config_service_price）；
	public function  config_service_price(){
		$tmcid=LI('tmcId');
		$serv_price_config=M('');
		$serv_pricesql="select c.name,s.* from 73go_serv_price_config s LEFT JOIN 73go_tmc  t ON
						s.tmc_id=t.id LEFT JOIN 73go_company c on s.co_id=c.id
						where s.tmc_id=$tmcid and s.`status`=0";
		$request=$serv_price_config->query($serv_pricesql);
		$this->assign('request',$request);
		//查询所有企业信息
		$comsql="select * from 73go_company where `status`=0 ";
		$comrequest=$serv_price_config->query($comsql);
		
		$this->assign('com',$comrequest);
		$this->theme("agent")->display("config_service_price");
	}
	
//产品管理  更新配置表（tmc_config_tbl）;
	//进行配置淘宝价格显示
	public function save_taobao_product(){
		//加载布局
		layout('tmc');
		$tmc_config=M('tmc_config_tbl');		
		$tmc_id=LI("tmcId");
		$map['alitrip_show']=$_POST['taobao'];
		//$map['ctrip_show']=$_POST['ctrip'];		
		$data=$tmc_config->where("tmc_id=".$tmc_id)->save($map);

	}
	
	//进行配置携程价格显示
	public function save_ctrip_product(){
		//加载布局
		layout('tmc');
		$tmc_config=M('tmc_config_tbl');		
		$tmc_id=LI("tmcId");
		//$map['alitrip_show']=$_POST['taobao'];
		$map['ctrip_show']=$_POST['ctrip'];		
		$data=$tmc_config->where("tmc_id=".$tmc_id)->save($map);

	}
	//添加机票价格配置
	public function addflight_price(){
		$var = $_POST;
		$obj = array();
		$tmcId=LI('tmcId');
		$policy=M('tmc_ft_policy');
		//分装数据保存到数组中去
		foreach($var['list'] as $key=>$val){
			$arr = array();
			$arr[0]=$this->is_co($val['code']);
			$arr[1]=$this->is_airline_co($val['name']);
			$arr[2]=$this->is_airline_co($val['airline']);
			$arr[3]=$this->is_airline_co($val['flight_num']);
			$arr[4]=$this->is_airline_co($val['class']);
			$arr[5]=$this->is_type($val['type']);
			$arr[6]=$val['val_opp'];
			$arr[7]=$val['val_abs'];
			$arr[8]=$this->is_time($val['time']);
			$arr[9]=$val['fileId'];			
			array_push($obj, $arr);
		}
		foreach ($obj as $k=>$flight){
			foreach($flight[0] as $flkey=>$flval){//企业名称
				foreach ($flight[1] as $flightkey=>$flightval){//航空公司
					foreach ($flight[2] as $flightkeyto=>$flightvalto){//航线
						foreach ($flight[3] as $flightkeysan=>$flightvalsan){//适用航班
							foreach ($flight[4] as $flightkeysi=>$flightvalsi){//仓位
								if($flval== null)
								{
									$flval =array('exp','IS NULL');
									$select=$this->selectprice($tmcId, $flval, $flightval, $flightvalto, $flightvalsan, $flightvalsi, $flight[5], $flight[6], $flight[7], $this->is_okTime($flight[8][0]), $this->is_okTime($flight[8][1]));
									if($select){
										echo 1;
										continue;
									}else{
										$flval=null;
										$this->addprice($tmcId, $flval, $flightval, $flightvalto, $flightvalsan, $flightvalsi, $flight[5], $flight[6], $flight[7], $this->is_okTime($flight[8][0]), $this->is_okTime($flight[8][1]),$flight[9]);
										echo 2;
										continue;
									}
								}
								if($flval!=null){
									$select=$this->selectprice($tmcId, $flval, $flightval, $flightvalto, $flightvalsan, $flightvalsi, $flight[5], $flight[6], $flight[7], $this->is_okTime($flight[8][0]), $this->is_okTime($flight[8][1]));
									if($select){
										echo 1;
										continue;
									}else{
										$this->addprice($tmcId, $flval, $flightval, $flightvalto, $flightvalsan, $flightvalsi, $flight[5], $flight[6], $flight[7], $this->is_okTime($flight[8][0]), $this->is_okTime($flight[8][1]),$flight[9]);
										echo 2;
										continue;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	public function addprice($tmcId,$flval,$flightval,$flightvalto,$flightvalsan,$flightvalsi,$type,$val_opp,$val_abs,$time_start,$time_end,$fileId=false){
			$policy=M('tmc_ft_policy');
			$data['tmc_id']=$tmcId;
			$data['co_id']=$flval;
			$data['airline_co']=$flightval;
			$data['airline']=$flightvalto;
			$data['flight_num']=$flightvalsan;
			$data['class']=$flightvalsi;
			$data['type']=$type;
			$data['val_opp']=$val_opp;
			$data['val_abs']=$val_abs;
			$data['time_start']=$time_start;
			$data['time_end']=$time_end;
			$data['file_id']=$fileId;
			$data['status']=0;
			$request=$policy->data($data)->add();
	}
	public function selectprice($tmcId,$flval,$flightval,$flightvalto,$flightvalsan,$flightvalsi,$type,$val_opp,$val_abs,$time_start,$time_end){
			$policy=M('tmc_ft_policy');
			$data['tmc_id']=$tmcId;
			$data['co_id']=$flval;
			$data['airline_co']=$flightval;
			$data['airline']=$flightvalto;
			$data['flight_num']=$flightvalsan;
			$data['class']=$flightvalsi;
			$data['type']=$type;
			$data['val_opp']=$val_opp;
			$data['val_abs']=$val_abs;
			$data['time_start']=$time_start;
			$data['time_end']=$time_end;
			$data['status']=0;
			return $policy->where($data)->find();
	}
	//处理适用客户字段
	public function is_co($res){
		if($res != ""){
			$com=M('company');
			$data['name|short_name']=$res;
			$data['status']=0;
			$request= $com->where($data)->find();
			$id=$request['id'];
			$arr[]=array();
			$arr[0]=$id;
			return $arr;
		}
		if($res == ""){
			$arr[]=array();
			$arr[0]=$id;
			return $arr;
		}
	}
	//处理航空公司字段
	public function is_airline_co($res){
		$a=strtoupper($res); 
		$b=preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)|(\、)|(-)/",',',$a);
		$c=explode(",",$b);
		return $c;
	}
	//处理旅游类型字段
	public function is_type($res){
		$dictionary=M('dictionary');
		$data['d_group']="flight_type";
		$data['d_value']=$res;
		$request=$dictionary->where($data)->find();
		return $request['d_key'];
	}
	//处理时间
	public function is_time($res){
		$a=strtoupper($res); 
		$b=preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)|(-)/",',',$a);
		$c=explode(",",$b);
		$d=preg_replace("/(年)|(月)|(日)/",'-',$c);
		return $d;
	}
	//时间再处理
	public function is_okTime($res){
		$a=rtrim($res,"-");
		$arr = explode('-',$a);
 		// 按 "-" 分隔成数组,也可以是其它的分隔符
 		$time = mktime(0,0,0,$arr[1],$arr[2],$arr[0]);
 		// 根据数组中的三个数据生成UNIX时间戳mktime(时,分,秒,月,日,年)
 		$fmtDate = date('Y-m-d H:i:s', $time);
		return $fmtDate;
	}
	
	//添加退改签信息
	public function addflight_refund(){
		$var = $_POST;
		$obj = array();
		$tmcId=LI('tmcId');
		$policy=M('tmc_refund_policy');
		//分装数据保存到数组中去
		foreach($var['list'] as $key=>$val){
			$arr = array();
			$arr[0]=$this->is_co($val['code']);
			$arr[1]=$this->is_airline_co($val['name']);
			$arr[2]=$this->is_airline_co($val['airline']);
			$arr[3]=$this->is_airline_co($val['flight_num']);
			$arr[4]=$this->is_airline_co($val['class']);
			$arr[5]=$this->is_type($val['type']);
			$arr[6]=$val['da'];
			$arr[7]=$this->is_time($val['time']);
			$arr[8]=$val['fileId'];
			array_push($obj, $arr);
		}
		foreach ($obj as $k=>$flight){
			foreach($flight[0] as $flkey=>$flval){//企业名称
				foreach ($flight[1] as $flightkey=>$flightval){//航空公司
					foreach ($flight[2] as $flightkeyto=>$flightvalto){//航线
						foreach ($flight[3] as $flightkeysan=>$flightvalsan){//适用航班
							foreach ($flight[4] as $flightkeysi=>$flightvalsi){//仓位
								$data['tmc_id']=$tmcId;
								$data['co_id']=$flval;
								$data['airline_co']=$flightval;
								$data['airline']=$flightvalto;
								$data['flight_num']=$flightvalsan;
								$data['class']=$flightvalsi;
								$data['type']=$flight[5];
								$data['content']=$flight[6];
								$data['time_start']=$this->is_okTime($flight[7][0]);
								$data['time_end']=$this->is_okTime($flight[7][1]);
								$data['file_id']=$flight[8];
								$data['status']=0;
								$requestse=$policy->where($data)->select();
								if($requestse){
									echo 1;
									continue;
								}else{
									$request=$policy->data($data)->add();
									dump($request);
								}
								
							}
						}
					}
				}
			}
			
		}
	}
	
	public function addrefund($tmcId,$flval,$flightval,$flightvalto,$flightvalsan,$flightvalsi,$type,$content,$time_start,$time_end){
		$data['tmc_id']=$tmcId;
		$data['co_id']=$flval;
		$data['airline_co']=$flightval;
		$data['airline']=$flightvalto;
		$data['flight_num']=$flightvalsan;
		$data['class']=$flightvalsi;
		$data['type']=$type;
		$data['content']=$content;
		$data['time_start']=$time_start;
		$data['time_end']=$time_end;
		$data['status']=0;
		$request=$policy->data($data)->add();
	}

}