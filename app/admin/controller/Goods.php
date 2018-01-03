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
/**
 * 商品编辑数据
 * @author 89830
 */
class Goods extends Base {
    
    /**
     * 产品列表
     */
    public function productList(){
        $data=request()->param();
        $product_condition=[
            'admin_id'=>$this->uid
        ];
        if (!empty($data['key_words']))$product_condition['name|sub_name']=['like',"%{$data['key_words']}%"];
        if (!empty($data['category_id']))
        if (time_condition(input('start_time'),input('end_time')))$product_condition['start_time|end_time']=time_condition(input('start_time'),input('end_time')); 
        $page=input('page_num',config('paginate.list_rows'));
        $product_data=db('product_info')->where($product_condition)->field('product_details,img_list,cover_img',true)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
        $product_temp=$product_data->toArray();
        $product_list=$product_temp['data'];
        if ($product_list){
            foreach ($product_list as $product_key=>$product_val){
            
            } 
        }
        $category_list=db('product_category')->order('sort asc')->select();
        $this->assign('category_list',$category_list);
        $this->assign('page',show_page($product_data->render()));
        $this->assign('product_list',$product_list);
        return $this->fetch();
    }
    
    /**
     * 查看商品各种属性值
     */
    public function productAttrList(){
        //获取商品列缩图
        config('default_ajax_return','json');
        $img_url=db('product_info')->where(['admin_id'=>$this->uid,'id'=>input('product_id')])->value('thumb_cover_img');
        $attr_list=db('product_data')->where(['admin_id'=>$this->uid,'product_id'=>input('product_id')])->field('create_time,update_time',true)->select();
        if ($attr_list){
            $categrot_titles=[];
            foreach ($attr_list as $attr_key=>$attr_val){
                //分类获取
                $categrot_id=[];
                if (!empty($attr_val['category_id_1'])){
                    $categrot_id[]=$attr_val['category_id_1'];
                }
                if (!empty($attr_val['category_id_2'])){
                    $categrot_id[]=$attr_val['category_id_2'];
                }
                if (!empty($attr_val['category_id_3'])){
                    $categrot_id[]=$attr_val['category_id_3'];
                }
                if (!empty($attr_val['category_id_4'])){
                    $categrot_id[]=$attr_val['category_id_4'];
                }
                
                if (!empty($categrot_id)){
                    $categrot_titles=db('product_category')->where(['id'=>['in',$categrot_id]])->column('name');
                }
                //属性变量
                $attr_data=json_decode($attr_val['attr_data'],true);
                $attr_oprion=[];
                if (is_array($attr_data)){
                    foreach ($attr_data as $attr_id=>$attr_val_id){
                        $attr_name=db('product_attr')->where(['id'=>$attr_id,'category_id'=>end($categrot_id)])->value('name');
                        $attr_val_name=db('product_attr_val')->where(['attr_id'=>$attr_id,'id'=>$attr_val_id])->value('name');
                        $attr_oprion[]=$attr_name.'：'.$attr_val_name;
                    }
                }
                $attr_list[$attr_key]['attr_data']=empty($attr_oprion)?'暂无属性':implode('；', $attr_oprion);
                $attr_list[$attr_key]['enabled_url']=url('admin/Goods/changeProductStatus',['t'=>'e']);
                $attr_list[$attr_key]['edit_url']=new_url('admin/Goods/editProductAttr',['id'=>$attr_val['id'],'product_id'=>$attr_val['product_id']]);
            }
            return ['code'=>1,
                'data_list'=>$attr_list,
                'img_url'=>$img_url,
                'category_title'=>implode('-', $categrot_titles),
                'edit_all_url'=>new_url('admin/Goods/editProductAttr',['product_id'=>input('product_id'),'type'=>'all']),
                'add_url'=>new_url('admin/Goods/addProductAttr',['product_id'=>input('product_id'),'categrot_ids'=>implode('_', $categrot_id)])
            ];
        }
        else{
            return ['code'=>0,'img'=>'没有任何属性'];
        }
    }
    
    /**
     * 编辑添加产品
     */
    public function editProduct(){
        $id=input('id');
        $product_info=$this->getDataInfo('product_info', $id);
        if (input('type')=='copy')$product_info['id']=null;
        $attr_checked_ids=[];
        if (!empty($product_info['img_list'])){
            $product_info['img_list']=json_decode($product_info['img_list'],true);
            $attr_list=db('product_data')->where(['admin_id'=>$this->uid,'product_id'=>$id])->field('is_active,active_price,start_active_time,end_active_time,create_time,update_time',true)->select();
            $category_ids=[
                $attr_list[0]['category_id_1'],
                $attr_list[0]['category_id_2'],
                $attr_list[0]['category_id_3'],
                $attr_list[0]['category_id_4'],
            ];
            if ($attr_list){
                foreach ($attr_list as $attr_key=>$attr_val){
                    if (!empty($attr_val['attr_data'])){
                        $attr_val_ids=[];
                        $attr_data=json_decode($attr_val['attr_data'],true);
                        $attr_option=[];
                        foreach ($attr_data as $attr_id=>$attr_val_id){
                            $attr_val_ids[]=$attr_val_id;
                            $attr_checked_ids[]=$attr_val_id;
                            $attr_val_name=db('product_attr_val')->where('id',$attr_val_id)->value('name');
                            $attr_option[$attr_id]=[
                                'name'=>$attr_val_name,
                                'id'=>$attr_val_id
                                ];
                        }
                        $attr_count=count($attr_option);
                        $attr_list[$attr_key]['attr_val_str']=implode('_', $attr_val_ids);
                        $attr_list[$attr_key]['attr_data']=$attr_option;
                   }
                }
                $category_titles=db('product_category')->where(['id'=>['in',$category_ids]])->column('name');
                $product_info['attr_count']=$attr_count;
                $product_info['category_titles']=implode('-', $category_titles);
                $product_info['attr_list']=$attr_list;
                //获取相应的商品属性
                $attr_name_list=db('product_attr')->where(['category_id'=>end($category_ids),'is_enabled'=>1])->field('id,name')->order('sort asc')->select();
                if ($attr_name_list){
                    foreach ($attr_name_list as $attr_name_key=>$attr_name_val){
                        $attr_val_list=db('product_attr_val')->where(['is_enabled'=>1,'attr_id'=>$attr_name_val['id']])->field('id,name,attr_id')->order('sort asc')->select();
                        if ($attr_val_list){
                            $attr_name_list[$attr_name_key]['attr_val_list']=$attr_val_list;
                        }
                    }
                }
                $this->assign('category_ids',implode(',', $category_ids));
                $this->assign('attr_name_list',$attr_name_list);
            }
        }
        $this->assign('attr_checked_ids',implode(',', $attr_checked_ids));
        $this->assign('product_info',$product_info);
        return $this->fetch();
    }
    
    /**
     * 保存产品
     */
    public function saveProduct(){
        config('default_ajax_return','json');
        $data=request()->post();
        $rlue=[
            ['name','require|length:3,18','请填写商品名称|名称长度3到18个字符'],
            ['sub_name','length:3,24','商品子名称长度3到24个字符'],
            ['cover_img','require','请上传低于2M商品封面图'],
            ['img_list','require','请上传低于2M的轮播图'],
            ['category_id','require','请选择商品分类'],
            ['attr','require','请选择商品属性'],
            ['product_option','require','请选择完整的商品属性'],
            ['product_details','require|length:1,6000','请填写商品详情|商品详情字数上限6000'],
            ['shelves_time','requireIf:shelves_type,2','请选择预定上架时间'],
            ['lowest_price','number|>=:0','一口价必须是数字|请填写大于会等于0的数字'],
            ['sort','number','排序请填写数字']
        ];
        if (!empty($data['id']) && is_string($data['category_id'])){
            $data['category_id']=explode(',', $data['category_id']);
        }
        $product_option=permutation_and_combination(array_values($data['attr']));
        if (count($product_option[0])<count($data['product_option'])){
            return ['code'=>0,'msg'=>'商品属性组合不合法'];
        }
        $validate=new \think\Validate($rlue);
        if (!$validate->check($data)){
            return ['code'=>0,'msg'=>$validate->getError()];
        }
        //处理商品信息 保存商品获取ID
        //获取总库存
        $total_stock=0;
        foreach ($data['product_option'] as $option_val){
            $total_stock+=intval($option_val['stock']);
        }
        if ($data['shelves_type']==1){
            $shelves_time=time();
        }
        elseif ($data['shelves_type']==2){
            $shelves_time=strtotime($data['shelves_time']);
        }
        $save_product_info=[
            'id'=>empty($data['id'])?null:$data['id'],
            'name'=>$data['name'],
            'sub_name'=>$data['sub_name'],
            'admin_id'=>$this->uid,
            'cover_img'=>$data['cover_img'],
            'total_stock'=>$total_stock,
            'thumb_cover_img'=>$data['thumb_cover_img'],
            'img_list'=>empty($data['img_list'])?null:json_encode($data['img_list']),
            'is_top'=>empty($data['is_top'])?'0':1,
            'product_details'=>$data['product_details'],
            'is_display'=>empty($data['is_display'])?'0':1,
            'sort'=>(empty($data['sort']))?100:$data['sort'],
            'lowest_price'=>$data['lowest_price'],
            'shelves_type'=>$data['shelves_type'],
            'shelves_time'=>($data['shelves_type'])==3?null:$shelves_time,
            'create_time'=>time(),
            'update_time'=>time()
        ];
        $save_result=$this->saveData('product_info', $save_product_info,null,new_url('admin/Goods/productList'));
        //1.处理商品各个属性价格与库存
        //属性处理JSON {属性名ID:属性值，···}
        if (!empty($data['attr']) && $save_result['code']==1){
            $product_data=[];
            foreach ($product_option[0] as $attr_val_str){
                //匹配各个属性数据
                $product_code=null;
                $product_stock=0;
                $product_price=0;
                $product_data_id=null;
                foreach ($data['product_option'] as $option_srt=>$option_val){
                    if ($attr_val_str==$option_srt){
                        $product_code=$option_val['code'];
                        $product_stock=$option_val['stock'];
                        $product_price=$option_val['price'];
                        $product_data_id=$option_val['id'];
                        $product_name=$option_val['name'];
                    }
                }
                //分割字符串
                $attr_data=[];
                foreach (explode('_', $attr_val_str) as $attr_val_id){
                    foreach ($data['attr'] as $attr_id=>$check_val_ids){
                        if (in_array($attr_val_id, $check_val_ids)){
                            $attr_data[$attr_id]=$attr_val_id;
                        }
                    }
                }
                $product_temp_data=[
                    'admin_id'=>$this->uid,
                    'product_id'=>$save_result['id'],
                    'product_code'=>$product_code,
                    'attr_data'=>empty($attr_data)?null:json_encode($attr_data),
                    'sale_price'=>$product_price,
                    'category_id_1'=>empty($data['category_id'][0])?null:$data['category_id'][0],
                    'category_id_2'=>empty($data['category_id'][1])?null:$data['category_id'][1],
                    'category_id_3'=>empty($data['category_id'][2])?null:$data['category_id'][2],
                    'category_id_4'=>empty($data['category_id'][3])?null:$data['category_id'][3],
                    'name'=>$product_name,
                    'stock'=>$product_stock,
                    'create_time'=>time(),
                    'update_time'=>time()
                ];
                if (!empty($product_data_id)){
                    //新增
                    $product_data[]=$product_temp_data;
                }
            }
            //批量新增商品属性数据
            if (!empty($product_data)){
                db('product_data')->where(['admin_id'=>$this->uid,'product_id'=>$save_result['id']])->delete();
                db('product_data')->insertAll($product_data);
            }
        }
        return ['code'=>1,'msg'=>'操作成功','url'=>new_url('admin/Goods/productList')]; 
    }
    
    /**
     * 删除产品
     */
    public function delProduct(){
        $ids=input('ids/a');
        $del_result=$this->reallyDelete('product_info');
        db('product_data')->where(['admin_id'=>$this->uid,'product_id'=>['in'=>$ids]])->delete();
        return $del_result;
    }
    
    /**
     * 改变产品是否显示
     */
    public function changeProductStatus(){
        $t=input('t');
        if($t=='d'){
            return $this->changeStatus('product_info','is_display',2);
        }
        elseif ($t=='t'){
            return $this->changeStatus('product_info','is_top',4);
        } 
        elseif ($t=='e'){
            return $this->changeStatus('product_data');
        }
    }
    
    /**
     * 产品排序
     */
    public function orderProduct(){
        return $this->dataOrder('product_info'); 
    }
    
    /**
     * 由分类添加商品属性
     * @param $id
     */
    public function productAttr(){
        config('default_ajax_return','json');
        //$product_attr_info=db('product_data')->where(['admin_id'=>$this->uid,'id'=>$id])->find();
        $attr_list=db('product_attr')->where(['category_id'=>input('category_id'),'is_enabled'=>1])->order('sort asc')->select();
        if ($attr_list){
            foreach ($attr_list as $attr_key=>$attr_val){
                $attr_list[$attr_key]['attr_val_list']=db('product_attr_val')->where(['is_enabled'=>1,'attr_id'=>$attr_val['id']])->order('sort asc')->select();
            }
        }
        return ['code'=>1,'data_list'=>$attr_list];       
    }
        
    /**
     * 修改商品属性
     */
    public function editProductAttr(){
        $id=input('id');
        $data=request()->param();
        $attr_info=$this->getDataInfo('product_data', $id,['product_id'=>$data['product_id']]);
        $this->assign('attr_info',$attr_info);
        return $this->fetch();
    }

    /**
     * 保存修改商品属性
     */
    public function saveProductAttr(){
        config('default_ajax_return','json');
        $data=request()->param();
        $rule=[
            ['product_code','alphaDash','商品编码只能是字母和数字，下划线_及破折号-'],
            ['sale_price','number|>=:0','销售价必须为数字|销售价必须大于等于0'],
            ['stock','number|>=:0','库存必须为数字|库存必须大于等于0'],
            ['is_active','accepted','非法操作！'],
            ['start_active_time','requireIf:is_active,1|before:end_active_time','请填写活动开始时间|开始时间必须小于结束时间'],
            ['end_active_time','requireIf:is_active,1|after:start_active_time','请填写活动结束时间|结束时间必须大于开始时间']
        ];
        $validate=new \think\validate($rule);
        if (!$validate->check($data)){
            return ['code'=>0,'msg'=>$validate->getError()];
        }
        if ($data['type']=='all'){
            if (empty($data['product_id'])){
                return ['code'=>0,'msg'=>'商品ID不存在！'];
            }
            $condition=[
                'admin_id'=>$this->uid,
                'product_id'=>$data['product_id']
            ];
            //更新总库存
            $count=db('product_data')->where($condition)->field('id')->count();
            db('product_info')->where(['admin_id'=>$this->uid,'id'=>$data['product_id']])->setField('total_stock',intval($count)*intval($data['stock']));
        }
        else{
            if (empty($data['id'])){
                return ['code'=>0,'msg'=>'ID不存在！'];
            }
            $condition=[
                'admin_id'=>$this->uid,
                'id'=>$data['id']
            ];
            //更新总库存
            $stock=db('product_data')->where($condition)->value('stock');
            $diff=$data['stock']-$stock;
            db('product_info')->where(['admin_id'=>$this->uid,'id'=>$data['product_id']])->setInc('total_stock',$diff);
        }
        $save_data=[
            'product_code'=>$data['product_code'],
            'sale_price'=>$data['sale_price'],
            'stock'=>$data['stock'],
            'is_active'=>empty($data['is_active'])?0:1,
            'start_active_time'=>strtotime($data['start_active_time']),
            'end_active_time'=>strtotime($data['end_active_time']),
            'update_time'=>time()
        ];
        if (!empty($data['name']))$save_data['name']=$data['name'];
        $save_result=db('product_data')->where($condition)->setField($save_data);
        if ($save_result){
            //更新总库存
            return ['code'=>1,'msg'=>'操作成功',new_url('admin/Goods/productList')];
        }
        else{
            return ['code'=>0,'msg'=>'请重新操作一次'];
        }
    }
    
    /**
     * 删除商品分类
     */
    public function delProductAttr(){
        return $this->reallyDelete('product_data');
    }
    
    /**
     * 产品分类
     */
    public function categoryList(){
        $category_condition=[
            //'admin_id'=>$this->uid
        ];
        $data=request()->param();
        if (!empty($data['key_words']))$category_condition['name']=['like',"%{$data['key_words']}%"];
        $category_condition['pid']=!empty($data['pid'])?$data['pid']:'0';
        $page=input('page_num',config('paginate.list_rows'));
        $category_list=db('product_category')->where($category_condition)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
        $show=show_page($category_list->render());
        $this->assign('page',$show);
        $this->assign('category_list',$category_list);
        if (input('type')=='sub'){
            $this->assign('selector',$data['selector']);
            return $this->fetch('ajaxCategoryList');
        }
        return $this->fetch();
    }
    
    /**
     * 编辑添加分类
     */
    public function editCategory(){
        $id=input('id');
        $category_info=$this->getDataInfo('product_category', $id,[],false); 
        $this->assign('category_info',$category_info);
        $category_list=db('product_category')->where([
            //'admin_id'=>$this->uid,
            'levle'=>['<',4]
        ])->order('sort asc')->select();
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }
    
    /**
     * 保存分类
     */
    public function saveCategory(){
        $data=request()->param();
        $rule=[
            ['name','require|length:2,16','请填写种类名称|字符长度2到16'],
            ['sort','number','排序请填写数字'],
        ];
        del_old_file($data['cover_img'], $data['old_cover_img']);
        del_old_file($data['thumb_cover_img'], $data['old_thumb_cover_img']);
        if (!empty($data['pid'])){
            $levle=db('product_category')->where(['admin_id'=>$this->uid,'id'=>$data['pid']])->value('levle');
            $data['levle']=$levle+1;
        }
        return $this->saveData('product_category', $data,$rule,new_url('admin/Goods/categoryList'));
    }
    
    /**
     * 删除分类
     */
    public function delCategory(){
        $ids=input('ids/a');
        $cover_img=db('product_category')->where([
            'admin_id'=>$this->uid,
            'id'=>['in',$ids]
        ])->column('cover_img','thumb_cover_img');
        del_file($cover_img);
        $this->delPidCategory($ids[0]);//删除子类
        return $this->reallyDelete('product_category',false,new_url('admin/Goods/categoryList'));
    }
    
    private function delPidCategory($pid){
        $son_info = db('product_category')->where('pid',$pid)->value('id');
        if ($son_info){
            $son_pid = $son_info['id'];
            db('product_category')->where(['admin_id'=>$this->uid,'pid'=>$pid])->delete();
            $this->delPidCategory($son_pid);
        }
        else{
            return true;
        }
    }
    
    /**
     * 分类是否启用
     */
    public function changeCategoryStatus(){
        return $this->changeStatus('product_category');
    }
    
    /**
     * 分类排序
     */
    public function orderCategory(){
        return $this->dataOrder('product_category');
    }
    
    
    /**
     * 产品属性类
     */
    public function attributeList(){
        $category_ids=db('product_attr')->column('category_id'); //'admin_id'=>$this->uid,
        $category_list=db('product_category')->where(['id'=>['in',$category_ids]])->select();
        $data=request()->param();
        $attr_condition=[
            //'admin_id'=>$this->uid
        ];
        if (!empty($data['category_id']))$attr_condition['category_id']=$data['category_id'];
        if (!empty($data['key_words']))$attr_condition['name']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $attr_data=db('product_attr')->where($attr_condition)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
        $attr_temp=$attr_data->toArray();
        $attr_list=$attr_temp['data'];
        if ($attr_list){
            foreach ($attr_list as $attr_key=>$attr_val){
                if (!empty($attr_val['category_id'])){
                    $attr_list[$attr_key]['category_title']=db('product_category')->where('id',$attr_val['category_id'])->value('name');
                }
                else{
                    $attr_list[$attr_key]['category_title']='【未分类】';
                }
            }
        }
        $show=show_page($attr_data->render());
        $this->assign('page',$show);
        $this->assign('attr_list',$attr_list);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }
    
    /**
     * 编辑添加属性类
     */
    public function editAttr(){
        $id=input('id');
        $attr_info=$this->getDataInfo('product_attr', $id,[],false);
        if ($attr_info){
            $attr_info['category_title']=db('product_category')->where('id',$attr_info['category_id'])->value('name');
        }
        $this->assign('attr_info',$attr_info);
        return $this->fetch();
    }
    
    public function getCategoryData(){
        config('default_ajax_return','json');
        $category_list=db('product_category')->where(['is_enabled'=>1,'pid'=>input('pid',0)])->field('id,name,pid,levle')->order('sort asc')->select();
        if ($category_list){
            return ['code'=>1,'data_list'=>$category_list];
        }
        else{
            return ['code'=>0];
        }
    }
    
    /**
     * 保存属性类
     */
    public function saveAttr(){
        config('default_ajax_return','json');
        $data=request()->param();
        $rule=[
            ['name','require|length:2,16','请填写属性名称|字符长度2到16'],
            ['sort','number','排序请填写数字']
        ];
        if (empty($data['id'])){
            if (empty($data['category_id']))return ['code'=>0,'msg'=>'请选择商品分类'];
        }
        if (!empty($data['category_id'])){
            $category_ids=$data['category_id'];
            $category_ids=$data['category_id'];
            foreach ($category_ids as $category_id){
                if($category_id==-1 || empty($category_id)){
                    return ['code'=>0,'msg'=>'请选择商品分类'];
                }
            }
            $data['category_id']=end($data['category_id']);
        }
        return $this->saveData('product_attr', $data,$rule,new_url('admin/Goods/attributeList'));     
    }
    
    /**
     * 删除属性类
     */
    public function delAttr(){
        $ids=input('ids/a');
        //删除所有属性值
        db('product_attr_val')->where([
            'admin_id'=>$this->uid,
            'attr_id'=>['in',$ids]
        ])->delete();
        return $this->reallyDelete('product_attr');
    }
    
    /**
     * 是否启用
     */
    public function changeAttrStatus(){
        return $this->changeStatus('product_attr');
    }
    
    /**
     * 排序属性类
     */
    public function orderAttr(){
        return $this->dataOrder('product_attr');
    }
    
    /**
     * 产品属性值
     */
    public function attValList(){
        config('default_ajax_return','json');
        $attr_val_list=db('product_attr_val')->where('attr_id',input('attr_id'))->order('sort asc')->field('create_time,update_time,admin_id',true)->select();       
        if ($attr_val_list){
            foreach ($attr_val_list as $val_key=>$val){
                $attr_val_list[$val_key]['url']=new_url('admin/Goods/editAttrVal',['id'=>$val['id'],'attr_id'=>$val['attr_id']]);
                if ($val['is_enabled']==1){
                    $attr_val_list[$val_key]['color']='label-success';
                }
                else{
                    $attr_val_list[$val_key]['color']='label-danger';
                }
            }
            return ['code'=>1,'data_list'=>$attr_val_list];
        }
        else{
            return['code'=>0,'msg'=>'暂无此属性值'];
        }
    }
    
    /**
     * 编辑添加属性值
     */
    public function editAttrVal(){
        $id=input('id');
        $attr_val_info=$this->getDataInfo('product_attr_val', $id,[],false);
        $this->assign('attr_val_info',$attr_val_info);
        return $this->fetch();
    }
    
    /**
     * 保存属性值
     */
    public function saveAttrVal(){
        $data=request()->param();
        $rule=[
            ['name','require|length:2,16','请填写属性值名称|字符长度2到16'],
            ['sort','number','排序请填写数字'],
            ['attr_id','require','选择所属属性']
        ];
        return $this->saveData('product_attr_val', $data,$rule);
    }
    
    /**
     * 删除属性值
     */
    public function delAttrVal(){
        return $this->reallyDelete('product_attr_val');
    }
     
}