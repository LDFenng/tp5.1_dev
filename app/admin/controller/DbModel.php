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
use think\Config;
use think\helper\Time;
use think\Validate;
//use PhpOffice\phpOffice;
class DbModel extends Base{
    
    /**
     * 模型列表所有数据表
     */
    public function modelList(){
        $page=input('page',config('paginate.list_rows'));
        $model_list = db('model')->paginate($page,false,['query'=>get_query()]);
        $show = show_page($model_list->render());
        $this->assign('page',$show);
        $this->assign('model_list',$model_list);
        return $this->fetch();
    }
    
    /**
     * 数据表字段编辑
     */
    public function editModel($model_id=null){
        $modal_info = db('model')->where('id',$model_id)->find();
        $field_list = json_decode($modal_info['fields'],true);
        //print_r($field_list);die; 
        $this->assign('model_info',$modal_info);
        $this->assign('field_list',$field_list);
        return $this->fetch();
    }
    /**
     * 复制数据表字段
     */
    public function copyModel($model_id=null){
        $modal_info = db('model')->where('id',$model_id)->find();
        unset($modal_info['id']);
        $field_list = json_decode($modal_info['fields'],true);
        $this->assign('model_info',$modal_info);
        $this->assign('field_list',$field_list);
        return $this->fetch('editModel');
    }
    
    public function changeModelStatus(){
        return $this->isEnabled('model');
    }
    
    /**
     * 保存模型
     */
    public function saveModel(){
        if (request()->isAjax()){
            config('default_ajax_return','json');
            $data = request()->param();
            static $db = null;
            static $db_prefix = null;
            $db_prefix = config('database.prefix');
            if (null === $db) {
                $db = Db::connect([], true);
            }
            $model_id = input('id', 0);
            $old_model=$db->name('model')->where('id',null)->find();
            $rule = [
                ['name','require','请设置模型名|模型表已经存在'],
                ['pk','require','请设置主键'],
                ['engine','require|in:MyISAM,InnoDB','请选择引擎|非法引擎'],
                ['order','require','请设置排序字段']
            ];  
            $validate = new Validate($rule);
            //修改字段 ALTER TABLE manual_record CHANGE `Note-sort` `Note_sort` varchar(50) DEFAULT NULL; 
            //字段数组,2维[0][字段名,字段标题,字段类型,字段数据,字段说明,字段长度,字段规则,默认值]
            $fields=input('fields');
            $model_fields=$fields?$this->fields(json_decode($fields,true)):[];
            if (empty ($model_fields)) {
                return ['code'=>0,'msg'=>'请设置模型字段'];
            }
            $fields=json_encode($model_fields);
            $result = $validate->check($data);
            if ($result){
                //保存到model数据表中
                $save_data=[
                    'name'=>$data['name'],
                    'title'=>$data['title'],
                    'pk'=>$data['pk'],
                    'order'=>$data['order'],
                    'sort'=>$data['sort'],
                    'fields'=>$fields,
                    'display_field'=>$data['display_field'],
                    'edit_field'=>$data['edit_field'],
                    'index_field'=>$data['index_field'],
                    'search_field'=>$data['search_field'],
                    'is_enabled'=>empty(input('is_enabled'))?0:1,
                    'engine'=>$data['engine']
                ];
                if ($old_model){
                    $old_table=$old_model['name'];
                    $save_data['update_time']=time();
                    if(db_is_valid_table_name($old_table,db_get_tables())){
                        //备份
                        $path=ROOT_PATH.'data/backup/';
                        if (!file_exists($path)) {
                            @mkdir($path,0777,true);
                        }
                        $content=db_get_insert_sqls($old_table);
                        file_put_contents($path.$db_prefix.$old_table.'.sql', $content);
                        //修改为临时文件名
                        if($db->execute("RENAME TABLE `$db_prefix$old_table` TO `$db_prefix$old_table"."_temp`;")!==false){
                            $old_table=$old_table.'_temp';
                        }
                    }
                }
                else{
                    $save_data['update_time']=time();
                    $save_data['create_time']=time();
                    $model_id=$db->name('model')->insertGetId($save_data);
                    if($model_id==false){
                        return ['code'=>0,'msg'=>'创建模型失败'];
                    }
                }            
                //删除 原表重建 
                $model_name = $data['name'];
                $sql="DROP TABLE IF EXISTS `$db_prefix$model_name`;";
                $db->execute($sql);
                switch (config('database.type')) {
                    case 'mysql' :
                        //重新创建
                        $model_pk = $data['pk'];
                        $model_engine = $data['engine'];
                        $sql = ("CREATE TABLE `$db_prefix$model_name` (
                        `$model_pk` INT(32) UNSIGNED NOT NULL AUTO_INCREMENT,
                        %FIELDS_SQL%
                        %PRIMARY_KEY_SQL%
                        %UNIQUE_KEY_SQL%
                        %KEY_SQL%
                        )ENGINE=$model_engine AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
                        $sql_fields = [];
                        $sql_primary_key = "PRIMARY KEY (`$model_pk`)";
                        $sql_unique_key = [];
                        $sql_key = [];
                        foreach ($model_fields as $f=>$fi) {
                            $rules = explode(',', str_replace(' ', '', $fi ['rules']));
                            $length=($fi['type']=='VARCHAR')?'255':'';
                            $length=($fi['type']=='TEXT')?'0':$fi ['length'];
                            $funsigned = (in_array('unsigned', $rules))?'UNSIGNED':null;
                            $fnull = (in_array('required', $rules))?'NOT NULL':null;
                            $default=$fi['default'];
                            if ($fnull=='NOT NULL'){
                                $default_val=NULL;
                            }
                            else{
                                $default_val=empty($default)?"DEFAULT NULL":"DEFAULT '{$default}'";
                            }
                            $description=empty($fi['description'])?'':"COMMENT '{$fi['description']}'";
                            $type=strtolower($fi['type']);
                            $sql_fields [] = "`{$fi['name']}` {$type}({$length}) {$funsigned} {$fnull} {$default_val} {$description}";
                            if (in_array('unique', $rules))$sql_unique_key [] = "UNIQUE KEY {$fi['name']} ({$fi['name']})";                           
                        }
                        $sql_fields [] = "`create_time` INT(11) NULL DEFAULT NULL";
                        $sql_fields [] = "`update_time` INT(11) NULL DEFAULT NULL";
                //索引数组
                $model_indexes=empty($data['index_field'])?null:explode(',',$data['index_field']);
                //print_r($data['index_field']);die;
                if (!empty($model_indexes)) {
                    foreach ($model_indexes as $indexes) {
                        $sql_key[] = "INDEX {$model_name}_{$indexes} ({$indexes})";
                    }
                }
                //替换sql语句
                $sql = str_replace([
                    '%FIELDS_SQL%',
                    '%PRIMARY_KEY_SQL%',
                    '%UNIQUE_KEY_SQL%',
                    '%KEY_SQL%'
                ], [
                    join(",\n", $sql_fields) . ((empty ($sql_primary_key) && empty ($sql_unique_key) && empty ($sql_key)) ? '' : ",\n"),
                    $sql_primary_key . ((empty ($sql_primary_key) || (empty ($sql_unique_key) && empty ($sql_key))) ? '' : ",\n"),
                    join(",\n", $sql_unique_key) . ((empty ($sql_unique_key) || empty ($sql_key)) ? '' : ",\n"),
                    join(",\n", $sql_key)
                ], $sql);
                //print_r($sql);die;
                $rst=$db->execute($sql);
                //为表添加注释
                $sql_c="ALTER TABLE {$db_prefix}{$model_name} COMMENT='{$data['title']}'";
                $db->execute($sql_c);
                if($rst===false){
                    //改回原来的数据表
                    if ($old_model){
                        $db->execute("RENAME TABLE `$db_prefix$old_table` TO `$db_prefix{$old_model['name']}`;");
                    }
                    return ['code'=>0,'msg'=>'编辑模型失败'];
                }else{
                    if ($old_model){
                        $rst=$db->name('model')->where('id',$model_id)->update($save_data);
                        if($rst===false){
                            $sql="DROP TABLE IF EXISTS `$db_prefix$model_name`;";
                            $db->execute($sql);
                            $sql="RENAME TABLE `$db_prefix$old_table` TO `$db_prefix{$old_model['name']}`;";
                            $db->execute($sql);
                            return ['code'=>0,'msg'=>'编辑模型失败'];
                        }else{
                            //删除改名的数据表
                            $sql="DROP TABLE IF EXISTS `$db_prefix$old_table`;";
                            $db->execute($sql);
                        }
                    }                  
                }
                break;
                //TODO mysql以外数据类型
                default :
                    return ['code'=>0,'msg'=>'不支持的数据库类型'];
                }
                $msg = $old_model?'编辑模型成功，原数据已备份':'创建模型成功';
                return ['code'=>1,'msg'=>$msg,'url'=>url('admin/DbModel/modelList')];
            }
            else{
                return [
                    'code'=>0,
                    'msg'=>$validate->getError()
                ];
            }
        }
    }
    
    /**
     * model_fields处理
     */
    private function fields($fields){
        $rst=[];
        foreach($fields as $v){
            //[字段名,字段标题,字段类型,字段数据,字段说明,字段长度,字段规则,默认值]
            $rst[$v[0]]=[
                'name'=>$v[0],
                'title'=>$v[1],
                'type'=>$v[2],
                'data'=>$v[3],
                'description'=>$v[4],
                'length'=>$v[5],
                'rules'=>$v[6],
                'default'=>$v[7]
            ];
        }
        return $rst;
    }
    
    /**
     * 删除模型     
     */
    public function delModel(){
        config('default_ajax_return','json');
        $model_ids=input('ids/a');
        foreach ($model_ids as $id_val){
            $model_info=db('model')->where('id',$id_val)->find();
            if (empty($model_info)){
                return ['code'=>0,'msg'=>'ID为'.$id_val.'不存在参数'];
            }else{
                //备份
                static $db = null;
                static $db_prefix = null;
                $db_prefix = config('database.prefix');
                if (null === $db) {
                    $db = Db::connect([], true);
                }
                $tablename=$model_info['name'];
                $path=ROOT_PATH.'data/backup/';
                if (!file_exists($path)) {
                    @mkdir($path,0777,true);
                }
                $content=db_get_insert_sqls($model_info['name']);
                file_put_contents($path.$db_prefix.$tablename.'.sql', $content);
                //删除
                $sql="DROP TABLE `$db_prefix$tablename`;";
                $rst=$db->execute($sql);
                if($rst===false){
                    return ['code'=>0,'msg'=>'模型删除失败！'];
                }
                //删模型
                $rst=db('model')->where('id',$id_val)->delete();
                if($rst!==false){
                    return ['code'=>1,'msg'=>'模型删除成功','url'=>url('admin/DbModel/modelList')];
                }else{
                    return ['code'=>0,'msg'=>'模型删除失败'];
                }
            }
        }
    }
    
    /**
     * 自定义表单列表
     */
    public function formList(){
        $form_condition = [
            'user_id'=>$this->user_id
        ];
        $data = request()->param();
        if (!empty($data['table_name']))$form_condition['table_name'] = $data['table_name'];
        $form_list = db('custom_form')->where($form_condition)->order('sort asc')
        ->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
        $show = show_page($form_list->render());
        $this->assign('page',$show);
        $this->assign('form_list',$form_list);
        //$table = new Log($this->user_id);
        //$table_list = $table->getAllTable();
        $this->assign('table_list',$table_list);
        return $this->fetch();
    }
    
    /**
     * 编辑表单列表
     */
    public function editForm($form_id=null){
        $form_condition = [
            'user_id'=>$this->user_id,
            'id'=>$form_id
        ];
        $form_info = db('custom_form')->where($form_condition)->find();
        if (!$form_info){
           // $table = new Log($this->user_id);
           // $table_list = $table->getAllTable();
            $this->assign('table_list',$table_list);
        }
        else{
            $field_arr = db('custom_form')->where('table_name',$form_info['table_name'])->column('form_name');
            foreach ($field_arr as $k=>$v){
                if (del_all_trim($v)==del_all_trim($form_info['form_name']))unset($field_arr[$k]);
            } //print_r($field_arr);die;
            $field_list = $this->getTableField($form_info['table_name'],$field_arr,$type=2);
           
            $this->assign('field_list',$field_list);
        }
        //print_r($form_info);die;
        $this->assign('form_info',$form_info);
        return $this->fetch();
    }
    
    /**
     * 获取数据表所需字段
     * @param unknown $t_name
     * @param unknown $field_arr
     * @return unknown|boolean
     */
    public function getTableField($t_name,$field_arr=null,$type=1){
        //$table = new Log($this->user_id);
        //$field_list = $table::getTableInfo($t_name);
        if ($field_list){
            foreach ($field_list as $field_key=>$field_val){
                if (!empty($field_arr)){
                    if (in_array($field_val['field_name'], $field_arr)){
                        unset($field_list[$field_key]);
                    }
                }
                if ($field_val['field_name']=='create_time' || $field_val['field_name']=='update_time'){
                    unset($field_list[$field_key]);
                }
            }
        }
        if ($field_list){
            if ($type==1){
                config('default_ajax_return','json');
                return $field_list;
            }
            else return $field_list; 
        }else{
            return false;
        }
    }
    
    public function changeFormStatus(){
        if (request()->isAjax()){
            $id=input('x');
            return $this->isEnabled('custom_form', $id);
        }
    }
    
    /**
     * 保存表单列表
     */
    public function saveForm(){
        config('default_ajax_return','json');
        $data = request()->param();
        $rule = [
            ['title','require','请填写名称'],
            ['table_name','require','请选择表名'],
            ['form_name','require','请选择字段'],
            ['form_type','require','请选择表单类型'],
            ['rows','>=:0|number','必须大于0|请填写数字'],
            ['cols','>=:0|number','必须大于0|请填写数字'],
            ['min','>=:0|number','必须大于0|请填写数字'],
            ['max','>=:0|number','必须大于0|请填写数字'],
            ['maxlength','>:0|number','必须大于0|请填写数字'],
            ['sort','>=:0|number','必须大于0|请填写数字']
        ];
        if (!empty($data['id'])){
            unset($rule[1]);
        }
        $validate = new Validate($rule);
        $result = $validate->check($data);
        if ($result){
            $data['is_hide']=empty($data['is_hide'])?0:1;
            $data['is_disabled']=empty($data['is_disabled'])?0:1;
            $data['is_read']=empty($data['is_read'])?0:1;
            $data['is_required']=empty($data['is_required'])?0:1;
            $data['is_multiple']=empty($data['is_multiple'])?0:1;
            $data['autocomplete']=empty($data['autocomplete'])?'off':'on';
            if ($data['form_type']=='image')$data['default_val']=default_img();
            return $this->saveData('custom_form', $data,url('admin/DbModel/formList'));
        }
        else{
            return [
                'code'=>0,
                'msg'=>$validate->getError()
            ];
        }
    }
    
    /**
     * 删除表单列表
     */
    public function delForm(){
        if(request()->isAjax()){
            $ids=input('ids/a');
            return $this->reallyDelete('custom_form',$ids);
        }
    }
    
    
}
