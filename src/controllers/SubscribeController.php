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
        $email = Craft::$app->getRequest()->getParam("email");
        $redirectTo = Craft::$app->getRequest()->getParam("redirect");

        if (!$email) {
            return \Craft::$app->getRequest()->isAjax
                ? $this->asErrorJson("Email not provided")
                : $this->redirect($redirectTo . "?missing=email");
        }

        $response = ApsisNewsletter::getInstance()->apsisnewsletter->addSubscriber($email);

        // An invalid response or Code of non-1 indicates an error
        if (!$response || $response["Code"] !== 1) {
            return \Craft::$app->getRequest()->isAjax
                ? $this->asErrorJson("Could not sign up to mailing list")
                : $this->redirect($redirectTo . "?success=false");
        }

        return \Craft::$app->getRequest()->isAjax
            ? $this->asJson("Succesfully signed up for mailing list")
            : $this->redirect($redirectTo . "?success=true");
    }
}