<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_search_engine_t_se_groups extends \UdsRecord
{
    const FId         = 'FId';
    const FKFUin      = 'FKFUin';
    const FEngineType = 'FEngineType';
    const FAccountId  = 'FAccountId';
    const FPlanId     = 'FPlanId';
    const FGroupId    = 'FGroupId';
    const FGroupName  = 'FGroupName';
    const FGroupData  = 'FGroupData';
    const FCreateTime = 'FCreateTime';
    const FModifyTime = 'FModifyTime';
    protected $fields = array(
        'FId',
        'FKFUin',
        'FEngineType',
        'FAccountId',
        'FPlanId',
        'FGroupId',
        'FGroupName',
        'FGroupData',
        'FCreateTime',
        'FModifyTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_search_engine';
    protected $tblBase = 't_se_groups';
    protected $multiDB = false;
    protected $multiTbl = true;
    public $FId = '';
    public $FKFUin = '';
    public $FEngineType = '';
    public $FAccountId = '';
    public $FPlanId = '';
    public $FGroupId = '';
    public $FGroupName = '';
    public $FGroupData = '';
    public $FCreateTime = '';
    public $FModifyTime = '';

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = null)
    {
        $conf = \CRMConst::getCCUdsNewL5();
        $conf = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        parent::__construct($kfuin, $kfext, $connMod, $udsMode, $conf);
        $this->tbl = $this->dbBase;
        if ($this->multiDB) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -3, 1));
        }
        $this->tbl .= '.' . $this->tblBase;
        if ($this->multiTbl) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -2));
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
