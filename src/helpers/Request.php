<?php

namespace yii2module\rest_client\helpers;

use Yii;
use yii2module\rest_client\models\ResponseRecord;
use yii2module\rest_client\models\RequestForm;

class Request
{

    static public function send($model)
    {
        $begin = microtime(true);
        $response = self::httpRequest($model);
        $duration = microtime(true) - $begin;

        $record = self::createResponseRecord($response);
        $record->duration = $duration;
        return $record;
    }

    static public function httpRequest($model)
    {
        /** @var \yii\httpclient\Client $client */
        $client = Yii::createObject(Yii::$app->controller->module->clientConfig);
        $client->baseUrl = Yii::$app->controller->module->baseUrl;
        $response = $client->createRequest()
            ->setMethod($model->method)
            ->setUrl($model->getUri())
            ->setData($model->getBodyParams())
            ->setHeaders($model->getHeaders())
            ->send();
        return $response;
    }

    static public function createRequestFrom($endpoint, $method = 'get', $body = [], $header = [])
    {
        $modelAuth = Yii::createObject(RequestForm::className());
        $modelAuth->method = $method;
        $modelAuth->endpoint = $endpoint;
        if(!empty($body)) {
            foreach($body as $bodyKey => $bodyValue) {
                $modelAuth->bodyKeys[] = $bodyKey;
                $modelAuth->bodyValues[] = $bodyValue;
                $modelAuth->bodyActives[] = 1;
            }
        }
        if(!empty($header)) {
            foreach($header as $headerKey => $headerValue) {
                $modelAuth->headerKeys[] = $headerKey;
                $modelAuth->headerValues[] = $headerValue;
                $modelAuth->headerActives[] = 1;
            }
        }
        return $modelAuth;
    }

    static protected function createResponseRecord($response)
    {
        $record = new ResponseRecord();
        $record->status = $response->getStatusCode();
        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $record->headers[$name] = $values;
        }
        $record->content = $response->getContent();
        return $record;
    }

}