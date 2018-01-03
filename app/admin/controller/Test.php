<?php
namespace app\admin\controller;
use think\Request;
use think\Controller;
use think\helper\Time;
use Aliyun\Sms\Sms;
class Test extends Base
{
    public function index(){
        
        //print_r(config('not_check_action'));die;
        return $this->fetch();
    }
    
    public function alipay(){
        
        return $this->fetch();
    }
    
    public function upload(){
    

       
        return $this->fetch();
    }
    
    public function modal(){

        return $this->fetch();
    }
    
    public function bbb(){


        return $this->fetch();
    }
    
    public function ueditor(){
    
    
        return $this->fetch();
    }
    
    public function custom_form(){
    
    
        return $this->fetch();
    }
    
    public function sms(){
        $sms_option=[
            'sign_name'=>'林冬锋lin108219',
            'template_code'=>'SMS_96745013',
            'access_key_id'=>'LTAISSluKgIpiuhQ',
            'access_key_secret'=>'z5dEdUVVnBQF7XUNlA2YcpLiUqQOlP'
        ];
        $ali=new Sms($sms_option);
        $content=[
            'user_name'=>'林冬锋',
            'time'=>date('Y-m-d H:i:s')
        ];
        $send_result=$ali->sendSms('13613003160,18688072295,15302671731',$content);
        
    }
    
    public function getImgData(){
        if (!request()->isAjax()){
            $this->error('非法操作！');
        }
        else{
            $img_list = [];
            if (!empty(session('img_list'))){
                $img_list = session('img_list');
            }
            else{
                for ($i=0;$i<=15;$i++){
                    $date = date('Ymd',Time::daysAgo($i));
                    $dir = ROOT_PATH . 'data' . DS . 'uploads/img/'.$this->user_id.'/'.$date;
                    $src = request()->root().'/data/uploads/img/'.$this->user_id.'/'.$date;
                
                    if (is_dir($dir)) {
                
                        if (opendir($dir)) {
                
                            $dh = opendir($dir);
                
                            while (($file = readdir($dh)) !== false) {
                                if ($file!="." && $file!="..") {
                                    $img_temp = [];
                                    $img_temp['file_name'] = $file;
                                    $img_temp['src'] = $src.'/'.$file;
                                    $img_list[] = $img_temp;
                                }
                            }
                            closedir($dh);
                        }
                    }
                }
                session('img_list',$img_list);
            }
            if ($img_list){
                $totals=count($img_list);
                $page = input('page','first_page');
                $p = input('p','0');
                if ($page=='first_page'){
                    $p = 0;
                }
                elseif ($page=='last_page'){
                    $p =ceil($totals/8);
                } 
                $is_button = false;
                if ($totals>8){
                    $is_button = true;
                }
                $img_data = page_array(8,$p,$img_list);
               // print_r($img_data);die;
                if ($img_data){
                    return [
                        'code'=>1,
                        'totals_p'=>ceil($totals/8),
                        'p'=>$p,
                        'data_img'=>$img_data
                    ];
                }
                else{
                    return [
                        'code'=>0,
                    ];
                }
            }
        }
    }
    
    public function saveUpload(Request $request){
        $file = $request->file('avatar_file');
        $post_data = $request->param('avatar_data');
        $post_src =$request->param('avatar_src'); //原图片路径；
        //先上传原文件获取路径和文件唯一名称还有几本文件信息，再由从获取的路径进行裁剪图片
        //$result = $this->validate(['file' => $file], ['file'=>'require|image|fileSize:2097152'],['file.require' => '请选择上传文件', 'file.image' => '非法图像文件'])
        if (!empty($post_data)) {
            $img_dir = ROOT_PATH . 'data' . DS . 'uploads/img/'.$this->user_id; //保存路径
            $img_config = [
                'size'=>2097152,  //2M
                'ext'=>'jpg,png,gif'
            ];
            //print_r($post_src);die;
            $file_data = pathinfo($_SERVER['DOCUMENT_ROOT'].$post_src);
            if (!empty($post_src) && file_exists($_SERVER['DOCUMENT_ROOT'].$post_src) &&
                !empty($file_data['basename'])){  //同名覆盖
                $real_path = realpath($_SERVER['DOCUMENT_ROOT'].$post_src);
                return $this->_cropImg($real_path, $img_dir, $post_data, $file_data['basename'], $file_data['extension'],$post_src,true);
            }
            else{
                $img_info = $file->validate($img_config)->move($img_dir);
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
            return [
                'code'=>0,
                'msg'=>'请选择上传图片'
            ];
        }        
    }
    
    public function alipayTest(){
        
        echo 'hello word';
    }
    
    public function alipayCallback(){
        
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
                //     imagegif()：以 GIF 格式将图像输出到浏览器或文件
                //     imagejpeg()：以 JPEG 格式将图像输出到浏览器或文件
                //     imagepng()：以 PNG 格式将图像输出到浏览器或文件
                //     imagewbmp()：以 WBMP 格式将图像输出到浏览器或文件
                //imagedestroy();
              //  chmod($dst.'/'.date('Ymd').'/'.$file_name,0777);
                
                $file_name = uniqid(md5(msectime())).'.'.$type;
                $new_src = $dst.'/'.date('Ymd').'/'.$file_name;
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
                }
                if ($is_old){
                    unlink($_SERVER['DOCUMENT_ROOT'].$post_src);
                }
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
                        'img_url'=>request()->root().'/data/uploads/img/'.$this->user_id.'/'.date('Ymd').'/'.$file_name
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
}