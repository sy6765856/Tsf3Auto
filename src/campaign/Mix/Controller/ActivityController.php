<?php

namespace campaign_mix_svr\Mix\Controller;

use campaign_mix_svr\Mix\Base\CampaignError;
use campaign_mix_svr\Mix\Model\ActivityModel;
use campaign_mix_svr\Mix\Model\ActivityReferralUrlModel;
use Qidian\Web\Marketing\Campaign;
use TSF\Facade\Mix\Request;
use TSF\Facade\Mix\Response;

class ActivityController
{
    /*
     * 获取活动列表
     */
    public function actionGetList()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("get list param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            $cmdBody = $reqBody->getGetActivitiesListReq();
            if (!is_object($cmdBody)) {
                throw new \Exception('parse GetActivitiesListReq pb error', 400);
            }
            $activityModel   = new ActivityModel($kfuin);
            $type            = $cmdBody->getUint32Type();
            $activityListRet = $activityModel->getActivityList($type, $cmdBody->getUint32ListLevel(), $cmdBody->getStrFatherId(), $cmdBody->getUint32Start(), $cmdBody->getUint32Count(), $cmdBody->getStrKeyword());

            $list       = $activityListRet['records'];
            $getListRsp = new Campaign\GetActivitiesListRsp();
            if (!empty($list)) {
                foreach ($list as $row) {
                    $activityRecord = $this->generateActivity($row, $type);
                    $getListRsp->appendActivity($activityRecord);
                }
            }
            $getListRsp->setUint32Total($activityListRet['total']);
            $rspBody->setGetActivitiesListRsp($getListRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("get list param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_ACTIVITIES_LIST, $seq, $rspBody);
        $response->text($sndData);
    }

    /*
     * 保存推广链接
     */
    public function actionSaveReferralUrl()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("SaveReferralUrl param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            $cmdBody  = $reqBody->getSaveReferralUrl();
            $url      = $cmdBody->getStrUrl();
            $parseUrl = parse_url($url);
            if (empty($parseUrl['scheme'])) {
                $parseUrl['scheme'] = 'http';
            }
            $url      = "{$parseUrl['scheme']}://{$parseUrl['host']}{$parseUrl['path']}";
            $checkRes = \UrlChecker::checkUrl($url); /*脏url检测*/
            if (!isset($checkRes['r']) or $checkRes['r'] != 0) {
                \QdLogService::logError("check url: {$url} failed res: " . print_r($checkRes, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
                throw new \Exception(CampaignError::getErrorMessage(CampaignError::CHECK_URL_FAILED), CampaignError::CHECK_URL_FAILED);
            }
            \QdLogService::logDebug("check url res: " . print_r($checkRes['data'], true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
            //检查结果判定
            if ($checkRes['data']['urltype'] == 2) {/*黑名单*/
                throw new \Exception(CampaignError::getErrorMessage(CampaignError::CHECK_URL_BLACK), CampaignError::CHECK_URL_BLACK);
            }
            $activityModel = new ActivityReferralUrlModel($kfuin);
            $saveUrlRsp    = new Campaign\SaveReferralUrlRsp();
            $aid           = $activityModel->getIdByReferralUrl($url);
            $saveUrlRsp->setUint64Id($aid);
            $rspBody->setSaveReferralUrl($saveUrlRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("SaveReferralUrl param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_SAVE_REFERRAL_URL, $seq, $rspBody);
        $response->text($sndData);
    }

    /*
     * 保存活动成本
     */
    public function actionSaveCost()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("SaveCost param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            $cmdBody       = $reqBody->getSaveActivityCostReq();
            $relatedId     = $cmdBody->getStringRelatedId();
            $cost          = $cmdBody->getStringCost();
            $activityModel = new ActivityModel($kfuin);
            $activityModel->saveCost($relatedId, $cost);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("SaveCost param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_SAVE_ACTIVITY_COST, $seq, $rspBody);
        $response->text($sndData);
    }

    /*
     * 给活动关联营销计划
     */
    public function actionAssociateActivityCampaign()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("AssociateActivityCampaign param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            $cmdBody       = $reqBody->getAssociateActivityCampaignReq();
            $type          = $cmdBody->getUint32Type();
            $aid           = $cmdBody->getUint32Id();
            $campaignId    = $cmdBody->getUint64CampaignId();
            $activityModel = new ActivityModel($kfuin);
            $activityModel->associateActivityCampaign($type, $aid, $campaignId);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("AssociateActivityCampaign param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_ASSOCIATE_ACTIVITY_CAMPAIGN, $seq, $rspBody);
        $response->text($sndData);
    }

    /*
     * 获取活动关联的campaign
     */
    public function actionGetRelatedCampaign()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("GetRelatedCampaign param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            $cmdBody                  = $reqBody->getGetCampaignByActivityReq();
            $activities               = $cmdBody->getActivity();
            $activityModel            = new ActivityModel($kfuin, $cmdBody->getUint64Kfext());
            $getCampaignByActivityRsp = new Campaign\GetCampaignByActivityRsp();
            if (!empty($activities)) {
                $typeAids = [];
                foreach ($activities as $activity) {
                    $typeAids[$activity->getUint32Type()][] = $activity->getUint32Type() . "_" . $activity->getUint32Id();
                }
                if (!empty($typeAids)) {
                    foreach ($typeAids as $type => $aids) {
                        $relatedCampaigns = $activityModel->getRelatedCampaigns($type, $aids);
                        if (!empty($relatedCampaigns)) {
                            foreach ($relatedCampaigns as $aid => $campaignInfo) {
                                $activityRelatedCampaign = new Campaign\ActivityRelatedCampaign();
                                $activity                = new Campaign\Activity();
                                $activity->setUint32Type($type);
                                $activity->setUint32Id($aid);
                                $activityRelatedCampaign->setActivity($activity);
                                $campaignRecord = new Campaign\CampaignRecord();
                                $campaignRecord->setUint64CampaignId($campaignInfo['campaignId']);
                                $campaignRecord->setStrName($campaignInfo['name']);
                                $campaignRecord->setUint64Begintime($campaignInfo['beginTime']);
                                $campaignRecord->setUint64Endtime($campaignInfo['endTime']);
                                $campaignRecord->setUint64Createtime($campaignInfo['createTime']);
                                $activityRelatedCampaign->setCampaignRecord($campaignRecord);
                                $getCampaignByActivityRsp->appendActivityRelatedCampaign($activityRelatedCampaign);
                            }
                        }
                    }
                }
            }
            $rspBody->setGetCampaignByActivityRsp($getCampaignByActivityRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("GetRelatedCampaign param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_CAMPAIGN_BY_ACTIVITY, $seq, $rspBody);
        $response->text($sndData);
    }

    /*
     * 根据活动父级获取子活动
     */
    public function actionGetActivityFatherIds()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        \QdLogService::logInfo("GetActivityFatherIds param in: " . print_r(json_encode($reqBody), true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = new Campaign\RspBody();
        try {
            //$reqBody                     = new Campaign\ReqBody();
            $cmdBody                     = $reqBody->getGetActivitiesByFatheridsReq();
            $fatherids                   = $cmdBody->getFatherids();
            $type                        = $cmdBody->getUint32Type();
            $activityModel               = new ActivityModel($kfuin);
            $activities                  = $activityModel->getActivitiesByFatherids($type, $fatherids);
            $getActivitiesByFatheridsRsp = new Campaign\GetActivitiesByFatheridsRsp();
            if(!empty($activities['records'])) {
                foreach ($activities['records'] as $row) {
                    $activityRecord = $this->generateActivity($row, $type);
                    $getActivitiesByFatheridsRsp->appendActivity($activityRecord);
                }
            }
            $getActivitiesByFatheridsRsp->setUint64Total($activities['total']);
            $rspBody->setGetActivitiesByFatheridsRsp($getActivitiesByFatheridsRsp);
            $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logDebug("actionGetActivityFatherIds param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_ACTIVITIES_BY_FATHERIDS, $seq, $rspBody);
        $response->text($sndData);
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

    private function generateRetInfo($code, $msg = '')
    {
        $retInfo = new Campaign\RetInfo();
        $retInfo->setUint32Code($code);
        $retInfo->setStrMessage($msg);
        return $retInfo;
    }

    public function actionGetByUpData()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $req      = $data->reqBody;
        \QdLogService::logInfo("GetByUpData param in: " . print_r(json_encode($req), true));
        $cmdBody      = $req->getGetActivitiesByUpdataReq();
        $kfuin        = $cmdBody->getUint64Kfuin();
        $kfext        = $cmdBody->getUint64Kfext();
        $activityData = $cmdBody->getActivityData();
        $rsp          = new Campaign\RspBody();
        try {
            $model = new ActivityModel($kfuin, $kfext);
            $ret   = $model->getActivitiesByUpData($activityData);
            if ($ret['r'] != CampaignError::SUCCESS) {
                $retInfo = $this->generateRetInfo($ret['r'], $ret['msg']);
            } else {
                $mapData = $ret['data'];
                $cmdRsp  = new Campaign\GetActivitiesByUpDataRsp();
                foreach ($mapData as $map) {
                    $cmdRsp->appendActivityMap($map);
                }
                $retInfo = $this->generateRetInfo(CampaignError::SUCCESS);
                $rsp->setGetActivitiesByUpdataRsp($cmdRsp);
            }
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rsp->setRetInfo($retInfo);
        \QdLogService::logInfo("GetByUpData param out: " . print_r($rsp, true));
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_ACTIVITIES_BY_UPDATA, $seq, $rsp);
        $response->text($sndData);
    }
}