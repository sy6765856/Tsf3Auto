<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/20
 */

namespace campaign_mix_svr\Mix\Model;

abstract class ActivityBaseModel implements ActivityInterface
{
    protected $kfuin;
    protected $kfext;
    protected $seq;
    protected $ipPort;

    public function __construct($kfuin, $kfext = 0, $seq = '')
    {
        $this->kfuin = $kfuin;
        $this->kfext = $kfext;
        if (empty($seq)) {
            $seq = mt_rand();
        }
        $this->seq = $seq;
        $this->setIpPort();
    }

    abstract function setIpPort();

    protected function send($cmd, $reqBody, $useTcp = false)
    {
        \QdLogService::logInfo("send in, cmd:{$cmd},useTcp:{$useTcp}", $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        $sndData = $this->packRequestData($cmd, $reqBody);
        if ($useTcp) {
            $ret = \TcpClient::send($this->ipPort['ip'], $this->ipPort['port'], $sndData, 5, function($data) {
                if (strlen($data) < 17) {
                    return false;
                }
                $headLen = substr($data, 9, 4);
                $headLen = unpack('Nlen', $headLen);
                $headLen = $headLen['len'];
                $bodyLen = substr($data, 13, 4);
                $bodyLen = unpack('Nlen', $bodyLen);
                $bodyLen = $bodyLen['len'];
                return strlen($data) === 18 + $headLen + $bodyLen;
            });
        } else {
            $ret = \UdpClient::send($this->ipPort['ip'], $this->ipPort['port'], $sndData, 5);
        }
        \QdLogService::logInfo("send ret, cmd:{$cmd},useTcp:{$useTcp},ret:" . print_r($ret, true) . ',ip:' . print_r($this->ipPort, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
        if ($ret['r'] != \BaseError::SUCCESS) {
            \QdLogService::logError("request failed. cmd:{$cmd},useTcp:{$useTcp},ret:" . print_r($ret, true) . ',ipPort:' . print_r($this->ipPort, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
            throw new \Exception('request failed');
        }
        $rspBody = $this->unpackResponseData($ret['data']);
        return $rspBody;
    }

    /**
     * 打包
     *
     * @param $cmd
     * @param $reqBody
     * @return string
     */
    protected function packRequestData($cmd, $reqBody)
    {
        $head          = new \Com\Tencent\Epc\Innerprocess\Head();
        $corpInnerHead = new \Com\Tencent\Epc\Innerprocess\CorpInnerHead();
        $corpInnerHead->setUint32HeadVer(1);
        $corpInnerHead->setUint32Seq($this->seq);
        $corpInnerHead->setUint32Cmd($cmd);
        $uinIds = new \Com\Tencent\Epc\Innerprocess\UinIDs();
        $uinIds->setUint64Kfuin($this->kfuin);
        $corpInnerHead->setUinIds($uinIds);
        $head->setUint64ProtoType(0x2);
        $head->setInnerHead($corpInnerHead);
        $headBuf = $head->serializeToString();
        $bodyBuf = $reqBody->serializeToString();
        $sndData = pack('C', 0x5b) . pack('N', $cmd) . pack('N', $this->seq) . pack('N', strlen($headBuf)) . pack('N', strlen($bodyBuf)) . $headBuf . $bodyBuf . pack('C', 0x5d);
        return $sndData;
    }

    /**
     * 解析返回包
     *
     * @param $data
     * @return string 包体
     * @throws string 解包异常
     */
    protected function unpackResponseData($data)
    {
        $stx = substr($data, 0, 1);
        //$cmd     = substr($data, 1, 4);
        //$cmd     = unpack('Ncmd', $cmd);
        //$cmd     = $cmd['cmd'];
        //$seq     = substr($data, 5, 4);
        //$seq     = unpack('Nseq', $seq);
        //$seq     = $seq['seq'];
        $headLen = substr($data, 9, 4);
        $headLen = unpack('Nlen', $headLen);
        $headLen = $headLen['len'];
        $bodyLen = substr($data, 13, 4);
        $bodyLen = unpack('Nlen', $bodyLen);
        $bodyLen = $bodyLen['len'];
        //$reqHead = substr($data, 17, $headLen);
        $rspBody = substr($data, 17 + intval($headLen), $bodyLen);
        $etx     = substr($data, -1);
        //判断是否是合法的包
        if ($stx != pack('C', 0x5b) || $etx != pack('C', 0x5d)) {
            //throw new \Exception('pack parse error', -1);
        }
        return $rspBody;
    }
}