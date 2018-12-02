<?php
/**
 * Created by PhpStorm.
 * User: jianzjzhang
 * Date: 2018/5/29
 * Time: 17:59
 */

namespace campaign_mix_svr\Mix\Controller;

use campaign_mix_svr\Mix\Model\ReportModel;
use Qidian\Web\Marketing\Campaign;
use TSF\Facade\Mix\Request;
use TSF\Facade\Mix\Response;

class CampaignReportController
{
    const ALL_UPDATE = 0;

    /**
     * 获取一个或多个Campaign下所有通路的汇总数据,pb发来的请求包中包含一个或多个campaign id
     * example $ret
     * [
     * campaignid1=>{
     * 'code' => 0,
     * 'msg'  => '',
     * 'records' => {
     * '0' => {
     * 'type': 0,              //campaign summary
     * 'visitNum': 1030,       //访问次数
     * 'newCustomerNum': 123,  //新增客户数
     * 'cost': 6534,           //总成本
     * 'costPerCustomer': 35,  //单客成本
     * 'beginTime': '2017-06-05 20:00',
     * 'endTime': '2017-06-18 20:00',
     * 'lastEditTime': '2017-06-05 20:00',
     * 'lastModifier': '周瑶',
     * 'name': ''              //campaign name
     * 'description': ''       //计划描述
     * },
     * ...
     * '4' => {
     * 'type': 4,            //微信公众号
     * 'sendCount': 530,     //发送数
     * 'readCount': 80,      //阅读数
     * 'readUserCount': 0,   //阅读人数
     * 'oriReadCount': 0,    //原文阅读数
     * 'oriReadUserCount': 0 //原文阅读人数
     * 'shareCount': 15,     //分享数
     * 'shareUserCount':0    //分享人数
     * 'newCustomerNum': 7,  //新增客户数
     * 'cost': -1,           //总成本
     * 'costPerCustomer': -1 //单客成本
     *  }
     *  ...
     *  }
     *  }
     *  campaignid2=>{
     *  }
     *  ...
     * ]
     */
    public function actionGetAllChannelSummary()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        $rspBody  = new Campaign\RspBody();
        \QdLogService::logInfo("get all channel summary param in: " . print_r($reqBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        try {
            $campaignIds         = $reqBody->getGetAllChannelSummaryReq()->getUint64CampaignId();
            $updateActivityTypes = $reqBody->getGetAllChannelSummaryReq()->getUint32UpdateActivityType();
            $report              = new ReportModel($kfuin);

            if (count($campaignIds) != count($updateActivityTypes)) {
                $updateActivityTypes = array_fill(0, count($campaignIds), self::ALL_UPDATE);
            }

            $ret = array();
            foreach (array_combine($campaignIds, $updateActivityTypes) as $campaignId => $updateActivityType) {
                if ($updateActivityType === self::ALL_UPDATE) {
                    $report->updateCache($campaignId);
                }
                $ret[$campaignId] = $report->getAllChannelSummaryFromCache($campaignId);
            }

            $rspContent = new Campaign\GetAllChannelSummaryRsp();
            $rspContent->setStrJson(json_encode($ret));

            $rspBody->setGetAllChannelSummaryRsp($rspContent);
            $retInfo = $this->generateRetInfo(0);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("get all channel summary param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_ALL_CHANNEL_SUMMARY, $seq, $rspBody);
        $response->text($sndData);
    }

    /**
     * 获取一个campaign下一个特定通路的数据明细,请求pb包中包含一个campaign id,一个activity type,
     * 以及查询的offset, limit, orderby, desc
     * example $ret
     * {
     * 'code': 0,
     * 'msg': '',
     * 'records':[
     * {
     * 'activityid': '',     //活动id
     * 'activityName': '春节雪村游推广',
     * 'imgUrl': '',         //图片链接
     * 'sendCount': 530,     //发送数
     * 'readCount': 80,      //阅读数
     * 'shareCount': 15,     //分享数
     * 'newCustomerNum': 7,  //新增客户数
     *          ...
     * 'cost': -1,           //总成本
     * 'costPerCustomer': -1 //单客成本
     * 'detailUrl':          //详情页链接
     * }
     * ...
     * ]
     *  'total': 123        //数据总条数
     * }
     */

    public function actionGetChannelDetail()
    {
        $response = Response::facade();
        $request  = Request::facade();
        $data     = $request->buf;
        $seq      = $data->seq;
        $kfuin    = $data->kfuin;
        $reqBody  = $data->reqBody;
        $rspBody  = new Campaign\RspBody();

        try {
            $req = $reqBody->getGetChannelDetailReq();

            $campaignId    = $req->getUint64CampaignId();
            $activity_type = $req->getUint32Type();
            $offset        = $req->getUint32Offset();
            $limit         = $req->getUint32Limit();
            $orderby       = $req->getStrOrderby();
            $desc          = $req->getBoolDesc();
            $channel       = $req->getUint32Channel();

            $report = new ReportModel($kfuin);

            //如果请求默认的第一页，读缓存
            if (empty($orderby) && $offset === 0 && $channel <= 0 && $limit <= 15) {
                $ret = $report->getChannelDetailFromCache($campaignId, $activity_type);
            }
            else {
                $ret = $report->getChannelDetail($campaignId, $activity_type, $orderby, $offset, $limit, $desc, $channel);
            }

            $rspContent = new Campaign\GetChannelDetailRsp();
            $rspContent->setStrJson(json_encode($ret));

            $rspBody->setGetChannelDetailRsp($rspContent);
            $retInfo = $this->generateRetInfo(0);
        } catch (\Exception $e) {
            $retInfo = $this->generateRetInfo($e->getCode(), $e->getMessage());
        }
        $rspBody->setRetInfo($retInfo);
        \QdLogService::logInfo("get channel detail param out: " . print_r($rspBody, true), $kfuin, $kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = \PBHelper::packMsg(Campaign\CampaignCmd::CMD_GET_CHANNEL_DETAIL, $seq, $rspBody);
        $response->text($sndData);
    }

    private function generateRetInfo($code, $msg = '')
    {
        $retInfo = new Campaign\RetInfo();
        $retInfo->setUint32Code($code);
        $retInfo->setStrMessage($msg);
        return $retInfo;
    }
}