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
        C ( 'LAYOUT_ON', false );
        vendor('Ctrip.CtripUnion');
        //,'99668','542961','1B365D98-B21E-404F-9084-2EE3F709403D'
        $cu = new \CU ('flight', 'OTA_FlightSearch','99668','542961','1B365D98-B21E-404F-9084-2EE3F709403D');
        ini_set('memory_limit', '256M');
        set_time_limit(0);

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        //设置搜索参数
        $data['searchtype'] = $_POST['searchtype'];
        $data['depart_city'] =  $_POST['depart_city'];
        $data['arrive_city'] =$_POST['arrive_city'];
        $data['depart_date'] = $_POST['depart_date'];
        $userid= $_POST['userid'];
        $flight1 =$_POST['flight1'];
        $flight2 = $_POST['flight2'];
        if($data['searchtype']=='D') {
            $data['return_date'] = $_POST['return_date'];
        }else {
            $data['return_date'] = '';
        }
        //设置缓存
        $now= time();
        $rt_tmp = S("$userid");
        $cachename="$userid"."$now";
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
        //抽取航班号
       // $rt_num_count = $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['0']['RecordCount'];
       // for ($i=0;$i<$rt_num_count;$i++){
        //    $flight[$i]= $rt['FlightSearchResponse']['FlightRoutes']['DomesticFlightRoute']['FlightsList']['DomesticFlightData'][$i]['Flight'];
        //}
        //$flight= array_unique($flight);
        //$flight= array_values($flight);
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
        var_dump($flight_info);
       $this->ajaxReturn($flight_info, 'JSON' );
        //$this->getFlightInfosFromCtrip($cu, $data);
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
}