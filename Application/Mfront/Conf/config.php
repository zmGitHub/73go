<?php
/**
 * Created by PhpStorm.
 * User: davidlaw
 * Date: 2015/3/2
 * Time: 9:56
 */

return array (

    // '配置项'=>'配置值'

    "THINK_WECHAT" => array (
        'APP_ID' => 'wxe88f8959823e92a2', // 应用ID
        'APP_SECRET' => 'e69408e7a7237a285712bd9f0dba7770', // 应用密钥
        'ACCOUNT_BINDING_URL' => "http%3a%2f%2fweixin.73go.cn%2fMfront%2fWScheduleInterface%2freceiveAccountBound&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect", //绑定接口URL
        'COMMON_URL'=> "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=http://weixin.73go.cn/Mfront/#porm#&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect",

        //微信菜单
        'JSON_MENU' => '{
                  "button":[
                  {
                        "name":"手机预定",
                       "sub_button":[
                        {
                           "type":"click",
                           "name":"语音预定",
                           "key":"语音预定"
                        },
                        {
                           "type":"view",
                           "name":"轻松预定",
                           "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=http://weixin.73go.cn/Mfront/WScheduleInterface/receiveEasybooking&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                        },
                        {
                           "type":"view",
                           "name":"自助预定",
                           "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=http%3a%2f%2fweixin.73go.cn%2fMfront%2fWScheduleInterface%2freceiveHelpbooking&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                        }]
                   },
                   {
                       "name":"我",
                       "sub_button":[
                        {
                           "type":"view",
                           "name":"我的订单",
                           "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=http%3a%2f%2fweixin.73go.cn%2fMfront%2fWScheduleInterface%2fselectOrder&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                        },
                        {
                           "type":"view",
                           "name":"账号绑定",
                           "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=http%3a%2f%2fweixin.73go.cn%2fMfront%2fWScheduleInterface%2freceiveAccountBound&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                        }]
                   },
                   {
                       "name":"技术支持",
                       "sub_button":[
                        {
                           "type":"click",
                           "name":"文档下载",
                           "key":"文档下载"
                        },
                        {
                           "type":"click",
                           "name":"技术社区",
                           "key":"技术社区"
                        },
                        {
                           "type":"view",
                           "name":"挖金币",
                           "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe88f8959823e92a2&redirect_uri=120.24.242.116/DigCoin/index.jsp&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect"
                        }]
                   }]
             }'
          //微信菜单结束
    )

);