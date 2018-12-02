<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_marketing_t_campaign extends \UdsRecord
{
    const FId                   = 'FId';
    const FKFUin                = 'FKFUin';
    const FName                 = 'FName';
    const FDescription          = 'FDescription';
    const FBeginTime            = 'FBeginTime';
    const FEndTime              = 'FEndTime';
    const FStatus               = 'FStatus';
    const FCreateTime           = 'FCreateTime';
    const FLastModifier         = 'FLastModifier';
    const FLastUpdateTime       = 'FLastUpdateTime';
    const FVisitNum             = 'FVisitNum';
    const FNewCustomerNum       = 'FNewCustomerNum';
    const FCost                 = 'FCost';
    const FStatisticsUpdateTime = 'FStatisticsUpdateTime';
    protected $fields = array(
        'FId',
        'FKFUin',
        'FName',
        'FDescription',
        'FBeginTime',
        'FEndTime',
        'FStatus',
        'FCreateTime',
        'FLastModifier',
        'FLastUpdateTime',
        'FVisitNum',
        'FNewCustomerNum',
        'FCost',
        'FStatisticsUpdateTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = true;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_marketing';
    protected $tblBase = 't_campaign';
    protected $multiDB = false;
    protected $multiTbl = false;
    public $FId = '';
    public $FKFUin = '';
    public $FName = '';
    public $FDescription = '';
    public $FBeginTime = '';
    public $FEndTime = '';
    public $FStatus = '';
    public $FCreateTime = '';
    public $FLastModifier = '';
    public $FLastUpdateTime = '';
    public $FVisitNum = '';
    public $FNewCustomerNum = '';
    public $FCost = '';
    public $FStatisticsUpdateTime = '';

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

    public function searchCampaignByName($keyword, $start, $count)
    {
        $sql = 'SELECT FId,FName,FDescription,FBeginTime,FEndTime,FCreateTime,FLastModifier,FVisitNum,FNewCustomerNum,FCost,FStatisticsUpdateTime FROM ' . $this->tbl . ' WHERE FStatus=0 AND FKFUin=' . $this->kfuin .' AND FName LIKE "%' . addslashes($keyword) . '%"' . " limit $start, $count";
        return $this->executeSql($sql);
    }

    public function countCampaignByName($keyword)
    {
        $sql = 'SELECT count(1) FROM ' . $this->tbl . ' WHERE FStatus=0 AND FName LIKE "%' . addslashes($keyword) . '%"';
        $ret = $this->executeSql($sql);
        if (!is_array($ret[0])) {
            return 0;
        }
        $tmp = array_values($ret[0]);
        return $tmp[0] ? intval($tmp[0]) : 0;
    }

    public function executeSql($sql = '')
    {
        \QdLogService::logDebug("batchInsert execute start,mod={$this->connMod},udlMode={$this->udlMod},sql={$sql}", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        if ($this->connMod == self::UDS_MOD_UDP) {
            $ret = $this->udl->getResult($sql, $this->udlMod);
        } else {
            $ret = $this->udl->getResultWithTcp($sql, $this->udlMod);
        }
        if ($ret['r'] != 0) {
            $this->setErr($ret['r'], 'uds execute failed');
            \QdLogService::logError("execute err:" . \Util::array2Str($ret), $this->kfuin, $this->kfext, $ret['r'], __CLASS__, __LINE__, __FUNCTION__);
            return array();
        }
        \QdLogService::logDebug("batchInsert execute success:", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        return $ret['data']['rows'];
    }
}
