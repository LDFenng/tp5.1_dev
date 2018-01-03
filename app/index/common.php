<?php
// +----------------------------------------------------------------------
// | LDF [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
/**
 * 替换系统分页
 * @param $show
 * @return mixed
 */
function show_page($show,$page_class='ajax-page'){
    return preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a class='{$page_class}' data-page='$1'>$2</a>",$show);
}