<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:18:10.527011 +0800 CST m=+0.002216660
 */

class ConsoleQidianConfig extends \config\QidianConfig
{
    public function getDcConfig()
    {
        $conf = array(
            'dcid'      => 'dc03648',
            'self_dcid' => 'dc03613',
        );
        return $conf;
    }

    public function getMoConfig()
    {
        $conf = array(
            'bid'    => 'b_teg_rt_index',
            'sysId'  => 9900501,
            'tid'    => 'campaign',
            'intfId' => 3,
        );

        return $conf;
    }

    public function getUlsConfig()
    {
        $conf = array(
            'appid' => '0x950008',
        );
        return $conf;
    }
}