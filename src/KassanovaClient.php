<?php
namespace lightvoldemar\kassanovaBankApi\Kassanova;

use yii\httpclient\Client;

/**
 * Клиентский класс.
 */
class KassanovaClient
{
    /**
     * URL редиректа.
     */
    public $dataRedirectUrl = "ERROR";

    /**
     * Код платежа.
     */
    public $dataOrderSig;

    /**
     * URL успешной транзакции.
     */
    public $returnUrl;

    /**
     * URL не успешной транзакции.
     */
    public $failUrl;

    /**
     * Текущая валюта.
     */
    private $currency;

    /**
     * Текущий язык.
     */
    private $language;

    /**
     * API логин.
     */
    public $apiLogin;

    /**
     * API пароль.
     */
    public $apiPassword;

    /**
     * URL регистрации заказа.
     */
    public $registerUrl = 'https://3ds.kassanova.kz/payment/rest/register.do';
    
    /**
     * Доступные валюты (ISO 4217).
     *
     * @var array
     */
    protected $currencyEnum = array(
        840 => 'USD',
        398 => 'KZT',
    );

    /**
     * Доступные языки.
     *
     * @var array
     */
    protected $languageList = array(
        'en' => 'en',
        'ru' => 'ru',
    );

    /**
     * Конфигурация.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Конструктор.
     */
    public function __construct()
    {

    }

    /**
     * Возвращает ID валюты.
     *
     * @param  string $key
     * @return null|integer
     */
    public function getCurrencyId($key = 'KZT')
    {
        $types = array_flip($this->currencyEnum);

        return isset($types[$key]) ? $types[$key] : null;
    }

    /**
     * Задает указанный тип валюты.
     *
     * @param  string $key
     */
    public function setCurrency($key = 'KZT')
    {
        $types = array_flip($this->currencyEnum);

        $this->currency = isset($types[$key]) ? $types[$key] : null;
    }

    /**
     * Возвращает ID языка.
     *
     * @param  string $key
     * @return null|integer
     */
    public function getLangId($key = 'ru')
    {
        $types = array_flip($this->langList);

        return isset($types[$key]) ? $types[$key] : null;
    }

    /**
     * Задает указанный тип языка.
     *
     * @param  string $key
     */
    public function setLang($key = 'ru')
    {
        $types = array_flip($this->langList);

        $this->currency = isset($types[$key]) ? $types[$key] : null;
    }

    /**
     * Функция оплаты.
     *
     * @param int $amount сумма плетажа
     * @param int $orderId идентификатор ордера
     * @return boolean
     */
    public function pay($amount,$orderId) {
        $order['order_id'] = $orderId;
        $order['return_url'] = $this->returnUrl;
        $order['fail_url'] = $this->failUrl;
        $result = $this->registerOrder($amount,$orderId);
        $this->dataRedirectUrl = $result['formUrl'];
        $this->dataOrderSig = $result['orderId'];
    }

    /**
     * Регистрация заказа.
     *
     * @param int $amount сумма плетажа
     * @param int $orderId идентификатор ордера
     * @return object
     */
    private function registerOrder($amount,$orderId) {
        $data['amount'] = $amount."00";
        $data['currency'] = $this->currency;
        $data['language'] = $this->language;
        $data['orderNumber'] = $orderId;
        $data['userName'] = $this->apiLogin;
        $data['password'] = $this->apiPassword;
        $data['returnUrl'] = $this->returnUrl;
        $data['failUrl'] = $this->failUrl;

        return $this->sendRequest($this->registerUrl,$data);
    }

    
    private function reverseOrder() {
        $url_key = "reverse";
        /*
         * ?language=ru&
         * orderId=9231a838-ac68-4a3e-bddb-d9781433d852&
         * password=password&
         * userName=userName
         * */

        // Язык системы
        $arr[$this->requestParamsArr['LANG']] = $this->params['lang'];
        // ID заказа
        $arr[$this->requestParamsArr['ORDER_ID']] = '';
        // Логин
        $arr[$this->requestParamsArr['USERNAME']] = $this->params['username'];
        // Пароль
        $arr[$this->requestParamsArr['PASSWORD']] = $this->params['password'];

        $this->sendRequest($url_key,$arr);
    }

    private function refundOrder() {
        $url_key = "refund";

        /*
         * amount=500&
         * currency=643&
         * language=ru&
         * orderId=5e97e3fd-1d20-4b4b-a542-f5995f5e8208&
         * password=password&
         * userName=userName
         * */

        // Сумма возврата

        // Валюта
        //$arr[$this->requestParamsArr['CURRENCY']] = '';
        // Язык системы
        $arr[$this->requestParamsArr['LANG']] = $this->params['lang'];
        // ID заказа
        $arr[$this->requestParamsArr['ORDER_ID']] = '';
        // Логин
        $arr[$this->requestParamsArr['USERNAME']] = $this->params['username'];
        // Пароль
        $arr[$this->requestParamsArr['PASSWORD']] = $this->params['password'];

        // Отправка запроса
        $this->sendRequest($url_key,$arr);
    }

    private function getOrderStatus() {
        $url_key = "getOrderStatus";

        /*
         * orderId=b8d70aa7-bfb3-4f94-b7bb-aec7273e1fce&
         * language=ru&
         * password=password&
         * userName=userName
         * */

        // Язык системы
        $arr[$this->requestParamsArr['LANG']] = $this->params['lang'];
        // ID заказа
        $arr[$this->requestParamsArr['ORDER_ID']] = '';
        // Логин
        $arr[$this->requestParamsArr['USERNAME']] = $this->params['username'];
        // Пароль
        $arr[$this->requestParamsArr['PASSWORD']] = $this->params['password'];

        // Отправка запроса
        $this->sendRequest($url_key,$arr);
    }

    private function getOrderStatusExtended() {
        $url_key = "getOrderStatusExtended";

        /*
         * userName=userName&
         * password=password&
         * orderId=b9054496-c65a-4975-9418-1051d101f1b9&
         * language=ru&
         * merchantOrderNumber=0784sse49d0s134567890
         * */

        // Логин
        $arr[$this->requestParamsArr['USERNAME']] = $this->params['username'];
        // Пароль
        $arr[$this->requestParamsArr['PASSWORD']] = $this->params['password'];
        // ID заказа
        $arr[$this->requestParamsArr['ORDER_ID']] = '';
        // Язык системы
        $arr[$this->requestParamsArr['LANG']] = $this->params['lang'];
        // ID ордера по мерчанту
        $arr[$this->requestParamsArr['MERCHANT_ORDER_NUM']] = '';

        // Отправка запроса
        $this->sendRequest($url_key,$arr);
    }

    private function getLastOrdersForMerchants() {
        $url_key = "getLastOrdersForMerchants";

        /*
         * userName=userName&
         * password=password&
         * language=ru&
         * page=0&
         * size=100&
         * from=20141009160000&
         * to=20141111000000&
         * transactionStates=DEPOSITED,REVERSED&
         * merchants=SevenEightNine&
         * searchByCreatedDate=false
         * */

        // Логин
        $arr[$this->requestParamsArr['USERNAME']] = $this->params['username'];
        // Пароль
        $arr[$this->requestParamsArr['PASSWORD']] = $this->params['password'];
        // Язык системы
        $arr[$this->requestParamsArr['LANG']] = $this->params['lang'];
        // Номер страницы
        $arr[$this->requestParamsArr['PAGE']] = '';
        // Кол-во записей на  странице
        $arr[$this->requestParamsArr['SIZE']] = '';
        // Дата начала
        $arr[$this->requestParamsArr['FROM']] = '';
        // Дата окончания
        $arr[$this->requestParamsArr['TO']] = '';
        // Статусы заказов
        $arr[$this->requestParamsArr['transactionStates']] = '';
        // Список мерчантов
        //$arr[$this->requestParamsArr['merchants']] = '';
        // Использовать дату оплаты или дату создания заказов
        $arr[$this->requestParamsArr['searchByCreatedDate']] = 'false';

        // Отправка запроса
        $this->sendRequest($url_key,$arr);
    }

    private function verifyEnrollment() {
        $url_key = "verifyEnrollment";

        /*
         * userName=userName&
         * password=password&
         * pan=4111111111111111
         * */

        // Логин
        $arr[$this->requestParamsArr['USERNAME']] = $this->params['username'];
        // Пароль
        $arr[$this->requestParamsArr['PASSWORD']] = $this->params['password'];
        // Номер карты
        $arr[$this->requestParamsArr['PAN']] = '';

        // Отправка запроса
        $this->sendRequest($url_key,$arr);
    }

    /**
     * Отправка запроса.
     *
     * @param string $url адрес отправки запроса
     * @param array $data массив данных запроса
     * @return object
     */
    private function sendRequest($url,$data) {

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($url)
            ->setData($data)
            ->send();

        if($response->isOk) {
            return $response;
        }
    }
  
}
