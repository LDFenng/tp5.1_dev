<?php
// +----------------------------------------------------------------------
// | LDF [ DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\common\taglib;
use think\template\TagLib;
use think\db;
class customTag extends TagLib{  
    /**
     * 定义标签列表
    */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'input'     => ['attr' => 'table_name,form_type,data_info,ids', 'close' => 0,'level'=>4], //闭合标签，默认为不闭合
        'select'    => ['attr' => 'table_name,form_type,data_info,id1111', 'close' => 0,'level'=>4],
        'pagenum'   => ['attr' => 'selector,field_name,width,class_name', 'close' => 0],
        'screen'    => ['attr' => 'table_name,field_name,width,table_field,class_name,selector,is_multiple,select_val,is_edit','close'=> 0],
        'iddata'    => ['attr' => 'table_name,id,get_name', 'close' => 0]
        //'textarea'  => ['attr' => 'table_name,form_type,data_info,id', 'close' => 0,'level'=>4]
    ];
    
    /**
     * 通用input输入表单标签 由数据表字段决定
     * @param 标签参数 $tag
     * @param 内容 $content
     */
    public function tagInput($tag,$content){
        if (empty($tag['table_name'])){
            exception('table_name 不存在！', 100006);
        } 
        $condition=[
            ['table_name','=',$tag['table_name']],
            ['is_enabled','=',1]
        ];
        if(!empty($tag['form_type']))$condition[]=['form_type','=',$tag['form_type']];
        if (!empty($tag['ids'])){
            $ids = explode(',', $tag['ids']);
            $condition[]=['id','in',$ids];
        }
        $tag_list = Db::name('custom_form')->where($condition)->order('sort asc')->select();
        //遍历标签属性 
        $parse_str = "";
        foreach ($tag_list as $tag_val){
            $form_type = !empty($tag_val['form_type'])?"type='".$tag_val['form_type']."'":"type='text'";
            $form_name = !empty($tag_val['form_name'])?"name='".$tag_val['form_name']."'":null;
            $value = !empty($tag_val['default_val'])?$tag_val['default_val']:null; //是否有默认值
            if (!empty($tag_val['data_info'])){
                foreach ($tag_val['data_info'] as $key=>$val){
                    if (del_all_trim($tag_val['form_name'])==del_all_trim($key)){
                        $value = $val; //外面有值时
                    }
                }
            }
            $default_val = !empty($value)?"value='{$value}'":null;
            if ($tag_val['form_type']!='hidden'){
                $is_hide = $tag_val['is_hide']==1?"style='display:none'":null; //是否是隐藏
                $is_disabled = $tag_val['is_disabled']==1?"disabled='disabled'":null;  //是否禁止调用
                $is_read = $tag_val['is_read']==1?"readonly='readonly'":null; //是否只读
                $is_required = $tag_val['is_required']==1?"required":null; //是否必填
                $maxlength = !empty($tag_val['maxlength'])?"maxlength='".$tag_val['maxlength']."'":null;  //最大长度
                $max = !empty($tag_val['max'])?"max='".$tag_val['max']."'":null; //最大数值
                $min = !empty($tag_val['min'])?"min='".$tag_val['min']."'":null; //最小数值
                $autocomplete = $tag_val['autocomplete']==1?"autocomplete='on'":"autocomplete='off'"; //是否自动完成
                $pattern = !empty($tag_val['pattern'])?"pattern='".$tag_val['pattern']."'":null; //是否启用正则匹配
                $tip_text = !empty($tag_val['tip_text'])?"title='".$tag_val['tip_text']."'":null; //提示语
                $form_css = !empty($tag_val['form_css'])?"class='".$tag_val['form_css']." limited'":"class='col-xs-8 limited'"; //表单样式 
                $placeholder = !empty($tag_val['placeholder'])?"placeholder='".$tag_val['placeholder']."'":null;
                if ($tag_val['form_type']=='textarea'){
                    if (empty($placeholder)){
                        $placeholder = "placeholder='最多可输入".$tag_val['maxlength']."字符'";
                    }
                    $cols = !empty($tag_val['cols'])?"cols='".$tag_val['cols']."'":"cols='20'";
                    $rows = !empty($tag_val['rows'])?"rows='".$tag_val['rows']."'":"rows='3'";
                    $chars_str = "<span class='help-inline col-xs-8'>还可以输入 <span class='middle red charsLeft'></span>个字符</span>";
                }
               
                $is_form_control = strstr($form_css,'form-control');
                $span_str = !empty($is_required)?"<span class='middle red'>*</span>":null;
                $parse_str .= "<div class='form-group' {$is_hide} id='group_{$tag_val['id']}'>";
                $parse_str .= "<label class='col-sm-3 control-label no-padding-right'>".$tag_val['title']."</label>";
                
                if ($tag_val['form_type']=='textarea'){
                    $parse_str .= "<div class='col-xs-9'>";
                    $parse_str .= "<textarea id='{$tag_val['form_name']}' {$form_name} {$cols} {$rows} {$form_css} {$placeholder} {$is_disabled} {$is_read} {$is_required} {$maxlength}>{$default_val}</textarea>";
                    $parse_str .=$chars_str;
                }
                elseif ($tag_val['form_type']=='checkbox'){
                    $parse_str .= "<div class='col-xs-1' style='margin-top:0.78rem'><label>";
                    $checked = !empty($value)?"checked='checked'":'';
                    $parse_str .= "<input class='ace ace-switch ace-switch-4 btn-rotate' {$form_name} type='checkbox' value='1' {$checked}>";
                    $parse_str .= "<span class='lbl'></span></label>";
                    $parse_str .= "<span class='help-inline col-xs-3'>";
                    $parse_str .= "{$span_str}{$tag_val['tip_text']}</span>";
                }
                elseif ($tag_val['form_type']=='image'){
                    $parse_str .= "<div class='col-sm-4'>";
                    $parse_str .= "<input type='hidden' name='old_{$tag_val['form_name']}' {$default_val}/>";
                    $parse_str .= "<input type='hidden' {$form_name} id='{$tag_val['form_name']}' {$default_val}/>";
                    $parse_str .= "<img id='src_{$tag_val['form_name']}' {$form_css} src='{$value}' data-input-id='{$tag_val['form_name']}' data-src-id='src_{$tag_val['form_name']}' data-rel='tooltip' title='点击上传图片' />";
                    $parse_str .= "<span class='help-inline col-xs-3'>";
                    $parse_str .= "{$span_str}{$tag_val['tip_text']}</span>";
                }
                else{
                    $parse_str .= !$is_form_control?"<div class='col-xs-9'>":"<div class='col-xs-6'>";
                    $parse_str .= "<input id='{$tag_val['form_name']}' {$form_type} {$form_name} {$default_val} {$placeholder} {$is_disabled} {$is_read} {$is_required} {$maxlength} {$max} {$min} {$autocomplete} {$pattern} {$tip_text} {$form_css}/>";
                    if (!$is_form_control){
                        $parse_str .= "<span class='help-inline col-xs-3'>";
                        $parse_str .= "{$span_str}{$tag_val['tip_text']}</span>";
                    }
                }
                $parse_str .= "</div></div>";                
            }
            else {
                $parse_str .= "<input {$form_type} {$form_name} {$default_val}/>";
            }
        }
        if ($parse_str){
            return $parse_str;
        }
        else{
            return false;
        }    
    }
    
    /**
     * 下拉列表
     * @param 标签 $tag
     * @param 内容 $content
     */
    public function tagSelect($tag,$content){
        
    }
    
    /**
     * 文本域
     * @param unknown $tag
     * @param unknown $content
     */
    public function tagArea($tag,$content){
        
    }
    
    /**
     * 页面显示条数
     * @param unknown $tag
     * @param unknown $content
     */
    public function tagPageNum($tag,$content){
        $name=empty($tag['field_name'])?'page_num':$tag['field_name'];
        $width=empty($tag['width'])?'':"style='width:{$tag['width']}'";
        $class=empty($tag['class_name'])?'ajax-change select2':'ajax-change select2 '.$tag['class_name'];
        $select_id=empty($tag['selector'])?'selector':$tag['selector'];
        $select_num=empty($tag['select_num'])?config('paginate.list_rows'):$tag['select_num'];
        $default_page_num = config('paginate.list_rows');
        $tpl="<select name='{$name}' class='{$class}' {$width}>";
        $tpl.="<option value='{$default_page_num}' {if condition='{$default_page_num}=={$select_num}'}selected='selected'{/if}>每页{$default_page_num}条</option>";
        $tpl.="<option value='15' {if condition='15=={$select_num}'}selected='selected'{/if}>每页15条</option>";
        $tpl.="<option value='20' {if condition='20=={$select_num}'}selected='selected'{/if}>每页20条</option>";
        $tpl.="<option value='50' {if condition='50=={$select_num}'}selected='selected'{/if}>每页50条</option>";
        $tpl.="<option value='100' {if condition='100=={$select_num}'}selected='selected'{/if}>每页100条</option>";
        $tpl.="</select>";
        return $tpl;
    }
    
    /**
     * 下拉列表查询标签
     * @param unknown $tag
     * @param unknown $content
     */
    public function tagScreen($tag,$content){
        if (empty($tag['table_name'])) exception('table_name 不存在！', 100006);
        $title=empty($tag['table_field'])?'title':$tag['table_field'];
        $width=empty($tag['width'])?'':"style='width:{$tag['width']}'";
        $select_val=empty($tag['select_val'])?'':$tag['select_val'];
        $is_multiple=empty($tag['is_multiple'])?false:true;
        $select_option="";
        if ($is_multiple){
            $class_name=empty($tag['class_name'])?'select2':'select2 '.$tag['class_name'];
            $option = "multiple title='条件筛选···'";
            $select_option="<option value='0'>条件筛选···</option>";
            $field_name=empty($tag['field_name'])?'':"name='{$tag['field_name']}[]'";
        }
        else{
            if(!empty($tag['is_edit'])){
                $class_name=empty($tag['class_name'])?'select2':'select2 '.$tag['class_name'];
            }
            else{
                $class_name=empty($tag['class_name'])?'ajax-change select2':'ajax-change select2 '.$tag['class_name'];
                $select_option="<option value='0'>条件筛选···</option>";
            }
            $option = " ";
            $field_name=empty($tag['field_name'])?'':"name='{$tag['field_name']}'";
        }
        if(empty($tag['selector'])){
            $selector=(!empty($tag['field_name']))?"id='{$tag['field_name']}'":null;
        }
        else{
            $selector="id='{$tag['selector']}'";
        }
        $data_list = db($tag['table_name'])->where('admin_id',session('aid'))->select();
        $select_tpl='';
        $select_tpl.="<select {$field_name} class='{$class_name}' {$width} data-placeholder='点击选择···' {$selector} {$option}>";
        $select_tpl.=$select_option;
        if ($data_list){
            foreach ($data_list as $k=>$v){
                if (isset($v['is_disabled'])){
                    if ($v['is_disabled']==1)unset($data_list[$k]);
                }
                if (isset($v['is_enabled'])){
                    if ($v['is_enabled']==0 || empty($v['is_enabled']))unset($data_list[$k]);
                }
                if (isset($v['is_delete'])){
                    if ($v['is_delete']==1)unset($data_list[$k]);
                }
            }
            foreach($data_list as $key=>$val){
                $select_tpl.="<option value='{$val['id']}' {in name='{$val['id']}' value='{$select_val}'}selected='selected'{/in}>{$val[$title]}</option>";
            }
            $select_tpl.="</select>";
            
            if ($select_tpl){
                return $select_tpl;
            }
            else{
                return false;
            } 
        }
        else{
            return false;
        }
    }
    
    /**
     * 表格标签
     * @param unknown $tag
     * @param unknown $content
     */
    public function tagTable($tag,$content){
        
    }
    
    /**
     * ID兑换对应的名称
     * @param unknown $tag
     * @param unknown $content
     */
    public function tagIdData($tag,$content){
        if (empty($tag['table_name'])) exception('table_name 不存在！', 100006);
        if (empty($tag['id'])) exception('id 不存在！', 100006);
        print_r($tag);die;
        $filed=empty($tag['get_name'])?'name':$tag['get_name'];
        $name=db($tag['table_name'])->where('id',$tag['id'])->value($filed);
        return empty($name)?'【未定义】':$name;
    }
}




