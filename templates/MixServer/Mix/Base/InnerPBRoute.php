<?php
/**
 * auto generated.
 * Time: {{.Time}}
 */

namespace {{.Name}}\Mix\Base;

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