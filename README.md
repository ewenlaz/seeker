# Seeker

Seeker是一个基于Swoole实现的服务架构整体实现

------

实现服务

> * Master:负责管理所有节点
> * Node:用于管理该节点下所有的Service
> * ServiceProcess:运行具体的业逻辑
> * Harbor:用于转发协议，连接所有Node.

### LIST

- [ ] CLI命令行管理Master, 增加节点，发布service process ....
- [ ] HTTP异步下载文件，用于节点收到发布任务时 进行下载执行源码或是文件
- [ ] .....
- [x] 消息分发器
- [x] 远程调用
- [x] .....

### 启动Master

> php ./bin/launcher.php --host=0.0.0.0 --port=9901 --exec-php=/use/local/php --type=master

### 启动Node

> php ./bin/launcher.php --host=0.0.0.0 --port=9901 --exec-php=/use/local/php --type=node

### 启动Harbor

> php ./bin/launcher.php --host=0.0.0.0 --port=9901 --exec-php=/use/local/php --type=harbor

### 开发时启动Service process

> 需要将目录切换到seeker的上次目录。
> php ./seeker/bin/service.php --process=user --version=2.0.1 --vendor=./vendor --debug-user=./seeker/bin/demo/user/2.0.1 --debug-widget-demo=./seeker/bin/demo/widget_demo/1.0.0

### Service 示例

```php
namespace Seeker\Service\Master;

use Seeker\Sharded;
use Seeker\Manager\NodeClient;
use Seeker\Protocol\Error;
use Seeker\Service\Common\Base;

class Deploy extends Base
{
    //节点认证
    public function progress()
    {
        //找到相应的Node...
        $nodeId = $this->request->getFromNode();
        $progress = $this->request->get('progress');
        $taskId = $this->request->get('taskId');

        print_r($this->request);

        echo 'Deploy Progress:' . sprintf('N:%d, T:%s, %d%%'
            , $nodeId
            , $taskId
            , $progress
        ) . PHP_EOL;
    }
}
```

### 调用别的Service 示例

```php
$this->dispatcher->remoteCall('common.node.login')
    ->setToNode($this->nodeId)
    ->set('type', 'master')
    ->set('authKey', $this->authKey)
    ->then(function($connection, $response) {
        if (!$response->getCode()) {
            $connection->setAuthed(static::AUTHED_COMMON | static::AUTHED_NODE);
        } else {
            $connection->close();
        }
    })
    ->sendTo($this);
```
