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

 Date: 14/12/2023 23:03:59
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `account` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '密码',
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `age` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '年龄',
  `sex` enum('man','woman','others') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'others' COMMENT '性别',
  `status` enum('ban','active') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'active' COMMENT '用户状态',
  `refresh_jwt_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '刷新JwtToken',
  `jwt_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'Jwt',
  `role_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '角色ID(外键)',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `phone_idx`(`phone`) USING BTREE,
  INDEX `account_idx`(`account`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'Jerry', '0b2952b0d93576dd24b49dcb66a9c7d8', '15361158702', 29, 'man', 'active', 'eyJ0eXAiOiJKV1QiLCJjdHkiOiIiLCJraWQiOm51bGwsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJodHRwczovL2FwaS50emYtZm9yeW91Lnh5eiIsInN1YiI6ImFjY3JlZGl0IiwiYXVkIjoicGMiLCJpYXQiOjE3MDI1NjI4NjcsIm5iZiI6MTcwMjU2Mjg2OCwiZXhwIjoxNzAzMTY3NjY3LCJkYXRhIjoie1widWlkXCI6MSxcInJpZFwiOltcIjFcIixcIjNcIixcIjRcIl19In0.GBS8nQqD2Gsh-bYKFVeCuuLXsmq6X1d_K6bz2L_6K-Q', 'eyJ0eXAiOiJKV1QiLCJjdHkiOiIiLCJraWQiOm51bGwsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJodHRwczovL2FwaS50emYtZm9yeW91Lnh5eiIsInN1YiI6ImFjY3JlZGl0IiwiYXVkIjoicGMiLCJpYXQiOjE3MDI1NjI4NjcsIm5iZiI6MTcwMjU2Mjg2OCwiZXhwIjoxNzAyNjQ5MjY3LCJkYXRhIjoie1widWlkXCI6MSxcInJpZFwiOltcIjFcIixcIjNcIixcIjRcIl19In0.97EngZWqLvm4bx-HgZNVErv1FB0XiLeKXS6aHn6YFAg', '1,3,4', '2023-12-04 01:40:33', '2023-12-14 22:07:47');
INSERT INTO `users` VALUES (2, 'Foo', 'fcea920f7412b5da7be0cf42b8c93759', '13939265073', 0, 'others', 'active', NULL, NULL, '3', '2023-12-09 23:11:10', '2023-12-09 23:11:10');

SET FOREIGN_KEY_CHECKS = 1;
