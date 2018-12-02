<?php
/**
 * auto generated.
 * Time: {{.Time}}
 */

namespace  {{.Name}}\Mix\Base;

class CampaignError
{
    /* 错误码定义 */

    // 系统级错误码 : 通用错误码0-10000
    const SUCCESS                 = 0; // 成功
    const SYS_ERR                 = 1; //系统错误
    const PARAMS_ERR              = 2; //参数错误
    const LOGIN_STATUS_TIMEOUNT   = 3; // 登录态超时
    const LOGIN_AUTH_ERROR        = 4; // 登录态错误
    const DIRTY_WORD_FOUND        = 5; // 敏感词错误
    const GET_CMLB_FAIL           = 6; // 从cmlb获取服务器信息失败
    const PERMISSION_FAIL         = 7;//没有权限
    const DIRTY_WORD_SERVER_ERROR = 8; //检查敏感词返回系统错误
    const DATA_SQL_SELECT_FAIL    = 9; //UDS查询返回系统错误
    const ACCOUNT_NOT_ACTIVE      = 10;//账号被停用
    const NON_QIDIAN_USER_LOGIN   = 11;   // 非企点QQ登录
    const ACCOUNT_API             = 12;//API账号
    const SEND_RECEIVE_ERR        = 13;//发送失败
    const DATA_DRUID_SEARCH_FAIL  = 14; //druid系统错误
    const DATA_DRUID_SYNTAX_ERROR = 15; //druid查询语法错误
    const NO_ACTIVITY_RELATED     = 16; //未关联任何活动
    const INVALID_SORT_LABEL      = 17; //非法排序列

    /* 错误消息定义 */
    protected static $errorMessages = array(
        self::SUCCESS               => '操作成功',
        self::SYS_ERR               => '系统异常，请稍后再试',
        self::LOGIN_STATUS_TIMEOUNT => '登录超时',
        self::LOGIN_AUTH_ERROR      => '登录态错误',
        self::PARAMS_ERR            => '请求参数错误',
        self::DIRTY_WORD_FOUND      => '参数含有敏感词',
        self::GET_CMLB_FAIL         => '从cmlb获取服务器配置失败',
        self::PERMISSION_FAIL       => '没有权限',
        self::ACCOUNT_NOT_ACTIVE    => '账号被停用',
        self::NO_ACTIVITY_RELATED   => '未关联任何活动',
        self::INVALID_SORT_LABEL    => '非法排序列',
    );

    public static function getErrorMessage($errorCode)
    {
        return isset(self::$errorMessages[$errorCode]) ? self::$errorMessages[$errorCode] : $errorCode;
    }
}