<?php


namespace app\public1jk\controller;


use think\Controller;
use think\Db;

class Article extends Controller{
//    保存文章
    public function article_preservation(){
//        标题
        $title=input('title');
//        内容
        $content=input('content');
//        用户id
        $user_id=input('user_id');
        if(empty($title)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'title为空',
            );
            return $jg_return;
        }
        if(empty($content)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'content为空',
            );
            return $jg_return;
        }
        if(empty($user_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'user_id为空',
            );
            return $jg_return;
        }
//        保存文章内容获取内容id
        $content_id=$this->article_preservation_content($content);
        if(!$content_id){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'保存文章内容错误',
            );
            return $jg_return;
        }
        $article_id=$this->article_preservation_id($title,$content_id,$user_id);
        if(!$article_id){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'保存文章id错误',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'保存文章成功',
            'article_id'=>$article_id,
        );
        return $jg_return;
    }
//    保存文章内容
    public function article_preservation_content($content){
        $data=array(
            'content'=>$content,
        );
        $data_return=Db::name('nav_article_content')->insert($data);
        if(!$data_return){
            return false;
        }
        $content_id = Db::name('nav_article_content')->getLastInsID();
        if(!$content_id){
            return false;
        }
        return $content_id;
    }
//    保存文章内容id
    public function article_preservation_id($title,$content_id,$user_id){
        //        当前时间
        $time=date('Y-m-d h:i:s', time());
        $data=array(
            'title'=>$title,
            'content_id'=>$content_id,
            'user_id'=>$user_id,
            'publish_time'=>$time,
            'see_frequency'=>'0',
            'comment_frequency'=>'0',
            'fabulous_frequency'=>'0',
        );
        $data_return=Db::name('nav_article_id')->insert($data);
        if(!$data_return){
            return false;
        }
        $article_id = Db::name('nav_article_id')->getLastInsID();
        if(!$article_id){
            return false;
        }
        return $article_id;
    }
//    文章id获取文章信息
    public  function article_obtain_article_id(){
//        文章id
        $article_id=input('article_id');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        $article_id_suz['id']=$article_id;
        $data_return=Db::table('nav_article_id')->where($article_id_suz)->find();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章id失败',
            );
            return $jg_return;
        }
        $article_id_suz['id']=$data_return['content_id'];
        $data_return_2=Db::table('nav_article_content')->where($article_id_suz)->find();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章内容失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>array(
            'id'=>$data_return['id'],
            'title'=>$data_return['title'],
            'content_id'=>$data_return['content_id'],
            'content'=>$data_return_2['content'],
            'user_id'=>$data_return['user_id'],
            'publish_time'=>$data_return['publish_time'],
            'see_frequency'=>$data_return['see_frequency'],
            'comment_frequency'=>$data_return['comment_frequency'],
            'fabulous_frequency'=>$data_return['fabulous_frequency'],
            )
        );
        return $jg_return;
    }
//    用户id获取文章信息
    public function article_obtain_user_id(){
        $user_id=input('user_id');
        if(empty($user_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'user_id为空',
            );
            return $jg_return;
        }
        $user_id_suz['user_id']=$user_id;
        $data_return=Db::table('nav_article_id')->where($user_id_suz)->select();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章id失败',

            );
            return $jg_return;
        }

        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
            'length'=>count($data_return),
        );
        return $jg_return;
    }
//    文章id获取文章内容
    public function article_obtain_article_id_content(){
        $content_id=input('content_id');
        if(empty($content_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'content_id为空',
            );
            return $jg_return;
        }
        $content_id_suz['id']=$content_id;
        $data_return=Db::table('nav_article_content')->where($content_id_suz)->find();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章内容失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
//    删除文章
    public function article_delete_article(){
        $article_id=input('article_id');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        $article_id_suz['id']=$article_id;
        //获取文章内容id
        $data_return=Db::table('nav_article_id')->where($article_id_suz)->find();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章id失败',
            );
            return $jg_return;
        }
        $content_id=$data_return['content_id'];
        $content_id_suz['id']=$content_id;
//        删除文章内容
        $data_return=Db::table('nav_article_content')->where($content_id_suz)->delete();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'删除文章评论id失败',
            );
//            return $jg_return;
        }
//        删除点赞
        $article_id_suz_2=array('article_id'=>$article_id,);
        $data_return=Db::table('nav_article_fabulous_frequency')->where($article_id_suz_2)->delete();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'删除文章点赞id失败',
            );
//            return $jg_return;
        }
//        删除评论内容
        $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz_2)->select();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章评论内容id失败',
            );
//            return $jg_return;
        }
        foreach($data_return as $value){
            $content_id=$value['content_id'];
            $content_id_suz=array('id'=>$content_id);
            $data_return=Db::table('nav_article_comment_content')->where($content_id_suz)->delete();
//            if(!$data_return){
//                $jg_return=array(
//                    'code'=>'0',
//                    'msg'=>'删除文章评论内容失败',
//                );
//                return $jg_return;
//            }
        }
        $data_return=Db::table('nav_article_fabulous_frequency')->where($article_id_suz_2)->delete();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'删除文章评论id失败',
            );
//            return $jg_return;
        }
//        删除文章id
        $data_return=Db::table('nav_article_id')->where($article_id_suz)->delete();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'删除文章id失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'删除文章成功',
        );
        return $jg_return;
    }
//    更改文章标题-内容
    public function article_modify_content(){
        $article_id=input('article_id');
        $title=input('title');
        $content=input('content');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        if(empty($title)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'title为空',
            );
            return $jg_return;
        }
        if(empty($content)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'content为空',
            );
            return $jg_return;
        }
//        查询文章内容id
        $article_id_suz['id']=$article_id;
        $data_return=Db::table('nav_article_id')->where($article_id_suz)->find();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询文章id错误',
            );
            return $jg_return;
        }
        $content_id=$data_return['content_id'];
        if($data_return['title']!=$title){
            $title_suz['title']=$title;
            $data_return=Db::table('nav_article_id')->where($article_id_suz)->update($title_suz);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更改文章标题错误',
                );
                return $jg_return;
            }
        }
        $content_id_suz['id']=$content_id;
        $data_return=Db::table('nav_article_content')->where($content_id_suz)->find();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询文章内容id错误',
            );
            return $jg_return;
        }
        if($data_return['content']!=$content){
            $content_suz['content']=$content;
            $data_return=Db::table('nav_article_content')->where($content_id_suz)->update($content_suz);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更改文章内容错误',
                );
                return $jg_return;
            }
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'更改文章成功',
        );
        return $jg_return;
    }
//    修改文章评论-查看-点赞次数
    public function article_modify_frequency(){
        $article_id=input('article_id');
        $user_id=input('user_id');
        $type=input('type');
        $increase=input('increase');
        $content=input('content');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        if(empty($type)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'type为空',
            );
            return $jg_return;
        }
        if(empty($user_id)){
            if(empty($increase)){
                if($increase!='0'){
                    $jg_return=array(
                        'code'=>'0',
                        'msg'=>'increase为空',
                    );
                    return $jg_return;
                }
            }
            $data_return=$this->article_modify_frequency_1($article_id,$increase,$type);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更改错误',
                );
                return $jg_return;
            }
            $jg_return=array(
                'code'=>'1',
                'msg'=>'更改成功',
            );
            return $jg_return;
        }else{
            $article_id_suz['user_id']=$user_id;
            $article_id_suz['article_id']=$article_id;
            switch($type){
                case 2:
                    $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz)->find();
                    if(!$data_return){
                        //增加评论
                        if(empty($content)){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'content为空',
                            );
                            return $jg_return;
                        }
                        echo 1;
                        //保存评论内容后返回id在保存id
                        $content_suz=array('content'=>$content);
                        $data_return=Db::table('nav_article_comment_content')->insert($content_suz);
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'保存评论内容错误',
                            );
                            return $jg_return;
                        }
                        //获取保存内容的id
                        $data_return=Db::name('nav_article_comment_content')->getLastInsID();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'获取评论内容id错误',
                            );
                            return $jg_return;
                        }
                        //保存评论id数据
                        $time=date('Y-m-d h:i:s', time());
                        $article_id_suz['time']=$time;
                        $article_id_suz['content_id']=$data_return;
                        $data_return=Db::table('nav_article_comment_frequency')->insert($article_id_suz);
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'保存评论id错误',
                            );
                            return $jg_return;
                        }
                        $increase_2=true;
                    }else{
//                        删除评论
                        $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz)->find();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'查询评论id错误',
                            );
                            return $jg_return;
                        }
                        $content_id=$data_return['content_id'];
                        $content_id_suz=array('id'=>$content_id);
                        $data_return=Db::table('nav_article_comment_content')->where($content_id_suz)->delete();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'删除评论内容错误',
                            );
                            return $jg_return;
                        }
                        $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz)->delete();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'删除评论id错误',
                            );
                            return $jg_return;
                        }
                        $increase_2=false;
                    }
                    break;
                case 3:
                    //查询点赞数量
                    $data_return=Db::table('nav_article_fabulous_frequency')->where($article_id_suz)->find();
                    if(!$data_return){
                        //增加点赞记录
                        $time=date('Y-m-d h:i:s', time());
                        $article_id_suz['time']=$time;
                        $data_return=Db::table('nav_article_fabulous_frequency')->insert($article_id_suz);
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'增加点赞错误',
                            );
                            return $jg_return;
                        }
                        $increase_2=true;
                    }else{
                        //删除点赞记录
                        $data_return=Db::table('nav_article_fabulous_frequency')->where($article_id_suz)->delete();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'删除点赞错误',
                            );
                            return $jg_return;
                        }
                        $increase_2=false;
                    }
                    break;
                default:
                    $jg_return=array(
                        'code'=>'0',
                        'msg'=>'type类型错误',
                    );
                    return $jg_return;
                break;
            }
            $data_return=$this->article_modify_frequency_1($article_id,$increase_2,$type);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'自增自减错误',
                );
                return $jg_return;
            }
            $jg_return=array(
                'code'=>'1',
                'msg'=>'更改成功',
            );
            return $jg_return;
        }
    }
//    自增自减文章评论-查看-点赞次数
    public function article_modify_frequency_1($article_id,$increase,$type){
        $article_id_suz['id']=$article_id;
        if($increase){
//                自增
            switch($type){
                case 1:
                    $data_return=Db::table('nav_article_id')->where($article_id_suz)->setInc('see_frequency');
                    break;
                case 2:
                    $data_return=Db::table('nav_article_id')->where($article_id_suz)->setInc('comment_frequency');
                    break;
                case 3:
                    $data_return=Db::table('nav_article_id')->where($article_id_suz)->setInc('fabulous_frequency');
                    break;
                default:
                    return false;
                    break;
            }
        }else{
//                自减
            switch($type){
                case 1:
                    $data_return=Db::table('nav_article_id')->where($article_id_suz)->setDec('see_frequency');
                    break;
                case 2:
                    $data_return=Db::table('nav_article_id')->where($article_id_suz)->setDec('comment_frequency');
                    break;
                case 3:
                    $data_return=Db::table('nav_article_id')->where($article_id_suz)->setDec('fabulous_frequency');
                    break;
                default:
                    return false;
                    break;
            }
        }
        if(!$data_return){
            return false;
        }
        return $data_return;
    }
//    统计文章评论-点赞次数
    public function article_statistics_frequency(){
        $article_id=input('article_id');
        $type=input('type');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        if(empty($type)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'type为空',
            );
            return $jg_return;
        }
        $article_id_suz['article_id']=$article_id;
        switch($type){
            case 2:
                $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz)->select();
                break;
            case 3:
                $data_return=Db::table('nav_article_fabulous_frequency')->where($article_id_suz)->select();
                break;
            default:
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'type类型错误',
                );
                return $jg_return;
            break;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
            'length'=>count($data_return),
        );
        return $jg_return;

    }
}