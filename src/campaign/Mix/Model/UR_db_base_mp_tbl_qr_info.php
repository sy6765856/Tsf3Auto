<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_base_mp_tbl_qr_info extends \UdsRecord
{
    const FId         = 'FId';
    const FTId        = 'FTId';
    const FType       = 'FType';
    const FValue      = 'FValue';
    const FCreateTime = 'FCreateTime';
    const FUpdateTime = 'FUpdateTime';
    protected $fields = array(
        'FId',
        'FTId',
        'FType',
        'FValue',
        'FCreateTime',
        'FUpdateTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_base_mp';
    protected $tblBase = 'tbl_qr_info';
    protected $multiDB = false;
    protected $multiTbl = false;
    public $FId = '';
    public $FTId = '';
    public $FType = '';
    public $FValue = '';
    public $FCreateTime = '';
    public $FUpdateTime = '';

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
        parent::__construct($kfuin, $kfext, $connMod, $udsMode, \CampaignConst::getAdL5Conf());

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
