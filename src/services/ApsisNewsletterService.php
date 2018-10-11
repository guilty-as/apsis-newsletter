<?php

namespace Guilty\Apsis\Newsletter;

use craft\base\Component;
use GuzzleHttp\Client;

class ApsisNewsletterService extends Component
{
    // Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    public function getPlugin()
    {
        return craft()->plugins->getPlugin('apsisNewsletter');
    }

    public function getSettings()
    {
        return $this->getPlugin()->getSettings();
    }

    protected function getApiKey()
    {
        return $this->getSettings()->apsisApiKey;
    }

    protected function hasApiKey()
    {
        return !!$this->getSettings()->apsisApiKey;
    }

    protected function getSelectedMailingListId()
    {
        return $this->getSettings()->apsisMailingList;
    }

    public function addSubscriber($email)
    {
        return $this->getSettings()->apsisRequireDoubleOptIn
            ? $this->createSubscriberWithDoubleOptIn($email)
            : $this->createSubscriber($email);
    }

    public function getMailingLists()
    {
        $jsonResponse = $this->sendRequest("post", "mailinglists/v2/all");

        if (!$jsonResponse) {
            return [
                ["label" => "Please provide an API key to make requests to the APSIS API",]
            ];
        }

        return array_map(function ($data) {
            return [
                "label" => $data["Name"],
                "value" => $data["Id"],
            ];
        }, $jsonResponse["Result"]);
    }


    public function createSubscriberWithDoubleOptIn($email)
    {
        $mailingList = $this->getSelectedMailingListId();
        return $this->sendRequest("post", "/v1/subscribers/mailinglist/" . $mailingList . "/createWithDoubleOptIn", [
            "Email" => $email
        ]);
    }

    public function createSubscriber($email)
    {
        $mailingList = $this->getSelectedMailingListId();
        return $this->sendRequest("post", "/v1/subscribers/mailinglist/" . $mailingList . "/create?updateIfExists=true", [
            "Email" => $email
        ]);
    }

    protected function sendRequest($method, $resource, $body = false)
    {
        if (!$this->hasApiKey()) {
            return false;
        }


        try {
            $client = new Client("http://se.api.anpdm.com/");

            $request = $client->createRequest($method, $resource);
            $request->setHeader('Accept', 'application/json');
            $request->setAuth($this->getApiKey(), '');

            if ($body) {
                $request->setBody(json_encode($body), "application/json");
            }


            $response = $request->send();

            return $response->json();
        } catch (\Exception $exception) {
            Craft::log($exception->getResponse()->getBody(true));
            Craft::log($exception);
            return false;
        }
    }
}
