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

 Date: 14/12/2023 23:05:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for auths
-- ----------------------------
DROP TABLE IF EXISTS `auths`;
CREATE TABLE `auths`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `method` enum('GET','POST','PUT','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'GET' COMMENT 'http方法',
  `route` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '可用路由',
  `controller` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '控制器',
  `function` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '控制器方法',
  `status` enum('active','ban','pause') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active' COMMENT 'api状态',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `rte_idx`(`route`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 30 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '可用api表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auths
-- ----------------------------
INSERT INTO `auths` VALUES (1, 'GET', '/role/list', 'App\\Controller\\RoleController', 'getRoleList', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (2, 'GET', '/image/captcha/show', 'App\\Controller\\ImageController', 'getCaptcha', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (3, 'GET', '/image/captcha/verify', 'App\\Controller\\ImageController', 'verifyCaptcha', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (4, 'GET', '/auth/list', 'App\\Controller\\AuthController', 'getAuthsList', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (5, 'GET', '/auth/sync/list', 'App\\Controller\\AuthController', 'getAuthsListAndUpdate', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (6, 'POST', '/role/add', 'App\\Controller\\RoleController', 'addRole', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (7, 'POST', '/role/bind', 'App\\Controller\\RoleController', 'bind', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (8, 'POST', '/role/update', 'App\\Controller\\RoleController', 'update', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (9, 'POST', '/image/qrcode/upload', 'App\\Controller\\ImageController', 'uploadQrcodeToOss', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (10, 'POST', '/image/barcode/upload', 'App\\Controller\\ImageController', 'uploadBarcodeToOss', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (11, 'POST', '/image/qrcode/decode', 'App\\Controller\\ImageController', 'decodeQrcode', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (12, 'POST', '/image/qrcode/download', 'App\\Controller\\ImageController', 'downloadQrcode', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (13, 'POST', '/image/barcode/download', 'App\\Controller\\ImageController', 'downloadBarcode', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (14, 'POST', '/image/qrcode/show', 'App\\Controller\\ImageController', 'qrcode', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (15, 'POST', '/image/barcode/show', 'App\\Controller\\ImageController', 'barcode', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (16, 'POST', '/auth/myself/info', 'App\\Controller\\AuthController', 'getSelfAuthorityInfo', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (17, 'POST', '/auth/belong/roles', 'App\\Controller\\AuthController', 'authBelongRole', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (18, 'POST', '/auth/status/update', 'App\\Controller\\AuthController', 'addRole', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (19, 'POST', '/user/auth/info', 'App\\Controller\\UserController', 'info', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (20, 'POST', '/user/update/password', 'App\\Controller\\UserController', 'updatePassword', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (21, 'POST', '/user/update/info', 'App\\Controller\\UserController', 'updateInfo', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (22, 'POST', '/user/bind/role', 'App\\Controller\\UserController', 'bind', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (23, 'POST', '/login/jwt/get', 'App\\Controller\\LoginController', 'login', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (24, 'POST', '/login/register', 'App\\Controller\\LoginController', 'register', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (25, 'POST', '/login/jwt/deactivate', 'App\\Controller\\LoginController', 'logout', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (26, 'POST', '/login/jwt/status', 'App\\Controller\\LoginController', 'loginStatus', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (27, 'POST', '/login/jwt/refresh', 'App\\Controller\\LoginController', 'refresh', 'active', '2023-12-11 18:31:34', '2023-12-11 18:31:34');
INSERT INTO `auths` VALUES (28, 'POST', '/login/send/sms', 'App\\Controller\\LoginController', 'sendSmsForRegister', 'active', '2023-12-12 13:56:21', '2023-12-12 13:56:21');
INSERT INTO `auths` VALUES (29, 'POST', '/short_chain/convert', 'App\\Controller\\ShortChainController', 'convert', 'active', '2023-12-14 10:02:39', '2023-12-14 10:02:39');
INSERT INTO `auths` VALUES (30, 'POST', '/short_chain/reconvert', 'App\\Controller\\ShortChainController', 'reConvert', 'active', '2023-12-14 22:37:00', '2023-12-14 22:37:00');

SET FOREIGN_KEY_CHECKS = 1;
