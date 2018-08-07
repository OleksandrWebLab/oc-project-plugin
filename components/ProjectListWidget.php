<?php namespace Bronx\Project\Components;

use Bronx\Project\Models\Project;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;

class ProjectListWidget extends ComponentBase
{
    public $projects;

    private $catalogPage;
    private $catalogPerPage;
    private $catalogOrderBy;
    private $catalogOrderSort;

    public function componentDetails()
    {
        return [
            'name'        => 'ProjectListWidget',
            'description' => '...',
        ];
    }

    public function defineProperties()
    {
        return [
            'catalogPage'      => [
                'title' => 'Catalog page',
                'type'  => 'dropdown',
            ],
            'catalogPerPage'   => [
                'title'   => 'Catalog list per page',
                'default' => '5',
            ],
            'catalogOrderBy'   => [
                'title'   => 'Catalog list order by',
                'type'    => 'dropdown',
                'options' => [
                    'sort_order' => 'Sort order',
                    'created_at' => 'Created at',
                ],
            ],
            'catalogOrderSort' => [
                'title'   => 'Catalog list order sort',
                'type'    => 'dropdown',
                'options' => [
                    'asc'  => 'ASC',
                    'desc' => 'DESC',
                ],
            ],
        ];
    }

    public function getCatalogPageOptions()
    {
        return Page::sortBy('baseFileName')
            ->lists('url', 'baseFileName');
    }

    private function beforeRun()
    {
        $this->catalogPage = $this->property('catalogPage');
        $this->catalogPerPage = $this->property('catalogPerPage');
        $this->catalogOrderBy = $this->property('catalogOrderBy');
        $this->catalogOrderSort = $this->property('catalogOrderSort');
    }

    public function onRun()
    {
        $this->beforeRun();

        $this->projects = Project::with(['relImage'])
            ->take($this->catalogPerPage)
            ->orderBy($this->catalogOrderBy, $this->catalogOrderSort)
            ->get();

        $this->projects->each(function ($project) {
            $project->url = $project->takeUrl($this->catalogPage, $this->controller);
        });
    }
}