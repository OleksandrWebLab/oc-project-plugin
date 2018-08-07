<?php namespace Bronx\Project\Models;

use October\Rain\Database\Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'bronx_project_settings';
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->google_maps_key = '';
    }
}