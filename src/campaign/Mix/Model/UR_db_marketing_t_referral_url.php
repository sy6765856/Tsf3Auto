<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_marketing_t_referral_url extends \UdsRecord
{
    const FId             = 'FId';
    const FKFUin          = 'FKFUin';
    const FUrl            = 'FUrl';
    const FStatus         = 'FStatus';
    const FCreateTime     = 'FCreateTime';
    const FLastUpdateTime = 'FLastUpdateTime';
    protected $fields = array(
        'FId',
        'FKFUin',
        'FUrl',
        'FStatus',
        'FCreateTime',
        'FLastUpdateTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = true;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_marketing';
    protected $tblBase = 't_referral_url';
    protected $multiDB = false;
    protected $multiTbl = true;
    public $FId = '';
    public $FKFUin = '';
    public $FUrl = '';
    public $FStatus = '';
    public $FCreateTime = '';
    public $FLastUpdateTime = '';

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

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
