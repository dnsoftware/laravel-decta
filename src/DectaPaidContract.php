<?php



/**
 *  Контракт который нужно реализовать на стороне клиента
 *  Реализация методов зависит от конкретной внутренней структуры БД
 *  и алгоритмов приложения клиента
 *  Перед внедрением в контроллер нужно связать этот контракт со своей реализацией
 *  для этого в сервис-провайдер в метод "public function register()" добавляем строку
 *  $this->app->bind(DectaPaidContract::class, DectaPaidService::class);
 *  где DectaPaidService - ваша реализация контракта
 */

namespace Dnsoftware\Decta;


interface DectaPaidContract
{
    /**
     * Пометка ордера/заказа и т.п. как "Оплаченный"
     * @param $order_id - внутренний идентификатор заказа (ордера и т.п.)
     * @param $params - доп. параметры передаваемые платежной системой (ID заказа в платежной системе и т.п.)
     * @return mixed
     */
    public function markOrderAsPaid($order_id, $params);

    /**
     * Куда перенаправляем после оплаты
     * @param $order_id - внутренний идентификатор заказа (ордера и т.п.)
     * @return mixed
     */
    public function redirectAfterPaid($order_id);

    /**
     * @param $purchase_merchant_id - ID заказа в платежной системе
     * @return mixed
     */
    public function setPurchaseMerchantId($order_id, $params);

}
