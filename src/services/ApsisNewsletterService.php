<?php

namespace Guilty\Apsis\Newsletter\services;

use craft\base\Component;
use Guilty\Apsis\Newsletter\ApsisNewsletter;
use Guilty\Apsis\Newsletter\models\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ApsisNewsletterService extends Component
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var Settings
     */
    protected $settings;

    public function init()
    {
        parent::init();

        $this->settings = ApsisNewsletter::$plugin->getSettings();

        $this->client = new Client([
            "base_uri" => "http://se.api.anpdm.com/",
            "http_errors" => false,
            "headers" => ["Accept" => "application/json"],
            "auth" => [$this->settings->apsisApiKey, ""],
        ]);
    }

    public function addSubscriber($email)
    {
        return $this->settings->apsisRequireDoubleOptIn
            ? $this->createSubscriberWithDoubleOptIn($email)
            : $this->createSubscriber($email);
    }

    public function getMailingLists()
    {
        if (!$this->settings->apsisApiKey) {
            return [
                ["label" => "Please provide an API key to make requests to the APSIS API",],
            ];
        }

        $response = $this->getResponseAsJson($this->client->post("mailinglists/v2/all"));

        return array_map(function ($data) {
            return [
                "label" => $data["Name"],
                "value" => $data["Id"],
            ];
        }, $response["Result"]);
    }


    public function createSubscriberWithDoubleOptIn($email)
    {
        $id = $this->settings->apsisMailingList;
        $url = "/v1/subscribers/mailinglist/{$id}/createWithDoubleOptIn";

        $response = $this->client->post($url, [
            "Email" => $email,
        ]);

        return $this->getResponseAsJson($response);
    }

    public function createSubscriber($email, $updateIfExists = true)
    {
        $id = $this->settings->apsisMailingList;
        $url = "/v1/subscribers/mailinglist/{$id}/create";

        $response = $this->client->post($url, [
            "Email" => $email,
            'query' => [
                'updateIfExists' => $updateIfExists,
            ],
        ]);

        return $this->getResponseAsJson($response);
    }

    protected function getResponseAsJson(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}