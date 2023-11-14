<?php

return [

    /**
     * |--------------------------------------------------------------------------
     * | 智能创作配置
     * |--------------------------------------------------------------------------
     * | definition:视频编码配置 ID，支持自定义创建，推荐优先使用系统预置的导出配置。
     * |   10：分辨率为 480P，输出视频格式为 MP4；
     * |   11：分辨率为 720P，输出视频格式为 MP4；
     * |   12：分辨率为 1080P，输出视频格式为 MP4。
     * |
     */
    'global' => [
        'auth' => ['auth.api'], // 路由中间件
        'definition' => 10,
        'exportDestination' => 'VOD',
        'version' => '2019-10-29',
        'host' => 'cme.tencentcloudapi.com'
    ],
    'cme' => [
        'secret_id' => '',
        'secret_key' => '',
        'platform' => env('TENCENT_VOD_PLATFORM', '1500004122'),
    ],
];