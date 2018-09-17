<?php

/**
 * Telegram Client Class.
 *
 * @author  Alexander Rodriguez <alexr1712@gmail.com>
 */

namespace App;

class Telegram
{
    private $token;
    private $webhook;
    private $apiUrl;

    public function __construct($config, $pwrtelegram = false)
    {
        /*
         * Can use PWRTelegram for active more power of telegram.
         */
        $this->apiUrl = ($pwrtelegram) ? 'https://api.pwrtelegram.xyz/bot'.$config['TELEGRAM_TOKEN'].'/' : 'https://api.telegram.org/bot'.$config['TELEGRAM_TOKEN'].'/';
        $this->webhook = $config['WEBHOOK_URL'];
    }

    /**
     * @param string $method
     */
    public function apiRequestWebhook($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("El nombre del método debe ser una cadena de texto\n");

            return false;
        }

        if (!$parameters) {
            $parameters = [];
        } elseif (!is_array($parameters)) {
            error_log("Los parámetros deben ser un arreglo/matriz\n");

            return false;
        }

        $parameters['method'] = $method;
        header('Content-Type: application/json');
        echo json_encode($parameters);

        return true;
    }

    /**
     * @param resource $handle
     */
    public function exec_curl_request($handle)
    {
        $response = curl_exec($handle);
        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl retornó un error $errno: $error\n");
            curl_close($handle);

            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
        if ($http_code >= 500) {

        // do not wat to DDOS server if something goes wrong

            sleep(10);

            return false;
        } elseif ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("La solicitud falló con el error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                http_response_code(401);

                throw new \Exception('El token provisto es inválido');
            } else {
                http_response_code($response['error_code']);

                throw new \Exception("La solicitud falló con el error {$response['error_code']}: {$response['description']}\n");
            }

            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("La solicitud fue exitosa: {$response['description']}\n");
            }

            $response = $response['result'];
        }

        return $response;
    }

    /**
     * @param string $method
     */
    public function apiRequest($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("El nombre del método debe ser una cadena de texto\n");

            return false;
        }

        if (!$parameters) {
            $parameters = [];
        } elseif (!is_array($parameters)) {
            error_log("Los parámetros deben ser un arreglo/matriz\n");

            return false;
        }

        foreach ($parameters as $key => &$val) {

        // encoding to JSON array parameters, for example reply_markup

            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }

        $url = $this->apiUrl.$method.'?'.http_build_query($parameters);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        return self::exec_curl_request($handle);
    }

    /**
     * @param string $method
     */
    public function apiRequestJson($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("El nombre del método debe ser una cadena de texto\n");

            return false;
        }

        if (!$parameters) {
            $parameters = [];
        } elseif (!is_array($parameters)) {
            error_log("Los parámetros deben ser un arreglo/matriz\n");

            return false;
        }

        $parameters['method'] = $method;
        $handle = curl_init($this->apiUrl);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        return exec_curl_request($handle);
    }

    public function sendMessage($chat_id, $text, $args = [])
    {
        $parameters = $args;
        $parameters['chat_id'] = $chat_id;
        $parameters['text'] = $text;

        return $this->apiRequest('sendMessage', $parameters);
    }

    public function kickChatMember($chat_id, $user_id, $until_date = null)
    {
        $parameters = [];
        $parameters['chat_id'] = $chat_id;
        $parameters['user_id'] = $user_id;
        $parameters['until_date'] = $until_date;

        return $this->apiRequest('kickChatMember', $parameters);
    }

    public function deleteMessage($chat_id, $message_id)
    {
        $parameters = [];
        $parameters['chat_id'] = $chat_id;
        $parameters['message_id'] = $message_id;

        return $this->apiRequest('deleteMessage', $parameters);
    }

    public function setWebhook($certificate = null, $max_connections = null, $allowed_updates = [])
    {
        $parameters['url'] = $this->webhook;
        $parameters['certificate'] = $certificate;
        $parameters['max_connections'] = $max_connections;
        $parameters['allowed_updates'] = $allowed_updates;

        return $this->apiRequest('setWebhook', $parameters);
    }
}
