<?php

namespace Guilty\Apsis\Newsletter\controllers;

use craft\web\Controller;

class ApsisNewsletterController extends Controller
{
    protected $allowAnonymous = true;

    public function actionHandleFormSubmission()
    {
        $email = \Craft::$app->request->get("email", false);
        $response = \Craft::$app->apsisNewsletter->addSubscriber($email);

        if (!$response || $response["Code"] !== 1) {
            return \Craft::$app->request->isAjax
                ? $this->asErrorJson("Could not sign up to mailing list")
                : $this->redirect("/?sent=false");
        }

        return \Craft::$app->request->isAjax
            ? $this->asJson("Succesfully signed up for mailing list")
            : $this->redirect("/?sent=true");
    }
}