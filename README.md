# laravel-TencentVod

基于laravel的腾讯智能创作

[![image](https://img.shields.io/github/stars/jiaoyu-cn/laravel-tencent-cme)](https://github.com/jiaoyu-cn/laravel-tencent-cme/stargazers)
[![image](https://img.shields.io/github/forks/jiaoyu-cn/laravel-tencent-cme)](https://github.com/jiaoyu-cn/laravel-tencent-cme/network/members)
[![image](https://img.shields.io/github/issues/jiaoyu-cn/laravel-tencent-cme)](https://github.com/jiaoyu-cn/laravel-tencent-cme/issues)

## 安装

```shell
composer require githen/laravel-tencent-cme:~v1.0.0

# 迁移配置文件
php artisan vendor:publish --provider="Githen\LaravelTencentCme\CmeProvider"
```

## 配置文件说明

生成`cme.php`上传配置文件

```php
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
        'platform' => env('TENCENT_VOD_PLATFORM', ''),
    ],
];
```

## 支持方法

### 获取项目签名 getSignature($userId, $projectId, $action = "OpenProject", string $label = 'cme')

| 参数         | 类型        | 说明                   |
|------------|-----------|----------------------|
| $userId    | 必填：String | 所属人id                |
| $projectId | String    | cme项目id              |
| $action    | String    | 操作OpenProject、Upload |
| $label     | String    | 配置项名默认cme            |

### 创建cme项目 CreateProject($name, $ownerId)

| 参数       | 类型        | 说明        |
|----------|-----------|-----------|
| $name    | 必填：String | cme项目名称   |
| $ownerId | String    | 所属人id     |
| $label   | String    | 配置项名默认cme |

### 在项目中导入媒体 ImportMediaToProject($projectId, $fileId, string $label = 'cme')

| 参数         | 类型        | 说明         |
|------------|-----------|------------|
| $projectId | 必填：String | cme项目id    |
| $fileId    | String    | 云点播媒资文件 Id |
| $label     | String    | 配置项名默认cme  |

### 项目导出 ExportVideoEditProject($projectId, $projectName, string $label = 'cme')

| 参数           | 类型        | 说明        |
|--------------|-----------|-----------|
| $projectId   | 必填：String | cme项目id   |
| $projectName | String    | 导出名称      |
| $label       | String    | 配置项名默认cme |

### 获取任务详情 DescribeTaskDetail($taskId, string $label = 'cme')

| 参数      | 类型        | 说明        |
|---------|-----------|-----------|
| $taskId | 必填：String | 任务id      |
| $label  | String    | 配置项名默认cme |
