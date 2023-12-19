/*
 Navicat Premium Data Transfer

 Source Server         : 腾讯云
 Source Server Type    : MySQL
 Source Server Version : 80032 (8.0.32)
 Source Host           : 42.193.136.117:3306
 Source Schema         : hyperf

 Target Server Type    : MySQL
 Target Server Version : 80032 (8.0.32)
 File Encoding         : 65001

 Date: 19/12/2023 01:43:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for log_records
-- ----------------------------
DROP TABLE IF EXISTS `log_records`;
CREATE TABLE `log_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `level` enum('INFO','WARNING','ERROR','ALERT','CRITICAL','EMERGENCY','NOTICE') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'INFO' COMMENT '日志级别',
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打印日志所在的文件',
  `line` int unsigned NOT NULL DEFAULT '0' COMMENT '打印日志所在文件的行号',
  `class` varchar(128) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '调用类',
  `function` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '调用方法',
  `message` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '日志信息',
  `context` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志详情',
  `trace` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Trace 信息',
  `create_time` datetime DEFAULT NULL COMMENT '日志时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `msg_idx` (`message`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='日志记录表';

SET FOREIGN_KEY_CHECKS = 1;
