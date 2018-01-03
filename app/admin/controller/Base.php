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
use think\Controller;
use think\Session;
use think\Cache;
use think\Validate;
use MenuAuth\Auth;
//权限认证
class Base extends Controller {
	//初始化
    protected $uid;  //自己账号的user_id
    protected $parent_id;  //主user_id
	protected $chief_id;
	protected function _initialize(){
        parent::_initialize();
		//未登陆，不允许直接访问
        if (!defined('__ROOT__')) {
            $_root = rtrim(dirname(rtrim(request()->baseFile(), '/')), '/');
            define('__ROOT__', (('/' == $_root || '\\' == $_root) ? '' : $_root));
        }
        if (request()->isMobile()){
            Session::clear();
            Cache::clear();
            config('default_ajax_return','json');
            $this->error('暂不支持手机登陆',url('admin/Login/login'));
        }
        $this->parent_id = session('aid');
        $this->uid = session('user_id');
        $this->chief_id = session('chief_id');
		$login_time = session('login_time');   
		if (empty($this->uid)){
		    Session::clear();
		    Cache::clear();
		    config('default_ajax_return','json');
		    $this->error('请登录账号',url('admin/Login/login'));
		}
		//print_r($login_time);die;
 		if(time()>($login_time+intval(config('login_config.effective_time')*60))){
 		    Session::clear();
 		    Cache::clear();
 		    config('default_ajax_return','json');
 		    $this->error('请重新登录',url('admin/Login/login'));	   
		}
		session('login_time',time());
		$controll_name = request()->controller();
		$action = request()->action();
		//已登录，不需要验证的权限 ;
		$not_check = config('not_check_action'); //不需要检测的控制器/方法
		//当前操作的请求                 模块名/方法名
		//不在不需要检测的控制器/方法时,检测
		if ($this->uid!=10000){
		    //print_r($this->oneself_id);die;
		    if(!in_array($controll_name.'/'.$action, $not_check)){
		        $auth = new Auth();
		        if(!$auth->check($controll_name.'/'.$action,$this->uid)){
     			    config('default_ajax_return','json');
     			    $this->error('没有权限');
		        }
		    }
		}
	}

	/**
	 * 获取数据表信息
	 * @param string $table_name 表名
	 * @param array $condition 条件
	 * @param string $is_sid 是否需要 $sid
	 * @param array 不需要字段 $field 默认全去出
	 */
	protected function getDataInfo($table_name,$id,$condition=[],$is_sid=true,$field=[]){
	    $data_condition = ['id'=>$id];
	    if ($is_sid){
	        $data_condition['admin_id'] = $this->uid;
	    }
	    if (!empty($condition))$data_condition = array_merge($data_condition,$condition);
	    $data_condition = array_unique($data_condition);
	    $data_info = db($table_name)->where($data_condition)->field(true)->find();
	    return $data_info;
	}
	
	/**
	 * 通用保存
	 * @param string $table_name 表名
	 * @param array $data 保存数据
	 * @param string $jump_url
	 * @param string $reload 是否刷新页面
	 * @param array $param_data 跳转参数
	 * @param string $user 是否需要user_id
	 * @return number[]|string[]
	 */
	protected function saveData($table_name,$data,$rule=null,$jump_url=null,$user=true,$is_ajax=true){
	    config('default_ajax_return','json');
	    if ($is_ajax && !request()->isAjax())$this->error('提交方式错误');
	    
	    if ($user)$data['admin_id'] = $this->uid;
	    $is_update = true;$check_result=false;
	    //验证规则
	    if (!empty($rule)){
	        $validate = new Validate($rule);
	        $result = $validate->check($data);
	        if (!$result){
	            return ['code'=>0,'msg'=>$validate->getError()];
	        }
	    }
	    if (empty($data['id']) || !isset($data['id'])){
	        unset($data['id']);
	        $data['create_time'] = time();
	        $data['update_time'] = time();
	        $is_update = false;
	        $save_result = db($table_name)->insertGetId($data);
	    }
	    else{
	        $data['update_time'] = time();
	        $save_result = db($table_name)->update($data);
	    }  
	    if ($save_result) {
	        if(!empty($data['id']) && isset($data['id'])){
	            $id =$data['id'];
	        }
	        else{
	            $id=$save_result;
	        }
	        return ['code'=>1,'is_update'=>$is_update,'id'=>$id,'msg'=>'操作成功','url'=>$jump_url];
	    } 
	    else {
	        return ['code'=>0,'id'=>$id,'msg'=>'非法操作','url'=>false];
	    }
	}
	
	/**
	 * 彻底删除，不可恢复！
	 * @param string $table_name 表名
	 * @param array $ids
	 * @param bool $is_all 是否删除所有数据
	 * @return number[]|number[]|string[]
	 */
	protected function reallyDelete($table_name,$is_all=false,$url=null){
	    config('default_ajax_return','json');
	    $ids=input('ids/a');
	    if (!empty($ids) || $is_all){
	        //$log = new Log($this->user_id);
	        //$log->saveLog($table_name, $ids,4);
	        $ids=($is_all)?true:$ids;
	        $del_result = db($table_name)->delete($ids);
	        if ($del_result){
	            return ['code'=>1,'msg'=>'成功删除','url'=>$url];
	        }
	        else {
	            return ['code'=>0,'msg'=>'请选要删除的数据'];
	        }
	    }
	    else {
	        return ['code'=>0,'msg'=>'请选要删除的数据'];
	    } 
	}
	
	/**
	 * 软删除可恢复数据
	 * @param string $table_name 表名
	 * @param array $ids
	 * @param bool $is_all 是否删除所有数据
	 * @param string $delte_field
	 * @param string $update_time
	 * @return number[]|number[]|string[]
	 */
	protected function softDelete($table_name,$is_all=false,$url=null,$delte_field='is_delete',$update_time='update_time') {
	    config('default_ajax_return','json');
	    $ids=input('ids/a');
	    if (!empty($ids) || $is_all){
	        $save_data = [
	            $delte_field=>1,
	            $update_time=>time()
	        ];
	        //$log = new Log($this->user_id);
	        //$log->saveLog($table_name, $ids,4);
	        if ($is_all){
	            $del_result = db($table_name)->setField($save_data);
	        }
	        else{
	            $del_result = db($table_name)->where('id','in',$ids)->setField($save_data);
	        }
	        if ($del_result){
	            return ['code'=>1,'url'=>$url];
	        }
	        else {
	            return ['code'=>0,'msg'=>'请选要删除的数据'];
	        }
	    }
	    else {
	        return ['code'=>0,'msg'=>'请选要删除的数据'];
	    }
	}
	
	/**
	 * 通用改变状态
	 * @param 表名 $table_name
	 * @param string $field
	 * @param string $type
	 * @return number[]|string[]
	 */
	protected function changeStatus($table_name,$field='is_enabled',$type='1',$url=null,$css=[]){
	    config('default_ajax_return','json');
	    if (!request()->isAjax())['code'=>0,'msg'=>'提交方式错误'];
	    switch ($type){
	        case 1: //是否启用
	            $msg_1='启用';
	            $msg_0='禁用';
	            break;
	        case 2: //是否显示
	            $msg_1='显示';
	            $msg_0='隐藏';
	            break;
	        case 3: //审核是否通过
	            $msg_1='通过';
	            $msg_0='未审核';
	            break;
	        case 4: //是否顶置
	            $msg_1='顶置';
	            $msg_0='未顶置';
	            break;
	        case 5: //是否拉黑    
	            $msg_1='已拉黑';
	            $msg_0='正常';
	            break;
	    }
	    if (!empty($css)){
	        $css_1=!empty($css[0])?$css[0]:'btn-danger';
	        $css_2=!empty($css[1])?$css[1]:'btn-yellow';
	    }
	    else{
	        $css_1='btn-danger';
	        $css_2='btn-yellow';
	    }
	    $id = input('x');
	    if (!empty($id)){
	        $field_val=db($table_name)->where('id',$id)->field(true)->value($field);//判断当前状态情况
	        //$log = new Log($this->user_id);
	        //$log->saveLog($table_name, ['id'=>$id,$field=>$field]);
	        if($field_val==1){
	            $statedata = [$field=>0,'update_time'=>time()];
	            db($table_name)->where('id',$id)->setField($statedata);
	            return ['code'=>1,'msg'=>0,'tip'=>$msg_0,'css_1'=>$css_1,'css_2'=>$css_2];
	        }else{
	            $statedata = [$field=>1,'update_time'=>time()];
	            db($table_name)->where('id',$id)->setField($statedata);
	            return ['code'=>1,'msg'=>1,'tip'=>$msg_1,'url'=>$url,'css_1'=>$css_1,'css_2'=>$css_2];
	        }
	    }
	    else{
	        return ['code'=>0,'msg'=>'ID不存在'];
	    }
	}
	
	/**
	 * 通用排序函数
	 * @param 数据表名 $table_name
	 * @param 排序字段 $sort_field
	 * @param 跳出url $url
	 * @param 查询条件字段 $select_field
	 * @return number[]|string[]
	 */
	protected function dataOrder($table_name,$sort_field='sort',$url=null,$select_field='id'){
	    if (request()->isAjax()){
	        config('default_ajax_return','json');
	        $data=request()->param('data');
	        //print_r($data);die;
	        if ($data){
	            $data_arr=json_decode($data,true);
	            if ($data_arr){
	                foreach ($data_arr as $id=>$sort){
	                    db($table_name)->where($select_field,$id)->setField(['update_time'=>time(),$sort_field=>$sort]);
	                }
	                return ['code'=>1,'msg'=>'排序成功','url'=>$url];
	            }
	            else{
	                return ['code'=>0,'msg'=>'无数据排序','url'=>$url];
	            }
	        }
	    }
	}
}

