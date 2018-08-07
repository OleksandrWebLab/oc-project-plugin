<?php namespace Bronx\Project\Components;

use Bronx\Project\Models\Category;
use Bronx\Project\Models\Project;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class Catalog extends ComponentBase
{
    public $isCategory = false;
    public $isCategoryRoot = false;
    public $isProject = false;

    public $category;
    public $project;

    public $parentCategories;
    public $childrenCategories;
    public $categories;
    public $projects;

    private $urlCanonicalPage;
    private $urlCurrentPage;
    private $urlNextPage;
    private $urlPrevPage;

    private $catalogPage;
    private $catalogSlug;
    private $catalogSlugName;
    private $catalogPerPage;
    private $catalogOrderBy;
    private $catalogOrderSort;

    private $internalRequest = [];
    private $externalRequest = [];
    private $temporaryRequest = [];

    public function componentDetails()
    {
        return [
            'name'        => 'ProjectCatalog',
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
            'catalogSlug'      => [
                'title'   => 'Catalog slug',
                'default' => '{{ :slug }}',
            ],
            'catalogPerPage'   => [
                'title'   => 'Catalog list per page',
                'default' => 10,
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
        if ($this->isCategory == true) {
            $this->catalogPage = $this->property('catalogPage');
            $this->catalogPerPage = $this->property('catalogPerPage');
            $this->catalogOrderBy = $this->property('catalogOrderBy');
            $this->catalogOrderSort = $this->property('catalogOrderSort');

            if (!isset($this->externalRequest['page'])) {
                $this->externalRequest['page'] = Input::get('page', 1);
            }

            /*
             * Set global page variables
             */
            $this->page['listPage'] = $this->externalRequest['page'];
        } else if ($this->isProject == true) {

        }
    }

    private function afterRun()
    {
        if ($this->isCategory == true) {
            $this->urlCanonicalPage = $this->controller->pageUrl($this->catalogPage, $this->controller);
            $this->urlCurrentPage = $this->generateUrl();
            $this->urlPrevPage = $this->projects->previousPageUrl();
            $this->urlNextPage = $this->projects->nextPageUrl();

            $this->page->urlCanonicalPage = $this->urlCanonicalPage;
            $this->page->urlCurrentPage = $this->urlCurrentPage;
            $this->page->urlPrevPage = $this->urlPrevPage;
            $this->page->urlNextPage = $this->urlNextPage;

            if ($this->isCategoryRoot == true) {
                /*
                 * Set page titles
                 */
                $this->page->title = $this->category->name;

                if ($this->category->meta_title != null) {
                    $this->page->meta_title = $this->category->meta_title;
                }

                if ($this->category->meta_description != null) {
                    $this->page->meta_description = $this->category->meta_description;
                }

                if ($this->category->meta_keywords != null) {
                    $this->page->meta_keywords = $this->category->meta_keywords;
                }
            }
        } else if ($this->isProject == true) {
            /*
             * Set page titles
             */
            $this->page->title = $this->project->name;

            if ($this->project->meta_title != null) {
                $this->page->meta_title = $this->project->meta_title;
            }

            if ($this->project->meta_description != null) {
                $this->page->meta_description = $this->project->meta_description;
            }

            if ($this->project->meta_keywords != null) {
                $this->page->meta_keywords = $this->project->meta_keywords;
            }
        }
    }

    public function onRun()
    {
        /**
         * Init component
         */
        $this->catalogSlug = $this->property('catalogSlug');
        $this->catalogSlugName = $this->paramName('catalogSlug');

        /*
         * Get type of page
         */
        $segments = explode('/', $this->catalogSlug);
        $slug = end($segments);
        $parts = explode('-', $slug);

        switch ($parts[0]) {
            case 'p':
                $this->isProject = true;
                break;
            case 'c':
                $this->isCategory = true;
                $this->isCategoryRoot = true;
                break;
            case '':
                $this->isCategory = true;
                $this->isCategoryRoot = false;
                break;
        }

        $this->beforeRun();

        /**
         * Run the processing
         */
        if ($this->isCategory == true) {
            if ($this->isCategoryRoot == true) {
                /**
                 * Make checks
                 */
                $this->category = Category::where($this->catalogSlugName, $slug)
                    ->first();

                // Check exist category
                if ($this->category == null) {
                    // Trying redirect to new address
                    $category = Category::where('id', $parts[1])
                        ->first();

                    if ($category == null) {
                        return $this->controller->run('404');
                    } else {
                        return Redirect::to($category->takeUrl($this->catalogPage, $this->controller), 301);
                    }
                }

                // Check equal current page slug* with category slug
                if ($this->catalogSlug != $this->category->takeSlug()) {
                    return Redirect::to($this->category->takeUrl($this->catalogPage, $this->controller), 301);
                }

                /**
                 * Get categories for breadcrumb
                 */
                $this->parentCategories = $this->category
                    ->getParents();

                $this->parentCategories->each(function ($category) {
                    $category->url = $category->takeUrl($this->catalogPage, $this->controller);
                });

                /**
                 * Get categories for eloquent
                 */
                $this->childrenCategories = $this->category
                    ->getAllChildrenAndSelf();

                $this->childrenCategories->each(function ($category) {
                    $category->url = $category->takeUrl($this->catalogPage, $this->controller);
                });

                /**
                 * Get categories for navigation
                 */
                $this->categories = $this->category
                    ->getChildren();

                if ($this->categories->isEmpty()) {
                    $this->categories = $this->category
                        ->getSiblingsAndSelf();
                }
            } else {
                $this->categories = Category::get();

                $this->categories->each(function ($category) {
                    $category->url = $category->takeUrl($this->catalogPage, $this->controller);
                });

                $this->childrenCategories = $this->categories;
            }

            /**
             * Prepare projects
             */
            $this->projects = Project::whereIn('category_id', $this->childrenCategories->lists('id'))
                ->orderBy($this->catalogOrderBy, $this->catalogOrderSort)
                ->paginate($this->catalogPerPage, $this->externalRequest['page']);

            $this->projects->each(function ($project) {
                $project->url = $project->takeUrl($this->catalogPage, $this->controller);
            });
        } else if ($this->isProject == true) {
            /**
             * Make checks
             */
            $this->project = Project::where($this->catalogSlugName, $slug)
                ->first();

            // Check exist project
            if ($this->project == null) {
                // Trying redirect to new address
                $project = Project::where('id', $parts[1])
                    ->first();

                if ($project == null) {
                    return $this->controller->run('404');
                } else {
                    return Redirect::to($project->takeUrl($this->catalogPage, $this->controller), 301);
                }
            }

            // Check equal current page slug* with project slug
            if ($this->catalogSlug != $this->project->takeSlug()) {
                return Redirect::to($this->project->takeUrl($this->catalogPage, $this->controller), 301);
            }

            $this->category = $this->project
                ->relCategory()
                ->first();

            /**
             * Formation category list for breadcrumb
             */
            $this->parentCategories = $this->category
                ->getParents();

            $this->parentCategories->each(function ($category) {
                $category->url = $category->takeUrl($this->catalogPage, $this->controller);
            });
        } else {
            return $this->controller->run('404');
        }


        $this->afterRun();
    }

    /**
     * Формируем новый URL, возвращаем результат
     * @return array
     */
    private function refreshContent()
    {
        return [
            '#_project_list_wrapper'    => $this->renderPartial('project/wrapper'),
            '#_project_list_pagination' => $this->renderPartial('project/pagination'),
            'urlCurrentPage'            => $this->urlCurrentPage,
            'urlPrevPage'               => $this->urlPrevPage,
            'urlNextPage'               => $this->urlNextPage,
        ];
    }

    /*
     * Генерация URL
     */
    private function generateUrl()
    {
        if ($this->externalRequest['page'] == 1) {
            $this->externalRequest['page'] = null;
        }

        $url = trim($this->urlCanonicalPage . '?' . http_build_query($this->externalRequest), '/?');

        $url = str_replace('%5B', '[', $url);
        $url = str_replace('%5D', ']', $url);

        return $url;
    }

    /*
     * AJAX Events
     */
    public function onChangeNumPage()
    {
        $this->externalRequest['page'] = Input::get('page', 1);

        $this->onRun();

        return $this->refreshContent();
    }
}