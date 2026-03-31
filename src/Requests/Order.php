<?php

namespace Aoxiang\FengQiao\Requests;

/**
 * 顺丰下单请求参数
 * 接口: EXP_RECE_CREATE_ORDER
 */
class Order implements \JsonSerializable
{
    /**
     * 客户订单号（必填）
     * @var string
     */
    public $orderId;

    /**
     * 快件产品类别（必填）
     * 1: 顺丰标快
     * 2: 顺丰特惠
     * 6: 顺丰即日
     * @var int
     */
    public $expressTypeId = 1;

    /**
     * 付款方式（必填）
     * 1: 寄方付
     * 2: 收方付
     * 3: 第三方付
     * @var int
     */
    public $payMethod = 1;

    /**
     * 月结卡号
     * 使用月结付款时必填
     * @var string
     */
    public $monthlyCard = '';

    /**
     * 是否通知收派员上门取件
     * 1: 通知
     * 0或不传: 不通知
     * @var int
     */
    public $isDocall = 0;

    /**
     * 是否返回运单号
     * 1: 返回
     * @var int
     */
    public $isReturnWaybillNo = 1;

    /**
     * 语言
     * zh_CN: 中文
     * @var string
     */
    public $language = 'zh_CN';

    /**
     * 货物详情（必填）
     * @var array
     */
    public $cargoDetails = [];

    /**
     * 联系人信息（必填）
     * 包含发件人和收件人
     * @var array
     */
    public $contactInfoList = [];

    /**
     * 备注
     * @var string
     */
    public $remark = '';

    /**
     * 寄件人信息
     * @var Customer|null
     */
    protected $sender;

    /**
     * 收件人信息
     * @var Customer|null
     */
    protected $receiver;

    /**
     * @param string $orderId 客户订单号
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * 设置月结卡号
     * @param string $monthlyCard
     * @return $this
     */
    public function setMonthlyCard(string $monthlyCard): self
    {
        $this->monthlyCard = $monthlyCard;
        return $this;
    }

    /**
     * 设置快件产品类型
     * @param int $type 1:标快 2:特惠 6:即日
     * @return $this
     */
    public function setExpressType(int $type): self
    {
        $this->expressTypeId = $type;
        return $this;
    }

    /**
     * 设置付款方式
     * @param int $method 1:寄付 2:到付 3:第三方付
     * @return $this
     */
    public function setPayMethod(int $method): self
    {
        $this->payMethod = $method;
        return $this;
    }

    /**
     * 设置是否通知快递员上门取件
     * @param bool $docall
     * @return $this
     */
    public function setIsDocall(bool $docall): self
    {
        $this->isDocall = $docall ? 1 : 0;
        return $this;
    }

    /**
     * 设置备注
     * @param string $remark
     * @return $this
     */
    public function setRemark(string $remark): self
    {
        $this->remark = $remark;
        return $this;
    }

    /**
     * 设置发件人
     * @param Customer $sender
     * @return $this
     */
    public function setSender(Customer $sender): self
    {
        $sender->contactType = 1; // 1: 发件人
        $this->sender = $sender;
        return $this;
    }

    /**
     * 设置收件人
     * @param Customer $receiver
     * @return $this
     */
    public function setReceiver(Customer $receiver): self
    {
        $receiver->contactType = 2; // 2: 收件人
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * 添加货物详情
     * @param CargoDetails $cargo
     * @return $this
     */
    public function addCargo(CargoDetails $cargo): self
    {
        $this->cargoDetails[] = $cargo;
        return $this;
    }

    /**
     * 快速创建货物详情并添加
     * @param string $name 货物名称
     * @param float $weight 重量(kg)
     * @param int $count 数量
     * @return $this
     */
    public function setCargo(string $name, float $weight = 1.0, int $count = 1): self
    {
        $cargo = new CargoDetails();
        $cargo->name = $name;
        $cargo->weight = $weight;
        $cargo->count = $count;
        $this->cargoDetails[] = $cargo;
        return $this;
    }

    /**
     * 序列化为 JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        // 构建联系人列表
        $this->contactInfoList = [];
        if ($this->sender) {
            $this->contactInfoList[] = $this->sender;
        }
        if ($this->receiver) {
            $this->contactInfoList[] = $this->receiver;
        }

        $data = [
            'orderId'           => $this->orderId,
            'expressTypeId'     => $this->expressTypeId,
            'payMethod'         => $this->payMethod,
            'isReturnWaybillNo' => $this->isReturnWaybillNo,
            'language'          => $this->language,
            'cargoDetails'      => $this->cargoDetails,
            'contactInfoList'   => $this->contactInfoList,
        ];

        // 可选参数
        if ($this->monthlyCard) {
            $data['monthlyCard'] = $this->monthlyCard;
        }
        if ($this->isDocall) {
            $data['isDocall'] = $this->isDocall;
        }
        if ($this->remark) {
            $data['remark'] = $this->remark;
        }

        return $data;
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
