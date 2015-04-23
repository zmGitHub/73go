<?php

namespace Home\Controller;

use System\LoginInfo;
use Think\Controller;

class HotelController extends Controller {



    /**
     * 加载酒店页面 listBox
     * 钟明
     */
    public function hotel() {
        layout ( "home" );
        $this->theme ( 'default' )->display ( "hotel" );
    }

    /**
     * 显示酒店的数据列表
     * 钟明
     */
    public function showData(){
        C ( 'LAYOUT_ON', FALSE );
        $this->theme ( 'default' )->display ( "HotelList" );
    }

    /**
     * 显示酒店的详情
     * 钟明
     */

    public function hotelDetail(){
        C ( 'LAYOUT_ON', FALSE );
        $this->theme ( 'default' )->display ( "hotelDetail" );
    }

    /**
     * 支付订单
     * 钟明
     */

    public function payOrder(){
        C ( 'LAYOUT_ON', FALSE );
        $this->theme ( 'default' )->display ( "pay" );
    }
}