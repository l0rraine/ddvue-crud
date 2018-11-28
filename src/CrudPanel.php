<?php

namespace DDVue\Crud;

use DDVue\Crud\app\Models\QueryParam;
use DDVue\Crud\PanelTraits\Access;
use DDVue\Crud\app\Models\BaseClassifiedModel;
use Illuminate\Database\Eloquent\Model;

class CrudPanel
{
    use Access;

    /**
     * 路由起始名，如，Role.edit 的$route='Role'
     * @var string
     */
    public $route;

    /**
     * 路由的后缀，形成如Asset.own.index的路由
     * @var string
     */
    public $routeSuffix = '';

    public $indexUrl = '';

    /*
     * 标题，用于form_header中的显示
     * @var string
     */
    public $title;

    /**
     * breadcrumb data
     * @var array
     */
    public $navigator = [];

    /**
     * 权限的起始名
     * @var string
     */
    public $permissionName;

    /**
     * create和update中的保存按钮默认的动作
     * @var string
     */
    public $saveActions;

    /**
     * index页面table的记录是否带有parent_id和list_classes
     * @var bool
     */
    public $indexRecursive = false;

    /**
     * 自行规定view的前缀，实际路径为 manager.pages.$viewName.['create','update','list']
     * @var string
     */
    public $viewName = '';

    /** @var BaseClassifiedModel */
    public $model;

    /** @var string */
    public $modelName;


    public $request;


    /**
     * @var array 查询参数
     */
    public $queryParams = ['model' => '', 'groups' => []];

    /**
     * Set the route for this CRUD.
     * Ex: manager/article.
     *
     * @param [string] Route name.
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Set the route for this CRUD using the route name.
     * Ex: manager.article.
     *
     * @param [string] Route name.
     * @param [array] Parameters.
     */
    public function setRouteName($route, $parameters = [])
    {
        $complete_route = $route . '.index';

        if (!\Route::has($complete_route)) {
            throw new \Exception('There are no routes for this route name.', 404);
        }

        $this->route = route($complete_route, $parameters);
    }

    public function getFullRoute()
    {
        if ($this->routeSuffix != '') {
            return $this->route . '.' . $this->routeSuffix;
        } else {
            return $this->route;
        }
    }

    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get the corresponding Eloquent Model for the CrudController, as defined with the setModel() function;.
     *
     * @return [Eloquent Collection]
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * 设置模型
     * 查找不到模型后添加命名空间\App\Models
     *
     * @param [string] model namespace. Ex: App\Models\Article or Article
     */

    /**
     * 设置模型
     * 查找不到模型后添加命名空间\App\Models
     *
     * @param string|Model $model_name
     *
     * @throws \Exception
     */
    public function setModel($model)
    {
        if ($model instanceof Model) {
            $this->model     = $model;
            $this->modelName = get_class($model);
        } else {
            if (!class_exists($model)) {
                $model = "\\App\\Models\\" . $model;
                if (!class_exists($model)) {
                    throw new \Exception('This model does not exist.', 404);
                }
            }

            $this->modelName = $model;
            $this->model     = new $model();
        }


    }

    public function getIndexUrl()
    {
        return empty($this->indexUrl) ? route($this->getFullRoute() . '.index') : $this->indexUrl;
    }

    /**
     * @param mixed $permissionName
     */
    public function setPermissionName($permissionName)
    {
        $this->permissionName = $permissionName;
        $this->hasAccessOrFail($permissionName);
    }

    /**
     * @return array
     */
    public function getNavigator()
    {
        $this->navigator = [
            'title'    => $this->title,
            'subtitle' => $this->title . '列表',
            'items'    => [
                [
                    'title' => $this->title,
                    'link'  => $this->getIndexUrl()
                ],
                [
                    'title' => '列表',
                    'link'  => ''
                ]
            ]
        ];

        return $this->navigator;
    }

    /**
     * @param array $navigator
     */
    public function setNavigator($navigator)
    {
        $this->navigator = $navigator;
    }

    /**
     *
     * @param string $title   组名，同时也是前台select下拉列表中的group title
     * @param string $join    需要查询的数据在模型的外联里时设置，默认为空，值要取模型的$with属性内的值
     * @param string $key     外联的foreign key
     * @param array  $columns 需要查询的列名数组
     * @param array  $maps    用于显示的列，必须含有value=>，该项用于前台下拉列表显示。某一项不一定有值时，可以用||隔开的方式定义多列对应
     */
    public function addQueryParam(string $title, array $columns, array $maps, string $join = '', string $key = '')
    {
        array_push($this->queryParams['groups'], new QueryParam($title, $columns, $maps, $join, $key));
    }
}
