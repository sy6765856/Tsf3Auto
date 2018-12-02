<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/25
 */

namespace campaign_mix_svr\Mix\Model;

use Qidian\Web\Marketing\Campaign;
use campaign_mix_svr\Mix\Base\CampaignError;

class ActivityCCWPAModel extends ActivityBaseModel
{
    public function setIpPort()
    {
    }

    /*
    * 获取CCWPA活动列表
    */
    public function getList($start, $count, $keyword = '')
    {
        $retArray    = [];
        $whereParams = "FStatus=1 and FKFUin = {$this->kfuin}";
        if (!empty($keyword)) {
            $whereParams .= ' and FName LIKE "%' . addslashes($keyword) . '%"';
        }
        $total = UR_Qidian_WpaInfo::find($this->kfuin, $this->kfext)->where($whereParams)->count();
        $ret   = UR_Qidian_WpaInfo::find($this->kfuin, $this->kfext)->where($whereParams)->orderBy('FUpdateTime', 'DESC')->limit($start, $count)->asArray()->all();
        \QdLogService::logDebug("getCCWPAList: " . print_r($ret, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        if ($ret['ret']['r'] !== CampaignError::SUCCESS) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_CCWPA_LIST_FAILED), CampaignError::GET_CCWPA_LIST_FAILED);
        }
        $aids = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retArray[] = [
                    'aid'          => $row['FID'],
                    'name'         => $row['FName'],
                    'indexId'      => $row['FID'],
                    'extendField1' => $this->generateCCWpaParams($row),
                    'extendField2' => '',
                    'extendField3' => '',
                    'extendField4' => '',
                ];
                $aids[]     = Campaign\ActivityType::TYPE_CC_WPA . '_' . $row['FID'];
            }
        }
        $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
        $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_CC_WPA, $aids);
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
        $ret = UR_Qidian_WpaInfo::find($this->kfuin, $this->kfext)->where([
            'FID'                      => $fids,
            'FKFUin'                   => $this->kfuin,
            UR_Qidian_WpaInfo::FStatus => 1,
        ])->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $retArray = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retArray[] = [
                    'type'         => Campaign\ActivityType::TYPE_CC_WPA,
                    'aid'          => $row['FID'],
                    'name'         => $row['FName'],
                    'indexId'      => $row['FID'],
                    'extendField1' => $this->generateCCWpaParams($row),
                    'extendField2' => '',
                ];
            }
        }
        return $retArray;
    }

    private function generateCCWpaParams($row)
    {
        return json_encode([
            'fkfextname' => $row['FKFEXTName'],
            'id'         => $row['FID'],
            'fkfuin'     => $row['FKFUin'],
            'cate'       => $row['FType'],
            'type'       => $row['FSubType'],
            'scene'      => $row['FScene'],
            'avatar'     => $row['FAvatar'],
            'theme'      => $row['FTheme'],
            'signature'  => $row['FSignature'],
            'title'      => $row['FTitle'],
            'btnText'    => $row['FBtnText'],
            'position'   => $row['FPosition'],
        ]);
    }
}