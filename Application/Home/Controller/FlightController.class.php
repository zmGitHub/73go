<?php
/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2015/3/24
 * Time: 15:01
 */
namespace Home\Controller;

use Think\Controller;
//use Vendor\Ctrip\API;

class FlightController extends Controller {

    /**
     * 获取酒店基础信息入库
     *
     * @author cgk
     */
    public function getFlightInfo() {
        vendor('Ctrip.CtripUnion');
        //,'99668','542961','1B365D98-B21E-404F-9084-2EE3F709403D'
        $cu = new \CU ('flight', 'OTA_FlightSearch','99668','542961','1B365D98-B21E-404F-9084-2EE3F709403D');
        ini_set('memory_limit', '256M');
        set_time_limit(0);

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        //设置搜索参数
        $data['searchtype'] = $_POST['searchtype'];
        $data['depart_city'] = $_POST['depart_city'];
        $data['arrive_city'] =$_POST['arrive_city'];
        $data['depart_date'] =$_POST['depart_date'];
        $account= $_POST['account'];
        $flight1 =$_POST['flight1'];
        $flight2 = $_POST['flight2'];
        if($data['searchtype']=='D') {
            $data['return_date'] = $_POST['return_date'];
        }else {
            $data['return_date'] = '';
        }
        //设置缓存
        $now= time();
        $cachename="$account";
        $rt_tmp = S("$cachename");
        if (empty($flight1) || empty($rt_tmp)){
        $rt_tmp = $cu->OTA_FlightSearch ( $data, 'array' );
        $rt_tmp['time']=time()+300;
        S("$cachename",$rt_tmp,300);
        }else if( $now>$rt_tmp['time']){
            S("$cachename",null);
        }else{
        $rt_tmp = S("$cachename");
        }
        $rt=$rt_tmp;
        $m_city = M('city');
        $dicty = $m_city->where("CityCode='%s'",$data['depart_city'])->select();
        $aicty = $m_city->where( "CityCode='%s'",$data['arrive_city'])->select();
        $m_airport = M('airport');
        $dport = $m_airport->where('CityId ='.$dicty[0]['CityId'])->select();
        $dport_num = count($dport);
        $aport = $m_airport->where('CityId ='.$aicty[0]['CityId'])->select();
        $aport_num = count($aport);
        //抽取航班号
       // $rt_num_count = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['0']['RecordCount'];
       // for ($i=0;$i<$rt_num_count;$i++){
        //    $flight[$i]= $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'][$i]['Flight'];
        //}
        //$flight= array_unique($flight);
        //$flight= array_values($flight);
        //修改机场名称
        for($i=0;$i<$rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['RecordCount'];$i++){
            for($j=0;$j<$dport_num;$j++){
                if($data['searchtype']=='S'){
                    if($rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'][$i]['DPortCode']==$dport[$j]['AirPort']){
                        $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'][$i]['DPortCode'] = $dport[$j]['AirPortName'];
                    }
                }
                else{
                    if($rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute'][0]['FlightsList']['DomesticFlightData'][$i]['DPortCode']=$dport[$j]['AirPort']){
                        $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute'][0]['FlightsList']['DomesticFlightData'][$i]['DPortCode'] = $dport[$j]['AirPortName'];
                    }
                }

            }
        }

        for($i=0;$i<$rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['RecordCount'];$i++){
            for($j=0;$j<$aport_num;$j++){
                if($data['searchtype']=='S'){
                    if($rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'][$i]['APortCode']==$aport[$j]['AirPort']){
                        $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'][$i]['APortCode'] = $aport[$j]['AirPortName'];
                    }
                }
                else{
                    if($rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute'][0]['FlightsList']['DomesticFlightData'][$i]['APortCode']=$aport[$j]['AirPort']){
                        $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute'][0]['FlightsList']['DomesticFlightData'][$i]['APortCode'] = $aport[$j]['AirPortName'];
                    }
                }

            }
        }
        //选择航班
        if($data['searchtype']=='S') {
            $dpt_rt_num_count = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['RecordCount'];
            $dpt_flight_info = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'];
            $rtn_rt_num_count='';
            $rtn_flight_info ='';
        }else{
            $dpt_flight_info = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['0']['FlightsList']['DomesticFlightData'];
            $dpt_rt_num_count = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['0']['RecordCount'];
            $rtn_flight_info = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['1']['FlightsList']['DomesticFlightData'];
            $rtn_rt_num_count = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['1']['RecordCount'];
        }
        //选择最便宜的航班作为列表输出

        if($data['searchtype']=='S'&& empty($flight1)) {
            $dpt_flight_cheapest_array = $this->ReturnCheapFlight($dpt_flight_info, $dpt_rt_num_count);
            $rtn_flight_cheapest_array ='';
        }else if ($data['searchtype']=='D'&&empty($flight1)){
            $dpt_flight_cheapest_array = $this->ReturnCheapFlight($dpt_flight_info, $dpt_rt_num_count);
            $rtn_flight_cheapest_array = $this->ReturnCheapFlight($rtn_flight_info, $rtn_rt_num_count);
        }else{

        }
        $flight_cheapest_array= array('D'=>$dpt_flight_cheapest_array,'R'=>$rtn_flight_cheapest_array);
        //搜索具体航班信息。区分单程和往返
        if($data['searchtype']=='S'&& !empty($flight1)) {
            $dpt_flight_detail=$this->FlightDetail($dpt_flight_info,$flight1);
            $rtn_flight_detail='';

        }else if (!empty($flight1)){
            $dpt_flight_detail=$this->FlightDetail($dpt_flight_info,$flight1);
            $rtn_flight_detail=$this->FlightDetail($rtn_flight_info,$flight2);
        }else{

        }
        $flight_detail = array('D'=>$dpt_flight_detail,'R'=>$rtn_flight_detail);
        //选择输出便宜的航班列表还是具体航班信息
        if(empty($flight1)){
            $flight_info= $flight_cheapest_array;
        }else {
            $flight_info= $flight_detail;
        }
        //$flight_info =array($flight,$flight_cheapest_array);
        $this->ajaxReturn($flight_info, 'JSON' );
    }
    public function ReturnCheapFlight($flight_info,$rt_num_count){
        $flight_cheapest_array=array();
        $flight_tmp='';
        for($j=0;$j<$rt_num_count;$j++){
            if($flight_info[$j]['Flight'] == $flight_tmp){
                $flight_cheapest_array= $flight_cheapest_array;
            }else{
                $flight_cheapest_array[] = $flight_info[$j];
                $flight_tmp= $flight_info[$j]['Flight'];
            }
        }
        return $flight_cheapest_array;
    }


    public function FlightDetail($flight_info,$flight){
        foreach($flight_info as $values){
            if($values['Flight'] == $flight){
                $flight_detail[] = $values;
            }else {
                $flight_detail = $flight_detail;
            }
        }
        return $flight_detail;
    }

    //录入机场的数据信息
    public function airport_storage(){
        $airport_path = C('AIRPORT_PATH');
        $airport_full_path = $airport_path.'airport.xml';
        $airport_list=simplexml_load_file($airport_full_path);
        $airport_infolist=$airport_list->AirportInfosList ;
        $airport_count= $airport_list->RecordCount;
       for($i=0;$i<$airport_count;$i++) {
           $airport_infoentities[$i] = $airport_infolist->AirportInfoEntity[$i];
       }
       foreach ($airport_infoentities as $airport_info){
            $airport['code'] = $airport_info->AirPort;
            $airport['name']= $airport_info->AirPortName;
            $airport['ename']=$airport_info->AirPortEName;
            $airport['sname']= $airport_info->ShortName;
            $airport['cityid']= $airport_info->CityId;
            $airport['cityname']= $airport_info->CityName;
            $airport_list= M('airport');
            $airport_list->data($airport)->add();
        }
    }

    public function ctrip_submit(){
        vendor('Ctrip.CtripUnion');
        //,'99668','542961','1B365D98-B21E-404F-9084-2EE3F709403D'
        $cu = new \CU ('flight', 'OTA_FltSaveOrder','99668','542961','1B365D98-B21E-404F-9084-2EE3F709403D');
        ini_set('memory_limit', '256M');
        set_time_limit(0);

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $data['ordertype'] = 'ADU';//$_POST['ordertype '];
        $data['amount'] = '730';//$_POST['amount '];
        $data['desc'] = 'PEKtoSHA';// $_POST['desc '];
        $data['dcity_id']  = '1';//$_POST['dcity_id'];
        $data['acity_id']  = '2';//$_POST['acity_id'];
        $data['dport'] = 'PEK';//$_POST['dport'];
        $data['aport']  = 'SHA';//$_POST['aport '];
        $data['airlinecode']  = 'HO';//$_POST['airlinecode '];
        $data['flight']  = 'HO1252';//$_POST['airlinecode '];
        $data['class']   = 'Y';//$_POST['class  '];
        $data['subclass']  = 'K';//$_POST['subclass '];
        $data['dtime']  = '2015-05-15T06:50:00';////$_POST['dtime '];
        $data['atime']  = '2015-05-15T09:05:00';//$_POST['atime '];
        $data['rate']  = '0.55';//$_POST['rate '];
        $data['price']  = '680';//$_POST['price '];
        $data['tax']  = '50';//$_POST['tax '];
        $data['oilfee']  = '0.0';//$_POST['oilfee '];
        $data['nonrer']  ='H';// $_POST['nonrer  '];
        $data['nonref']  = 'H';//$_POST['nonref  '];
        $data['nonend']  ='T';// $_POST['nonend  '];
        $data['rernote']  ='起飞前2小时（含）以外需收取票面价10%的变更费，2小时以内及起飞后收取票面价20%的变更费。改期费与升舱费同时发生时，则需同时收取改期费和升舱差额。';// $_POST['rernote  '];
        $data['RefNote']  = '起飞前2小时（含）以外办理需收取票面价20％的退票费，2小时以内及起飞后办理需收取票面价30％的退票费。';//$_POST['RefNote  '];
        $data['EndNote']  = '不得签转。';//$_POST['EndNote '];
        $data['CraftType']  ='320';// $_POST['CraftType '];
        $data['Quantity']  ='10';// $_POST['Quantity '];
        $data['RefundFeeFormulaID']  ='142';//$_POST['RefundFeeFormulaID  '];
        $data['UpGrade']  ='T';// $_POST['UpGrade  '];
        $data['ProductType']  = 'Normal';//$_POST['ProductType  '];
        $data['PassengerName']  = '张鹏';//$_POST['PassengerName  '];
        $data['BirthDay']  = '1985-10-30';//$_POST['BirthDay'];
        $data['PassportTypeID']  = '1';//$_POST['PassportTypeID'];
        $data['PassportNo']  = '350622198510301039';//$_POST['PassportNo'];
        $data['ContactName']  = '张鹏';//$_POST['ContactName'];
        $data['MobilePhone']  = '18857166486';//$_POST['MobilePhone'];
        $data['ContactEMail']  = '18065355@qq.com';//$_POST['ContactEMail'];
        $data['DeliveryType']  = 'PJN';//$_POST['DeliveryType'];
        $data['SendTicketCityID']  = '';//$_POST['SendTicketCityID'];
        $data['SendTicketCityID']  = '';//$_POST['SendTicketCityID'];
        $data['Receiver']  = '';//$_POST['Receiver'];
        $data['Province']  = '';//$_POST['Province'];
        $data['City']  ='';// $_POST['City '];
        $data['Canton']  = '';//$_POST['Canton'];
        $data['Address']  = '';//$_POST['Address'];
        $data['PostCode']  = '';//$_POST['PostCode '];
        $rt_tmp = $cu->OTA_FltSaveOrder ( $data, 'array' );
        var_dump($rt_tmp) ;
    }

    //根据机场三字码返回机场名称
    public function airport_read($airport_code){
        $map['code'] =$airport_code;
        $airport_list= M('airport');
        $airport_detail= $airport_list->where($map)->select();
        return $airport_detail;
    }


    // 查找数据更新标识
    public function getTag($id) {
        $updata_tag = M ( "updata_tag" );
        $tag = $updata_tag->find ( $id );
        return $tag;
    }

    // 修改数据更新标识；
    public function updataTag($data) {
        $updata_tag = M ( "updata_tag" );
        $condition ['id'] = $data ['id'];
        $updata_tag->where ( $condition )->save ( $data );
    }
    public function airport_reg(){
        $file = "C:/wamp/www/73go/ThinkPHP/Library/Vendor/Ctrip/flight/airport.xml";
        $airport_list = simplexml_load_file($file);
        $count = $airport_list->RecordCount;
        $m_airport = M('airport');
        for($i=0;$i<$count;$i++){
            $m_airport ->data($airport_list->AirportInfosList->AirportInfoEntity[$i])->add();

        }

    }
    public function city_reg(){
        $file = "C:/wamp/www/73go/ThinkPHP/Library/Vendor/Ctrip/flight/city.xml";
        $airport_list = simplexml_load_file($file);
        $count = $airport_list->RecordCount;
        $m_airport = M('city');
        for($i=0;$i<$count;$i++){
            $m_airport ->data($airport_list->CityInfosList->CityInfoEntity[$i])->add();

        }

    }
}