<?php

/**
 * Created by PhpStorm.
 * User: rocwu
 * Date: 2017/12/13
 * Time: 下午9:20
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
            //'modId' => 482497,
            //'cmdId' => 2424832,
            'modId' => 777409,
            'cmdId' => 131072,
        );
    }

    public static function getCCUdsConf()
    {
        $l5  = array(
            'cmdId' => 65536,
            'modId' => 808513,
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
            ActivityType::TYPE_KEYWORDS        => 'ad_campaign_other_oa', //搜索关键词
            ActivityType::TYPE_MASS_SMS        => 'ad_campaign_sms_task_oa_v1',  //短信任务
            ActivityType::TYPE_MP_ACCOUNT_MASS => 'weixin_pic_and_text_report_oa_v1',  //微信公众号
            ActivityType::TYPE_SPONSORED_LINK  => 'ad_campaign_other_oa',  //推广链接
            ActivityType::TYPE_QRCODE          => 'ad_campaign_other_oa',  //二维码
            ActivityType::TYPE_COUPON          => '',
            ActivityType::TYPE_WPA             => 'ad_campaign_other_oa',  //接待组件
            ActivityType::TYPE_WX_H5           => 'ad_campaign_weixin_h5_task_oa_v1', //微信H5任务
            ActivityType::TYPE_GDT             => 'ad_campaign_other_oa',  //广点通广告
        ];
    }

    public static function getSmsSvrConf()
    {
        $l5  = array(
            'modId' => 958977,
            'cmdId' => 655360,
        );
        $ret = L5Assistant::getRoute($l5['modId'], $l5['cmdId']);
        if ($ret['r'] === 0) {
            $conf['ip']   = $ret['ip'];
            $conf['port'] = $ret['port'];
        } else {
            $conf['ip']   = '10.100.71.194';
            $conf['port'] = '9697';
        }
        return $conf;
    }

    public static function getKeywordsSvrConf()
    {
        $l5  = array(
            'modId' => 1043457,
            'cmdId' => 458752,
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

    public static function getH5SvrConf()
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

    public static function getCampaignCKVConf()
    {
        //return array(
        //    'bid'    => 101020498,
        //    'l5Conf' => array(
        //        'modId' => 115841,
        //        'cmdId' => 65536,
        //    ),
        //);
        return array(
            'bid'    => 101023081,
            'l5Conf' => array(
                'modId' => 1062785,
                'cmdId' => 458752,
            ),
        );
    }
}