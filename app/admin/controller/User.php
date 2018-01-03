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
class User extends Base
{
    public function userInfo(){

        //print_r(PHP_INT_MAX);die;
		//$this->assign('slide_list',$slide_list);
		//渲染模板
		$user_info=db('admin_info')->where('id',$this->uid)->find();
		$this->assign('user_info',$user_info);
        return $this->fetch();
    }
    
    public function saveUser(){
        config('default_ajax_return','json');
        $data=request()->param();
        if ($data['t']=='e'){
            del_old_file($data['head_img'], $data['old_head_img']);
            $rule=[];
            $save_data=[
                'update_time'=>time(),
                'head_img'=>$data['head_img']
            ];
            if (!empty($data['user_name'])){
                $rule[]=['user_name','require|chsDash|length:3,16|unique:admin_info','请输入用户名|用户名只能只能是汉字、字母、下划线组成|用户名长度最小3个字符最长16个字符|用户名已被注册'];
                $save_data['user_name']=$data['user_name'];
            }
            if (!empty($data['user_email'])){
                $rule[]=['user_email','require|email|unique:admin_info','请输入邮箱|请输入正确的邮箱格式|邮箱格式已被注册'];
                $save_data['user_email']=$data['user_email'];
            }
            if (!empty($data['user_phone'])){
                $rule[]=['user_phone',['require','length:11','regex'=>'/^(13[0-9]{9})|(15[0-9]{9})|(18[0-9]{9})|(17[0-9]{9})/','unique'=>'admin_info'],'请填写手机号码|手机号码目前仅支持11位|电话号码格式错误|此手机号码已注册'];
                $save_data['user_phone']=$data['user_phone'];
            }
            if (!empty($rule)){
                $validate=new \think\Validate($rule);
                $result=$validate->check($save_data);
                if (!$result){
                    return ['code'=>0,'msg'=>$validate->getError()];
                }
            }
            $save_result=db('admin_info')->where('id',$this->uid)->setField($save_data);
            if ($save_result){
                session('avatar_src',$data['head_img']);
                return ['code'=>1,'msg'=>'操作成功'];
            }
            else{
                return ['code'=>0,'msg'=>'网络错误，请重新操作'];
            }
        }
        elseif ($t=='p'){
            $password=db('admin_info')->where('id',$this->uid)->value('password');
            $rule=[
                ['password','require|different:new_password','原密码不能为空|新密码不能与旧密码相同'],
                ['new_password',['require','length'=>'6,16','regex'=>'/^[a-zA-Z](?=.*\d)[a-zA-Z\d]/','confirm'=>'again_password'],'密码不可为空|密码长度必须在6位到16位|首位必须以字母开头包含数字组成密码|密码不一致'],
                ['again_password',['require','length'=>'6,16','regex'=>'/^[a-zA-Z](?=.*\d)[a-zA-Z\d]/'],'密码不可为空|密码长度必须在6位到16位|首位必须以字母开头包含数字组成密码']
            ];
            if (password_verify ($data['password'] , $password )){
                return ['code'=>0,'msg'=>'原密码错误'];
            }
            $save_data=[
                'update_time'=>time(),
                'password'=>password_hash($data['new_password'])
            ];
            $validate=new \think\Validate($rule);
            if ($validate->check($save_data)){
                $save_result=db('admin_info')->where('id',$this->uid)->setField($save_data);
                if ($save_result){
                    return ['code'=>0,'msg'=>'修改成功，将重新登陆系统','url'=>url('admin/Login/login')];
                }
                else{
                    return ['code'=>0,'msg'=>'网络错误，请重新操作'];
                }
            }
            else{
                return ['code'=>0,'msg'=>$validate->getError()];
            }
        }
    }
}