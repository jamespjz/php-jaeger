<?php
/**+----------------------------------------------------------------------
 * JamesPi Jaeger [php-Jaeger]
 * +----------------------------------------------------------------------
 * Jaeger Service Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

#jaeger配置项
return[
    'host' => '192.168.109.166',
    'port' => '6831',
    'auth' => null,
    'server_name' => 'gc-jaeger',
    'create_url' => 'http://192.168.109.166:14268/api/traces',
    'jaeger_setting' => 1, //jaeger环境（1：单机 2：集群）
];