<?php
/**
 * auto generated.
 * Time: {{.Time}}
 */

namespace {{.Name}}\Mix\Base;

class InnerPBProtocol
{
    protected $cmd_route = [{{range .Cmds}}
        {{.Cmd}}=>'{{.Name}}',{{end}}
    ];
    protected $route = [{{range .Cmds}}
        '{{.Name}}'=>[
            'controller' => '',
            'action'     => '',
        ],{{end}}
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