<?php

const HASH_FILENAME     = __DIR__ . '/var/hash';
const DATA_FILENAME     = __DIR__ . '/var/data';
const TG_TOKEN_FILENAME = __DIR__ . '/telegram_token.txt';

const  CHATS = [
    9955337  => 'vanya_1s',
    10416247 => 'dmitrov',
];

function deep_ksort($input)
{
    if (!is_object($input) && !is_array($input)) {
        return $input;
    }

    foreach ($input as $k => $v) {
        if (is_object($v) || is_array($v)) {
            $input[$k] = deep_ksort($v);
        }
    }

    if (is_array($input)) {
        ksort($input);
    }

    // Do not sort objects

    return $input;
}

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$httpClient = new Client();

$response = $httpClient->request(
    'GET',
    'https://www.apple.com/ru/shop/delivery-message?parts.0=MQAC2RU%2FA&parts.1=MQAF2RU%2FA'
);

$responseContent = $response->getBody()->getContents();
$encodedContent  = json_decode($responseContent, true);

$encodedContent = deep_ksort($encodedContent);

$responseHash = md5(json_encode($encodedContent)) . "\n";

$deliveryMessage = $encodedContent['body']['content']['deliveryMessage'];

$text = [];

foreach ($deliveryMessage as $model => $value) {
    if (!in_array($model, ['MQAC2RU/A', 'MQAF2RU/A'], true)) {
        continue;
    }

    $model = [
                 'MQAC2RU/A' => 'iPhone X, 64 ГБ, «Серый космос»',
                 'MQAF2RU/A' => 'iPhone X, 256 ГБ, «Серый космос»',
             ][$model];

    $text[] = sprintf(
        "*%s*.\n%s %s.", $model, $value['orderByDeliveryBy'], implode(' | ', $value['deliveryOptionMessages'])
    );
}

$text = "Что-то обновилось на сайте:\n\n" . implode("\n\n", $text) . "\n";

$prevResponseHash = @file_get_contents(HASH_FILENAME);

if ($responseHash === $prevResponseHash) {
    echo 'Ничего не изменилось.' . "\n";

    exit(0);
}

echo 'Что-то изменилось. Отправляю сообщение в телеграм.' . "\n";

file_put_contents(HASH_FILENAME, $responseHash);
file_put_contents(DATA_FILENAME, $responseContent . "\n");

$tgToken = trim(file_get_contents(TG_TOKEN_FILENAME));

//$response = $httpClient->request(
//    'GET',
//    sprintf('https://api.telegram.org/bot%s/getUpdates', $tgToken)
//);
//
//foreach (json_decode($response->getBody()->getContents(), true)['result'] as $data) {
//    var_dump($data);
//}

foreach (array_keys(CHATS) as $chatId) {
    $response = $httpClient->request(
        'POST',
        sprintf('https://api.telegram.org/bot%s/sendMessage', $tgToken),
        [
            'form_params' => [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'markdown',
            ],
        ]
    );
}

