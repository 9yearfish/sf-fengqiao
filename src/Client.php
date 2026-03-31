<?php

namespace Aoxiang\FengQiao;

use Aoxiang\FengQiao\Requests\CargoDetails;
use Aoxiang\FengQiao\Requests\Customer;
use Aoxiang\FengQiao\Requests\Route;
class Client
{
    protected $partnerId;
    protected $checkWord;
    protected $serviceCode;
    protected $timestamp;
    protected $debug = false;
    protected $result = null;
    //沙箱环境的地址
    protected $sandBoxUrl = "https://sfapi-sbox.sf-express.com/std/service";
    //生产环境的地址
    protected $url = "https://sfapi.sf-express.com/std/service";

    /**
     * Client constructor.
     *
     * @param  string  $partnerId
     * @param  string  $checkWord
     * @param  bool    $debug
     */
    public function __construct(string $partnerId, string $checkWord, $debug = false)
    {
        $this->partnerId = $partnerId;
        $this->checkWord = $checkWord;
        $this->setDebug($debug);
    }

    

    /**
     *
     * @param                $orderId
     * @param  CargoDetails  货物信息 $cargoDetails
     * @param  Customer      发件人 $sendCustomer
     * @param  Customer      收件人 $recoverCustomer
     */
    public function createOrder($orderId, CargoDetails $cargoDetails, Customer $sendCustomer, Customer $recoverCustomer, array $options = [])
    {
        $data = [
            'orderId'           => $orderId,
            'cargoDetails'      => [$cargoDetails],
            'contactInfoList'   => [$sendCustomer, $recoverCustomer],
            'language'          => 'zh_CN',
            'isReturnWaybillNo' => 1,
        ];

        if( isset($options['monthlyCard']) && $options['monthlyCard'] ){
            $data['monthlyCard'] = $options['monthlyCard'];
        }
        if( isset($options['isDocall']) ){
            $data['isDocall'] = $options['isDocall'] ? 1 : 0;
        }
        if( isset($options['payMethod']) ){
            $data['payMethod'] = $options['payMethod'];
        }
        if( isset($options['expressTypeId']) ){
            $data['expressTypeId'] = $options['expressTypeId'];
        }
        if( isset($options['remark']) ){
            $data['remark'] = $options['remark'];
        }

        $this->setServiceCode('EXP_RECE_CREATE_ORDER')->request($data);

        return $this;
    }


    /**
     * @param $route
     *
     * @return $this
     * @throws FengQiaoException
     */
    public function getRoute($route)
    {
        if( !$route instanceof Route ){
            $route = new Route($route);
        }
        $this->setServiceCode('EXP_RECE_SEARCH_ROUTES')->request($route);

        return $this;
    }


    /**
     * 云打印面单转PDF接口
     * 返回面单的PDF Base64数据，可用于图片转换后打印
     *
     * @param  string  $waybillNo  运单号
     * @param  string  $templateCode  模板编码，如 fm_150_standard
     * @param  string  $version  版本号
     * @return $this
     * @throws FengQiaoException
     */
    public function getWaybillPdf(string $waybillNo, string $templateCode = 'fm_150_standard', string $version = '2.0', string $fileType = 'pdf')
    {
        $data = [
            'templateCode' => $templateCode,
            'version'      => $version,
            'fileType'     => $fileType,
            'sync'         => 1,
            'documents'    => [
                [
                    'masterWaybillNo' => $waybillNo,
                ],
            ],
        ];
        $this->setServiceCode('COM_RECE_CLOUD_PRINT_WAYBILLS')->request($data);

        return $this;
    }

    /**
     * 云打印面单转指令接口（CPCL/ZPL）
     * 返回打印机可直接执行的指令字符串
     * 预留接口，待购入支持CPCL的面单打印机后启用
     *
     * @param  string  $waybillNo  运单号
     * @param  string  $templateCode  模板编码，如 fm_150_standard_CPCL
     * @param  string  $version  版本号
     * @return $this
     * @throws FengQiaoException
     */
    public function getWaybillCommand(string $waybillNo, string $templateCode = 'fm_150_standard_CPCL', string $version = '2.0')
    {
        $data = [
            'templateCode' => $templateCode,
            'version'      => $version,
            'sync'         => 1,
            'documents'    => [
                [
                    'masterWaybillNo' => $waybillNo,
                ],
            ],
        ];
        $this->setServiceCode('COM_RECE_CLOUD_PRINT_COMMAND')->request($data);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->result->apiResultData->msgData ?? null;
    }

    /**
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param  string  $code
     *
     * @return $this
     */
    protected function setServiceCode(string $code)
    {
        $this->serviceCode = $code;

        return $this;
    }

    /**
     * @param $debug
     *
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @param $data
     *
     * @return mixed
     * @throws FengQiaoException
     */
    protected function request($data)
    {
        $data            = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->timestamp = time();
        $post_data       = [
            'partnerID'   => $this->partnerId,
            'requestID'   => $this->createUuid(),
            'serviceCode' => $this->serviceCode,
            'timestamp'   => $this->timestamp,
            'msgDigest'   => $this->createDigest($data),
            'msgData'     => $data,
        ];

        $postdata = http_build_query($post_data);
        $url      = $this->debug ? $this->sandBoxUrl : $this->url;
        $raw      = $this->post($url, $postdata);

        \Log::info('顺丰API原始返回', ['raw' => mb_substr($raw, 0, 2000)]);

        $result = json_decode($raw);
        if( !is_object($result) ){
            throw new FengQiaoException('数据解析失败: ' . substr($raw, 0, 200));
        }

        if( isset($result->apiResultData) ){
            if( is_string($result->apiResultData) ){
                $result->apiResultData = json_decode($result->apiResultData);
            }
        } else {
            $errorMsg  = isset($result->apiErrorMsg) ? $result->apiErrorMsg : '未知错误';
            $errorCode = isset($result->apiResultCode) ? $result->apiResultCode : '';
            throw new FengQiaoException('顺丰API错误[' . $errorCode . ']: ' . $errorMsg);
        }

        if( isset($result->apiResultData->success) && $result->apiResultData->success ){
            $this->result = $result;

            return $this->result;
        }

        $errorMsg = isset($result->apiResultData->errorMsg) ? $result->apiResultData->errorMsg : '未知业务错误';
        $errorCode = isset($result->apiResultData->errorCode) ? $result->apiResultData->errorCode : '';

        throw new FengQiaoException($errorMsg . ($errorCode ? " (Code: {$errorCode})" : ''));
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function createDigest($data)
    {
        return base64_encode(md5((urlencode($data . $this->timestamp . $this->checkWord)), true));
    }

    /**
     * @param  string  $url
     * @param  string  $postdata
     *
     * @return string
     * @throws FengQiaoException
     */
    protected function post($url, $postdata)
    {
        if( function_exists('curl_init') ){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-type:application/x-www-form-urlencoded;charset=utf-8',
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            if( defined('CURL_IPRESOLVE_V4') ){
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            if( $response === false ){
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                curl_close($ch);
                throw new FengQiaoException('网络请求失败: ' . $error . ($errno ? " (#{$errno})" : ''));
            }
            curl_close($ch);

            return $response;
        }

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded;charset=utf-8',
                'content' => $postdata,
                'timeout' => 15 * 60,
            ],
            'ssl'  => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];
        $context = stream_context_create($options);
        $result  = @file_get_contents($url, false, $context);

        if( $result === false ){
            $error   = error_get_last();
            $message = '网络请求失败';
            if( !empty($error['message']) ){
                $message .= '：' . $error['message'];
            }
            throw new FengQiaoException($message);
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function createUuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid  = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);

        return $uuid;
    }
}
