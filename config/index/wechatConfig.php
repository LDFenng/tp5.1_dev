<?php
$wechat_config=[];
$condition[]=['admin_id','=',session('user_id')];
$condition[]=['is_enabled','=',1];
$wechat_list=db('wechat_info')->where($condition)->field(true)->cache(true,7200)->select();
if ($wechat_list){
    foreach ($wechat_list as $wechat_val){
        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        $wechat_config['wechat_option_'.$wechat_val['id']]=[
            'debug'         =>true,  //Debug 模式，bool 值：true/false；当值为 false 时，所有的日志都不会记录
            'app_id'        =>$wechat_val['app_id'],
            'secret'        =>$wechat_val['app_secret'],
            'token'         =>$wechat_val['token'],
            'aes_key'       =>$wechat_val['aes_key'], // EncodingAESKey，兼容与安全模式下请一定要填写！！！
            'response_type' =>'array',   //指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'log'           =>[
                'level'=>'debug', //level: 日志级别, 可选为： debug/info/notice/warning/error/critical/alert/emergency
                'permission'    => 0777,  //permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
                'file'          => 'runtime/log/wechat_'.$wechat_val['id'].'/'.date('Ymd').'wechat.log',
            ],
            'http'          =>[  //接口请求相关配置，超时时间等，具体可用参数请参考：
                'retries'       => 3,  //'runtime/log/wechat_'.$wechat_val['id'].'/easywechat.log',
                'retry_delay'   => 500,  //retry_delay: 重试延迟间隔（单位：ms），默认 500
                'timeout'       => 5.0,
                'base_uri'      => 'https://api.weixin.qq.com/',
            ],
            'oauth'         =>[
                'scopes'        => ['snsapi_userinfo'],
                'callback'      => '/examples/oauth_callback.php',
            ]
        ];
    }
}
return $wechat_config;