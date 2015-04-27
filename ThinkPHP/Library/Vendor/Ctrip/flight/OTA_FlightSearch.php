<?php
/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2015/3/24
 * Time: 10:40
 */
class OTA_FlightSearch
{
    public $open_api = '/Flight/DomesticFlight/OTA_FlightSearch.asmx';

    // 酒店信息查询条件：查询属性中至少有一条查询条件 @var $hotel_city_code $area_id $hotel_name
    public $searchtype ='';
    public $depart_city = ''; 	// 城市ID
    public $arrive_city = ''; 	// 行政区ID
    public $depart_date = ''; 	// 酒店名称(模糊查询)
    public $return_date = ''; 	// 酒店名称(模糊查询)
    public $AirlineDibitCode= '';
    public $DepartPort='';
    public $ArrivePort='';
    public $EarliestDepartTime='';
    public $LatestDepartTime='';
    public $SendTicketCity='';

   /* public $indicator = "true"; 		// 国内酒店：true/false 可预订/已激活; 海外酒店：true/false 过滤booking和agoda/不过滤
    public $position_type = ''; 		// 坐标类型,参见ListCode(PTC),501Mapbar 坐标,502Google 坐标
    public $provider = 'HotelStarRate'; // 评分者,HotelStarRate(酒店星级),CtripStarRate(携程星级),CtripRecommendRate(携程评分)
    public $rating = ''; 				// 评分分数或级别
    */
    public function __construct( $open_api, $args )
    {

        if( array_key_exists('searchtype',$args) && $args['searchtype'])
        {
            $this->searchtype = $args['searchtype'];
        }
        if( array_key_exists('depart_city',$args) && $args['depart_city'])
        {
            $this->depart_city = $args['depart_city'];
        }
        if( array_key_exists('arrive_city',$args) && $args['arrive_city'])
        {
            $this->arrive_city = $args['arrive_city'];
        }
        if( array_key_exists('depart_date',$args) && $args['depart_date'])
        {
            $this->depart_date = $args['depart_date'];
        }
        if( array_key_exists('return_date',$args) && $args['return_date'])
        {
            $this->return_date = $args['return_date'];
        }
        if( !$this->searchtype && !$this->depart_city && !$this->arrive_city && $this->depart_date  )
        {
            trigger_error('航程类型、起飞城市、到达城市和起飞时间均不能为空',E_USER_ERROR);
        }
        if( array_key_exists('AirlineDibitCode',$args) && $args['AirlineDibitCode'] )
        {
            $this->AirlineDibitCode = $args['AirlineDibitCode'];
        }
        if( array_key_exists('DepartPort',$args) && $args['DepartPort'])
        {
            $this->DepartPort = $args['DepartPort'];
        }
        if( array_key_exists('ArrivePort',$args) && $args['ArrivePort'])
        {
            $this->ArrivePort = $args['ArrivePort'];
        }
        if( array_key_exists('EarliestDepartTime',$args) && $args['EarliestDepartTime'])
        {
            $this->EarliestDepartTime = $args['EarliestDepartTime'];
        }
        if( array_key_exists('LatestDepartTime',$args) && $args['LatestDepartTime'])
        {
            $this->LatestDepartTime = $args['LatestDepartTime'];
        }
        if( array_key_exists('SendTicketCity',$args) && $args['SendTicketCity'])
        {
            $this->SendTicketCity = $args['SendTicketCity'];
        }


        $this->open_api = $open_api.$this->open_api; // TODO:检测open api，如果不合法则覆盖重写
    }

    /**
     * 构造请求xml字符串
     * @param int $uid
     * @param int $sid
     * @param string $stmp
     * @param string $sign
     * @param stirng $type
     */
    public function single_trip_request_xml( $uid, $sid, $stmp, $sign, $type )
    {
        $request_xml =
                    '<?xml version="1.0" encoding="utf-8"?>'
                    .'<Request>'
                    .'<Header AllianceID="'.$uid.'" SID="'.$sid.'" TimeStamp="'.$stmp.'" RequestType="'.$type.'" Signature="'.$sign.'" />'
                    //.'<Header AllianceID="'.$uid.'" SID="'.$sid.'" TimeStamp="'.$stmp.'" RequestType="'.$type.'" Signature="'.$sign.'" />'
                    .'<RequestBody xmlns:ns="http://www.opentravel.org/OTA/2003/05" '
                    .'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
                    .'xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
                    .'<FlightSearchRequest>'
                    .'<SearchType>%s</SearchType>'
                    .'<Routes>'
                    .'<FlightRoute>'
                    .'<DepartCity>%s</DepartCity>'
                    .'<ArriveCity>%s</ArriveCity>'
                    .'<DepartDate>%s</DepartDate>'
                    .'<AirlineDibitCode>%s</AirlineDibitCode>'
                    .'<DepartPort>%s</DepartPort>'
                    .'<ArrivePort>%s</ArrivePort>'
                    .'<EarliestDepartTime>0001-01-01T00:00:00</EarliestDepartTime>'
                    .'<LatestDepartTime>0001-01-01T00:00:00</LatestDepartTime>'
                    .'</FlightRoute>'
                    .'</Routes>'
                    .'<SendTicketCity>%s</SendTicketCity>'
                    .'<IsSimpleResponse>false</IsSimpleResponse>'
                    .'<IsLowestPrice>false</IsLowestPrice>'
                    .'<PriceTypeOptions>'
                    .'<string>NormalPrice</string>'
                    .'</PriceTypeOptions>'
                    .'<ProductTypeOptions>Normal</ProductTypeOptions>'
                    .'<Classgrade>F</Classgrade>'
                    .'<OrderBy>Price</OrderBy>'
                    .'<Direction>ASC</Direction>'
                    .'</FlightSearchRequest>'
                    .'</RequestBody>'
                    .'</Request>';


        // 模板替换
        $searchtype = $this->searchtype;
        $depart_city = $this->depart_city; 	// 城市ID
        $arrive_city =  $this->arrive_city; 	// 行政区ID
        $depart_date =  $this->depart_date; 	// 酒店名称(模糊查询)
        $return_date =  $this->return_date; 	// 酒店名称(模糊查询)
        $AirlineDibitCode= $this->AirlineDibitCode;
        $DepartPort= $this->DepartPort;
        $ArrivePort= $this->ArrivePort;
//        $EarliestDepartTime= $this->EarliestDepartTime;
//        $LatestDepartTime= $this->LatestDepartTime;
        $SendTicketCity= $this->SendTicketCity;


        $request_xml = sprintf($request_xml,$searchtype,$depart_city,$arrive_city,$depart_date,$AirlineDibitCode,$DepartPort,$ArrivePort,$arrive_city,$depart_city,$return_date,$AirlineDibitCode,$DepartPort,$ArrivePort,$SendTicketCity);

        // 需要将此处的xml嵌入到外层xml中，故需要将其转义
        $request_xml = str_replace("<",@"&lt;",$request_xml);
        $request_xml = str_replace(">",@"&gt;",$request_xml);
//echo $request_xml;
      //  exit;
        return $request_xml;
    }

    public function double_trip_request_xml( $uid, $sid, $stmp, $sign, $type )
    {
        $request_xml =
            '<?xml version="1.0" encoding="utf-8"?>'
            .'<Request>'
            .'<Header AllianceID="'.$uid.'" SID="'.$sid.'" TimeStamp="'.$stmp.'" RequestType="'.$type.'" Signature="'.$sign.'" />'
            //.'<Header AllianceID="'.$uid.'" SID="'.$sid.'" TimeStamp="'.$stmp.'" RequestType="'.$type.'" Signature="'.$sign.'" />'
            .'<RequestBody xmlns:ns="http://www.opentravel.org/OTA/2003/05" '
            .'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            .'xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
            .'<FlightSearchRequest>'
            .'<SearchType>%s</SearchType>'
            .'<Routes>'
            .'<FlightRoute>'
            .'<DepartCity>%s</DepartCity>'
            .'<ArriveCity>%s</ArriveCity>'
            .'<DepartDate>%s</DepartDate>'
            .'<AirlineDibitCode>%s</AirlineDibitCode>'
            .'<DepartPort>%s</DepartPort>'
            .'<ArrivePort>%s</ArrivePort>'
            .'<EarliestDepartTime>0001-01-01T00:00:00</EarliestDepartTime>'
            .'<LatestDepartTime>0001-01-01T00:00:00</LatestDepartTime>'
            .'</FlightRoute>'
            .'<FlightRoute>'
            .'<DepartCity>%s</DepartCity>'
            .'<ArriveCity>%s</ArriveCity>'
            .'<DepartDate>%s</DepartDate>'
            .'<AirlineDibitCode>%s</AirlineDibitCode>'
            .'<DepartPort>%s</DepartPort>'
            .'<ArrivePort>%s</ArrivePort>'
            .'<EarliestDepartTime>0001-01-01T00:00:00</EarliestDepartTime>'
            .'<LatestDepartTime>0001-01-01T00:00:00</LatestDepartTime>'
            .'</FlightRoute>'
            .'</Routes>'
            .'<SendTicketCity>%s</SendTicketCity>'
            .'<IsSimpleResponse>false</IsSimpleResponse>'
            .'<IsLowestPrice>false</IsLowestPrice>'
            .'<PriceTypeOptions>'
            .'<string>NormalPrice</string>'
            .'</PriceTypeOptions>'
            .'<ProductTypeOptions>Normal</ProductTypeOptions>'
            .'<Classgrade>F</Classgrade>'
            .'<OrderBy>Price</OrderBy>'
            .'<Direction>ASC</Direction>'
            .'</FlightSearchRequest>'
            .'</RequestBody>'
            .'</Request>';


        // 模板替换
        $searchtype = $this->searchtype;
        $depart_city = $this->depart_city; 	// 城市ID
        $arrive_city =  $this->arrive_city; 	// 行政区ID
        $depart_date =  $this->depart_date; 	// 酒店名称(模糊查询)
        $return_date =  $this->return_date; 	// 酒店名称(模糊查询)
        $AirlineDibitCode= $this->AirlineDibitCode;
        $DepartPort= $this->DepartPort;
        $ArrivePort= $this->ArrivePort;
//        $EarliestDepartTime= $this->EarliestDepartTime;
//        $LatestDepartTime= $this->LatestDepartTime;
        $SendTicketCity= $this->SendTicketCity;


        $request_xml = sprintf($request_xml,$searchtype,$depart_city,$arrive_city,$depart_date,$AirlineDibitCode,$DepartPort,$ArrivePort,$arrive_city,$depart_city,$return_date,$AirlineDibitCode,$DepartPort,$ArrivePort,$SendTicketCity);

        // 需要将此处的xml嵌入到外层xml中，故需要将其转义
        $request_xml = str_replace("<",@"&lt;",$request_xml);
        $request_xml = str_replace(">",@"&gt;",$request_xml);
//echo $request_xml;
        //  exit;
        return $request_xml;
    }

    public function respond_xml( $string )
    {
        // 将内层xmll中转义的符号恢复
        $string = str_replace("&lt;","<",$string);
        $string = str_replace("&gt;",">",$string);

        return simplexml_load_string($string);
    }
}
