<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\index\controller;
use think\Controller;
use think\db\Query;
class Index extends Base{
    public function index(){
        $mysql=db()->query("select VERSION()");
        $max_connections=db()->query("show variables like '%max_connections%'");  
        $system_info=[  
            'php_v'=>PHP_VERSION,  //php版本
            'zend_v'=>zend_version(),  //zend版本
            'mysql_v'=>$mysql[0]['VERSION()'],  //mysql版本
            'max_mysql_link'=>$max_connections[0]['Value'],
            'php_os'=>PHP_OS, //服务器系统
            'upload_max_filesize'=>get_cfg_var ("upload_max_filesize")?get_cfg_var ("upload_max_filesize"):"不允许上传附件",  //最大上传大小
            'max_time'=>get_cfg_var("max_execution_time")."秒 ",  //最大执行时间
            'max_memory'=>get_cfg_var("memory_limit")?get_cfg_var("memory_limit"):"无",
            'broswer'=>get_broswer(),   //获取浏览器信息
            'get_os'=>get_os(),   //获取客户端系统版本
            'is_curl'=>function_exists('curl_init')?'支持':'不支持'  //是否支持curl
        ];
        $this->assign('system_info',$system_info);
        return $this->fetch();
    }

}
