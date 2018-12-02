<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:18:10.527011 +0800 CST m=+0.002216660
 */

namespace campaign\Mix\Base;

use TSF\Mix\Route;

class InnerPBRoute extends Route
{
    private $protocol;

    public function __construct()
    {
        $this->protocol = new InnerPBProtocol();
    }

    public function getRoute($data)
    {
        $pro                 = new InnerPBProtocolClass($data);
        $actionConf          = $pro->getActionByCmd($this->protocol);
        $actionConf['after'] = ['\EAServiceReport'];
        return $actionConf;
    }
}