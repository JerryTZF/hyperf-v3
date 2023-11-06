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

namespace App\Lib\Result;

use Hyperf\Context\Context;

// 协程结果集封装,支持链式调用,可以灵活的拼接出你想要的结果格式
class Result
{
    /**
     * 标准结果集.
     */
    public const RESULT = [
        'code' => 200,
        'msg' => 'ok',
        'status' => true,
        'data' => [],
    ];

    /**
     * 上下文key, 其他上下文请勿使用该key.
     */
    private string $key = 'ResponseResult';

    /**
     * 获取标准的返回格式.
     */
    public function getResult(): array
    {
        return Context::getOrSet($this->key, self::RESULT);
    }

    /**
     * 重置返回结果对象.
     * @return $this
     */
    public function resetResult(): Result
    {
        Context::set($this->key, self::RESULT);
        return $this;
    }

    /**
     * 设置错误码和错误信息.
     * @param mixed $errorCode
     * @param mixed $errorInfo
     * @return $this
     */
    public function setErrorInfo(int $errorCode, string $errorInfo): Result
    {
        $isExist = Context::has($this->key);
        if ($isExist) {
            $value = Context::get($this->key);
        } else {
            $value = self::RESULT;
        }
        $value['code'] = $errorCode;
        $value['msg'] = $errorInfo;
        $value['status'] = false;
        Context::set($this->key, $value);

        return $this;
    }

    /**
     * 设置数据.
     * @return $this
     */
    public function setData(array|string $data): Result
    {
        $isExist = Context::has($this->key);
        if ($isExist) {
            $value = Context::get($this->key);
        } else {
            $value = self::RESULT;
        }
        $value['data'] = $data;
        Context::set($this->key, $value);

        return $this;
    }

    /**
     * 添加额外Key-Value.
     * @return $this
     */
    public function addKey(string $key, array|string $values): Result
    {
        $isExist = Context::has($this->key);
        if ($isExist) {
            $value = Context::get($this->key);
        } else {
            $value = self::RESULT;
        }
        $value[$key] = $values;
        Context::set($this->key, $value);

        return $this;
    }
}
