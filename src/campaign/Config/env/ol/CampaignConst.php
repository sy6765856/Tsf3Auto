<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:18:10.527011 +0800 CST m=+0.002216660
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
            'modId' => 777409,
            'cmdId' => 131072,
        );
    }

    public static function getCCUdsConf()
    {
        $l5  = array(
            'cmdId' => 65536,
            'modId' => 816001,
        );
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '100.94.164.58';
            $conf['port'] = '8123';
        }
        return $conf;
    }

    public static function getEaWxSvrConf()
    {
        $setId = empty($_SERVER['QIDIAN_SET']) ? 0 : 1;//运营环境：灰度环境
        if ($setId === 1) {
            $l5 = array(
                'modId' => 1062785,
                'cmdId' => 196608,
            );
        } else {
            $l5 = array(
                'modId' => 963393,
                'cmdId' => 196608,
            );
        }
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '10.121.150.23';
            $conf['port'] = '9698';
        }
        return $conf;
    }

    public static function getSmsSvrConf()
    {
        $l5 = array(
            'modId' => '1073409',
            'cmdId' => '131072',
        );
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '100.116.160.162';
            $conf['port'] = '1234';
        }
        return $conf;
    }

    public static function getKeywordsSvrConf()
    {
        $l5 = array(
            'modId' => '1073409',
            'cmdId' => '131072',
        );
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '100.116.160.162';
            $conf['port'] = '1234';
        }
        return $conf;
    }

    public static function getH5SvrConf()
    {
        $setId = empty($_SERVER['QIDIAN_SET']) ? 0 : 1;//运营环境：灰度环境
        if ($setId === 1) {
            $l5 = array(
                'modId' => 1062785,
                'cmdId' => 196608,
            );
        } else {
            $l5 = array(
                'modId' => 963393,
                'cmdId' => 196608,
            );
        }
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '10.121.150.23';
            $conf['port'] = '9698';
        }
        return $conf;
    }

    public static function getAdL5Conf()
    {
        return array(
            'modId' => 459137,
            'cmdId' => 65536,
            'ifid'  => 142000935,
            'env'   => 0,
        );
    }

    public static function getDruidDataSource()
    {
        if ($_SERVER['QIDIAN_SET'] === '0') {
            return [
                ActivityType::TYPE_AD              => 'ad_campaign_other_ol_v1',  //广告跟踪
                ActivityType::TYPE_KEYWORDS        => 'ad_campaign_other_ol_v1',  //搜索关键词
                ActivityType::TYPE_MASS_SMS        => 'ad_campaign_sms_task_ol_v1',  //短信任务
                ActivityType::TYPE_MP_ACCOUNT_MASS => 'weixin_pic_and_text_report_ol_v1',  //微信公众号
                ActivityType::TYPE_SPONSORED_LINK  => 'ad_campaign_other_ol_v1',  //推广链接
                ActivityType::TYPE_QRCODE          => 'ad_campaign_other_ol_v1',  //二维码
                ActivityType::TYPE_COUPON          => '',
                ActivityType::TYPE_WPA             => 'ad_campaign_other_ol_v1',  //接待组件
                ActivityType::TYPE_WX_H5           => 'ad_campaign_weixin_h5_task_ol_v1', //微信H5任务
                ActivityType::TYPE_GDT             => 'ad_campaign_other_ol_v1',  //广点通广告
            ];
        } else {
            return [
                ActivityType::TYPE_AD              => 'ad_campaign_other_gray_v1',  //广告跟踪
                ActivityType::TYPE_KEYWORDS        => 'ad_campaign_other_gray_v1',  //搜索关键词
                ActivityType::TYPE_MASS_SMS        => 'ad_campaign_sms_task_gray_v1', //短信任务
                ActivityType::TYPE_MP_ACCOUNT_MASS => 'weixin_pic_and_text_report_gray_v1',  //微信公众号
                ActivityType::TYPE_SPONSORED_LINK  => 'ad_campaign_other_gray_v1',  //推广链接
                ActivityType::TYPE_QRCODE          => 'ad_campaign_other_gray_v1',  //二维码
                ActivityType::TYPE_COUPON          => '',
                ActivityType::TYPE_WPA             => 'ad_campaign_other_gray_v1',  //接待组件
                ActivityType::TYPE_WX_H5           => 'ad_campaign_weixin_h5_task_gray_v1', //微信H5任务
                ActivityType::TYPE_GDT             => 'ad_campaign_other_gray_v1',  //广点通广告
            ];
        }
    }

    public static function getCampaignCKVConf()
    {
        return array(
            'bid'    => 101023081,
            'l5Conf' => array(
                'modId' => 1062785,
                'cmdId' => 458752,
            ),
        );
    }
}