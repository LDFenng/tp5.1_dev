<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
use \think\facade\Route;
// action变量的值作为操作方法传入
//':action/blog/:id' => 'index/blog/:action'
// 变量传入index模块的控制器和操作方法
//':c/:a'=> 'index/:c/:a'
//Route::miss('blog/miss'); //不存在时执行
if (cache('?route_data')){
    $url_list=cache('route_data');
}
else{
    $url_list=db('background_menu')->where('is_enabled',1)->field('id,pid,action_name,route_url')->cache('route_data',14400)->select();
}
// if ($url_list){
//     //Route::rule(‘路由表达式’,‘路由地址’,‘请求类型’,‘路由参数（数组）’,‘变量规则（数组）’);
//     $route_list=[];
//     foreach ($url_list as $url_key=>$url_val){
//         $route=empty($url_val['route_url'])?$url_val['pid'].$url_key.'/[:ldf]':$url_val['route_url'].'/[:ldf]';
//        // $route_list[$route]='index/'.$url_val['action_name'];
//         if($url_val['action_name']=='Index/index'){
//             Route::rule($route,'index/'.$url_val['action_name']);
//         }
//         else{
//             Route::rule($route,'index/'.$url_val['action_name'],'GET|POST');
//         }
//     }  
// }
Route::rule('l','index/Login/login');
Route::rule('v','index/Login/loginVerify');
Route::rule('ali','index/Pay/ailNotify');


