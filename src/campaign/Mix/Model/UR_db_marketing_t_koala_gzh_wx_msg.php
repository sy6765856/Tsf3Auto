<?php

namespace campaign_mix_svr\Mix\Model;

class UR_db_marketing_t_koala_gzh_wx_msg extends \UdsRecord
{
    const FId          = 'FId';
    const FTsId        = 'FTsId';
    const FAppId       = 'FAppId';
    const FKFUin       = 'FKFUin';
    const FTagId       = 'FTagId';
    const FTagName     = 'FTagName';
    const FTagType     = 'FTagType';
    const FCreateTime  = 'FCreateTime';
    const FStatus      = 'FStatus';
    const FSendType    = 'FSendType';
    const FResult      = 'FResult';
    const FMsgDataId   = 'FMsgDataId';
    const FContentType = 'FContentType';
    const FContentData = 'FContentData';
    const FVersion     = 'FVersion';
    protected $fields = array(
        'FId',
        'FTsId',
        'FAppId',
        'FKFUin',
        'FTagId',
        'FTagName',
        'FTagType',
        'FCreateTime',
        'FStatus',
        'FSendType',
        'FResult',
        'FMsgDataId',
        'FContentType',
        'FContentData',
        'FVersion',
    );
    protected $primaryKey = array('FId');
    protected $pkAuto = false;
    protected $enableNumProtect = self::NUMBER_PROTECT_OFF;
    protected $dbBase = 'db_marketing';
    protected $tblBase = 't_koala_gzh_wx_msg';
    protected $multiDB = false;
    protected $multiTbl = true;
    public $FId = '';
    public $FTsId = '';
    public $FAppId = '';
    public $FKFUin = '';
    public $FTagId = '';
    public $FTagName = '';
    public $FTagType = '';
    public $FCreateTime = '';
    public $FStatus = '';
    public $FSendType = '';
    public $FResult = '';
    public $FMsgDataId = '';
    public $FContentType = '';
    public $FContentData = '';
    public $FVersion = '';

    public function __construct($kfuin = null, $kfext = null, $connMod = null, $udsMode = 0)
    {
        parent::__construct($kfuin, $kfext, $connMod, $udsMode);

        if ($this->multiTbl) {
            $this->tblBase .= '_' . intval(substr($this->kfuin, -2));
        }

        $this->tbl          = $this->dbBase . "." . $this->tblBase;
        $conf               = \EACRMConst::getCCUdsNewL5();
        $conf               = \L5Assistant::getRoute($conf['modId'], $conf['cmdId']);
        $this->udl          = new \WebUdlClientAdvMR($conf['ip'], $conf['port']);
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

    public function batchInsertExecute($sql = '')
    {
        CrmLogService::logDebug("batchInsert execute start,mod={$this->connMod},udlMode={$this->udlMod}", $this->kfuin, $this->kfext, BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        if ($this->connMod == self::UDS_MOD_UDP) {
            $ret = $this->udl->getResult($sql, $this->udlMod);
        } else {
            $ret = $this->udl->getResultWithTcp($sql, $this->udlMod);
        }
        if ($ret['r'] != 0) {
            $this->setErr($ret['r'], 'uds execute failed');
            CrmLogService::logError("excute err:" . Util::array2Str($ret), $this->kfuin, $this->kfext, $ret['r'], __CLASS__, __LINE__, __FUNCTION__);
            return array();
        }
        CrmLogService::logDebug("batchInsert execute success:", $this->kfuin, $this->kfext, BaseError::SUCCESS, __CLASS__, __LINE__, __FUNCTION__);
        return $ret;
    }

    public function batchInsert($msgData)
    {
        $sql = '';
        //多图文拆分成多条插入数据库
        foreach ($msgData['content']['message'] as $article) {
            $sql .= $this->formatInsertSql($article, $msgData);
        };
        return $this->batchInsertExecute($sql);
    }

    public function formatInsertSql($article, $msgData)
    {

        $content   = json_encode([
            'type'    => $msgData['content']['type'],
            'message' => $article,
        ]);
        $sqlFormat = 'INSERT INTO ' . $this->tbl . ' (`FTsId`,`FAppId`,`FKFUin`,`FTagId`,`FTagName`,`FTagType`,`FCreateTime`,`FStatus`,`FSendType`,`FResult`,`FMsgDataId`,`FContentType`, `FContentData`, `FVersion`) VALUES ("%d", "%s", %d, "%d","%s","%s","%s","%d","%s","%s","%d", "%d","%s","%d");';
        $sql       = sprintf($sqlFormat, addslashes($msgData['ts_id']), addslashes($msgData($msgData['appid'])), addslashes($msgData['uin']), addslashes($msgData['tag_id']), addslashes($msgData['tag_name']), addslashes($msgData['tag_type']), addslashes($msgData['create_time']), addslashes($msgData['status']), addslashes($msgData['send_type']), json_encode($msgData['result']), addslashes($msgData['msg_data_id']), addslashes($msgData['content']['type']), $content, addslashes($msgData['modify_micro_time']));
        //CrmLogService::logInfo('OrderDetail Batch sql '. $sql);
        return $sql;
    }

    public static function msgTypeMap()
    {
        return array('text' => 1, 'image' => 2, 'news' => 3, 'voice' => 4, 'card' => 5);
    }
}
