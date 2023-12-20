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

 Date: 20/12/2023 19:14:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for goods
-- ----------------------------
DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `price` decimal(10,2) NOT NULL COMMENT '商品单价',
  `stock` int unsigned NOT NULL DEFAULT '0' COMMENT '库存数量',
  `brand` varchar(128) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '品牌',
  `version` int unsigned NOT NULL DEFAULT '0' COMMENT '乐观锁字段',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `stock_idx` (`stock`) USING BTREE COMMENT '库存索引'
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='商品表';

-- ----------------------------
-- Records of goods
-- ----------------------------
BEGIN;
INSERT INTO `goods` (`id`, `name`, `price`, `stock`, `brand`, `version`, `create_time`, `update_time`) VALUES (1, 'IPhone15 Pro 1TB 暗紫色', 5999.00, 100, 'Apple', 0, '2023-12-19 15:48:00', '2023-12-19 17:46:02');
INSERT INTO `goods` (`id`, `name`, `price`, `stock`, `brand`, `version`, `create_time`, `update_time`) VALUES (2, 'Xiaomi 14 128G 雪花白', 4888.88, 100, '小米', 0, '2023-12-19 15:48:00', '2023-12-19 15:48:00');
INSERT INTO `goods` (`id`, `name`, `price`, `stock`, `brand`, `version`, `create_time`, `update_time`) VALUES (3, 'Huawei p50 pro 256G 标准版', 5666.00, 100, '华为', 0, '2023-12-19 15:48:00', '2023-12-19 15:48:00');
INSERT INTO `goods` (`id`, `name`, `price`, `stock`, `brand`, `version`, `create_time`, `update_time`) VALUES (4, '赣州脐橙 一箱 5KG', 48.99, 96, '赣州脐橙', 207, '2023-12-19 15:48:00', '2023-12-20 19:02:30');
INSERT INTO `goods` (`id`, `name`, `price`, `stock`, `brand`, `version`, `create_time`, `update_time`) VALUES (5, '腾讯音乐VIP会员 1个月', 29.99, 100, '腾讯', 0, '2023-12-19 15:48:00', '2023-12-19 15:48:00');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
