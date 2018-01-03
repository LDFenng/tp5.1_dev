<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 *@param 无限树形分离函数（非递归方式）
 *@param array $list 分类二位数组
 *@param string $id 分类id
 *@param string $pid 分类父级id
 *@param string $child 分类子字段
 *@return array
 *@author 麦当苗儿 <zuojiazi@vip.qq.com>
 **/
function list_to_tree($list, $pk='id', $pid = 'pid', $child = 'child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

function default_img(){
    
    return '..';
}

/**
 * 去除字符串所有空格、换行等
 * @param 字符串 $str
 * @return mixed
 */
function del_all_trim($str,$replace_str=1){
    $search = [" ","　","\n","\r","\t"];
    $replace = [
        1=>["","","","",""],
        2=>[',',',',',',',',',']
    ];
    return str_replace($search, $replace[$replace_str], $str);
}

/**
 * 获取客户端浏览器信息 添加win10 edge浏览器判断
 * @param  null
 * @author  Jea杨
 * @return string
 */
function get_broswer(){
    $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
        preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1];  //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
        preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
        $exp[0] = "傲游";
        $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "MSIE") > 0) {
        preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
        preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    } elseif(stripos($sys, "Edge") > 0) {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
        preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){
        preg_match("/rv:([\d\.]+)/", $sys, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    }else {
        $exp[0] = "未知浏览器";
        $exp[1] = "";
    }
    return $exp[0].'('.$exp[1].')';
}

/**
 * 获取客户端操作系统信息包括win10
 * @param  null
 * @author  Jea杨
 * @return string
 */
function get_os(){
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;
    if (preg_match('/win/i', $agent) && strpos($agent, '95')){
        $os = 'Windows 95';
    }
    else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')){
        $os = 'Windows ME';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)){
        $os = 'Windows 98';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)){
        $os = 'Windows Vista';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)){
        $os = 'Windows 7';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)){
        $os = 'Windows 8';
    }
    else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)){
        $os = 'Windows 10';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)){
        $os = 'Windows XP';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)){
        $os = 'Windows 2000';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)){
        $os = 'Windows NT';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)){
        $os = 'Windows 32';
    }
    else if (preg_match('/linux/i', $agent)){
        $os = 'Linux';
    }
    else if (preg_match('/unix/i', $agent)){
        $os = 'Unix';
    }
    else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)){
        $os = 'SunOS';
    }
    else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)){
        $os = 'IBM OS/2';
    }
    else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)){
        $os = 'Macintosh';
    }
    else if (preg_match('/PowerPC/i', $agent)){
        $os = 'PowerPC';
    }
    else if (preg_match('/AIX/i', $agent)){
        $os = 'AIX';
    }
    else if (preg_match('/HPUX/i', $agent)){
        $os = 'HPUX';
    }
    else if (preg_match('/NetBSD/i', $agent)){
        $os = 'NetBSD';
    }
    else if (preg_match('/BSD/i', $agent)){
        $os = 'BSD';
    }
    else if (preg_match('/OSF1/i', $agent)){
        $os = 'OSF1';
    }
    else if (preg_match('/IRIX/i', $agent)){
        $os = 'IRIX';
    }
    else if (preg_match('/FreeBSD/i', $agent)){
        $os = 'FreeBSD';
    }
    else if (preg_match('/teleport/i', $agent)){
        $os = 'teleport';
    }
    else if (preg_match('/flashget/i', $agent)){
        $os = 'flashget';
    }
    else if (preg_match('/webzip/i', $agent)){
        $os = 'webzip';
    }
    else if (preg_match('/offline/i', $agent)){
        $os = 'offline';
    }
    else{
        $os = '未知操作系统';
    }
    return $os;
}

/**
 * 获取当前request参数数组,去除值为空
 * @return array
 */
function get_query(){
    $param=request()->except(['s']);
    $rst=[];
    foreach($param as $k=>$v){
        if(!empty($v)){
            $rst[$k]=$v;
        }
    }
    return $rst;
}

/**
 * 取代url函数；自带分页参数
 * @param 原有url参数 $url
 * @param url参数 array $param
 * @return url
 */
function new_url($url,$param=[]){
    $param_data=array_merge(['id'=>request()->param('id',null),'page'=>request()->param('page',null),'page_num'=>request()->param('page_num',10)],$param);
    return url($url,$param_data);
}