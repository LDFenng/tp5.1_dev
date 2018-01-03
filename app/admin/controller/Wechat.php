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
use EasyWeChat\Foundation\Application as wechatApi;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Article;
//use EasyWeChat\Message\Material;
class wechat extends Base{
    /**
     * 检测是否存在微信公众号
     */
    protected function _initialize(){
        parent::_initialize();
        $wechat_info=db('wechat_info')->where(['admin_id'=>$this->uid,'is_enabled'=>1])->field('id')->find();
        if (!$wechat_info){
            config('default_ajax_return','json');
            $this->error('请前往系统管理配置微信公众号',url('admin/Sys/wechatList'));
        }
    }
    /**
     * 菜单列表
     */
    public function menuList(){
        $menu_condition=[
            'wechat_menu.admin_id'=>$this->uid,
            'wechat_menu.pid'=>input('pid',0)
        ];
        $page_condition=[
            'admin_id'=>$this->uid,
            'pid'=>0
        ];
        $data=request()->param();
        if(!empty($data['wechat_id'])){
            $menu_condition['wechat_menu.wechat_id']=$data['wechat_id'];
            $page_condition['wechat_id']=$data['wechat_id'];
        }
        if (!empty($data['key_words'])){
            $menu_condition['wechat_menu.title']=['like',"%{$data['key_words']}%"];
            $page_condition['wechat_id']=$data['wechat_id'];
        }
        $page=input('page_num',config('paginate.list_rows'));
        /*分页*/
        $menu_data = db('wechat_menu')->where($page_condition)->paginate($page,false,['query'=>get_query()]);
        
        $menu_list = Db::view('wechat_menu')
        ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_menu.wechat_id')
        ->where($menu_condition)->order('wechat_menu.sort asc')->select();
        //$menu_list=list_to_tree($temp_data);
        $this->assign('menu_list',$menu_list);
        //print_r($menu_list);
        $this->assign('page',show_page($menu_data->render()));
        if (input('ttype')=='sub'){
            return $this->fetch('ajaxMenuList');
        }
        else{
            return $this->fetch();
        } 
    }
    
    /**
     * 菜单编辑、添加
     */
    public function editMenu(){
        $id=input('id');
        $menu_info=$this->getDataInfo('wechat_menu', $id);
        $return_data=$this->getEditWechatMsg($menu_info);
        $this->assign('news_list',$return_data['news_list']);
        $menu_info=$return_data['msg_info'];
        $pid=empty(input('pid'))?$menu_info['pid']:input('pid');
        $parent_title=db('wechat_menu')->where('id',$pid)->value('title');
        $this->assign('parent_title',$parent_title);
        $this->assign('menu_info',$return_data['msg_info']);
        return $this->fetch();
    }
    
    /**
     * 菜单排序
     */
    public function orderMenu(){
        return $this->dataOrder('wechat_menu');
    }
    
    /**
     * 菜单状态改变
     */
    public function changeMenuState(){
        //先判断启用菜单个数 一级pid=0（3个）;二级pid!=0（5个）
        $id = input('x');
        config('default_ajax_return','json');
        $is_enabled=db('wechat_menu')->where('id',$id)->value('is_enabled');
        if ($is_enabled==0 || empty($is_enabled)){
            $wechat_condition=[
                'admin_id'=>$this->uid,
                'wechat_id'=>input('t'),
                'pid'=>input('i',0),
                'is_enabled'=>1
            ];
            //统计启用个数
            $menu_num=db('wechat_menu')->where($wechat_condition)->count();
            //判断是一级菜单还是二级菜单
            if (input('i',0)==0 || empty(input('i'))){
                if ($menu_num>=3){
                    return ['code'=>0,'msg'=>'此公众号一级菜单已启用了3个，如需启用另一个；请先禁用其中一个'];
                }
            }
            else{
                if ($menu_num>=5){
                    return ['code'=>0,'msg'=>'此公众号二级菜单已启用了5个，如需启用另一个；请先禁用其中一个'];
                } 
            }
        }
        return $this->changeStatus('wechat_menu');
    }
    
    /**
     * 保存菜单
     */
    public function saveMenu(){
        config('default_ajax_return','json');
        $data=request()->param();
        /*1.菜单点击类型：
         * a.文本类型：content字段不可控；
         * b.图片类型：img_url字段不可控；
         * c.图文类型：news_ids字段不可空；
         * d.视频类型：video_url字段不可空；
         * e.音频类型：voice_url字段不可控；
         * f.显示二级菜单：以上所有均不可用（二级菜单不存在）。
         *2.菜单跳转类型：url字段不可空。 
         *3.触发数据均保存到微信菜单数据表，由各种类型保存，对象数据以JSON保存 
         * */ 
        $rule=[
            ['title','require|length:1,7','菜单名称必须填写|菜单名称最多7个字符'],
            ['pid','require','选择所属菜单'],
            ['wechat_id','require','选择微信公众号'],
            ['even_type','require','选择菜单类型'],
            ['url','requireIf:even_type,2','跳转类型菜单url不可为空'],
            ['msg_type','requireIf:even_type,1','选择信息类型'],
            ['key_words','requireIf:msg_type,1|requireIf:msg_type,2|requireIf:msg_type,3|requireIf:msg_type,4|requireIf:msg_type,5|requireIf:msg_type,6','关键字不可为空|关键字不可为空|关键字不可为空|关键字不可为空|关键字不可为空|关键字不可为空'],
            ['content','requireIf:msg_type,1','信息类型为文本，文本不可为空'],
            ['img_id','requireIf:msg_type,2','信息类型为图片，图片不可为空'],
            ['news_ids','requireIf:msg_type,3','信息类型为图文，图文不可为空'],
            ['video_id','requireIf:msg_type,4','信息类型为视频，视频不可为空'],
            ['voice_id','requireIf:msg_type,5','信息类型为音频，音频不可为空'],
            ['sort','number','排序填写数字']
        ];
        
        $data['is_enabled']=empty(input('is_enabled'))?0:1;
        return $this->saveData('wechat_menu', $data,$rule,new_url('admin/Wechat/menuList'));  
    }
    
    /**
     * 删除菜单
     */
    public function delMenu(){
        return $this->reallyDelete('wechat_menu');
    }
    
    /**
     * 菜单操作结果同步至微信菜单
     */
    public function syncWechat(){
        config('default_ajax_return','json');
        $data=request()->param();
        $rule=[
            ['wechat_id','require|number','请选择要同步的微信公众号|非法操作']
        ];
        $validate = new\think\Validate($rule);
        $result = $validate->check($data);
        if ($result){
            //print_r($wechat_info);die;
            $options_list=config('wechatConfig');
            $options=$options_list['wechat_option_'.$data['wechat_id']];
            $wechat_api = new wechatApi($options);
            $menu_api=$wechat_api->menu;
            //xiandui
            $button_data=db('wechat_menu')->where(['admin_id'=>$this->uid,
                'wechat_id'=>$data['wechat_id'],'is_enabled'=>1])->select();
            if ($button_data){
                //创建菜单
                $button_list=list_to_tree($button_data);
                $buttons=[];
                foreach ($button_list as $button_key=>$button_val){
                    if (!isset($button_val['child'])){
                        //一级菜单
                        $buttons[]=$this->handleMenu($button_val);
                    }
                    elseif (isset($button_val['child']) && !empty($button_val['child'])){
                        //二级菜单
                        $sub_button=[];
                        foreach ($button_val['child'] as $child_val){
                            $sub_button[]=$this->handleMenu($child_val);
                        }
                        $buttons[]=[
                            'name'=>$button_val['title'],
                            'sub_button'=>$sub_button
                        ];
                    }
                }
            }
            $return_data=$menu_api->add($buttons)->toArray();
            if (isset($return_data['errcode']) && $return_data['errcode']==0){
                return ['code'=>1,'msg'=>'同步成功！取消关注再关注可立即查看'];
            }
        }  
    }
    
    /**
     * 菜单事件处理
     * @param $button_val
     */
    private function handleMenu($button_val){
        $buttons=[];
        switch ($button_val['even_type']){
            case 1:  //点击事件
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'key'=>$button_val['id']
                ];
                break;
            case 2: //跳转
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'url'=>$button_val['url']
                ];
                break;
            case 3: //调用扫一扫
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'key'=>$button_val['id']
                ];
                break;
            case 4:  //获取相机或者相册
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'key'=>$button_val['id']
                ];
                break;
            case 5:  //获取地理位置
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'key'=>$button_val['id']
                ];
                break;
            case 6: //获取图片
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'media_id'=>$button_val['msg_data']  //素材ID（认证用户不建议使用）
                ];
                break;
            case 7:  //获取图文
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'media_id'=>$button_val['msg_data']  //素材ID（认证用户不建议使用）
                ];
                break;
            case 8:  //小程序
                $buttons=[
                'type'=>get_ext_title('wechatExt.wechat_click_type', $button_val['even_type'],'type'),
                'name'=>$button_val['title'],
                'url'=>$button_val['url'],
                'appid'=>$button_val['appid'],
                'pagepath'=>$button_val['pagepath']
                ];
                break;
        }
        return $buttons;
    }
    
    
    //消息回复
    /*
     *a.被动回复
     *b.自动回复
     *c.关键字回复 
     */
    /**
     * 被动回复列表
     */
    public function passiveReply(){
        $return_data=$this->getWechatMsg(1);
        $this->assign('page',show_page($return_data->render()));
        $this->assign('msg_list',$return_data);
        return $this->fetch();
    }
    
    /**
     * 被动回复
     */
    public function editPassiveReply(){
        $id=input('id');
        $msg_info=$this->getDataInfo('wechat_msg_reply', $id);
        $return_data=$this->getEditWechatMsg($msg_info);
        $this->assign('news_list',$return_data['news_list']);
        $this->assign('msg_info',$return_data['msg_info']);
        return $this->fetch();
    }
    
    /**
     * 保存被动回复 
     */
    public function savePassiveReply(){
        $save_result=$this->saveWechatMsg(1);
        if (!empty($save_result['media_id'])){
            return ['code'=>1,'msg'=>'操作成功','url'=>new_url('admin/Wechat/passiveReply')];
        }
        else{
            return ['code'=>0,'msg'=>'请重新操作'];
        }
    }
    
    /**
     * 删除被动回复
     */
    public function delPassiveReply(){
        return $this->reallyDelete('wechat_msg_reply');
    }
    
    /**
     * 改变被动回复
     */
    public function changPassiveReply(){
        config('default_ajax_return','json');
        $wechat_msg_condition=[
            'admin_id'=>$this->uid,
            'reply_type'=>1,
            'is_enabled'=>1
        ];
        $id=input('x');
        $reply_data=db('wechat_msg_reply')->where($wechat_msg_condition)->field('id,is_enabled')->find();
        if ($reply_data && $id!=$reply_data['id'])return ['code'=>0,'msg'=>'被动回复最多同时只能启用一个,请先禁用另一个'];  
        return $this->changeStatus('wechat_msg_reply');
    }
    
    /**
     * 自动回复
     */
    public function autoReply(){
        $return_data=$this->getWechatMsg(3);
        $this->assign('page',show_page($return_data->render()));
        $this->assign('msg_list',$return_data);
        return $this->fetch();
    }
    
    /**
     * 编辑自动回复
     */
    public function editAutoReply(){
        $id=input('id');
        $msg_info=$this->getDataInfo('wechat_msg_reply', $id);
        $return_data=$this->getEditWechatMsg($msg_info);
        $this->assign('news_list',$return_data['news_list']);
        $this->assign('msg_info',$return_data['msg_info']);
        return $this->fetch();
    }
    
    /**
     * 保存自动回复
     */
    public function saveAutoReply(){
        $save_result=$this->saveWechatMsg(3);
        if (!empty($save_result['media_id'])){
            return ['code'=>1,'msg'=>'操作成功','url'=>new_url('admin/Wechat/autoReply')];
        }
        else{
            return ['code'=>0,'msg'=>$save_result['msg']];
        }
    }
    
    /**
     * 删除自动回复
     */
    public function delAutoReply(){
        return $this->reallyDelete('wechat_msg_reply');
    }
    
    /**
     * 改变状态
     */
    public function changeAutoReply(){
        config('default_ajax_return','json');
        $wechat_msg_condition=[
            'admin_id'=>$this->uid,
            'reply_type'=>3,
            'is_enabled'=>1
        ];
        $id=input('x');
        $reply_data=db('wechat_msg_reply')->where($wechat_msg_condition)->field('id,is_enabled')->find();
        if ($reply_data && $id!=$reply_data['id'])return ['code'=>0,'msg'=>'自动回复最多同时只能启用一个,请先禁用另一个'];
        return $this->changeStatus('wechat_msg_reply');
    }
    
    /**
     * 关键字回复
     */
    public function keywordReply(){
        $return_data=$this->getWechatMsg(5);
        $this->assign('page',show_page($return_data->render()));
        $this->assign('msg_list',$return_data);
        return $this->fetch();
    }
    
    /**
     * 编辑关键字回复
     */
    public function editKeyWordReply(){
        $id=input('id');
        $msg_info=$this->getDataInfo('wechat_msg_reply', $id);
        $return_data=$this->getEditWechatMsg($msg_info);
        $this->assign('news_list',$return_data['news_list']);
        $this->assign('msg_info',$return_data['msg_info']);
        return $this->fetch();
    }
    
    /**
     * 保存关键字回复
     */
    public function saveKeyWordReply(){
        $save_result=$this->saveWechatMsg(5);
        if (!empty($save_result['media_id'])){
            return ['code'=>1,'msg'=>'操作成功','url'=>new_url('admin/Wechat/keywordReply')];
        }
        else{
            return ['code'=>0,'msg'=>$save_result['msg']];
        }
    }
    
    /**
     * 删除关键字回复
     */
    public function delKeyWordReply(){
        return $this->reallyDelete('wechat_msg_reply');
    }
    
    /**
     * 排序
     */
    public function ordeKeyWordReply(){
        return $this->dataOrder('wechat_msg_reply');
    }
    
    /**
     * 改变状态
     */
    public function changeKeyWordReply(){
        return $this->changeStatus('wechat_msg_reply');
    }
    
    /**
     * 获取自动回复信息数据
     * 同张表不同条件查询
     */
    private function getWechatMsg($reply_type){
       $data=request()->param();
       $msg_condition=[
           'wechat_msg_reply.admin_id'=>$this->uid,
           'wechat_msg_reply.reply_type'=>$reply_type
       ];
       if (!empty($data['wechat_id']))$msg_condition['wechat_msg_reply.wechat_id']=$data['wechat_id'];
       if (!empty($data['title']))$msg_condition['wechat_msg_reply.title']=['like',"%{$data['title']}%"];
       if (!empty($data['msg_type']))$msg_condition['msg_type']=$data['msg_type'];
       $page=input('page_num',config('paginate.list_rows'));
       $result_data=Db::view('wechat_msg_reply')
       ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_msg_reply.wechat_id')
       ->where($msg_condition)->order('wechat_msg_reply.sort asc')
       ->paginate($page,false,['query'=>get_query()]);
       return $result_data;
    }
    
    private function getEditWechatMsg($msg_info=null){
       $news_list=db('news')->where(['admin_id'=>$this->uid,'is_display'=>1])->order('sort asc')
       ->field('id,title,cover_img,key_words,summary')->select();
       if ($msg_info){
           switch ($msg_info['msg_type']){
               case 2:  //图片
                   $msg_info['img_url']=db('img_data')->where('id',$msg_info['img_id'])->value('path_url');
                   break;
               case 3:  //图文
                   $new_ids=(!empty($msg_info['news_ids']))?explode(',', $msg_info['news_ids']):[];
                   $news=[];
                   if ($news_list){
                       foreach ($news_list as $new_val){
                           if (in_array($new_val['id'], $new_ids)){
                               $news[]=$new_val;
                           }
                       }
                   }
                   $msg_info['news_list']=$news;
                   break;
               case 4:  //视频
                   $msg_info['video_url']=db('video_data')->where('id',$msg_info['video_id'])->value('path_url');
                   break;
               case 5: //音频
                   $msg_info['audio_url']=db('audio_data')->where('id',$msg_info['audio_id'])->value('path_url');
                   break;
           }
       }
       return ['msg_info'=>$msg_info,'news_list'=>$news_list];
    }
    
    private function saveWechatMsg($msg_type){
       config('default_ajax_return','json');
       $data=request()->param();
       $rule=[
           ['title','require|length:1,80','名称必须填写|菜单名称最多80个字符'],
           ['wechat_id','require','选择微信公众号'],
           ['msg_type','require','选择信息类型'],
           ['content','requireIf:msg_type,1','信息类型为文本，文本不可为空'],
           ['img_id','requireIf:msg_type,2','信息类型为图片，图片不可为空'],
           ['news_ids','requireIf:msg_type,3','信息类型为图文，图文不可为空'],
           ['video_id','requireIf:msg_type,4','信息类型为视频，视频不可为空'],
           ['voice_id','requireIf:msg_type,5','信息类型为音频，音频不可为空'],
           ['sort','number','排序填写数字']
       ];
       
       if ($msg_type==5){
           $rule[]=['key_words','require|length:1,62','关键字不可为空|关键字长度不可多于60个字符'];
       }
       $validate = new\think\Validate($rule);
       $result = $validate->check($data);
       if ($result){
           if (!empty($data['key_words'])){
               $data['key_words']=comma_change($data['key_words']);
               $key_words=explode(',', $data['key_words']);
               if (count($key_words)>8)return ['code'=>0,'msg'=>'关键字个数不得超过8个'];
           }
           $data['reply_type']=$msg_type;
           $data['is_enabled']=empty($data['is_enabled'])?0:1;
           $save_result=$this->saveData('wechat_msg_reply', $data,null);
           if ($save_result['code']==1){
               switch ($data['msg_type']){
                   case 2: //图片上传微信端
                       $media_data=db('img_data')->where(['admin_id'=>$this->uid,'id'=>$data['img_id'],'is_enabled'=>1])->find();
                       return $this->getWechatMedia($data['wechat_id'], $media_data, 1);
                       break;
                   case 3: //图文
                       //print_r($data['news_ids']);die;
                       foreach (explode(',', $data['news_ids']) as $news_id){
                           $media_data=db('news')->where(['admin_id'=>$this->uid,'id'=>$news_id])->find();
                           return $this->getWechatMedia($data['wechat_id'], $media_data, 2);
                       }
                       break;
                   case 4:  //视频
                       $media_data=db('video_data')->where(['admin_id'=>$this->uid,'id'=>$data['video_id']])->find();
                       return $this->getWechatMedia($data['wechat_id'], $media_data, 4);
                       break;
                   case 5:  //音频
                       $media_data=db('audio_data')->where(['admin_id'=>$this->uid,'id'=>$data['voice_id']])->find();
                       return $this->getWechatMedia($data['wechat_id'], $media_data, 3);
                       break;
               }
           }
       }
       else {
           return ['code'=>0,'msg'=>$validate->getError()];
       }
    }
    
    /**
    * 用户标签
    */
    public function fansTagList(){
       $tag_condition=[
           'admin_id'=>$this->uid
       ];
       $data=request()->param();
       if (!empty($data['key_words']))$tag_condition['name']=['like',"%{$data['key_words']}%"];
       $page=input('page_num',config('paginate.list_rows'));
       $tag_list=db('wechat_user_tag')->where($tag_condition)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
       $tag_temp=$tag_list->toArray();
       $tag_data=$tag_temp['data'];
       if ($tag_data){
           foreach ($tag_data as $tag_key=>$tag_val){
               $wechat_t_g_condition=['wechat_media_id.admin_id'=>$this->uid,'type'=>6,'wechat_media_id.media_id'=>$tag_val['id']];
               if (!empty($data['wechat_id'])){
                   $wechat_t_g_condition['wechat_media_id.wechat_id']=$data['wechat_id'];
               }
               $wechat_titles=Db::view('wechat_media_id',['admin_id,wechat_id,media_id'])
               ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_media_id.wechat_id')
               ->where($wechat_t_g_condition)->select();
               $tag_data[$tag_key]['wechat_titles']=$wechat_titles;
           }
       }
       $this->assign('page',show_page($tag_list->render()));
       $this->assign('tag_list',$tag_data);
       return $this->fetch();
    }
    
    /**
    * 更新标签
    */
    public function updateTagData(){
       config('default_ajax_return','json');
       $data=request()->param();
       $options_list=config('wechatConfig');
       $options=$options_list['wechat_option_'.$data['wechat_id']];
       $wechat_api = new wechatApi($options);
       $tag_data=$wechat_api->user_tag->lists(); //获取所有用户组 保存至本地
       //进行group_id匹配，存在时不进行保存
       $options_list=config('wechatConfig');
       if ($tag_data){
           foreach ($tag_data as $tag_val){
               $g_info=db('wechat_media_id')->where(['admin_id'=>$this->uid,'wechat_id'=>$data['wechat_id'],'type'=>6,'wechat_media_id'=>$tag_val['id']])->field('id')->find();
               if (!$g_info){
                   $save_tag_data=[
                       'admin_id'=>$this->uid,
                       'name'=>$tag_val['name'],
                       'create_time'=>time(),
                       'update_time'=>time()
                   ];
                   $save_group_result=$this->saveData('wechat_user_tag', $save_tag_data);
                   $save_data=[
                       'admin_id'=>$this->uid,
                       'wechat_id'=>$data['wechat_id'],
                       'type'=>6,
                       'count'=>$tag_val['count'],
                       'media_id'=>$save_group_result['id'],
                       'wechat_media_id'=>$tag_val['id'],
                       'create_time'=>time(),
                       'update_time'=>time()
                   ];
                   $save_result=$this->saveData('wechat_media_id', $save_data);
               }
           }
           return $save_result;
       } 
    }
    
    /**
    * 编辑、添加标签
    */
    public function editFansTag(){
       $id=input('id');
       $tag_info=$this->getDataInfo('wechat_user_tag', $id);
       $wechat_list=db('wechat_info')->where(['admin_id'=>$this->uid,'is_enabled'=>1])->order('sort asc')->field('title,id')->select();
       $this->assign('wechat_list',$wechat_list);
       $this->assign('tag_info',$tag_info);
       return $this->fetch();
    }
    
    /**
    * 保存标签
    */
    public function saveFansTag(){
       $data=request()->param();
       $rule=[
           ['name','require|length:1,15','请填写标签名|名字不得少于一个或多于15个'],
           ['sort','number','排序请填写数字']
       ];
       config('default_ajax_return','json');
       $save_return=$this->saveData('wechat_user_tag', $data,$rule,new_url('admin/Wechat/fansTagList'));
       if ($save_return['code']==1){
           $options_list=config('wechatConfig');
           if ($save_return['is_update']){
               foreach ($data['wechat_id'] as $wechat_id){
                   $t_info=db('wechat_media_id')->where(['admin_id'=>$this->uid,'wechat_id'=>$wechat_id,'type'=>6,'media_id'=>$data['id']])->find();
                   $options=$options_list['wechat_option_'.$data['wechat_id']];
                   $wechat_api = new wechatApi($options);
                   $return=$wechat_api->user_tag->update($t_info['wechat_tag_id'], $data['name'])->toArray();
               }
           }
           else{
               foreach ($data['wechat_id'] as $wechat_id){
                   $options=$options_list['wechat_option_'.$wechat_id];
                   $wechat_api = new wechatApi($options);
                   $return=$wechat_api->user_tag->create($data['name'])->toArray();
                   $data['group_id']=$return['id'];
                   $save_data=[
                       'admin_id'=>$this->uid,
                       'wechat_id'=>$wechat_id,
                       'type'=>6,
                       'count'=>0,
                       'media_id'=>$save_return['id'],
                       'wechat_media_id'=>$return['id'],
                       'create_time'=>time(),
                       'update_time'=>time()
                   ];
                   db('wechat_media_id')->insert($save_data);
               }
       
           }
       }
       return $save_return;
    }
    
    public function delFansTag(){
       config('default_ajax_return','json');
       $data=request()->param();
       if (empty($data['ids'])){
           return ['code'=>0,'msg'=>'请选择要删除的标签'];
       }
       foreach ($data['ids'] as $id){
           $wechat_relation_condition=[
               'admin_id'=>$this->uid,
               'wechat_id'=>$data['wechat_id'],
               'media_id'=>$id,
               'type'=>6
           ];
           $relation_info=db('wechat_media_id')->field(true)->find();
           if ($relation_info){
               if ($relation_info['count']>0){
                   return ['code'=>0,'msg'=>'标签编号为'.$id.'含有用户，不可删除；请先移除标签'];
               }
           }
       }
    }
    
    /**
    * 粉丝组别列表
    */
    public function fansGroupList(){
       $group_condition=[
           'admin_id'=>$this->uid,
           'id'=>['not in',[-1,'0']]
       ]; 
       $data=request()->param();
       if (!empty($data['key_words']))$group_condition['name']=['like',"%{$data['key_words']}%"];
       $page=input('page_num',config('paginate.list_rows'));
       $group_list=db('wechat_user_group')->where($group_condition)->order('sort asc')->paginate($page,false,['query'=>get_query()]);
       $group_temp=$group_list->toArray();
       $group_data=$group_temp['data'];
       if ($group_data){
           foreach ($group_data as $group_key=>$group_val){
               $wechat_t_g_condition=['wechat_media_id.admin_id'=>$this->uid,'type'=>5,'wechat_media_id.media_id'=>$group_val['id']];
               if (!empty($data['wechat_id'])){
                   $wechat_t_g_condition['wechat_media_id.wechat_id']=$data['wechat_id'];
               }
               $wechat_titles=Db::view('wechat_media_id',['admin_id,wechat_id,media_id'])
               ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_media_id.wechat_id')
               ->where($wechat_t_g_condition)->select();
               $group_data[$group_key]['wechat_titles']=$wechat_titles;
           }
       }
       $this->assign('page',show_page($group_list->render()));
       $this->assign('group_list',$group_data);
       return $this->fetch();
    }
    
    /**
    * 编辑粉丝组别列表
    * @param $id
    */
    public function editFansGroup(){
       $id=input('id');
       $group_info=$this->getDataInfo('wechat_user_group', $id);
       $wechat_list=db('wechat_info')->where(['admin_id'=>$this->uid,'is_enabled'=>1,'wechat_type'=>['not in',1,5]])->order('sort asc')->field('title,id')->select();
       if ($wechat_list){
           foreach ($wechat_list as $wechat_key=>$wechat_val){
               $group_id=db('wechat_media_id')->where([
                   'admin_id'=>$this->uid,
                   'wechat_id'=>$wechat_val['id'],
                   'type'=>5,
                   'media_id'=>$id])->value('id');
               $wechat_list[$wechat_key]['is_selected']=false;
               if ($group_id){
                   $wechat_list[$wechat_key]['is_selected']=true;
               }
           }
       }
       $this->assign('wechat_list',$wechat_list);
       $this->assign('group_info',$group_info);
       return $this->fetch();
    }
    
    /**
    * 保存粉丝组别；同步至微信
    */
    public function saveFansGroup(){
       $data=request()->param();
       $rule=[
           ['name','require|length:1,15','请填写组名|名字不得少于一个或多于15个'],
           ['sort','number','排序请填写数字']
       ];
       config('default_ajax_return','json');
       $save_return=$this->saveData('wechat_user_group', $data,$rule,new_url('admin/Wechat/fansGroupList'));
       if ($save_return['code']==1){
           $options_list=config('wechatConfig');
           if ($save_return['is_update']){
               foreach ($data['wechat_id'] as $wechat_id){
                   $g_info=db('wechat_media_id')->where(['admin_id'=>$this->uid,'wechat_id'=>$wechat_id,'type'=>5,'media_id'=>$data['id']])->find();
                   $options=$options_list['wechat_option_'.$wechat_id];
                   $wechat_api = new wechatApi($options);
                   $return=$wechat_api->user_group->update($g_info['wechat_media_id'], $data['name'])->toArray();
                   //print_r($return);die;
               }
           }
           else{
               foreach ($data['wechat_id'] as $wechat_id){
                   $options=$options_list['wechat_option_'.$wechat_id];
                   $wechat_api = new wechatApi($options);
                   $return=$wechat_api->user_group->create($data['name'])->toArray();
                   $save_data=[
                       'admin_id'=>$this->uid,
                       'wechat_id'=>$wechat_id,
                       'type'=>5,
                       'count'=>0,
                       'media_id'=>$save_return['id'],
                       'wechat_media_id'=>$return['group']['id'],
                       'create_time'=>time(),
                       'update_time'=>time()
                   ];
                   db('wechat_media_id')->insert($save_data);
               }
    
           }
       }
       return $save_return;
    }
   
    /**
    * 删除粉丝之前先判断此组是否存在粉丝，存在时不可删除
    */
    public function delFansGroup(){
       config('default_ajax_return','json');
       $options_list=config('wechatConfig');
       $data=request()->param();
       if (empty($data['ids'])){
           return ['code'=>0,'msg'=>'请选择要删除的用户组'];
       }
       //查询未分组ID（每个账号仅一个）
       $group_info=db('wechat_user_group')->where(['admin_id'=>$this->uid,'name'=>'未分组'])->find();
       foreach ($data['ids'] as $id){
           $wechat_relation_condition=[
               'admin_id'=>$this->uid,
               'wechat_id'=>$data['wechat_id'],
               'media_id'=>$id,
               'type'=>5
           ];
           $relation_info=db('wechat_media_id')->field(true)->find();
           if ($relation_info){
               if ($relation_info['count']>100000){
                   return ['code'=>0,'msg'=>'标签编号为'.$id.'含10万有用户，不可删除；请先移除用户'];
               }
           }
       }       
       foreach ($data['ids'] as $id){
           $wechat_relation_condition=[
               'admin_id'=>$this->uid,
               'wechat_id'=>$data['wechat_id'],
               'media_id'=>$id,
               'type'=>5
           ];
           $relation_info=db('wechat_media_id')->field(true)->find();
           if ($relation_info){
               $options=$options_list['wechat_option_'.$data['wechat_id']];
               $wechat_api = new wechatApi($options);
               $wechat_api->user_group->delete($relation_info['wechat_media_id']);
               db('wechat_user')->where(['admin_id'=>$this->uid,'wechat_id'=>$data['wechat_id'],'group_id'=>$id])->setFiled('group_id','0');
           }     
       }
       return $this->reallyDelete('wechat_user_group');
    }
    
    /**
    * 粉丝列表
    */
    public function fansList(){
       $fans_condition=[
           'wechat_user.admin_id'=>$this->uid
       ]; 
       $data=request()->param();
       if (!empty($data['wechat_id']))$fans_condition['wechat_user.wechat_id']=$data['wechat_id'];
       if (!empty($data['key_words']))$fans_condition['wechat_user.nickname|wechat_user.city|wechat_user.province|wechat_user.country']=['like',"%{$data['key_words']}%"];
       if (!empty($data['group_id']))$fans_condition['wechat_user.group_id']=$data['group_id'];
       if (time_condition(input('start_time'),input('end_time')))$fans_condition['wechat_user.subscribe_time']=time_condition(input('start_time'),input('end_time'));
       $page=input('page_num',config('paginate.list_rows'));
       $user_data=Db::view('wechat_user')
       ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_user.wechat_id')
       ->view('wechat_user_group',['name'=>'group_name'],'wechat_user_group.id=wechat_user.group_id')
       ->order('wechat_user.id desc')->where($fans_condition)->paginate($page,false,['query'=>get_query()]);
    //        $user_temp=$user_data->toArray();
    //        $user_list=$user_temp['data'];
    //        if ($user_list){
    //            foreach ($user_list as $user_key=>$user_val){
    //                $group_name=db('wechat_user_group')->where(['admin_id'=>$this->uid,'id'=>$user_val['id']])->value('name');
    //                $user_list[$user_key]['group_name']=empty($group_name)?'未分组':$group_name;
    //                if (!empty($user_val['tag_list'])){
    //                    $tag_list=db('wechat_user_tag')->where(['admin_id'=>$this->uid,'id'=>['in',explode(',',explode(',', $user_val['tag_list']))]])->column('name');
    //                }
    //                $user_list[$user_key]['tag_list']=empty($tag_list)?[]:$tag_list;
    //            }
    //        }
       $wechat_group=db('wechat_user_group')->where('admin_id',$this->uid)->whereOr('id',-1)->whereOr('id',-2)->select();
       $this->assign('wechat_group',$wechat_group);
       $this->assign('page',show_page($user_data->render()));
       $this->assign('fans_list',$user_data);
       return $this->fetch();
    }
   
    /**
    * 拉黑
    */
    public function pullBlack(){
       $return_data=$this->changeStatus('wechat_user','is_pull_black',5,['btn-yellow','btn-inverse']);
       $data=request()->param();
       $options_list=config('wechatConfig');
       $options=$options_list['wechat_option_'.$data['w']];
       $wechat_api = new wechatApi($options);
       $openid=db('wechat_user')->where(['admin_id'=>$this->uid,'wechat_id'=>$data['w'],'id'=>$data['x']])->value('openid');
       if ($return_data['code']==1){
           if ($return_data['msg']==1){
               //拉黑
               $return=$wechat_api->user->batchBlock([$openid])->toArray();
           }
           else{
               //取消拉黑
               $return=$wechat_api->user->batchUnblock([$openid])->toArray();  //取消拉黑
           }
       }
       return $return_data;  
    }
    
    public function editFans(){
        $id=input('id');
        $user_info=$this->getDataInfo('wechat_user', $id);
        $this->assign('user_info',$user_info);
        return $this->fetch();
    }
    
    public function saveFans(){
       $data=request()->param();
       $save_result=$this->saveData('wechat_user', $data);
       if ($save_result['code']==1){
           $options_list=config('wechatConfig');
           $options=$options_list['wechat_option_'.$data['w']];
           $wechat_api = new wechatApi($options);
           $openid=db('wechat_user')->where(['admin_id'=>$this->uid,'wechat_id'=>$data['w'],'id'=>$data['id']])->value('openid');
           $wechat_api->user->remark($openid, $data['remark']); // 成功返回boolean
       }
       return $save_result;
    }
   
    /**
    * 粉丝分组(星标用户组不可删默认3个分组不可删)
    */
    public function fansGrouping(){
       config('default_ajax_return','json');
       $data=request()->param();
       if (empty($data['user_ids']))return ['code'=>0,'msg'=>'请选择要分组的用户'];
       if (count(explode(',', $data['user_ids']))>50)return ['code'=>0,'msg'=>'每次分组用户数量不可超过50个'];
       if (empty($data['group_id']))return ['code'=>0,'msg'=>'请选择组'];
       $save_result=db('wechat_user')->where([
           'admin_id'=>$this->uid,
           'id'=>['in',explode(',', $data['user_ids'])]
       ])->setField([
           'group_id'=>$data['group_id'],
           'update_time'=>time()
       ]);
       if ($save_result){
           return ['code'=>1,'msg'=>'操作成功','url'=>new_url('admin/Wechat/fansList')];
       }
       else{
           return ['code'=>0,'msg'=>'请再操作一次'];
       }
    /*不进行微端同步操作*/
    //        $options_list=config('wechatConfig');
    //        $options=$options_list['wechat_option_'.$data['wechat_id']];
    //        $wechat_api = new wechatApi($options);
    //        $openids=db('wechat_user')->where([
    //            'admin_id'=>$this->uid,
    //            'wechat_id'=>$data['wechat_id'],
    //            'id'=>['in',$data['user_ids']]])->column('openid');
    //        //获取微信组ID
    //        $group_id=db('wechat_media_id')->where([
    //            'type'=>5,
    //            'admin_id'=>$this->uid,
    //            'media_id'=>$data['group_id'],
    //            'wechat_id'=>$data['wechat_id']
    //        ])->value('wechat_media_id');
    //        $return=$wechat_api->user_group->moveUsers($openids,$group_id);
    
    }
   
    /**
    * 群发信息记录
    * 可根据组别，或者粉丝
    */
    public function massMsgList(){
       $mass_condition=['wechat_mass_msg.admin_id'=>$this->uid];
       $data=request()->param();
       if (!empty($data['msg_type']))$mass_condition['wechat_mass_msg.msg_type']=$data['msg_type'];
       if (!empty($data['wechat_id']))$mass_condition['wechat_mass_msg.wechat_id']=$data['wechat_id'];
       if (time_condition(input('start_time'),input('end_time')))$mass_condition['mass_time']=time_condition(input('start_time'),input('end_time'));
       $page=input('page_num',config('paginate.list_rows')); 
       $mass_data=Db::view('wechat_mass_msg')
       ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_mass_msg.wechat_id')
       ->where($mass_condition)->paginate($page,false,['query'=>get_query()]);
       $mass_temp=$mass_data->toArray();
       $mass_list=$mass_temp['data'];
       if ($mass_list){
           foreach ($mass_list as $mass_key=>$mass_val){
               if ($mass_val['mass_obj']!='所有用户'){
                   $nick_name=db('wechat_user')->where([
                       'admin_id'=>$this->uid,
                       'id'=>['in',explode(',', $mass_val['mass_obj'])],
                       'wechat_id'=>$mass_val['wechat_id']
                        ])->column('nickname');
                   $mass_list[$mass_key]['mass_obj']=implode('，', $nick_name);
               }
           }
       }
       $this->assign('mass_list',$mass_list);
       $this->assign('page',show_page($mass_data->render()));
       return $this->fetch();
    }
   
    /**
    * 编辑群发信息
    * 可根据组别，或者粉丝
    */
    public function editMassMsg(){
       config('default_ajax_return','json');
       $news_list=db('news')->where([
           'admin_id'=>$this->uid,
           'is_display'=>1
       ])->order('sort asc')->field('content',true)->select();
       $this->assign('news_list',$news_list);
       $data=request()->param();
       if (!empty($data['user_ids'])){
           if (count($data['user_ids'])<=1){
               return ['code'=>0,'msg'=>'群发人数不可少于两人'];
           }
           $data['user_ids']=implode(',',$data['user_ids']);
       }
       else{
           $data['user_ids']=null;
       }
       if (empty($data['wechat_id'])){
           return ['code'=>0,'msg'=>'请选择要群发的公众号'];
       } 
       else {
           $check_type=db('wechat_info')->where([
               'admin_id'=>$this->uid,
               'id'=>$data['wechat_id']
           ])->field('wechat_type,title')->find();
           if (!in_array($check_type['wechat_type'], [2,4])){
               
               return ['code'=>0,'msg'=>$check_type['title'].'不支持群发信息'];
           }
       }
       //print_r($data);die;
       config('default_ajax_return','html');
       $this->assign('param_data',$data);
       return $this->fetch();
    }
    
    /**
    * 群发信息(并保存记录)
    * 可根据组别或者标签、粉丝（粉丝至少两个）
    */
    public function massMsg(){
       config('default_ajax_return','json');
       $data=request()->param();
       $user_condition=[
           'admin_id'=>$this->uid,
           'wechat_id'=>$data['wechat_id']
       ];
       if (!empty($data['oid']))$user_condition['id']=['in',explode(',', $data['oid'])];
       if (!empty($data['group_id']))$user_condition['group_id']=$data['group_id'];
       if (isset($user_condition['id']) || isset($user_condition['group_id'])){
           $openids=db('wechat_user')->where($user_condition)->column('openid');
           if (count($openids)<=1) return ['code'=>0,'msg'=>'群发人数不可少于两人'];
       }
       else{
           $openids=null;
       }
       switch ($data['msg_type']) {
           case 1: //文本消息类型，所对应的 `$message` 为一个文本字符串
               $result=$this->massMsgHandle($data['wechat_id'], $data['content'], 1,$openids);
               break;
           case 2:   //图片消息类型，所对应的 `$message` 为 media_id
               $result=$this->massMsgHandle($data['wechat_id'], $data['img_id'], 2,$openids);
               break;
           case 3:  //图文消息类型，所对应的 `$message` 为 media_id
               $result=$this->massMsgHandle($data['wechat_id'], $data['news_ids'], 3,$openids);
               break;
           case 4:  //视频消息类型，所对应的 `$message` 为 media_id
               $result=$this->massMsgHandle($data['wechat_id'], $data['video_id'], 4,$openids);
               break;
           case 5: //语音消息类型，所对应的 `$message` 为 media_id
                $result=$this->massMsgHandle($data['wechat_id'], $data['voice_id'], 5,$openids);
               break;
           case 8: //卡券消息类型，所对应的 `$message` 为 card_id
               $result=$this->massMsgHandle($data['wechat_id'], $data['card_id'], 8,$openids);
           default:
               return ['code'=>0,'msg'=>'不支持此信息类型'];
       }
       if ($result['errcode']==0){
           //保存记录
           if (isset($user_condition['id']) || isset($user_condition['group_id'])){
               $obj=implode(',', $openids);
           }
           else{
               $obj='所有用户';
           }
           $save_data=[
               'admin_id'=>$this->uid,
               'wechat_id'=>$data['wechat_id'],
               'msg_type'=>$data['msg_type'],
               'msg_id'=>$result['msg_id'],
               'content'=>$data['content'],
               'img_id'=>$data['img_id'],
               'news_ids'=>$data['news_ids'],
               'video_id'=>$data['video_id'],
               'voice_id'=>$data['voice_id'],
               'mass_time'=>time(),
               'mass_obj'=>$obj,
               'create_time'=>time(),
               'update_time'=>time()
           ];
           db('wechat_mass_msg')->insert($save_data);
           return ['code'=>1,'msg'=>'成功群发','url'=>new_url('admin/Wechat/fansList')];
       }
    }
   
    /**
    * 客服列表（每个公众号最多10个人）
    */
    public function customList(){
        config('default_ajax_return','json');
        return ['code'=>0,'msg'=>'模板不存在'];
        return $this->fetch();
    }
    
    /**
    * 编辑、添加客服
    * @param $id
    */
    public function editCustom(){
        $id=input('id');
        return $this->fetch();
    }
    
    /**
    * 删除
    */
    public function delCustom(){
       
    }
    
    /**
    * 保存
    */
    public function saveCustom(){
       
    }
    
    /**
    * 客服发送信息
    */
    public function customSendMsg(){
        config('default_ajax_return','json');
        return ['code'=>0,'msg'=>'模板不存在'];
        return $this->fetch();
    }
    
    /**
    * 模板行业数据
    */
    public function getIndustryList(){
        config('default_ajax_return','json');
        return ['code'=>0,'msg'=>'模板不存在'];
        return $this->fetch();
       
    }
   
    /**
    * 修改账号行业数据
    */
    public function setIndustry(){
        config('default_ajax_return','json');
        return ['code'=>0,'msg'=>'模板不存在'];
        return $this->fetch();
    
    }
    
    /**
    * 模板列表
    */
    public function templatesList(){
       config('default_ajax_return','json');
       return ['code'=>0,'msg'=>'模板不存在'];
       return $this->fetch();
       
    }
   
   /**
    * 编辑添加模板
    */
    public function editTemplate(){
        config('default_ajax_return','json');
        return ['code'=>0,'msg'=>'模板不存在'];
        return $this->fetch();
        
    }
   
    /**
     * 保存模板并获取模板ID
     */
    public function saveTemplate(){
        
    }
    
    /**
     * 删除模板指定ID
     */
    public function delTemplate(){
        
    }
    
    /**
     * 卡券列表
     */
    public function cardList(){
        $data=request()->param();
        $card_condition=['admin_id'=>$this->uid];
        if (!empty($data['code_type']))$card_condition['code_type']=$data['code_type'];
        if (!empty($data['card_type']))$card_condition['card_type']=$data['card_type'];
        if (!empty($data['key_words']))$card_condition['title|brand_name']=['like',"%{$data['key_words']}%"];
        $page=input('page_num',config('paginate.list_rows'));
        $card_list=db('wechat_card')->where($card_condition)->field('base_info,advanced_info',true)
        ->paginate($page,false,['query'=>get_query()]);
        $this->assign('page',show_page($card_list->render()));
        $this->assign('card_list',$card_list);
        return $this->fetch();
    }
    
    /**
     * 添加、编辑卡券
     */
    public function editCard(){
        $id=input('id');
        $card_info=$this->getDataInfo('wechat_card', $id);
        $wecaht_list=db('wechat_info')->where([
            'admin_id'=>$this->uid,
            'is_enabled'=>1,
            'wechat_type'=>['in',[2,4]]
        ])->select();
        $this->assign('wechat_list',$wecaht_list);
        $this->assign('card_info',$card_info);
        return $this->fetch();
    }
    
    /**
     * 保存卡券（返回微信端卡券ID）
     */
    public function saveCard(){
        config('default_ajax_return','json');
        $data=request()->param();
        //print_r($data);die;
        $wechat_card_color=config('wechatExt.wechat_card_color');
        $wechat_code_type=config('wechatExt.wechat_code_type');
        foreach ($wechat_card_color as $val){
            if (!empty($val['color']))$colors[]=$val['color'];
            if ($data['color']==$val['color']){
                $color_name=$val['code'];
            }
        }
        $colors=implode(',', $colors);
        foreach ($wechat_code_type as $code_val){
            if (!empty($code_val['code']))$codes[]=$code_val['code'];
            if ($data['code_type']==$code_val['code']){
                $code_type=$code_val['type'];
            }
        }
        $codes=implode(',', $codes);
        $rule=[
            ['title','require|length:1,9','请填写卡券名称|名称长度最多9个字符'],
            ['brand_name','require|length:1,12','请填写商户名称|商户名称长度最多12个字符'],
            ['logo_url','require','商户LOGO不可为空'],
            ['wechat_id','require','请选择应用卡券的微信公众号'],
            ['color',"require|in:{$colors}",'请选择背景色|背景色非法操作'],
            ['quantity','require|number|between:1,100000000','请填写库存|请填写数字|库存最小为1最大1亿'],
            ['code_type',"require|in:{$codes}",'请选择核销码类型|核销码类型非法操作'],
            ['begin_time','requireIf:type,1|dateFormat:Y-m-d','生产日期必须填写|生产日期格式错误'],
            ['type','require|in:1,2','日期方式必须选择|日期方式非法操作'],
            ['end_time','requireIf:type,1|dateFormat:Y-m-d','有效日期必须填写|有效日期格式错误'],
            ['fixed_term','requireIf:type,2|number|>=:1','请填写卡券保留时间（单位：天）|请输入数字|卡券保留时间至少一天'],
            ['fixed_begin_term','requireIf:type,2|number|>=:0','请填写商户名称|请输入数字|卡券默认启用是当天'],
            ['deal_detail','requireIf:code_type,1','请输入团购详情'],
            ['notice','require|length:1,16','请填写卡券提现标语|最多可输入16个字符'],
            ['description','require|length:1,1024','请填写卡券使用说明|最多可输入1024个字符'],
            ['least_cost','requireIf:code_type,2|number|>=:0','请填写商代金券起用金额|请输入数字|代金券必须大于或等于0'],
            ['reduce_cost','requireIf:code_type,2|number|>:0','请填写商户名称|请输入数字|减免金额必须大于0'],
            ['discount','requireIf:code_type,3|number|between:1,10','请填写打折额度|请输入数字|打折额度只能1到10'], //有个坑。。。。。7折传至微信时需要10-7=3！！！
            ['gift','requireIf:code_type,4','请填写商户名称|请输入数字|卡券默认启用是当天'],
            ['default_detail','requireIf:code_type,5|length:1,300|>=:0','请填写商户名称|请输入数字|卡券默认启用是当天'],
            ['get_limit','require|number|>:0','请填写每人可领券的数量限制|请输入数字|数量必须大于0'],
            ['use_limit','require|number|>:0','请填写每人可使用券的数量限制|请输入数字|数量必须大于0'],
            ['text_image_list','requireIf:code_type,5','请填写图文']
        ];
        $validate=new \Think\Validate($rule);
        $result=true;//$validate->check($data);
        if ($result){
            //一系列处理
            if ($data['type']==1){
                //固定日期范围
                //判断日期范围是否合法 必须大于一天，结束日期大于或等于第二天，开始日期不填默认当天
                $begin_timestamp=empty($data['begin_time'])?time():strtotime($data['begin_time']);
                $end_timestamp=empty($data['end_time'])?strtotime("+1 days"):strtotime($data['end_time']);
                if ($end_timestamp<$begin_timestamp){
                    return ['code'=>0,'msg'=>'卡券有效时间格式错误'];
                }
                $date_info=[
                    'type'=>'DATE_TYPE_FIX_TIME_RANGE',
                    "begin_timestamp" => $begin_timestamp,
                    "end_timestamp" => $end_timestamp
                ];
            }
            else{
                //固定时长
                $date_info=[
                    'type'=>'DATE_TYPE_FIX_TERM',
                    "fixed_term" => empty($data['fixed_term'])?1:$data['fixed_term'],
                    "fixed_begin_term" => empty($data['fixed_begin_term'])?time():$data['fixed_begin_term']
                ];
            }
            $base_info=[
                "logo_url" => $data['logo_url'],
                "brand_name" => $data['brand_name'],
                "code_type" => $code_type,
                "title" => $data['title'],
                "color" => $color_name,
                "notice" => $data['notice'],
                "service_phone" => empty($data['service_phone'])?null:$data['service_phone'],
                "description" => $data['description'],
                "date_info" => $date_info,
                "sku" => ["quantity" => empty($data['quantity'])?5000:$data['quantity']],
                "center_title" => empty($data['center_title'])?'立即使用':$data['center_title'],
                "center_sub_title" => empty($data['center_sub_title'])?'立即享受优惠':$data['center_sub_title'],
                "center_url" => null,
                "custom_url_name" => null,
                "custom_url" => null,
                "custom_url_sub_title" => null,
                "promotion_url_name" => null, //"更多优惠",
                "promotion_url" => null,//"http://www.qq.com",
                "source" => "微信卡券"
            ];
            //判断是否启用小程序（）
            //卡券类型数据
            switch ($data['card_type']){
                case 1: //团购券
                    $especial=["deal_detail" => empty($data['deal_detail'])?'暂无详情介绍':$data['deal_detail']];
                    $card_type='GROUPON';
                    break;
                case 2: //代金券
                    $especial=[
                    "least_cost" => empty($data['least_cost'])?'0':$data['least_cost'],
                    'reduce_cost' =>$data['reduce_cost']*100
                    ];
                    $card_type='CASH';
                    break;
                case 3: //折扣券
                    $especial=["discount" => (10-$data['discount'])];
                    $card_type='DISCOUNT';
                    break;
                case 4: //兑换券
                    $especial=["gift" => $data['gift']];
                    $card_type='GIFT';
                    break;
                case 5:  //优惠券
                    $especial=["default_detail" => $data['default_detail']];
                    $card_type='GENERAL_COUPON';
                    break;   
            }
                    
//                     $advanced_info =[
//                             "use_condition" => [
//                                     "accept_category" => empty($data['accept_category'])?null:$data['accept_category'],
//                                     "reject_category" => empty($data['reject_category'])?null:$data['reject_category'],
//                                     "can_use_with_other_discount" => empty($data['can_use_with_other_discount'])?false:true
//                                 ],
//                             "abstract" => [
//                                     "abstract" => empty($data['abstract'])?null:$data['abstract'],
//                                     "icon_url_list" => [
//                                             "http://mmbiz.qpic.cn/mmbiz/p98FjXy8LacgHxp3sJ3vn97bGLz0ib0Sfz1bjiaoOYA027iasqSG0sjpiby4vce3AtaPu6cIhBHkt6IjlkY9YnDsfw/0"
//                                         ]
//                                 ],

//                             "text_image_list" => [
//                                     [
                                        
//                                             "image_url" => "http://mmbiz.qpic.cn/mmbiz/p98FjXy8LacgHxp3sJ3vn97bGLz0ib0Sfz1bjiaoOYA027iasqSG0sjpiby4vce3AtaPu6cIhBHkt6IjlkY9YnDsfw/0",
//                                             "text" => "此菜品精选食材，以独特的烹饪方法，最大程度地刺激食 客的味蕾"
//                                     ],

//                                     [
                                        
//                                             "image_url" =>"http://mmbiz.qpic.cn/mmbiz/p98FjXy8LacgHxp3sJ3vn97bGLz0ib0Sfz1bjiaoOYA027iasqSG0sjpiby4vce3AtaPu6cIhBHkt6IjlkY9YnDsfw/0",
//                                             "text" => "此菜品迎合大众口味，老少皆宜，营养均衡"
//                                     ]

//                                 ],
//                             "time_limit" => [
//                                     [
//                                             "type" => 'MONDAY',
//                                             "begin_hour" => 0,
//                                             "end_hour" => 10,
//                                             "begin_minute" => 10,
//                                             "end_minute" => 59
//                                         ],
//                                     [
//                                             "type" => 'HOLIDAY'
//                                     ]
//                                 ],
//                             "business_service" => [
//                                     "BIZ_SERVICE_FREE_WIFI", 
//                                     "BIZ_SERVICE_WITH_PET", 
//                                     "BIZ_SERVICE_FREE_PARK",
//                                     "BIZ_SERVICE_DELIVER", 
//                                 ]
//                    ];
            $data_info=[
                "service_phone" => empty($data['service_phone'])?null:$data['service_phone'],
                "center_title" => empty($data['center_title'])?'立即使用':$data['center_title'],
                "center_sub_title" => empty($data['center_sub_title'])?'立即享受优惠':$data['center_sub_title'],
                "center_url" => null,
                "custom_url_name" => null,
                "custom_url" => null,
                "custom_url_sub_title" => null,
                "promotion_url_name" => null, //"更多优惠",
                "promotion_url" => null,//"http://www.qq.com",
                "source" => "微信卡券"
            ];
            //保存卡券至本地
            $save_data=[
                'admin_id'=>$this->uid,
                'title'=>$data['title'],
                'logo_url'=>$data['logo_url'],
                'card_type'=>$data['card_type'],
                'code_type'=>$data['card_type'],
                'brand_name'=>$data['brand_name'],
                'color'=>$data['color'],
                'deal_detail'=>$data['deal_detail'],
                'least_cost'=>$data['least_cost'],
                'reduce_cost'=>$data['reduce_cost'],
                'data_info'=>json_encode($data_info),
                'discount'=>$data['discount'],
                'default_detail'=>$data['default_detail'],
                'gift'=>$data['gift'],
                'notice'=>$data['notice'],
                'description'=>$data['description'],
                'quantity'=>$data['quantity'],
                'type'=>$data['type'],
                'begin_time'=>empty($data['begin_time'])?time():strtotime($data['begin_time']),
                'end_time'=>empty($data['end_time'])?strtotime("+1 days"):strtotime($data['end_time']),
                'fixed_term'=>empty($data['fixed_term'])?1:$data['fixed_term'],
                'fixed_begin_term'=>empty($data['fixed_begin_term'])?time():$data['fixed_begin_term'],
                'is_enabled'=>empty($data['is_enabled'])?0:1,
                'create_time'=>time(),
                'update_time'=>time()
            ];
            $options_list=config('wechatConfig');
            foreach ($data['wechat_id'] as $wechat_id){
                $options=$options_list['wechat_option_'.$wechat_id];
                $wechat_api = new wechatApi($options);
                $result = $wechat_api->card->create($card_type, $base_info, $especial,$advanced_info=[]); 
//                 $cards = [
//                     'action_name' => 'QR_CARD',
//                     'expire_seconds' => 1800,
//                     'action_info' => [
//                         'card' => [
//                             'card_id' => $result['card_id'],
//                             'is_unique_code' => false,
//                             'outer_id' => 1,
//                         ],
//                     ],
//                 ];
//                 $result_11 = $wechat_api->card->QRCode($cards);
                   
                $save_data['card_id']=$result['card_id'];
                db('wechat_card')->insert($save_data);
            }
            return ['code'=>1,'msg'=>'操作成功','url'=>new_url('admin/Wechat/cardList')];
        }
        else{
            return ['code'=>0,'msg'=>$validate->getError()];
        }
    }
    
    /**
     * 删除卡券（同时删除微信端）
     */
    public function delCard(){
    
    }
    
    /**
     * 卡券是否启用
     */
    public function changeCardStatus(){
    
    }
    
    /**
     * 卡券排序
     */
    public function orderCard(){
    
    }
    
    /**
     * 卡券code列表（用户领取索取的数据）
     */
    public function cardCodeList(){
        
        config('default_ajax_return','json');
        return ['code'=>0,'msg'=>'模板不存在'];
        return $this->fetch();
    }
    
    /**
     * 群发信息处理
     * $obj 发送对象，可以openid（至少两个）；可以是标签ID；可以是分组ID；为空是所有用户
     */
    private function massMsgHandle($wechat_id,$data_info,$msg_type,$obj=null){
        $options_list=config('wechatConfig');
        $options=$options_list['wechat_option_'.$wechat_id];
        $wechat_api = new wechatApi($options);
        $broadcast = $wechat_api->broadcast;
        switch ($msg_type){
            case 1:  //文本
                $mass_type=$broadcast::MSG_TYPE_TEXT;
                $message= $data_info;
                break;
            case 2: //图片
                //先判断图片数据是否已经存在 media_id有效数据；否则先上传图片获取 media_id
                $img_condition=['admin_id'=>$this->uid,'id'=>$data_info,'is_enabled'=>1];
                $img_info=db('img_data')->where($img_condition)->field('id,path_url')->find();
                $return_data=$this->getWechatMedia($wechat_id, $img_info, 1);
                $mass_type=$broadcast::MSG_TYPE_IMAGE;
                break;
            case 3: //图文
                $news_condition=[
                'admin_id'=>$this->uid,
                'id'=>['in',explode(',', $data_info)],
                'is_display'=>1
                ];
                $news_list=db('news')->where($news_condition)->field('content',true)->select();
                if ($news_list){
                    $news=[];
                    foreach ($news_list as $news_val){
                        $return=$this->getWechatMedia($wechat_id, $news_val, 2);
                        $news[] = new Article([
                            'thumb_media_id'=>$return['thumb_media_id'],
                            'author'=>$news_val['author'],
                            'title'=>$news_val['title'],
                            'content'=>$news_val['content'],
                            'digest'=>$news_val['summary'],
                            'source_url'=>empty($news_val['jump_url'])?url('wap/News/newsList',['uid'=>$this->uid,'id'=>$news_val['id'],'category_id'=>$news_val['category_id'],'block_id'=>$news_val['block_id']]):$news_val['jump_url'],
                            'show_cover'=>1,
                        ]);
                    } 
                    $return_data=$wechat_api->material->uploadArticle($news);
                    $mass_type=$broadcast::MSG_TYPE_NEWS;
                }
                break;
            case 4:  //视频
                $video_condition=[
                'admin_id'=>$this->uid,
                'id'=>$data_info,
                'is_enabled'=>1
                ];
                $video_info=db('video_data')->where($video_condition)->field(true)->find();
                $return_data=$this->getWechatMedia($wechat_id, $video_info, 3);
                $mass_type=$broadcast::MSG_TYPE_VIDEO;
                break;
            case 5:  //音频
                $audio_condition=[
                'admin_id'=>$this->uid,
                'id'=>$data_info,
                'is_enabled'=>1
                ];
                $audio_info=db('audio_data')->where($audio_condition)->field(true)->find();
                
                $return_data=$this->getWechatMedia($wechat_id, $audio_info, 3);
                $mass_type=$broadcast::MSG_TYPE_VOICE;
                break;
        }
        if ($msg_type==1){
            $message=$data_info;
        }
        else{
            $message=$return_data['media_id'];
        }
        $send_result=$wechat_api->broadcast->send($mass_type, $message, $obj)->toArray();
        //$status=$broadcast->status($send_result['msg_id'])->toArray(); //查询群发状态
        return $send_result;
    }
    
    /**
     * 保存从微信服务端返回的媒体ID
     * @param $wechat_id
     * @param $msg_id
     * @param $type
     * @param $media_id
     * @param $thumb_media_id
     */
    private function saveWechatMedia($wechat_id,$msg_id,$type,$result){
        $save_data=[
            'admin_id'=>$this->uid,
            'wechat_id'=>$wechat_id,
            'media_id'=>$msg_id,
            'type'=>$type,
            'wechat_media_id'=>!empty($result['media_id'])?$result['media_id']:null,
            'wechat_thumb_media_id'=>!empty($result['thumb_media_id'])?$result['thumb_media_id']:null,
            'url'=>!empty($result['url'])?$result['url']:null,
            'create_time'=>time(),
            'update_time'=>time()
        ];
        db('wechat_media_id')->insert($save_data);
    }
    
    /**
     * 获取微信服务器保存下来的媒体ID
     */
    private function getWechatMedia($wechat_id,$media_data,$msg_type){
        $options_list=config('wechatConfig');
        $options=$options_list['wechat_option_'.$wechat_id];
        $wechat_api = new wechatApi($options);
        $wechat_media_condition=[
            'admin_id'=>$this->uid,
            'media_id'=>$media_data['id'],
            'wechat_id'=>$wechat_id,
            'type'=>$msg_type
        ];
        
        $wechat_media_info=db('wechat_media_id')->where($wechat_media_condition)->find();
        if ($wechat_media_info){
            return [
                'media_id'=>$wechat_media_info['wechat_media_id'],
                'thumb_media_id'=>$wechat_media_info['wechat_thumb_media_id'],
                'url'=>$wechat_media_info['url']
            ];
        }
        else{
            switch ($msg_type){
                case 1: //图片
                    $result = $wechat_api->material->uploadImage(absolute_path($media_data['path_url']))->toArray();
                    $this->saveWechatMedia($wechat_id, $media_data['id'], 1, $result);
                    break;
                case 2:  //图文（单图文！！！）
                    $return = $wechat_api->material->uploadImage(absolute_path($media_data['cover_img']))->toArray();
                    $article = new Article([
                        'thumb_media_id'=>$return['media_id'],
                        'author'=>$media_data['author'],
                        'title'=>$media_data['title'],
                        'content'=>$media_data['content'],
                        'digest'=>$media_data['summary'],
                        'source_url'=>empty($media_data['jump_url'])?url('wap/News/newsList',['uid'=>$this->uid,'id'=>$media_data['id'],'category_id'=>$media_data['category_id'],'block_id'=>$media_data['block_id']]):$media_data['jump_url'],
                        'show_cover'=>1,
                    ]);
                    $return_article=$wechat_api->material->uploadArticle($article)->toArray();
                    $result=['media_id'=>$return_article['media_id'],'thumb_media_id'=>$return['media_id']];
                    $this->saveWechatMedia($wechat_id, $media_data['id'], 2,$result);                   
                    break;
                case 3:  //音频
                    $result=$wechat_api->material->uploadVideo(absolute_path($media_data['path_url']), $media_data['name'], $media_data['describe'])->toArray();
                    
                    $this->saveWechatMedia($wechat_id, $media_data['id'], 3, $result);
                    break;
                case 4:  //视频
                    $result=$wechat_api->material->uploadVoice(absolute_path($media_data['path_url']))->toArray();
                    $this->saveWechatMedia($wechat_id, $media_data['id'], 4, $result);            
                    break;
                case 5:  //微信用户组
                    
                    break;
                case 6:  //微信标签组
                    
                    break;
            } 
            return [
                'media_id'=>$result['media_id'],
                'thumb_media_id'=>empty($result['thumb_media_id'])?null:$result['thumb_media_id'],
                'url'=>empty($result['url'])?null:$result['url'],
            ];
        }
    }
}





