<?php


function getBin($askId, $data, $service)
{
    $header = pack('nnnNnnLnl'
        , strlen($data)
        , 0
        , 0
        , $askId
        , 0
        , 0
        , crc32($service)
        , 0
        , 0
    );
    echo 'call Service:'. crc32($service) . PHP_EOL;
    return $header . $data;
}


class Client extends Swoole\Client
{

    public $_askid = 0;
    public $_callbacks = [];

    public function sendCallback ($data, $service, $callback)
    {
        $this->_askid ++;
        $this->_callbacks[$this->_askid] = $callback;
        $this->send(getBin($this->_askid, $data, $service));
    }
}

$client = new Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC); //异步非阻塞


$client->set(

    [
        'open_length_check'     => true,
        'package_length_type'   => 'n',
        'package_length_offset' => 0,       //第N个字节是包长度的值
        'package_body_offset'   => 24,       //第几个字节开始计算长度
        'package_max_length'    => 1024 * 8  //协议最大长度 8K
    ]
);

$client->_file = file_get_contents('service.zip');
$client->_start = 0;
$client->_size = strlen($client->_file);
$client->_fileMd5 = md5_file(__DIR__ .'/service.zip');
$client->_buffsize = 2 * 1024;

$client->on("connect", function(Swoole\Client $cli) {

    echo 'on connect' . PHP_EOL;
    $loginData = [
        'type' => 'tool',
        'authKey' => 'tool'
    ];

    $cli->sendCallback(json_encode($loginData), 'common.node.login', function($header, $body) use ($cli) {
        if ($header['code'] === 0) {

            //发送挂载协议
            $nodeAddData = [
                'ip' => '0.0.0.0',
                'port' => 9902,
                'nodeId' => 10000,
                'authedKey' => 'node_10000'
            ];

            $cli->sendCallback(json_encode($nodeAddData), 'manager.node.add', function($header, $body) use ($cli) {
                echo json_encode($header) . PHP_EOL;
                echo $body . PHP_EOL;

                $pushData = [
                    'process' => 'user_process',
                    'version' => '2.0.0',
                    'password' => '123455',
                    'nodeId' => 10000,
                    'taskId' => 'user_process_2_0_0',
                    'url' => 'http://baidu.com/user_process_2_0_0.zip?key=bacdaaaa'
                ];

                $cli->sendCallback(json_encode($pushData), 'manager.node.deploy', function($header, $body) {
                    echo json_encode($header) . PHP_EOL;
                    echo $body . PHP_EOL;
                });

                
            });


        }
    });
});

$client->on("receive", function(Swoole\Client $cli, $data){
    $protocol = unpack('nlen/nfromNode/nfromProcess/NaskId/ntoNode/ntoProcess/lservice/sflag/lcode', $data);
    print_r($protocol);
    if ($cli->_callbacks[$protocol['askId']]) {
        $cli->_callbacks[$protocol['askId']]($protocol, substr($data, 24));
    }
});

$client->on("error", function(Swoole\Client $cli){
    echo "error\n";
});

$client->on("close", function(Swoole\Client $cli){
    echo "Connection close\n";
});

$client->connect('127.0.0.1', 9901);