<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Service;

use App\Model\Roles;

class RoleService extends AbstractService
{
    /**
     * 添加角色(默认没有任何权限).
     * @param string $roleName 角色名称
     */
    public function add(string $roleName): void
    {
        (new Roles(['role_name' => $roleName]))->save();
    }
}
