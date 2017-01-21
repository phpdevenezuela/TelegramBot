<?php
/**
 * Codigo base encontrado en https://core.telegram.org/bots/samples/hellobot.
 */
define('BOT_TOKEN', '12345678:replace-me-with-real-token');
define('WEBHOOK_URL', 'https://your.webhook/url/bot.php');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

/**
 * @param string $method
 */
function apiRequestWebhook($method, $parameters)
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
function exec_curl_request($handle)
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
            throw new Exception('El token provisto es inválido');
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
function apiRequest($method, $parameters)
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

    $url = API_URL.$method.'?'.http_build_query($parameters);
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

    return exec_curl_request($handle);
}

/**
 * @param string $method
 */
function apiRequestJson($method, $parameters)
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
    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    return exec_curl_request($handle);
}

function processMessage($message)
{

    // process incoming message

    $message_id = $message['message_id'];
    $chat_id = $message['chat']['id'];
    if (isset($message['text'])) {

        // incoming text message

        $text = $message['text'];
        if (strpos($text, '/start') === 0) {
            apiRequestJson('sendMessage', [
                'chat_id'      => $chat_id,
                'text'         => 'Hola',
                'reply_markup' => [
                    'keyboard'          => [['Hola', 'Epale']],
                    'one_time_keyboard' => true,
                    'resize_keyboard'   => true, ],
                ]);
        } elseif ($text === 'Hola' || $text === 'Epale') {
            apiRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text'    => 'Es un placer conocerte',
                ]);
        } elseif (strpos($text, '/stop') === 0) {

            // stop now
        } else {
            apiRequestWebhook('sendMessage', [
            'chat_id' => $chat_id,
            'text'    => 'Yo sólo entiendo mensajes de texto',
            ]);
        }
    } else {
        apiRequest('sendMessage', [
            'chat_id' => $chat_id,
            'text'    => 'Yo sólo entiendo mensajes de texto',
            ]);
    }
}

function saluteNewMember($message)
{
    $chat_id = $message['chat']['id'];
    $first_name = $message['new_chat_member']['first_name'];
    $username = ($message['new_chat_member']['username']) ? '( @'.$message['new_chat_member']['username'].' )' : '';
    $welcome_text = "📢 Bienvenido/a *$first_name* $username a [PHP.ve](https://telegram.me/PHP_Ve), te invitamos a que leas la [Descripción y Normas del Grupo](http://telegra.ph/PHPve-11-24)";
    apiRequest('sendMessage', [
        'chat_id'                  => $chat_id,
        'text'                     => $welcome_text,
        'parse_mode'               => 'Markdown',
        'disable_web_page_preview' => true,
        ]);
}

if (php_sapi_name() == 'cli') {

    // if run from console, set or delete webhook

    apiRequest('setWebhook', ['url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL]);
    exit;
}

$content = file_get_contents('php://input');
$update = json_decode($content, true);

if (!$update) {

    // receive wrong update, must not happen

    exit;
}

if (isset($update['message']['new_chat_member'])) {
    saluteNewMember($update['message']);
} elseif (isset($update['message']['text'])) {
    processMessage($update['message']);
}