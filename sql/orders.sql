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

 Date: 20/12/2023 19:15:25
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `gid` int unsigned NOT NULL DEFAULT '0' COMMENT '商品ID(外键)',
  `order_no` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单唯一编号',
  `number` int unsigned NOT NULL COMMENT '商品数量',
  `payment_money` decimal(10,2) NOT NULL COMMENT '支付金额',
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '买家UID(外键)',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_idx` (`order_no`) USING BTREE COMMENT '订单编号唯一索引'
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='订单表';

SET FOREIGN_KEY_CHECKS = 1;
