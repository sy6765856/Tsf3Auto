<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/20
 */

namespace campaign_mix_svr\Mix\Model;

use Qidian\Web\Marketing\Campaign;
use campaign_mix_svr\Mix\Base\CampaignError;

class ActivityAdModel extends ActivityBaseModel
{
    public function setIpPort()
    {

    }

    /*
     * 获取广告跟踪列表
     */
    public function getList($start, $count, $keyword = '')
    {
        $whereParams = "FQidianUin = {$this->kfuin}";
        if (!empty($keyword)) {
            $whereParams .= ' and FTrackURL LIKE "%' . addslashes($keyword) . '%"';
        }

        $retArray = [];
        $total    = UR_db_base_mp_tbl_ad_track::find($this->kfuin, $this->kfext)->where($whereParams)->count();
        $ret      = UR_db_base_mp_tbl_ad_track::find($this->kfuin, $this->kfext)->where($whereParams)->limit($start, $count)->orderBy(UR_db_base_mp_tbl_ad_track::FUpdatedAt, UR_db_base_mp_tbl_ad_track::ORDER_DESC)
        ->asArray()->all();
        \QdLogService::logDebug("getAdList: " . print_r($ret, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        if ($ret['ret']['r'] !== CampaignError::SUCCESS) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_WPA_LIST_FAILED), CampaignError::GET_WPA_LIST_FAILED);
        }
        foreach ($ret['data'] as $row) {
            $retArray[] = [
                'aid'          => $row['FId'],
                'name'         => $row['FTrackURL'],
                'indexId'      => rawurlencode(urldecode($row['FTrackURL'])),
                'extendField1' => '',
                'extendField2' => '',
                'extendField3' => '',
                'extendField4' => '',
            ];
            $aids[]     = Campaign\ActivityType::TYPE_AD . '_' . $row['FId'];
        }
        $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
        $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_AD, $aids);
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
        \QdLogService::logInfo("getAdByFIds fids:" . print_r(implode(',', $fids), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $ret = UR_db_base_mp_tbl_ad_track::find($this->kfuin, $this->kfext)->where([
            'FId'        => $fids,
            'FQidianUin' => $this->kfuin,
        ])->asArray()->all();
        \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $retData = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retData[] = [
                    'type'         => Campaign\ActivityType::TYPE_AD,
                    'aid'          => $row['FId'],
                    'name'         => urldecode($row['FTrackURL']),
                    'indexId'      => rawurlencode(urldecode($row['FTrackURL'])),
                    'extendField1' => '',
                    'extendField2' => '',
                ];
            }
        }
        return $retData;
    }
}