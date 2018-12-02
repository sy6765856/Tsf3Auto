<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/5/30
 */

namespace campaign_mix_svr\Mix\Model;

use campaign_mix_svr\Mix\Base\CampaignError;
use Qidian\Web\Marketing\Campaign;

class ActivityModel
{
    private $kfuin;
    private $kfext;
    private $seq;
    public $activityTypeMap = [
        1  => Campaign\ActivityType::TYPE_MASS_SMS,
        2  => Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS,
        3  => Campaign\ActivityType::TYPE_AD,
        4  => Campaign\ActivityType::TYPE_KEYWORDS,
        5  => Campaign\ActivityType::TYPE_SPONSORED_LINK,
        6  => Campaign\ActivityType::TYPE_QRCODE,
        7  => Campaign\ActivityType::TYPE_COUPON,
        8  => Campaign\ActivityType::TYPE_WPA,
        9  => Campaign\ActivityType::TYPE_CC_WPA,
        10 => Campaign\ActivityType::TYPE_WX_H5,
        11 => Campaign\ActivityType::TYPE_GDT,
    ];

    public function __construct($kfuin, $kfext = 0, $seq = '')
    {
        $this->kfuin = $kfuin;
        $this->kfext = $kfext;
        if (empty($seq)) {
            $seq = mt_rand();
        }
        $this->seq = $seq;
    }

    /*
     * 获取活动列表
     * @param $type 活动类型
     * @param $listLevel 需要获取的活动列表层级
     * @param $fatherId 活动列表层级的父id
     * @param $start （分页）开始
     * @param $limit （分页）条数
     * @param $keyword 搜索活动名称
     * @return [
     *  'total' => $total,  //总数
     *  'records' => [
     *       [
     *               'aid'=>111, //活动id
     *               'name'=>'xxx',  //活动名称
     *               'extendField1' => '',
     *               'extendField2' => '',
     *               'extendField3' => '',
     *       ],
     *  ]]
    */
    public function getActivityList($type, $listLevel, $fatherId, $start, $count, $keyword = '')
    {
        \QdLogService::logInfo("getActivityList type: {$type}, listLevel:{$listLevel}, fatherId:{$fatherId}, start:{$start}, count:{$count}, keyword:{$keyword}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        switch ($type) {
            case Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS: //公众号群发
                $activityModel = new ActivityWxMassModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getMpAccountMassList($listLevel, $fatherId, $start, $count, $keyword);
            case Campaign\ActivityType::TYPE_WPA: //服务接待组件
                $activityModel = new ActivityWPAModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_CC_WPA: //电话接待组件
                $activityModel = new ActivityCCWPAModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_AD:  //广告跟踪
                $activityModel = new ActivityAdModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_KEYWORDS: //关键词
                $activityModel = new ActivityKeywordsModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getKeyWordsList($listLevel, $fatherId, $start, $count, $keyword);
            case Campaign\ActivityType::TYPE_MASS_SMS: //短信群发
                $activityModel = new ActivitySmsModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_SPONSORED_LINK: //推广链接
                $activityModel = new ActivityReferralUrlModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_QRCODE: //二维码
                $activityModel = new ActivityQRCodeModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_WX_H5: //TS H5活动
                $activityModel = new ActivityH5Model($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getList($start, $count, $keyword);
            case Campaign\ActivityType::TYPE_GDT: //广点通
                $activityModel = new ActivityGdtModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getGdtList($listLevel, $fatherId, $start, $count, $keyword);
            default:
                throw new \Exception("wrong activityType: {$type}", CampaignError::WRONG_ACTIVITY_TYPE);
        }
    }

    /*
     * 获取活动关联campaign
     * @param $type 活动类型
     * @params $aids 活动ids
     * @return [aid => [
                'campaignId' => 111, //营销计划id
                'name'       => 'xxxx',  //营销计划名称
                'beginTime'  => 1533830400, //营销开始时间
                'endTime'    => 1535903999, //营销结束时间
                'createTime' => 1533868249, //营销创建时间
            ],]
     */
    public function getRelatedCampaigns($type, $aids)
    {
        if (empty($aids)) {
            return [];
        }
        \QdLogService::logInfo("getRelatedCampaign type:{$type},aids:" . print_r($aids, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $ret = UR_db_marketing_t_campaign_relation::find($this->kfuin, $this->kfext)->where([
            'FRelatedId' => $aids,
            'FKFUin'     => $this->kfuin,
            'FType'      => $type,
        ])->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }

        $campaignIds      = [];
        $relatedCampaigns = [];
        if (empty($ret['data'])) {
            return $relatedCampaigns;
        }
        foreach ($ret['data'] as $row) {
            $campaignIds[] = $row['FCampaignId'];
        }
        $campaignIds = array_unique($campaignIds);
        \QdLogService::logInfo("campaignIds:" . print_r($campaignIds, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $campaignIdMapping = [];
        if (!empty($campaignIds)) {
            $campaignRet = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
                'FId'     => $campaignIds,
                'FKFUin'  => $this->kfuin,
                'FStatus' => 0,
            ])->asArray()->all();
            \QdLogService::logDebug("campaignRet:" . print_r($campaignRet, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            if (!isset($campaignRet['ret']['r']) or $campaignRet['ret']['r'] != 0) {
                \QdLogService::logError("ret:" . print_r($campaignRet, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
                throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
            }
            if (!empty($campaignRet['data'])) {
                foreach ($campaignRet['data'] as $row) {
                    $campaignIdMapping[$row['FId']] = $row;
                }
            }
        }
        foreach ($ret['data'] as $row) {
            $obj                       = explode('_', $row['FRelatedId']);
            $relatedCampaigns[$obj[1]] = [
                'campaignId' => $row['FCampaignId'],
                'name'       => $campaignIdMapping[$row['FCampaignId']]['FName'],
                'beginTime'  => $campaignIdMapping[$row['FCampaignId']]['FBeginTime'],
                'endTime'    => $campaignIdMapping[$row['FCampaignId']]['FEndTime'],
                'createTime' => $campaignIdMapping[$row['FCampaignId']]['FCreateTime'],
            ];
        }
        \QdLogService::logInfo("getRelatedCampaigns out:" . print_r($relatedCampaigns, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $relatedCampaigns;
    }

    /*
     * 获取活动内容
     * @param $type 活动类型
     * @params $activityIds 活动ids
     * @params $rowsInfo t_campaign_relation_x 表字段数组 [[
                    'FIndexId'           => $row['FIndexId'],
                    'FRelatedFirstLevel' => $row['FRelatedFirstLevel'],
                    'FName'              => $row['FName'],
                ],]
     */
    public function getActivities($type, $activityIds, $rowsInfo = [])
    {
        \QdLogService::logInfo("getRelatedActivities type: {$type}, activityIds:" . print_r($activityIds, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        switch ($type) {
            case Campaign\ActivityType::TYPE_AD: //广告跟踪
                $activityModel = new ActivityAdModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS: //公众号群发
                $activityModel = new ActivityWxMassModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_SPONSORED_LINK:  //推广链接
                $activityModel = new ActivityReferralUrlModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_QRCODE: //二维码
                $activityModel = new ActivityQRCodeModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_WPA: //服务接待组件
                $activityModel = new ActivityWPAModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_CC_WPA: //电话接待组件
                $activityModel = new ActivityCCWPAModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_KEYWORDS: //关键词
                $activityModel = new ActivityKeywordsModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds, $rowsInfo);
            case Campaign\ActivityType::TYPE_MASS_SMS: //短信群发
                $activityModel = new ActivitySmsModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_WX_H5: //TS h5活动
                $activityModel = new ActivityH5Model($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds);
            case Campaign\ActivityType::TYPE_GDT: //广点通
                $activityModel = new ActivityGdtModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getItemsByIds($activityIds, $rowsInfo);
            default:
                throw new \Exception("wrong activityType: {$type}", CampaignError::WRONG_ACTIVITY_TYPE);
        }
    }

    public function saveCost($frelatedId, $cost)
    {
        $model = UR_db_marketing_t_campaign_relation::find($this->kfuin, $this->kfext)->where([
            'FRelatedId' => $frelatedId,
            'FKFUin'     => $this->kfuin,
        ])->one();
        if (empty($model->FId)) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::ERROR_RELATED_ID), CampaignError::ERROR_RELATED_ID);
        }
        $model->FCost           = $cost;
        $model->FLastUpdateTime = date('Y-m-d H:i:s');
        if (!$model->save()) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::SAVE_COST_FAILED), CampaignError::SAVE_COST_FAILED);
        }
        return true;
    }

    public function associateActivityCampaign($type, $aid, $campaignId)
    {
        $model = UR_db_marketing_t_campaign_relation::find($this->kfuin, $this->kfext)->where([
            'FRelatedId' => "{$type}_{$aid}",
            'FKFUin'     => $this->kfuin,
        ])->one();
        if (empty($model->FId)) {
            $model              = new UR_db_marketing_t_campaign_relation($this->kfuin, $this->kfext);
            $model->FKFUin      = $this->kfuin;
            $model->FStatus     = 0;
            $model->FType       = $type;
            $model->FRelatedId  = "{$type}_{$aid}";
            $model->FCreateTime = time();
        }
        $model->FCampaignId     = $campaignId;
        $model->FLastUpdateTime = date('Y-m-d H:i:s');
        if (!$model->save()) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::SAVE_ACTIVITY_CAMPAIGN_FAILED), CampaignError::SAVE_ACTIVITY_CAMPAIGN_FAILED);
        }
        return true;
    }

    public function getActivitiesByFatherids($type, $fatherids)
    {
        switch ($type) {
            case Campaign\ActivityType::TYPE_KEYWORDS: //关键词
                $activityModel = new ActivityKeywordsModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getKeywordsByFatherIds($fatherids);
            case Campaign\ActivityType::TYPE_GDT: //广点通
                $activityModel = new ActivityGdtModel($this->kfuin, $this->kfext, $this->seq);
                return $activityModel->getAdGroupsByFatherIds($fatherids);
            default:
                throw new \Exception("unsupported activityType: {$type}", CampaignError::WRONG_ACTIVITY_TYPE);
        }
    }

    /**
     * @param \Qidian_Web_Marketing_Campaign_ActivityUpData[] $activityData
     * @return array
     */
    public function getActivitiesByUpData($activityData)
    {
        $retData = [];
        foreach ($activityData as $data) {
            $srvId = $data->getStringActivityId();
            $type  = $data->getUint32ActitivyType();
            if (isset($this->activityTypeMap[$type])) {
                $type = $this->activityTypeMap[$type];
            } else {
                return ['r' => CampaignError::WRONG_WPA_TYPE, 'msg' => 'type error'];
            }
            $ret = $this->getActivitiesByUpDataType($type, $srvId);
            if ($ret['r'] != CampaignError::SUCCESS) {
                return $ret;
            }
            $mapData = new Campaign\ActivityMap();
            $mapData->setStringActivityId($srvId);
            foreach ($ret['data'] as $actData) {
                $act = new Campaign\Activity();
                $act->setUint32Id($actData['aid']);
                $act->setUint32Type($type);
                $act->setStrName($actData['name']);
                foreach (['extendField1', 'extendField2', 'extendField3', 'extendField4'] as $extend) {
                    if (isset($actData[$extend])) {
                        $func = 'setStr' . ucfirst($extend);
                        $act->$func($actData[$extend]);
                    }
                }
                $mapData->appendActivity($act);
            }
            $retData[] = $mapData;
        }
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param int $type
     * @param string $srvId
     * @return array
     */
    public function getActivitiesByUpDataType($type, $srvId)
    {
        switch ($type) {
            case Campaign\ActivityType::TYPE_MASS_SMS :
                return $this->getActivitiesBySms($srvId);
            case Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS :
                return $this->getActivitiesByWechatMp($srvId);
            case Campaign\ActivityType::TYPE_AD :
                return $this->getActivitiesByAdTrack($srvId);
            case Campaign\ActivityType::TYPE_KEYWORDS :
                return $this->getActivitiesBySemTrack($srvId);
            case Campaign\ActivityType::TYPE_SPONSORED_LINK :
                return $this->getActivitiesByLandingPage($srvId);
            case Campaign\ActivityType::TYPE_QRCODE :
                return $this->getActivitiesByQrcode($srvId);
            case Campaign\ActivityType::TYPE_COUPON :
                return $this->getActivitiesByCoupon($srvId);
            case Campaign\ActivityType::TYPE_WPA :
                return $this->getActivitiesByImWpa($srvId);
            case Campaign\ActivityType::TYPE_CC_WPA :
                return $this->getActivitiesByCcWpa($srvId);
            case Campaign\ActivityType::TYPE_WX_H5:
                return $this->getActivitiesByH5($srvId);
            default :
                return ['r' => CampaignError::WRONG_ACTIVITY_TYPE, 'msg' => 'type not supported'];
        }
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesBySms($srvId)
    {
        $ActivitySmsModel = new ActivitySmsModel($this->kfuin, $this->kfext, $this->seq);
        $retData             = $ActivitySmsModel->getItemsByIds([$srvId]);
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByH5($srvId)
    {
        $ActivityH5Model = new ActivityH5Model($this->kfuin, $this->kfext, $this->seq);
        $retData             = $ActivityH5Model->getItemsByIds([$srvId]);
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByWechatMp($srvId)
    {
        $ActivityWxMassModel = new ActivityWxMassModel($this->kfuin, $this->kfext, $this->seq);
        $retData             = $ActivityWxMassModel->getItemsByIds([$srvId]);
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByAdTrack($srvId)
    {
        $url = urldecode($srvId);
        list($path, $queryStr) = explode('?', $url);
        $paramsArr = explode('&', $queryStr);
        $utmData   = [
            'campaign' => '',
            'source'   => '',
            'medium'   => '',
        ];
        foreach ($paramsArr as $params) {
            list($key, $value) = explode('=', $params);
            switch ($key) {
                case 'utm_campaign' :
                    $utmData['campaign'] = urldecode($value);
                    break;
                case 'utm_source' :
                    $utmData['source'] = urldecode($value);
                    break;
                case 'utm_medium' :
                    $utmData['medium'] = urldecode($value);
                    break;
            }
        }
        $retData = [];
        $ret     = UR_db_base_mp_tbl_ad_track::find($this->kfuin, $this->kfext)->where([
            'FQidianUin' => $this->kfuin,
            'FSource'    => $utmData['source'],
            'FMedium'    => $utmData['medium'],
            'FCampaign'  => $utmData['campaign'],
        ])->fields(['FId'])->all();
        if ($ret['ret']['r'] != CampaignError::SUCCESS) {
            return $ret['ret'];
        }
        if (empty($ret['data'])) {
            $retData[] = [
                'aid'  => 0,
                'name' => $url,
            ];
        } else {
            foreach ($ret['data'] as $row) {
                $retData[] = [
                    'aid'  => intval($row->FId),
                    'name' => $url,
                ];
            }
        }
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesBySemTrack($srvId)
    {
        $ids = explode('_', $srvId, 2);
        $ret = UR_db_marketing_t_campaign_relation::findWithTcp($this->kfuin, $this->kfext)->where([
            'FType' => Campaign\ActivityType::TYPE_KEYWORDS,
            'FRelatedFirstLevel'  => $ids[0],
            'FIndexId' => $ids[1],
            'FKFUin'      => $this->kfuin,
        ])->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_RELATED_ACTIVITY_FAILED), CampaignError::GET_RELATED_ACTIVITY_FAILED);
        }
        $retData = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retData[] = [
                    'aid'  => explode('_', $row['FRelatedId'], 2)[1],
                    'name' => $ids[1],
                ];
            }
        }
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByLandingPage($srvId)
    {
        $url     = urldecode($srvId);
        $retData = [];
        $ret     = UR_db_marketing_t_referral_url::find($this->kfuin, $this->kfext)->where([
            'FKFUin' => $this->kfuin,
            'FUrl'   => $url,
        ])->fields(['FId'])->all();
        if ($ret['ret']['r'] != CampaignError::SUCCESS) {
            return $ret['ret'];
        }
        if (empty($ret['data'])) {
            $retData[] = [
                'aid'  => 0,
                'name' => $url,
            ];
        } else {
            foreach ($ret['data'] as $row) {
                $retData[] = [
                    'aid'  => intval($row->FId),
                    'name' => $url,
                ];
            }
        }
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByQrcode($srvId)
    {
        $url = urldecode($srvId);
        list($path, $queryStr) = explode('?', $url);
        $paramsArr = explode('&', $queryStr);
        $utmData   = [
            'campaign' => '',
            'source'   => '',
            'medium'   => '',
            'name'     => '',
        ];
        foreach ($paramsArr as $params) {
            list($key, $value) = explode('=', $params);
            switch ($key) {
                case 'utm_campaign' :
                    $utmData['campaign'] = urldecode($value);
                    break;
                case 'utm_source' :
                    $utmData['source'] = urldecode($value);
                    break;
                case 'utm_medium' :
                    $utmData['medium'] = urldecode($value);
                    break;
                case 'utm_ad' :
                    $utmData['name'] = urldecode($value);
            }
        }
        $retData = [];
        $ret     = UR_db_base_mp_tbl_qr_list::find($this->kfuin, $this->kfext)->where([
            'FTId'      => $this->kfuin,
            'FName'     => $utmData['name'],
            'FCampaign' => $utmData['campaign'],
            'FSource'   => $utmData['source'],
            'FMedium'   => $utmData['medium'],
            'FUrl'      => $path,
        ])->fields(['FId', 'FName'])->all();
        if ($ret['ret']['r'] != CampaignError::SUCCESS) {
            return $ret['ret'];
        }
        if (empty($ret['data'])) {
            $retData[] = [
                'aid'  => 0,
                'name' => $utmData['name'],
            ];
        } else {
            foreach ($ret['data'] as $row) {
                $retData[] = [
                    'aid'  => intval($row->FId),
                    'name' => $row->FName,
                ];
            }
        }
        return ['r' => CampaignError::SUCCESS, 'data' => $retData];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByCoupon($srvId)
    {
        // TODO
        return ['r' => CampaignError::SUCCESS, 'data' => []];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByImWpa($srvId)
    {
        // TODO
        return ['r' => CampaignError::SUCCESS, 'data' => []];
    }

    /**
     * @param $srvId
     * @return array
     */
    public function getActivitiesByCcWpa($srvId)
    {
        // TODO
        return ['r' => CampaignError::SUCCESS, 'data' => []];
    }
}
