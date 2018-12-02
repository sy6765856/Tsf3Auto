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

class ActivityWPAModel extends ActivityBaseModel
{
    public function setIpPort()
    {
    }

    /*
    * 获取WPA活动列表
    */
    public function getList($start, $count, $keyword = '')
    {
        $whereParams = "(FFeatureBit&512=0) and FKFUin = {$this->kfuin}";
        if (!empty($keyword)) {
            $whereParams .= ' and FName LIKE "%' . addslashes($keyword) . '%"';
        }

        $retArray = [];
        $total    = UR_Crm3Mp_WpaInfo::find($this->kfuin, $this->kfext)->where($whereParams)->count();
        $ret      = UR_Crm3Mp_WpaInfo::find($this->kfuin, $this->kfext)->where($whereParams)->limit($start, $count)->orderBy(UR_Crm3Mp_WpaInfo::FCreateTime, UR_Crm3Mp_WpaInfo::ORDER_DESC)->asArray()->all();
        \QdLogService::logDebug("getWPAList: " . print_r($ret, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        if ($ret['ret']['r'] !== CampaignError::SUCCESS) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_WPA_LIST_FAILED), CampaignError::GET_WPA_LIST_FAILED);
        }
        foreach ($ret['data'] as $row) {
            $retArray[] = [
                'aid'          => $row['FID'],
                'name'         => $row['FName'],
                'indexId'      => $row['FID'],
                'extendField1' => $this->generateWpaParams($row),
                'extendField2' => '',
                'extendField3' => '',
                'extendField4' => '',
            ];
            $aids[]     = Campaign\ActivityType::TYPE_WPA . '_' . $row['FID'];
        }
        $activityModel    = new ActivityModel($this->kfuin, $this->kfext, $this->seq);
        $relatedCampaigns = $activityModel->getRelatedCampaigns(Campaign\ActivityType::TYPE_WPA, $aids);
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
        //FFeatureBit 第九位为1是企点电话工作台自动创建的wpa，在列表中不展示。
        $ret = UR_Crm3Mp_WpaInfo::find($this->kfuin, $this->kfext)->where([
            'FID'    => $fids,
            'FKFUin' => $this->kfuin,
        ])->asArray()->all();
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $retArray = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retArray[] = [
                    'type'         => Campaign\ActivityType::TYPE_WPA,
                    'aid'          => $row['FID'],
                    'name'         => $row['FName'],
                    'indexId'      => $row['FID'],
                    'extendField1' => $this->generateWpaParams($row),
                    'extendField2' => '',
                ];
            }
        }
        return $retArray;
    }

    private function generateWpaParams($row)
    {
        return json_encode([
            'fkfextname' => trim($row['FKFEXTName']),
            'fkfext'     => intval($row['FKFExt']),
            'id'         => intval($row['FID']),
            'fkfuin'     => intval($row['FKFUin']),
            'cate'       => intval($row['FType']),
            'type'       => intval($row['FSubType']),
            'scene'      => intval($row['FScene']),
            'avatar'     => trim($row['FAvatar']),
            'theme'      => intval($row['FTheme']),
            'signature'  => trim($row['FSignature']),
            'title'      => trim($row['FTitle']),
            'btnText'    => unserialize($row['FBtnText']),
            'position'   => intval($row['FPosition']),
            'isCorpUin'  => $row['FIsCorpUin'],
            'qrCodeImg'  => $row['FQrCodeImg'],
            'linkUrl'    => $row['FLinkUrl'],
            'name'       => trim($row['FName']),
            'custom'     => json_decode($row['FCustom'], true),
            'imUrl'      => $row['FImUrl'],
            'btnBgColor' => unserialize($row['FBtnBgColor']),

        ]);
    }
}