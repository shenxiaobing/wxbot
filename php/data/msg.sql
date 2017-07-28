/*
Navicat MySQL Data Transfer

Source Server         : 172.16.12.254
Source Server Version : 50555
Source Host           : 172.16.12.254:3306
Source Database       : wechat

Target Server Type    : MYSQL
Target Server Version : 50555
File Encoding         : 65001

Date: 2017-07-28 14:20:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `msg`
-- ----------------------------
DROP TABLE IF EXISTS `msg`;
CREATE TABLE `msg` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `wx_name` varchar(300) NOT NULL,
  `gourp_id` varchar(100) NOT NULL COMMENT '群id',
  `gourp_name` varchar(200) NOT NULL COMMENT '群名称',
  `msg_id` varchar(100) NOT NULL COMMENT '消息id',
  `msg_type_id` int(4) NOT NULL COMMENT '消息类型',
  `user_id` varchar(100) NOT NULL COMMENT '发送者id',
  `username` varchar(100) NOT NULL COMMENT '发送者名称',
  `data_type` int(4) NOT NULL COMMENT '消息数据类型',
  `data` text NOT NULL COMMENT '消息数据',
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

