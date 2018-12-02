<?php
/**
 * auto generated.
 * Time: {{.Time}}
 */
define("QD_LOG_DIR_NAME", "{{.Name}}");

const TSFlib   = '/usr/local/services/TSF3_qidian-1.0';
const ServPath = __DIR__ . '/../../';

//引入框架的autoload文件
$classLoader = require TSFlib . '/vendor/autoload.php';
require __DIR__ . '/../vendor/autoload.php';

//配置业务代码能够被自动加载
$classLoader->setPsr4('{{.Name}}\\', [__DIR__ . '/../../{{.Name}}']);

$server = new TSF\Core\Server('server', '{{.Name}}', ServPath);

$server->bind('TSF\Mix\Route', '{{.Name}}\Mix\Base\InnerPBRoute');
$server->bind('TSF\Stream\Scanner', '{{.Name}}\Mix\Base\InnerPBScanner');
$server->bind('TSF\Contract\Kernel\Base', 'TSF\Mix\MixKernel');

\Composer\Autoload\includeFile(__DIR__ . "/../Config/env/{$_SERVER['QIDIAN_ENV']}/CampaignConst.php");
\Composer\Autoload\includeFile(__DIR__ . "/../library/ConsoleQidianConfig.php");
$server->bind('QidianConfig', '\ConsoleQidianConfig');
$server->start();