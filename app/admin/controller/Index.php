<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Controller;
use think\Db;
use PHPMailer\Mailer;

// use EasyWeChat\Foundation\Application as wechatApi;
// use EasyWeChat\Message\Material;
class index extends Base
{
    public function index(){
        
        $mail=new mailer($this->uid);
        //$mail->sendMail($to_user, $title, $content);
//         $wechat_info=db('wechat_info')->where('id',2)->find();
//         $options=[
//             'app_id'  => $wechat_info['app_id'],         // AppID
//             'secret'  => $wechat_info['secret'],     // AppSecret
//             'token'   => $wechat_info['token'],          // Token
//             'aes_key' => $wechat_info['aes_key'],      // EncodingAESKey，安全模式下请一定要填写！！！
//         ];
//         $wechat_api = new wechatApi($options);
//         $path="data/upload/10000/img/20170729/6846b18ce8c3a861c6c80394575a6fe9.jpg";
//         $return_result = $wechat_api->material->uploadImage($path);
       
        $slide_condition=[
            'admin_id'=>$this->uid,
            'is_enabled'=>1,
            'scene'=>1
        ];
        $slide_list=db('slide')->where($slide_condition)->order('sort asc')->field(true)->select();
        if ($slide_list){
            foreach ($slide_list as $slide_key=>$slide_val){
                if (!empty($slide_val['animate_data'])){
                    $slide_list[$slide_key]['animate_data']=json_decode($slide_val['animate_data'],true);
                }
            }
        }
		$this->assign('slide_list',$slide_list);
		//渲染模板
        return $this->fetch();
    }
}
