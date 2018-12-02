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

class ActivityQRCodeModel extends ActivityBaseModel
{
    public function setIpPort()
    {
    }

    /*
     * 获取二维码列表
     */
    public function getList($start, $count, $keyword = '')
    {
        $whereParams = "FTId = {$this->kfuin} and FStatus=1";
        if (!empty($keyword)) {
            $whereParams .= ' and FName LIKE "%' . addslashes($keyword) . '%"';
        }

        $retArray = [];
        $total    = UR_db_base_mp_tbl_qr_list::find($this->kfuin, $this->kfext)->where($whereParams)->count();
        $ret      = UR_db_base_mp_tbl_qr_list::find($this->kfuin, $this->kfext)->where($whereParams)->limit($start, $count)->orderBy(UR_db_base_mp_tbl_qr_list::FCreateTime, UR_db_base_mp_tbl_qr_list::ORDER_DESC)
        ->asArray()->all();
        \QdLogService::logDebug("getList: " . print_r($ret, true), $this->kfuin, $this->kfuin, 0, __CLASS__, __LINE__, __METHOD__);
        if ($ret['ret']['r'] !== CampaignError::SUCCESS) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::GET_WPA_LIST_FAILED), CampaignError::GET_WPA_LIST_FAILED);
        }
        foreach ($ret['data'] as $row) {
            $retArray[] = [
                'aid'          => $row['FId'],
                'name'         => $row['FName'],
                'indexId'      => rawurlencode(urldecode($this->generateQrcodeUrl($row))),
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
        \QdLogService::logInfo("getQrcodeByFIds fids:" . print_r(implode(',', $fids), true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $ret = UR_db_base_mp_tbl_qr_list::find($this->kfuin, $this->kfext)->where([
            'FId'     => $fids,
            'FTId'    => $this->kfuin,
            'FStatus' => 1,
        ])->asArray()->all();
        \QdLogService::logInfo("ret:" . print_r($ret, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if (!isset($ret['ret']['r']) or $ret['ret']['r'] != 0) {
            throw new \Exception(CampaignError::getErrorMessage(CampaignError::DATA_SQL_SELECT_FAIL), CampaignError::DATA_SQL_SELECT_FAIL);
        }
        $retArray = [];
        if (!empty($ret['data'])) {
            foreach ($ret['data'] as $row) {
                $retArray[] = [
                    'type'         => Campaign\ActivityType::TYPE_QRCODE,
                    'aid'          => $row['FId'],
                    'name'         => $row['FName'],
                    'indexId'      => rawurlencode(urldecode($this->generateQrcodeUrl($row))),
                    'extendField1' => '',
                    'extendField2' => '',
                ];
            }
        }
        return $retArray;
    }

    private function generateQrcodeUrl($row)
    {
        $url     = $this->clearUrl($row['FUrl']);
        $added   = "utm_campaign=" . urlencode($row['FCampaign']) . "&utm_ad=" . urlencode($row['FName']) . "&utm_source=" . urlencode($row['FSource']) . "&utm_medium=" . urlencode($row['FMedium']) . "&utm_type=qrc";
        $tempUrl = $url;
        $hasRoot = strpos($tempUrl, '/');
        list($tempUrl, $archor) = explode('#', $tempUrl);
        $tempUrl = $this->addUrlPath($tempUrl);
        if (strpos($tempUrl, '?')) {
            $finalUrl = $tempUrl . '&' . $added;
        } elseif ($hasRoot) {
            $finalUrl = $tempUrl . '?' . $added;
        } else {
            $finalUrl = $tempUrl . '/?' . $added;
        }
        return $finalUrl;
    }

    private function addUrlPath($url)
    {
        $ret = parse_url($url);
        if (!isset($ret['path']) || empty($ret['path'])) {
            $url = $url . '/';
        }
        return $url;
    }

    /**
     * url清除
     */
    private function clearUrl($url)
    {
        return $this->clearUrlParams($url, array('utm_campaign', 'utm_ad', 'utm_source', 'utm_medium', 'utm_type'));
    }

    private function clearUrlParams($url, $removeParams = array())
    {
        $url = trim($url);
        $url = trim($url, "?&#");
        $pos = strpos($url, '#');
        if ($pos === false) {
            $tempUrl = $url;
        } else {
            $tempUrl = substr($url, 0, $pos);
            $archor  = substr($url, $pos + 1);
        }
        $tempUrl = trim($tempUrl);
        $tempUrl = trim($tempUrl, "?&");
        $pos     = strpos($tempUrl, '?');
        if ($pos === false) {
            if (isset($archor)) {
                return $tempUrl . '#' . $archor;
            }
            return $tempUrl;
        } else {
            $path  = substr($tempUrl, 0, $pos);
            $query = substr($tempUrl, $pos + 1);
        }
        $path      = trim($path);
        $query     = trim($query);
        $params    = explode('&', $query);
        $newParams = array();
        foreach ($params as $param) {
            $keyVals = explode('=', $param);
            if (in_array($keyVals[0], $removeParams)) {
                continue;
            }
            $newParams[] = $param;
        }
        $url = $path;
        if ($newParams) {
            $url .= '?' . implode('&', $newParams);
        }
        if (isset($archor)) {
            $url .= '#' . $archor;
        }
        return $url;
    }
}