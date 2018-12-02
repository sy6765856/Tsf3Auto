<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/5/25
 */

namespace campaign_mix_svr\Mix\Model;

use campaign_mix_svr\Mix\Base\CampaignError;
use Qidian\Web\Marketing\Campaign;

class CampaignModel
{
    private $kfuin;
    private $kfext;

    public function __construct($kfuin, $kfext = 0)
    {
        $this->kfuin = $kfuin;
        $this->kfext = $kfext;
    }

    public function getList($start, $count, $keyword = '')
    {
        \QdLogService::logInfo("begin getList,start:{$start}, count:{$count}, keyword:{$keyword}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!empty($keyword)) {
            $campaignModel = new UR_db_marketing_t_campaign($this->kfuin, $this->kfext);
            return $campaignModel->searchCampaignByName($keyword, $start, $count);
        }
        $ret = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
            'FKFUin'  => $this->kfuin,
            'FStatus' => 0,
        ])->limit($start, $count)->orderBy(UR_db_marketing_t_campaign::FLastUpdateTime, UR_db_marketing_t_campaign::ORDER_DESC)->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logError("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        \QdLogService::logInfo("ret:" . print_r(json_encode($ret['data']), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $ret['data'];
    }

    /*
     * @params $keyword 搜索计划名称
     */
    public function getListTotal($keyword = '')
    {
        if (empty($keyword)) {
            return UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
                'FKFUin'  => $this->kfuin,
                'FStatus' => 0,
            ])->count();
        } else {
            $model = new UR_db_marketing_t_campaign($this->kfuin, $this->kfext);
            return $model->countCampaignByName($keyword);
        }
    }

    public function getDetail($campaignId, $withActivity = false)
    {
        if ($withActivity) {
            $activities = $this->getRelatedActivities($campaignId);
        } else {
            $activities = [];
        }
        return ['baseInfo' => $this->getBaseInfo($campaignId), 'activities' => $activities];
    }

    public function getRelatedAids($campaignId, $type = 0)
    {
        if (empty($type)) {
            $ret = UR_db_marketing_t_campaign_relation::find($this->kfuin, $this->kfext)->where([
                'FCampaignId' => $campaignId,
                'FKFUin'      => $this->kfuin,
            ])->asArray()->all();
        } else {
            $ret = UR_db_marketing_t_campaign_relation::find($this->kfuin, $this->kfext)->where([
                'FCampaignId' => $campaignId,
                'FType'       => $type,
                'FKFUin'      => $this->kfuin,
            ])->asArray()->all();
        }
        \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_RELATED_ACTIVITY_FAILED), CampaignError::GET_RELATED_ACTIVITY_FAILED);
        }
        $relatedActivities = [];
        if (empty($ret['data'])) {
            return [];
        }
        foreach ($ret['data'] as $row) {
            $relatedActivities[] = ['type' => $row['FType'], 'relatedId' => $row['FRelatedId']];
        }
        return $relatedActivities;
    }

    /*
     * @params $campaignId 营销计划id
     * @params $type 活动类型
     * @channel 活动来源（关键词使用 1=百度来源 2=搜狗来源 0=全部）
     */
    public function getRelatedActivities($campaignId, $type = 0, $channel = 0)
    {
        if (empty($type)) {
            $ret = UR_db_marketing_t_campaign_relation::findWithTcp($this->kfuin, $this->kfext)->where([
                'FCampaignId' => $campaignId,
                'FKFUin'      => $this->kfuin,
            ])->asArray()->all();
        } else {
            if(empty($channel)) {
                $ret = UR_db_marketing_t_campaign_relation::findWithTcp($this->kfuin, $this->kfext)->where([
                    'FCampaignId' => $campaignId,
                    'FType'       => $type,
                    'FKFUin'      => $this->kfuin,
                ])->asArray()->all();
            } else {
                $ret = UR_db_marketing_t_campaign_relation::findWithTcp($this->kfuin, $this->kfext)->where([
                    'FCampaignId' => $campaignId,
                    'FType'       => $type,
                    'FKFUin'      => $this->kfuin,
                    'FRelatedFirstLevel' => $channel,
                ])->asArray()->all();
            }
        }
        //\QdLogService::logDebug("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_RELATED_ACTIVITY_FAILED), CampaignError::GET_RELATED_ACTIVITY_FAILED);
        }
        $relatedActivities = [];
        $costs             = [];
        if (empty($ret['data'])) {
            return [];
        } else {
            $activityTypeIds = [];
            foreach ($ret['data'] as $row) {
                $costs[$row['FRelatedId']]           = $row['FCost'];
                $obj                                 = explode('_', $row['FRelatedId'], 2);
                $activityTypeIds[$row['FType']][0][] = $obj[1];
                $activityTypeIds[$row['FType']][1][] = [
                    'FIndexId'           => $row['FIndexId'],
                    'FRelatedFirstLevel' => $row['FRelatedFirstLevel'],
                    'FName'              => $row['FName'],
                ];
            }
            if (!empty($activityTypeIds)) {
                foreach ($activityTypeIds as $type => $ids) {
                    $activityModel = new ActivityModel($this->kfuin, $this->kfext);
                    try {
                        $activities        = $activityModel->getActivities($type, $ids[0], $ids[1]);
                        $relatedActivities = array_merge($relatedActivities, $activities);
                    } catch (\Exception $e) {
                        ///todo
                    }
                }
            }
        }
        if (!empty($relatedActivities)) {
            foreach ($relatedActivities as &$row) {
                if (isset($costs["{$row['type']}_{$row['aid']}"])) {
                    $row['cost'] = $costs["{$row['type']}_{$row['aid']}"];
                } else {
                    $row['cost'] = null;
                }
            }
        }
        //\QdLogService::logDebug("relatedActivities:" . print_r($relatedActivities, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $relatedActivities;
    }

    /*
     * 获取营销计划基础信息
     * @params $campaignId 营销计划id
     */
    public function getBaseInfo($campaignId)
    {
        $ret = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
            'FId'    => $campaignId,
            'FKFUin' => $this->kfuin,
        ])->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        if (empty($ret['data'])) {
            \QdLogService::logError("campaignId:{$campaignId}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_BASE_INFO_FAILED), CampaignError::GET_BASE_INFO_FAILED);
        }
        return $ret['data'][0];
    }

    public function deleteOne($campaignId)
    {
        $campaign          = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([UR_db_marketing_t_campaign::FId => $campaignId])->one();
        if(empty($campaign)) {
            return true;
        }
        $campaign->FStatus = 1;
        if ($campaign->save()) {
            return true;
        } else {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
    }

    /*
     * 营销计划名称重复性检查
     * @params $name 营销计划名称
     */
    public function checkConflict($name)
    {
        $ret = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
            UR_db_marketing_t_campaign::FKFUin  => $this->kfuin,
            UR_db_marketing_t_campaign::FName   => $name,
            UR_db_marketing_t_campaign::FStatus => 0,
        ])->asArray()->all();
        \QdLogService::logInfo("ret:" . microtime() . "," . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        if (empty($ret['data'])) {
            return false;
        } else {
            return true;
        }
    }

    public function createOne(Campaign\CampaignRecord $info)
    {
        $campaignModel        = new UR_db_marketing_t_campaign($this->kfuin, $this->kfext);
        $campaignModel->FName = $info->getStrName();
        if ($this->checkConflict($campaignModel->FName)) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::CREATE_NAME_CONFLICT), CampaignError::CREATE_NAME_CONFLICT);
        }
        $campaignModel->FBeginTime      = $info->getUint64Begintime();
        $campaignModel->FEndTime        = $info->getUint64Endtime();
        $campaignModel->FDescription    = $info->getStrDescription();
        $campaignModel->FCreateTime     = time();
        $campaignModel->FKFUin          = $this->kfuin;
        $campaignModel->FStatus         = 0;
        $campaignModel->FLastModifier   = $this->kfext;
        $campaignModel->FLastUpdateTime = date('Y-m-d H:i:s');
        if ($campaignModel->save()) {
            return $campaignModel->FId;
        } else {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
    }

    public function modifyBaseInfo($campaignId, Campaign\CampaignRecord $info)
    {
        $campaignRecord = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
            UR_db_marketing_t_campaign::FKFUin  => $this->kfuin,
            UR_db_marketing_t_campaign::FId     => $campaignId,
            UR_db_marketing_t_campaign::FStatus => 0,
        ])->one();
        if (empty($campaignRecord)) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $campaignRecord->FDescription    = $info->getStrDescription();
        $campaignRecord->FBeginTime      = $info->getUint64Begintime();
        $campaignRecord->FEndTime        = $info->getUint64Endtime();
        $campaignRecord->FLastModifier   = $this->kfext;
        $campaignRecord->FLastUpdateTime = date('Y-m-d H:i:s');
        if ($campaignRecord->save()) {
            return true;
        } else {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::MODIFY_BASE_INFO_FAILED), CampaignError::MODIFY_BASE_INFO_FAILED);
        }
    }

    /*
     * @params $statistics['visitNum']
     * @params $statistics['newCustomerNum']
     * @params $statistics['cost']
     */
    public function modifyStatistics($campaignId, $statistics)
    {
        $campaignRecord = UR_db_marketing_t_campaign::find($this->kfuin, $this->kfext)->where([
            UR_db_marketing_t_campaign::FKFUin => $this->kfuin,
            UR_db_marketing_t_campaign::FId    => $campaignId,
        ])->one();
        if (empty($campaignRecord)) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        if (isset($statistics['visitNum'])) {
            $campaignRecord->FVisitNum = $statistics['visitNum'];
        }
        if (isset($statistics['newCustomerNum'])) {
            $campaignRecord->FNewCustomerNum = $statistics['newCustomerNum'];
        }
        if (isset($statistics['cost'])) {
            $campaignRecord->FCost = $statistics['cost'];
        }
        $campaignRecord->FStatisticsUpdateTime = time();
        if ($campaignRecord->save()) {
            return true;
        } else {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::MODIFY_BASE_INFO_FAILED), CampaignError::MODIFY_BASE_INFO_FAILED);
        }
    }

    public function associateActivities($campaignId, $relatedActivities)
    {
        //$typeIds             = [];
        $newRelatedIds       = [];
        $relatedActivityInfo = [];
        if (empty($relatedActivities)) {
            $ret = UR_db_marketing_t_campaign_relation::findWithTcp($this->kfuin, $this->kfext)->where([
                [
                    'FCampaignId',
                    $campaignId,
                    '=',
                ],
            ])->asArray()->all();
        } else {
            foreach ($relatedActivities as $relatedActivity) {
                $relatedId                                    = $this->associateActivity($relatedActivity);
                $newRelatedIds[]                              = $relatedId;
                //$typeIds[$relatedActivity->getUint32Type()][] = $relatedActivity->getStrFrelatedid();
                $indexId = $relatedActivity->getStrIndexId();
                $name = $relatedActivity->getStrName();
                if(empty($indexId) or empty($name)) {
                    try {
                        $activityModel = new ActivityModel($this->kfuin, $this->kfext);
                        $activityModelRet = $activityModel->getActivities($relatedActivity->getUint32Type(), [$relatedActivity->getStrFrelatedid()]);
                        if(!empty($activityModelRet)) {
                            $indexId = $activityModelRet[0]['indexId'];
                            $name = $activityModelRet[0]['name'];
                        }
                    } catch (\Exception $e) {}
                }
                $relatedActivityInfo[$relatedId]              = [
                    'indexId'           => $indexId,
                    'relatedfirstlevel' => $relatedActivity->getStrRelatedfirstlevel(),
                    'name'              => $name,
                ];
            }
            $ret = UR_db_marketing_t_campaign_relation::findWithTcp($this->kfuin, $this->kfext)->where([
                [
                    'FCampaignId',
                    $campaignId,
                    '=',
                ],
                ['FRelatedId', $newRelatedIds, 'in'],
                'or',
            ])->asArray()->all();
        }
        //if (!empty($typeIds)) {
        //    foreach ($typeIds as $type => $ids) {
        //        $ckvConfig = \CampaignConst::getCampaignCKVConf();
        //        $ckvObj    = new \WebCkv($ckvConfig['bid'], null, true, $ckvConfig['l5Conf'], true);
        //        $ckvKey    = "{$this->kfuin}_{$campaignId}_{$type}";
        //        $ckvRet    = $ckvObj->setCol($ckvKey, 0, implode(',', $ids));
        //        //$ckvRet = $ckvObj->getCol($ckvKey, 0);
        //        //file_put_contents('/tmp/hs', $ckvKey . ":" .print_r($ckvRet,true)."\n", FILE_APPEND);
        //    }
        //}
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $modifys            = ['delete' => [], 'update' => [], 'insert' => []];
        $alreadyDealRelates = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                if ($row['FCampaignId'] == $campaignId and in_array($row['FRelatedId'], $newRelatedIds)) {
                    $alreadyDealRelates[] = $row['FRelatedId'];
                } elseif ($row['FCampaignId'] == $campaignId) { //delete
                    $alreadyDealRelates[] = $row['FRelatedId'];
                    $modifys['delete'][]  = $row['FRelatedId'];
                } elseif (in_array($row['FRelatedId'], $newRelatedIds)) { //update
                    $alreadyDealRelates[] = $row['FRelatedId'];
                    $modifys['update'][]  = $row['FRelatedId'];
                }
            }
        }
        //insert
        $modifys['insert'] = array_diff($newRelatedIds, $alreadyDealRelates);
        \QdLogService::logDebug("modifys" . print_r($modifys, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $model = new UR_db_marketing_t_campaign_relation($this->kfuin, $this->kfext, 2);
        \QdLogService::logDebug("relatedActivity:" . print_r($relatedActivityInfo, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $model->batchModifyByCampaignId($campaignId, $modifys, $this->kfuin, $relatedActivityInfo);
    }

    public function associateActivity(Campaign\ActivityRelated $relatedActivity)
    {
        return $relatedActivity->getUint32Type() . "_" . $relatedActivity->getStrFrelatedid();
    }
}