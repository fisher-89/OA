<?php

return [
    /**
     * 服务端接口地址
     */
    'server_api' => 'https://oapi.dingtalk.com/',
    /**
     * 企业ID
     */
    'CorpId' => env('DINGTALK_CORPID'),
    /**
     * 企业密钥
     */
    'CorpSecret' => env('DINGTALK_CORPSECRET'),
    /**
     * 微应用ID
     */
    'agentId' => '',
    /**
     * 签名随机字符串
     */
    'nonceStr' => env('DINGTALK_NONCESTR'),
    /**
     * 加解密token
     */
    'token' => env('DINGTALK_TOKEN'),
    /**
     * 加解密密钥，必须为43位
     */
    'AESKey' => env('DINGTALK_AESKEY'),
];
