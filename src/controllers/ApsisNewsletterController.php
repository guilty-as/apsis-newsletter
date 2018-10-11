<?php

namespace Craft;

use Guzzle\Http\Exception\ClientErrorResponseException;

class ApsisNewsletterController extends BaseController
{

    protected $allowAnonymous = [
        "actionHandleFormSubmission",
    ];

    public function actionHandleFormSubmission()
    {
        $email = craft()->request->getQuery("email", false);

        $response = craft()->apsisNewsletter->addSubscriber($email);


        if (!$response || $response["Code"] !== 1) {
            return craft()->request->isAjaxRequest()
                ? $this->returnJson(["success" => false])
                : $this->redirect("/?sent=false");
        }

        return craft()->request->isAjaxRequest()
            ? $this->returnJson(["success" => true])
            : $this->redirect("/?sent=true");
    }
}