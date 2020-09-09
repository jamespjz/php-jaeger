# php-jaeger
此客户端为分布式场景下链路追踪系统，基于jukylin/jaeger-php实现。<br>

使用教程说明：
--------------------
简单理解下相关概念：<br>
分布式追踪，也称为分布式请求追踪，是一种用于分析和监视应用程序的方法，特别是那些使用微服务体系结构构建的应用程序，IT和DevOps团队可以使用分布式追踪来监视应用程序; 分布式追踪有助于查明故障发生的位置以及导致性能低下的原因，开发人员可以使用分布式跟踪来帮助调试和优化他们的代码。<br>
 - Trace 事物在分布式系统中移动时的描述
 - Span 一种命名的、定时的操作，表示工作流的一部分。Spans接受key:value标签以及附加到特定Span实例的细粒度、带时间戳的结构化日志
 - Span Contenxt 携带分布式事务的跟踪信息，包括当它通过网络或消息总线将服务传递给服务时。SPAN上下文包含Trace标识符、SPAN标识符和跟踪系统需要传播到下游服务的任何其他数据
 
 使用示例：
````
require_once 'vendor/autoload.php';
use Jamespi\Jaeger\Start;

$type = 1; //1：client  2：server
$config = [
    'host' => '192.168.109.166',
    'port' => '6831',
    'server_name' => 'gc-jaeger', //服务名称
    'create_url' => 'http://192.168.109.166:14268/api/traces', //jaeger-collector地址
];
$params = [
    'span_name' => 'gc-jaeger-test',//默认jaeger-span
    'version' => '0.0.1',//可不传
    'tags' => [
        'order_code' => '00000011122',
        'order_status' => '1',
    ]//标签，便于查询
];

链路追踪调用顺序：
//创建服务
echo (new Start())->run(2, $config)->create($params);
//节点1
echo (new Start())->run(1, $config)->create($params1);//选择实际场景参数（参考$params）
//节点2
echo (new Start())->run(1, $config)->create($params2);//选择实际场景参数（参考$params）
//节点3
echo (new Start())->run(1, $config)->create($params3);//选择实际场景参数（参考$params）

````