<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_search_engine_t_corp_se_account extends \UdsRecord
{
    const FId                  = 'FId';
    const FKFUin               = 'FKFUin';
    const FEngineType          = 'FEngineType';
    const FAccountName         = 'FAccountName';
    const FAccountData         = 'FAccountData';
    const FStatus              = 'FStatus';
    const FOpenSyncSwitchTime  = 'FOpenSyncSwitchTime';
    const FAutoSync            = 'FAutoSync';
    const FLastSyncTime        = 'FLastSyncTime';
    const FLastSyncSuccessTime = 'FLastSyncSuccessTime';
    const FLastSyncStatus      = 'FLastSyncStatus';
    const FLastSyncMsg         = 'FLastSyncMsg';
    const FCreateTime          = 'FCreateTime';
    const FModifyTime          = 'FModifyTime';
    protected $fields = array(
        'FId',
        'FKFUin',
        'FEngineType',
        'FAccountName',
        'FAccountData',
        'FStatus',
        'FOpenSyncSwitchTime',
        'FAutoSync',
        'FLastSyncTime',
        'FLastSyncSuccessTime',
        'FLastSyncStatus',
        'FLastSyncMsg',
        'FCreateTime',
        'FModifyTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_search_engine';
    protected $tblBase = 't_corp_se_account';
    protected $multiDB = false;
    protected $multiTbl = false;
    public $FId = '';
    public $FKFUin = '';
    public $FEngineType = '';
    public $FAccountName = '';
    public $FAccountData = '';
    public $FStatus = '';
    public $FOpenSyncSwitchTime = '';
    public $FAutoSync = '';
    public $FLastSyncTime = '';
    public $FLastSyncSuccessTime = '';
    public $FLastSyncStatus = '';
    public $FLastSyncMsg = '';
    public $FCreateTime = '';
    public $FModifyTime = '';

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = 0)
    {
        $conf = \CRMConst::getCCUdsNewL5();
        $conf = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        parent::__construct($kfuin, $kfext, $connMod, $udsMode, $conf);
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
