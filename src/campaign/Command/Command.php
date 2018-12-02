<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:08:18.037558 +0800 CST m=+0.001939780
 */

const TSFlib   = '/usr/local/services/TSF3_qidian-1.0';
const ServPath = __DIR__ . '/../../';

//引入框架的autoload文件
$classLoader = require TSFlib . '/vendor/autoload.php';

//\Composer\Autoload\includeFile( __DIR__ . '/../com.tencent.qidian.webserver.inner.head.php');
\Composer\Autoload\includeFile(__DIR__ . '/../../tsf3_library/require.php');
\Composer\Autoload\includeFile(__DIR__ . "/../Config/env/{$_SERVER['QIDIAN_ENV']}/CampaignConst.php");
\Composer\Autoload\includeFile(__DIR__ . "/../library/ConsoleQidianConfig.php");

//配置业务代码能够被自动加载
$classLoader->setPsr4('campaign_mix_svr\\', [__DIR__ . '/../../campaign']);
$classLoader->setPsr4('Qidian\\', [__DIR__ . '/../../pb_library/Qidian']);

function test_udp_mix_server()
{
    $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_UDP);
    $client->connect('127.0.0.1', 9505, 4);
    $kfuin = 2852199351;
    $cmd   = 100;
    $body  = 'success';

    $uinids = new \Com\Tencent\Epc\Innerprocess\UinIDs();
    $uinids->setUint64Kfuin($kfuin);
    $innerHead = new \Com\Tencent\Epc\Innerprocess\CorpInnerHead();
    $innerHead->setUint32Cmd($cmd);
    $innerHead->setUinIds($uinids);
    $head = new \Com\Tencent\Epc\Innerprocess\Head();
    $head->setUint64ProtoType(0x01);
    $head->setInnerHead($innerHead);
    $head    = $head->serializeToString();
    $headLen = strlen($head);
    $bodyLen = strlen('success');
    $data    = pack('C', 0x5b) . pack('N', $cmd) . pack('N', 10000) . pack('N', $headLen) . pack('N', $bodyLen) . $head . $body . pack('C', 0x5d);
    $client->send($data);
    $data = $client->recv();
    var_dump($data);
}

function test_tcp_mix_server()
{
    $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
    $client->connect('127.0.0.1', 9505, 4);
    $kfuin = 2852199351;
    $cmd   = 100;
    $body  = 'success';

    $uinids = new \Com\Tencent\Epc\Innerprocess\UinIDs();
    $uinids->setUint64Kfuin($kfuin);
    $innerHead = new \Com\Tencent\Epc\Innerprocess\CorpInnerHead();
    $innerHead->setUint32Cmd($cmd);
    $innerHead->setUinIds($uinids);
    $head = new \Com\Tencent\Epc\Innerprocess\Head();
    $head->setUint64ProtoType(0x01);
    $head->setInnerHead($innerHead);
    $head    = $head->serializeToString();
    $headLen = strlen($head);
    $bodyLen = strlen('success');
    $data    = pack('C', 0x5b) . pack('N', $cmd) . pack('N', 10000) . pack('N', $headLen) . pack('N', $bodyLen) . $head . $body . pack('C', 0x5d);
    $client->send($data);
    $data = $client->recv();
    var_dump($data);
    $client->close();
}

function test_get_env()
{
    $conf   = \L5Assistant::getRoute(798145, 2162688);
    $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_UDP);
    $client->connect($conf['ip'], $conf['port'], 4);
    $kfuin = 2852199351;
    $data  = json_encode(['type' => 0, 'data' => ['kfuin' => $kfuin]]);
    $client->send($data);
    $data = $client->recv();
    var_dump($data);
}

function refreshNameAndIndexId($kfuin) {
    $ret = campaign_mix_svr\Mix\Model\UR_db_marketing_t_campaign_relation::findWithTcp($kfuin,$kfuin)->where([
        'FKFUin'      => $kfuin,
    ])->all();
    if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
        \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_RELATED_ACTIVITY_FAILED), CampaignError::GET_RELATED_ACTIVITY_FAILED);
    }
    if (!empty($ret['data'])) {
        foreach ($ret['data'] as $row) {
            $activityModel = new campaign_mix_svr\Mix\Model\ActivityModel($kfuin);
            $actvitiyInfo = $activityModel->getActivities($row->FType,[explode('_',$row->FRelatedId,2)[1]]);
            if(!empty($row->FIndexId) or !empty($row->FName)) {
                continue;
            }
            $row->FIndexId = $actvitiyInfo[0]['indexId'];
            $row->FName = $actvitiyInfo[0]['name'];
            $row->setIsNew(0);
            $r = $row->save();
            var_dump($row->FId.":".$r);
        }
    }
}

Swoole\Coroutine::create(function() {
    //refreshNameAndIndexId(2852150212);
});