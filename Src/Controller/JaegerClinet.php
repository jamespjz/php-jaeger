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

class JaegerClinet
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
    protected $clientTracer;
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
        $this->clientTracer = $this->mode->initTracer($this->config['server_name'], $this->config['host'].":".$this->config['port']);
    }

    /**
     * 获取分布式锁
     * @param array $arguments 请求参数
     * @return mixed|void
     */
    public function acquireLock(array $arguments)
    {
        $header = [];
        $spanName = $arguments['span_name']??$this->spanName;
        $spanContext = $this->clientTracer->extract(Formats\TEXT_MAP, $_SERVER);
        if ($spanContext){
            $clientSpan = $this->clientTracer->startSpan($spanName, ['child_of' => $spanContext]);
        }else{
            $clientSpan = $this->clientTracer->startSpan($spanName);
        }
        if ($arguments['version'])
            $clientSpan->addBaggageItem("version", $arguments['version']);

        $this->clientTracer->inject($clientSpan->spanContext, Formats\TEXT_MAP, $header);

        $client = Client::create($this->config['create_url'], false);

        if($header){
            foreach($header as $key => $val){
                $client->setHeader($key, $val);
            }
        }
        if (isset($arguments['tags']) && !empty($arguments['tags']) && is_array($arguments['tags'])){
            foreach ($arguments['tags'] as $k=>$v){
                $clientSpan->setTag($k, $v);
            }
        }
        $clientSpan->finish();
    }

    public function __destruct()
    {
        $this->mode->flush();
    }
}