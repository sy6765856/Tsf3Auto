<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/8/28
 */
namespace campaign_mix_svr\Mix\Model;
class CoroutineTaskManager {

    private $kfuin;
    private $kfext;
    private $maxCorNum = 10;

    private $class;
    private $action;

    private $tasksParams;
    private $timeout = 5;

    private $sleepSeconds = 0.01;
    private $querySeconds = 0.2;


    public function __construct($class, $action, $tasksParams, $kfuin=0, $kfext=0) {
        $this->class = $class;
        $this->action = $action;
        $this->tasksParams = $tasksParams;
        $this->kfuin = $kfuin;
        $this->kfext = $kfext;
    }

    public function setQuerySeconds($seconds) {
        $this->querySeconds = $seconds;
    }

    public function setMaxCorNum($maxCorNum) {
        if($maxCorNum > 62) {
            return false;
        }
        $this->maxCorNum = $maxCorNum;
        return true;
    }

    public function exec() {
        $subResponses = [];
        $mutiReq = 0;
        $mutiRsp = 0;
        $index = 0;
        $startTime = time();
        foreach ($this->tasksParams as $params) {
            $mutiReq |= (1<<$index);
            \Swoole\Coroutine::create(function() use ($index, $params, &$subResponses, &$mutiRsp){
                $subResponses[$index] = $this->class->{$this->action}($params);
                $mutiRsp |= (1<<$index);
                \QdLogService::logDebug("mutiRsp:" . print_r($mutiRsp, true), $this->kfuin, $this->kfext, 0, __CLASS__, __LINE__, __METHOD__);
                \Swoole\Coroutine::sleep($this->sleepSeconds);
            });
            $index ++;
        }
        while ($startTime + $this->timeout > time()) {
            \Swoole\Coroutine::sleep($this->querySeconds);
            if($mutiReq == $mutiRsp) {
                return $subResponses;
            }
        }
        return [];
    }
}