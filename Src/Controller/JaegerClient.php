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

class JaegerClient
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
     * 创建jaeger客户端trace
     * @param array $arguments 请求参数
	 * @param int $time 休眠时间
     * @return mixed|void
     */
    public function create(array $arguments, int $time = 0)
    {
        try {
            $header = [];
            $spanName = $arguments['span_name'] ?? $this->spanName;
			if (!isset($_SERVER['UBER-TRACE-ID']) && isset($_SERVER['HTTP_UBER_TRACE_ID'])) {
                $_SERVER['UBER-TRACE-ID'] = $_SERVER['HTTP_UBER_TRACE_ID'];
            }
            if (!isset($_SERVER['UBERCTX-VERSION']) && isset($_SERVER['HTTP_UBERCTX_VERSION'])) {
                $_SERVER['UBERCTX-VERSION'] = $_SERVER['HTTP_UBERCTX_VERSION'];
            }
            $spanContext = $this->clientTracer->extract(Formats\TEXT_MAP, $_SERVER);
            if ($spanContext) {
                $clientSpan = $this->clientTracer->startSpan($spanName, ['child_of' => $spanContext]);
            } else {
                $clientSpan = $this->clientTracer->startSpan($spanName);
            }
			
			if($time){
				sleep($time);
			}
			
            if ($arguments['version'])
                $clientSpan->addBaggageItem("version", $arguments['version']);

            $this->clientTracer->inject($clientSpan->spanContext, Formats\TEXT_MAP, $header);

            $client = Client::create($this->config['create_url'], false);

            if ($header) {
                foreach ($header as $key => $val) {
                    $client->setHeader($key, $val);
                }
            }
            if (isset($arguments['tags']) && !empty($arguments['tags']) && is_array($arguments['tags'])) {
                foreach ($arguments['tags'] as $k => $v) {
                    $clientSpan->setTag($k, $v);
                }
            }
            $clientSpan->finish();
            return json_encode(['status'=>'success', 'msg'=>'链路注入成功', 'data'=>['headers'=>$header] ]);
        }catch (\Exception $e){
            return json_encode(['status'=>'failed', 'msg'=>$e->getMessage()]);
        }
    }

    public function __destruct()
    {
        $this->mode->flush();
    }
}