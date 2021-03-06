<?php

namespace Dnsoftware\Decta;


define('DECTA_MODULE_VERSION', 'v3.0');
define('ROOT_URL', 'https://gate.decta.com');

Class DectaAPI
{
    public function __construct($private_key, $public_key, $logger)
    {
        $this->private_key = $private_key;
        $this->public_key = $public_key;
        $this->logger = $logger;
    }

    public function create_payment($params)
    {
        $this->log_info(sprintf("Loading payment form for order #%s", $params['number']));
        $this->log_info("Create payment params: " . print_r($params, true));

        $result = $this->call('POST', '/api/v0.6/orders/', $params);
        if ($result == NULL)
        {
            return NULL;
        }

        $this->log_info("Create payment result: " . print_r($result, true));

        if (isset($result['full_page_checkout']) && isset($result['id']))
        {
            $this->log_info(sprintf("Form loaded successfully for order #%s", $params['number']));
            return $result;
        }
        else
        {
            return NULL;
        }
    }

    public function getUser($filter_email, $filter_phone){
        $params['filter_email'] = $filter_email;
        $params['filter_phone'] = $filter_phone;
        $users = $this->call('GET', '/api/v0.6/clients/', $params);
        if (isset($users['results'][0])) {
            return $users['results'][0];
        } else {
            return null;
        }
        //return $users['results'][0] ?: null;
    }

    public function createUser($params) {
        return $this->call('POST', '/api/v0.6/clients/', $params);
    }

    public function get_payment_info($payment_id) {
        $result = $this->call('GET', sprintf('/api/v0.6/orders/%s/', $payment_id));

        return $result;
    }

    public function was_payment_successful($order_id, $payment_id)
    {
        $this->log_info(sprintf("Validating payment for order #%s, payment #%s", $order_id, $payment_id));

        $order_id = (string)$order_id;
        $result = $this->call('GET', sprintf('/api/v0.6/orders/%s/', $payment_id));

//        dd($result['transaction_details']);

        if ($result == NULL)
        {
            return false;
        }

        $this->log_info("was_payment_successful result: " . print_r($result, true));

        $payment_has_matching_order_id = $order_id == (string)$result['number'];
        if (!$payment_has_matching_order_id)
        {
            $this->log_error('Payment object has a wrong order id');
        }

        if ($result && $payment_has_matching_order_id && ($result['status'] == 'paid' || $result['status'] == 'withdrawn'))
        {
            $this->log_info(sprintf("Validated order #%s, payment #%s", $order_id, $payment_id));
            return true;
        }
        else
        {
            $this->log_error('Could not validate payment');
            return false;
        }
    }

    public function call($method, $route, $params = array())
    {
        $private_key = $this->private_key;
        $original_params = $params;
        if (!empty($params)) {
            $params = json_encode($params);
        }

        $authorization_header = "Bearer " . $private_key;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, ROOT_URL . $route);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_PUT, 1);
        }

        if($method == 'GET') {
            $get_params = '';
            foreach($original_params as $key=>$value)
                $get_params .= $key.'='.urlencode($value).'&';
            $get_params = trim($get_params,'&');
            curl_setopt($ch, CURLOPT_URL, ROOT_URL.$route.'?'.$get_params);
        }

        if ($method == 'PUT' or $method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json',
            'Authorization: ' . $authorization_header
        ));


        $response = curl_exec($ch);

        if (!$response) {
            $this->log_error('cURL', curl_error($ch));
        }


        curl_close($ch);

        $result = json_decode($response, true);


        if (!$result)
        {
            $this->log_error('JSON parsing error/NULL API response');
            return NULL;
        }

        if (!empty($result['errors']))
        {
            $this->log_error('API Errors', $result['errors']);
            return NULL;
        }


        return $result;
    }

    public function log_info($text, $error_data = NULL)
    {
        $text = "DECTA INFO: " . $text . ";";
        $this->logger->log(DECTA_MODULE_VERSION . ' ' . $text);
    }

    public function log_error($error_text, $error_data = NULL)
    {
        $error_text = "DECTA ERROR: " . $error_text . ";";
        if ($error_data)
        {
            $error_text.= " ERROR DATA: " . var_export($error_data, true) . ";";
        }

        $this->logger->log(DECTA_MODULE_VERSION . ' ' . $error_text);
    }
}
