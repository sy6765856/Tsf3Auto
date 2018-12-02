<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/20
 */

namespace campaign_mix_svr\Mix\Model;

use Qidian\Web\Marketing\Sms;
use Qidian\Web\Marketing\Campaign;
use campaign_mix_svr\Mix\Base\CampaignError;

class ActivitySmsModel extends ActivityBaseModel
{
    public function setIpPort()
    {
        $this->ipPort = \CampaignConst::getSmsSvrConf();
    }

    /*
     * 获取短信任务列表
     */
    public function getList($start, $count, $keyword = '')
    {
        \QdLogService::logInfo("getList in: {$start}, {$count}, {$keyword}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $getTaskListReq = new Sms\GetTaskListReq();
        $getTaskListReq->setUint64Kfuin($this->kfuin);
        $getTaskListReq->setUint32Index($start / $count + 1);
        $getTaskListReq->setUint32Count($count);
        if (!empty($keyword)) {
            $getTaskListReq->setStrSearch($keyword);
        }
        $reqBody = new Sms\ReqBody();
        $reqBody->setGetTaskListReq($getTaskListReq);
        $body        = $this->sendRequest(Sms\SmsCmd::CMD_GET_TASK_LIST, $reqBody);
        $taskListRsp = $body->getGetTaskListRsp();
        if (!is_object($taskListRsp)) {
            return [];
        }
        $retArray = [];
        $records  = $taskListRsp->getTaskRecord();
        $aids     = [];
        $mtids    = [];
        if (!empty($records)) {
            foreach ($records as $item) {
                $retArray[] = [
                    'aid'          => $item->getUint64Id(),
                    'name'         => $item->getStrName(),
                    'indexId'      => $item->getUint64Id(),
                    'extendField1' => '',
                    'extendField2' => '',
                    'extendField3' => '',
                    'extendField4' => '',
                    'smsTid'       => $item->getUint32TemplateId(),
                ];
                $aids[]     = Campaign\ActivityType::TYPE_MASS_SMS . '_' . $item->getUint64Id();
                $mtids[]    = $item->getUint32TemplateId();
            }
        }
        $messageDetails   = $this->getMessageListDetail($mtids);
        $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
        $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_MASS_SMS, $aids);
        if (!empty($retArray)) {
            foreach ($retArray as &$row) {
                if (isset($relatedCampaigns[$row['aid']])) {
                    $row['extendField3'] = $relatedCampaigns[$row['aid']]['name'];
                }
                $row['extendField2'] = $messageDetails[$row['smsTid']]['msgContent'];
            }
        }
        \QdLogService::logInfo("getList out:" . print_r(json_encode($retArray), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return ['total' => $taskListRsp->getUint64Total(), 'records' => $retArray];
    }

    private function getMessageListDetail($mT_ids)
    {
        $returnArray = array();
        $getListReq  = new Sms\GetSmsListReq();
        if (!empty($mT_ids)) {
            foreach ($mT_ids as $id) {
                $getListReq->appendIdList($id);
            }
        }
        \QdLogService::logInfo("query req:" . \Util::array2Str($getListReq), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);

        $reqBody = new Sms\ReqBody();
        $reqBody->setGetSmsListReq($getListReq);
        $body    = $this->sendRequest(Sms\SmsCmd::CMD_SMS_LIST, $reqBody);
        $rspBody = $body->getGetSmsListRsp();
        $records = $rspBody->getSmsInfo();
        if (empty($records)) {
            return [];
        }
        foreach ($records as $record) {
            $link           = array();
            $arrPlaceHolder = json_decode($record->getStrPlaceholderRes(), true);
            if (!empty($arrPlaceHolder)) {
                foreach ($arrPlaceHolder as $item) {
                    $link[] = $item['name'];
                }
            }

            $returnArray[$record->getUint64Id()] = [
                'id'           => $record->getUint64Id(),
                'msgContent'   => $record->getStrWholeContent(),
                'url'          => boolval($record->getUint32HasLink() == 1),
                'sendTelphone' => $record->getStrSendNumber(),
            ];
        }
        \QdLogService::logInfo("returnArray:" . print_r(json_encode($returnArray), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $returnArray;
    }

    public function getItemsByIds($fids)
    {
        \QdLogService::logInfo("getItemsByIds in, fids:" . print_r(implode(',', $fids), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $ret = [];
        if (empty($fids)) {
            return $ret;
        }
        $getTaskListReq = new Sms\GetTaskListByIdListReq();
        foreach ($fids as $fid) {
            $getTaskListReq->appendIdList($fid);
        }
        $reqBody = new Sms\ReqBody();
        $reqBody->setGetTaskListByIdlistReq($getTaskListReq);
        $body        = $this->sendRequest(Sms\SmsCmd::CMD_GET_TASK_LIST_BY_IDLIST, $reqBody);
        $taskListRsp = $body->getGetTaskListByIdlistRsp();
        if (!is_object($taskListRsp)) {
            return [];
        }
        $taskList = $taskListRsp->getTaskRecord();
        if (!empty($taskList)) {
            $mtids = [];
            foreach ($taskList as $task) {
                $ret[] = [
                    'type'         => Campaign\ActivityType::TYPE_MASS_SMS,
                    'aid'          => $task->getUint64Id(),
                    'name'         => $task->getStrName(),
                    'indexId'      => $task->getUint64Id(),
                    'extendField1' => $task->getUint32Status(),
                    'extendField2' => '',
                    'extendField3' => '',
                    'extendField4' => '',
                    'smsTid'       => $task->getUint32TemplateId(),
                ];
                $mtids[]    = $task->getUint32TemplateId();
            }
            $messageDetails   = $this->getMessageListDetail($mtids);
            if (!empty($ret)) {
                foreach ($ret as &$row) {
                    $row['extendField2'] = $messageDetails[$row['smsTid']]['msgContent'];
                }
            }
        }
        \QdLogService::logInfo("getItemsByIds out:" . print_r(json_encode($ret), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $ret;
    }

    public function sendRequest($cmd, $reqBody, $useTcp = false)
    {
        $rspBody = $this->send($cmd, $reqBody, $useTcp);
        $body    = new Sms\RspBody();
        $body->reset();
        try {
            $body->parseFromString($rspBody);
        } catch (\Exception $e) {
            throw new \Exception('body parse error', -1);
        }
        if (!$body) {
            throw new \Exception("pb data body params error ");
        }
        return $body;
    }
}