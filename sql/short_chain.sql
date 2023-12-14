/*
 Navicat MySQL Data Transfer

 Source Server         : 42.193.136.117_3306
 Source Server Type    : MySQL
 Source Server Version : 80032
 Source Host           : 42.193.136.117:3306
 Source Schema         : hyperf

 Target Server Type    : MySQL
 Target Server Version : 80032
 File Encoding         : 65001

 Date: 14/12/2023 23:06:16
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for short_chain
-- ----------------------------
DROP TABLE IF EXISTS `short_chain`;
CREATE TABLE `short_chain`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int UNSIGNED NOT NULL DEFAULT 0 COMMENT 'users外键',
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '待转换url',
  `short_chain` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '生成的短链',
  `hash_code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '短链hash码',
  `expire_at` datetime(0) NULL DEFAULT NULL COMMENT '失效日期',
  `status` enum('active','ban') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active' COMMENT '状态',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `short_idx`(`hash_code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '短链映射表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
