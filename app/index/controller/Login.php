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
use think\captcha\Captcha;
use think\Db;
use think\facade\Validate;
use think\facade\Request;
class Login extends Controller {
	//登入页面
	public function login(){
		//已登录,跳转到首页
 	    return $this->fetch('login1');
// 		if(session('aid')){
// 			$this->redirect('admin/Index/index');
// 		} 
// 	    $options_list=config('wechatConfig');
// 	    $options=$options_list['wechat_option_2'];
// 	    $wechat_api=new \EasyWeChat\Foundation\Application($options);
// 	    $oauth = $wechat_api->oauth;
	    // 未登录
// 	    if (empty($_SESSION['wechat_user'])) {
// 	        $_SESSION['target_url'] = url('admin/Login/test');
// 	        return $oauth->redirect();
// 	        // 这里不一定是return，如果你的框架action不是返回内容的话你就得使用
// 	         $oauth->redirect()->send();
// 	    }
	    
	}
	
	public function test(){
	    
	    $wechat_oauth=action();
	    $options_list=config('wechatConfig');
	    $options=$options_list['wechat_option_2'];
	    $wechat_api = new \EasyWeChat\Foundation\Application($options);
	    
	    $oauth = $wechat_api->oauth;
	    // 获取 OAuth 授权结果用户信息
	    $user = $oauth->user()->toArray();
	    //针对用户信息处理
	    
	    print_r($user);die;
	    //$_SESSION['wechat_user'] = $user->toArray();
	    //$targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];
	    
	    //header('location:'. $targetUrl); // 跳转到 user/profile
	    
	    return $this->fetch();
	}
	
	//验证码
	public function loginVerify(){
		ob_end_clean();
		$verify = new Captcha (config('login.verify'));
        return $verify->entry(time());
    }
	//登陆验证
	public function CheckLogin(){
		if (!request()->isAjax()){
		    config('default_ajax_return','json');
			$this->error("提交方式错误！",url('index/Login/login'));
		}else{
		    config('default_ajax_return','json');
			$user_name=input('user_name');
			$password=input('password');
			if(config('geetest.geetest_on')){
			    if (empty(input('geetest_validate'))){
			        $this->error('点击验证');
			    }
                if(!geetest_check(input('post.'))){
                    $this->error('验证不通过');
                }
            }
            elseif (config('is_verify')){
                $verify =new Captcha ();
                if (!$verify->check(input('verify'), 'aid')) {
                    $this->error('验证码错误');
                }
            }            
			$admin_info=Db::name('admin_info')->where('user_name',del_all_trim($user_name))->find();
    		if (!$admin_info) {
    		    $this->error('用户名不存在');
    		}
    		if (!password_verify($password, $admin_info['password'])) {
    		    $this->error('密码错误');
    		}
			//登录后更新数据库，登录IP，登录次数,登录时间
			$save_data=[
			    'id'=>$admin_info['id'],
                'last_login_time'=>time(),
				'login_ip'=>request()->ip(),
			    'update_time'=>time(),
			    'login_times'=>$admin_info['login_times']+1
			];
			db('admin_info')->where('id',$admin_info['id'])->update($save_data);
			if (!empty($admin_info['pid']) && $admin_info['pid']!=0){
			    $parent_id = get_parent_id('admin_info', 'id', 'pid', $admin_info['id']);
			    session('aid',$parent_id);
			    session('chief_id',$admin_info['pid']);
			}
			else{
			    session('aid',$admin_info['id']);
			    session('chief_id',$admin_info['id']);
			}
			session('user_id',$admin_info['id']);
			session('user_name',$admin_info['user_name']);
			session('avatar_src',$admin_info['head_img']);
			session('login_time',time());
			return [
			    'code'=>1,
			    'msg'=>'登陆成功',
			    'url'=>url('index/Index/index')
			];
		}
	}
	
	public function register(){
	    if (!request()->isAjax()){
	        $this->error('提交方式错误');
	    }
	    else{
	        return [
	            'code'=>0,
	            'msg'=>'暂不开放'
	        ];
	       $rule = [
                ['user_name','require|max:60|unique:admin_info','用户名必须|名称最多不能超过60个字符|用户名已被注册'],
                ['user_email','require|email|unique:admin_info','请填写邮箱|请输入正确的邮箱格式|邮箱已被注册'],
                ['user_phone',['require','length:11','regex'=>'/^(13[0-9]{9})|(15[0-9]{9})|(18[0-9]{9})|(17[0-9]{9})/','unique'=>'admin_info'],'请填写手机号码|手机号码目前仅支持11位|电话号码格式错误|此手机号码已注册'],
                ['password','require|alphaDash|length:6,16','请填写密码|密码由字母数字下划线组成|密码最小长度为6最大为16'],
                ['password_confirm','require|confirm|alphaDash|length:6,16','请填写密码|密码不一致|密码由字母数字下划线组成|密码最小长度为6最大为16'],
                ['is_accept','accepted','接收条款']    
	       ];
            $data = request()->param();
            $validate = new Validate($rule);
            $result = $validate->check($data);
            if ($result){
                $save_data = [
                    'id'=>$data['id'],
                    'pid'=>$this->sid,
                    'user_name'=>$data['user_name'],
                    'user_email'=>$data['user_email'],
                    'user_phone'=>$data['user_phone'],           
                    'effective_time'=>date('Y-m-d',strtotime("+7 days")),
                    'password'=>password_hash($data['password'], PASSWORD_DEFAULT),
                    'user_type'=>'AD',
                    'create_time'=>time(),
                    'update_time'=>time(),
                    'last_login_time'=>time(),
                    'login_ip'=>request()->ip(),
                    'login_times'=>0
                ];
                $save_result =Db::name('admin')->insertGetId($save_data);
                if ($save_result){
                    return [
                        'code'=>1,
                        'msg'=>'成功注册'
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
	
	
	/*
	 * 退出登录
	 */
	public function logout(){
	    //$log = new Log(session('sid'));
	    //$log->saveLog('admin', '',6,'admin_id');
	    \think\facade\Session::clear();
	    \think\facade\Cache::clear();
		$this->redirect('index/Login/login');
	}
	
	
	
}