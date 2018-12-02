<?php
/**
 * auto generated.
 * Time: {{.Time}}
 */

use Qidian\Web\Marketing\Campaign\ActivityType;

class CampaignConst
{
    /**
     * 获取Http正向代理配置
     *
     * @return array
     */
    public static function getHttpProxyConf()
    {
        return array(
            'modId' => 482497,
            'cmdId' => 2359296,
        );
    }

    public static function getCCUdsConf()
    {
        $l5  = array(
            'cmdId' => 65536,
            'modId' => 804353,
        );
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '10.240.64.145';
            $conf['port'] = '3535';
        }
        return $conf;
    }

    public static function getEaWxSvrConf()
    {
        $l5  = array(
            'modId' => 958977,
            'cmdId' => 393216,
        );
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '10.100.71.194';
            $conf['port'] = '9698';
        }
        return $conf;
    }

    public static function getAdL5Conf()
    {
        return array(
            'modId' => 484161,
            'cmdId' => 131072,
            'env'   => 1,
        );
    }

    public static function getDruidDataSource()
    {
        return [
            ActivityType::TYPE_AD              => 'ad_campaign_other_oa',  //广告跟踪
            ActivityType::TYPE_KEYWORDS        => '',
            ActivityType::TYPE_MASS_SMS        => '',
            ActivityType::TYPE_MP_ACCOUNT_MASS => 'weixin_pic_and_text_report_oa_v1',  //微信公众号
            ActivityType::TYPE_SPONSORED_LINK  => 'ad_campaign_other_oa',  //推广链接
            ActivityType::TYPE_QRCODE          => 'ad_campaign_other_oa',  //二维码
            ActivityType::TYPE_COUPON          => '',
            ActivityType::TYPE_WPA             => 'ad_campaign_other_oa',  //接待组件
        ];
    }
}