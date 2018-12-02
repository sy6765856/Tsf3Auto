<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_marketing_t_campaign_relation extends \UdsRecord
{
    const FId                 = 'FId';
    const FKFUin              = 'FKFUin';
    const FCampaignId         = 'FCampaignId';
    const FType               = 'FType';
    const FRelatedFirstLevel  = 'FRelatedFirstLevel';
    const FRelatedSecondLevel = 'FRelatedSecondLevel';
    const FRelatedId          = 'FRelatedId';
    const FStatus             = 'FStatus';
    const FCreateTime         = 'FCreateTime';
    const FLastUpdateTime     = 'FLastUpdateTime';
    const FCost               = 'FCost';
    const FIndexId            = 'FIndexId';
    const FName               = 'FName';
    protected $fields = array(
        'FId',
        'FKFUin',
        'FCampaignId',
        'FType',
        'FRelatedFirstLevel',
        'FRelatedSecondLevel',
        'FRelatedId',
        'FStatus',
        'FCreateTime',
        'FLastUpdateTime',
        'FCost',
        'FIndexId',
        'FName',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_marketing';
    protected $tblBase = 't_campaign_relation';
    protected $multiDB = false;
    protected $multiTbl = true;
    public $FId = '';
    public $FKFUin = '';
    public $FCampaignId = '';
    public $FType = '';
    public $FRelatedFirstLevel = '';
    public $FRelatedSecondLevel = '';
    public $FRelatedId = '';
    public $FStatus = '';
    public $FCreateTime = '';
    public $FLastUpdateTime = '';
    public $FCost = '';
    public $FIndexId = '';
    public $FName = '';

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

    public function batchExecuteSql($sql = '')
    {
        \QdLogService::logDebug("batchExecuteSql start,mod={$this->connMod},udlMode={$this->udlMod}", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        //\QdLogService::logDebug("sql:" . print_r($sql, true), $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
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
        return $ret;
    }

    public function batchModifyByCampaignId($campaignId, $modifys, $kfuin, $relatedActivityInfo = [])
    {
        \QdLogService::logDebug("batchModifyByCampaignId begin", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        if (empty($campaignId)) {
            $campaignId = 0;
        }
        $sqls = [];

        //delete
        \QdLogService::logDebug("delete sql begin", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        $deleteCount = count($modifys['delete']);
        if ($deleteCount > 0) {
            $indexDeleteCount = 0; $countDeleteLimit = 500;
            do {
                $sqls[] = 'DELETE FROM ' . $this->tbl . ' WHERE FRelatedId iN ("' . implode('","', array_slice($modifys['delete'], $indexDeleteCount, $countDeleteLimit)) . '") and FKFUin=' . $kfuin;
                $indexDeleteCount+=$countDeleteLimit;
            } while($indexDeleteCount<$deleteCount);
        }

        //update
        \QdLogService::logDebug("update sql begin", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        $updateCount = count($modifys['update']);
        if ($updateCount > 0) {
            $indexUpdateCount = 0; $countUpdateLimit = 500;
            do {
                $sqls[] = 'UPDATE ' . $this->tbl . ' SET FCampaignId=' . $campaignId . " WHERE FRelatedId iN ('" . implode("','", array_slice($modifys['update'],$indexUpdateCount, $countUpdateLimit)) . "') and FKFUin=" . $kfuin;
                $indexUpdateCount+=$countUpdateLimit;
            } while($indexUpdateCount<$updateCount);

        }

        //insert
        \QdLogService::logDebug("insert sql begin", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        $insertCount = count($modifys['insert']);
        if ($insertCount > 0) {
            $indexInsertCount = 0; $countInsertLimit = 500;
            do {
                $sql     = 'INSERT INTO ' . $this->tbl . '(`FKFUin`,`FCampaignId`,`FRelatedId`,`FType`,`FCreateTime`,`FIndexId`,`FRelatedFirstLevel`, `FName`) VALUES ';
                $inserts = [];
                foreach (array_slice($modifys['insert'], $indexInsertCount, $countInsertLimit) as $item) {
                    $obj               = explode('_', $item);
                    $indexId           = $relatedActivityInfo[$item]['indexId'];
                    $relatedfirstlevel = $relatedActivityInfo[$item]['relatedfirstlevel'];
                    $name              = $relatedActivityInfo[$item]['name'];
                    $inserts[]         = "({$kfuin}, {$campaignId}, '{$item}', '{$obj[0]}', unix_timestamp(),'{$indexId}','{$relatedfirstlevel}','{$name}')";
                }
                $sqls[] = ($sql . implode(',', $inserts) . ";");
                $indexInsertCount+=$countInsertLimit;
            } while($indexInsertCount<$insertCount);
        }
        foreach ($sqls as $sql) {
            $ret = $this->batchExecuteSql($sql);
        }
        return $ret;
    }
}
