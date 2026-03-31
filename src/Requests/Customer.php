<?php

namespace Aoxiang\FengQiao\Requests;

/**
 * 顺丰联系人信息
 */
class Customer implements \JsonSerializable
{
    /**
     * 联系人类型
     * 1: 寄件人
     * 2: 收件人
     * @var int
     */
    public $contactType = 1;

    /**
     * 联系人姓名
     * @var string
     */
    public $contact = "";

    /**
     * 手机号码
     * @var string
     */
    public $mobile = "";

    /**
     * 固定电话（可选）
     * @var string
     */
    public $tel = "";

    /**
     * 国家代码
     * @var string
     */
    public $country = "CN";

    /**
     * 省份
     * @var string
     */
    public $province = "";

    /**
     * 城市
     * @var string
     */
    public $city = "";

    /**
     * 区县
     * @var string
     */
    public $county = "";

    /**
     * 详细地址
     * @var string
     */
    public $address = "";

    /**
     * 邮编
     * @var string
     */
    public $postCode = "";

    /**
     * 公司名称
     * @var string
     */
    public $company = "";

    /**
     * 兼容旧版构造函数
     *
     * @param string|null $contact 联系人姓名
     * @param string|null $address 地址
     * @param string|null $tel 电话
     * @param int $contactType 联系人类型
     */
    public function __construct(?string $contact = null, ?string $address = null, ?string $tel = null, int $contactType = 1)
    {
        if ($contact !== null) {
            $this->contact = $contact;
        }
        if ($address !== null) {
            $this->address = $address;
        }
        if ($tel !== null) {
            $this->tel = $tel;
            $this->mobile = $tel;
        }
        $this->contactType = $contactType;
    }

    /**
     * 设置联系人信息
     *
     * @param string $name 姓名
     * @param string $mobile 手机号
     * @return $this
     */
    public function setContact(string $name, string $mobile): self
    {
        $this->contact = $name;
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * 设置地址信息
     *
     * @param string $province 省份
     * @param string $city 城市
     * @param string $county 区县
     * @param string $address 详细地址
     * @return $this
     */
    public function setAddress(string $province, string $city, string $county, string $address): self
    {
        $this->province = $province;
        $this->city = $city;
        $this->county = $county;
        $this->address = $address;
        return $this;
    }

    /**
     * 序列化为 JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = [
            'contactType' => $this->contactType,
            'contact'     => $this->contact,
            'country'     => $this->country,
            'address'     => $this->address,
        ];

        // 添加手机号或电话
        if ($this->mobile) {
            $data['mobile'] = $this->mobile;
        }
        if ($this->tel) {
            $data['tel'] = $this->tel;
        }

        // 添加省市区（如果有）
        if ($this->province) {
            $data['province'] = $this->province;
        }
        if ($this->city) {
            $data['city'] = $this->city;
        }
        if ($this->county) {
            $data['county'] = $this->county;
        }

        // 可选字段
        if ($this->postCode) {
            $data['postCode'] = $this->postCode;
        }
        if ($this->company) {
            $data['company'] = $this->company;
        }

        return $data;
    }
}
