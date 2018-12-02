<?php
/**
 * auto generated.
 * Time: 2018-12-03 01:08:18.037558 +0800 CST m=+0.001939780
 */

namespace campaign\Mix\Base;

use TSF\Stream\Scanner;

class InnerPBScanner extends Scanner
{
    //ready, next
    public function scan($data)
    {
        if (strlen($data) < 18) {
            return ['ready' => null, 'next' => $data];
        }
        $headLen = substr($data, 9, 4);
        $headLen = unpack('Nlen', $headLen);
        $headLen = $headLen['len'];
        $bodyLen = substr($data, 13, 4);
        $bodyLen = unpack('Nlen', $bodyLen);
        $bodyLen = $bodyLen['len'];
        $currLen = 18 + $headLen + $bodyLen;
        if ($currLen > strlen($data)) {
            return ['ready' => null, 'next' => $data];
        }
        return ['ready' => substr($data, 0, $currLen), 'next' => substr($data, $currLen)];
    }
}