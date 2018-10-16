<?php

namespace Guilty\Apsis\Newsletter;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use Guilty\Apsis\Newsletter\models\Settings;
use Guilty\Apsis\Newsletter\services\ApsisNewsletterService;
use yii\base\Event;

/**
 * Class ApsisNewsletter
 *
 * @property  ApsisNewsletterService $apsisnewsletter
 * @package Guilty\Apsis\Newsletter
 */
class ApsisNewsletter extends Plugin
{

    /** @var ApsisNewsletter */
    public static $plugin;

    public $schemaVersion = '1.0.1';

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['apsis/newsletter'] = 'apsis-newsletter/subscribe';
            }
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'apsis-newsletter/settings',
            [
                'settings' => $this->getSettings(),
                "mailingLists" => $this->apsisnewsletter->getMailingLists(),
            ]
        );
    }
}
