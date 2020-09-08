<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Controller Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Jaeger\Controller;

use Hprose\Client;
use Jaeger\Config;
use OpenTracing\Formats;

class JaegerServer
{
    /**
     * 服务配置参数
     * @var
     */
    protected $config;
    /**
     * 实例化链接
     * @var
     */
    protected $mode;
    /**
     *
     */
    protected $serverTracer;
    /**
     * span默认名称
     */
    protected $spanName = 'jaeger-span';


    /**
     * JaegerClinet constructor.
     * @param array $config 配置参数
     */
    public function __construct(array $config)
    {
        unset($_SERVER['argv']);
        $this->config = $config;
        $this->mode = Config::getInstance();
        $this->serverTracer = $this->mode->initTracer($this->config['server_name'], $this->config['host'].":".$this->config['port']);
    }

    /**
     * 创建jaeger服务端trace
     * @param array $arguments 请求参数
     * @return mixed|void
     */
    public function create(array $arguments)
    {
        try {
            $header = [];
            $spanName = $arguments['span_name'] ?? $this->spanName;
            $spanContext = $this->serverTracer->extract(Formats\TEXT_MAP, $_SERVER);
            if ($spanContext) {
                $serverSpan = $this->serverTracer->startSpan($spanName, ['child_of' => $spanContext]);
            } else {
                $serverSpan = $this->serverTracer->startSpan($spanName);
            }

            $this->clientTracer->inject($serverSpan->spanContext, Formats\TEXT_MAP, $_SERVER);

            $serverSpan->finish();
            return json_encode(['status'=>'success', 'msg'=>'链路注入成功']);
        }catch (\Exception $e){
            return json_encode(['status'=>'failed', 'msg'=>$e->getMessage()]);
        }
    }

    public function __destruct()
    {
        $this->mode->flush();
    }
}