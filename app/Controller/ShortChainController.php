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

namespace App\Controller;

use App\Request\ShortChainRequest;
use App\Service\ShortChainService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;

/**
 * 短链相关.
 * Class ShortChainController.
 */
#[Controller(prefix: 'short_chain')]
class ShortChainController extends AbstractController
{
    #[Inject]
    protected ShortChainService $service;

    /**
     * 转换短链.
     * @param ShortChainRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'convert')]
    #[Scene(scene: 'convert')]
    public function convert(ShortChainRequest $request): array
    {
        $url = $request->input('url');
        $ttl = $request->input('ttl', 60);
        $uid = $this->jwtPayload['data']['uid'];
        $shortChain = $this->service->convert($uid, $url, $ttl);
        return $this->result->setData(['short_chain' => $shortChain])->getResult();
    }
}
