<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Basic Service Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Jaeger;

use ReflectionClass;
use Jamespi\Jaeger\Controller\JaegerClient;
use Jamespi\Jaeger\Controller\JaegerServer;
class Start
{
    /**
     * 锁服务配置项
     * @var mixed
     */
    protected $config = [];
    /**
     * 业务场景类别
     * @var mixed
     */
    protected $type = 1;
    /**
     * 服务实例化对象
     * @var object
     */
    protected $model;

    public function __construct()
    {
        $this->config = include (__DIR__ . '/Config/Config.php');
    }

    /**
     * 启动服务
     * @param int $type 服务类型 (1：客户端  2：服务端)
     * @param array $config 服务配置
     * @return $this 服务实例化对象
     */
    public function run(int $type, array $config)
    {
        $mysql = [];
        $this->type = $type;
        if (!empty($config)) $this->config = array_merge($this->config, $config);

        switch ($type){
            case 1:
                $this->model = (new JaegerClient($this->config));
                break;
            case 2:
                $this->model = (new JaegerServer($this->config));
                break;
            default:
                $this->model = (new JaegerClient($this->config));
                break;
        }

        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        try{
            $class = new ReflectionClass($this->model);
            $class->getMethod($name);
            $data = call_user_func_array([$this->model, $name], $arguments);
            $data = json_decode($data, true);
            if ($data['status'] == 'success')
                return json_encode(['status'=>'success', 'msg'=>'调用成功！', 'data'=>isset($data['data'])?$data['data']:[]]);
            else
                return json_encode(['status'=> 'failed', 'msg'=>'Error：'.isset($data['msg'])?$data['msg']:[], 'data'=>isset($data['data'])?$data['data']:[]]);
        }catch (\Exception $e){
            return json_encode(['status'=> 'failed', 'msg'=>'Error：'.$e->getMessage()]);
        }
    }

}