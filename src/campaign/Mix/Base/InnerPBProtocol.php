<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:08:18.037558 +0800 CST m=+0.001939780
 */

namespace campaign\Mix\Base;

class InnerPBProtocol
{
    protected $cmd_route = [
        310150=>'CMD_GET_LIST',
        310151=>'CMD_GET_DETAIL',
        310152=>'CMD_CREATE_ONE',
        310153=>'CMD_DELETE_ONE',
        310154=>'CMD_MODIFY_INFO',
        310155=>'CMD_GET_ACTIVITIES_LIST',
        310156=>'CMD_GET_RELATED_ACTIVITIES',
        310157=>'CMD_GET_ALL_CHANNEL_SUMMARY',
        310158=>'CMD_GET_CHANNEL_DETAIL',
        310159=>'CMD_SAVE_REFERRAL_URL',
        310160=>'CMD_SAVE_ACTIVITY_COST',
        310161=>'CMD_ASSOCIATE_ACTIVITY_CAMPAIGN',
        310162=>'CMD_GET_CAMPAIGN_BY_ACTIVITY',
        310163=>'CMD_GET_ACTIVITIES_BY_UPDATA',
        310164=>'CMD_GET_ACTIVITIES_BY_FATHERIDS',
    ];
    protected $route = [
        'CMD_GET_LIST'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_DETAIL'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_CREATE_ONE'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_DELETE_ONE'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_MODIFY_INFO'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_ACTIVITIES_LIST'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_RELATED_ACTIVITIES'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_ALL_CHANNEL_SUMMARY'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_CHANNEL_DETAIL'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_SAVE_REFERRAL_URL'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_SAVE_ACTIVITY_COST'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_ASSOCIATE_ACTIVITY_CAMPAIGN'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_CAMPAIGN_BY_ACTIVITY'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_ACTIVITIES_BY_UPDATA'=>[
            'controller' => '',
            'action'     => '',
        ],
        'CMD_GET_ACTIVITIES_BY_FATHERIDS'=>[
            'controller' => '',
            'action'     => '',
        ],
    ];

    public function getRoute($cmd)
    {
        $cmd_controller = $this->cmd_route[$cmd];
        if (!isset($this->route[$cmd_controller])) {
            return [
                'controller' => 'Error',
                'action'     => 'Error',
            ];
        }
        return $this->route[$cmd_controller];
    }
}
