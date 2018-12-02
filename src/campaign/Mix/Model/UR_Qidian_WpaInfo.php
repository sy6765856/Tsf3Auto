<?php

namespace campaign_mix_svr\Mix\Model;

class UR_Qidian_WpaInfo extends \UdsRecord
{
    const FID         = 'FID';
    const FCode       = 'FCode';
    const FKFUin      = 'FKFUin';
    const FKFExt      = 'FKFExt';
    const FKFEXTName  = 'FKFEXTName';
    const FStatus     = 'FStatus';
    const FType       = 'FType';
    const FSubType    = 'FSubType';
    const FScene      = 'FScene';
    const FName       = 'FName';
    const FTitle      = 'FTitle';
    const FSignature  = 'FSignature';
    const FBtnText    = 'FBtnText';
    const FAvatar     = 'FAvatar';
    const FPosition   = 'FPosition';
    const FBtnBgColor = 'FBtnBgColor';
    const FTheme      = 'FTheme';
    const FCreateTime = 'FCreateTime';
    const FUpdateTime = 'FUpdateTime';
    protected $fields = array(
        'FID',
        'FCode',
        'FKFUin',
        'FKFExt',
        'FKFEXTName',
        'FStatus',
        'FType',
        'FSubType',
        'FScene',
        'FName',
        'FTitle',
        'FSignature',
        'FBtnText',
        'FAvatar',
        'FPosition',
        'FBtnBgColor',
        'FTheme',
        'FQrCodeImg',
        'FLinkUrl',
        'FCreateTime',
        'FUpdateTime',
    );
    protected $primaryKey = array('FID');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_qidian_cc_tp';
    protected $tblBase = 't_wpa_info';
    protected $multiDB = false;
    protected $multiTbl = true;
    public $FID = '';
    public $FCode = '';
    public $FKFUin = '';
    public $FKFExt = '';
    public $FKFEXTName = '';
    public $FStatus = '';
    public $FType = '';
    public $FSubType = '';
    public $FScene = '';
    public $FName = '';
    public $FTitle = '';
    public $FSignature = '';
    public $FBtnText = '';
    public $FAvatar = '';
    public $FPosition = '';
    public $FBtnBgColor = '';
    public $FTheme = '';
    public $FCreateTime = '';
    public $FUpdateTime = '';
    public $FQrCodeImg = '';
    public $FLinkUrl = '';

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = null)
    {
        parent::__construct($kfuin, $kfext, $connMod, $udsMode);

        $this->tbl = $this->dbBase;
        if ($this->multiDB) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -3, 1));
        }
        $this->tbl .= "." . $this->tblBase;
        if ($this->multiTbl) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -2));
        }
        //$this->udl          = new \WebUdlClientAdv('100.116.80.3', 8123);
        //$this->insertIgnore = true;
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
