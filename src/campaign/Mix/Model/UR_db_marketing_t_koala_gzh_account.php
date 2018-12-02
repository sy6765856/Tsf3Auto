<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_marketing_t_koala_gzh_account extends \UdsRecord
{
    const FId              = 'FId';
    const FAppId           = 'FAppId';
    const FAppName         = 'FAppName';
    const FKFUin           = 'FKFUin';
    const FServiceTypeInfo = 'FServiceTypeInfo';
    const FVerifyTypeInfo  = 'FVerifyTypeInfo';
    const FCreateTime      = 'FCreateTime';
    protected $fields = array(
        'FId',
        'FAppId',
        'FAppName',
        'FKFUin',
        'FServiceTypeInfo',
        'FVerifyTypeInfo',
        'FCreateTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_marketing';
    protected $tblBase = 't_koala_gzh_account';
    protected $multiDB = false;
    protected $multiTbl = false;
    public $FId = '';
    public $FAppId = '';
    public $FAppName = '';
    public $FKFUin = '';
    public $FServiceTypeInfo = '';
    public $FVerifyTypeInfo = '';
    public $FCreateTime = '';

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = 0)
    {
        parent::__construct($kfuin, $kfext, $connMod, $udsMode, \CampaignConst::getCCUdsConf());

        $this->tbl = $this->dbBase;
        if ($this->multiDB) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -3, 1));
        }
        $this->tbl .= "." . $this->tblBase;
        if ($this->multiTbl) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -2));
        }

        $conf               = \CRMConst::getCCUdsNewL5();
        $conf               = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        $this->udl          = new \WebUdlClientAdv($conf['ip'], $conf['port']);
        $this->insertIgnore = true;
    }
}
