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
Route::rule('api/InviteCode','api/User/InviteCode');
Route::rule('api/Getinvitecode','api/User/Getinvitecode');
Route::rule('api/GetinviterList','api/User/GetinviterList');
Route::rule('api/GetOtherUserInfo','api/user/GetOtherUserInfo');
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
Route::rule('api/SearchPost','api/bbs/SearchPost');
Route::rule('api/LikePost','api/bbs/LikePost');
Route::rule('api/CancelLikePost','api/bbs/CancelLikePost');
Route::rule('api/GetLikePost','api/bbs/GetLikePost');
Route::rule('api/IsLikePost','api/bbs/IsLikePost');

/**
 * 密钥开通
 */
Route::rule('api/KeyVip','api/key/KeyVip');
Route::rule('api/KeyMoney','api/key/KeyMoney');
Route::rule('api/KeyExp','api/key/KeyExp');
Route::rule('api/vipPermanent','api/key/vipPermanent');


/**
 * 外链
 */
Route::rule('notes/:id','index/index/querynotes/');
Route::rule('bbs/:id','index/index/querypost/');

//v2版本
/**
 * 用户功能接口
 */
Route::rule('apiv2/login','apiv2/User/Login');
Route::rule('apiv2/setLogin','apiv2/User/setLogin');
Route::rule('apiv2/Register','apiv2/user/Register');
Route::rule('apiv2/GetUserInfo','apiv2/user/GetUserInfo');
Route::rule('apiv2/UserSign','apiv2/user/UserSign');
Route::rule('apiv2/GetRegCode','apiv2/user/GetRegCode');
Route::rule('apiv2/GetPasswordCode','apiv2/user/GetPasswordCode');
Route::rule('apiv2/ResetPassword','apiv2/user/ResetPassword');
Route::rule('apiv2/UpdateUser','apiv2/user/UpdateUser');
Route::rule('apiv2/UploadHead','apiv2/user/UploadHead');
Route::rule('apiv2/UserList','apiv2/user/UserList');
Route::rule('apiv2/isVip','apiv2/User/isVip');
Route::rule('apiv2/UpdatePassword','apiv2/User/UpdatePassword');
Route::rule('apiv2/InviteCode','apiv2/User/InviteCode');
Route::rule('apiv2/Getinvitecode','apiv2/User/Getinvitecode');
Route::rule('apiv2/GetinviterList','apiv2/User/GetinviterList');
Route::rule('apiv2/LoginOut','apiv2/User/LoginOut');
Route::rule('apiv2/GetOtherUserInfo','apiv2/user/GetOtherUserInfo');
Route::rule('apiv2/GetOnlineUserNum','apiv2/user/GetOnlineUserNum');
Route::rule('apiv2/GetMessage','apiv2/bbs/GetMessage');
Route::rule('apiv2/getUnreadMessageCount','apiv2/bbs/getUnreadMessageCount');
/**
 * app
 */
Route::rule('apiv2/GetAppGg','apiv2/app/GetAppGg');
Route::rule('apiv2/GetAppInfo','apiv2/app/GetAppInfo');
Route::rule('apiv2/GetAppUpdate','apiv2/app/GetAppUpdate');
Route::rule('apiv2/AddAppView','apiv2/app/AddAppView');
/**
 * 卡密
 */
Route::rule('apiv2/UserKm','apiv2/km/UserKm');
/**
 * 笔记管理
 */
Route::rule('apiv2/GetNotesList','apiv2/notes/GetNotesList');
Route::rule('apiv2/GetNotes','apiv2/notes/GetNotes');
Route::rule('apiv2/UpdateNotes','apiv2/notes/UpdateNotes');
Route::rule('apiv2/deleteNotes','apiv2/notes/deleteNotes');
Route::rule('apiv2/addNotes','apiv2/notes/addNotes');

/**
 * 商城管理
 */
Route::rule('apiv2/GetShopList','apiv2/shop/GetShopList');
Route::rule('apiv2/GetShop','apiv2/shop/GetShop');
Route::rule('apiv2/BuyShop','apiv2/shop/BuyShop');
Route::rule('apiv2/UserShopOrder','apiv2/shop/UserShopOrder');
/**
 * 论坛功能
 */
Route::rule('apiv2/GetPlateList','apiv2/bbs/GetPlateList');
Route::rule('apiv2/GetAllPostList','apiv2/bbs/GetAllPostList');
Route::rule('apiv2/GetPostList','apiv2/bbs/GetPostList');
Route::rule('apiv2/GetPost','apiv2/bbs/GetPost');
Route::rule('apiv2/AddPost','apiv2/bbs/AddPost');
Route::rule('apiv2/UpdatePost','apiv2/bbs/UpdatePost');
Route::rule('apiv2/DeletePost','apiv2/bbs/DeletePost');
Route::rule('apiv2/GetUserPostList','apiv2/bbs/GetUserPostList');
Route::rule('apiv2/GetCommentList','apiv2/bbs/GetCommentList');
Route::rule('apiv2/GetUserCommentList','apiv2/bbs/GetUserCommentList');
Route::rule('apiv2/AddComment','apiv2/bbs/AddComment');
Route::rule('apiv2/GetUserCommentList','apiv2/bbs/GetUserCommentList');
Route::rule('apiv2/DeleteComment','apiv2/bbs/DeleteComment');
Route::rule('apiv2/SearchPost','apiv2/bbs/SearchPost');
Route::rule('apiv2/LikePost','apiv2/bbs/LikePost');
Route::rule('apiv2/CancelLikePost','apiv2/bbs/CancelLikePost');
Route::rule('apiv2/GetLikePost','apiv2/bbs/GetLikePost');
Route::rule('apiv2/IsLikePost','apiv2/bbs/IsLikePost');

/**
 * 密钥开通
 */
Route::rule('apiv2/KeyVip','apiv2/key/KeyVip');
Route::rule('apiv2/KeyMoney','apiv2/key/KeyMoney');
Route::rule('apiv2/KeyExp','apiv2/key/KeyExp');
Route::rule('apiv2/vipPermanent','apiv2/key/vipPermanent');

//pro
Route::rule('apipro/login','apipro/apipro/Login');
Route::rule('apipro/Register','apipro/apipro/Register');
Route::rule('apipro/GetRegCode','apipro/apipro/GetRegCode');
Route::rule('apipro/ResetPassword','apipro/apipro/ResetPassword');
Route::rule('apipro/GetPasswordCode','apipro/apipro/GetPasswordCode');
Route::rule('apipro/GetUserInfo','apipro/apipro/GetUserInfo');
Route::rule('apipro/getUserInfoByUsername','apipro/apipro/getUserInfoByUsername');
Route::rule('apipro/UserSign','apipro/apipro/UserSign');
Route::rule('apipro/updateUserInfo','apipro/apipro/updateUserInfo');
Route::rule('apipro/updateUser','apipro/apipro/updateUser');
Route::rule('apipro/UploadHead','apipro/apipro/UploadHead');
Route::rule('apipro/UserList','apipro/apipro/UserList');
Route::rule('apipro/isVip','apipro/apipro/isVip');
Route::rule('apipro/setInviteCode','apipro/apipro/setInviteCode');
Route::rule('apipro/Getinvitecode','apipro/apipro/Getinvitecode');
Route::rule('apipro/Logout','apipro/apipro/Logout');
Route::rule('apipro/Getuserlog','apipro/apipro/Getuserlog');
//卡密管理
Route::rule('apipro/UseKm','apipro/apipro/UseKm');
//论坛系统
Route::rule('apipro/GetPlateList','apipro/apipro/GetPlateList');
Route::rule('apipro/GetPostList','apipro/apipro/GetPostList');
Route::rule('apipro/GetAllPostList','apipro/apipro/GetAllPostList');
Route::rule('apipro/GetPost','apipro/apipro/GetPost');
Route::rule('apipro/AddPost','apipro/apipro/AddPost');
Route::rule('apipro/UpdatePost','apipro/apipro/UpdatePost');
Route::rule('apipro/DeletePost','apipro/apipro/DeletePost');
Route::rule('apipro/GetUserPostList','apipro/apipro/GetUserPostList');
Route::rule('apipro/GetCommentList','apipro/apipro/GetCommentList');
Route::rule('apipro/AddComment','apipro/apipro/AddComment');
Route::rule('apipro/GetUserCommentList','apipro/apipro/GetUserCommentList');
Route::rule('apipro/DeleteComment','apipro/apipro/DeleteComment');
Route::rule('apipro/SearchPost','apipro/apipro/SearchPost');
Route::rule('apipro/LikePost','apipro/apipro/LikePost');
Route::rule('apipro/CancelLikePost','apipro/apipro/CancelLikePost');
Route::rule('apipro/GetLikePostList','apipro/apipro/GetLikePostList');
Route::rule('apipro/IsLikePost','apipro/apipro/IsLikePost');
//公告系统
Route::rule('apipro/GetAppNotice','apipro/apipro/GetAppNotice');
Route::rule('apipro/GetAppUpdate','apipro/apipro/GetAppUpdate');
Route::rule('apipro/GetAppInfo','apipro/apipro/GetAppInfo');
Route::rule('apipro/AddAppView','apipro/apipro/AddAppView');
//商城系统
Route::rule('apipro/GetShopList','apipro/apipro/GetShopList');
Route::rule('apipro/GetShopInfo','apipro/apipro/GetShopInfo');
Route::rule('apipro/BuyShop','apipro/apipro/BuyShop');
Route::rule('apipro/UserShopOrder','apipro/apipro/UserShopOrder');
//笔记系统
Route::rule('apipro/addNotes','apipro/apipro/addNotes');
Route::rule('apipro/deleteNotes','apipro/apipro/deleteNotes');
Route::rule('apipro/updateNotes','apipro/apipro/updateNotes');
Route::rule('apipro/getNotesList','apipro/apipro/getNotesList');
Route::rule('apipro/getNotesInfo','apipro/apipro/getNotesInfo');
