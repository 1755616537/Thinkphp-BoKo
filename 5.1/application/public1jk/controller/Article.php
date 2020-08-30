<?php


namespace app\public1jk\controller;


use think\Controller;
use think\Db;
use think\facade\Cookie;
use think\facade\Session;

class Article extends Controller{
    public function a(){
        $list = Db::name('nav_article_id')->where('user_id',14)->paginate(10);
        $suz=array();
        foreach ($list as $e) {
            $suz2=array('id'=>$e['content_id'],);
            $a = Db::name('nav_article_content')->where($suz2)->find();
            array_push($suz,$a);
        }
        $this->assign('list', $list);
        $this->assign('list_content', $suz);
        return $this->fetch();
    }
//    保存文章
    public function article_preservation(){
//        标题
        $title=input('title');
//        内容
        $content=input('content');
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
//        用户id
        $user_id=$data_return['id'];
        if(empty($title)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'title为空',
            );
            return json($jg_return);
        }
        if(empty($content)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'content为空',
            );
            return json($jg_return);
        }
        if(empty($user_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'user_id为空',
            );
            return json($jg_return);
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
//        把发表时间记录到排序表中
        $data_return=$this->article_sort_publish_time($article_id,$time,1);
        if(!$data_return){
            return false;
        }
        return $article_id;
    }
//    文章id获取文章信息
    public  function article_obtain_article_id(){
        $article_id=input('article_id');
//       获取文章id
        $data_return=$this->fz_article_open_article_id($article_id);
        if($data_return['code']==0){
            return $data_return;
        }
        $data_return=$data_return['msg'];
//        获取文章内容
        $data_return_2=$this->fz_article_open_article_content($article_id);
        if($data_return_2['code']==0){
            return $data_return_2;
        }
        $data_return_2=$data_return_2['msg'];
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
        return json($jg_return);
    }
//    用户id获取文章信息
    public function article_obtain_user_id(){
        $user_id=input('user_id');
        if(empty($user_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'user_id为空',
            );
            return json($jg_return);
        }
        $user_id_suz['user_id']=$user_id;
        $data_return=Db::table('nav_article_id')->where($user_id_suz)->select();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章id',
            );
            return json($jg_return);
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章id失败',

            );
            return json($jg_return);
        }

        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
            'length'=>count($data_return),
        );
        return json($jg_return);
    }
//    文章id获取文章内容
    public function article_obtain_article_id_content(){
        $content_id=input('content_id');
        if(empty($content_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'content_id为空',
            );
            return json($jg_return);
        }
        $content_id_suz['id']=$content_id;
        $data_return=Db::table('nav_article_content')->where($content_id_suz)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章内容',
            );
            return json($jg_return);
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章内容失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return json($jg_return);
    }
//    删除文章
    public function article_delete_article(){
        $article_id=input('article_id');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return json($jg_return);
        }
//        登陆成功验证token
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
        $user_id=$data_return['id'];
//  --------------------------------------
        $article_id_suz['id']=$article_id;
        //获取文章内容id
        $data_return=Db::table('nav_article_id')->where($article_id_suz)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章id',
            );
            return json($jg_return);
        }
//        验证是否发表文章用户
        if($data_return['user_id']!=$user_id){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'你不是此发表文章的用户',
            );
            return json($jg_return);
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取文章id失败',
            );
            return json($jg_return);
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
        $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz_2)->delete();
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
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'删除文章成功',
        );
        return json($jg_return);
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
        //        登陆成功验证token
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
        $user_id=$data_return['id'];
//  --------------------------------------
//        查询文章内容id
        $article_id_suz['id']=$article_id;
        $data_return=Db::table('nav_article_id')->where($article_id_suz)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章id',
            );
            return json($jg_return);
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询文章id错误',
            );
            return json($jg_return);
        }
        //        验证是否发表文章用户
        if($data_return['user_id']!=$user_id){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'你不是此发表文章的用户',
            );
            return json($jg_return);
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
                return json($jg_return);
            }
        }
        $content_id_suz['id']=$content_id;
        $data_return=Db::table('nav_article_content')->where($content_id_suz)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章内容',
            );
            return json($jg_return);
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询文章内容id错误',
            );
            return json($jg_return);
        }
        if($data_return['content']!=$content){
            $content_suz['content']=$content;
            $data_return=Db::table('nav_article_content')->where($content_id_suz)->update($content_suz);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更改文章内容错误',
                );
                return json($jg_return);
            }
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'更改文章成功',
        );
        return json($jg_return);
    }
//    修改文章评论-查看-点赞次数
    public function article_modify_frequency(){
        $article_id=input('article_id');
        $type=input('type');
        $increase=input('increase');
        $content=input('content');
        $additional_comment_id=input('additional_comment_id');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return json($jg_return);
        }
        if(empty($type)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'type为空',
            );
            return json($jg_return);
        }
        //        登陆成功验证token
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return json($jg_return);
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
        $user_id=$data_return['id'];
//  --------------------------------------
        if(empty($user_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'更改错误',
            );
            return json($jg_return);


            if(empty($increase)){
                if($increase!='0'){
                    $jg_return=array(
                        'code'=>'0',
                        'msg'=>'increase为空',
                    );
                    return json($jg_return);
                }
            }
            $data_return=$this->article_modify_frequency_1($article_id,$increase,$type);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更改错误',
                );
                return json($jg_return);
            }
            $jg_return=array(
                'code'=>'1',
                'msg'=>'更改成功',
            );
            return json($jg_return);
        }else{
            $article_id_suz['user_id']=$user_id;
            $article_id_suz['article_id']=$article_id;
            switch($type){
                case 2:
                    $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz)->find();
                    //默认不自动删除评论
                    if(!$data_return || 1==1){
                        //增加评论
                        if(empty($content)){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'content为空',
                            );
                            return json($jg_return);
                        }
                        //保存评论内容后返回id在保存id
                        $content_suz=array('content'=>$content);
                        $data_return=Db::table('nav_article_comment_content')->insert($content_suz);
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'保存评论内容错误',
                            );
                            return json($jg_return);
                        }
                        //获取保存内容的id
                        $data_return=Db::name('nav_article_comment_content')->getLastInsID();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'获取评论内容id错误',
                            );
                            return json($jg_return);
                        }
                        //保存评论id数据
                        $time=date('Y-m-d h:i:s', time());
                        $article_id_suz['time']=$time;
                        $article_id_suz['content_id']=$data_return;
                        if(!empty($additional_comment_id)){
                            $article_id_suz['additional_comment_id']=$additional_comment_id;
                        }
                        $data_return=Db::table('nav_article_comment_frequency')->insert($article_id_suz);
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'保存评论id错误',
                            );
                            return json($jg_return);
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
                            return json($jg_return);
                        }
                        $content_id=$data_return['content_id'];
                        $content_id_suz=array('id'=>$content_id);
                        $data_return=Db::table('nav_article_comment_content')->where($content_id_suz)->delete();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'删除评论内容错误',
                            );
                            return json($jg_return);
                        }
                        $data_return=Db::table('nav_article_comment_frequency')->where($article_id_suz)->delete();
                        if(!$data_return){
                            $jg_return=array(
                                'code'=>'0',
                                'msg'=>'删除评论id错误',
                            );
                            return json($jg_return);
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
                            return json($jg_return);
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
                            return json($jg_return);
                        }
                        $increase_2=false;
                    }
                    break;
                default:
                    $jg_return=array(
                        'code'=>'0',
                        'msg'=>'type类型错误',
                    );
                    return json($jg_return);
                break;
            }
            $data_return=$this->article_modify_frequency_1($article_id,$increase_2,$type);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'自增自减错误',
                );
                return json($jg_return);
            }
            //查询点赞数量
            $article_id_suz_2['id']=$article_id;
            $data_return=Db::table('nav_article_id')->where($article_id_suz_2)->find();
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'查询点赞数量失败',
                );
                return json($jg_return);
            }
//            更新评论数量-点赞数量 到排序表中
            $data_return_2=$this->article_sort_publish_time($article_id,$data_return['comment_frequency'],2);
            if(!$data_return_2){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更新评论排序失败',
                );
                return json($jg_return);
            }
            $data_return=$this->article_sort_publish_time($article_id,$data_return['fabulous_frequency'],3);
            if(!$data_return){
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'更新点赞排序失败',
                );
                return json($jg_return);
            }
            $jg_return=array(
                'code'=>'1',
                'msg'=>'更改成功',
            );
            return json($jg_return);
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
//    获取文章评论-点赞次数
    public function article_statistics_frequency(){
        $article_id=input('article_id');
        $type=input('type');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return json($jg_return);
        }
        if(empty($type)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'type为空',
            );
            return json($jg_return);
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
                return json($jg_return);
            break;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
            'length'=>count($data_return),
        );
        return json($jg_return);

    }
//    发表时间记录-评论数量--点赞数量 记录到排序表
    public function article_sort_publish_time($article_id,$data,$type){
        $article_id_suz['article_id']=$article_id;
//        查询是否已经存在
        switch($type){
            case 1:
                $data_return=Db::table('nav_article_sort_publish_time')->where($article_id_suz)->find();
                if($data_return['publish_time']==$data){
                    return true;
                }
                $data_suz['publish_time']=$data;
                break;
            case 2:
                $data_return=Db::table('nav_article_sort_comment_frequency')->where($article_id_suz)->find();
                if($data_return['comment_frequency']==$data){
                    return true;
                }
                $data_suz['comment_frequency']=$data;
                break;
            case 3:
                $data_return=Db::table('nav_article_sort_fabulous_frequency')->where($article_id_suz)->find();
                if($data_return['fabulous_frequency']==$data){
                    return true;
                }
                $data_suz['fabulous_frequency']=$data;
                break;
            default:
                return false;
                break;
        }
        if(!$data_return){
//            添加记录
            $data_suz['article_id']=$article_id;
            switch($type){
                case 1:
                    $data_return=Db::table('nav_article_sort_publish_time')->insert($data_suz);
                    break;
                case 2:
                    $data_return=Db::table('nav_article_sort_comment_frequency')->insert($data_suz);
                    break;
                case 3:
                    $data_return=Db::table('nav_article_sort_fabulous_frequency')->insert($data_suz);
                    break;
                default:
                    return false;
                    break;
            }
            if(!$data_return){
                return false;
            }
        }else{
//            更改记录
            switch($type){
                case 1:
                    $data_return=Db::table('nav_article_sort_publish_time')->where($article_id_suz)->update($data_suz);
                    break;
                case 2:
                    $data_return=Db::table('nav_article_sort_comment_frequency')->where($article_id_suz)->update($data_suz);
                    break;
                case 3:
                    $data_return=Db::table('nav_article_sort_fabulous_frequency')->where($article_id_suz)->update($data_suz);
                    break;
                default:
                    return false;
                    break;
            }
            if(!$data_return){
                return false;
            }
        }
        return true;
    }
//    获取前几的发表时间-评论-点赞 文章id
    public function article_obtain_sort(){
        $article_id=input('article_id');
        $small_number=input('small_number');
        $large_number=input('large_number');
        $type=input('type');
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return json($jg_return);
        }
        if(empty($small_number)){
            $small_number=0;
        }
        if(empty($large_number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'large_number为空',
            );
            return json($jg_return);
        }
        if(empty($type)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'type为空',
            );
            return json($jg_return);
        }
        if($large_number>20){
            $large_number=20;
        }
        $article_id_suz['article_id']=$article_id;
        switch($type){
            case 1:
                $data_return=Db::table('nav_article_sort_publish_time')->where($article_id_suz)->limit($small_number,$large_number)->select();
                break;
            case 2:
                $data_return=Db::table('nav_article_sort_comment_frequency')->where($article_id_suz)->limit($small_number,$large_number)->select();
                break;
            case 3:
                $data_return=Db::table('nav_article_sort_fabulous_frequency')->where($article_id_suz)->limit($small_number,$large_number)->select();
                break;
            default:
                $jg_return=array(
                    'code'=>'0',
                    'msg'=>'type类型错误',
                );
                return json($jg_return);
            break;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return json($jg_return);
    }
//    分页式查询文章
    public function article_branch_obtain_article_id(){
        $page=input('page');
        $number=input('number');
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return json($jg_return);
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return json($jg_return);
        }
        if($number>20){
            $number=20;
        }
        $data_return=Db::table('nav_article_id')->page($page,$number)->select();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'查询失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
            'length'=>count($data_return),
        );
        return json($jg_return);
    }
//    打开文章详情页面
    public function article_open_article_page(){
        $article_id=input('article_id');
        $type=input('type');
//        获取文章id
        $data_return=$this->fz_article_open_article_id($article_id);
        if($data_return['code']==0){
            return json($data_return);
        }
//        获取文章内容
        $data_return=$data_return['msg'];
        $data_return_2=$this->fz_article_open_article_content($data_return['content_id']);
        if($data_return_2['code']==0){
            return json($data_return_2);
        }
        $data_return_2=$data_return_2['msg'];
//        获取文章是否已经点赞
        $data_return_3=$this->fz_article_open_fabulous($article_id);

//        记录查看次数
        $this->article_modify_frequency_1($article_id,true,1);

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
                'fabulous_on'=>$data_return_3,
            )
        );
        if($type==1){
            return json($jg_return);
        }
//        赋值到网页模板
        $this->assign(['article_id_jg_return'  =>$jg_return,]);
        return $this->fetch();
    }
    //    封装文章id获取评论
    public function fz_article_open_comment($article_id){
//        获取文章id
        $data_return=$this->fz_article_open_article_id($article_id);
        if($data_return['code']==0){
            return $data_return;
        }
//        获取评论id
        $data_return=$this->fz_article_open_comment_id($article_id,1,20);
        print_r($data_return);
        if($data_return['code']==0){
            return $data_return;
        }
        $data_return=$data_return['msg'];
        $data_return_2=array();
        foreach($data_return as $value){
            $content_id=$value['content_id'];
//            获取评论内容
            $data_return=$this->fz_article_open_comment_content($content_id);
            if($data_return['code']==1){
                array_push($data_return_2,$data_return['msg']);
            }
        }
        if(!$data_return_2){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论内容失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return_2,
        );
        return $jg_return;
    }
    //    评论id获取附加评论
    public function article_open_comment_additional(){
        $comment_id=input('comment_id');
        $page=input('page');
        $number=input('number');
//        获取评论id
        $data_return=$this->fz_article_open_comment_id_additional($comment_id,$page,$number);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
        $data_return_2=array();
        foreach($data_return as $value){
//            获取评论内容
            $data_return=$this->fz_article_open_comment_content($value['content_id']);
            if($data_return['code']==1){
                $value['content']=$data_return['msg'];
                array_push($data_return_2,$value);
            }
        }
        if(!$data_return_2){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论内容失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return_2,
        );
        return json($jg_return);
    }
//    文章id获取一级评论
    public function article_open_comment(){
        $article_id=input('article_id');
        $page=input('page');
        $number=input('number');
//        获取文章id
        $data_return=$this->fz_article_open_article_id($article_id);
        if($data_return['code']==0){
            return json($data_return);
        }
//        获取评论id
        $data_return=$this->fz_article_open_comment_id($article_id,$page,$number);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
        $data_return_2=array();
        foreach($data_return as $value){
//            获取评论内容
            $data_return=$this->fz_article_open_comment_content($value['content_id']);
            if($data_return['code']==1){
                $value['content']=$data_return['msg'];
                array_push($data_return_2,$value);
            }
        }
        if(!$data_return_2){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论内容失败',
            );
            return json($jg_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return_2,
        );
        return json($jg_return);
    }
    //    封装文章id获取文章是否已经点赞
    public function fz_article_open_fabulous($article_id){
//        获取文章id
        $data_return=$this->fz_article_open_article_id($article_id);
        if($data_return['code']==0){
            return $data_return;
        }
        $article_id=$data_return['msg'];
//        获取user_id
        $data_return=$this->fz_article_open_token_user_id();
        if($data_return['code']==0){
            return $data_return;
        }
        $user_id=$data_return['msg'];
//        查询点赞记录表
        $article_id_suz['article_id']=$article_id['id'];
        $article_id_suz['user_id']=$user_id;
        $data_return=$this->fz_article_open_fabulous_id($article_id['id'],$user_id);
        if($data_return['code']==0){
            return $data_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>'已点赞',
        );
        return $jg_return;
    }
//    文章id获取文章是否已经点赞
    public function article_open_fabulous(){
        $article_id=input('article_id');
        //        获取user_id
        $data_return=$this->fz_article_open_token_user_id();
        if($data_return['code']==0){
            return json($data_return);
        }
//        获取文章id
        $data_return=$this->fz_article_open_article_id($article_id);
        if($data_return['code']==0){
            return json($data_return);
        }
        $user_id=$data_return['msg'];
//        查询点赞记录表
        $article_id_suz['article_id']=$article_id['id'];
        $article_id_suz['user_id']=$user_id;
        $data_return=$this->fz_article_open_fabulous_id($article_id['id'],$user_id);
        if($data_return['code']==0){
            return json($data_return);
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return['msg'],
        );
        return json($jg_return);
    }
    //    搜索文章内容
    public function article_open_article_id(){
        $content=input('content');
        $page=input('page');
        $number=input('number');
//        搜索文章内容
        $data_return=$this->fz_article_open_article_content_id($content,$page,$number);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
        $data_return_2=array();
        foreach($data_return as $value){
//            文章内容id获取文章id
            $data_return=$this->fz_article_open_content_article_id($value['id']);
            if($data_return['code']==1){
                $data_return['msg']['content']=$value['content'];
                array_push($data_return_2,$data_return['msg']);
            }
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return_2,
        );
        return json($jg_return);
    }
    //    搜索文章内容到模板
    public function article_open_article_id_2(){
        $content=input('article_content');
        $type=input('type');
        if($content==null){
            $content=Cookie::get('article_content');
        }else{
            Cookie::delete('article_content');
        }
//        搜索文章内容
        $data_return=$this->fz_article_open_article_content_id_2($content);
        if($data_return['code']==0){
            return json($data_return);
        }
        if(count($data_return['msg'])==0){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在数据',
            );
            return json($jg_return);
        }
        $data_return=$data_return['msg'];
        $data_return_2=array();
        foreach($data_return as $value){
//            文章内容id获取文章id
            $data_return_3=$this->fz_article_open_content_article_id($value['id']);
            if($data_return_3['code']==1){
                $data_return_3['msg']['content']=$value['content'];
                array_push($data_return_2,$data_return_3['msg']);
            }
        }
        if($type==1){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取成功',
                'list'=>$data_return_2,
                'total'=>ceil($data_return->total() / 10),
                'total_len'=>$data_return->total(),
            );
            return json($jg_return);
        }
        Cookie::set('article_content',$content,3600);
        $this->assign('list_content', $data_return);
        $this->assign('list', $data_return_2);
        return $this->fetch();
    }
    //    封装验证用户账号密码
    public function fz_open_user_information($username,$password){
        if(empty($username)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'username为空',
            );
            return $jg_return;
        }
        if(empty($password)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'password为空',
            );
            return $jg_return;
        }
        $suz['username']=$username;
        $suz['password']=$password;
        $data_return=Db::table('nav_user_information')->where($suz)->failException(false)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在用户',
            );
            return $jg_return;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'用户不存在或密码错误',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
    //    封装获取发表文章时间排行榜表
    public function fz_article_open_publish_time_sort($page,$number){
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return $jg_return;
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return $jg_return;
        }
        $data_return=Db::table('nav_article_sort_publish_time')->failException(false)->page($page,$number)->select();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取发表文章时间排行榜失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
    //    封装获取点赞排行榜表
    public function fz_article_open_fabulous_sort($page,$number){
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return $jg_return;
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return $jg_return;
        }
        $data_return=Db::table('nav_article_sort_fabulous_frequency')->failException(false)->page($page,$number)->select();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取点赞排行榜失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
    //    封装获取评论排行榜表
    public function fz_article_open_comment_sort($page,$number){
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return $jg_return;
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return $jg_return;
        }
        $data_return=Db::table('nav_article_sort_comment_frequency')->failException(false)->page($page,$number)->select();
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论排行榜失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
    //    封装获取评论内容表
    public function fz_article_open_comment_content($content_id){
        if(empty($content_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'content_id为空',
            );
            return $jg_return;
        }
        $suz['id']=$content_id;
        $data_return=Db::table('nav_article_comment_content')->where($suz)->failException(false)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在评论内容',
            );
            return $jg_return;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论内容失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
    //    封装获取评论id表-附加评论
    public function fz_article_open_comment_id_additional($comment_id,$page,$number){
        if(empty($comment_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'comment_id为空',
            );
            return $jg_return;
        }
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return $jg_return;
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return $jg_return;
        }
        $suz['additional_comment_id']=$comment_id;
        $data_return=Db::table('nav_article_comment_frequency')->where($suz)->failException(false)->page($page,$number)->select();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在评论id',
            );
            return $jg_return;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论id失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
    //    封装获取一级评论id表
    public function fz_article_open_comment_id($article_id,$page,$number){
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return $jg_return;
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return $jg_return;
        }
        if($number>20){
            $number=20;
        }
        $suz['article_id']=$article_id;
        $suz['additional_comment_id']=null;
        $data_return=Db::table('nav_article_comment_frequency')->where($suz)->failException(false)->page($page,$number)->select();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在评论id',
            );
            return $jg_return;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取评论id失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
//    封装获取点赞id表
    public function fz_article_open_fabulous_id($article_id,$user_id){
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
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
        $suz['article_id']=$article_id;
        $suz['user_id']=$user_id;
        $data_return=Db::table('nav_article_fabulous_frequency')->where($suz)->failException(false)->select();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在点赞id',
            );
            return $jg_return;
        }
        if(!$data_return){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'获取点赞id失败',
            );
            return $jg_return;
        }
        $jg_return=array(
            'code'=>'1',
            'msg'=>$data_return,
        );
        return $jg_return;
    }
//        封装Session获取user_id信息
    public function fz_article_open_token_user_id(){
        //        登陆成功验证token
        $token=Cookie::get('token');
        if(empty($token)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'未登陆',
            );
            return $jg_return;
        }
        $Token=new Token();
        $data_return=$Token->verification($token);
        if($data_return['code']==0){
            return json($data_return);
        }
        $data_return=$data_return['msg'];
//  --------------------------------------
        $user_id=$data_return['id'];
        $jg_return=array(
            'code'=>'1',
            'msg'=>$user_id,
        );
        return $jg_return;
    }
    //    封装文章内容获取文章id表
    public function fz_article_open_article_content_id($article_content,$page,$number){
        if(empty($article_content)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_content为空',
            );
            return $jg_return;
        }
        if(empty($page)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'page为空',
            );
            return $jg_return;
        }
        if(empty($number)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'number为空',
            );
            return $jg_return;
        }
        if($number>20){
            $number=20;
        }
        $suz['content']=$article_content;
        $data_return=Db::table('nav_article_content')->whereOr('content','LIKE','%'.$article_content.'%')->failException(false)->page($page,$number)->select();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章内容',
            );
            return $jg_return;
        }
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
    //    封装文章内容获取文章id表2
    public function fz_article_open_article_content_id_2($article_content){
        if(empty($article_content)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_content为空',
            );
            return $jg_return;
        }
        $suz['content']=$article_content;
        $data_return=Db::table('nav_article_content')->whereOr('content','LIKE','%'.$article_content.'%')->failException(false)->paginate(10);
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章内容',
            );
            return $jg_return;
        }
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
    //    封装文章内容id获取文章id
    public function fz_article_open_content_article_id($article_content_id){
        if(empty($article_content_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_content_id为空',
            );
            return $jg_return;
        }
        $suz['content_id']=$article_content_id;
        $data_return=Db::table('nav_article_id')->where($suz)->failException(false)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章id',
            );
            return $jg_return;
        }
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
        );
        return $jg_return;
    }
    //    封装文章id获取文章内容表
    public function fz_article_open_article_content($article_content_id){
        if(empty($article_content_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_content_id为空',
            );
            return $jg_return;
        }
        $suz['id']=$article_content_id;
        $data_return=Db::table('nav_article_content')->where($suz)->failException(false)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章内容id',
            );
            return $jg_return;
        }
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
//    封装获取文章id信息
    public function fz_article_open_article_id($article_id){
        if(empty($article_id)){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'article_id为空',
            );
            return $jg_return;
        }
        $suz['id']=$article_id;
        $data_return=Db::table('nav_article_id')->where($suz)->failException(false)->find();
        if($data_return==null){
            $jg_return=array(
                'code'=>'0',
                'msg'=>'不存在文章id',
            );
            return $jg_return;
        }
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
        );
        return $jg_return;
    }

}