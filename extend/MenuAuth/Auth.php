<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>　
// +----------------------------------------------------------------------
namespace MenuAuth;
use think\facade\Db;
use think\facade\Cache;
/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and') 
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 * 
 * 4，支持规则表达式。
 *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */


class Auth{
    //默认配置
    protected $_config = [
        'auth_on'           => true,                     // 认证开关
        'auth_type'         => 1,                        // 认证方式，1为实时认证；2为登录认证。
        'auth_group'        => 'auth_group',             // 用户组数据表名
        'admin_auth'        => 'admin_auth',             // 用户-用户组关系表
        'background_menu'   => 'background_menu',        // 菜单权限规则表
        'auth_user'         => 'admin_info'              // 用户信息表
    ];
    public function __construct() {
        if (config('auth_config')) {
            //可设置配置项 auth_config, 此配置项为数组。
            $this->_config = array_merge($this->_config, config('auth_config'));
        }
    }
    /**
      * 检查权限
      * @param string|array $url_name  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
      * @param   int  $uid         认证用户的id
      * @param int $type        执行check的模式
      * @param string $mode        执行check的模式
      * @param string $relation    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
      * @return boolean           通过验证返回true;失败返回false
     */
    public function check($url_name, $uid,$mode='url', $relation='or') {       
        if (!$this->_config['auth_on'])return true;
        $type=$this->_config['auth_type'];
        $auth_list = $this->getAuthList($uid,$type); //获取用户需要验证的所有有效规则列表
        if (is_string($url_name)) {
            $url_name = strtolower($url_name);  //转化小写
            if (strpos($url_name, ',') !== false) {  //判断是否是含有多个url名称
                $name_info = explode(',', $url_name);  //存在时分割为数组
            } else {
                $name_info = [$url_name]; //否则强制性转化为数组
            }
        }
        $action_list = []; //保存验证通过的规则名
        if ($mode=='url') {
            $REQUEST = unserialize(strtolower(serialize($_REQUEST))); //序列化反序列化
        }
        foreach ($auth_list as $auth_val) {
            $query = preg_replace('/^.+\?/U','',$auth_val['action_name']);
            if ($mode=='url' && $query!=$auth_val['action_name']) {
                parse_str($query,$param); //解析规则中的url参数
                $intersect = array_intersect_assoc($REQUEST,$param); //返回相同参数               
                //$auth = preg_replace('/\?.*$/U','',$auth_val);
                if (in_array(strtolower($auth_val['action_name']),$name_info)) {  //如果节点相符且url参数满足 暂不比较参数  && $intersect==$param 
                    $action_list[] = $auth_val['action_name'];
                }
            }else if (in_array(strtolower($auth_val['action_name']) ,$name_info)){
                $action_list[] = $auth_val['action_name'];
            }
        }
        if ($relation == 'or' && !empty($action_list)) {
            return true;
        } 
        $diff = array_diff($name_info, $action_list);
        if ($relation == 'and' && empty($diff)) {
            return true;
        }
        return false;
    }
    /**
     * 根据用户id获取用户组,返回值为数组 用户组ID
     * @param  int  $uid    用户id
     * @return array  用户所属的用户权限组 
     */
    public function getAuthGroups($uid) {
        //Session::clear();
        if (!Cache::has('admin_auth_'.$uid)){
            $admin_auth = db($this->_config['admin_auth'])->where('admin_id',$uid)->cache('admin_auth_'.$uid,7200)->find();
        }
        else{
            $admin_auth = cache('admin_auth_'.$uid);
        }
        return $admin_auth;
    }
    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type 
     */
    protected function getAuthList($uid,$type) {
        static $_authList = []; //保存用户验证通过的权限列表
        if (isset($_authList[$uid])) {
            return $_authList[$uid];
        }
        if($this->_config['auth_type']==2 && Cache::has('menu_list_'.$uid)){
            return cache('menu_list_'.$uid); 
        }
        //读取用户所属用户组
        $admin_auth = $this->getAuthGroups($uid);
        $group_ids = []; //保存用户所属用户组设置的所有权限组ID
        $group_ids = explode(',', trim($admin_auth['group_ids'], ','));
        //根据权限组ID获取所有权限ID
        if (Cache::has('auth_group_'.$uid)){
            $group_list = cache('auth_group_'.$uid);
        }
        else{
            $group_condition = [
                //'admin_id'=>$uid,
                ['id','in',$group_ids],
                ['is_enabled','=',1]
            ];
            $group_list = db($this->_config['auth_group'])->where($group_condition)->cache('auth_group_'.$uid,7200)->select();
        }
        $ids = []; //保存用户所属用户组设置的所有权限规则id
        foreach ($group_list as $group_val) {
            $ids = array_merge($ids, explode(',', trim($group_val['menu_ids'], ',')));
        }
        $ids = array_unique($ids); //去掉重复的权限ID
        if (empty($ids)) {
            $_authList[$uid] = [];
            return [];
        }
        $menu_condition = [
            ['id','in', $ids],
            ['is_enabled','=',1]
        ];
        //循环规则，判断结果。
        $auth_list = [];
        //读取用户组所有权限规则
        if (Cache::has('menu_list_'.$uid)){
            $auth_list = cache('menu_list_'.$uid);
        }
        else{
            $auth_list = db($this->_config['background_menu'])->where($menu_condition)->order('sort asc')->cache('menu_list_'.$uid,7200)->field(true)->select();
//             foreach ($menu_list as $menu_key=>$menu_val) {
//                 //只要存在就记录
//                 $menu_list[$menu_key]['action_name'] = strtolower($menu_val['action_name']);
//                 $auth_list[] = $menu_list[$menu_key];
//             }
        }
        $_authList[$uid] = $auth_list;
        //if($this->_config['auth_type']==2){
            
        //}
        return $auth_list;
    }
    
    /**
     * 获取用户对应的菜单
     */
    public function getMenuData($uid){
        return $this->getAuthList($uid, $this->_config['auth_type']);
    }
    
    /**
     * 获得用户资料,根据自己的情况读取数据库
     */
    protected function getUserInfo($uid) {
        static $userinfo=[];
        if (Cache::has($userinfo[$uid])){
            return cache($userinfo[$uid]);
        }
        static $userinfo=[];
        if(!isset($userinfo[$uid])){
             $userinfo[$uid] = db($this->_config['auth_user'])->where('id',$uid)->cache($userinfo[$uid],7200)->find();
        }
        return $userinfo[$uid];
    }
}