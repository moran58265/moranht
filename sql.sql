ALTER TABLE `mr_user` ADD `invitecode` VARCHAR(255) NOT NULL AFTER `signtime`, ADD `invitetotal` VARCHAR(255) NOT NULL DEFAULT '0' AFTER `invitecode`, ADD `inviter` VARCHAR(255) NOT NULL AFTER `invitetotal`;ALTER TABLE `mr_app` ADD `invitemoney` VARCHAR(255) NOT NULL AFTER `commentexp`, ADD `inviteexp` VARCHAR(255) NOT NULL AFTER `invitemoney`, ADD `invitevip` VARCHAR(255) NOT NULL AFTER `inviteexp`, ADD `finvitemoney` VARCHAR(255) NOT NULL AFTER `invitevip`, ADD `finviteexp` VARCHAR(255) NOT NULL AFTER `finvitemoney`, ADD `finvitevip` VARCHAR(255) NOT NULL AFTER `finviteexp`;UPDATE `mr_user` SET `invitetotal` = '0';UPDATE `mr_app` SET `invitemoney` = '0',`inviteexp` = '0',`invitevip` = '0',`finvitemoney` = '0',`finviteexp` = '0',`finvitevip` = '0';