<?php namespace Bronx\Project\Components;

use Bronx\Project\Models\Category;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;

class CategoryListWidget extends ComponentBase
{
    public $categories;

    private $catalogPage;
    private $categoryRoot;

    public function componentDetails()
    {
        return [
            'name'        => 'CategoryListWidget',
            'description' => '...',
        ];
    }

    public function defineProperties()
    {
        return [
            'catalogPage'    => [
                'title' => 'Product list page',
                'type'  => 'dropdown',
            ],
            'categoryRoot'   => [
                'title' => 'Category list root',
                'type'  => 'dropdown',
            ],
        ];
    }

    public function getCatalogPageOptions()
    {
        return Page::sortBy('baseFileName')
            ->lists('url', 'baseFileName');
    }

    public function getCategoryRootOptions()
    {
        return [null => '-- корень --'] + Category::lists('name', 'slug');
    }

    public function beforeRun()
    {
        $this->catalogPage = $this->property('catalogPage');
        $this->categoryRoot = $this->property('categoryRoot');
    }

    public function onRun()
    {
        $this->beforeRun();

        $this->categories = Category::with('relImage');

        if ($this->categoryRoot != null) {
            $this->categories = $this->categories
                ->where('slug', $this->categoryRoot);
        }

        $this->categories = $this->categories
            ->getNested();

        $this->categories = $this->setCategoriesLinks($this->categories);
    }

    private function setCategoriesLinks($categories)
    {
        return $categories->each(function ($category) {
            $category->url = $category->takeUrl($this->catalogPage, $this->controller);

            if ($category->children) {
                $this->setCategoriesLinks($category->children);
            }
        });
    }
}