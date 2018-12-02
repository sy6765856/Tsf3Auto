<?php
/**
 * auto generated.
 * Time: {{.Time}}
 */

namespace campaign_mix_svr\Mix\Controller;

use campaign_mix_svr\Mix\Base\CampaignError;
use campaign_mix_svr\Mix\Model\CampaignModel;
use campaign_mix_svr\Mix\Model\ReportModel;
use Qidian\Web\Marketing\Campaign;
use TSF\Facade\Mix\Request;
use TSF\Facade\Mix\Response;

class CampaignController
{
    public function actionGetList()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("actionGetList in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            $cmdBody = $reqBody->getGetListReq();
            if (empty($cmdBody)) {
                $cmdBody = new Campaign\GetListReq();
            }
            $campaign   = new CampaignModel($kfuin);
            $keyword    = $cmdBody->getStrKeyword();
            $list       = $campaign->getlist($cmdBody->getUint32Start(), $cmdBody->getUint32Count(), $keyword);
            $getListRsp = new Campaign\GetListRsp();
            if (!empty($list)) {
                $campaignIds = [];
                $baseInfoes  = [];
                $now         = time();
                foreach ($list as $row) {
                    if (!empty($row['FId']) and ($now - $row['FStatisticsUpdateTime']) > 3600) {
                        $campaignIds[]           = $row['FId'];
                        $baseInfoes[$row['FId']] = $row;
                    }
                }
                //获取统计数据
                $campaignSummary = [];
                if ($cmdBody->getUint32WithSummary() and !empty($campaignIds)) {
                    $reportModel        = new ReportModel($kfuin);
                    $campaignSummaryRet = $reportModel->getCampaignsSummary($campaignIds, $baseInfoes);
                    //\QdLogService::logDebug("campaignIds:". print_r($campaignIds, true).",campaignSummaryRet: " . print_r($campaignSummaryRet, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
                    if (is_array($campaignSummaryRet)) {
                        foreach ($campaignSummaryRet as $campaignId => $retData) {
                            if ($retData['code'] == 0 and isset($retData['records'][0])) {
                                $summaryInfo = $retData['records'][0];
                                $campaignSummary[$campaignId] = [
                                    'cost'=>$summaryInfo['cost']['total'],
                                    'newCustomerNum'=>$summaryInfo['newCustomerNum']['total'],
                                    'visitNum'=>$summaryInfo['visitNum']['total'],
                                ];
                            }
                        }
                    }
                }

                foreach ($list as $row) {
                    if (($now - $row['FStatisticsUpdateTime']) > 3600) {
                        $useLocal = false;
                    } else {
                        $useLocal = true;
                    }
                    $campaignRecord = $this->generateCampaignRecord($row, $campaignSummary[$row['FId']], $useLocal);
                    $getListRsp->appendCampaignRecord($campaignRecord);
                    if($useLocal == false) { //本地过期刷新
                        $campaign->modifyStatistics($row['FId'], $campaignSummary[$row['FId']]);
                    }
                }
                $getListRsp->setUint32Total($campaign->getListTotal($keyword));
            } else {
                $getListRsp->setUint32Total($campaign->getListTotal($keyword));
            }
            $rspBody->setGetListRsp($getListRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("actionGetList out: " . print_r(json_encode($rspBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_LIST, $seq, $rspBody);
        $response->text($sndData);
    }

    public function actionDetail()
    {
        $request  = Request::facade();
        $response = Response::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("actionDetail in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);

        $cmdBody = $reqBody->getGetDetailReq();
        if (empty($cmdBody)) {
            $cmdBody = new Campaign\GetDetailReq();
        }
        $rspBody = new Campaign\RspBody();
        try {
            $campaign = new CampaignModel($kfuin);
            $detail   = $campaign->getDetail($cmdBody->getUint64CampaignId(), $cmdBody->getUint32WithActivitity());
            \QdLogService::logInfo("detail: " . print_r($detail, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
            $getDetailRsp   = new Campaign\GetDetailRsp();
            $campaignRecord = $this->generateCampaignRecord($detail['baseInfo']);
            $getDetailRsp->setCampaignRecord($campaignRecord);
            if (!empty($detail['activities'])) {
                foreach ($detail['activities'] as $activity) {
                    $activity = $this->generateActivity($activity, $activity['type']);
                    $getDetailRsp->appendActivity($activity);
                }
            }
            $rspBody->setGetDetailRsp($getDetailRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("actionDetail out: " . print_r(json_encode($rspBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_DETAIL, $seq, $rspBody);
        $response->text($sndData);
    }

    public function actionCreateOne()
    {
        $request  = Request::facade();
        $response = Response::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("actionCreateOne in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $cmdBody = $reqBody->getCreateOneReq();
        if (empty($cmdBody)) {
            $cmdBody = new Campaign\CreateOneReq();
        }
        $rspBody = new Campaign\RspBody();
        try {
            $createInfo = $cmdBody->getCampaignRecord();
            if (!is_object($createInfo)) {
                throw new \Exception('pb empty create info', CampaignError::EMPTY_CREATE_INFO);
            }
            $campaign   = new CampaignModel($kfuin, $cmdBody->getUint64OperatorKfext());
            $campaignId = $campaign->createOne($createInfo);
            $createRsp  = new Campaign\CreateOneRsp();
            $createRsp->setUint64CampaignId($campaignId);
            $rspBody->setCreateOneRsp($createRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
            $campaign->associateActivities($campaignId, $cmdBody->getActivitityRelated());
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("actionCreateOne out: " . print_r(json_encode($rspBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_CREATE_ONE, $seq, $rspBody);
        $response->text($sndData);
    }

    public function actionDeleteOne()
    {
        $request  = Request::facade();
        $response = Response::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("actionDeleteOne in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        //$reqBody = new Campaign\ReqBody();
        $cmdBody = $reqBody->getDeleteOneReq();
        if (empty($cmdBody)) {
            $cmdBody = new Campaign\DeleteOneReq();
        }
        $rspBody = new Campaign\RspBody();
        try {
            $campaign   = new CampaignModel($kfuin);
            $campaignId = $cmdBody->getUint64CampaignId();
            $campaign->deleteOne($campaignId);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("actionDeleteOne out: " . print_r(json_encode($rspBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_DELETE_ONE, $seq, $rspBody);
        $response->text($sndData);
    }

    public function actionModifyInfo()
    {
        $request  = Request::facade();
        $response = Response::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("actionModifyInfo in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $cmdBody = $reqBody->getModifyInfoReq();
        if (empty($cmdBody)) {
            $cmdBody = new Campaign\ModifyInfoReq();
        }
        $rspBody = new Campaign\RspBody();
        try {
            $campaign   = new CampaignModel($kfuin, $cmdBody->getUint64OperatorKfext());
            $campaignId = $cmdBody->getUint64CampaignId();
            $campaign->modifyBaseInfo($campaignId, $cmdBody->getCampaignRecord());
            $campaign->associateActivities($campaignId, $cmdBody->getActivitityRelated());

            $modifyInfoRspPb = new Campaign\ModifyInfoRsp();
            $modifyInfoRspPb->setUint64CampaignId($campaignId);
            $rspBody->setModifyInfoRsp($modifyInfoRspPb);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("actionModifyInfo out: " . print_r(json_encode($rspBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_MODIFY_INFO, $seq, $rspBody);
        $response->text($sndData);
        /*刷新数据缓存*/
        $reportModel = new ReportModel($kfuin);
        $reportModel->updateCache($campaignId);
        \QdLogService::logInfo("actionModifyInfo updateCache end.", $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
    }

    public function actionGetRelatedActivities()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("GetRelatedActivities param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            //$reqBody = new Campaign\ReqBody();
            $cmdBody                 = $reqBody->getGetRelatedActivitiesReq();
            $campaignId              = $cmdBody->getUint64CampaignId();
            $type                    = $cmdBody->getUint32Type();
            $campaign                = new CampaignModel($kfuin);
            $activities              = $campaign->getRelatedAids($campaignId, $type);
            $getRelatedActivitiesRsp = new Campaign\GetRelatedActivitiesRsp();
            if (!empty($activities)) {
                foreach ($activities as $activity) {
                    $activityPb = new Campaign\ActivityRelated();
                    $activityPb->setUint32Type($type);
                    $activityPb->setStrFrelatedid($activity['relatedId']);
                    $getRelatedActivitiesRsp->appendActivitityRelated($activityPb);
                }
            }
            $rspBody->setGetRelatedActivitiesRsp($getRelatedActivitiesRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("GetRelatedActivities param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_RELATED_ACTIVITIES, $seq, $rspBody);
        $response->text($sndData);
    }

    private function generateCampaignRecord($record, $campaignSummary = [], $useLocal = false)
    {
        $campaignRecord = new Campaign\CampaignRecord();
        if (isset($record['FId'])) {
            $campaignRecord->setUint64CampaignId($record['FId']);
        }
        $campaignRecord->setStrName($record['FName']);
        $campaignRecord->setStrDescription($record['FDescription']);
        $campaignRecord->setUint64Begintime($record['FBeginTime']);
        $campaignRecord->setUint64Endtime($record['FEndTime']);
        $campaignRecord->setUint64Createtime($record['FCreateTime']);
        $campaignRecord->setUint64Lastmodifier($record['FLastModifier']);
        if ($useLocal) {
            $campaignSummary['cost']            = $record['FCost'];
            $campaignSummary['newCustomerNum']  = $record['FNewCustomerNum'];
            $campaignSummary['visitNum']        = $record['FVisitNum'];
            $campaignSummary['costPerCustomer'] = ($record['FNewCustomerNum'] > 0 ? ($record['FCost'] / $record['FNewCustomerNum']) : -1);
        }
        if (!empty($campaignSummary)) {
            $campaignRecord->setStringCost($campaignSummary['cost']);
            $campaignRecord->setUint64NewCustomerNum($campaignSummary['newCustomerNum']);
            $campaignRecord->setUint64VisitNum($campaignSummary['visitNum']);
            $campaignRecord->setStringCostPerCustomer($campaignSummary['costPerCustomer']);
        }
        return $campaignRecord;
    }

    private function generateRetInfo($code, $msg = '')
    {
        $retInfo = new Campaign\RetInfo();
        $retInfo->setUint32Code($code);
        $retInfo->setStrMessage($msg);
        return $retInfo;
    }

    private function generateActivity($activity, $type)
    {
        $activityPb = new Campaign\Activity();
        $activityPb->setUint32Type($type);
        $activityPb->setUint32Id($activity['aid']);
        $activityPb->setStrName($activity['name']);
        if (isset($activity['indexId'])) {
            $activityPb->setStrIndexId($activity['indexId']);
        }
        for ($i = 1; $i <= 4; $i++) {
            $setKey = "setStrExtendField$i";
            $key    = "extendField$i";
            if (isset($activity[$key])) {
                $activityPb->{$setKey}($activity[$key]);
            }
        }
        return $activityPb;
    }
}