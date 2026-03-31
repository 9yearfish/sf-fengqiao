<?php

namespace Aoxiang\FengQiao\Requests;

/**
 * 顺丰货物详情
 */
class CargoDetails implements \JsonSerializable
{
    /**
     * 货物名称
     * @var string
     */
    public $name = '';

    /**
     * 数量
     * @var int|float
     */
    public $count = 1;

    /**
     * 单位
     * @var string
     */
    public $unit = '个';

    /**
     * 重量(kg)
     * @var float
     */
    public $weight = 1.0;

    /**
     * 货物金额
     * @var float
     */
    public $amount = 0;

    /**
     * 货币类型
     * @var string
     */
    public $currency = 'CNY';

    /**
     * 原产地
     * @var string
     */
    public $sourceArea = 'CHN';

    /**
     * @param string|null $name 货物名称
     * @param float $weight 重量
     * @param int $count 数量
     */
    public function __construct(?string $name = null, float $weight = 1.0, int $count = 1)
    {
        if ($name !== null) {
            $this->name = $name;
        }
        $this->weight = $weight;
        $this->count = $count;
    }

    /**
     * 序列化为 JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = [
            'name' => $this->name,
        ];

        if ($this->count) {
            $data['count'] = $this->count;
        }
        if ($this->unit) {
            $data['unit'] = $this->unit;
        }
        if ($this->weight) {
            $data['weight'] = $this->weight;
        }
        if ($this->amount) {
            $data['amount'] = $this->amount;
            $data['currency'] = $this->currency;
        }
        if ($this->sourceArea) {
            $data['sourceArea'] = $this->sourceArea;
        }

        return $data;
    }
}