<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
//use think\Db;
use Payment\Common\PayException;
use Payment\Client\Charge;
use Payment\Config;
/**
 * 商品编辑数据
 * @author 89830
 */
class Pay extends Base {
    
    public function alipayList(){
        $page=input('page_num',config('list_rows'));
        $alipay_list=db('alipay_config')->where('admin_id',$this->uid)->paginate($page,false,['query'=>get_query()]);
        $show=show_page($alipay_list->render());
        $this->assign('page',$show);
        $this->assign('alipay_list',$alipay_list);
        return $this->fetch();
    }
    /**
     * 支付宝支付配置
     */
    public function alipayConfig(){
        $ali_info=$this->getDataInfo('alipay_config', input('id'));
        $this->assign('ali_info',$ali_info);
        return $this->fetch();
    }
    
    /**
     * 保存支付配置
     */
    public function saveAlipay(){
        $data=request()->param();
        $rule=[
            ['partner_id','require|min:5','支付宝账号ID不可为空|支付宝账号ID的长度不可小于5'],
            ['app_id','require|min:5','请填写应用APPID|APPID的长度不可小于5'],
            ['ali_public_key','require','请填写支付宝公钥'],
            ['rsa_private_key','require','请填写支付宝密钥'],
            ['limit_pay','length:0,6','至少要保留一个支付方式']
        ];
        return $this->saveData('alipay_config', $data,$rule,new_url('admin/Pay/alipayList'));
    }
    
    /**
     * 删除配置
     */
    public function delAlipay(){
        return $this->reallyDelete('alipay_config');
    }
    
    /**
     * 改变状态
     */
    public function changeAlipayStatus(){
        return $this->changeStatus('alipay_config');
    }
    
    /**
     * 排序
     */
    public function alipayOrder(){
        return $this->dataOrder('alipay_config');
    }
    
    /**
     * 支付宝回调地址（以后统一接入api模块，不在此处！！！！）
     */
    public function ailNotify(){
        
    }
    
    /**
     * 招商一网通配置列表
     */
    public function cmbList(){
        $page=input('page_num',config('list_rows'));
        $cmb_list=db('cmb_config')->where('admin_id',$this->uid)->paginate($page,false,['query'=>get_query()]);
        $show=show_page($cmb_list->render());
        $this->assign('page',$show);
        $this->assign('cmb_list',$cmb_list);
        return $this->fetch();
    }
    
    /**
     * 编辑添加一网通配置数据
     * @return mixed|string
     */
    public function editCmb(){
        $id=input('id');
        $cmb_info=$this->getDataInfo('cmb_config', $id);
        $this->assign('cmb_info',$cmb_info);
        return $this->fetch();
    }
    
    /**
     * 保存一网通配置数据
     */
    public function saveCmb(){
        if (request()->isAjax()){
            $rule=[
                ['title','require','请填写名称'],
                ['branch_no','require|number|length:4','请填写商户分行号|商户分行号必须是数字|商户分行号只能4个数字'],
                ['merchant_no','require|number|length:6','请填写商户号|商户号必须是数字|商户号只能6个数字'],
                ['mer_key','require','请填写密钥'],
                ['op_pwd','require','请填写操作员密码']
            ];
            $data['limit_pay']=empty($data['limit_pay'])?'A':NULL; 
            $data=request()->param();
            return $this->saveData('cmb_config', $data,$rule,new_url('admin/Pay/cmbList'));
        }
    }
    
    /**
     * 删除一网通配置数据
     */
    public function delCmb(){
        return $this->reallyDelete('cmb_config');
    }
    
    /**
     * 一网通排序
     */
    public function orderCmb(){
        return $this->dataOrder('cmb_config');
    }
    
    /**
     * 改变状态
     */
    public function changeCmbStatus(){
        return $this->changeStatus('cmb_config');
    }
    
}