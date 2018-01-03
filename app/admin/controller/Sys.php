<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Db;
use think\Cache;
use think\Validate;
use City\city;
class Sys extends Base {   
    public function MenuList(){
        $id_str=input('str_id','pid');
        $pid=input('pid',0);
        $menu_list = db('background_menu')->where('pid',$pid)->order('sort asc')->select();
        if($menu_list){
           foreach ($menu_list as $menu_key=>$menu_val){
                $str = '';
                for ($i=1;$i<=$menu_val['levle'];$i++){
                    $str .='─';
                }
                $menu_list[$menu_key]['title'] = '├'.$str.$menu_val['title'];
            }
        }
        $this->assign('menu_list',$menu_list);
        $this->assign('pid',$id_str);        //print_r($pid);die;
        if(input('type')=='son'){
            return $this->fetch('ajaxMenuList');
        }else{
            return $this->fetch();
        }
    }
    
    public function menuOrder(){
        return $this->dataOrder('background_menu');
    }
    
    public function editMenu(){
        $pid=input('pid',0);
        $id=input('id');
        $menu_info = db('background_menu')->where('id',$id)->find();
        $this->assign('menu_info',$menu_info);
        $pid = db('background_menu')->where('id',$pid)->value('pid');
        $menu_list = db('background_menu')->where('pid',$pid)->select();
        $this->assign('menu_list',$menu_list);
        return $this->fetch();
    }
    
    public function saveMenu(){
        if (request()->isAjax()){
            config('default_ajax_return','json');
            $data = request()->param();
            $data['is_display'] = input('is_display',0);
            $data['is_enabled'] = input('is_enabled',0);
            $rule = [
                ['title','require','填写菜单名称'],
                ['action_name','require','填写方法']
            ];
            $validate = new Validate($rule);
            $result = $validate->check($data);
            if ($result){
                //判断是否存在控制器和方法
                if ($data['pid']==0){
                    //顶级菜单
                    $data['levle']=1;
                }
                else{
                    //下级菜单
                    $levle = db('background_menu')->where('id',$data['pid'])->value('levle');
                    $data['levle'] = $levle+1;
                }
//                 switch ($data['levle']){
//                     case 1:
//                         $name=input('action_name');
//                         if(strpos($name,'/') === true){
//                             if (!has_controller(APP_PATH . 'admin'. DS .'controller',$name)) {
//                                 return ['code'=>0,'msg'=>'不存在 '.$name.' 的控制器'];
//                             }
//                         }
//                         else{
//                             $arr=explode('/',$name);
//                             if (isset($arr[1])){
//                                 $rst=has_action(APP_PATH . 'admin'. DS .'controller',$arr[0],$arr[1]);
//                                 if($rst==1){
//                                     return [
//                                         'code'=>0,
//                                         'msg'=>'不存在 '.$arr[0].' 控制器'
//                                     ];
//                                 }elseif($rst==2){
//                                     return [
//                                         'code'=>0,
//                                         'msg'=>'控制器'.$arr[0].'不存在方法'
//                                     ];
//                                 }
//                             }
//                             else{
//                                 if (!has_controller(APP_PATH . 'admin'. DS .'controller',$arr[0])) {
//                                     return ['code'=>0,'msg'=>'不存在 '.$name.' 的控制器'];
//                                 }
//                             }
//                         }
//                         break;  
//                     default:
//                         //是否存在控制器/方法
//                         $arr=explode('/',input('action_name'));
//                         if(count($arr)<=2 && isset($arr[1])){
//                             $rst=has_action(APP_PATH . 'admin'. DS .'controller',$arr[0],$arr[1]);
//                             if($rst==1){
//                                 return [
//                                     'code'=>0,
//                                     'msg'=>'不存在 '.$arr[0].' 控制器'
//                                 ];
//                             }elseif($rst==2){
//                                 return [
//                                     'code'=>0,
//                                     'msg'=>'控制器'.$arr[0].'不存在方法'
//                                 ];
//                             }
//                         }
// //                         else{
// //                             return [
// //                                 'code'=>0,
// //                                 'msg'=>'提交名称不规范'
// //                             ];
// //                         }
//                         break;
                        
//                 }
                if (!empty($data['id'])){
                    $data['update_time'] = time();
                    $save_result = db('background_menu')->where('id',$data['id'])->update($data);
                }
                else{
                    $data['create_time'] = time();
                    $data['update_time'] = time();
                    $save_result = db('background_menu')->insertGetId($data);
                }
                if ($save_result){
                    Cache::clear(); 
                    return [
                        'code'=>1,
                        'msg'=>'保存成功',
                        'url'=>url('admin/Sys/MenuList')
                    ];
                }
                else{
                    return [
                        'code'=>0,
                        'msg'=>'非法操作'
                    ];
                }
            }
            else{
                return [
                    'code'=>0,
                    'msg'=>$validate->getError()
                ];
            }
        }
    }
    
    public function changeMenuStatus(){
        if (request()->isAjax()){
            $type = request()->param('type');
            if ($type=='a'){
                $result = $this->changeStatus('background_menu');
            }
            elseif ($type=='b'){
                $result = $this->changeStatus('background_menu','is_display',2);
            }
            if ($result){
                Cache::clear();
                return $result;
            }
        }
    }
    
    public function delMenu(){
        if (request()->isAjax()){
            config('default_ajax_return','json');
            $ids = input('ids/a');
            //print_r($ids);die;
            if (!empty($ids)){
                foreach ($ids as $id_val){
                    $del_result = $this->delSonMenu($id_val); 
                };
                db('background_menu')->delete($ids);
                if ($del_result){
                    Cache::clear('menus_list_'.$this->uid);
                    return [
                        'code'=>1
                    ];
                }
                else {
                    return [
                        'code'=>0,
                        'msg'=>'非法操作'
                    ];
                }
            }
            else {
                return [
                    'code'=>0,
                    'msg'=>'请选要删除的数据'
                ];
            }
        }
    }
    
    /**
     * 权限组
     * @return mixed|string
     */
    public function authGroupList(){
        $page=input('page_num',config('list_rows'));
        $group_list = Db::view('auth_group')->where('auth_group.admin_id',$this->uid)
        ->view('admin_info',['user_name'=>'admin_name'],'admin_info.id=auth_group.admin_id')
        ->paginate($page,false,['query'=>get_query()]);
        $show=show_page($group_list->render());
        $this->assign('page',$show);
        $this->assign('group_list',$group_list);
        return $this->fetch();
    }
    
    public function changeGroupStatus(){
        return $this->changeStatus('auth_group'); 
    }
    
    public function editAuthGroup(){
        $id=input('id');
        $group_info = $this->getDataInfo('auth_group', $id);
        $this->assign('group_info',$group_info);
        return $this->fetch();
    }
    
    public function saveAuthGroup(){
        if (request()->isAjax()){
            $data = request()->param();
            $data['is_enabled'] = input('is_enabled',0);
            $rule = [
                ['title','require','填写组别名称'],
            ];
            return $this->saveData('auth_group', $data,$rule, url('admin/Sys/authGroupList',['page'=>input('page')]));         
        }
    }
    
    /**
     * 用户组授权
     */
    public function authList(){
        $id=input('id');
        if ($this->uid ==10000){
            $groups_condition = [
                'is_enabled'=>1
            ];
        }
        else{
            $groups_condition = [
                'admin_id'=>$this->uid,
                'is_enabled'=>1
            ];
        }
        $auth_groups = db('auth_group')->where($groups_condition)->select();
        $group_ids=[];
        $ids = [];
        foreach ($auth_groups as $auth_val) {
            if (!empty($auth_val['menu_ids'])){
                $ids = array_merge($ids, explode(',', trim($auth_val['menu_ids'], ',')));
                if ($auth_val['id']==$id){
                    $group_ids = explode(',', trim($auth_val['menu_ids'], ','));
                }
            }
        }
        if (!empty($ids))$ids = array_unique($ids); //去掉重复的权限ID
        if ($this->uid ==10000){
            $menu_condition = [
                'is_enabled'=>1
            ];
        }
        else{
            //获取菜单所有权限
            $auth_ids=db('admin_auth')->where('admin_id',$this->uid)->column('group_ids');
            $group_arr=[];
            foreach ($auth_ids as $group_str){
                $group_arr=array_merge($group_arr, explode(',', trim($group_str, ',')));
            }
            if (!empty($group_arr))$group_arr = array_unique($group_arr); //去掉重复的权限ID
            //由组权限获取菜单
            $menus_arr=db('auth_group')->where(['id'=>['in',$group_arr]])->column('menu_ids');
            $menus_ids=[];
            foreach ($menus_arr as $menus_id){
                $menus_ids=array_merge($menus_ids, explode(',', trim($menus_id, ',')));
            }
            if (!empty($menus_ids))$menus_ids = array_unique($menus_ids); //去掉重复的权限ID
            $menu_condition = [
                'is_enabled'=>1,
                'id'=>['in',$menus_ids]
            ];
        }
        $auth_list = db('background_menu')->where($menu_condition)->select();
        
        $this->assign('group_ids',$group_ids);
        $this->assign('auth_list',list_to_tree($auth_list));
        return $this->fetch();
    }
    
    public function saveAuth(){
        if (request()->isAjax()){
            $data = request()->param();
            $data['menu_ids'] = $data['id_str'];
            unset($data['id_str']);
            return $this->saveData('auth_group', $data,null,url('admin/Sys/authGroupList',['page'=>input('page')]));
        }
    }
    
    /**
     * 管理员
     */
    public function adminList(){
        $admin_condition = [
            'is_delete'=>0,
        ];
        if ($this->uid!=10000){
            $admin_condition['pid'] = $this->uid;
        }
        $page=input('page_num',config('paginate.list_rows'));
        $admin_data = db('admin_info')->where($admin_condition)->paginate($page,false,['query'=>get_query()]);
        $admin_info = $admin_data->toArray();
        $admin_list = $admin_info['data'];
        if ($admin_list){
            foreach ($admin_list as $admin_key=>$admin_val){
                if ($admin_val['pid']!=0){
                    $admin_list[$admin_key]['pid_name'] = db('admin_info')->where('id',$admin_val['pid'])->value('user_name');
                }  
                if (!empty($admin_val['effective_time'])){
                    if (strtotime($admin_val['effective_time'].' 23:59:59')<time()){
                        $admin_list[$admin_key]['effective_time']="<font color='red'>已过期<font>";
                    }
                }
            }
        }
        $show=show_page($admin_data->render());
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);
        return $this->fetch();
    }
    
    Public function changeAdminStatus(){
        return $this->changeStatus('admin_info');
    }
    
    public function gaveAuthGroup(){
        $admin_id=request()->param('admin_id');
        if (request()->isAjax()){
            $group_list = db('auth_group')->where(['admin_id'=>$this->uid,'is_enabled'=>1])->select();
            $admin_auth_info = db('admin_auth')->where('admin_id',$admin_id)->find();
            $this->assign('group_list',$group_list);
            $this->assign('auth_ids',explode(',', $admin_auth_info['group_ids']));
            return $this->fetch();
        }
    }
    
    public function saveGroupAuth(){
        if (request()->isAjax()){
            $group_ids = input('group_id/a',null);
            if ($group_ids){
                $group_ids = implode(',', $group_ids);
            }
            $save_data =[
                'group_ids'=>$group_ids,
                'admin_id'=>input('admin_id')
            ];
            $admin_auth_info = db('admin_auth')->where('admin_id',input('admin_id'))->find();
            if ($admin_auth_info){
                $save_data['id'] = $admin_auth_info['id'];
            }
            return $this->saveData('admin_auth', $save_data,null,url('admin/Sys/adminList',['page'=>input('page')]),false);
        }
    }
    
    public function delGroupAuth(){
        return $this->reallyDelete('admin_auth');
    }
    
    public function editAdmin(){
        $id=input('id');
        $admin_info = $this->getDataInfo('admin_info', $id,[],false);
        $this->assign('admin_info',$admin_info);
        return $this->fetch();
    }
    
    public function saveAdmin(){
        if (request()->isAjax()){
            $data = request()->param();
            if (!empty($data['id'])){
                $rule = [
                    ['password',['length'=>'6,16','regex'=>'/^[a-zA-Z](?=.*\d)[a-zA-Z\d]/','confirm'=>'password_confirm'],'密码长度必须在6位到16位|首位必须以字母开头包含数字组成密码|密码不一致'],
                    ['password_confirm',['length'=>'6,16','regex'=>'/^[a-zA-Z](?=.*\d)[a-zA-Z\d]/'],'密码长度必须在6位到16位|首位必须以字母开头包含数字组成密码']
                ];
                if (del_all_trim($data['user_name'])!=$data['old_user_name']){
                    $rule[]=['user_name','require|max:60|unique:admin_info','用户名必须|名称最多不能超过60个字符|用户名已被注册'];
                }
                if (del_all_trim($data['user_phone'])!=$data['old_user_phone']){
                    $rule[]=['user_phone',['require','length:11','regex'=>'/^(13[0-9]{9})|(15[0-9]{9})|(18[0-9]{9})|(17[0-9]{9})/','unique'=>'admin_info'],'请填写手机号码|手机号码目前仅支持11位|电话号码格式错误|此手机号码已注册'];
                }
                if (del_all_trim($data['user_email'])!=$data['old_user_email']){
                    $rule[]=['user_email','email|unique:admin_info','请填写邮箱|请输入正确的邮箱格式|邮箱已被注册'];
                }
            }
            else{
                $rule = [
                    ['user_name','require|max:60|unique:admin_info','用户名必须|名称最多不能超过60个字符|用户名已被注册'],
                    ['user_email','email|unique:admin_info','请填写邮箱|请输入正确的邮箱格式|邮箱已被注册'],
                    ['user_phone',['require','length:11','regex'=>'/^(13[0-9]{9})|(15[0-9]{9})|(18[0-9]{9})|(17[0-9]{9})/','unique'=>'admin_info'],'请填写手机号码|手机号码目前仅支持11位|电话号码格式错误|此手机号码已注册'],
                    ['password',['require','length'=>'6,16','regex'=>'/^[a-zA-Z](?=.*\d)[a-zA-Z\d]/','confirm'=>'password_confirm'],'新用户密码不可为空|密码长度必须在6位到16位|首位必须以字母开头包含数字组成密码|密码不一致'],
                    ['password_confirm',['require','length'=>'6,16','regex'=>'/^[a-zA-Z](?=.*\d)[a-zA-Z\d]/'],'新用户密码不可为空|密码长度必须在6位到16位|首位必须以字母开头包含数字组成密码']
                ];
            }
            $validate = new Validate($rule);
            $result = $validate->check($data);
            if ($result){
                $save_data = [
                    'id'=>$data['id'],
                    'user_name'=>$data['user_name'],
                    'user_email'=>$data['user_email'],
                    'user_phone'=>$data['user_phone'],
                    'head_img'=>$data['head_img'],
                    'effective_time'=>$data['effective_time'],
                    'user_type'=>'SUB',
                    'create_time'=>time(),
                    'update_time'=>time(),
                    'pid'=>$this->uid,
                    'last_login_time'=>time()
                ];
                session('avatar_src',$data['head_img']);
                if (!empty($data['password']))$save_data['password']=password_hash($data['password'], PASSWORD_DEFAULT);
                //$save_data['pid']=($this->chief_id==$this->uid)?'0':$this->chief_id;
                del_old_file($data['head_img'], $data['old_admin_avatar']);
                return $this->saveData('admin_info', $save_data,null,new_url('admin/Sys/adminList'),false);
            }
            else{
                config('default_ajax_return','json');
                return [
                    'code'=>0,
                    'msg'=>$validate->getError()
                ];
            }
        }
    }
    
    public function delAdmin(){
        if (request()->isAjax()){
            $ids = input('ids/a');
            if (in_array($this->uid, $ids)){
                return [
                    'code'=>0,
                    'msg'=>'含有本操作账号不可删除'
                ];
            }
            return $this->softDelete('admin_info',$ids);
        }
    }
    
    //删除父级菜单对旗下子菜单进行删除
    private function delSonMenu($pid){      
        $son_info = db('background_menu')->where('pid',$pid)->find();
        if ($son_info){
            $son_pid = $son_info['id'];
            db('background_menu')->where('pid',$pid)->delete();
            $this->delSonMenu($son_pid);
        }
        else{
            return true;
        }
    }
     
    /**
     * 微信公众号配置列表，可多个存在
     * @return mixed|string
     */
    public function wechatList(){
        $wechat_condition = [
            'admin_id'=>$this->uid
        ];
        $data = request()->param();
        if (!empty($data['key_words'])){
            $wechat_condition['wechat_appid|app_secret|title'] = ['like',"%{$data['key_words']}%"];
        }
        if (!empty($data['wechat_type'])){
            $wechat_condition['wechat_type'] = $data['wechat_type'];
        }
        $page=input('page_num',config('paginate.list_rows'));
        $wechat_list = db('wechat_info')->where($wechat_condition)->order('sort desc')
        ->paginate($page,false,['query'=>get_query()]);
        $show=show_page($wechat_list->render());
        $this->assign('wechat_list',$wechat_list);
        $this->assign('page',$show);
        return $this->fetch();
    }
    
    public function wechatOrder(){
        return $this->dataOrder('wechat_info');
    }
    
    public function changeWechatStatus(){
        return $this->changeStatus('wechat_info'); 
    }
    
    public function editWechat(){
        $id=input('id');
        $wechat_info = $this->getDataInfo('wechat_info', $id);
        $this->assign('wechat_info',$wechat_info);
        return $this->fetch();
    }
    
    public function saveWechat(){
        if (request()->isAjax()){
            config('default_ajax_return','json');
            $rule = [
                ['title','require','请填写微信名称'],
                ['app_id','require|alphaDash','appid必填|不可填写中文'],
                ['app_secret','require|alphaDash','app_secret必填|不可填写中文'],
                ['wechat_type','require','请选择微信公众号'],
                ['token','require|alphaDash','请填写token'],
                ['cert_path','file|fileExt:.pem|fileSize:1048576','请上传正确的证书|仅支持后缀为pem的证书|证书大小不得超过1MB'],
                ['key_path','file|fileExt:.pem|fileSize:1048576','请上传正确的密钥|仅支持后缀为pem的密钥|密钥大小不得超过1MB']
            ];
            $data = request()->param();
            $validate=new \think\Validate($rule);
            if ($validate->check($data)){
                unset($data['cert_path']);
                unset($data['key_path']);
                $cert_file=request()->file('cert_path');
                if ($cert_file){
                    $save_path=ROOT_PATH .'data'. DS . 'upload'.DS.$this->uid.DS.'cert'.DS.'wechat_'.$data['app_id'];
                    $cert_info = $cert_file->move($save_path,'weixin_app_cert');
                    if (!$cert_info){
                        return ['code'=>0,'msg'=>$cert_info->getError()];  //绝对路径
                    }
                    $data['cert_path']=$cert_info->getRealPath();
                }
                $key_file=request()->file('key_path');
                if ($key_file){
                    $key_info = $key_file->move($save_path,'weixin_app_key');
                    if (!$key_info){
                        return ['code'=>0,'msg'=>$key_info->getError()];
                    }
                    $data['key_path']=$key_info->getRealPath();   //绝对路径
                }
                return $this->saveData('wechat_info', $data,null,new_url('admin/Sys/wechatList'));
            }
            else{
                return ['code'=>0,'msg'=>$validate->getError()];
            } 
        }
    }
    
    public function delWechat(){
        $app_ids=db('wechat_info')->where(['ids'=>['in',input('ids/a')]])->column('app_id');
        foreach ($app_ids as $app_id){
            $del_path=ROOT_PATH .'data'. DS . 'upload'.DS.$this->uid.DS.'cert'.DS.'wechat_'.$app_id;
            @del_file($del_path);
        }
        return $this->reallyDelete('wechat_info');
    }
    
    /**
     * 网站配置
     * @return mixed|string
     */
    public function siteConfig(){        
        $site_info = db('site_info')->where('admin_id',$this->uid)->find();
//         if((empty($map_lat) || empty($map_lng)) && !empty($sys['site_co_name'])){
//             $strUrl="http://api.map.baidu.com/place/v2/search?query=".$sys['site_co_name']."&region=全国&city_limit=false&output=json&ak=".config('baidumap_ak');//自己去申请ak
//             //接受json数据
//             $jsonStr = file_get_contents($strUrl);
//             //进行json字符串编码
//             $map = json_decode($jsonStr,TRUE);
//             if(!empty($map['results']) && !empty($map['results'][0]['location'])){
//                 $map_lat=$map['results'][0]['location']['lat'];
//                 $map_lng=$map['results'][0]['location']['lng'];
//             }
//         }
        //print_r(config('geetest.private_key'));die;
        $this->assign('site_info',$site_info);
        return $this->fetch();
    }
    
    /**
     * 保存网站
     */
    public function saveSite(){
        if (request()->isAjax()){
            $data = request()->param();
            $rule = [
                ['name','require','请填写网站名称'],
                ['host','url','请填写正确的网址'],
                ['captcha_id','requireIf:geetest_on,1','请填写极验captcha_id'],
                ['private_key','requireIf:geetest_on,1','请填写极验private_key'],
                ['fontSize','requireIf:is_verify,1|between:10,50','请填写验证码大小|请输入10-50范围的数值'],
                ['length','requireIf:is_verify,1|between:2,8','请填写验证码长度|请输入2-8范围的数值'],
                ['email','email','请填写正确的邮箱'],
                ['effective_time','number','请填写登陆有效时间|请填写数字'],
                ['tel','unique:site_info,admin_id='.$this->uid,'电话号码已被使用']
            ];//print_r($data);die;
            $geetest_on=input('geetest_on',0,'intval')?true:false;
            $captcha_id=input('captcha_id','');
            $private_key=input('private_key','');
            if(empty($captcha_id) || empty($private_key)) $geetest_on=false;
            config_setbykey('geetest',['geetest_on'=>$geetest_on,'captcha_id'=>$captcha_id,'private_key'=>$private_key]);
            if($geetest_on){
                //自动开启路由
                config_setbykey('url_route_on',true);
            }
            //验证码
            config_setbykey('is_verify', input('is_verify',0,'intval')?true:false);
            config_setbykey('login_config',['effective_time'=>input('effective_time',30)]);
            $useZh=input('useZh',false,'intval')?true:false;
            $useNoise=input('useNoise',true,'intval')?true:false;
            $useCurve=input('useCurve',false,'intval')?true:false;
            config_setbykey('verify', ['fontSize'=>input('fontSize',20,'intval'),'useZh'=>$useZh,
                'expire'=>input('expire',90,'intval'),'useNoise'=>$useNoise,
                'imageH'=>42,'imageW'=>250,'length'=>input('length',4,'intval'),
                'useCurve'=>$useCurve]);
            del_old_file($data['logo'],$data['old_logo']);
            del_old_file($data['wechat_qcode'],$data['old_wechat_qcode']);
            return $this->saveData('site_info',$data,$rule, url('admin/Sys/siteConfig'));
        }
    }
    
    /**
     * 图片上传配置
     */
    public function picConfig(){
        $pic_config=db('pic_config')->where('admin_id',$this->uid)->find();
        if ($pic_config){
            $pic_config['water_data']=json_decode($pic_config['water_data'],true);
            $pic_config['thumb_data']=json_decode($pic_config['thumb_data'],true);
        }
       // print_r($pic_config);die;
        $this->assign('pic_config',$pic_config);
        return $this->fetch();
    }
    
    public function savePicConfig(){
        $data=request()->param();
        $rule=[
            ['water_local','requireIf:is_water,1|between:1,9','选择水印位置|水印位置非法操作！'],
            ['water_img',"requireIf:img_or_text,'img'",'图片水印请上传图片'],
            ['img_or_text',"require",'请选择水印类型'],
            ['text',"requireIf:img_or_text,'text'",'文本水印请填写文字'],
            ['font',"requireIf:img_or_text,'text'",'信文本水印请选择文字类型'],
            ['size','number','文字大小请填写数字'],
            ['angle','number','倾斜角度请填写数字'],
            ['alpha','number','图片透明度请填写数字'],
            ['max_width','number','最大宽度请填写数字'],
            ['max_width','number','最大高度请填写数字'],
            ['thumb_type','between:1,5','略缩类型图非法操作']
        ];
        $validate = new \think\Validate($rule);
        $result = $validate->check($data);
        if ($result){
            if ($data['img_or_text']=='img'){
                $domain=request()->domain().get_root_dir();
                $path_dir=str_replace($domain,'',$data['water_img']);
                $water_data=[
                    'local'=>empty($data['water_local'])?1:$data['water_local'],
                    'src'=>$path_dir,
                    'img_url'=>$data['water_img'],
                    'alpha'=>empty($data['alpha'])?100:$data['alpha']
                ];
            }
            elseif ($data['img_or_text']=='text'){
                $water_data=[
                    'local'=>empty($data['water_local'])?1:$data['water_local'],
                    'text'=>$data['text'],
                    'font_code'=>$data['font'],
                    'font'=>ROOT_PATH . 'public' . DS . 'pc-ui/font/'.$data['font'].'.ttf',
                    'size'=>empty($data['size'])?20:$data['size'],
                    'color'=>empty($data['color'])?'#000000':$data['color'],
                    'angle'=>empty($data['angle'])?0:$data['angle']
                ];
            }
            else{
                config('default_ajax_return','json');
                return ['code'=>0,'msg'=>'水印类型非法操作'];
            }
            $thumb_data=[
                'max_width'=>empty($data['max_width'])?150:$data['max_width'],
                'max_height'=>empty($data['max_height'])?150:$data['max_height'],
                'thumb_type'=>empty($data['thumb_type'])?2:$data['thumb_type']
            ];
            $save_data=[
                'id'=>$data['id'],
                'is_water'=>empty($data['is_water'])?0:1,
                'water_type'=>$data['img_or_text']=='img'?1:2,
                'water_data'=>json_encode($water_data),
                'thumb_data'=>json_encode($thumb_data),
            ];
            return $this->saveData('pic_config', $save_data);
        }
        else{
            config('default_ajax_return','json');
            return ['code'=>0,'msg'=>$validate->getError()];
        }
    }
    
    //数据库备份
    public function database(){
        $type=request()->param('type');
        if(empty($type)){
            $type='export';
        }
        $title='';
        $list=array();
        switch ($type) {
            /* 数据还原 */
            case 'import':
                //列出备份文件列表
                $path=config('db_path');
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }
                $path = realpath($path);
                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($path,  $flag);
    
                $list = array();
                foreach ($glob as $name => $file) {
                    if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
    
                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];
    
                        if(isset($list["{$date} {$time}"])){
                            $info = $list["{$date} {$time}"];
                            $info['part'] = max($info['part'], $part);
                            $info['size'] = $info['size'] + $file->getSize();
                        } else {
                            $info['part'] = $part;
                            $info['size'] = $file->getSize();
                        }
                        $extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                        $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                        $info['time']     = strtotime("{$date} {$time}");
                        $list["{$date} {$time}"] = $info;
                    }
                }
                $title = '数据还原';
                break;
    
                /* 数据备份 */
            case 'export':
                $list  = Db::query('SHOW TABLE STATUS FROM '.config('database.database'));
                $list  = array_map('array_change_key_case', $list);
                //过滤非本项目前缀的表
                foreach($list as $k=>$v){
                    if(stripos($v['name'],strtolower(config('database.prefix')))!==0){
                        unset($list[$k]);
                    }
                }
                $title = '数据备份';
                break;
    
            default:
                $this->error('参数错误！');
        }
    
        //渲染模板
        $this->assign('meta_title', $title);
        $this->assign('data_list', $list);
        return $this->fetch($type);
    }
    //数据库还原
    public function import(){
        $path=config('db_path');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $path = realpath($path);
        $flag = \FilesystemIterator::KEY_AS_FILENAME;
        $glob = new \FilesystemIterator($path,$flag);
    
        $list = array();
        foreach ($glob as $name => $file) {
            if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
    
                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[6];
    
                if(isset($list["{$date} {$time}"])){
                    $info = $list["{$date} {$time}"];
                    $info['part'] = max($info['part'], $part);
                    $info['size'] = $info['size'] + $file->getSize();
                } else {
                    $info['part'] = $part;
                    $info['size'] = $file->getSize();
                }
                $extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                $info['time']     = strtotime("{$date} {$time}");
    
                $list["{$date} {$time}"] = $info;
            }
        }
        //渲染模板
        $this->assign('data_list', $list);
        return $this->fetch();
    }
    /**
     * 优化表
     * @param  String $tables 表名
     * @author rainfer <81818832@qq.com>
     */
    public function optimize(){
        config('default_ajax_return','json');
        $tables=request()->param('tables');
        if($tables) {
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = Db::query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                    return ['code'=>1,'msg'=>'数据表优化完成！'];
                } else {
                    return ['code'=>0,'msg'=>'数据表优化出错请重试！'];
                }
            } else {
                $list = Db::query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                    return ['code'=>1,'msg'=>"数据表'{$tables}'优化完成！"];
                } else {
                    return ['code'=>0,'msg'=>"数据表'{$tables}'优化出错请重试！"];
                }
            }
        } else {
            return ['code'=>0,'msg'=>"请指定要优化的表！"];
        }
    }
    /**
     * 修复表
     * @param  String $tables 表名
     */
    public function repair($tables = null){
        config('default_ajax_return','json');
        $tables=request()->param('tables');
        if($tables) {
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = Db::query("REPAIR TABLE `{$tables}`");
                if($list){
                    return ['code'=>1,'msg'=>'数据表修复完成！'];
                } else {
                    return ['code'=>0,'msg'=>'数据表修复出错请重试！'];
                }
            } else {
                $list = Db::query("REPAIR TABLE `{$tables}`");
                if($list){
                    return ['code'=>1,'msg'=>"数据表'{$tables}'修复完成！"];
                } else {
                    return ['code'=>0,'msg'=>"数据表'{$tables}'修复出错请重试！"];
                }
            }
        } else {
            return ['code'=>0,'msg'=>"请指定要修复的表！"];
        }
    }
    /**
     * 备份单表
     * @param  String $table 不含前缀表名
     */
    public function exportsql(){
        config('default_ajax_return','json');
        $tables=request()->param('tables');
        if($table){
            if(stripos($table,config('database.prefix'))==0){
                //含前缀的表,去除表前缀
                $table=str_replace(config('database.prefix'),"",$table);
            }
            if (!db_is_valid_table_name($table)) {
                return ['code'=>0,'msg'=>"不存在表" . ' ' . $table];
            }
            force_download_content(date('Ymd') . '_' . config('database.prefix') . $table . '.sql', db_get_insert_sqls($table));
        }else{
            return ['code'=>0,'msg'=>'未指定需备份的表'];
        }
    }
    /**
     * 删除备份文件
     * @param  Integer $time 备份时间
     * @author rainfer <81818832@qq.com>
     */
    public function del($time = 0){
        if($time){
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(config('db_path')) . DS . $name;
            array_map("unlink", glob($path));
            if(count(glob($path))){
                $this->error('备份文件删除失败，请检查权限！',url('admin/Sys/import'));
            } else {
                $this->success('备份文件删除成功！',url('admin/Sys/import'));
            }
        } else {
            $this->error('参数错误！',url('admin/Sys/import'));
        }
    }
    public function restore(){
        //读取备份配置
        $time=input('time',0);
        $part=input('part');
        $start=input('start');
        $config = array(
            'path'     => realpath(config('db_path')) . DS,
            'part'     => config('db_part'),
            'compress' => config('db_compress'),
            'level'    => config('db_level'),
        );
        if(is_numeric($time) && is_null($part) && is_null($start)){ //初始化
            //获取备份文件信息
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(config('db_path')) . DS . $name;
            $files = glob($path);
            $list  = array();
            foreach($files as $name){
                $basename = basename($name);
                $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
                $list[$match[6]] = array($match[6], $name, $gz);
            }
            ksort($list);
            //检测文件正确性
            $last = end($list);
            if(count($list) === $last[0]){
                session('backup_list', $list); //缓存备份列表
                $this->restore(0,1,0);
            } else {
                $this->error('备份文件可能已经损坏，请检查！');
            }
        } elseif(is_numeric($part) && is_numeric($start)) {
            $list  = session('backup_list');
            $db = new \Database\Database($list[$part],$config);
            $start = $db->import($start);
            if(false === $start){
                $this->error('还原数据出错！');
            } elseif(0 === $start) { //下一卷
                if(isset($list[++$part])){
                    //$data = array('part' => $part, 'start' => 0);
                    $this->restore(0,$part,0);
                } else {
                    session('backup_list', null);
                    $this->success('还原完成！',url('admin/Sys/Sys/import'));
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if($start[1]){
                    $this->restore(0,$part, $start[0]);
                } else {
                    $data['gz'] = 1;
                    $this->restore(0,$part, $start[0]);
                }
            }
        } else {
            $this->error('参数错误！');
        }
    }
    
    public function export(){
        $id=input('id');
        config('default_ajax_return','json');
        $tables=request()->param('tables');
        $start=request()->param('start');
        if(request()->isPost() && !empty($tables) && is_array($tables)){ //初始化
            //读取备份配置
            $config = [
                'path'     => realpath(config('db_path')) . DS,
                'part'     => config('db_part'),
                'compress' => config('db_compress'),
                'level'    => config('db_level'),
            ];
            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
            	
            if(is_file($lock)){
                return [
                    'code'=>0,
                    'msg'=>'检测到有一个备份任务正在执行，请稍后再试！'
                ];
            } else {
                //创建锁文件
                file_put_contents($lock, time());
            }
            //检查备份目录是否可写
            if (!is_writeable($config['path'])){
                return [
                    'code'=>0,
                    'msg'=>'检测到有一个备份任务正在执行，请稍后再试2！'
                ];
            }
            session('backup_config', $config);
            //生成备份文件信息
            $file = array(
                'name' => date('Ymd-His', time()),
                'part' => 1,
            );
            session('backup_file', $file);
            //缓存要备份的表
            session('backup_tables', $tables);
            //创建备份文件
            $Database = new \Database\Database($file, $config);
            if(false !== $Database->create()){
                $tab = ['id' => 0, 'start' => 0];
                return ['code'=>1,'tab' => $tab,'tables' => $tables,'msg'=>'初始化成功！'];
            } else {
                return ['code'=>0,'msg'=>'初始化失败，备份文件创建失败！'];
            }
        } elseif (request()->isGet() && is_numeric($id) && is_numeric($start)) { //备份数据
             
            $tables = session('backup_tables');
            //备份指定表
            $Database = new \Database\Database(session('backup_file'), session('backup_config'));
            $start  = $Database->backup($tables[$id], $start);
            	
            if(false === $start){ //出错
                return ['code'=>0,'msg'=>'备份出错！'];
            } elseif (0 === $start) { //下一表
                if(isset($tables[++$id])){
                    $tab = ['id' =>$id, 'start' => 0];
                    return ['code'=>1,'tab' => $tab,'tables' => $tables,'msg'=>'备份完成！'];
                } else { //备份完成，清空缓存
                    unlink(session('backup_config.path') . 'backup.lock');
                    session('backup_tables', null);
                    session('backup_file', null);
                    session('backup_config', null);
                    return ['code'=>1,'msg'=>'备份完成！'];
                }
            } else {
                $tab  = ['id' => $id, 'start' => $start[0]];
                $rate = floor(100 * ($start[0] / $start[1]));
                return ['code'=>1,'tab' => $tab,'msg'=>"正在备份...({$rate}%)"];
            }
        } else { //出错
            return ['code'=>1,'msg'=>'参数错误！'];
        }
    }
    
    /**
     * 路由美化
     */
    public function routeList(){
        $page=input('page_num',config('paginate.list_rows'));
        $menu_condition=[
            'module'=>1
        ];
        $key_words=input('key_words');
        if (!empty($key_words))$menu_condition['title']=['like',"%{$key_words}%"];
        $route_list=db('background_menu')->where($menu_condition)->order('action_name asc')->paginate($page,false,['query'=>get_query()]);
        $this->assign('route_list',$route_list);
        $this->assign('page',show_page($route_list->render()));
        return $this->fetch();
    }
    
    public function saveRoute(){
        config('default_ajax_return','json');
        $data=request()->param('data');
        $route_data=json_decode($data,true);
        if ($route_data){
            $save_data=[];
            $rule=[
                ['route_url','alphaDash','路由只能是字母和数字、下划线_及破折号-']
            ];
            $validate=new \think\Validate($rule);
            foreach ($route_data as $id=>$route){
                if (!empty($route)){
                    if (!$validate->check(['route_url'=>$route])){
                        return ['code'=>0,',msg'=>$validate->getError()];
                    }
                    $save_data=[
                        'route_url'=>$route,
                        'update_time'=>time()
                    ];
                    $save_result=db('background_menu')->where('id',$id)->setField($save_data);
                }
            }
            return ['code'=>1,'msg'=>'操作成功！请退出重新登陆'];
        }
        else{
            return ['code'=>0,'msg'=>'未进行任何美化'];
        }
    }
    
    //清除缓存
    public function cleanCache(){
        config('default_ajax_return','json');
        Cache::clear();
        //删除临时图片
        $img_config = config('img_config');
        $get_path = str_replace("{sid}",$this->uid,$img_config['get_path']);
        $file_src=$get_path.'/temp/';
        del_file($file_src);
        //删除缓存文件
        del_file(ROOT_PATH."runtime/temp/");
        return ['code'=>1,'msg'=>'清理缓存成功'];
    }
    
    /**
     * 获取城市数据 
     * @param number $pid 0为中国
     */
    public function getCityData(){
        $pid=request()->param('pid');
        config('default_ajax_return','json');
        $city_list= city::getCityData([$pid]);
        if ($city_list){
            return ['code'=>1,'data_list'=>$city_list];
        }
    }
    
    /**
     * 城市列表
     * @return mixed|string
     */
    public function cityList(){
        $pid=input('pid',1);
        $data=request()->param();
        $city_condition=[
            'pid'=>$pid
        ];
        if (!empty($data['key_words']))$city_condition['name|name_en']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        if ($pid!=1)$page=100;
        $city_list=db('city')->where($city_condition)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
        $show=show_page($city_list->render());
        $this->assign('page',$show);
        $this->assign('city_list',$city_list);
        if (input('type')=='sub'){
            $this->assign('selector',$data['selector']);
            return $this->fetch('ajaxCityList');
        }
        return $this->fetch();
    }
    
    /**
     * 编辑添加城市
     * @return mixed|string
     */
    public function editCity(){
        $city_info=$this->getDataInfo('city', input('id'),[],false);
        $city_list=db('city')->order('sort asc')->field('name,id,pid,levle')->select();
        $this->assign('city_info',$city_info);
        $this->assign('city_list',$city_list);
        return $this->fetch();
    }
    
    /**
     * 保存城市
     */
    public function saveCity(){
        if (request()->isAjax()){
            $rule=[
                ['name','require','城市不可为空']
            ];
            $data=request()->param();
            if ($data['pid']!=0){
                $levle=db('city')->where('id',$data['pid'])->value('levle');
                $data['levle']=$levle+1;
            }
            else{
                $data['levle']=1;
            }
            return $this->saveData('city', $data,$rule,new_url('admin/Sys/cityList'));
        }
    }
    
    /**
     * 删除城市
     */
    public function delCity(){
        $ids=input('ids/a');
        $this->_changeCityPid($ids,$enabled=null,true);
        return $this->reallyDelete('city');
    }
    
    /**
     * 城市排序
     */
    public function cityOrder(){
        return $this->dataOrder('city');
    }
    
    /**
     * 改变城市状态
     */
    public function changeCityStatus(){
        $id = input('x');
        $is_enabled=db('city')->where('id',$id)->value('is_enabled');
        $enabled=$is_enabled?0:1;
        $this->_changeCityPid([$id],$enabled);
        return $this->changeStatus('city','is_enabled',1,new_url('admin/Sys/cityList'));        
    }
    
    private function _changeCityPid($pids,$enabled,$is_delete=false){
        $city_ids=db('city')->where(['pid'=>['in',$pids]])->column('id');
        if ($city_ids && !$is_delete){
            db('city')->where(['id'=>['in',$city_ids]])->setField(['is_enabled'=>$enabled,'update_time'=>time()]);
            return $this->_changeCityPid($city_ids,$enabled);
        }
        elseif ($is_delete){
            db('city')->where(['id'=>['in',$city_ids]])->delete();
            return $this->_changeCityPid($city_ids,$enabled=null,true);
        }
        else{
            return true;
        }
    }
    
    public function emailList(){
        $data=request()->param();
        $email_condition=[
            'admin_id'=>$this->uid
        ];
        if (!empty($data['key_words']))$email_condition['name']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $email_list=db('email_config')->where($email_condition)->order('sort asc')->field(true)->paginate($page,false,['query'=>get_query()]);
        $this->assign('page',show_page($email_list->render()));
        $this->assign('email_list',$email_list);
        return $this->fetch();
    }
    
    public function editEmail(){
        $email_info=$this->getDataInfo('email_config', input('id'));
        $this->assign('email_info',$email_info);
        return $this->fetch();
    }
    
    public function saveEmail(){
        if (request()->isAjax()){
            $rule=[
                ['name','require','请填写发件人名字'],
                ['email','require|email','请填写邮件地址|请填写正确的邮件地址'],
                ['password','require','请填写邮件登陆密码'],
                ['mail_port','require','请填写邮件服务端口'],
                ['reply_mail','email','请填写正确的邮件地址'],
                ['host','require','请填写邮件服务商地址']
            ];
            $data=request()->param();
            if (empty($data['reply_mail']))$data['reply_mail']=$data['email'];
            return $this->saveData('email_config', $data);
        }
    }
    
    public function delEmail(){
        return $this->reallyDelete('email_config');
    }
    
    public function changeEmailStatus(){
        return $this->changeStatus('email_config');
    }
    
    public function orderEmail(){
        return $this->dataOrder('email_config');
    }
    
    public function sendEmail(){
        $data=request()->param();
        $mail=new \PHPMailer\Mailer($this->uid);
        $to_user=[$data['name']=>$data['email']];
        $send_result=$mail->sendMail($to_user, $data['title'], $data['content']);
        if ($send_result){
            return ['code'=>1,'msg'=>'已发送'];
        }
    }
}



