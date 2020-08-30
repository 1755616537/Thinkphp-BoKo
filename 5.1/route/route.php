<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//获取验证码图片
Route::any('gainVerification', 'public1jk/Land/huoQu_verify')->allowCrossDomain();
//登陆
Route::post('login', 'public1jk/Land/shiFou_login')->allowCrossDomain();
//是否已经登陆
Route::post('if_login', 'public1jk/Land/if_shiFou_login')->allowCrossDomain();
//退出
Route::post('quit', 'public1jk/Land/quit')->allowCrossDomain();
//注册
Route::post('register', 'public1jk/Land/register')->allowCrossDomain();
//找回密码
Route::post('retrieve', 'public1jk/Land/retrieve')->allowCrossDomain();
//更改密码
Route::post('changepassword', 'public1jk/Land/changepassword')->allowCrossDomain();
//登陆网页
Route::any('xlogin', 'public1jk/Land/xlogin')->allowCrossDomain();
//搜索文章网页
Route::any('article_open', 'public1jk/Article/article_open_article_id_2')->allowCrossDomain();
//保存文章
Route::any('article_preservation', 'public1jk/Article/article_preservation')->allowCrossDomain();
//文章id获取文章信息
Route::any('article_obtain_article_id', 'public1jk/Article/article_obtain_article_id')->allowCrossDomain();
//用户id获取文章信息
Route::any('article_obtain_user_id', 'public1jk/Article/article_obtain_user_id')->allowCrossDomain();
//文章id获取文章内容
Route::any('article_obtain_article_id_content', 'public1jk/Article/article_obtain_article_id_content')->allowCrossDomain();
//删除文章
Route::any('article_delete_article', 'public1jk/Article/article_delete_article')->allowCrossDomain();
//更改文章标题-内容
Route::any('article_modify_content', 'public1jk/Article/article_modify_content')->allowCrossDomain();
//增加文章评论-增加删除点赞
Route::any('article_modify_frequency', 'public1jk/Article/article_modify_frequency')->allowCrossDomain();
//获取文章评论-点赞次数
Route::any('article_statistics_frequency', 'public1jk/Article/article_statistics_frequency')->allowCrossDomain();
//文章id获取前几的评论-点赞
Route::any('article_obtain_sort', 'public1jk/Article/article_obtain_sort')->allowCrossDomain();
//查询文章-分页式
Route::any('article_branch_obtain_article_id', 'public1jk/Article/article_branch_obtain_article_id')->allowCrossDomain();
//打开文章详情页面-分页式
Route::any('article_open_article_page', 'public1jk/Article/article_open_article_page')->allowCrossDomain();
//评论id获取附加评论-分页式
Route::any('article_open_comment_additional', 'public1jk/Article/article_open_comment_additional')->allowCrossDomain();
//文章id获取一级评论-分页式
Route::any('article_open_comment', 'public1jk/Article/article_open_comment')->allowCrossDomain();
//文章id获取自己是否已经点赞文章
Route::any('article_open_fabulous', 'public1jk/Article/article_open_fabulous')->allowCrossDomain();
//搜索文章内容-分页式
Route::any('article_open_article_id', 'public1jk/Article/article_open_article_id')->allowCrossDomain();
//搜索文章内容2-分页式
Route::any('article_open_article_id_2', 'public1jk/Article/article_open_article_id_2')->allowCrossDomain();

return [

];
