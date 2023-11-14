<?php

namespace Githen\LaravelTencentCme;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * 自动注册服务
 */
class CmeProvider extends ServiceProvider
{
    /**
     * 服务注册
     * @return void
     */
    public function register()
    {
        $this->updateFile();
    }

    /**
     * 服务启动
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('jiaoyu.tencent.cme', function () {
            return $this;
        });
    }

    /**
     * 发布文件
     * @return void
     */
    private function updateFile()
    {
        $this->publishes([__DIR__ . '/config/cme.php' => config_path('cme.php')]);
    }


    /**
     * ============================
     * 以下为逻辑代码
     */
    public function getSignature($userId, $projectId, $action = "OpenProject"): array
    {
        if (!$config = config('cme.cme')) {
            return $this->message(1, "获取配置文件失败：" . $label);
        }

        $current = time();
        $expired = $current + 300;  // 签名有效期：1天
        $args = array(
            "secretId" => config('cme.cme.secret_id', ''),
            "currentTimeStamp" => $current,
            "expireTime" => $expired,
            "random" => rand(),
            "platform" => config('cme.cme.platform', ''),
            "userId" => $userId,
            "action" => $action,
            "openProject.projectId" => $projectId,
        );
        // 计算签名
        $original = http_build_query($args);
        $signature = base64_encode(hash_hmac('SHA1', $original, config('cme.cme.secret_key', ''), true) . $original);

        return $this->message(0, '成功', ['sign' => $signature]);
    }

    /**
     * 创建项目
     * @param $params
     * @return array
     */
    public function CreateProject($name, $ownerId)
    {
        $params = [
            'Platform' => config('cme.cme.platform', ''),
            'Name' => $name,
            'Owner' => [
                'Type' => 'PERSON',
                'Id' => strval($ownerId),
            ],
            'Category' => 'VIDEO_EDIT',
            'Description' => "",
            'VideoEditProjectInput' => [
                'AspectRatio' => '16:9'
            ]
        ];
        $resp = $this->httpRequest('CreateProject', $params);
        return $resp;
    }

    public function ImportMediaToProject($projectId, $fileId)
    {
        $params = [
            'Platform' => config('cme.cme.platform', ''),
            'ProjectId' => $projectId,
            'VodFileId' => $fileId,
            'PreProcessDefinition' => 10
        ];
        $resp = $this->httpRequest('ImportMediaToProject', $params);
        return $resp;
    }

    public function ExportVideoEditProject($projectId, $projectName)
    {
        $params = [
            'Platform' => config('cme.cme.platform', ''),
            'ProjectId' => $projectId,
            'Definition' => config('cme.global.definition'),
            'ExportDestination' => config('cme.global.exportDestination'),
            'VODExportInfo' => [
                'Name' => $projectName,
            ],
        ];
        $resp = $this->httpRequest('ExportVideoEditProject', $params);
        return $resp;
    }

    private function getAuthorization($postData): string
    {
        $secretId = config('cme.cme.secret_id', '');
        $secretKey = config('cme.cme.secret_key', '');
        $timestamp = time();
        $service = "cme";
        $algorithm = "TC3-HMAC-SHA256";
        $host = config('cme.global.host', '');

        $httpRequestMethod = "POST";
        $canonicalUri = "/";
        $canonicalQueryString = "";
        $canonicalHeaders = "content-type:application/json; charset=utf-8\n" . "host:" . $host . "\n";
        $signedHeaders = "content-type;host";

        $payload = json_encode($postData);
        $hashedRequestPayload = hash("SHA256", $payload);
        $canonicalRequest = $httpRequestMethod . "\n"
            . $canonicalUri . "\n"
            . $canonicalQueryString . "\n"
            . $canonicalHeaders . "\n"
            . $signedHeaders . "\n"
            . $hashedRequestPayload;

        $date = gmdate("Y-m-d", $timestamp);
        $credentialScope = $date . "/" . $service . "/tc3_request";
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $stringToSign = $algorithm . "\n"
            . $timestamp . "\n"
            . $credentialScope . "\n"
            . $hashedCanonicalRequest;

        $secretDate = hash_hmac("SHA256", $date, "TC3" . $secretKey, true);
        $secretService = hash_hmac("SHA256", $service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $stringToSign, $secretSigning);

        return $algorithm
            . " Credential=" . $secretId . "/" . $credentialScope
            . ", SignedHeaders=content-type;host, Signature=" . $signature;
    }

    private function httpRequest($action, $params = [])
    {
        $host = config('cme.global.host', '');
        $url = "https://{$host}";
        try {
            $client = new Client();
            $resp = $client->request('POST', $url, ['verify' => false,
                //'debug'=>true,
                'headers' => ['Authorization' => $this->getAuthorization($params),
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Host' => $host,
                    'X-TC-Action' => $action,
                    'X-TC-Timestamp' => time(),
                    'X-TC-Version' => config('cme.global.version'),
                    'X-TC-Region' => ''],
                'json' => $params,
            ]);
            $content = $resp->getBody()->getContents();
            if (empty($content)) {
                return $this->message(1, '获取失败');
            }
            $aContent = json_decode($content, true);
            if (isset($aContent['Response']['Error'])) {
                return $this->message(1, $aContent['Response']['Error']['Message']??'');
            }
            return $this->message(0, '获取成功', $aContent);
        } catch (\Exception $e) {
            return $this->message(1, $e->getMessage());
        }
    }

    private function message($code, $message, $data = []): array
    {
        return ['code' => $code, 'message' => $message, 'data' => $data];
    }
}
