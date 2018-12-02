<?php
/**
 * Created by URTools.
 * Date: 2016-01-19
 * Time: 17:50
 * Desc:
 */

namespace campaign_mix_svr\Mix\Model;

class UR_Crm3Mp_WpaInfo extends \UdsRecord
{
    const FID         = 'FID';
    const FKFUin      = 'FKFUin';
    const FKFExt      = 'FKFExt';
    const FKFEXTName  = 'FKFEXTName';
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
    const FMediaId    = 'FMediaId';
    const FFeatureBit = 'FFeatureBit';
    const FQrCodeImg  = 'FQrCodeImg';
    const FLinkUrl    = 'FLinkUrl';
    const FIsCorpUin  = 'FIsCorpUin';
    const FImUrl      = 'FImUrl';
    const FCustom     = 'FCustom';
    protected $fields = array(
        'FID',
        'FKFUin',
        'FKFExt',
        'FKFEXTName',
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
        'FCreateTime',
        'FUpdateTime',
        'FMediaId',
        'FFeatureBit',
        'FQrCodeImg',
        'FLinkUrl',
        'FIsCorpUin',
        'FImUrl',
        'FCustom',
    );
    protected $primaryKey = array('FID');
    protected $pkAuto = true;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_qidian_serv_tp';
    protected $tblBase = 't_wpa_info';
    protected $multiDB = true;
    protected $multiTbl = true;
    protected $udlMod = \WebUdlClientAdv::DIRECT_MYSQL;
    public $FID = null;
    public $FKFUin = null;
    public $FKFExt = 0;
    public $FKFEXTName = '';
    public $FType = 0;
    public $FSubType = 0;
    public $FScene = 0;
    public $FName = '';
    public $FTitle = '';
    public $FSignature = '';
    public $FBtnText = '';
    public $FAvatar = '';
    public $FPosition = 0;
    public $FBtnBgColor = '';
    public $FTheme = 0;
    public $FCreateTime = 0;
    public $FUpdateTime = 0;
    public $FMediaId = '';
    public $FFeatureBit = 0;
    public $FQrCodeImg = null;
    public $FLinkUrl = null;
    public $FIsCorpUin = 0;
    public $FImUrl = '';
    public $FCustom = '';

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = null)
    {
        parent::__construct($kfuin, $kfext, $connMod, $udsMode);

        $this->tbl = $this->dbBase;
        if ($this->multiDB) {
            $this->tbl .= '_' . (($kfuin / 100) % 10);
        }
        $this->tbl .= "." . $this->tblBase;
        if ($this->multiTbl) {
            $this->tbl .= '_' . ($kfuin % 100);
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
