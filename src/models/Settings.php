<?php

namespace Guilty\Apsis\Newsletter\models;

use craft\base\Model;


class Settings extends Model
{
    public $apsisMailingList;
    public $apsisApiKey;
    public $apsisRequireDoubleOptIn = false;

    public function rules()
    {
        return [

            ['apsisApiKey', 'required'],
            ['apsisApiKey', 'string'],
            ['apsisMailingList', 'string'],
            ['apsisRequireDoubleOptIn', 'bool'],
        ];
    }
}
