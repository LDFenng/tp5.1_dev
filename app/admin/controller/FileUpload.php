<?php
// +----------------------------------------------------------------------
// | LDF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.yolaila.top All rights reserved.
// +----------------------------------------------------------------------
// | Author: LDF <898303969@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\File;
use think\Validate;
//权限认证
class FileUpload extends Base {  
    
    /**
     * 图片裁剪上传
     */
    public function uploadOneImg(){
        config('default_ajax_return','html');
        $this->assign('img_info',request()->param());
        return $this->fetch();
    }
    
    /**
     * 上传保存图片
     */
    public function saveUpload(){
        config('default_ajax_return','json');
        $file = request()->file("avatar_file"); //$_FILES['avatar_file'];// 
        $post_data = request()->param("avatar_data");
        $post_src =request()->param("avatar_src"); //原图片路径；
        //先上传原文件获取路径和文件唯一名称还有几本文件信息，再由从获取的路径进行裁剪图片
        $img_config = config('img_config');
        //$result = $this->validate(['file' => $file],['file'=>"require|fileExt:jpg,png,gif|fileSize:{$img_config['size']}"],['file.require' => '请选择上传文件', 'file.fileExt' => '非法图像文件','file.fileSize'=>"图像文件不得超过 {$img_config['size']}M"]);
        if (!empty($file)) {
            //$img_info = $file->getInfo();
            $get_path = str_replace("{sid}",$this->uid,$img_config['get_path']);
            $img_dir = $get_path.'/temp/';
            $img_validate = [
                'size'=>$img_config['size'],  //2M
                'ext'=>'jpg,png,gif'
            ];
            $validate=new Validate();
            $validate->setTypeMsg($img_validate,['上传图像不可大于'.format_bytes($img_config['size']),'图像格式仅支持：jpg,png,gif']);
            //判断是否存在文件夹，不存在创建
            $this->checkDir($img_dir);
            $file_data = pathinfo($post_src);
            //print_r($post_src);die;
            if (!empty($post_src) && file_exists($_SERVER['DOCUMENT_ROOT'].$post_src) &&
                !empty($file_data['basename'])){  //同名覆盖
                $real_path = realpath($_SERVER['DOCUMENT_ROOT'].$post_src);
                return $this->_cropImg($real_path, $img_dir, $post_data, $file_data['basename'], $file_data['extension'],$post_src,true);
            }
            else{
                $img_info = $file->validate($img_validate)->rule('uniqid')->move($img_dir);
                if ($img_info) {
                    //取出已保存的图片信息加以裁剪在代替保存
                    return $this->_cropImg($img_info->getRealPath(), $img_dir, $post_data,$img_info->getFilename(),$img_info->getExtension());
                }
                else {
                    return [
                        'code'=>0,
                        'msg'=>$file->getError()
                    ];
                }                
            }
        }
        else{
            return ['code'=>0,'msg'=>$this->_checkFileSize('avatar_file')];   
        }        
    }
     
    /**
     * 裁剪图片
     * @param $src 图片路径
     * @param $dst 保存路径
     * @param $data 图片裁剪信息
     */
    private function _cropImg($src, $dst,$post_data,$file_name,$type,$post_src=null,$is_old=false) {
        $data = json_decode($post_data);
        if (!empty($src) && !empty($dst) && !empty($data)) {
            switch ($type) {
                case 'gif':
                    $src_img = imagecreatefromgif($src);
                    break;
                case 'jpg':
                    $src_img = imagecreatefromjpeg($src);
                    break;    
                case 'png':
                    $src_img = imagecreatefrompng($src);
                    break;
            }             
            if (!$src_img) {
                return [
                    'code'=>0,
                    'msg'=>'图片不存在！'
                ];
            }        
            $size = getimagesize($src);
            $size_w = $size[0]; // 图片上传原宽度
            $size_h = $size[1]; // 图片上传原高度
            $src_img_w = $size_w;
            $src_img_h = $size_h;   
            $degrees = $data -> rotate;
            // print_r($degrees);die;
            // 图片旋转
            if (is_numeric($degrees) && $degrees != 0) {
                // PHP's degrees is opposite to CSS's degrees
                $new_img = imagerotate($src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );
                imagedestroy($src_img);
                $src_img = $new_img; 
                $deg = abs($degrees) % 180;
                $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;
                $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);  //旋转之后的宽度
                $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);  //旋转之后的高度 
                // Fix rotated image miss 1px issue when degrees < 0
                $src_img_w -= 1;
                $src_img_h -= 1;
            }
            
            $tmp_img_w = $data -> width;  
            $tmp_img_h = $data -> height; 
            $dst_img_w = $data -> width; //220;
            $dst_img_h = $data -> height; //220;
            $src_x = $data -> x; //图片裁剪起始X坐标
            $src_y = $data -> y; //图片裁剪起始Y坐标
    
            if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
                $src_x = $src_w = $dst_x = $dst_w = 0;
            } else if ($src_x <= 0) {
                $dst_x = -$src_x;
                $src_x = 0;
                $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
            } else if ($src_x <= $src_img_w) {
                $dst_x = 0;
                $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
            }
    
            if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
                $src_y = $src_h = $dst_y = $dst_h = 0;
            } else if ($src_y <= 0) {
                $dst_y = -$src_y;
                $src_y = 0;
                $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
            } else if ($src_y <= $src_img_h) {
                $dst_y = 0;
                $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
            }
    
            // 图片缩放比 原始宽度/裁剪宽度
            $ratio = $tmp_img_w / $dst_img_w;
            $dst_x /= $ratio;
            $dst_y /= $ratio;
            $dst_w /= $ratio;
            $dst_h /= $ratio;   
            $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);              
            // Add transparent background to destination image
            imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
            imagesavealpha($dst_img, true);
            //$dst_img（目标图像）；$src_img（原图像）；$dst_x、$dst_y（目标图像的X、Y坐标）；$src_x、$src_y（原图像的X、Y坐标）
            $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);           
            if ($result) {
                $file_name = uniqid(msectime()).'.'.$type;
                $file_path = $this->getSavePath();
                $src_path = $this->_create_file($file_path);
                $new_src = $src_path.'/'.$file_name;
                switch ($type) {
                    case 'gif':
                        $img_result = imagegif($dst_img, $new_src);
                        break;
                    case 'jpg':
                        $img_result = imagejpeg($dst_img, $new_src);
                        break;
                    case 'png':
                        $img_result = imagepng($dst_img, $new_src);
                        break;
                    case 'bmp':
                        $img_result = imagewbmp($dst_img, $new_src);
                        break;
                }
                //是否开启水印功能 
                $this->createImgWater($new_src);
                //创建缩略图
                $thumb_src=$this->createThumb($new_src, $file_name);
                if ($is_old) @unlink($_SERVER['DOCUMENT_ROOT'].$post_src);
                imagedestroy($src_img);
                imagedestroy($dst_img);
                if (!$img_result) {
                    return [
                        'code'=>0,
                        'msg'=>'裁剪失败，重新裁剪'
                    ];                    
                }
                else{
                    return [
                        'code'=>1,
                        'img_url'=>get_host().__ROOT__.'/'.$this->getSavePath().'/'.$file_name,
                        'thumb_url'=>$thumb_src?get_host().__ROOT__.'/'.$thumb_src:null
                    ];
                }
            }
            else {
                return [
                    'code'=>0,
                    'msg'=>'裁剪失败，重新裁剪'
                ];
            }
        }
    }
    
    /**
     * 多图片上传
     */
    public function multiUpload(){
        config('default_ajax_return','json');
        $files = request()->file('multi_file');
        //$data = request()->param('file');
        $img_dir = ROOT_PATH . 'data' . DS . 'upload/'.$this->uid.'/img/'.date('Ymd'); //保存路径
        $img_config = [
            'size'=>2097152,  //2M
            'ext'=>'jpg,png,gif'
        ];
        $img_info = $files->validate($img_config)->move($img_dir,'');
        if($img_info){
            // 输出 jpg
            return [
                'code'=>1,
                'img_url'=>get_host().__ROOT__.'/data/upload/'.$this->uid.'/img/'.date('Ymd').'/'.iconv("gb2312","UTF-8", $img_info->getFilename())
            ];
        }else{
            // 上传失败获取错误信息
            return [
                'code'=>0,
                'msg'=>iconv("gb2312","UTF-8", $img_info->getError())
            ];
        }
    }
    
    /**
     * 多图片上传删除
     */
    public function delMutilData(){
        if (!request()->isAjax()){
            $this->error('提交方式错误');
        }
        else{
            config('default_ajax_return','json');
            $file_name=input('post.file_name',null);
            if (empty($file_name)){
                return [
                    'code'=>0,
                    'msg'=>'请选择删除文件'
                ];
            }
            switch (input('post.type','img')){
                case 'img':
                    $data_dir = ROOT_PATH . 'data' . DS . 'upload/'.$this->uid.'/img/'.date('Ymd');
                    break;
                case 'file':
                    $data_dir = ROOT_PATH . 'data' . DS . 'upload/'.$this->uid.'/file/'.date('Ymd');
                    break;
            }
            $file = iconv('UTF-8','GB2312',$data_dir.'/'.$file_name);
            if (!file_exists($file)){
                return [
                    'code'=>0,
                    'msg'=>'图片不存在或者已删除'
                ];
            }
            if (unlink($file)){
                return [
                    'code'=>1,
                    'msg'=>'操作成功'
                ];
            }
        }
    }
    
    /**
     * 创建图片水印
     */
    private function createImgWater($img_src){
        $water_config=$this->getPicConfig(1);
        if ($water_config){
            if (!$water_config['is_water'])return false;
            $water_type=$water_config['water_type'];
            $img_info=\think\Image::open($img_src);
            switch ($water_type){
                case 1:
                    $img_info->water($water_config['src'],$water_config['local'],$water_config['alpha'])->save($img_src);
                    break;
                case 2:
                    $img_info->text($water_config['text'],$water_config['font'] ,$water_config['size'], $water_config['color'], $water_config['local'],0,$water_config['angle'])->save($img_src);
                    break;
                default:return false;
            }
        }
    }
    
    /**
     * 创建缩列图
     */
    private function createThumb($img_path,$file_name){
        $thumb_config=$this->getPicConfig(2);
        if ($thumb_config){
            //if(!$thumb_config['is_thumb'])return false;
            $img_config = config('img_config');
            $thumb_path = str_replace("{sid}",$this->uid,$img_config['thumb_path']);
            $this->_create_file($thumb_path);
            $thumb_save = $thumb_path.date('Ymd');
            $this->_create_file($thumb_save);
            $image = \think\Image::open($img_path);
            $max_width=$thumb_config['max_width']; //最大宽度
            $max_height =$thumb_config['max_height']; //最大高度
            $thumb_type = $thumb_config['thumb_type'];  //缩略图裁剪类型
            $image->thumb($max_width, $max_height,$thumb_type)->save($thumb_save.'/'.$file_name);
            return $thumb_save.'/'.$file_name;
        }
    }
    
    /**
     * 获取水印数据或者略缩图
     * @param 常量 $type;1为水印；2为略缩图
     */
    private function getPicConfig($type){
        $water_data=false;
        $thumb_data=false;
        if (cache('water_data_'.$this->uid) && cache('thumb_data_'.$this->uid)){
            $water_data=cache('water_data_'.$this->uid);
            $thumb_data=cache('thumb_data_'.$this->uid);
        }
        else{
            $pic_config=db('pic_config')->where('admin_id',$this->uid)->find();
            if ($pic_config){
                if (!empty($pic_config['water_data'])){
                    $water_data=json_decode($pic_config['water_data'],true);
                    $water_data['is_water']=$pic_config['is_water'];
                    $water_data['water_type']=$pic_config['water_type'];
                    cache('water_data_'.$this->uid,$water_data,3600);
                }
                if (!empty($pic_config['thumb_data'])){
                    $thumb_data=json_decode($pic_config['thumb_data'],true);
                    cache('thumb_data_'.$this->uid,$thumb_data,3600);
                }
            }
            return false;
        }
        switch ($type){
            case 1:return $water_data;break;
            case 2:return $thumb_data;break;
        }
    }
    
    /**
     * 文件创建
     */
    private function _create_file($file_path){
        if (is_dir($file_path)){           
            return realpath($file_path);
        }
        else{
            $mk_result = mkdir($file_path);
            chmod($file_path,0777);
            if ($mk_result){
                return $file_path;
            }
            else{
                return [
                    'code'=>0,
                    'msg'=>'创建文件夹失败'
                ];
            }
        }
    }
    
    /**
     * 检查目录是否可写
     * @param  string   $path    目录
     * @return boolean
     */
    protected function checkDir($path){
        if (is_dir($path)) {
            return true;
        }
        if (mkdir($path, 0755, true)) {
            return true;
        } else {
            return ['code'=>0,'msg'=>"目录 {$path} 创建失败！"];
            return false;
        }
    }
    
    /**
     * 获取保存路径
     */
    private function getSavePath(){
        $img_config = config('img_config');
        $get_path = str_replace("{sid}",$this->uid,$img_config['get_path']);
        return $get_path.date('Ymd'); //保存路径
    }
    
    /**
     * 文件上传
     */
    public function filesUpload(){  
        $file_info = request()->param();
        $this->assign('file_info',$file_info);
        return $this->fetch();
    }
    
    /**
     * 文件上传处理
     */
    public function handleFile(){
        config('default_ajax_return','json');
        $file = request()->file('file_name');
        $file_type = input('file_type');
        if (!empty($file)){
            switch ($file_type){
                case 'video':
                    $file_config = config('video_config');
                    $file_config['type']=['.mp4','.flv','.rmvb','.mpg','.mov','.avi'];
                    $type='video';
                    break;
                case 'audio':
                    $file_config = config('audio_config');
                    $type='audio';
                    break;
                default:
                    $file_config = config('file_config');
                    $type='file';
                    break;
            }
            $file_dir = str_replace("{sid}",$this->uid,$file_config['get_path']).date('Ymd');
            $ext_type=[];
            if($file_config['type']){
                foreach ($file_config['type'] as $val){
                    $ext_type[]=str_replace(".",'',$val);
                }
            }
            $file_validate = [
                'size'=>$file_config['size'],
                'ext'=>implode(',', $ext_type)
            ];
            $file_info = $file->validate($file_validate)->move($file_dir,'');
            if ($file_info) {
                $path = $file_dir.'/'.$file_info->getFilename();
                $getId3 = new \GetId3\GetId3Core();
                $file_data = $getId3
                ->setEncoding('UTF-8')
                ->setOptionMD5Data(true)
                ->setOptionMD5DataSource(true)
                ->analyze($path);
                //提取文件信息
                if ($file_type=='video'){
                    //convertToFlv($path, "output.jpg" ); //截屏权限不够 暂不用
                    //die;
                }
                $file_name=$file_info->getBasename('.'.$file_info->getExtension());
                $upload_info=[
                    'size'=>$file_info->getSize(), //字节
                    'suffix'=>$file_info->getExtension(),
                    'file_name'=>iconv("gbk//ignore","UTF-8", $file_name)
                ];
                if (!isset($file_data['error'])){
                    if (isset($file_data['audio'])){
                        $upload_info['bitrate']=empty($file_data['bitrate'])?'':$file_data['bitrate']; //比特率
                    }
                    if (isset($file_data['video'])){
                        $upload_info['resolution_x']=empty($file_data['resolution_x'])?'':$file_data['resolution_x'];
                        $upload_info['resolution_y']=empty($file_data['resolution_y'])?'':$file_data['resolution_y'];
                    }
                    $upload_info['play_time']=empty($file_data['playtime_string'])?'':$file_data['playtime_string']; //时长
                    if (isset($file_data['tags'])){
                        if (!empty($file_data['tags']['id3v2'])){
                            $id3v2=$file_data['tags']['id3v2'];
                            $upload_info['album']=empty($id3v2['album'])?'':$id3v2['album']; //专辑
                            $upload_info['name']=empty($id3v2['title'])?'':$id3v2['title']; //标题
                            $upload_info['artist']=empty($id3v2['artist'])?'未知':$id3v2['artist'];  //艺术家
                            $upload_info['band']=empty($id3v2['band'])?'':$id3v2['band']; //乐队
                        }
                    } 
                }
                return [
                    'code'=>1,
                    'file_info'=>$upload_info,
                    'file_url'=>get_host().__ROOT__.'/data/upload/'.$this->uid.'/'.$type.'/'.date('Ymd').'/'.iconv("gbk//ignore","UTF-8", $file_info->getFilename())
                ];
            }
            else{
                return [
                    'code'=>0,
                    'msg'=>$file->getError()
                ];
            }
        }
        else{
            return ['code'=>0,'msg'=>$this->_checkFileSize('file_name')];
        }
    }
    
    /**
     * 图片先压缩再上传
     */
    public function imgCompress(){
        if (request()->isPost()){
            config('default_ajax_return','json');
            $data = request()->post();
            header('Content-type:text/html;charset=utf-8');
            //匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data['img_data'], $result)){
                $type = $result[2];
                $img_config = config('img_config');
                if (!in_array('.'.$type,$img_config['type'])){
                    return ['code'=>0,'msg'=>'图片格式错误'];
                }
                if ($data['size']>$img_config['size']){
                    return ['code'=>0,'msg'=>'图片大小不得超过'.format_bytes($img_config['size'])];
                }
                $file_path = $this->getSavePath();
                $new_file = $this->_create_file($file_path);
                $file_name = msectime().".{$type}";
                $new_file = $new_file.'/'.$file_name;
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $data['img_data'])))){
                    return [
                        'code'=>1,
                        'img_url'=>get_host().__ROOT__.'/data/upload/'.$this->uid.'/img/'.date('Ymd').'/'.$file_name,
                        'size'=>$data['size']
                    ];
                }else{
                    return [
                        'code'=>0,
                        'msg'=>'上传失败，重新操作'
                    ];
                }
            } 
        }
    }
    
    private function _checkFileSize($file_name){
        switch($_FILES[$file_name]['error']) {
            case 1:$msg="文件大小超出了服务器的空间大小";break;
            case 2: $msg="上传的文件大小超出浏览器限制";break;
            case 3:
            case 4:
            case 5:
            case 6:$msg="请重新选择上传文件";break;
        }
        return $msg;
    }
    
    /**
     * 富文本编辑器
     */
    public function requestUE(){
        $handler = new \UEditor\UEditor();
        $handler->request($this->uid);
    }
    
    /**
     * 获取素材
     */
    public function materialFile(){
        $z=request()->param('z');
        switch ($z){
            case 'video': //视频
                $material_data=$this->materialData('video');
                break;
            case 'audio': //音频
                $material_data=$this->materialData('audio');
                break;
            case 'img':  //图片
                $material_data=$this->materialData('img');
                break;
        }
        $show=show_page($material_data['data_list']->render(),'modal-page');
        $this->assign('data_date',page_array(config('paginate.list_rows'), 0, $material_data['data_date']));
        $totals=count($material_data['data_date']);
        $countpage=ceil($totals/config('paginate.list_rows'));
        $this->assign('date_page',show_page_ui($countpage, url('admin/FileUpload/MaterialDatePage')));
        $this->assign('data_list',$material_data['data_list']);
        $this->assign('page',$show);
        return $this->fetch();
    }
    
    /**
     * 分页获取素材资料
     */
    public function getMaterialData(){
        $type=request()->param('type');
        switch ($type){
            case 'video':
                $table_name='video_data';
                $filed='id,name,size,type,path_url,play_time,create_time';
                break;
            case 'audio':
                $table_name='audio_data';
                $filed='id,name,size,type,path_url,play_time,create_time';
                break;
            case 'img':
                $table_name='img_data';
                $filed='id,name,size,type,path_url,create_time';
                break;
        }
        $update_time=strtotime(input('date',date('Y-m-d')));
        $material_condition=['admin_id'=>$this->uid];
        $data_list = db($table_name)->where($material_condition)
        ->whereTime('create_time','between',[strtotime(date('Y-m-d '.'00:00:00',$update_time)),strtotime(date('Y-m-d '.'23:59:59',$update_time))])
        ->field($filed)->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
        $show=show_page($data_list->render(),'modal-page');
        $this->assign('data_list',$data_list);
        $this->assign('page',$show);
        return $this->fetch('ajaxMaterialFile');
    }
    
    
    /**
     * 分页获取素材日期
     * @param number $page
     */
    public function MaterialDatePage(){
        $data=request()->param();
        $page=input('page',1);
        $type=$data['type'];
        if(cache('data_date_'.$type.'_'.$this->uid)){
            $data_date=cache('data_date_'.$type.'_'.$this->uid);
        }
        else{
            $material_data=$this->materialData($type);
            $data_date=$material_data['data_date'];
        }
        $this->assign('data_date',page_array(config('paginate.list_rows'), $page, $data_date));
        $totals=count($data_date);
        if ($page==$totals){
            config('default_ajax_return','json');
            return ['code'=>'0','msg'=>'无更多数据'];
        }
        $countpage=ceil($totals/config('paginate.list_rows')); #计算总页面数
        $this->assign('date_page',show_page_ui($countpage, url('admin/FileUpload/MaterialDatePage')));
        return $this->fetch('ajaxMaterialDate');
    }
    
    /**
     * 获取音频、视频数据
     * @param unknown $type
     */
    private function materialData($type){
            //读取缓存 判断
        if(cache('data_date_'.$type.'_'.$this->uid) && cache('data_list_'.$type.'_'.$this->uid)){
            $data_date=cache('data_date_'.$type.'_'.$this->uid);
            $data_list=cache('data_list_'.$type.'_'.$this->uid);
        }
        else{
            switch ($type){
                case 'video':
                    $table_name='video_data';
                    $filed='id,name,size,type,path_url,play_time,create_time';
                    break;
                case 'audio':
                    $table_name='audio_data';
                    $filed='id,name,size,type,path_url,play_time,create_time';
                    break;
                case 'img':
                    $table_name='img_data';
                    $filed='id,name,size,type,path_url,create_time';
                    break;
            }
            $material_condition=['admin_id'=>$this->uid];
            $update_max_time = db($table_name)->where($material_condition)->max('create_time');
            $update_min_time = db($table_name)->where($material_condition)->min('create_time');
            $diff_days=diff_between_days($update_min_time, $update_max_time,false);
            $data_date=[];
            for($i=0;$i<=$diff_days;$i++){
                $data=db($table_name)->where($material_condition)
                ->whereTime('create_time','between',[strtotime(date('Y-m-d'. '00:00:00',$update_max_time)."-{$i} day"),strtotime(date('Y-m-d '. '23:59:59',$update_max_time)."-{$i} day")])
                ->field('id')->find();
                if ($data){
                    $data_date[]=[
                        'index_id'=>$i,
                        'upload_date'=>date('Y-m-d',strtotime(date('Y-m-d',$update_max_time)."-{$i} day"))
                        ];
                }
            }
            cache('data_date_'.$type.'_'.$this->uid,$data_date);
            $data_list = db($table_name)->where($material_condition)->whereTime('create_time','between',[strtotime(date('Y-m-d '.'00:00:00',$update_max_time)),strtotime(date('Y-m-d '.'23:59:59',$update_max_time))])
            ->field($filed)->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
            cache('data_list_'.$type.'_'.$this->uid,$data_list);
        }
        return ['data_date'=>$data_date,'data_list'=>$data_list];
    }
    
    public function imgFileList(){
        $date_list=$this->getImgDateList();
        $file_name=$date_list?$date_list[0]['file_name']:null;
        $img_list=$this->getImgInfo($file_name);
        $this->assign('data_date',page_array(config('paginate.list_rows'), 0, $date_list));
        $totals_date=count($date_list);
        $countpage_date=ceil($totals_date/config('paginate.list_rows'));
        $this->assign('date_page',show_page_ui($countpage_date, url('admin/FileUpload/imgDatePage')));
        
        $this->assign('data_list',page_array(config('paginate.list_rows'), 0, $img_list));
        $totals_img=count($img_list);
        $countpage_img=ceil($totals_img/config('paginate.list_rows'));
        $this->assign('page',show_page_ui($countpage_img, url('admin/FileUpload/getImgData'),'modal-page'));
        
        return $this->fetch();
    }
    
    public function imgDatePage(){
        $page=request()->param('page');
        $date_list=$this->getImgDateList();
        $this->assign('data_date',page_array(config('paginate.list_rows'), $page, $date_list));
        $totals_date=count($date_list);
        $countpage_date=ceil($totals_date/config('paginate.list_rows'));
        if ($page==$totals_date){
            config('default_ajax_return','json');
            return ['code'=>'0','msg'=>'无更多数据'];
        }
        //print_r(page_array(1, $page, $date_list));die;
        $this->assign('date_page',show_page_ui($countpage_date, url('admin/FileUpload/imgDatePage')));
        return $this->fetch('ajaxMaterialDate');
    }
    
    public function getImgData(){
        $data=request()->param();
        $file_date=date('Ymd',strtotime($data['date']));
        $img_list=$this->getImgInfo($file_date);
        $this->assign('data_list',page_array(config('paginate.list_rows'), $data['page'], $img_list));
        $totals_img=count($img_list);
        $countpage_img=ceil($totals_img/config('paginate.list_rows'));
        $this->assign('page',show_page_ui($countpage_img, url('admin/FileUpload/getImgData'),'modal-page'));
        return $this->fetch('ajaxMaterialFile'); 
    }
    
    public function delImg(){
        $src=request()->param('src');
        del_file($src);
    }
    
    private function getImgInfo($date){
        if (cache('img_list_'.$date.'_'.$this->uid)){
            $img_list=cache('img_list_'.$date.'_'.$this->uid);
        }
        else{
            $img_data=[];
            $img_config = config('img_config');
            $img_dir = str_replace("{sid}",$this->uid,$img_config['get_path']);
            $file_date= $date;  //$date_list[0]['file_name'];
            $dir = $img_dir.$file_date;
            $src = get_host().__ROOT__.'/'.$img_dir.$file_date;
            //print_r($dir);die;
            if (is_dir($dir)) {
                if (opendir($dir)) {
                    $dh = opendir($dir);
                    $i=uniqid();
                    while (($file = readdir($dh)) !== false) {
                        if ($file!="." && $file!="..") {
                            $img_data[] = [
                                'id'=>$i++,
                                'name'=>$file,
                                'path_url'=>$src.'/'.$file,
                                'data_type'=>'img',
                                'size'=>filesize($img_dir.$file_date.'/'.$file)
                            ];
                        }
                    }
                    closedir($dh);
                }
            }
            cache('img_list_'.$date.'_'.$this->uid,$img_data,3600);
            $img_list=$img_data;
        }
        return $img_list;
    }
    
    private function getImgDateList(){
        if(cache('img_date_'.$this->uid)){
            return cache('img_date_'.$this->uid);
        }
        else{
            $img_config = config('img_config');
            $img_dir = str_replace("{sid}",$this->uid,$img_config['get_path']);
            $file_names=[];
            if (is_dir($img_dir)){
                if (opendir($img_dir)){
                    $dh=opendir($img_dir);
                    while (($file = readdir($dh)) !== false) {
                        if (is_numeric($file)) $file_names[]=$file;
                    }closedir($dh);
                }
                $date_list=[];
                if (!empty($file_names)){
                    //匹配日期
                    foreach ($file_names as $key=>$date){
                        $date_list[]=[
                            'index_id'=>$key,
                            'upload_date'=>substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2),
                            'file_name'=>$date
                        ];
                    }
                    $date_list=list_sort_by($date_list,'file_name','desc');
                    cache('img_date_'.$this->uid,$date_list,3600);
                    return $date_list;
                } 
            }
        }
    }
}