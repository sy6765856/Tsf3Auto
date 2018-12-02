<?php
/**
 * auto generated.
 * Time: {{.Time}}
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
            'tid'    => '{{.Name}}',
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