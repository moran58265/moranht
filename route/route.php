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


use think\facade\Route;

/**
 * 用户功能接口
 */
Route::rule('api/login','api/User/Login');
Route::rule('api/Register','api/user/Register');
Route::rule('api/GetUserinfo','api/user/GetUserinfo');
Route::rule('api/UserSign','api/user/UserSign');
Route::rule('api/GetRegCode','api/user/GetRegCode');
Route::rule('api/GetPasswordCode','api/user/GetPasswordCode');
Route::rule('api/ResetPassword','api/user/ResetPassword');
Route::rule('api/UpdateUser','api/user/UpdateUser');
Route::rule('api/UploadHead','api/user/UploadHead');
Route::rule('api/UserList','api/user/UserList');
Route::rule('api/isVip','api/User/isVip');
Route::rule('api/UpdatePassword','api/User/UpdatePassword');

/**
 * app
 */
Route::rule('api/GetAppGg','api/app/GetAppGg');
Route::rule('api/GetAppInfo','api/app/GetAppInfo');
Route::rule('api/GetAppUpdate','api/app/GetAppUpdate');
Route::rule('api/AddAppView','api/app/AddAppView');
/**
 * 卡密
 */
Route::rule('api/UserKm','api/km/UserKm');

/**
 * 笔记管理
 */
Route::rule('api/GetNotesList','api/notes/GetNotesList');
Route::rule('api/GetNotes','api/notes/GetNotes');
Route::rule('api/UpdateNotes','api/notes/UpdateNotes');
Route::rule('api/deleteNotes','api/notes/deleteNotes');
Route::rule('api/addNotes','api/notes/addNotes');

/**
 * 商城管理
 */
Route::rule('api/GetShopList','api/shop/GetShopList');
Route::rule('api/GetShop','api/shop/GetShop');
Route::rule('api/BuyShop','api/shop/BuyShop');
Route::rule('api/UserShopOrder','api/shop/UserShopOrder');

/**
 * 论坛功能
 */
Route::rule('api/GetPlateList','api/bbs/GetPlateList');
Route::rule('api/GetAllPostList','api/bbs/GetAllPostList');
Route::rule('api/GetPostList','api/bbs/GetPostList');
Route::rule('api/GetPost','api/bbs/GetPost');
Route::rule('api/AddPost','api/bbs/AddPost');
Route::rule('api/UpdatePost','api/bbs/UpdatePost');
Route::rule('api/DeletePost','api/bbs/DeletePost');
Route::rule('api/GetUserPostList','api/bbs/GetUserPostList');
Route::rule('api/GetCommentList','api/bbs/GetCommentList');
Route::rule('api/GetUserCommentList','api/bbs/GetUserCommentList');
Route::rule('api/AddComment','api/bbs/AddComment');
Route::rule('api/GetUserCommentList','api/bbs/GetUserCommentList');
Route::rule('api/DeleteComment','api/bbs/DeleteComment');

/**
 * 密钥开通
 */
Route::rule('api/KeyVip','api/key/KeyVip');
Route::rule('api/KeyMoney','api/key/KeyMoney');
Route::rule('api/KeyExp','api/key/KeyExp');
Route::rule('api/vipPermanent','api/key/vipPermanent');