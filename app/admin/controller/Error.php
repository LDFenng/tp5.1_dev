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
class Error{   
    public function _empty($name){
        config('default_ajax_return','json');
        $controller_name=request()->controller();
        if (!has_controller(APP_PATH . 'admin'. DS .'controller',$controller_name)) {
            return ['code'=>0,'msg'=>'不存在 '.$controller_name.' 的控制器'];
        }  
    }
}
