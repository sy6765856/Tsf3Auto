<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_search_engine_t_se_keywords extends \UdsRecord
{
    const FId         = 'FId';
    const FKFUin      = 'FKFUin';
    const FEngineType = 'FEngineType';
    const FAccountId  = 'FAccountId';
    const FPlanId     = 'FPlanId';
    const FGroupId    = 'FGroupId';
    const FWordId     = 'FWordId';
    const FWordName   = 'FWordName';
    const FWordData   = 'FWordData';
    const FCreateTime = 'FCreateTime';
    const FModifyTime = 'FModifyTime';
    protected $fields = array(
        'FId',
        'FKFUin',
        'FEngineType',
        'FAccountId',
        'FPlanId',
        'FGroupId',
        'FWordId',
        'FWordName',
        'FWordData',
        'FCreateTime',
        'FModifyTime',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_search_engine';
    protected $tblBase = 't_se_keywords';
    protected $multiDB = false;
    protected $multiTbl = true;
    public $FId = '';
    public $FKFUin = '';
    public $FEngineType = '';
    public $FAccountId = '';
    public $FPlanId = '';
    public $FGroupId = '';
    public $FWordId = '';
    public $FWordName = '';
    public $FWordData = '';
    public $FCreateTime = '';
    public $FModifyTime = '';

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = null)
    {
        $conf = \CRMConst::getCCUdsNewL5();
        $conf = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        parent::__construct($kfuin, $kfext, $connMod, $udsMode, $conf);
        $this->tbl = $this->dbBase;
        if ($this->multiDB) {
            $this->tbl .= '_' . intval(substr($this->kfuin, -3, 1));
        }
        $this->tbl .= '.' . $this->tblBase;
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

    public function getKeywordsByIdsTotal($idsArray) {
        $sql         = 'select count(1) as total from ' . $this->tbl . ' where FKFUin=' . $this->kfuin;
        $sqlKeys     = ['FEngineType', 'FAccountId', 'FPlanId', 'FGroupId', 'FWordId'];
        $orCondition = [];
        if (!empty($idsArray)) {
            foreach ($idsArray as $item) {
                $subcondition = [];
                $index        = 0;
                foreach ($item['ids'] as $id) {
                    $subcondition[] = "{$sqlKeys[$index]}='{$id}'";
                    $index++;
                }
                $orCondition[] = '(' . implode(' and ', $subcondition) . ')';
            }
        }
        $sql .= ' and (' . implode(' or ', $orCondition) . ');';
        \QdLogService::logDebug("batchExecuteSql start,mod={$this->connMod},udlMode={$this->udlMod}", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        \QdLogService::logDebug("sql:" . print_r($sql, true), $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        $ret = $this->udl->getResultWithTcp($sql, $this->udlMod);
        if ($ret['r'] != 0) {
            $this->setErr($ret['r'], 'uds execute failed');
            \QdLogService::logError("execute err:" . \Util::array2Str($ret), $this->kfuin, $this->kfext, $ret['r'], __CLASS__, __LINE__, __FUNCTION__);
            return array();
        }
        \QdLogService::logDebug("sql execute success:", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        return $ret['data']['rows'][0]['total'];
    }

    //'FWordData',
    public function getKeywordsByIds($idsArray, $limit = 2000)
    {
        $sql         = 'select FEngineType,FAccountId,FPlanId,FGroupId,FWordId,FWordName from ' . $this->tbl . ' where FKFUin=' . $this->kfuin;
        $sqlKeys     = ['FEngineType', 'FAccountId', 'FPlanId', 'FGroupId', 'FWordId'];
        $orCondition = [];
        if (!empty($idsArray)) {
            foreach ($idsArray as $item) {
                $subcondition = [];
                $index        = 0;
                foreach ($item['ids'] as $id) {
                    $subcondition[] = "{$sqlKeys[$index]}='{$id}'";
                    $index++;
                }
                $orCondition[] = '(' . implode(' and ', $subcondition) . ')';
            }
        }
        $sql .= ' and (' . implode(' or ', $orCondition) . ')  limit ' . $limit;
        \QdLogService::logDebug("batchExecuteSql start,mod={$this->connMod},udlMode={$this->udlMod}", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        \QdLogService::logDebug("sql:" . print_r($sql, true), $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        $ret = $this->udl->getResultWithTcp($sql, $this->udlMod);
        if ($ret['r'] != 0) {
            $this->setErr($ret['r'], 'uds execute failed');
            \QdLogService::logError("execute err:" . \Util::array2Str($ret), $this->kfuin, $this->kfext, $ret['r'], __CLASS__, __LINE__, __FUNCTION__);
            return array();
        }
        \QdLogService::logDebug("sql execute success:", $this->kfuin, $this->kfext, \BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        return $ret['data']['rows'];
    }
}
