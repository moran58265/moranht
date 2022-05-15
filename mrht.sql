/*
 Navicat Premium Data Transfer

 Source Server         : root
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : mrht10

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 15/05/2022 22:06:34
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for mr_admin
-- ----------------------------
DROP TABLE IF EXISTS `mr_admin`;
CREATE TABLE `mr_admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `userqq` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `userrole` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '盐',
  `admintoken` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `adminrole` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_admin
-- ----------------------------
INSERT INTO `mr_admin` VALUES (1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '2659917175', '默然', '1', 'OD552p', '', NULL);

-- ----------------------------
-- Table structure for mr_app
-- ----------------------------
DROP TABLE IF EXISTS `mr_app`;
CREATE TABLE `mr_app`  (
  `appid` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `appicon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图标',
  `introduction` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '简介',
  `author` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系方式',
  `group` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '官方群组',
  `userid` int(11) NULL DEFAULT NULL COMMENT '所属用户',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '暂无' COMMENT '公告标题',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '暂无' COMMENT '公告内容',
  `version` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1.0' COMMENT '版本号',
  `updatecontent` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '暂无' COMMENT '更新内容',
  `download` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '暂无' COMMENT '下载地址',
  `creattime` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `zcmoney` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '注册money',
  `zcexp` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '注册经验',
  `zcvip` varchar(255) CHARACTER SET sjis COLLATE sjis_japanese_ci NULL DEFAULT '0' COMMENT '注册会员',
  `signmoney` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '签到money',
  `signexp` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '签到经验',
  `signvip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '签到会员',
  `postmoney` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '发帖money',
  `postexp` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '发帖经验',
  `commentmoney` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '评论获得money',
  `commentexp` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '评论获得经验',
  `invitemoney` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '邀请码获得money',
  `inviteexp` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '邀请码获得exp',
  `invitevip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '邀请码获得vip',
  `finvitemoney` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '被邀请码获得money',
  `finviteexp` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '被邀请码获得exp',
  `finvitevip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '邀请码获得vip',
  `app_site_status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'true' COMMENT 'true/false',
  `is_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'false' COMMENT '注册是否要验证码true/false',
  `hierarchy` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '[0 => \'名称1\',100=>\'名称2\',200=>\'名称3\',300=>\'名称4\',400=>\'名称5\']' COMMENT '经验等级划分',
  `view` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '访问量',
  PRIMARY KEY (`appid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 10000 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = 'app列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_app
-- ----------------------------

-- ----------------------------
-- Table structure for mr_comment
-- ----------------------------
DROP TABLE IF EXISTS `mr_comment`;
CREATE TABLE `mr_comment`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '评论内容',
  `appid` int(11) NULL DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `plateid` int(11) NULL DEFAULT NULL COMMENT '板块id',
  `postid` int(11) NULL DEFAULT NULL COMMENT '帖子id',
  `creattime` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_comment
-- ----------------------------

-- ----------------------------
-- Table structure for mr_email
-- ----------------------------
DROP TABLE IF EXISTS `mr_email`;
CREATE TABLE `mr_email`  (
  `id` int(11) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `port` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `mail_way` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `email_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `email_content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_email
-- ----------------------------
INSERT INTO `mr_email` VALUES (1, '210793979@qq.com', '465', 'smtp.qq.com', 'rnbhnhnfttpdbhcg', '默然iapp后台管理系统', '你看到这封邮件，说明你的邮箱配置已经正常了');

-- ----------------------------
-- Table structure for mr_emailcode
-- ----------------------------
DROP TABLE IF EXISTS `mr_emailcode`;
CREATE TABLE `mr_emailcode`  (
  `id` int(11) NOT NULL,
  `emailcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '验证码',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `creat_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_emailcode
-- ----------------------------
INSERT INTO `mr_emailcode` VALUES (1, '', '', 0);

-- ----------------------------
-- Table structure for mr_km
-- ----------------------------
DROP TABLE IF EXISTS `mr_km`;
CREATE TABLE `mr_km`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `km` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '卡密',
  `exp` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '经验值',
  `money` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '金币值',
  `vip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'vip天数',
  `isuse` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'false' COMMENT '是否使用',
  `appid` int(11) NULL DEFAULT NULL COMMENT 'appid',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '未使用' COMMENT '使用者',
  `usetime` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '未使用' COMMENT '使用时间',
  `creattime` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '卡密列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_km
-- ----------------------------

-- ----------------------------
-- Table structure for mr_notes
-- ----------------------------
DROP TABLE IF EXISTS `mr_notes`;
CREATE TABLE `mr_notes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '笔记标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '笔记内容',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建者ip',
  `creattime` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updatetime` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `appid` int(11) NULL DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '笔记内容' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_notes
-- ----------------------------

-- ----------------------------
-- Table structure for mr_passcode
-- ----------------------------
DROP TABLE IF EXISTS `mr_passcode`;
CREATE TABLE `mr_passcode`  (
  `id` int(11) NOT NULL,
  `passcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '密码验证码',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `creattime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_passcode
-- ----------------------------
INSERT INTO `mr_passcode` VALUES (1, '9Veg', '127.0.0.1', 1648285619);

-- ----------------------------
-- Table structure for mr_plate
-- ----------------------------
DROP TABLE IF EXISTS `mr_plate`;
CREATE TABLE `mr_plate`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platename` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '板块名称',
  `plateicon` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '板块icon',
  `appid` int(11) NULL DEFAULT NULL COMMENT 'appid',
  `creattime` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_plate
-- ----------------------------

-- ----------------------------
-- Table structure for mr_post
-- ----------------------------
DROP TABLE IF EXISTS `mr_post`;
CREATE TABLE `mr_post`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '帖子标题',
  `postcontent` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '帖子内容',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '发帖用户名',
  `plateid` int(11) NULL DEFAULT NULL COMMENT '板块ID',
  `view` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '浏览量',
  `lock` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '1' COMMENT '0锁定1未锁定',
  `top` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '1' COMMENT '0置顶1不置顶',
  `replytime` datetime(0) NULL DEFAULT NULL COMMENT '最后回复时间',
  `appid` int(11) NULL DEFAULT NULL,
  `creat_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `file` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '图片链接',
  `is_audit` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '是否审核',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_post
-- ----------------------------

-- ----------------------------
-- Table structure for mr_shop
-- ----------------------------
DROP TABLE IF EXISTS `mr_shop`;
CREATE TABLE `mr_shop`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '商品名称',
  `shoptype` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '1为兑换会员，2为其他类型',
  `money` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '金币数',
  `vipnum` int(11) NULL DEFAULT NULL COMMENT '数量',
  `inventory` int(255) NULL DEFAULT NULL COMMENT '库存',
  `sales` int(255) NULL DEFAULT 0 COMMENT '销量',
  `appid` int(11) NULL DEFAULT NULL COMMENT 'app',
  `creat_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_shop
-- ----------------------------

-- ----------------------------
-- Table structure for mr_shoporder
-- ----------------------------
DROP TABLE IF EXISTS `mr_shoporder`;
CREATE TABLE `mr_shoporder`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `shopname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '商品名称',
  `shoptype` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '商品类型',
  `appid` int(11) NULL DEFAULT NULL,
  `creat_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_shoporder
-- ----------------------------

-- ----------------------------
-- Table structure for mr_upload
-- ----------------------------
DROP TABLE IF EXISTS `mr_upload`;
CREATE TABLE `mr_upload`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `size` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `filePath` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `fullPath` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `creat_time` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mr_upload
-- ----------------------------

-- ----------------------------
-- Table structure for mr_user
-- ----------------------------
DROP TABLE IF EXISTS `mr_user`;
CREATE TABLE `mr_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码',
  `nickname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '这个人没有昵称' COMMENT '昵称',
  `qq` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'qq',
  `useremail` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱',
  `usertx` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT './usertx.png' COMMENT '头像',
  `signature` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '这个人暂未介绍自己' COMMENT '个性签名',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '暂无称号' COMMENT '头衔',
  `appid` int(11) NULL DEFAULT NULL COMMENT 'appid',
  `viptime` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'vip时间',
  `money` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '余额',
  `exp` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '经验',
  `admin` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '是否是管理员',
  `creattime` int(11) NULL DEFAULT NULL COMMENT '注册时间',
  `banned` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'false' COMMENT 'true/false',
  `banned_reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '封禁理由',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ip',
  `user_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'token',
  `signtime` int(11) NULL DEFAULT NULL COMMENT '签到时间',
  `invitecode` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '邀请码',
  `invitetotal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '邀请总人数',
  `inviter` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '邀请人',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户账号' ROW_FORMAT = Dynamic;


SET FOREIGN_KEY_CHECKS = 1;
