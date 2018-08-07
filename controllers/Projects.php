<?php namespace Bronx\Project\Controllers;

use Backend\Facades\BackendMenu;
use Backend\Classes\Controller;

class Projects extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $bodyClass = '';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bronx.Project', 'project', 'projects');
    }

    public function create()
    {
        $this->bodyClass = 'compact-container breadcrumb-flush';

        $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->bodyClass = 'compact-container breadcrumb-flush';

        $this->asExtension('FormController')->update($recordId);
    }

    public function preview($recordId = null)
    {
        $this->bodyClass = 'compact-container breadcrumb-flush';

        $this->asExtension('FormController')->update($recordId);
    }
}