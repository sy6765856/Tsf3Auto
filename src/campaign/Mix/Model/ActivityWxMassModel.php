<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/20
 */

namespace campaign_mix_svr\Mix\Model;

use Qidian\Web\Marketing\Wx;
use Qidian\Web\Marketing\Campaign;
use campaign_mix_svr\Mix\Base\CampaignError;

class ActivityWxMassModel extends ActivityBaseModel
{
    public function setIpPort()
    {
        $this->ipPort = \CampaignConst::getEaWxSvrConf();
    }

    public function getMpAccountMassList($listLevel, $fatherId, $start, $count, $keyword = '')
    {
        $retArray = [];
        $total    = 0;
        if ($listLevel == 1) { //关联的公众号列表
            $wxAccountListReq = new Wx\GetWxAccountListReq();
            $wxAccountListReq->setUint64Kfuin($this->kfuin);
            $reqBody = new Wx\ReqBody();
            $reqBody->setGetWxAccountListReq($wxAccountListReq);
            $body           = $this->sendRequest(Wx\WxCmd::CMD_GET_WX_ACCOUNT, $reqBody);
            $accountListRsp = $body->getGetWxAccountListRsp();
            $accountList    = $accountListRsp->getWxAccountRecord();
            $total          = count($accountList);
            foreach ($accountList as $item) {
                $retArray[] = [
                    'aid'          => 0,
                    'name'         => $item->getStrAppName(),
                    'extendField1' => $item->getInt32VerifyTypeInfo(), //授权方认证类型，-1代表未认证，0代表微信认证
                    'extendField2' => $item->getUint32ServiceTypeInfo(),//授权方公众号类型，0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号
                    'extendField3' => $item->getStrAppId(),
                ];
            }
            //\QdLogService::logDebug("getMpAccountMassList: " . print_r($retArray, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        } elseif ($listLevel == 2) { //一个公众号下的群发列表
            $wxMsgListReq = new Wx\GetWxMsgListReq();
            $wxMsgListReq->setUint64Kfuin($this->kfuin);
            $wxMsgListReq->setStrAppId($fatherId);
            $wxMsgListReq->setUint32Index($start / $count);
            $wxMsgListReq->setUint32Count($count);
            if (!empty($keyword)) {
                $wxMsgListReq->setStrSearchName($keyword);
            }
            $reqBody = new Wx\ReqBody();
            $reqBody->setGetWxMsgListReq($wxMsgListReq);
            $body       = $this->sendRequest(Wx\WxCmd::CMD_GET_WX_MSG_LIST, $reqBody);
            $msgListRsp = $body->getGetWxMsgListRsp();
            if (!is_object($msgListRsp)) {
                return ['total' => $total, 'records' => $retArray];
            }
            $msgList = $msgListRsp->getMsgRecord();
            $total   = $msgListRsp->getUint64Total();
            $aids    = [];
            $appids  = [];
            foreach ($msgList as $item) {
                $appids[]   = $item->getStrAppId();
                $retArray[] = [
                    'aid'          => $item->getUint64Fid(),
                    'name'         => $item->getStrTitle(),
                    'indexId'      => $item->getUint64Fid(),
                    'extendField1' => $item->getDoubleCost(),
                    'extendField2' => $item->getStrContentData(),
                    'extendField3' => '',
                    'extendField4' => $item->getUint32ContentType(),
                    'appid'        => $item->getStrAppId(),
                ];
                $aids[]     = Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS . '_' . $item->getUint64Fid();
            }
            $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
            $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS, $aids);
            if (!empty($retArray)) {
                foreach ($retArray as &$row) {
                    if (isset($relatedCampaigns[$row['aid']])) {
                        $row['extendField3'] = $relatedCampaigns[$row['aid']]['name'];
                    }
                }
            }
            //\QdLogService::logDebug("getMpAccountMassList: " . print_r($retArray, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);

            $accountMapping = $this->getAccountMapping($appids);
            if (!empty($retArray)) {
                foreach ($retArray as &$row) {
                    $accountInfo         = $accountMapping[$row['appid']];
                    $row['extendField4'] = json_encode([
                        'groupId'               => $accountInfo['FAppId'],
                        'groupName'             => $accountInfo['FAppName'],
                        'groupIsAuthentication' => $accountInfo['FVerifyTypeInfo'],
                        'messageType'           => $row['extendField4'],
                    ]);
                }
            }
            //\QdLogService::logDebug("getMpAccountMassList: " . print_r($retArray, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        }
        return ['total' => $total, 'records' => $retArray];
    }

    /*
     * 获取列表
     */
    public function getList($start, $count, $keyword = '')
    {
        return false;
    }

    public function getItemsByIds($fids)
    {
        \QdLogService::logInfo("getMpAccountMassByFIds fids:" . print_r(implode(',', $fids), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $ret = [];
        if (empty($fids)) {
            return $ret;
        }
        $getWXMsgListByFidReq = new Wx\GetWXMsgListByFidReq();
        $getWXMsgListByFidReq->setUint64Kfuin($this->kfuin);
        $getWXMsgListByFidReq->setStrFidStr(implode(',', $fids));
        $reqBody = new Wx\ReqBody();
        $reqBody->setGetWxMsgListByFidReq($getWXMsgListByFidReq);
        $body       = $this->sendRequest(Wx\WxCmd::CMD_GET_WX_MSG_LIST_BY_FID, $reqBody);
        $msgListRsp = $body->getGetWxMsgListByFidRsp();
        if (!is_object($msgListRsp)) {
            return [];
        }
        $msgList = $msgListRsp->getMsgRecord();
        if (!empty($msgList)) {
            foreach ($msgList as $msgRecord) {
                $contentData = $msgRecord->getStrContentData();
                $contentType = $msgRecord->getUint32ContentType();
                $ret[]       = [
                    'type'         => Campaign\ActivityType::TYPE_MP_ACCOUNT_MASS,
                    'aid'          => $msgRecord->getUint64Fid(),
                    'name'         => $msgRecord->getStrTitle(),
                    'indexId'      => $msgRecord->getUint64Fid(),
                    'extendField1' => $msgRecord->getDoubleCost(),
                    'extendField2' => $contentData,
                    'extendField3' => "id=" . (($contentType == 3 and isset($contentData['message']['index'])) ? ($msgRecord->getUint64MsgDataId() . '_' . $contentData['message']['index']) : ($msgRecord->getUint64MsgDataId() . '_1')) . "&appid=" . $msgRecord->getStrAppId(),
                    'extendField4' => $contentType,
                ];
            }
        }
        \QdLogService::logInfo("getMpAccountMassByFIds out:" . print_r(json_encode($ret), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        return $ret;
    }

    public function sendRequest($cmd, $reqBody, $useTcp = false)
    {
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
        return $body;
    }

    public function getAccountMapping($appids)
    {
        //获取公众号信息
        $ret = UR_db_marketing_t_koala_gzh_account::find($this->kfuin, $this->kfext)->where([
            'FAppId' => $appids,
            'FKFUin' => $this->kfuin,
        ])->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $accountMapping = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $accountMapping[$row['FAppId']] = $row;
            }
        }
        return $accountMapping;
    }
}