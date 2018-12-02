<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_base_ad_t_gdt_info extends \UdsRecord
{
    const FID                  = 'FId';
    const FDevId               = 'FDevId';
    const FUin                 = 'FUin';
    const FGdtAccountId        = 'FGdtAccountId';
    const FGdtUasi             = 'FGdtUasi';
    const FGdtAuthInfo         = 'FGdtAuthInfo';
    const FAccessToken         = 'FAccessToken';
    const FAccessTokenExpires  = 'FAccessTokenExpires';
    const FRefreshToken        = 'FRefreshToken';
    const FRefreshTokenExpires = 'FRefreshTokenExpires';
    const FStatus              = 'FStatus';
    const FCreateTime          = 'FCreateTime';
    const FUpdateTime          = 'FUpdateTime';
    protected $fields = array(
        'FId',
        'FDevId',
        'FUin',
        'FGdtAccountId',
        'FGdtUasi',
        'FGdtAuthInfo',
        'FAccessToken',
        'FAccessTokenExpires',
        'FRefreshToken',
        'FRefreshTokenExpires',
        'FStatus',
        'FCreateTime',
        'FUpdateTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = true;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_base_ad';
    protected $tblBase = 't_gdt_info';
    protected $multiDB = false;
    protected $multiTbl = false;
    public $FId = 0; // 自增ID
    public $FDevId = 0; //开发者应用id
    public $FUin = 0; //企点主号
    public $FGdtAccountId = 0; //推广帐号id
    public $FGdtUasi = ''; // 用户行为数据来源user_action_set_id
    public $FGdtAuthInfo = ''; //推广帐号授权信息
    public $FAccessToken = ''; //推广账户access_token
    public $FAccessTokenExpires = ''; // 推广账户access_token有效时间：秒
    public $FRefreshToken = ''; // 推广帐号refresh_token
    public $FRefreshTokenExpires = ''; // 推广帐号refresh_token有效时间：秒
    public $FStatus = ''; // 1-正常；2-失效
    public $FCreateTime = ''; //创建时间
    public $FUpdateTime = ''; //更新时间

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = 0)
    {
        $conf = \CRMConst::getAdUdsL5Conf();
        $conf = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        \QdLogService::logInfo("info conf: " . \Util::array2Str($conf));
        parent::__construct($kfuin, $kfext, $connMod, $udsMode, $conf);

        $this->tbl = $this->dbBase;
        if ($this->multiDB) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -3, 1));
        }
        $this->tbl .= "." . $this->tblBase;
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
