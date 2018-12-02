<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/7/20
 * campaign 对外活动通信实现接口
 */

namespace campaign_mix_svr\Mix\Model;

interface ActivityInterface
{
    public function __construct($kfuin, $kfext = 0, $seq = '');

    public function getList($start, $count, $keyword = '');
}