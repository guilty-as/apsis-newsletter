<?php

namespace Guilty\Apsis\Newsletter;

use Craft;
use craft\base\Plugin;
use Guilty\Apsis\Newsletter\models\Settings;

class ApsisNewsletter extends Plugin
{

    // Static Properties
    // =========================================================================

    /**
     * @var HubspotConnector
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('hubspot', HubspotConnectorVariable::class);
        });
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'apsis-newsletter/settings',
            [
                'settings' => $this->getSettings(),
                "mailingLists" => craft()->apsisNewsletter->getMailingLists()
            ]
        );
    }


    public function registerSiteRoutes()
    {
        return [
            'apsis/newsletter' => ['action' => 'ApsisNewsletter/handleFormSubmission'],
        ];
    }


    // =========================================================================
    // HOOKS
    // =========================================================================


}
