<?php


namespace Dnsoftware\Decta\Services;


use Dnsoftware\Decta\DectaAPI;
use Dnsoftware\Decta\DectaLogger;
use TusPhp\Request;

class DectaOrderService
{
    /**
     * @var DectaAPI
     */
    private $decta;

    /**
     * @var array параметры для отправки на api Decta
     *      см. раздел Order на странице https://gate.decta.com/api/
     */
    private $params = [
        'number' => '',                    // номер заказа
        'referrer' => 'Laravel package',
        'language' => '',
        'success_redirect' => '',          // URL куда Decta отправляет уведомление об успешной оплате
        'failure_redirect' => '',          // URL куда Decta отправляет уведомление об неудачной оплате
        'currency'         => 'RUB',       // валюта заказа
        'client'           => [],          // данные клиента
        'products'         => [],          // массив данных по кажому товару в заказе
    ];

    /**
     * DectaOrderService constructor.
     * @param $params начальные параметры заказа
     * передаем в параметрах number, success_redirect, failure_redirect, currency
     */
    public function __construct($params)
    {
        $this->params['number'] = $params['number'];
        $this->params['language'] = app()->getLocale();
        $this->params['success_redirect'] = $params['success_redirect'];
        $this->params['failure_redirect'] = $params['failure_redirect'];
        $this->params['currency'] = $params['currency'];

        $this->decta = new DectaAPI(
            config('dectamerchant.decta_private_key'),
            config('dectamerchant.decta_public_key'),
            new DectaLogger('dummy', false)
        );
    }


    /**
     * @param $params - параметры клиента
     *   $params['email'] - мыло, обязательное
     *   $params['phone'] - телефон, обязательное
     *   $params['first_name'] - имя
     *   $params['last_name'] - фамилия
     *   $params['send_to_email'] - отправлять ли на почту клиенту
     */
    public function setClient($params)
    {
        $findUser = $this->decta->getUser($params['email'], $params['phone']);
        if(!$findUser){
            if($this->decta->createUser($params)){
                $findUser = $this->decta->getUser($params['email'], $params['phone']);
            }
        }
        $params['original_client'] = $findUser['id'];

        $this->params['client'] = $params;
    }

    /**
     * @param $product - данные по продукту (строка в заказе)
     *  $product['title'] - название, обязательное
     *  $product['price'] - цена, обязательное
     *  $product['quantity'] - кол-во (по умолчанию 1)
     *  $product['total'] - Calculated as quantity * price of this OrderProduct
     */
    public function addProduct($product)
    {
        $this->params['products'][] = $product;
    }


    /**
     *  Переход на страницу оплаты
     */
    public function sendOrder()
    {
        if (count($this->params['client']) <= 0) {
            abort(200, 'Не указан клиент!');
        }

        if (count($this->params['products']) <= 0) {
            abort(200, 'Покупки отсутствуют!');
        }

        $result = $this->decta->call('POST', '/api/v0.6/orders/', $this->params);

        return $result;

    }


    /**
     * @param $order_id - внутренний ID платежа
     * @param $payment_id - ID платежа в Decta
     */
    public static function wasPaymentSuccessful($order_id, $payment_id)
    {
        $decta = new DectaAPI(
            config('dectamerchant.decta_private_key'),
            config('dectamerchant.decta_public_key'),
            new DectaLogger('dummy', false)
        );

        if ($decta->was_payment_successful($order_id, $payment_id)) {
            file_put_contents(base_path().'/decta.log', 'OK - '.json_encode($order_id) . PHP_EOL, FILE_APPEND | LOCK_EX);
            return true;
        } else {
            file_put_contents(base_path().'/decta.log', 'ERROR - '.json_encode($order_id) . PHP_EOL, FILE_APPEND | LOCK_EX);
            return false;
        }

    }

    public static function getPaymentInfo($payment_id) {
        $decta = new DectaAPI(
            config('dectamerchant.decta_private_key'),
            config('dectamerchant.decta_public_key'),
            new DectaLogger('dummy', false)
        );

        $info = $decta->get_payment_info($payment_id);
        dd($info);

    }


}
