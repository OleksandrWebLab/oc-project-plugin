<?php namespace Bronx\Project;

use Backend\Facades\Backend;
use Bronx\Project\Components\CategoryListWidget;
use Bronx\Project\Components\Catalog;
use Bronx\Project\Components\ProjectListWidget;
use Bronx\Project\FormWidgets\AddressFinder;
use Bronx\Project\Models\Category;
use Bronx\Project\Models\Project;
use Bronx\Project\Models\Settings;
use Illuminate\Support\Facades\Event;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Проекты',
            'description' => '...',
            'author'      => 'Alexander Shapoval',
            'icon'        => 'icon-leaf',
        ];
    }

    public function register()
    {

    }

    public function boot()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                'projectCatalogCategory' => 'Проекты: категории',
                'projectCatalogProject'  => 'Проекты: проекты',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == 'projectCatalogCategory') {
                return Category::getMenuTypeInfo($type);
            } else if ($type == 'projectCatalogProject') {
                return Project::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($type, $item, $url, $theme) {
            if ($type == 'projectCatalogCategory') {
                return Category::resolveMenuItem($item, $url, $theme);
            } else if ($type == 'projectCatalogProject') {
                return Project::resolveMenuItem($item, $url, $theme);
            }
        });
    }

    public function registerComponents()
    {
        return [
            Catalog::class            => 'bronxProjectCatalog',
            CategoryListWidget::class => 'bronxProjectCategoryListWidget',
            ProjectListWidget::class  => 'bronxProjectProjectListWidget',
        ];
    }

    public function registerNavigation()
    {
        return [
            'project' => [
                'label'   => 'Проекты',
                'url'     => Backend::url('bronx/project/projects'),
                'icon'    => 'icon-leaf',
                'iconSvg' => 'plugins/bronx/project/assets/icon.svg',
                'order'   => 50,

                'sideMenu' => [
                    'projects'   => [
                        'label' => 'Проекты',
                        'url'   => Backend::url('bronx/project/projects'),
                        'icon'  => 'icon-leaf',
                    ],
                    'categories' => [
                        'label' => 'Категории',
                        'url'   => Backend::url('bronx/project/categories'),
                        'icon'  => 'icon-leaf',
                    ],
                ],
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Проекты',
                'description' => 'Управление настройками проектов',
                'category'    => 'Bronx',
                'icon'        => 'icon-cog',
                'class'       => Settings::class,
                'order'       => 70,
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            AddressFinder::class => [
                'label' => 'Address Finder',
                'code'  => 'addressfinder',
            ],
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'filesize' => function ($size) {
                    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $precision = 2;

                    $bytes = max($size, 0);
                    $pow = min(floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);
                    $bytes /= pow(1024, $pow);

                    return round($bytes, $precision) . ' ' . $units[$pow];
                },
            ],
        ];
    }
}
