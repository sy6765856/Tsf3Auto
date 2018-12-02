<?php
/**
 * Created by PhpStorm.
 * User: jianzjzhang
 * Date: 2018/5/29
 * Time: 18:00
 */

namespace campaign_mix_svr\Mix\Model;

use campaign_mix_svr\Mix\Base\CampaignError;
use Qidian\Web\Marketing\Campaign\ActivityType;

class ReportModel
{
    private $kfuin;
    private $kfext;
    const ALL_TYPE          = 0;
    const CACHE_EXPIRE_TIME = 300;
    private $ckv;
    private $druidClient;
    //每一项为['fieldName', 'name', 'aggregation type']
    const METRICS = [
        ActivityType::TYPE_AD              => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'hyperUnique'],
            ['vv_count', 'vv', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
        ],  //广告跟踪
        ActivityType::TYPE_KEYWORDS        => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'hyperUnique'],
            ['vv_count', 'vv', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
            ['impression', 'exposure_cnt', 'longSum'],
            ['click_cnt', 'linked_cnt', 'longSum'],
            ['cost', 'cost', 'doubleSum'],
        ],  //关键词
        ActivityType::TYPE_MASS_SMS        => [
            ['send_count', 'send_cnt', 'longSum'],
            ['delivery_count', 'received_cnt', 'hyperUnique'],
            ['has_url_send_count', 'sms_with_link_cnt', 'longSum'],
            ['has_url_delivery_count', 'sms_with_link_received_cnt', 'hyperUnique'],
            ['click_count', 'sms_linked_cnt', 'longSum'],
            ['click_sms_count', 'sms_linked_visited_cnt', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
            ['cost', 'cost', 'doubleSum'],
        ],  //短信任务
        ActivityType::TYPE_MP_ACCOUNT_MASS => [
            ['target_user', 'sendCount', 'longSum'],
            ['int_page_read_count', 'readCount', 'longSum'],
            ['int_page_read_user', 'readUserCount', 'longSum'],
            ['ori_page_read_count', 'oriReadCount', 'longSum'],
            ['ori_page_read_user', 'oriReadUserCount', 'longSum'],
            ['share_cnt', 'shareCount', 'longSum'],
            ['share_user', 'shareUserCount', 'longSum'],
        ],    //微信公众号
        ActivityType::TYPE_SPONSORED_LINK  => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'hyperUnique'],
            ['vv_count', 'vv', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
        ],  //推广链接
        ActivityType::TYPE_QRCODE          => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'hyperUnique'],
            ['vv_count', 'vv', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
        ],  //二维码
        ActivityType::TYPE_WPA             => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'hyperUnique'],
            ['vv_count', 'vv', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
        ],  //接待组件
        ActivityType::TYPE_WX_H5           => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'longSum'],
            ['share_pv_count', 'share_times_cnt', 'longSum'],
            ['share_uv_count', 'share_cnt', 'longSum'],
            ['timeline_pv_count', 'share_friends_times_cnt', 'longSum'],
            ['timeline_uv_count', 'share_friends_cnt', 'longSum'],
            ['friend_pv_count', 'share_moment_time_cnt', 'longSum'],
            ['friend_uv_count', 'share_moment_cnt', 'longSum'],
            ['max_level', 'share_layer_cnt', 'longMax'],
        ],  //微信H5任务
        ActivityType::TYPE_GDT             => [
            ['pv_count', 'pv', 'longSum'],
            ['uv_count', 'uv', 'hyperUnique'],
            ['vv_count', 'vv', 'hyperUnique'],
            ['session_cus_cnt', 'sessionCustomerCount', 'hyperUnique'],
            ['callsession_cus_cnt', 'callCustomerCount', 'hyperUnique'],
            ['new_cus_cnt', 'newCustomerNum', 'hyperUnique'],
        ]   //广点通广告
    ];

    public function __construct($kfuin, $kfext = 0)
    {
        $this->kfuin = $kfuin;
        $this->kfext = $kfext;

        $ckvConfig = \CampaignConst::getCampaignCKVConf();
        $this->ckv = new \WebCkv($ckvConfig['bid'], null, true, $ckvConfig['l5Conf'], true);

        //config Druid
        $this->druidClient = new \DruidClient();
        $routeconf         = \CRMConst::getDruidServiceConfig();
        $this->druidClient->configIpPort($routeconf['ip'], $routeconf['port']);
    }

    public function updateCache($campaignId)
    {
        $summary_key = 'campaign_getAllChannelSummary_return_cache_' . $this->kfuin . '_' . $campaignId;

        $summary_value = $this->getAllChannelSummary($campaignId);
        $this->ckv->set($summary_key, json_encode($summary_value), -1, self::CACHE_EXPIRE_TIME);

        $campaign   = new CampaignModel($this->kfuin);
        $statistics = array(
            'visitNum'       => $summary_value['records'][0]['visitNum']['total'],
            'newCustomerNum' => $summary_value['records'][0]['newCustomerNum']['total'],
            'cost'           => $summary_value['records'][0]['cost']['total'],
        );
        try {
            $campaign->modifyStatistics($campaignId, $statistics);
        }
        catch (\Exception $e) {
            \QdLogService::logError("modifyStatistics failed, campaignId:{$campaignId}, error: " . \Util::array2Str($e->getMessage()), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
        }

        foreach ($summary_value['records'] as $type => $data) {
            if ($type === 0) {
                continue;
            }
            $channel_key   = 'campaign_getChannelDetail_return_cache_' . $this->kfuin . '_' . $campaignId . '_' . $type;
            $channel_value = $this->getChannelDetail($campaignId, $type, '', 0, 15, true, 0);
            $this->ckv->set($channel_key, json_encode($channel_value), -1, self::CACHE_EXPIRE_TIME);
        }
        return true;
    }

    /**
     * @param $campaignIds (iterator or array)
     * @return
     *                     [
     *                     campaignid1 => campaignid1_data
     *                     campaignid2 => campaignid2_data
     *                     ...
     *                     ]
     */
    public function getCampaignsSummary($campaignIds, $baseInfoes = [])
    {
        $ret = array();
        \QdLogService::logDebug("start_time: ".time(), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
        foreach ($campaignIds as $campaignId) {
            try {
                if (isset($baseInfoes[$campaignId])) {
                    $campaignData = $this->getAllChannelSummary($campaignId, $baseInfoes[$campaignId]);
                } else {
                    $campaignData = $this->getAllChannelSummary($campaignId);
                }
            } catch (\Exception $e) {
                \QdLogService::logError("getCampaignsSummary failed, campaignId:{$campaignId}, error: " . \Util::array2Str($e->getMessage()), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
                $campaignData = [];
            }
            $ret[$campaignId] = $campaignData;
        }
        \QdLogService::logDebug("end_time: ".time(), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
        return $ret;
    }

    public function getAllChannelSummaryFromCache($campaignId)
    {
        $summary_key  = 'campaign_getAllChannelSummary_return_cache_' . $this->kfuin . '_' . $campaignId;
        $summary_data = $this->queryCacheForData($summary_key);
        if (!empty($summary_data)) {
            \QdLogService::logDebug("getAllChannelSummary cache hit, campaignId:{$campaignId}, cache data: " . print_r($summary_data, true), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
            return $summary_data;
        } else {
            $this->updateCache($campaignId);
        }
        $summary_data = $this->queryCacheForData($summary_key);
        if (empty($summary_data)) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::NO_ACTIVITY_RELATED), CampaignError::NO_ACTIVITY_RELATED);
        }
        return $summary_data;
    }

    /**
     * @param $campaignId
     * @param $baseInfo
     * @param $updateActivityType
     */
    public function getAllChannelSummary($campaignId, $baseInfo = '')
    {
        $campaign = new CampaignModel($this->kfuin);
        if (empty($baseInfo)) {
            $baseInfo = $campaign->getBaseInfo($campaignId);
        }

        $activityList = array();
        foreach (self::METRICS as $type => $metric) {
            $activityList[$type] = $campaign->getRelatedActivities($campaignId, $type);
            //如果$type是8，则要把电话WPA和会话WPA的活动id都取出来
            if ($type === ActivityType::TYPE_WPA) {
                $activityList[$type] = array_merge($activityList[$type], $campaign->getRelatedActivities($campaignId, ActivityType::TYPE_CC_WPA));
            }
            //如果$type是11，则要把广点通的点击、花费、成本拿到拼到活动信息中
            if ($type === ActivityType::TYPE_GDT && !empty($activityList[$type])) {
                $this->addGDTData($activityList[$type], $baseInfo['FBeginTime'], $baseInfo['FEndTime']);
            }
        }

        \QdLogService::logDebug("get all channel summary activityList: " . \Util::array2Str($activityList), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);

        //campaign 汇总数据初始化
        $summary = array(
            'type'            => 0,
            'visitNum'        => array('total' => 0),
            'newCustomerNum'  => array('total' => 0),
            'cost'            => array('total' => 0),
            'costPerCustomer' => array('total' => -1),
            'beginTime'       => (intval($baseInfo['FBeginTime']) === 0) ? 0 : date('Y-m-d', intval($baseInfo['FBeginTime'])),
            'endTime'         => (intval($baseInfo['FEndTime']) === 0) ? 0 : date('Y-m-d', intval($baseInfo['FEndTime'])),
            'lastEditTime'    => $baseInfo['FLastUpdateTime'],
            'lastModifier'    => $baseInfo['FLastModifier'],
            'name'            => $baseInfo['FName'],
            'description'     => $baseInfo['FDescription'],
        );

        $ret           = array();
        $all_index_ids = array();
        foreach ($activityList as $type => $activities) {
            $all_index_ids = array_merge($all_index_ids, array_column($activities, 'indexId'));
        }
        if (empty($all_index_ids)) {
            $ret[0] = $summary;
            return array(
                'code'    => CampaignError::NO_ACTIVITY_RELATED,
                'msg'     => CampaignError::getErrorMessage(CampaignError::NO_ACTIVITY_RELATED),
                'records' => $ret,
            );
        }

        //对每个有活动的通路请求一次通路的汇总数据
        foreach ($activityList as $type => $activities) {
            if (empty($activities)) {
                continue;
            }
            $activityIds = array_column($activities, 'indexId');

            $data   = $this->queryDruidForChannelSummary($type, $activityIds, $baseInfo['FBeginTime'], $baseInfo['FEndTime']);
            $tmpRow = array();
            //如果没有数据，补0处理
            if (!isset($data[0]) || !isset($data[0]['event'])) {
                foreach (array_column(self::METRICS[$type], 1) as $metric) {
                    $tmpRow[$metric] = 0;
                }
            } else {
                $tmpRow = $data[0]['event'];
            }
            $tmpRow['type'] = $type;

            //如果druid中没有成本，计算成本汇总
            if (!isset($tmpRow['cost'])) {
                $tmpRow['cost'] = array_sum(array_column($activities, 'cost'));
            }
            //短信要算一下链接点击率
            if ($type === ActivityType::TYPE_MASS_SMS) {
                $tmpRow['sms_linked_rate'] = (intval($tmpRow['received_cnt']) === 0) ? '-' : $tmpRow['sms_linked_visited_cnt'] / $tmpRow['received_cnt'];
            }
            //广点通还要加下点击和曝光
            if ($type === ActivityType::TYPE_GDT) {
                $tmpRow['linked_cnt']   = array_sum(array_column($activities, 'linked_cnt'));
                $tmpRow['exposure_cnt'] = array_sum(array_column($activities, 'exposure_cnt'));
            }

            //计算单客成本
            $tmpRow['costPerCustomer'] = (intval($tmpRow['newCustomerNum']) === 0) ? -1 : $tmpRow['cost'] / $tmpRow['newCustomerNum'];
            $ret[$type]                = $tmpRow;
        }

        $ret[0] = $this->calCampaignSummaryForRet($ret, $summary, $all_index_ids, $baseInfo['FBeginTime'], $baseInfo['FEndTime']);
        return array('code' => CampaignError::SUCCESS, 'msg' => '', 'records' => $ret);
    }

    public function getChannelDetailFromCache($campaignId, $activityType)
    {
        $channel_key  = 'campaign_getChannelDetail_return_cache_' . $this->kfuin . '_' . $campaignId . '_' . $activityType;
        $channel_data = $this->queryCacheForData($channel_key);
        if (!empty($channel_data)) {
            \QdLogService::logDebug("getChannelDetail cache hit, campaignId:{$campaignId}, activityType: {$activityType}, cache data: " . print_r($channel_data, true), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
            return $channel_data;
        } else {
            $this->updateCache($campaignId);
        }
        $channel_data = $this->queryCacheForData($channel_key);
        if (empty($channel_data)) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::NO_ACTIVITY_RELATED), CampaignError::NO_ACTIVITY_RELATED);
        }
        return $channel_data;
    }

    /**
     * @param $campaignId
     * @param $activityType
     * @param $orderby
     * @param $offset
     * @param $limit
     * @param $desc
     * @param $channel
     */
    public function getChannelDetail($campaignId, $activityType, $orderby, $offset = 0, $limit = 15, $desc = true,
        $channel = -1
    ) {
        if (!isset($orderby) || empty($orderby)) {
            $orderby = self::METRICS[$activityType][0][1];
        }
        if (!in_array($orderby, array_column(self::METRICS[$activityType], 1))) {
            return array(
                'code' => CampaignError::INVALID_SORT_LABEL,
                'msg'  => CampaignError::getErrorMessage(CampaignError::INVALID_SORT_LABEL),
            );
        }
        $campaign = new CampaignModel($this->kfuin);
        $baseInfo = $campaign->getBaseInfo($campaignId);

        //搜索关键词要区分来源是百度或搜狗或全部、用$channel判断
        if ($activityType === ActivityType::TYPE_KEYWORDS) {
            $activityList = $campaign->getRelatedActivities($campaignId, $activityType, $channel);
        } else {
            $activityList = $campaign->getRelatedActivities($campaignId, $activityType);
        }

        //如果$activityType是8，则要把电话WPA和会话WPA的活动id都取出来
        if ($activityType === ActivityType::TYPE_WPA) {
            $activityList = array_merge($activityList, $campaign->getRelatedActivities($campaignId, ActivityType::TYPE_CC_WPA));
        }

        //如果$activityType是11，则要把广点通的点击、花费、成本拿到拼到活动信息中
        if ($activityType === ActivityType::TYPE_GDT && !empty($activityList)) {
            $this->addGDTData($activityList, $baseInfo['FBeginTime'], $baseInfo['FEndTime']);
        }

        \QdLogService::logDebug("get channel detail activityList: " . \Util::array2Str($activityList), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);

        $activityIds = array_column($activityList, 'indexId');
        if (empty($activityIds)) {
            return array(
                'code' => CampaignError::NO_ACTIVITY_RELATED,
                'msg'  => CampaignError::getErrorMessage(CampaignError::NO_ACTIVITY_RELATED),
            );
        }

        $rawRet = $this->queryDruidForChannelDetail($activityType, $activityIds, $limit, $offset, $orderby, $desc, $baseInfo['FBeginTime'], $baseInfo['FEndTime']);
        //druid查询数据与活动信息拼接
        $ret          = array('code' => CampaignError::SUCCESS, 'msg' => '', 'records' => $rawRet);
        $ret['total'] = count($activityIds);

        //以indexId为key建立哈希表
        $activityInfo = array();
        foreach ($activityList as $activity) {
            $indexId = $activity['indexId'];
            //活动列表中可能有相同的indexId，那么这些活动的数据相同，但是要展示多个
            if (!isset($activityInfo[$indexId])) {
                $activityInfo[$indexId] = array();
            }
            $activityInfo[$indexId][] = $activity;
        }

        $dataAvailable    = array();
        $dataNotAvailable = array();
        $parsedIndexIds   = array();

        //先处理有数据的活动，从druid中查出来的数据是排序好的
        foreach ($ret['records'] as $data) {
            $data = $data['event'];
            foreach ($activityInfo[$data['index_id']] as $activity) {
                $tmp = $this->addBaseInfo($data, $activity, $activityType);
                unset($tmp['index_id']);
                unset($tmp['utm_type']);
                $dataAvailable[] = $tmp;
            }
            $parsedIndexIds[] = $data['index_id'];
        }

        //对没有数据的活动进行补-1处理，前端显示'-'
        foreach ($activityInfo as $indexId => $activities) {
            if (!in_array($indexId, $parsedIndexIds)) {
                foreach ($activities as $activity) {
                    $data = array();
                    foreach (array_column(self::METRICS[$activityType], 1) as $metric) {
                        $data[$metric] = -1;
                    }
                    $data               = $this->addBaseInfo($data, $activity, $activityType);
                    $dataNotAvailable[] = $data;
                }
            }
        }

        $postprocessed = array_merge($dataAvailable, $dataNotAvailable);

        $ret['records'] = array_slice($postprocessed, $offset, $limit);
        return $ret;
    }

    /**
     * 从druid或ckv读取一个通路的汇总信息
     *
     * @param $campaignId
     * @param $activityType
     * @param $updateActivityType
     * @param $activityIds
     * @param $beginTime
     * @param $endTime
     * @return mixed|string
     */
    private function autoGetChannelSummary($campaignId, $activityType, $updateActivityType, $activityIds, $beginTime,
        $endTime
    ) {
        $key = 'campaign_channel_summary_data_cache_' . $this->kfuin . '_' . $campaignId . '_' . $activityType;
        //如果这个类型需要刷新，读druid数据，并写入缓存
        if ($updateActivityType === self::ALL_TYPE || $activityType === $updateActivityType) {
            $data = $this->queryDruidForChannelSummary($activityType, $activityIds, $beginTime, $endTime);
            $this->ckv->set($key, json_encode($data), -1, self::CACHE_EXPIRE_TIME);
        } //否则读缓存数据
        else {
            $data = $this->queryCacheForData($key);
            \QdLogService::logDebug("channel summary data (type: {$activityType}) from ckv: " . \Util::array2Str($data), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
            //如果读取缓存失败，读druid数据
            if (empty($data)) {
                $data = $this->queryDruidForChannelSummary($activityType, $activityIds, $beginTime, $endTime);
                $this->ckv->set($key, json_encode($data), -1, self::CACHE_EXPIRE_TIME);
            }
        }
        return $data;
    }

    /**
     * 从druid读取某个通路的汇总信息
     *
     * @param $activityType
     * @param $activityIds
     * @param $beginTime
     * @param $endTime
     * @return mixed
     */
    private function queryDruidForChannelSummary($activityType, $activityIds, $beginTime, $endTime)
    {
        //druid 请求 json
        $query = array(
            'queryType'    => 'groupBy',
            'dataSource'   => ['type' => 'table', 'name' => $this->dataSource($activityType)],
            'granularity'  => 'all',
            'dimensions'   => ['kfuin'],
            'filter'       => [
                'type'   => 'and',
                'fields' => [
                    //电话会话客户数和会话客户数在一起，如果查询activityType=8，则要把activityType是8和9的都查出来
                    [
                        'type'      => 'in',
                        'dimension' => 'utm_type',
                        'values'    => ($activityType === ActivityType::TYPE_WPA) ? [
                            ActivityType::TYPE_WPA,
                            ActivityType::TYPE_CC_WPA,
                        ] : [$activityType],
                    ],
                    ['type' => 'in', 'dimension' => 'index_id', 'values' => $activityIds],
                    ['type' => 'selector', 'dimension' => 'kfuin', 'value' => $this->kfuin],
                ],
            ],
            'aggregations' => $this->Aggregations($activityType),
            'intervals'    => ['type' => "intervals", "intervals" => []],
        );

        $query = $this->addDateFilter($query, $activityType, $beginTime, $endTime);
        //queryDruid方法中已经做了抛出异常处理
        return $this->druidClient->queryDruid($query);
    }

    private function queryCacheForData($key)
    {
        $ret = $this->ckv->get($key);
        if ($ret['ret'] != 0) {
            \QdLogService::logError("read campaign summary data from cache fail, key:{$key}.", 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
            return '';
        }
        return json_decode($ret['data'], true);
    }

    /**
     * 从druid或ckv读取一个campaign的汇总信息
     *
     * @param $needUpdate
     * @param $campaignId
     * @param $channel_summary_data
     * @param $init_array
     * @param $activityIds
     * @param $startTime
     * @param $endTime
     * @return mixed|string
     * @throws \Exception
     */
    private function autoGetCampaignSummary($needUpdate, $campaignId, $channel_summary_data, $init_array, $activityIds,
        $startTime, $endTime
    ) {
        $campaign = new CampaignModel($this->kfuin);
        $key      = 'campaign_summary_data_cache_' . $this->kfuin . '_' . $campaignId;
        if ($needUpdate) {
            $summary    = $this->calCampaignSummaryForRet($channel_summary_data, $init_array, $activityIds, $startTime, $endTime);
            $statistics = array(
                'visitNum'       => $summary['visitNum']['total'],
                'newCustomerNum' => $summary['newCustomerNum']['total'],
                'cost'           => $summary['cost']['total'],
            );
            $campaign->modifyStatistics($campaignId, $statistics);
            $this->ckv->set($key, json_encode($summary), -1, self::CACHE_EXPIRE_TIME);
        } else {
            $summary = $this->queryCacheForData($key);
            \QdLogService::logDebug("campaign summary data from ckv: " . \Util::array2Str($summary), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);
            if (empty($summary)) {
                $summary    = $this->calCampaignSummaryForRet($channel_summary_data, $init_array, $activityIds, $startTime, $endTime);
                $statistics = array(
                    'visitNum'       => $summary['visitNum']['total'],
                    'newCustomerNum' => $summary['newCustomerNum']['total'],
                    'cost'           => $summary['cost']['total'],
                );
                $campaign->modifyStatistics($campaignId, $statistics);
                $this->ckv->set($key, json_encode($summary), -1, self::CACHE_EXPIRE_TIME);
            }
        }
        return $summary;
    }

    /**
     * 得到每个通道的汇总信息后，加和得到campaign的汇总信息
     *
     * @param $channel_summary_data
     * @param $init_array
     * @param $activityIds
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    private function calCampaignSummaryForRet($channel_summary_data, $init_array, $activityIds, $startTime, $endTime)
    {
        foreach ($channel_summary_data as $type => $data) {
            //微信H5任务没有这些汇总字段
            if (in_array($type, array(0, ActivityType::TYPE_WX_H5))) {
                continue;
            }
            //汇总信息
            if (in_array($type, array(
                ActivityType::TYPE_AD,
                ActivityType::TYPE_KEYWORDS,
                ActivityType::TYPE_SPONSORED_LINK,
                ActivityType::TYPE_QRCODE,
                ActivityType::TYPE_WPA,
                ActivityType::TYPE_GDT,
            ))) {
                $init_array['visitNum'][strval($type)] = $data['vv'];
                $init_array['newCustomerNum'][strval($type)]  = $data['newCustomerNum'];
                $init_array['cost']['total']                  += $data['cost'];
                $init_array['cost'][strval($type)]            = $data['cost'];
                $init_array['costPerCustomer'][strval($type)] = $data['costPerCustomer'];
            } elseif (in_array($type, array(ActivityType::TYPE_MP_ACCOUNT_MASS))) {
                $init_array['visitNum']['total']       += $data['readCount'];
                $init_array['visitNum'][strval($type)] = $data['readCount'];
            } elseif (in_array($type, array(ActivityType::TYPE_MASS_SMS))) {
                $init_array['newCustomerNum']['total']        += $data['newCustomerNum'];
                $init_array['newCustomerNum'][strval($type)]  = $data['newCustomerNum'];
                $init_array['visitNum']['total']              += $data['sms_linked_cnt'];
                $init_array['visitNum'][strval($type)]        = $data['sms_linked_cnt'];
                $init_array['cost']['total']                  += $data['cost'];
                $init_array['cost'][strval($type)]            = $data['cost'];
                $init_array['costPerCustomer'][strval($type)] = $data['costPerCustomer'];
            }
        }

        //广告跟踪、推广链接、二维码、WPA、关键词、广点通的vv要统一去重
        $unique_visitor_and_customer = $this->getUniqueVisitorAndNewCustomer($activityIds, $startTime, $endTime);

        $init_array['visitNum']['total']        += $unique_visitor_and_customer['vv'];
        $init_array['newCustomerNum']['total']  += $unique_visitor_and_customer['newCustomerNum'];
        $init_array['costPerCustomer']['total'] = (intval($init_array['newCustomerNum']['total']) === 0) ? -1 : $init_array['cost']['total'] / $init_array['newCustomerNum']['total'];
        return $init_array;
    }

    /**
     * 从druid读取某个通路的详情数据
     *
     * @param $activityType
     * @param $activityIds
     * @param $limit
     * @param $offset
     * @param $orderby
     * @param $desc
     * @param $beginTime
     * @param $endTime
     */
    private function queryDruidForChannelDetail($activityType, $activityIds, $limit, $offset, $orderby, $desc,
        $beginTime, $endTime
    ) {
        //druid 请求 json
        $query = array(
            'queryType'    => 'groupBy',
            'dataSource'   => ['type' => 'table', 'name' => $this->dataSource($activityType)],
            'granularity'  => 'all',
            'dimensions'   => ['index_id', 'utm_type'],
            'limitSpec'    => [
                'type'    => "default",
                "limit"   => $limit + $offset,
                "columns" => [
                    [
                        "dimension"      => $orderby,
                        "direction"      => $desc ? "descending" : "ascending",
                        "dimensionOrder" => "numeric",
                    ],
                ],
            ],
            'filter'       => [
                'type'   => 'and',
                'fields' => [
                    //电话会话客户数和会话客户数在一起，如果查询activityType=8，则要把activityType是8和9的都查出来
                    [
                        'type'      => 'in',
                        'dimension' => 'utm_type',
                        'values'    => ($activityType === ActivityType::TYPE_WPA) ? [
                            ActivityType::TYPE_WPA,
                            ActivityType::TYPE_CC_WPA,
                        ] : [$activityType],
                    ],
                    ['type' => 'in', 'dimension' => 'index_id', 'values' => $activityIds],
                    ['type' => 'selector', 'dimension' => 'kfuin', 'value' => $this->kfuin],
                ],
            ],
            'aggregations' => $this->Aggregations($activityType),
            'intervals'    => ['type' => "intervals", "intervals" => []],
        );

        $query = $this->addDateFilter($query, $activityType, $beginTime, $endTime);
        //queryDruid方法中已经做了抛出异常处理
        return $this->druidClient->queryDruid($query);
    }

    /**
     * 不同通路上的visitor_id和new_customer_id需要去重
     *
     * @param $activityIds
     * @param $beginTime
     * @param $endTime
     * @return mixed
     */
    private function getUniqueVisitorAndNewCustomer($activityIds, $beginTime, $endTime)
    {
        $dataSource = $this->dataSource(ActivityType::TYPE_AD);
        $query      = array(
            'queryType'    => 'groupBy',
            'dataSource'   => ['type' => 'table', 'name' => $dataSource],
            'granularity'  => 'all',
            'dimensions'   => ['kfuin'],
            'filter'       => [
                'type'   => 'and',
                'fields' => [
                    ['type' => 'in', 'dimension' => 'index_id', 'values' => $activityIds],
                    ['type' => 'selector', 'dimension' => 'kfuin', 'value' => $this->kfuin],
                ],
            ],
            'aggregations' => [
                ['type' => 'hyperUnique', 'name' => 'vv', 'fieldName' => 'vv_count', 'round' => true],
                ['type' => 'hyperUnique', 'name' => 'newCustomerNum', 'fieldName' => 'new_cus_cnt', 'round' => true],
            ],
            'intervals'    => ['type'      => "intervals",
                               "intervals" => [date(DATE_ISO8601, intval($beginTime)) . '/' . date(DATE_ISO8601, intval($endTime))],
            ],
        );

        $rawRet = $this->druidClient->queryDruid($query);
        return $rawRet[0]['event'];
    }

    /**
     * druid请求添加日期筛选条件
     *
     * @param $query
     * @param $activityType
     * @param $beginTime
     * @param $endTime
     * @return mixed
     */
    private function addDateFilter($query, $activityType, $beginTime, $endTime)
    {
        //微信公众号、关键词、微信H5任务的时间筛选要按ref_date, __time前后加一个5天的时间窗，防止数据同步有延迟
        if (in_array($activityType, array(
            ActivityType::TYPE_MP_ACCOUNT_MASS,
            ActivityType::TYPE_WX_H5,
            ActivityType::TYPE_KEYWORDS,
        ))) {
            $query['filter']['fields'][]     = [
                'type'      => 'bound',
                'dimension' => 'ref_date',
                'lower'     => date('Y-m-d', intval($beginTime)),
                'upper'     => date('Y-m-d', intval($endTime)),
            ];
            $query['intervals']['intervals'] = [
                date(DATE_ISO8601, intval($beginTime) - 5 * 86400) . '/' . date(DATE_ISO8601, intval($endTime) + 5 * 86400),
            ];
        } else {
            $query['intervals']['intervals'] = [
                date(DATE_ISO8601, intval($beginTime)) . '/' . date(DATE_ISO8601, intval($endTime)),
            ];
        }
        return $query;
    }

    /**
     * 给data添加'activityName'、'cost'、'costPerCustomer'、'detailUrl'、'imgUrl'并返回data
     */
    private function addBaseInfo($data, $baseInfo, $activityType)
    {
        $data['activityid']   = $this->parseIndexId($baseInfo['aid']);
        $data['activityName'] = $baseInfo['name'];
        if ($activityType === ActivityType::TYPE_MP_ACCOUNT_MASS) {
            $ContentData    = json_decode($baseInfo['extendField2'], true);
            $data['imgUrl'] = $ContentData['message']['thumb_url'];
        }
        if ($activityType === ActivityType::TYPE_MASS_SMS) {
            $data['sms_linked_rate'] = (intval($data['received_cnt']) > 0) ? $data['sms_linked_visited_cnt'] / $data['received_cnt'] : '-';
        }
        if ($activityType === ActivityType::TYPE_GDT) {
            $data['cost']         = doubleval($baseInfo['cost']);
            $data['linked_cnt']   = $baseInfo['linked_cnt'];
            $data['exposure_cnt'] = $baseInfo['exposure_cnt'];
        }
        if (!in_array($activityType, array(
            ActivityType::TYPE_KEYWORDS,
            ActivityType::TYPE_GDT,
            ActivityType::TYPE_MASS_SMS,
        ))) {
            $data['cost'] = doubleval($baseInfo['cost']);
        }
        $data['costPerCustomer'] = (intval($data['newCustomerNum']) > 0) ? $data['cost'] / $data['newCustomerNum'] : -1;
        $data['detailUrl']       = $this->detailUrl($activityType, $baseInfo);

        return $data;
    }

    /**
     * 广点通点击、曝光、花费数据需要从广点通接口获得
     *
     * @param $activityList
     * @param $startDate
     * @param $endDate
     */
    private function addGDTData(&$activityList, $startDate, $endDate)
    {
        $gdtModel = new ActivityGdtModel($this->kfuin, $this->kfext);
        $gdtData  = $gdtModel->getDataByAdGroupId(array_column($activityList, 'adgroupid'), $startDate, $endDate);
        \QdLogService::logDebug("gdt data: " . \Util::array2Str($gdtData), 0, 0, 0, __CLASS__, __LINE__, __METHOD__);

        foreach ($activityList as &$activity) {
            if (isset($gdtData[$activity['adgroupid']])) {
                $activity['linked_cnt']   = $gdtData[$activity['adgroupid']]['click'];
                $activity['exposure_cnt'] = $gdtData[$activity['adgroupid']]['impression'];
                $activity['cost']         = $gdtData[$activity['adgroupid']]['cost'] / 100;
            } else {
                $activity['linked_cnt']   = 0;
                $activity['exposure_cnt'] = 0;
                $activity['cost']         = 0;
            }
        }
        return true;
    }

    /**
     * 获取不同通路的统计指标
     *
     * @param $activityType
     */
    private function Aggregations($activityType)
    {
        $aggs = array();
        foreach (self::METRICS[$activityType] as $arr) {
            $tmpAgg = ['type' => $arr[2], 'name' => $arr[1], 'fieldName' => $arr[0]];
            if ($tmpAgg['type'] === 'hyperUnique') {
                //对hyperUniqe的参数加'round'=>true, 保证输出的值为整数
                $tmpAgg['round'] = true;
            }
            $aggs[] = $tmpAgg;
        }

        return $aggs;
    }

    private function dataSource($activityType = null)
    {
        if (!isset($activityType)) {
            return \CampaignConst::getDruidDataSource();
        } else {
            return \CampaignConst::getDruidDataSource()[$activityType];
        }
    }

    private function detailUrl($activityType, $params)
    {
        switch ($activityType) {
            case ActivityType::TYPE_MP_ACCOUNT_MASS:
                return '/ea/api/tsjump/stat?redirect_params=' . urlencode($params['extendField3']);
            default:
                return '';
        }

        return '';
    }

    private function parseIndexId($indexId)
    {
        $pos = strpos($indexId, '_');
        if ($pos === false) {
            return $indexId;
        } else {
            return substr($indexId, $pos + 1);
        }
    }
}