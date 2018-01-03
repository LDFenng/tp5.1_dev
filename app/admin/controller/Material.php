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
class material extends Base{
   
    /**
     * 模块
     */
    public function blockList(){
        $block_condition['admin_id']=$this->uid;
        if (!empty(input('key_words'))){
            $key_words=input('key_words');
            $block_condition['title']=['like',"%{$key_words}%"];
        }
        $page=input('page_num',config('paginate.list_rows'));
        $block_list = db('news_block')->where($block_condition)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
        $show=show_page($block_list->render());
        $this->assign('page',$show);
        $this->assign('block_list',$block_list);
        return $this->fetch();
    }
    
    public function changeBlockStatus(){
        $type=request()->param('type');
        if ($type=='a'){
            return $this->changeStatus('news_block');
        }
        elseif ($type=='b'){
            return $this->changeStatus('news_block','is_set_top',4);
        }
    }
    
    public function editBlock(){
        $id=input('id');
        $this->assign('block_info',$this->getDataInfo('news_block', $id));
        return $this->fetch();
    }
    
    public function saveBlock(){
        $data=request()->param();
        $rule=[
            ['title','require|length:2,80','标题必填|字符长度2到80'],
            ['sort','between:0,9999999','填写0到9999999范围内的数字']
        ];
        del_old_file($data['cover_img'], $data['old_cover_img']);
        return $this->saveData('news_block', $data,$rule,new_url('admin/Material/blockList',['page'=>input('page')]));
    }
    
    public function delBlock(){
        return $this->reallyDelete('news_block');
    }
    
    public function orderBlock(){
        return $this->dataOrder('news_block');
    }
    
    
    public function categoryList(){
        $category_condition['news_category.admin_id']=$this->uid;
        $data=request()->param();
        if (!empty($data['key_words'])){
            $category_condition['news_category.title']=['like',"%{$data['key_words']}%"];
        }
        if (!empty($data['block_id']))$category_condition['news_category.block_id']=$data['block_id'];
        $page=input('page_num',config('paginate.list_rows'));
        $category_list =Db::view('news_category')
        ->view('news_block',['title'=>'block_title'],'news_block.id=news_category.block_id')
        ->where($category_condition)->order('news_category.sort asc')->paginate($page,false,['query'=>get_query()]);
        $show=show_page($category_list->render());
        $this->assign('page',$show);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }
    
    public function editCategory(){
        $id=input('id');
        $this->assign('category_info',$this->getDataInfo('news_category', $id));
        $block_list=db('news_block')->where(['admin_id'=>$this->uid,'is_enabled'=>1])->select();
        $this->assign('block_list',$block_list);
        return $this->fetch();
    }
    
    public function changeCategoryStatus(){
        $type=request()->param('type');
        switch ($type){
            case 'a':
                return $this->changeStatus('news_category');
                break;
            case 'b':
                return $this->changeStatus('news_category','is_set_top',4);
                break;
        }
    }
    
    public function saveCategory(){
        $data=request()->param();
        $rule=[
            ['title','require|length:2,80','标题必填|字符长度2到80'],
            ['sort','between:0,9999999','填写0到9999999范围内的数字'],
            ['block_id','require','模块必选']
        ];
        del_old_file($data['cover_img'], $data['old_cover_img']);
        return $this->saveData('news_category', $data,$rule,new_url('admin/Material/categoryList',['page'=>input('page')]));
    }
    
    public function delCategory(){
        return $this->reallyDelete('news_category');
    }
    
    public function orderCategory(){
        return $this->dataOrder('news_category');
    }
    
    
    public function articleList(){
        $article_condition=['news.admin_id'=>$this->uid,'news.is_delete'=>0];
        $data=request()->param();
        if (!empty($data['key_words'])){
            $article_condition['news.title|news.author|news.sub_title']=['like',"%{$data['key_words']}%"];
        }
        if (!empty($data['block_id']))$article_condition['news.block_id']=$data['block_id'];
        if (!empty($data['category_id']))$article_condition['news.category_id']=$data['category_id'];
        if (time_condition(input('start_time'),input('end_time')))$article_condition['push_time']=time_condition(input('start_time'),input('end_time'));
        $page=input('page_num',config('paginate.list_rows'));
        $article_list=Db::view('news')
        ->view('news_block',['title'=>'block_title'],'news_block.id=news.block_id')
        ->view('news_category',['title'=>'category_title'],'news_category.id=news.category_id')
        ->where($article_condition)->order('sort asc,is_display desc,is_set_top desc')
        ->paginate($page,false,['query'=>get_query()]);
        //print_r($article_list);die;
        $show=show_page($article_list->render());
        $this->assign('article_list',$article_list);
        $this->assign('page',$show);
        return $this->fetch();
    }
    
    public function editArticle(){
        $id=input('id');
        $this->assign('article_info',$this->getDataInfo('news', $id));
        return $this->fetch();
    }
    
    public function getNewsCategory(){
        $block_id=request()->param('block_id');
        if (request()->isAjax()){
            config('default_ajax_return','json');
            $category_list=db('news_category')->where(['is_enabled'=>1,'block_id'=>$block_id])->order('sort asc')->field('id,title')->select();
            if ($category_list){
                return ['code'=>1,'data_list'=>$category_list];
            }
            else{
                return ['code'=>1,'msg'=>'暂无数据'];
            }
        }
    }
    
    public function changeArticleStatus(){
        $type=request()->param('type');
        switch ($type){
            case 'a':
                return $this->changeStatus('news','is_display',2);
                break;
            case 'b':
                return $this->changeStatus('news','is_set_top',4);
                break;
        }
    }
    
    public function saveArticle(){
        $data=request()->param();
        $rule=[
            ['title','require|length:3,80','标题必填|标题长度2到80'],
            ['sub_title','require|length:3,80','副标题必填|副标题长度2到80'],
            ['category_id','require','文章类型必选'],
            ['block_id','require','文章模块必选'],
            ['source_url','url','文章来源url格式错误'],
            ['summary','require|length:3,300','简要必填|简要长度3到300'],
            ['content','require','文章内容必填'],
            ['hits','number|between:0,9999999','点击量填写数字|填写0到9999999范围内的数字'],
            //['push_time','dateFormat:Y-m-dH:i','选择正确的时间格式'],
            ['sort','number|between:0,9999999','排序填写数字|填写0到9999999范围内的数字'],
        ];
        del_old_file($data['cover_img'], $data['old_cover_img']);
        $data['push_time']=strtotime($data['push_time']);
        return $this->saveData('news', $data,$rule,new_url('admin/Material/articleList',['page'=>input('page')]));
    }
    
    public function delArticle(){
        return $this->reallyDelete('news');
    }
    
    public function orderArticle(){
        return $this->dataOrder('news');
    }
    
    public function slideList(){
        $slide_condition=['admin_id'=>$this->uid];
        $data=request()->param();
        if (!empty($data['scene']))$slide_condition['scene']=$data['scene'];
        if (!empty($data['key_words']))$slide_condition['title']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $slide_list=db('slide')->where($slide_condition)->order('sort asc')
        ->paginate($page,false,['query'=>get_query()]);
        $this->assign('slide_list',$slide_list);
        $this->assign('page',show_page($slide_list->render()));
        return $this->fetch();
    }
    
    public function editSlide(){
        $id=input('id');
        $slide_info=$this->getDataInfo('slide', $id);
        if (!empty($slide_info['animate_data'])){
            $slide_info['animate_data']=json_decode($slide_info['animate_data'],true);
        }
        $this->assign('slide_info',$slide_info);
        return $this->fetch();
    }
    
    public function changeSlideStatus(){
        return $this->changeStatus('slide');
    }
    
    public function saveSlide(){
        config('default_ajax_return','json');
        $data=request()->param();
        $base_rule=[
            ['title','require|length:3,80','标题必填|标题长度2到80'],
            ['img_url','require','上传轮播图或者背景图'],
            ['url','url','填写正确的url地址'],
            ['animate_data','requireIf:is_animate,1','动画图片必须上传']
        ];
        $base_validate = new \think\Validate($base_rule);
        $check_result = $base_validate->check($data);
        if ($check_result){
            $animate_rule=[
                ['style','require','动画样式定位必须填'],
                ['effect','alphaNum','输入正确的动画样式，暂不支持自定义！'],
                ['duration','number','动画持续时间必须数字'],
                ['delay','number','出场延长时间必须数字'],
                ['url','url','填写正确的动画url地址'],
                ['animate_img_url','require','动画图片必须上传'],
            ];
            
            $animate_data=empty($data['animate_data'])?null:$data['animate_data'];
            if ($animate_data){
                $animate_list=[];
                $animate_validate = new \think\Validate($animate_rule);
                foreach ($animate_data as $animate_key=>$animate_val){                   
                    $animate_result = $animate_validate->check($animate_val);
                    if (!$animate_result){
                        return ['code'=>0,'msg'=>$animate_validate->getError()];
                        break;
                    }
                    del_old_file($animate_val['animate_img_url'], $animate_val['old_animate_img']);
                    unset($animate_val['old_animate_img']);
                    $animate_list[]=$animate_val;
                }
                $data['animate_data']=json_encode($animate_list);
            }
            elseif (input('is_animate',0)==1){
                return ['code'=>0,'msg'=>'动画图片必须上传'];
            }
            del_old_file($data['img_url'], $data['old_img_url']);
            unset($data['old_img_url']);
            $data['is_enabled']=input('is_enabled',0);
            $data['is_animate']=input('is_animate',0);
            return $this->saveData('slide', $data,null,new_url('admin/Material/slideList',['page'=>input('page')]));
        }
        else{
            return ['code'=>0,'msg'=>$base_validate->getError()];
        }     
    }
    
    public function orderSlide(){
        return $this->dataOrder('slide');
    }
    
    public function delSlide(){
        return $this->reallyDelete('slide');
    }
    
    public function articleRecycle(){
        
        return $this->fetch();
    }
    /**
     * 清空
     */
    public function emptyRecycle(){
        
    }
    
    /**
     * 还原
     */
    public function restoreData(){
    
    }
    
    /**
     * 音频列表
     */
    public function audioList(){
        $audio_condition=[
            'admin_id'=>$this->uid,
        ];
        $data=request()->param();
        if (!empty($data['type']))$audio_condition['type']=$data['type'];
        if (!empty($data['key_words']))$audio_condition['name|suffix']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $audio_list=db('audio_data')->where($audio_condition)->order('sort asc')
        ->paginate($page,false,['query'=>get_query()]);
        $this->assign('audio_list',$audio_list);
        $this->assign('page',show_page($audio_list->render()));
        return $this->fetch();
    }
    
    /**
     * 编辑添加音频
     */
    public function editAudio(){
        $id=input('id');
        $this->assign('audio_info',$this->getDataInfo('audio_data', $id));
        return $this->fetch();
    }
    
    /**
     * 保存音频
     */
    public function saveAudio(){
        $data=request()->param();
        $rule=[
            ['name','require','音频名称必须填写'],
            ['path_url','require','未上传任何音频'],
            ['sort','number','排序填写数字'],
            ['type','require','选择音频类型']
        ];
        $save_data=[
            'id'=>$data['id'],
            'name'=>$data['name'],
            'type'=>empty($data['type'])?null:$data['type'],
            'describe'=>$data['describe'],
            'path_url'=>$data['audio_url']
        ];
        if (!empty($data['file_info'])){
            $file_info=json_decode($data['file_info'],true);
            $save_data['artist']=empty($file_info['artist'][0])?'':$file_info['artist'][0];
            $save_data['suffix']=empty($file_info['suffix'])?'未知':$file_info['suffix'];
            $save_data['album']=empty($file_info['album'][0])?'':$file_info['album'][0];
            $save_data['size']=empty($file_info['size'])?0:$file_info['size'];  //字节
            $save_data['play_time']=empty($file_info['play_time'])?'0':$file_info['play_time'];
            $save_data['bitrate']=empty($file_info['bitrate'])?'0':round($file_info['bitrate']/1000,0);
        }
        del_old_file($data['audio_url'], $data['old_audio_url']);
        return $this->saveData('audio_data', $save_data,$rule,new_url('admin/Material/audioList'));
    }
    
    /**
     * 删除音频
     */
    public function delAudio(){
        $ids=input('ids/a');
        $audio_urls=db('audio_data')->where('id','in',$ids)->column('path_url');
        del_old_file($audio_urls,[]);
        return $this->reallyDelete('audio_data');
    }
    
    /**
     * 改变音频状态
     */
    public function changeAudioStatus(){
        return $this->changeStatus('audio_data','is_enabled',3);
    }
    
    /**
     * 音频排序
     */
    public function orderAudio(){
        return $this->dataOrder('audio_data');
    }
    
    /**
     * 视频列表
     */
    public function videoList(){
        $audio_condition=[
            'admin_id'=>$this->uid,
        ];
        $data=request()->param();
        if (!empty($data['type']))$audio_condition['type']=$data['type'];
        if (!empty($data['key_words']))$audio_condition['name|suffix']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $video_list=db('video_data')->where($audio_condition)->order('sort asc')
        ->paginate($page,false,['query'=>get_query()]);
        $this->assign('video_list',$video_list);
        $this->assign('page',show_page($video_list->render()));
        return $this->fetch();
    }
    
    /**
     * 编辑添加视频
     */
    public function editVideo(){
        $id=input('id');
        $this->assign('video_info',$this->getDataInfo('video_data', $id));
        return $this->fetch();
    }
    
    /**
     * 保存视频
     */
    public function saveVideo(){
        $data=request()->param();
        $rule=[
            ['name','require','视频名称必须填写'],
            ['path_url','require','未上传任何视频'],
            ['sort','number','排序填写数字'],
            ['type','require','选择视频类型']
        ];
        $save_data=[
            'id'=>$data['id'],
            'name'=>$data['name'],
            'type'=>empty($data['type'])?null:$data['type'],
            'describe'=>$data['describe'],
            'path_url'=>$data['video_url']
        ];
        if (!empty($data['file_info'])){
            $file_info=json_decode($data['file_info'],true);
            $save_data['artist']=empty($file_info['artist'][0])?'':$file_info['artist'][0];
            $save_data['suffix']=empty($file_info['suffix'])?'未知':$file_info['suffix'];
            $save_data['resolution']=empty($file_info['resolution_x'] && $file_info['resolution_y'])?'未知':$file_info['resolution_x'].'x'.$file_info['resolution_y'];
            $save_data['size']=empty($file_info['size'])?0:$file_info['size'];  //字节
            $save_data['play_time']=empty($file_info['play_time'])?'0':$file_info['play_time'];
            $save_data['bitrate']=empty($file_info['bitrate'])?'0':round($file_info['bitrate']/1000,0);
        }
        del_old_file($data['video_url'], $data['old_video_url']);
        return $this->saveData('video_data', $save_data,$rule,new_url('admin/Material/videoList'));
    }
    
    /**
     * 删除视频
     */
    public function delVideo(){
        $ids=input('ids/a');
        $vide_urls=db('video_data')->where('id','in',$ids)->column('path_url');
        del_file($vide_urls);
        return $this->reallyDelete('video_data');
    }
    
    /**
     * 改变视频状态
     */
    public function changeVideoStatus(){
        return $this->changeStatus('video_data','is_enabled',3);
    }
    
    /**
     * 视频排序
     */
    public function orderVideo(){
        return $this->dataOrder('video_data');
    }
    
    /**
     * 图片素材
     */
    public function imgList(){
        $img_condition=['admin_id'=>$this->uid];
        $data=request()->param();
        if (!empty($data['type']))$img_condition['type']=$data['type'];
        if (!empty($data['key_words']))$img_condition['name|suffix']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $img_list=db('img_data')->where($img_condition)->order('sort asc')
        ->paginate($page,false,['query'=>get_query()]);
        $this->assign('img_list',$img_list);
        $this->assign('page',show_page($img_list->render()));
        return $this->fetch();
    }
    
    public function editImg(){
        $id=input('id');
        $img_info=$this->getDataInfo('img_data', $id);
        $this->assign('img_info',$img_info);
        return $this->fetch();
    }
    
    public function saveImg(){
        $data=request()->param();
        $rule=[
            ['name','require','图片名称必须填写'],
            ['path_url','require','未上传任何图片'],
            ['sort','number','排序填写数字'],
            ['type','require','选择图片类型']
        ];
        if (!empty($data['path_url'])){
            $domain=request()->domain().get_root_dir();
            $dir=str_replace($domain,'',$data['path_url']);
            $pic_info=path_info($dir);
            $data['suffix']=$pic_info['extension'];
            $data['size']=filesize($dir);
        }
        del_old_file($data['path_url'], $data['old_path_url']);
        cache(null);
        unset($data['old_path_url']);
        return $this->saveData('img_data', $data,$rule,new_url('admin/Material/imgList'));
    }
    
    public function delImg(){
        $ids=input('ids/a');
        $vide_urls=db('img_data')->where('id','in',$ids)->column('path_url');
        del_file($vide_urls);
        return $this->reallyDelete('img_data');
    }
    
    public function orderImg(){
        return $this->dataOrder('img_data');
    }
    
    public function changeImgStatus(){
        return $this->changeStatus('img_data');
    }
}
