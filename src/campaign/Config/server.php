<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:18:10.527011 +0800 CST m=+0.002216660
 */
return [
    'name'            => env('APP_SERVER_NAME', 'campaign'),
    'mode'            => env('APP_SERVER_MODE', SWOOLE_PROCESS),
    'type'            => env('APP_SERVER_TYPE', 'Mix'),
    'closeWhenFinish' => true,//请求结束是否关闭连接，tcp和mix类型需要
    'host'            => env('APP_SERVER_HOST', '0.0.0.0'),
    'port'            => env('APP_SERVER_PORT', 9800),
    'pidDir'          => env('APP_SERVER_PID_DIR', realpath('/usr/local/services/TSF3_qidian-1.0/Storage/Pid')),
    'swoole'          => [
        'worker_num'               => 1,
        'daemonize'                => true,
        'log_file'                 => '/tmp/log/swoole.log',
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time'      => 10,
    ],
    'php'             => '/usr/local/services/TSF3_qidian-1.0/runtime/php/bin/php',
    'root'            => __DIR__ . '/../Mix/Server.php',
];