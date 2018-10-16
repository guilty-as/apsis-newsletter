<?php

namespace Guilty\Apsis\Newsletter\services;

use craft\base\Component;
use Guilty\Apsis\Newsletter\ApsisNewsletter;
use Guilty\Apsis\Newsletter\models\Settings;
use GuzzleHttp\Client;

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

        $response = $this->client->post("mailinglists/v2/all")->getBody()->getContents();
        $response = json_decode($response, true);

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

        return $this->client->post("/v1/subscribers/mailinglist/{$id}/createWithDoubleOptIn", [
            "Email" => $email,
        ]);
    }

    public function createSubscriber($email)
    {
        $id = $this->settings->apsisMailingList;

        return $this->client->post("/v1/subscribers/mailinglist/{$id}/create?updateIfExists=true", [
            "Email" => $email,
        ]);
    }


}