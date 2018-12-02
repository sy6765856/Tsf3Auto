<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/25
 */

namespace campaign_mix_svr\Mix\Model;

use Qidian\Web\Marketing\Wx;
use Qidian\Web\Marketing\Campaign;
use campaign_mix_svr\Mix\Base\CampaignError;

class ActivityH5Model extends ActivityBaseModel
{
    public function setIpPort()
    {
        $this->ipPort = \CampaignConst::getH5SvrConf();
    }

    /*
    * 获取H5活动列表
    */
    public function getList($start, $count, $keyword = '')
    {
        \QdLogService::logInfo("getList start:{$start}, count:{$count}, keyword:{$keyword}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $retArray  = [];
        $total     = 0;
        $h5ListReq = new Wx\GetWxH5EventListReq();
        $h5ListReq->setUint32Index($start / $count);
        $h5ListReq->setUint32Count($count);
        if (!empty($keyword)) {
            $h5ListReq->setStrSearchName($keyword);
        }
        $reqBody = new Wx\ReqBody();
        $reqBody->setGetWxH5EventListReq($h5ListReq);
        $body      = $this->sendRequest(Wx\WxCmd::CMD_GET_WX_H5_EVENT_LIST, $reqBody);
        $h5ListRsp = $body->getGetWxH5EventListRsp();
        if (!is_object($h5ListRsp)) {
            return ['total' => $total, 'records' => $retArray];
        }
        $h5List = $h5ListRsp->getH5Record();
        $total  = $h5ListRsp->getUint64Total();
        $aids   = [];
        foreach ($h5List as $item) {
            $retArray[] = [
                'aid'          => $item->getUint64EventId(),
                'name'         => $item->getStrEventName(),
                'indexId'      => $item->getUint64EventId(),
                'extendField1' => '',
                'extendField2' => $item->getStrUrl(),
                'extendField3' => '',
                'extendField4' => '',
            ];
            $aids[]     = Campaign\ActivityType::TYPE_WX_H5 . '_' . $item->getUint64EventId();
        }
        $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
        $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_WX_H5, $aids);
        if (!empty($retArray)) {
            foreach ($retArray as &$row) {
                if (isset($relatedCampaigns[$row['aid']])) {
                    $row['extendField3'] = $relatedCampaigns[$row['aid']]['name'];
                }
            }
        }
        return ['total' => $total, 'records' => $retArray];
    }

    public function getItemsByIds($fids)
    {
        \QdLogService::logInfo("getItemsByIds fids:" . print_r(implode(',', $fids), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $ret = [];
        if (empty($fids)) {
            return $ret;
        }
        $getH5ListByFidReq = new Wx\GetWxH5EventListByFidReq();
        foreach ($fids as $fid) {
            $wxH5EventIdRecord = new Wx\WxH5EventIdRecord();
            $wxH5EventIdRecord->setUint64EventId($fid);
            $getH5ListByFidReq->appendWxH5EventId($wxH5EventIdRecord);
        }
        $reqBody = new Wx\ReqBody();
        $reqBody->setGetWxH5EventListByFidReq($getH5ListByFidReq);
        $body      = $this->sendRequest(Wx\WxCmd::CMD_GET_WX_H5_EVENT_LIST_BY_FID, $reqBody);
        $h5ListRsp = $body->getGetWxH5EventListByFidRsp();
        if (!is_object($h5ListRsp)) {
            return [];
        }
        $h5List = $h5ListRsp->getH5Record();
        if (!empty($h5List)) {
            foreach ($h5List as $h5Record) {
                $ret[] = [
                    'type'         => Campaign\ActivityType::TYPE_WX_H5,
                    'aid'          => $h5Record->getUint64EventId(),
                    'name'         => $h5Record->getStrEventName(),
                    'indexId'      => $h5Record->getUint64EventId(),
                    'extendField1' => '',
                    'extendField2' => $h5Record->getStrUrl(),
                    'extendField3' => '',
                    'extendField4' => '',
                ];
            }
        }
        \QdLogService::logInfo("getItemsByIds out:" . print_r(json_encode($ret), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $ret;
    }

    public function sendRequest($cmd, $reqBody, $useTcp = false)
    {
        \QdLogService::logInfo("sendRequest in, cmd:{$cmd}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $rspBody = $this->send($cmd, $reqBody, $useTcp);
        $body    = new Wx\RspBody();
        $body->reset();
        try {
            $body->parseFromString($rspBody);
        } catch (\Exception $e) {
            throw new \Exception('body parse error', -1);
        }
        if (!$body) {
            throw new \Exception("pb data body params error ");
        }
        \QdLogService::logInfo("sendRequest out, cmd:{$cmd}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $body;
    }
}