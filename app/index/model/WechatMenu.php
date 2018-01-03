<?php  
namespace app\index\model;
use think\Model;
use think\db;
class WechatMenu extends Model{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $uid;

    public function getList($condition){
        $list = Db::view('wechat_menu')
        ->view('wechat_info',['title'=>'wechat_title'],'wechat_info.id=wechat_menu.wechat_id','RIGHT')
        ->where($condition)->order('wechat_menu.sort asc')->select();
        //$list = WechatMenu::where('admin_id','10000')->order('sort asc')->select();
        return $list;
    }
    
    public function page($condition,$page=1){
        $menu_data = db('wechat_menu')->where($condition)->paginate($page,false,['query'=>get_query()]);
        return show_page($menu_data->render());
    }
    
    public function getEditMenu($id,$uid){
        $menu_info=WechatMenu::find($id);
        $return_data=$this->getEditWechatMsg($menu_info,$uid);
        return $return_data;
    }
    
    private function getEditWechatMsg($msg_info=null,$uid){
        $news_list=db('news')->where(['admin_id'=>$uid,'is_display'=>1])->order('sort asc')
        ->field('id,title,key_words')->select();
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
}
