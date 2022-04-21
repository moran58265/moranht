# 默然iapp后台管理系统1.0

#### 介绍
默然iapp后台管理系统1.0是一个基于[Thinkphp](https://www.thinkphp.cn/) 的后台管理系统，提供了基本的应用管理、用户管理 、卡密管理 、笔记管理 、邮箱管理 、商城管理 、论坛管理 、附件管理等功能。


#### 软件架构
thinkphp5.1+mysql实现


#### 安装教程

1. 下载源码到服务器目录
2. 解压源码
3. 设置运行目录为public
4. 配置伪静态（详情看下方）
5. 访问域名即可
6. 安装完成
7. 开始使用
8. 后台：登录账号：admin 密码：123456  登录后台：http://域名/admin/

#### 伪静态
## Apache环境
```html
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
 
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```
## Nginx环境
```html
location / {
if (!-e $request_filename){
rewrite  ^(.*)$  /index.php?s=$1  last;   break;
}
}
```

#### 特别鸣谢
[笔下光年 / Light Year Admin v4](https://gitee.com/yinqi/Light-Year-Admin-Template-v4)


#### 更新介绍
