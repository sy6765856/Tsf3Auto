<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/20
 */

namespace campaign_mix_svr\Mix\Model;

use Qidian\Web\Marketing\Keywords;
use campaign_mix_svr\Mix\Base\CampaignError;
use Qidian\Web\Marketing\Campaign;

class ActivityKeywordsModel extends ActivityBaseModel
{
    public function setIpPort()
    {
        $this->ipPort = \CampaignConst::getKeywordsSvrConf();
    }

    /*
    * 获取关键词列表
    */
    public function getKeyWordsList($listLevel, $fatherId, $start, $count, $keyword = '')
    {
        \QdLogService::logInfo("getActivityList, listLevel:{$listLevel}, fatherId:{$fatherId}, start:{$start}, count:{$count}, keyword:{$keyword}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $retArray = [];
        $total    = 0;
        $index    = $start / $count;
        if (!empty($keyword) or $listLevel == 4) { //拉取关键词
            $wordsListReq = new Keywords\GetWordListReq();
            $wordsListReq->setUint64Kfuin($this->kfuin);
            $wordsListReq->setUint64Kfext($this->kfext);
            $wordsListReq->setUint32Index($index);
            $wordsListReq->setUint32Count($count);
            if (!empty($keyword)) {
                $wordsListReq->setStrWordName($keyword);
            }
            $keywordIds = explode('_', $fatherId, 4);
            if (!empty($keywordIds[0])) {
                $wordsListReq->setUint32EngineType($keywordIds[0]);
            }
            //else {
            //    $wordsListReq->setUint32EngineType(1);
            //}
            if (!empty($keywordIds[1])) {
                $wordsListReq->setUint32AccountId($keywordIds[1]);
            }
            if (!empty($keywordIds[2])) {
                $wordsListReq->setStrPlanId($keywordIds[2]);
            }
            if (!empty($keywordIds[3])) {
                $wordsListReq->setStrGroupId($keywordIds[3]);
            }
            $reqBody = new Keywords\ReqBody();
            $reqBody->setMsgGetWordListReq($wordsListReq);
            $body         = $this->sendRequest(Keywords\KeywordsCmd::CMD_KEYWORDS_WORD_LIST, $reqBody, true);
            $wordsListRsp = $body->getMsgGetWordListRsp();
            if(is_object($wordsListRsp)) {
                $wordsList    = $wordsListRsp->getRptWordInfo();
                $total        = $wordsListRsp->getUint32Total();
            } else {
                $wordsList = [];
                $total = 0;
            }
            $aids =[];
            if(!empty($wordsList)) {
                foreach ($wordsList as $item) {
                    $retArray[] = [
                        'aid'          => $item->getUint32EngineType() . "_" . $item->getUint32AccountId() . "_" . $item->getStrPlanId() . "_" . $item->getStrGroupId() . "_" . $item->getStrWordId(),
                        'name'         => $item->getStrWordName(),
                        'extendField1' => $item->getUint32EngineType(),
                        'extendField2' => $item->getStrWordId(),
                        'extendField3' => '',
                    ];
                    $aids[] = Campaign\ActivityType::TYPE_KEYWORDS . '_' . $item->getStrWordId();
                }
            }
            $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
            $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_KEYWORDS, $aids);
            if (!empty($retArray)) {
                foreach ($retArray as &$row) {
                    if (isset($relatedCampaigns[$row['extendField2']])) {
                        $row['extendField3'] = $relatedCampaigns[$row['extendField2']]['name'];
                    }
                }
            }
        } elseif ($listLevel == 1) { //拉取账号
            $accountListReq = new Keywords\GetAccountListReq();
            $accountListReq->setUint64Kfuin($this->kfuin);
            $accountListReq->setUint64Kfext($this->kfext);
            //$accountListReq->setUint32EngineType(1);
            //$accountListReq->setUint32Index($index);
            //$accountListReq->setUint32Count($count);
            $reqBody = new Keywords\ReqBody();
            $reqBody->setMsgGetAccountListReq($accountListReq);
            $body           = $this->sendRequest(Keywords\KeywordsCmd::CMD_KEYWORDS_ACCOUNT_LIST, $reqBody, true);
            $accountListRsp = $body->getMsgGetAccountListRsp();
            if(is_object($accountListRsp)) {
                $accountList    = $accountListRsp->getRptAccountInfo();
                $total          = count($accountList);
            } else {
                $accountList    = [];
                $total          = 0;
            }

            if(!empty($accountList)) {
                foreach ($accountList as $item) {
                    $retArray[] = [
                        'aid'          => $item->getUint32EngineType() . "_" . $item->getUint32Id(),
                        'name'         => $item->getStrAccountName(),
                        'extendField1' => '',
                        'extendField2' => '',
                        'extendField3' => '',
                    ];
                }
            }
        } elseif ($listLevel == 2) { //拉取推广计划
            $keywordIds  = explode('_', $fatherId, 2);
            $planListReq = new Keywords\GetPlanListReq();
            $planListReq->setUint64Kfuin($this->kfuin);
            $planListReq->setUint64Kfext($this->kfext);
            $planListReq->setUint32Index($index);
            $planListReq->setUint32Count($count);
            $planListReq->setUint32EngineType($keywordIds[0]);
            $planListReq->setUint32AccountId($keywordIds[1]);
            $reqBody = new Keywords\ReqBody();
            $reqBody->setMsgGetPlanListReq($planListReq);
            $body        = $this->sendRequest(Keywords\KeywordsCmd::CMD_KEYWORDS_PLAN_LIST, $reqBody, true);
            $planListRsp = $body->getMsgGetPlanListRsp();
            if(is_object($planListRsp)) {
                $planList    = $planListRsp->getRptPlanInfo();
                $total       = $planListRsp->getUint32Total();
            } else {
                $planList    = [];
                $total       = 0;
            }

            if(!empty($planList)) {
                foreach ($planList as $item) {
                    $retArray[] = [
                        'aid'          => $item->getUint32EngineType() . "_" . $item->getUint32AccountId() . "_" . $item->getStrPlanId(),
                        'name'         => $item->getStrPlanName(),
                        'extendField1' => '',
                        'extendField2' => '',
                        'extendField3' => '',
                    ];
                }
            }
        } elseif ($listLevel == 3) { //拉取关键词组
            $keywordIds   = explode('_', $fatherId, 3);
            $groupListReq = new Keywords\GetGroupListReq();
            $groupListReq->setUint64Kfuin($this->kfuin);
            $groupListReq->setUint64Kfext($this->kfext);
            $groupListReq->setUint32Index($index);
            $groupListReq->setUint32Count($count);
            $groupListReq->setUint32EngineType($keywordIds[0]);
            $groupListReq->setUint32AccountId($keywordIds[1]);
            $groupListReq->setStrPlanId($keywordIds[2]);
            $reqBody = new Keywords\ReqBody();
            $reqBody->setMsgGetGroupListReq($groupListReq);
            $body         = $this->sendRequest(Keywords\KeywordsCmd::CMD_KEYWORDS_GROUP_LIST, $reqBody, true);
            $groupListRsp = $body->getMsgGetGroupListRsp();
            if(is_object($groupListRsp)) {
                $groupList    = $groupListRsp->getRptGroupInfo();
                $total        = $groupListRsp->getUint32Total();
            } else {
                $groupList    = [];
                $total        = 0;
            }

            if(!empty($groupList)) {
                foreach ($groupList as $item) {
                    $retArray[] = [
                        'aid'          => $item->getUint32EngineType() . "_" . $item->getUint32AccountId() . "_" . $item->getStrPlanId() . "_" . $item->getStrGroupId(),
                        'name'         => $item->getStrGroupName(),
                        'extendField1' => '',
                        'extendField2' => '',
                        'extendField3' => '',
                    ];
                }
            }
        }
        \QdLogService::logInfo("retArray:" . print_r($retArray, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return ['total' => $total, 'records' => $retArray];
    }

    public function getList($start, $count, $keyword = '')
    {

    }

    public function getItemsByIds($fids, $rowsInfo)
    {
        $retArray = [];
        if (!empty($fids)) {
            $index = 0;
            foreach ($fids as $fid) {
                $retArray[] = [
                    'type'         => Campaign\ActivityType::TYPE_KEYWORDS,
                    'aid'          => $fid,
                    'name'         => $rowsInfo[$index]['FIndexId'],
                    'indexId'      => $rowsInfo[$index]['FRelatedFirstLevel'] . '_' . $rowsInfo[$index]['FIndexId'],
                    'extendField1' => $rowsInfo[$index]['FRelatedFirstLevel'],
                ];
                $index++;
            }
        }
        return $retArray;
    }

    public function sendRequest($cmd, $reqBody, $useTcp = false)
    {
        \QdLogService::logInfo("reqBody:" . print_r($reqBody, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = $this->send($cmd, $reqBody, $useTcp);
        $body    = new Keywords\RspBody();
        $body->reset();
        try {
            $body->parseFromString($rspBody);
        } catch (\Exception $e) {
            throw new \Exception('body parse error', -1);
        }
        if (!$body) {
            throw new \Exception("pb data body params error ");
        }
        \QdLogService::logInfo("body:" . print_r($body, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $body;
    }

    public function getKeywordsByFatherIds($fatherIds, $limit = 2000)
    {
        \QdLogService::logDebug("fatherIds:" . print_r($fatherIds, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $idsArray = [];
        $this->uniqueFatherIds($fatherIds, $idsArray);
        $keywords = new UR_db_search_engine_t_se_keywords($this->kfuin, $this->kfext);
        $total = $keywords->getKeywordsByIdsTotal($idsArray);
        if($total > $limit) {
            $retArray = [];
        } else {
            $ret      = $keywords->getKeywordsByIds($idsArray);
            $retArray = [];
            foreach ($ret as $row) {
                $retArray[] = [
                    'aid'          => $row['FWordId'],
                    'name'         => $row['FWordName'],
                    'indexId'      => $row['FWordName'],
                    'extendField1' => $row['FEngineType'],
                    'extendField2' => '',
                    'extendField3' => '',
                    'extendField4' => '',
                ];
            }
        }
        \QdLogService::logDebug("retArray:" . print_r($retArray, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
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
}