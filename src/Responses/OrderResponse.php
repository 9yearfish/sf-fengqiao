<?php

namespace Aoxiang\FengQiao\Responses;

/**
 * 顺丰下单响应
 */
class OrderResponse
{
    /**
     * 原始响应数据
     * @var object
     */
    protected $raw;

    /**
     * 顺丰运单号
     * @var string
     */
    public $waybillNo = '';

    /**
     * 客户订单号
     * @var string
     */
    public $orderId = '';

    /**
     * 顺丰筛选单号（用于取消订单）
     * @var string
     */
    public $filterResult = '';

    /**
     * 预估运费
     * @var float
     */
    public $estimatedFee = 0;

    /**
     * 是否成功
     * @var bool
     */
    public $success = false;

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

                if (isset($data->msgData)) {
                    $msgData = $data->msgData;

                    // 获取运单号
                    if (isset($msgData->waybillNoInfoList) && !empty($msgData->waybillNoInfoList)) {
                        $waybillInfo = $msgData->waybillNoInfoList[0];
                        $this->waybillNo = $waybillInfo->waybillNo ?? '';
                    }

                    // 获取订单号
                    $this->orderId = $msgData->orderId ?? '';

                    // 获取筛选结果
                    $this->filterResult = $msgData->filterResult ?? '';

                    // 预估运费
                    if (isset($msgData->estimatedDeliverTime)) {
                        // 某些返回可能包含预估信息
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
     * 获取运单号
     * @return string
     */
    public function getWaybillNo(): string
    {
        return $this->waybillNo;
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
            'success'      => $this->success,
            'waybillNo'    => $this->waybillNo,
            'orderId'      => $this->orderId,
            'filterResult' => $this->filterResult,
            'errorMsg'     => $this->errorMsg,
            'errorCode'    => $this->errorCode,
        ];
    }
}
