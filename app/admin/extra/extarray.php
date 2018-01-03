<?php 
 $select_config = [];
    $audio_type = [
        ['code'=>'','title'=>'类型'],
        ['code'=>1,'title'=>'音乐'],
        ['code'=>2,'title'=>'语音 '],
        ['code'=>3,'title'=>'文化'],
        ['code'=>4,'title'=>'教程'],
        ['code'=>5,'title'=>'生活'],
        ['code'=>6,'title'=>'新闻'],
        ['code'=>7,'title'=>'其他']
     ];
    $select_config['audio_type'] = $audio_type;
    $video_type = [
        ['code'=>'','title'=>'类型'],
        ['code'=>1,'title'=>'历史'],
        ['code'=>2,'title'=>'娱乐 '],
        ['code'=>3,'title'=>'文化'],
        ['code'=>4,'title'=>'教程'],
        ['code'=>5,'title'=>'生活'],
        ['code'=>6,'title'=>'新闻'],
        ['code'=>7,'title'=>'音乐MV'],
        ['code'=>8,'title'=>'其他']
    ];
    $select_config['video_type'] = $video_type;
    $img_type = [
        ['code'=>'','title'=>'类型'],
        ['code'=>1,'title'=>'娱乐'],
        ['code'=>2,'title'=>'表情'],
        ['code'=>3,'title'=>'文化'],
        ['code'=>4,'title'=>'教程'],
        ['code'=>5,'title'=>'生活'],
        ['code'=>6,'title'=>'新闻'],
        ['code'=>7,'title'=>'自然'],
        ['code'=>8,'title'=>'风景'],
        ['code'=>9,'title'=>'人文'],
        ['code'=>10,'title'=>'美女'],
        ['code'=>10,'title'=>'壁纸'],
        ['code'=>11,'title'=>'头像'],
        ['code'=>12,'title'=>'其他'],
    ];
    $select_config['img_type'] = $img_type;
    $select_config['file_type']=[
        ['code'=>1,'type'=>'audio','title'=>'音频'],
        ['code'=>2,'type'=>'video','title'=>'视频'],
        ['code'=>3,'type'=>'file','title'=>'文件']
    ];
    $field_type=[
        ['val'=>'TINYINT','title'=>'微型整数值（tinyint）','size'=>1],
        ['val'=>'SMALLINT','title'=>'小整数值（smallint）','size'=>2],
        ['val'=>'MEDIUMINT','title'=>'中整数值（mediumint）','size'=>3],
        ['val'=>'INT','title'=>'大整数值（int）','size'=>4],
        ['val'=>'BIGINT ','title'=>'极大整数值（bigint）','size'=>8],
        ['val'=>'FLOAT', 'title'=>'单精度浮点数值（float）','size'=>4],
        ['val'=>'DOUBLE','title'=>'双精度浮点数值（double）','size'=>8],
        ['val'=>'DECIMAL（decimal）','title'=>'小数值','size'=>''],
        ['val'=>'DATETIME','title'=>'混合日期和时间值（datetime） ','size'=>8],
        ['val'=>'CHAR','title'=>'定长字符串（char）','size'=>255],
        ['val'=>'VARCHAR','title'=>'变长字符串（varchar）','size'=>65535],
        ['val'=>'TINYBLOB', 'title'=>'不超过 255 个字符的二进制字符串 （tinyblob）','size'=>255],
        ['val'=>'TINYTEXT','title'=>'短文本字符串（tinytext）','size'=>255],
        ['val'=>'BLOB','title'=>'二进制形式的长文本数据 （blob）','size'=>65535],
        ['val'=>'TEXT','title'=>'长文本数据（text）','size'=>65535,],
        ['val'=>'MEDIUMBLOB','title'=>'二进制形式的中等长度文本数据 （mediumblob）','size'=>16777215],
        ['val'=>'MEDIUMTEXT','title'=>'中等长度文本数据 （mediumtext）','size'=>'16777215'],
        ['val'=>'LONGBLOB ','title'=>'二进制形式的极大文本数据（longblob） ','size'=>'4294967295'],
        ['val'=>'LONGTEXT','title'=>'极大文本数据 （longtext）','size'=>'4294967295']
    ];
    $select_config['field_type']=$field_type;
    $slide_scene=[
        ['code'=>1,'title'=>'后台顶部'],
        ['code'=>2,'title'=>'后台底部'],
        ['code'=>3,'title'=>'前台顶部'],
        ['code'=>4,'title'=>'前台左侧'],
        ['code'=>5,'title'=>'前台右侧'],
        ['code'=>6,'title'=>'前台底部'],
        ['code'=>7,'title'=>'前台漂浮'],
        ['code'=>8,'title'=>'移动端首页'],
        ['code'=>9,'title'=>'移动端底部'],
        ['code'=>10,'title'=>'介意顶部与底部之间']
    ];
    $select_config['scene_type']=$slide_scene;
    $font_type=[
        ['code'=>1,'title'=>'黄草字体','path'=>ROOT_PATH . 'public' . DS . 'pc-ui/font/1.ttf'],
        ['code'=>2,'title'=>'硬笔行书','path'=>ROOT_PATH . 'public' . DS . 'pc-ui/font/2.ttf'],
        ['code'=>3,'title'=>'楷体','path'=>ROOT_PATH . 'public' . DS . 'pc-ui/font/3.ttf'], 
    ];
    $select_config['font_type']=$font_type;
// $select_config['select_val'] = $select_val;
return $select_config;


