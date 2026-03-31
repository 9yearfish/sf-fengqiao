<?php

namespace Aoxiang\FengQiao\Responses;

/**
 * 顺丰云打印面单响应
 */
class WaybillResponse
{
    /**
     * 原始响应数据
     * @var object
     */
    protected $raw;

    /**
     * 是否成功
     * @var bool
     */
    public $success = false;

    /**
     * 面单文件列表
     * @var array
     */
    public $files = [];

    /**
     * 下载Token（用于下载PDF）
     * @var string
     */
    public $token = '';

    /**
     * 错误信息
     * @var string
     */
    public $errorMsg = '';

    /**
     * 错误代码
     * @var string
     */
    public $errorCode = '';

    /**
     * @param object $result API返回的原始结果
     */
    public function __construct($result = null)
    {
        if ($result) {
            $this->parse($result);
        }
    }

    /**
     * 解析响应数据
     * @param object $result
     * @return $this
     */
    public function parse($result): self
    {
        $this->raw = $result;

        if (isset($result->apiResultData)) {
            $data = $result->apiResultData;

            if (isset($data->success) && $data->success) {
                $this->success = true;

                if (isset($data->obj)) {
                    $obj = $data->obj;

                    // 获取Token
                    $this->token = $obj->token ?? '';

                    // 获取文件列表
                    if (isset($obj->files) && is_array($obj->files)) {
                        foreach ($obj->files as $file) {
                            // 每个文件有自己的token
                            $fileToken = $file->token ?? $this->token;
                            $this->files[] = [
                                'waybillNo' => $file->waybillNo ?? '',
                                'url'       => $file->url ?? '',
                                'token'     => $fileToken,
                            ];
                            // 如果顶层token为空，使用第一个文件的token
                            if (empty($this->token) && !empty($fileToken)) {
                                $this->token = $fileToken;
                            }
                        }
                    }
                }
            } else {
                $this->success = false;
                $this->errorMsg = $data->errorMsg ?? '未知错误';
                $this->errorCode = $data->errorCode ?? '';
            }
        }

        return $this;
    }

    /**
     * 是否成功
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * 获取第一个面单的下载URL
     * @return string
     */
    public function getFirstFileUrl(): string
    {
        if (!empty($this->files)) {
            return $this->files[0]['url'] ?? '';
        }
        return '';
    }

    /**
     * 获取所有面单文件
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * 获取下载Token
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * 获取原始响应
     * @return object
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * 转换为数组
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success'   => $this->success,
            'files'     => $this->files,
            'token'     => $this->token,
            'errorMsg'  => $this->errorMsg,
            'errorCode' => $this->errorCode,
        ];
    }
}
