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

class ActivityReferralUrlModel extends ActivityBaseModel
{
    public function setIpPort()
    {
    }

    /*
     * 获取推广链接列表
     */
    public function getList($start, $count, $keyword = '')
    {
        $whereParams = "FKFUin = {$this->kfuin}";
        if (!empty($keyword)) {
            $whereParams .= ' and FUrl LIKE "%' . addslashes($keyword) . '%"';
        }

        $retArray = [];
        $total    = UR_db_marketing_t_referral_url::find($this->kfuin, $this->kfext)->where($whereParams)->count();
        $ret      = UR_db_marketing_t_referral_url::find($this->kfuin, $this->kfext)->where($whereParams)->limit($start, $count)->orderBy(UR_db_marketing_t_referral_url::FCreateTime, UR_db_marketing_t_referral_url::ORDER_DESC)
        ->asArray()->all();
        \QdLogService::logDebug("getList: " . print_r($ret, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        if ($ret['ret']['r'] !== CampaignError::SUCCESS) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_WPA_LIST_FAILED), CampaignError::GET_WPA_LIST_FAILED);
        }
        foreach ($ret['data'] as $row) {
            $retArray[] = [
                'aid'          => $row['FId'],
                'name'         => urldecode($row['FUrl']),
                'indexId'      => rawurlencode(urldecode($row['FUrl'])),
                'extendField1' => '',
                'extendField2' => '',
                'extendField3' => '',
                'extendField4' => '',
            ];
            $aids[]     = Campaign\ActivityType::TYPE_SPONSORED_LINK . '_' . $row['FId'];
        }
        $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
        $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_SPONSORED_LINK, $aids);
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
        $ret = UR_db_marketing_t_referral_url::find($this->kfuin, $this->kfext)->where([
            'FKFUin' => $this->kfuin,
            'FId'    => $fids,
        ])->asArray()->all();
        \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $retArray = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retArray[] = [
                    'type'         => Campaign\ActivityType::TYPE_SPONSORED_LINK,
                    'aid'          => $row['FId'],
                    'name'         => urldecode($row['FUrl']),
                    'indexId'      => rawurlencode(urldecode($row['FUrl'])),
                    'extendField1' => '',
                    'extendField2' => '',
                ];
            }
        }
        return $retArray;
    }

    public function getIdByReferralUrl($url)
    {
        $ret = UR_db_marketing_t_referral_url::find($this->kfuin, $this->kfext)->where([
            'FKFUin' => $this->kfuin,
            'FUrl'   => $url,
        ])->asArray()->all();
        \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        if (!empty($ret['data'])) {
            return $ret['data'][0]['FId'];
        }

        //insert
        $referralUrlModel                  = new UR_db_marketing_t_referral_url($this->kfuin, $this->kfext);
        $referralUrlModel->FKFUin          = $this->kfuin;
        $referralUrlModel->FUrl            = $url;
        $referralUrlModel->FStatus         = 0;
        $referralUrlModel->FCreateTime     = time();
        $referralUrlModel->FLastUpdateTime = date('Y-m-d H:i:s');
        if ($referralUrlModel->save()) {
            return $referralUrlModel->FId;
        } else {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
    }
}