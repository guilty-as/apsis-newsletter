<?php

namespace Guilty\Apsis\Newsletter\controllers;

use Craft;
use craft\web\Controller;
use Guilty\Apsis\Newsletter\ApsisNewsletter;

class SubscribeController extends Controller
{
    protected $allowAnonymous = true;

    public function actionIndex()
    {
        // TODO(16 okt 2018) ~ Helge: Check if getParam works with json ajax requests
        $email = Craft::$app->getRequest()->getParam("email");

        // TODO(16 okt 2018) ~ Helge: Make configurable in the settings
        $redirectTo = Craft::$app->getRequest()->getParam("redirect", "/thanks");

        if (!$email) {
            return \Craft::$app->getRequest()->isAjax
                ? $this->asErrorJson("Email not provided")
                : $this->redirect($redirectTo . "?missing=email");
        }

        $response = ApsisNewsletter::getInstance()->apsisnewsletter->addSubscriber($email);

        // TODO(16 okt 2018) ~ Helge: Handle redirects better, maybe redirect back with error like craftcms/contact-form does?

        if ($this->isSuccessResponse($response)) {
            return \Craft::$app->getRequest()->isAjax
                ? $this->asJson("Succesfully signed up for mailing list")
                : $this->redirect($redirectTo . "?success=true");
        }

        return \Craft::$app->getRequest()->isAjax
            ? $this->asErrorJson("Could not sign up to mailing list")
            : $this->redirect($redirectTo . "?success=false");

    }

    /**
     * If the response is not an array or otherwise is falsy, the request failed,
     * if the Code param is not "1", this signifies a non-success response from APSIS
     *
     * API Docs can be found here:
     * @see http://se.apidoc.anpdm.com/Browse/Method/SubscriberService/CreateSubscriberWithDoubleOptIn
     * @see http://se.apidoc.anpdm.com/Browse/Method/SubscriberService/CreateSubscriber
     * @param $response
     * @return bool
     */
    protected function isSuccessResponse($response)
    {
        return (!$response || $response["Code"] !== 1);
    }
}