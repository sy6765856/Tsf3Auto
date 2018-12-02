<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_base_mp_tbl_qr_list extends \UdsRecord
{
    const FId         = 'FId';
    const FTId        = 'FTId';
    const FCampaign   = 'FCampaign';
    const FName       = 'FName';
    const FUrl        = 'FUrl';
    const FSource     = 'FSource';
    const FMedium     = 'FMedium';
    const FLogo       = 'FLogo';
    const FLogo2      = 'FLogo2';
    const FColor      = 'FColor';
    const FDesc       = 'FDesc';
    const FImageUrl   = 'FImageUrl';
    const FQrUrl      = 'FQrUrl';
    const FCreateTime = 'FCreateTime';
    const FUpdateTime = 'FUpdateTime';
    const FType       = 'FType';
    const FIsOneCode  = 'FIsOneCode';
    const FWxName     = 'FWxName';
    const FWxRawId    = 'FWxRawId';
    const FWxAppId    = 'FWxAppId';
    const FWxSceneId  = 'FWxSceneId';
    const FStatus     = 'FStatus';
    protected $fields = array(
        'FId',
        'FTId',
        'FCampaign',
        'FName',
        'FUrl',
        'FSource',
        'FMedium',
        'FLogo',
        'FLogo2',
        'FColor',
        'FDesc',
        'FImageUrl',
        'FQrUrl',
        'FCreateTime',
        'FUpdateTime',
        'FType',
        'FIsOneCode',
        'FWxName',
        'FWxRawId',
        'FWxAppId',
        'FWxSceneId',
        'FStatus',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_base_mp';
    protected $tblBase = 'tbl_qr_list';
    protected $multiDB = false;
    protected $multiTbl = false;
    public $FId = '';
    public $FTId = '';
    public $FCampaign = '';
    public $FName = '';
    public $FUrl = '';
    public $FSource = '';
    public $FMedium = '';
    public $FLogo = '';
    public $FLogo2 = '';
    public $FColor = '';
    public $FDesc = '';
    public $FImageUrl = '';
    public $FQrUrl = '';
    public $FCreateTime = '';
    public $FUpdateTime = '';
    public $FType = '';
    public $FIsOneCode = '';
    public $FWxName = '';
    public $FWxRawId = '';
    public $FWxAppId = '';
    public $FWxSceneId = '';
    public $FStatus = '';

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
