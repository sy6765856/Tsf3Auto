<?php
/**
 * Your file description
 *
 * @author honsytshen
 * @date   2018/5/30
 */

namespace campaign_mix_svr\Mix\Base;

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
    //pb 错误
    const EMPTY_CREATE_INFO = 400;
    //业务错误
    const WRONG_ACTIVITY_TYPE           = 1001;
    const WRONG_LIST_LEVEL              = 1002;
    const CREATE_NAME_CONFLICT          = 1003;
    const MODIFY_BASE_INFO_FAILED       = 1004;
    const WRONG_WPA_TYPE                = 1005;
    const GET_BASE_INFO_FAILED          = 1006;
    const GET_RELATED_ACTIVITY_FAILED   = 1007;
    const GET_EA_WX_FAILED              = 1008;
    const CHECK_URL_FAILED              = 1009;
    const CHECK_URL_BLACK               = 1010;
    const GET_CCWPA_LIST_FAILED         = 1011;
    const GET_WPA_LIST_FAILED           = 1012;
    const ERROR_RELATED_ID              = 1013;
    const SAVE_COST_FAILED              = 1014;
    const SAVE_ACTIVITY_CAMPAIGN_FAILED = 1015;
    const GDT_ACCOUNT_NOT_EXIST         = 1016;
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

        self::CREATE_NAME_CONFLICT          => '计划名称已存在',
        self::MODIFY_BASE_INFO_FAILED       => '更新基础信息失败',
        self::WRONG_WPA_TYPE                => 'WPA类型错误',
        self::GET_BASE_INFO_FAILED          => '获取基础信息失败',
        self::DATA_DRUID_SEARCH_FAIL        => 'druid系统错误',
        self::DATA_DRUID_SYNTAX_ERROR       => 'druid查询语法错误',
        self::GET_RELATED_ACTIVITY_FAILED   => '获取关联活动失败',
        self::CHECK_URL_FAILED              => '检查url失败',
        self::CHECK_URL_BLACK               => '黑名单url',
        self::GET_CCWPA_LIST_FAILED         => '获取电话WPA列表失败',
        self::GET_WPA_LIST_FAILED           => '获取WPA列表失败',
        self::ERROR_RELATED_ID              => '错误的活动关联id',
        self::SAVE_COST_FAILED              => '保存活动总价失败',
        self::SAVE_ACTIVITY_CAMPAIGN_FAILED => '保存活动计划关系失败',
        self::GDT_ACCOUNT_NOT_EXIST         => '广点通账号不存在',
    );

    public static function getErrorMessage($errorCode)
    {
        return isset(self::$errorMessages[$errorCode]) ? self::$errorMessages[$errorCode] : $errorCode;
    }
}