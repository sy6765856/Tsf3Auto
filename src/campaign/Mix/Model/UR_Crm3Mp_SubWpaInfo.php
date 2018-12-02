<?php
/**
 * Created by URTools.
 * Date: 2016-01-19
 * Time: 17:49
 * Desc:
 */

namespace campaign_mix_svr\Mix\Model;

class UR_Crm3Mp_SubWpaInfo extends \UdsRecord
{
    const FID         = 'FID';
    const FWpaId      = 'FWpaId';
    const FKFUin      = 'FKFUin';
    const FKFExt      = 'FKFExt';
    const FType       = 'FType';
    const FSubType    = 'FSubType';
    const FUin        = 'FUin';
    const FName       = 'FName';
    const FData       = 'FData';
    const FCreateTime = 'FCreateTime';
    protected $fields = array(
        'FID',
        'FWpaId',
        'FKFUin',
        'FKFExt',
        'FType',
        'FSubType',
        'FUin',
        'FName',
        'FData',
        'FCreateTime',
    );
    protected $primaryKey = array('FID');
    protected $pkAuto = true;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_qidian_serv_tp';
    protected $tblBase = 't_wpa_info_sub';
    protected $multiDB = true;
    protected $multiTbl = true;
    protected $udlMod = \WebUdlClientAdv::DIRECT_MYSQL;
    public $FID = null;
    public $FWpaId = null;
    public $FKFUin = null;
    public $FKFExt = 0;
    public $FType = null;
    public $FSubType = 0;
    public $FUin = 0;
    public $FName = '';
    public $FData = '';
    public $FCreateTime = 0;

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
        $this->udl          = new \WebUdlClientAdv('100.116.80.3', 8123);
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
