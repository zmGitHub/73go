<?php
/**
 * Created by PhpStorm.
 * User: steve
 * Date: 2015/3/24
 * Time: 10:40
 */
class OTA_FltSaveOrder
{
    public $open_api = '/Flight/DomesticFlight/OTA_FltSaveOrder.asmx';

    // 酒店信息查询条件：查询属性中至少有一条查询条件 @var $hotel_city_code $area_id $hotel_name
    public $ordertype = '';//ADU（成人），CHI（儿童）， BAB（婴儿）
    public $amount ='';//订单金额
    public $desc = '';
    public $dcity_id = '';
    public $acity_id = '';
    public $dport = '';
    public $aport = '';
    public $airlinecode = '';
    public $flight = '';
    public $class ='';
    public $subclass = '';
    public $dtime = '';
    public $atime = '';
    public $rate = '';
    public $price = '';
    public $tax= '';
    public $oilfee = '';
    public $nonrer ='';
    public $nonref = '';
    public $nonend = '';
    public $rernote = '';
    public $RefNote='';
    public $EndNote='';
    public $CraftType='';
    public $Quantity='';
    public $RefundFeeFormulaID= '';
    public $UpGrade = '';
    public $ProductType='';
    public $PassengerName ='';
    public $BirthDay = '';
    public $PassportTypeID= '';
    public $PassportNo='';
    public $ContactName = '';
    public $MobilePhone = '';
    public $ContactEMail = '';
    public $DeliveryType = '';
    public $SendTicketCityID = '';
    public $Receiver  = '';
    public $Province   = '';
    public $City   = '';
    public $Canton   = '';
    public $Address   = '';
    public $PostCode   = '';
   /* public $indicator = "true"; 		// 国内酒店：true/false 可预订/已激活; 海外酒店：true/false 过滤booking和agoda/不过滤
    public $position_type = ''; 		// 坐标类型,参见ListCode(PTC),501Mapbar 坐标,502Google 坐标
    public $provider = 'HotelStarRate'; // 评分者,HotelStarRate(酒店星级),CtripStarRate(携程星级),CtripRecommendRate(携程评分)
    public $rating = ''; 				// 评分分数或级别
    */
    public function __construct( $open_api, $args )
    {

        if( array_key_exists('ordertype',$args) && $args['ordertype'])
        {
            $this->ordertype = $args['ordertype'];
        }
        if( array_key_exists('amount',$args) && $args['amount'])
        {
            $this->amount = $args['amount'];
        }
        if( array_key_exists('desc',$args) && $args['desc'])
        {
            $this->desc = $args['desc'];
        }
        if( array_key_exists('dcity_id',$args) && $args['dcity_id'])
        {
            $this->dcity_id = $args['dcity_id'];
        }
        if( array_key_exists('acity_id',$args) && $args['acity_id'])
        {
            $this->acity_id = $args['acity_id'];
        }
        if( array_key_exists('dport',$args) && $args['dport'])
        {
            $this->dport = $args['dport'];
        }
        if( array_key_exists('aport',$args) && $args['aport'])
        {
            $this->aport = $args['aport'];
        }
        if( array_key_exists('airlinecode',$args) && $args['airlinecode'])
        {
            $this->airlinecode = $args['airlinecode'];
        }
        if( array_key_exists('flight',$args) && $args['flight'])
        {
            $this->flight = $args['flight'];
        }
        if( array_key_exists('class',$args) && $args['class'])
        {
            $this->class = $args['class'];
        }
        if( array_key_exists('subclass',$args) && $args['subclass'])
        {
            $this->subclass = $args['subclass'];
        }
        if( array_key_exists('dtime',$args) && $args['dtime'])
        {
            $this->dtime = $args['dtime'];
        }
        if( array_key_exists('atime',$args) && $args['atime'])
        {
            $this->atime = $args['atime'];
        }
        if( array_key_exists('rate',$args) && $args['rate'])
        {
            $this->rate = $args['rate'];
        }
        if( array_key_exists('price',$args) && $args['price'])
        {
            $this->price = $args['price'];
        }
        if( array_key_exists('tax',$args) && $args['tax'])
        {
            $this->tax = $args['tax'];
        }
        if( array_key_exists('oilfee',$args) && $args['oilfee'])
        {
            $this->oilfee = $args['oilfee'];
        }
        if( array_key_exists('nonrer',$args) && $args['nonrer'])
        {
            $this->nonrer = $args['nonrer'];
        }
        if( array_key_exists('nonref',$args) && $args['nonref'])
        {
            $this->nonref = $args['nonref'];
        }
        if( array_key_exists('nonend',$args) && $args['nonend'])
        {
            $this->nonend = $args['nonend'];
        }
        if( array_key_exists('rernote',$args) && $args['rernote'])
        {
            $this->rernote = $args['rernote'];
        }
        if( array_key_exists('RefNote',$args) && $args['RefNote'])
        {
            $this->RefNote = $args['RefNote'];
        }
        if( array_key_exists('EndNote',$args) && $args['EndNote'])
        {
            $this->EndNote = $args['EndNote'];
        }
        if( array_key_exists('CraftType',$args) && $args['CraftType'])
        {
            $this->CraftType = $args['CraftType'];
        }
        if( array_key_exists('Quantity',$args) && $args['Quantity'])
        {
            $this->Quantity = $args['Quantity'];
        }
        if( array_key_exists('RefundFeeFormulaID',$args) && $args['RefundFeeFormulaID'])
        {
            $this->RefundFeeFormulaID = $args['RefundFeeFormulaID'];
        }
        if( array_key_exists('UpGrade',$args) && $args['UpGrade'])
        {
            $this->UpGrade = $args['UpGrade'];
        }
        if( array_key_exists('ProductType',$args) && $args['ProductType'])
        {
            $this->ProductType = $args['ProductType'];
        }
        if( array_key_exists('PassengerName',$args) && $args['PassengerName'])
        {
            $this->PassengerName = $args['PassengerName'];
        }
        if( array_key_exists('BirthDay',$args) && $args['BirthDay'])
        {
            $this->BirthDay = $args['BirthDay'];
        }
        if( array_key_exists('PassportTypeID',$args) && $args['PassportTypeID'])
        {
            $this->PassportTypeID = $args['PassportTypeID'];
        }
        if( array_key_exists('PassportNo',$args) && $args['PassportNo'])
        {
            $this->PassportNo = $args['PassportNo'];
        }
        if( array_key_exists('ContactName',$args) && $args['ContactName'])
        {
            $this->ContactName = $args['ContactName'];
        }
        if( array_key_exists('MobilePhone',$args) && $args['MobilePhone'])
        {
            $this->MobilePhone = $args['MobilePhone'];
        }
        if( array_key_exists('ContactEMail',$args) && $args['ContactEMail'])
        {
            $this->ContactEMail = $args['ContactEMail'];
        }
        if( array_key_exists('DeliveryType',$args) && $args['DeliveryType'])
        {
            $this->DeliveryType = $args['DeliveryType'];
        }
        if( array_key_exists('SendTicketCityID',$args) && $args['SendTicketCityID'])
        {
            $this->SendTicketCityID = $args['SendTicketCityID'];
        }
        if( array_key_exists('Receiver',$args) && $args['Receiver'])
        {
            $this->Receiver = $args['Receiver'];
        }
        if( array_key_exists('Province',$args) && $args['Province'])
        {
            $this->Province = $args['Province'];
        }
        if( array_key_exists('City',$args) && $args['City'])
        {
            $this->City = $args['City'];
        }
        if( array_key_exists('Canton',$args) && $args['Canton'])
        {
            $this->Canton = $args['Canton'];
        }
        if( array_key_exists('Address',$args) && $args['Address'])
        {
            $this->Address = $args['Address'];
        }
        if( array_key_exists('PostCode',$args) && $args['PostCode'])
        {
            $this->PostCode = $args['PostCode'];
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
    public function order_generate( $uid, $sid, $stmp, $sign, $type )
    {
        $request_xml =
                    '<?xml version="1.0" encoding="utf-8"?>'
                    .'<Request>'
                    .'<Header AllianceID="'.$uid.'" SID="'.$sid.'" TimeStamp="'.$stmp.'" RequestType="'.$type.'" Signature="'.$sign.'" />'
                    //.'<Header AllianceID="'.$uid.'" SID="'.$sid.'" TimeStamp="'.$stmp.'" RequestType="'.$type.'" Signature="'.$sign.'" />'
                    .'<FltSaveOrderRequest>'
                    .'<UID>b3b103c8-e67a-4887-bd43-7cc897f57eb0</UID>'
                    .'<OrderType>%s</OrderType>'
                    .'<Amount>%s</Amount>'
                    .'<ProcessDesc>%s</ProcessDesc>'
                    .'<FlightInfoList>'
                    .'<FlightInfoRequest>'
                    .'<DepartCityID>%s</DepartCityID>'
                    .'<ArriveCityID>%s</ArriveCityID>'
                    .'<DPortCode>%s</DPortCode>'
                    .'<APortCode>%s</APortCode>'
                    .'<AirlineCode>%s</AirlineCode>'
                    .'<Flight>%s</Flight>'
                    .'<Class>%s</Class>'
                    .'<SubClass>%s</SubClass>'
                    .'<TakeOffTime>%s</TakeOffTime>'
                    .'<ArrivalTime>%s</ArrivalTime>'
                    .'<Rate>%s</Rate>'
                    .'<Price>%s</Price>'
                    .'<Tax>%s</Tax>'
                    .'<OilFee>%s</OilFee>'
                    .'<NonRer>%s</NonRer>'
                    .'<NonRef>%s</NonRef>'
                    .'<NonEnd>%s</NonEnd>'
                    .'<RerNote>%s</RerNote>'
                    .'<RefNote>%s</RefNote>'
                    .'<EndNote>%s</EndNote>'
                    .'<Remark></Remark>'
                    .'<NeedAppl>F</NeedAppl>'
                    .'<Recommend>2</Recommend>'
                    .'<Canpost>T</Canpost>'
                    .'<CraftType>%s</CraftType>'
                    .'<Quantity>%s</Quantity>'
                    .'<RefundFeeFormulaID>%s</RefundFeeFormulaID>'
                    .'<UpGrade>%s</UpGrade>'
                    .'<TicketType>1111</TicketType>'
                    .'<AllowCPType>1111</AllowCPType>'
                    .'<ProductType>%s</ProductType>'
                    .'<ProductSource>1</ProductSource>'
                    .'<InventoryType>Fav</InventoryType>'
                    .'<PriceType>NormalPrice</PriceType>'
                    .'<Onlyowncity>false</Onlyowncity>'
                    .'<CanSeparateSale />'
                    .'<RouteIndex>0</RouteIndex>'
                    .'</FlightInfoRequest>'
                    .'</FlightInfoList>'
                    .'<PassengerList>'
                    .'<PassengerRequest>'
                    .'<PassengerName>%s</PassengerName>'
                    .'<BirthDay>%s</BirthDay>'
                    .'<PassportTypeID>%s</PassportTypeID>'
                    .'<PassportNo>%s</PassportNo>'
                    .'<ContactTelephone />'
                    .'<Gender>M</Gender>'
                    .'<NationalityCode>1</NationalityCode>'
                    .'</PassengerRequest>'
                    .'</PassengerList>'
                    .'<ContactInfo>'
                    .'<ContactName>%s</ContactName>'
                    .'<ConfirmOption>TEL</ConfirmOption>'
                    .'<MobilePhone>%s</MobilePhone>'
                    .'<ContactTel />'
                    .'<ForeignMobile />'
                    .'<MobileCountryFix />'
                    .'<ContactEMail>%s</ContactEMail>'
                    .'<ContactFax />'
                    .'</ContactInfo>'
                    .'<DeliverInfo>'
                    .'<DeliveryType>%s</DeliveryType>'
                    .'<SendTicketCityID>0</SendTicketCityID>'
                    .'<OrderRemark>%s</OrderRemark>'
                    .'<PJS>'
                    .'<Receiver />'
                    .'<Province />'
                    .'<City />'
                    .'<Canton />'
                    .'<Address />'
                    .'<PostCode />'
                    .'</PJS>'
                    .'</DeliverInfo>'
                    .'<PayInfo>'
                    .'<CreditCardInfo>'
                    .'<CardInfoID>0</CardInfoID>'
                    .'<CreditCardType>11</CreditCardType>'
                    .'<CreditCardNumber>hiNI6GWod48+siR777rXyLgBgE0dF6f4</CreditCardNumber>'
                    .'<Validity>gSb+/Uj5DEE=</Validity>'
                    .'<CardBin>uffYuvmpsOg=</CardBin>'
                    .'<CardHolder>5/bqCaNz3yw=</CardHolder>'
                    .'<IdCardType>gBG1pcTHP+M=</IdCardType>'
                    .'<IdNumber>EDXkCmkgwpjSs25jdmMPk6hi0ZpuQ1mV</IdNumber>'
                    .'<CVV2No>0DXVwD+y96Y=</CVV2No>'
                    .'<AgreementCode></AgreementCode>'
                    .'<Eid>api</Eid>'
                    .'<Remark></Remark>'
                    .'<IsClient>true</IsClient>'
                    .'<CCardPayFee>0</CCardPayFee>'
                    .'<CCardPayFeeRate>0</CCardPayFeeRate>'
                    .'<Exponent>0</Exponent>'
                    .'<ExchangeRate></ExchangeRate>'
                    .'<FAmount></FAmount>'
                    .'</CreditCardInfo>'
                    .'</PayInfo>'
                    .'</FltSaveOrderRequest>'
                    .'</Request>';


        // 模板替换
         $ordertype = $this->ordertype;//ADU（成人），C
         $amount =$this->amount;//订单金额
         $desc = $this->desc;
         $dcity_id = $this->dcity_id;
         $acity_id = $this->acity_id;
         $dport = $this->dport;
         $aport =$this->aport;
         $airlinecode = $this->airlinecode;
         $flight = $this->flight;
         $class =$this->class;
         $subclass = $this->subclass;
         $dtime = $this->dtime;
         $atime = $this->atime;
         $rate = $this->rate;
         $price = $this->price;
         $tax= $this->tax;
         $oilfee = $this->oilfee;
         $nonrer =$this->nonrer;
         $nonref = $this->nonref;
         $nonend = $this->nonend;
         $rernote = $this->rernote;
         $RefNote=$this->RefNote;
         $EndNote=$this->EndNote;
         $CraftType=$this->CraftType;
         $Quantity=$this->Quantity;
         $RefundFeeFormulaID= $this->RefundFeeFormulaID;
         $UpGrade = $this->UpGrade;
         $ProductType=$this->ProductType;
         $PassengerName =$this->PassengerName;
         $BirthDay = $this->BirthDay;
         $PassportTypeID= $this->PassportTypeID;
         $PassportNo=$this->PassportNo;
         $ContactName = $this->ContactName;
         $MobilePhone = $this->MobilePhone;
         $ContactEMail = $this->ContactEMail;
         $DeliveryType = $this->DeliveryType;
         $SendTicketCityID = $this->SendTicketCityID;
         $Receiver  =$this->Receiver;
         $Province   = $this->Province;
         $City   = $this->City;
         $Canton   = $this->Canton;
         $Address   = $this->Address;
         $PostCode   = $this->PostCode;
        $request_xml = sprintf($request_xml,$ordertype,$amount,$desc,$dcity_id,$acity_id,$dport,$aport,$airlinecode,$flight,$class,$subclass,
                                            $dtime,$atime,$rate,$price,$tax,$oilfee,$nonrer,$nonref,$nonend,$rernote,$RefNote,$EndNote,$CraftType,$Quantity,$RefundFeeFormulaID,
                                            $UpGrade,$ProductType,$PassengerName,$BirthDay,$PassportTypeID,$PassportNo,$ContactName,$MobilePhone,$ContactEMail,$DeliveryType,$SendTicketCityID,
                                            $Receiver,$Province,$City,$Canton,$Address,$PostCode);

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
