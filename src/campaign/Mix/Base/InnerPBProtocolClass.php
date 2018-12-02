<?php
/**
 * Created by PhpStorm.
 * User: rocwu
 * Date: 2017/12/8
 * Time: 下午4:34
 */

namespace campaign_mix_svr\Mix\Base;

use Com\Tencent\Epc\Innerprocess;
use Qidian\Web\Marketing\Campaign;

class InnerPBProtocolClass
{
    public $cmd;
    public $subCmd;
    public $kfuin;
    public $kfext;
    public $seq;
    public $gseq;
    public $reqHead;
    public $reqBody;

    /**
     * @param $reqData
     * @throws \Exception
     */
    public function __construct($reqData)
    {
        $this->unpackRequestData($reqData);
    }

    /**
     * 解析请求包
     *
     * @param $data
     * @throws \Exception
     */
    public function unpackRequestData($data)
    {
        $stx     = substr($data, 0, 1);
        $cmd     = substr($data, 1, 4);
        $cmd     = unpack('Ncmd', $cmd);
        $cmd     = $cmd['cmd'];
        $seq     = substr($data, 5, 4);
        $seq     = unpack('Nseq', $seq);
        $seq     = $seq['seq'];
        $headLen = substr($data, 9, 4);
        $headLen = unpack('Nlen', $headLen);
        $headLen = $headLen['len'];
        $bodyLen = substr($data, 13, 4);
        $bodyLen = unpack('Nlen', $bodyLen);
        $bodyLen = $bodyLen['len'];
        $reqHead = substr($data, 17, $headLen);
        $reqBody = substr($data, 17 + intval($headLen), $bodyLen);
        $body    = new Campaign\ReqBody();
        $body->reset();
        try {
            $body->parseFromString($reqBody);
        } catch (\Exception $e) {
            throw new \Exception('body parse error', -1);
        }

        if (!$body) {
            throw new \Exception("pb data body params error ");
        }
        $etx = substr($data, -1);

        //判断是否是合法的包
        if ($stx != pack('C', 0x5b) || $etx != pack('C', 0x5d)) {
            throw new \Exception('pack parse error', -1);
        }

        $head = new Innerprocess\Head();
        $head->reset();
        try {
            $head->parseFromString($reqHead);
        } catch (\Exception $e) {
            throw new \Exception('head parse error', -1);
        }
        // get subcmd
        $corpInnerHead = $head->getInnerHead();
        $UinIds        = $corpInnerHead->getUinIds();
        $kfuin         = $UinIds->getUint64Kfuin();
        $strJsonTracing = $corpInnerHead->getMsgCcTracing()->getStrJsonTracing();
        $tracingArray = array();
        if (!empty($strJsonTracing)) {
            $tracingArray = json_decode($strJsonTracing, true);
        }
        $gseq = isset($tracingArray['gseq']) ? $tracingArray['gseq'] : 0;
        $kfext = isset($tracingArray['kfext']) ? $tracingArray['kfext'] : 0;

        $this->cmd     = $cmd;
        $this->seq     = $seq;
        $this->reqHead = $head;
        $this->reqBody = $body;
        $this->kfuin   = $kfuin;
        $this->gseq    = $gseq;
        $this->kfext   = $kfext;
    }

    /**
     * 获取对应的action
     *
     * @param InnerPBProtocol $protocol
     * @return mixed
     */
    public function getActionByCmd($protocol)
    {
        $config        = $protocol->getRoute($this->cmd);
        $config['buf'] = $this;
        return $config;
    }
}