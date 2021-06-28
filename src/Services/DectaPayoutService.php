<?php


namespace Dnsoftware\Decta\Services;


use Dnsoftware\Decta\DectaAPI;
use Dnsoftware\Decta\DectaLogger;
use TusPhp\Request;

class DectaPayoutService
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
        'client'           => [
            'first_name' => 'Dmitry',
            'last_name'  => 'N',
            'birth_date' => '1983-01-12',
            "country"    => 'UA'
        ],
        'amount'         => '10.00',
        'currency'       => 'GBP'
    ];

    /**
     * DectaOrderService constructor.
     * @param $params начальные параметры заказа
     * передаем в параметрах
     */
    public function __construct($params = [])
    {

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
     *  Переход на страницу оплаты
     */
    public function init_b2p()
    {

        $result = $this->decta->call('POST', '/api/v0.6/orders/init_b2p/', $this->params);

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


}
