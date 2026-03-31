<?php

namespace Aoxiang\FengQiao\Requests;

/**
 * 顺丰云打印面单请求参数
 * 接口: COM_RECE_CLOUD_PRINT_WAYBILLS
 */
class WaybillPrint implements \JsonSerializable
{
    /**
     * 面单打印模板编码
     * 需要在顺丰开放平台申请审核通过
     * @var string
     */
    public $templateCode = '';

    /**
     * 版本号，固定2.0
     * @var string
     */
    public $version = '2.0';

    /**
     * 文件类型
     * pdf: PDF格式
     * image: 图片格式
     * @var string
     */
    public $fileType = 'pdf';

    /**
     * 同步/异步模式
     * 1: 同步（直接返回面单）
     * 0: 异步（返回任务ID，需要再次查询）
     * @var int
     */
    public $sync = 1;

    /**
     * 运单号列表
     * @var array
     */
    protected $waybillNos = [];

    /**
     * 文档数据
     * @var array
     */
    public $documents = [];

    /**
     * @param string|array $waybillNos 运单号，可以是单个或数组
     * @param string $templateCode 模板编码
     */
    public function __construct($waybillNos = null, string $templateCode = '')
    {
        if ($waybillNos) {
            $this->setWaybillNos($waybillNos);
        }
        if ($templateCode) {
            $this->templateCode = $templateCode;
        }
    }

    /**
     * 设置运单号
     * @param string|array $waybillNos
     * @return $this
     */
    public function setWaybillNos($waybillNos): self
    {
        if (is_array($waybillNos)) {
            $this->waybillNos = $waybillNos;
        } else {
            $this->waybillNos = [$waybillNos];
        }
        $this->buildDocuments();
        return $this;
    }

    /**
     * 添加运单号
     * @param string $waybillNo
     * @return $this
     */
    public function addWaybillNo(string $waybillNo): self
    {
        $this->waybillNos[] = $waybillNo;
        $this->buildDocuments();
        return $this;
    }

    /**
     * 设置模板编码
     * @param string $templateCode
     * @return $this
     */
    public function setTemplateCode(string $templateCode): self
    {
        $this->templateCode = $templateCode;
        return $this;
    }

    /**
     * 设置文件类型
     * @param string $fileType pdf 或 image
     * @return $this
     */
    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;
        return $this;
    }

    /**
     * 设置同步模式
     * @param bool $sync
     * @return $this
     */
    public function setSync(bool $sync): self
    {
        $this->sync = $sync ? 1 : 0;
        return $this;
    }

    /**
     * 构建文档数据
     */
    protected function buildDocuments(): void
    {
        $this->documents = [];
        foreach ($this->waybillNos as $waybillNo) {
            $this->documents[] = [
                'masterWaybillNo' => $waybillNo,
            ];
        }
    }

    /**
     * 序列化为 JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'templateCode' => $this->templateCode,
            'version'      => $this->version,
            'fileType'     => $this->fileType,
            'sync'         => $this->sync,
            'documents'    => $this->documents,
        ];
    }

    /**
     * 转换为数组
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
