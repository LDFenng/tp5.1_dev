<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\widget;
use think\Controller;
use think\Request;
use think\Db;
use MenuAuth\Auth;
class NavBar extends Controller{
    
    protected $web_info;
    protected function _initialize(){
        $this->web_info = db('site_info')->where('admin_id',session('user_id'))->cache(14400)->field(true)->find();
    }
    public function leftMenu(){
        // 		//获取有权限的菜单tree
        $controll_name = request()->controller();
        $action = request()->action();
        $menu_id = request()->param('md');
        $auth=new Auth();
        $menu_data=$auth->getMenuData(session('user_id'));
        //获取选中的菜单（有权限时先帅选一遍再操作）
        $menu_info = db('background_menu')->where('id',$menu_id)->field('pid,levle')->find();
        $action_name = strtoupper(del_all_trim($controll_name.'/'.$action));
        $menu_list = [];
        if ($menu_data){
            foreach ($menu_data as $menu_k=>$menu_v){
                $menu_data[$menu_k]['is_selected'] = false;
                if ($menu_v['is_display']==1){
                    if ($menu_id==$menu_v['id']){
                        $menu_data[$menu_k]['is_selected'] = true;
                    }
                    if ($menu_info['pid']!=0){
                        //获取上级Pid
                        switch ($menu_info['levle']){
                            case 2:
                                if ($menu_info['pid']==$menu_v['id']){
                                    $menu_data[$menu_k]['is_selected'] = true;
                                }
                                break;
                            case 3:
                                $two_pid = db('background_menu')->where('id',$menu_info['pid'])->value('pid'); //3级
                                if ($two_pid==$menu_v['id']){
                                    $menu_data[$menu_k]['is_selected'] = true;
                                    $first_pid = db('background_menu')->where('id',$two_pid)->value('pid'); //2级
                                    foreach ($menu_data as $t_k=>$two_v){
                                        if ($first_pid==$two_v['id']){
                                            $menu_data[$t_k]['is_selected'] = true;
                                            break;
                                        }
                                    }
                                }
                                break;
                            case 4:
                                $three_pid = db('background_menu')->where('id',$menu_info['pid'])->value('pid'); //4级
                                if ($three_pid==$menu_v['id']){
                                    $menu_data[$menu_k]['is_selected'] = true;
                                    $two_pid = db('background_menu')->where('id',$three_pid)->value('pid'); //3级
                                    foreach ($menu_data as $t_k=>$two_v){
                                        if ($three_pid==$menu_v['id'] && $two_pid==$two_v['id']){
                                            $menu_data[$t_k]['is_selected'] = true;
                                            $first_pid = db('background_menu')->where('id',$two_pid)->value('pid'); //2级
                                            foreach ($menu_data as $t_r_k=>$three_v){
                                                if ($three_pid==$menu_v['id'] && $first_pid==$three_v['id']){
                                                    $menu_data[$t_r_k]['is_selected'] = true;
                                                }
                                            }
                                        }
                                    }
                                }
                                break;  
                        }
                    }
                    $menu_data[$menu_k]['url']=request()->module().'/'.$menu_v['action_name'];
                    $menu_list[] = $menu_data[$menu_k];
                }
            }
            $menus=list_to_tree($menu_list);
            $this->assign('menu_list',$menus);
        }
        return $this->fetch('public/leftMenu');
    }
    
    public function header(){
        $this->assign('header_info',$this->web_info);
        return $this->fetch('public/header');       
    }
      
    public function footer(){
        $this->assign('foot_info',$this->web_info);
        return $this->fetch('public/footer');
    }
    
    public function seo(){
        $this->assign('base_info',$this->web_info);
        return $this->fetch('public/seo');
    }
    
}
