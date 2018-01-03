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
use think\Db; 
class Sms extends Base{
    
    public function daYuConfig(){
        $page=input('page_num',config('paginate.list_rows'));
        $sms_list=db('ali_sms')->where('admin_id',$this->uid)->paginate($page,false,['query'=>get_query()]);
        $this->assign('sms_list',$sms_list);
        $this->assign('page',show_page($sms_list->render()));
        
        $appliy_info=db('is_open_sms')->where(['admin_id'=>$this->uid,'type'=>10])->find();
        $this->assign('appliy_info',$appliy_info);
        return $this->fetch();
    }
    
    public function editDaYu(){ 
        return $this->fetch();
    }
    
    public function saveDaYu(){
        $data=request()->param();
        if (input('t')=='u'){
            config('default_ajax_return','json');
            $rule=[
                ['id','require','非法操作']
            ];
            if (isset($data['sign_name']))$rule[]=['sign_name','require','短信签名必须填'];
            if (isset($data['sms_code']))$rule[]=['sms_code','require','短信模板ID必须填'];
            if (isset($data['sms_key']))$rule[]=['sms_key','require','短信key必须填'];
            if (isset($data['sms_secret']))$rule[]=['sms_secret','require','短信secret必须填'];
            $valiate=new \think\Validate($rule);
            if ($valiate->check($data)){
                $data['update_time']=time();
                db('ali_sms')->where('id',$data['id'])->update($data);
                return ['code'=>1,'msg'=>'更新成功','url'=>new_url('admin/Sms/daYuConfig')];
            }
            else{
                return ['code'=>1,'msg'=>$valiate->getError()];
            }
        }
        else{
            $data['id']=null;
            $rule=[
                ['title','require|length:3,16','短信名称必填|名称长度为3到16个字符'],
                ['sign_name','require','短信签名必须填'],
                ['sms_code','require','短信模板ID必须填'],
                ['sms_key','require','短信key必须填'],
                ['sms_secret','require','短信secret必须填']
            ];
            return $this->saveData('ali_sms',$data,$rule,new_url('admin/Sms/daYuConfig'));
        }
    }
    
    public function changeDaYuStatus(){
        return $this->changeStatus('ali_sms');
    }
    
    public function delDaYu(){
        return $this->reallyDelete('ali_sms',false,new_url('daYuConfig'));
    }
    
    public function changeAppliySms(){
        $data=request()->param();
        $save_data=[
            'id'=>$data['id'],
            'admin_id'=>$this->uid,
            'is_enabled'=>empty($data['is_enabled'])?0:1,
            'sms_id'=>$data['sms_id'],
            'type'=>10,
        ];
        return $this->saveData('is_open_sms',$save_data);
    }
}