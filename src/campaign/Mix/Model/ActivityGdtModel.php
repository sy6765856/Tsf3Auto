<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/25
 */

namespace campaign_mix_svr\Mix\Model;

use campaign_mix_svr\Mix\Model;
use Qidian\Web\Marketing\Campaign;
use campaign_mix_svr\Mix\Base\CampaignError;

class ActivityGdtModel extends ActivityBaseModel
{
    private $gdtAccountId;
    private $access_token;

    public function setIpPort()
    {
    }

    public function __construct($kfuin, $kfext = 0, $seq = '')
    {
        parent::__construct($kfuin, $kfext, $seq);
        $this->getBindGdtAccount();
    }

    private function getBindGdtAccount()
    {
        $gdtModel = Model\UR_db_base_ad_t_gdt_info::find($this->kfuin, $this->kfext);
        $gdtRet   = $gdtModel->where([
            ['FUin', $this->kfuin],
            ['FStatus', 1],
        ])->one();

        if (empty($gdtRet->FId)) {
            throw new \Exception(CampaignError::GDT_ACCOUNT_NOT_EXIST, '没有权限，请先绑定广点通账号');
        }
        //\QdLogService::logInfo(print_r($gdtRet->FAccessToken, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $this->access_token = $gdtRet->FAccessToken;
        $this->gdtAccountId = $gdtRet->FGdtAccountId;
        return $gdtRet;
    }

    /**
     * send http request
     *
     * @param  array $rq http请求信息
     *                   method     : 请求方法，'get', 'post', 'put', 'delete', 'head'
     *                   data       : 请求数据，如有设置，则method为post
     *                   header     : 需要设置的http头部
     *                   host       : 请求头部host
     *                   timeout    : 请求超时时间
     *                   cert       : ca文件路径
     *                   ssl_version: SSL版本号
     * @return array    http请求响应
     */
    private function sendToGdt($uri, $rq, $file = [], $timeout = 8)
    {
        $timestamp = time();
        $nonce     = 'qd' . $timestamp . rand();
        $url       = $uri . "?access_token={$this->access_token}&timestamp={$timestamp}&nonce={$nonce}";
        if (isset($rq['method']) and in_array(strtolower($rq['method']), array(
                'get',
                'delete',
            )) and !empty($rq['data'])) {
            foreach ($rq['data'] as $k => $v) {
                $url .= "&{$k}={$v}";
            }
        }
        $httpClient = new \HttpClient($url);
        $header     = isset($rq['header']) ? $rq['header'] : array();

        $conf      = \CampaignConst::getHttpProxyConf();
        $routeConf = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        $httpClient->setProxy($routeConf['ip'], $routeConf['port']);
        $httpClient->setTimeout($timeout);
        $header['Host'] = "api.e.qq.com";

        isset($rq['timeout']) && $httpClient->setTimeout($rq['timeout']);

        \QdLogService::logInfo("gdt send url: " . $url);
        \QdLogService::logInfo("gdt send req: " . \Util::array2Str($rq));

        switch (true) {
            case isset($rq['method']) && in_array(strtolower($rq['method']), array(
                    'get',
                    'delete',
                )):
                $ret = $httpClient->get(array(), $header);
                break;
            default:
                $ret = $httpClient->post($rq['data'], $header, $file);
        }
        if ($ret['r'] != 0) {
            \QdLogService::logError("http ret: " . print_r($ret, true));
            return array('header' => '', 'data' => \Util::array2Str($ret), 'info' => array('status' => $ret['r']));
        }
        if ($ret['data']['status'] != 200) {
            throw new \Exception('http request error', $ret['data']['status']);
        }
        $header = $ret['data']['headers'];
        $body   = $ret['data']['body'];
        \QdLogService::logInfo("gdt send ret: " . print_r($ret['r'], true));
        return array('header' => $header, 'data' => empty($body) ? '' : $body);
    }

    public function getList($start, $count, $keyword = '')
    {
        // TODO: Implement getList() method.
    }

    /*
    * 获取广点通列表
    */
    public function getGdtList($listLevel, $fatherId, $start, $count, $keyword)
    {
        \QdLogService::logInfo("getList listLevel:{$listLevel}, fatherId:{$fatherId}, start:{$start}, count:{$count}, keyword:{$keyword}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $retArray = [];
        $total    = 0;
        $page     = $start / $count + 1;
        if (!empty($keyword) or $listLevel == 2) { //拉取广告主列表
            $params['method'] = 'get';
            if (!empty($keyword)) {
                $params['data'] = [
                    'account_id' => $this->gdtAccountId,
                    //'filtering'  => '[{"field":"adgroup_name","operator":"CONTAINS","values":["' . $keyword . '"]}]',
                    'filtering'  => json_encode([
                        [
                            'field'    => 'adgroup_name',
                            'operator' => 'CONTAINS',
                            'values'   => [$keyword],
                        ],
                    ]),
                    'page'       => $page,
                    'page_size'  => $count,
                ];
            } else {
                $params['data'] = [
                    'account_id' => $this->gdtAccountId,
                    'filtering'  => '[{"field":"campaign_id","operator":"EQUALS","values":["' . $fatherId . '"]}]',
                    'page'       => $page,
                    'page_size'  => $count,
                ];
            }
            $ret       = $this->sendToGdt("https://api.e.qq.com/v1.0/adgroups/get", $params);
            $gdtApiRet = @json_decode($ret['data'], true);
            if ($gdtApiRet['code'] != 0) {
                throw new \Exception('gdt api ret error', $gdtApiRet['code']);
            }
            if (!empty($gdtApiRet['data'])) {
                $aids = [];
                foreach ($gdtApiRet['data']['list'] as $row) {
                    $retArray[] = [
                        'aid'          => $row['campaign_id'] . '_' . $row['adgroup_id'] . '_' . $row['adgroup_name'],
                        'name'         => $row['adgroup_name'],
                        'indexId'      => $row['adgroup_id'],
                        'extendField1' => '',
                        'extendField2' => '',
                        'extendField3' => '',
                        'extendField4' => '',
                    ];
                    $aids[]     = Campaign\ActivityType::TYPE_GDT . '_' . $row['adgroup_id'];
                }
                $total = $gdtApiRet['data']['page_info']['total_number'];
                $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
                $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_GDT, $aids);
                if (!empty($retArray)) {
                    foreach ($retArray as &$row) {
                        if (isset($relatedCampaigns[$row['indexId']])) {
                            $row['extendField3'] = $relatedCampaigns[$row['indexId']]['name'];
                        }
                    }
                }
            }
            \QdLogService::logDebug("retArray: " . print_r(json_encode($retArray), true));
        } elseif ($listLevel == 1) { //拉取广点通推广计划列表
            $params['method'] = 'get';
            $params['data']   = [
                'account_id' => $this->gdtAccountId,
                'page'       => $page,
                'page_size'  => $count,
            ];
            \QdLogService::logInfo('send to gdt begin', $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            $ret       = $this->sendToGdt("https://api.e.qq.com/v1.0/campaigns/get", $params);
            $gdtApiRet = @json_decode($ret['data'], true);
            if ($gdtApiRet['code'] != 0) {
                throw new \Exception('gdt api ret error', $gdtApiRet['code']);
            }
            if (!empty($gdtApiRet['data'])) {
                foreach ($gdtApiRet['data']['list'] as $row) {
                    $retArray[] = [
                        'aid'          => $row['campaign_id'],
                        'name'         => $row['campaign_name'],
                        'indexId'      => $row['campaign_id'],
                        'extendField1' => '',
                        'extendField2' => '',
                        'extendField3' => '',
                        'extendField4' => '',
                    ];
                    //$aids[]     = Campaign\ActivityType::TYPE_GDT . '_' . $row['campaign_id'];
                }
                $total = $gdtApiRet['data']['page_info']['total_number'];
            }
            \QdLogService::logDebug("retArray: " . print_r(json_encode($retArray), true));
        }

        return ['total' => $total, 'records' => $retArray];
    }

    public function getAdGroupsByFatherIds($fatherIds, $limit = 100)
    {
        \QdLogService::logInfo("fatherIds:" . print_r($fatherIds, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $idsArray = [];
        $this->uniqueFatherIds($fatherIds, $idsArray);
        $total = 0;
        $retArray = [];
        $paramsArray =[];
        foreach ($idsArray as $item) {
            if ($item['level'] == 1) { //广告计划取下面广告主
                $params['method'] = 'get';
                $params['data']   = [
                    'account_id' => $this->gdtAccountId,
                    'filtering'  => '[{"field":"campaign_id","operator":"EQUALS","values":["' . $item['id'] . '"]}]',
                ];
                $paramsArray[] = $params;
                //$gdtApiRets[]  = $this->getAdGroups($params);
            } else { //广告主直接返回
                $retArray[] = [
                    'aid'          => $item['ids'][1],
                    'name'         => $item['ids'][2],
                    'indexId'      => $item['ids'][1],
                    'extendField1' => $item['ids'][0],
                    'extendField2' => '',
                    'extendField3' => '',
                    'extendField4' => '',
                ];
                $total++;
            }
        }
        $coroTask = new CoroutineTaskManager(new ActivityGdtModel($this->kfuin, $this->kfext), 'getAdGroups', $paramsArray, $this->kfuin, $this->kfext);
        $gdtApiRets = $coroTask->exec();

        if(!empty($gdtApiRets)) {
            foreach ($gdtApiRets as $gdtApiRet) {
                $gdtApiRet        = @json_decode($gdtApiRet['data'], true);
                if ($gdtApiRet['code'] != 0) {
                    throw new \Exception('gdt api ret error', $gdtApiRet['code']);
                }
                if (!empty($gdtApiRet['data'])) {
                    foreach ($gdtApiRet['data']['list'] as $row) {
                        $retArray[] = [
                            'aid'          => $row['adgroup_id'],
                            'name'         => $row['adgroup_name'],
                            'indexId'      => $row['adgroup_id'],
                            'extendField1' => $row['campaign_id'],
                            'extendField2' => '',
                            'extendField3' => '',
                            'extendField4' => '',
                        ];
                    }
                    $total += $gdtApiRet['data']['page_info']['total_number'];
                }
            }
        }
        \QdLogService::logDebug("retArray:" . print_r(json_encode($retArray), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return ['total' => $total, 'records' => $retArray];
    }

    private function uniqueFatherIds($fatherIds, &$idsArray)
    {
        if (empty($fatherIds)) {
            return [];
        }
        //去重
        foreach ($fatherIds as $fatherId) {
            $ids        = explode('_', $fatherId);
            $idsArray[] = [
                'id'    => $fatherId,
                'ids'   => $ids,
                'level' => count($ids),
            ];
        }
        usort($idsArray, function($a, $b) {
            return $a['level'] > $b['level'];
        });
        $exists = [];
        foreach ($idsArray as $k => $row) {
            $prefix = $row['ids'][0];
            if ($exists[$prefix]) {
                unset($idsArray[$k]);
                continue;
            }
            for ($i = 1; $i < $row['level']; $i++) {
                $prefix .= "_{$row['ids'][$i]}";
                if ($exists[$prefix]) {
                    unset($idsArray[$k]);
                    break;
                }
            }
            $exists[$prefix] = 1;
        }
    }

    public function getItemsByIds($fids, $rowsInfo)
    {
        $retArray = [];
        if (!empty($fids)) {
            $index = 0;
            foreach ($fids as $fid) {
                $retArray[] = [
                    'type'         => Campaign\ActivityType::TYPE_GDT,
                    'aid'          => $fid,
                    'name'         => $rowsInfo[$index]['FName'],
                    'indexId'      => $rowsInfo[$index]['FRelatedFirstLevel'] . '_' .$rowsInfo[$index]['FIndexId'],
                    'extendField1' => $rowsInfo[$index]['FRelatedFirstLevel'],
                    'adgroupid'    => $rowsInfo[$index]['FIndexId'],
                ];
                $index++;
            }
        }
        return $retArray;
    }

    public function getDataByAdGroupId($adGroupIds, $startDate, $endDate)
    {
        //只能查一年内的数据
        $now = time();
        if ($now - $endDate > 365 * 86400) {
            return array();
        }
        if ($now - $startDate > 365 * 86400) {
            $startDate = $now - 365 * 86400;
        }

        $accoutInfo     = $this->getBindGdtAccount();
        $params         = array('method' => 'get');
        $params['data'] = [
            'account_id' => $accoutInfo->FGdtAccountId,
            'level'      => 'ADGROUP',
            'date_range' => json_encode([
                'start_date' => date('Y-m-d', intval($startDate)),
                'end_date'   => date('Y-m-d', intval($endDate)),
            ]),
            'filtering'  => json_encode([
                [
                    'field'    => 'adgroup_id',
                    'operator' => 'IN',
                    'values'   => $adGroupIds,
                ],
            ]),
            'group_by'   => json_encode(['adgroup_id']),
            'page'       => 1,
            'page_size'  => 100,
        ];
        $ret            = $this->sendToGdt("https://api.e.qq.com/v1.0/daily_reports/get", $params);
        \QdLogService::logDebug("get gdt data:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);

        $data = json_decode($ret['data'], true)['data']['list'];

        $ret = array();
        foreach ($data as $activity) {
            $ret[$activity['adgroup_id']] = $activity;
        }

        return $ret;
    }


    public function getAdGroups($params) {
        return $this->sendToGdt("https://api.e.qq.com/v1.0/adgroups/get", $params);
    }
}