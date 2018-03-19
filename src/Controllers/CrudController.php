<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 9:59
 */

namespace DDVue\Crud\Controllers;

use DDVue\Crud\app\Models\QueryParam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use DDVue\Crud\Controllers\Features\SaveActions;
use DDVue\Crud\CrudPanel;


class CrudController extends BaseController
{
    use SaveActions;

    /**
     * @var CrudPanel
     */
    public $crud;

    /*
     * @var array
     */
    public $data;


    /**
     * @var array|mixed crud操作后要处理的数据
     */
    public $doAfterCrudData;


    /**
     * @var \Illuminate\Validation\Validator
     */
    public $validator;

    public function __construct()
    {
        if (!$this->crud) {
            $this->crud = app()->make(CrudPanel::class);

            // call the setup function inside this closure to also have the request there
            // this way, developers can use things stored in session (auth variables, etc)
            $this->middleware(function ($request, $next) {
                $this->crud->request = $request;
                $this->setup();

                return $next($request);
            });

            $this->crud->saveActions = $this->getSaveAction();
        }
    }

    public function setup()
    {
        $this->crud->getNavigator();
        $this->crud->queryParams['model']  = $this->crud->modelName;
    }

    public function getIndex()
    {
        $this->data['crud'] = $this->crud;

        return view($this->crud->viewName . '.list', $this->data);
    }

    public function makeIndexJson($hasPaginator = false)
    {
        if (isset($_GET['searchParams'])) {
            $param      = json_decode($_GET['searchParams']);
            $this->data = $this->crud->model->where($param->key, $param->id)->get();
//            if ($param->key == 'id')
//                $this->data = collect([$this->data->toArray()]);
        } else {
            $this->data = $this->data ?? $this->crud->model->get();
        }

        return $hasPaginator ?
            $this->makePaginatorDataFromCollection($this->data) :
            json_encode($this->data);
    }


    private function makePaginatorDataFromCollection(Collection $data)
    {
        if (isset($_GET['page'])) {

            $total = $data->count();

            $temp = $data;

            $limit       = $_GET['limit'];
            $currentPage = $_GET['page'];
            $offset      = ($currentPage - 1) * $limit;

            $temp = $temp->forPage($currentPage, $limit);


            $i = 1;

            $data = ['rows' => []];
            // 生成最终数据
            foreach ($temp as $d) {
                //rowNumber只能在服务器端生成，否则会全部都从1开始
                $d              = json_decode(json_encode($d), true);
                $d['rowNumber'] = $offset + $i;
                $data['rows'][] = $d;
                $i++;
            }

            $data['total'] = $total;

        }

        return $data;
    }

    /**
     * 实现单表和一对一，一对多表（有外键）的查询
     * @param Request $request
     *
     * @return string
     */
    public function query(Request $request)
    {
        $queryString = $request->queryString;
        $data        = [];
        foreach ($this->crud->queryParams['groups'] as $param) {
            /** @var QueryParam $param */

            /** @var Model $model */
            $model = app($this->crud->queryParams['model']);

            if (empty($param->join)) {
                $model = $model->where($param->columns[0], 'like', '%' . $queryString . '%');
                for ($i = 1; $i < count($param->columns); $i++) {
                    $model = $model->orWhere($param->columns[ $i ], 'like', '%' . $queryString . '%');
                }
            } else {
                $model = $model->whereHas($param->join, function (Builder $query) use ($param, $queryString) {
                    $query->where($param->columns[0], 'like', '%' . $queryString . '%');
                    for ($i = 1; $i < count($param->columns); $i++) {
                        $query->orWhere($param->columns[ $i ], 'like', '%' . $queryString . '%');
                    }
                });
            }

            $d = $model->get()->map(function ($item) use ($param) {
                $map          = [];
                $map['group'] = $param->title;
                $map['id']    = $item->id;
                if (empty($param->join)) {
                    $map['id']  = $item->id;
                    $map['key'] = 'id';
                } else {
                    $j          = $param->join;
                    $map['id']  = $item->$j->id;
                    $map['key'] = $param->key;
                    $item       = $item->$j;
                }

                foreach ($param->maps as $k => $m) {
                    $a = explode('||', $m);
                    $v = '';
                    foreach ($a as $c) {
                        if ($item->$c) {
                            $v = $item->$c;
                            break;
                        }
                    }
                    $map[ $k ] = $v;
                }


                return $map;

            })->unique('id');

            if (count($d)) {
                $data[] = ['group' => $param->title, 'items' => $d];
            }

        }

        return json_encode($data);
    }

    public function getAdd()
    {
        $this->data['crud']  = $this->crud;
        $this->data['title'] = '新增' . $this->crud->title;

        return view($this->crud->viewName . '.store', $this->data);
    }

    public function storeCrud(Request $request)
    {

        $data            = $request->all();
        $this->validator = \Validator::make($data, $this->crud->model::rules(), $this->crud->model::messages());
        $this->validator->validate();

        $model = $this->crud->model->newInstance();
        $saved = $model->fill($data)->save();
        if ($saved) {
            $id = $model->id;
            $model->doAfterCU($this->doAfterCrudData);

            return json_encode(['success' => true, 'id' => $id]);
        } else {
            return redirect()->to($this->getRedirectUrl())->withInput()->withErrors(['0' => '新建' . $this->crud->title . '时出现错误，请联系管理员'],
                                                                                    $this->errorbag());
        }
    }

    public function getEdit($id)
    {
        $this->data['crud']  = $this->crud;
        $this->data['edit']  = true;
        $this->data['title'] = '编辑' . $this->crud->title;

        return view($this->crud->viewName . '.store', $this->data);
    }


    public function updateCrud(Request $request)
    {
        $data            = $request->all();
        $this->validator = \Validator::make($data, $this->crud->model::rules(), $this->crud->model::messages());
        $this->validator->validate();


        $model = $this->crud->model->find($data['id']);
        $saved = $model->update($data);
        if ($saved) {
            $id = $model->id;
            $model->doAfterCU($this->doAfterCrudData);

            return json_encode(['success' => true]);//$this->performSaveAction($id);
        } else {
            return redirect()->to($this->getRedirectUrl())->withInput()->withErrors(['0' => '修改' . $this->crud->title . '信息时出现错误，请联系管理员'],
                                                                                    $this->errorbag());
        }
    }


    public function del(Request $request)
    {
        $data         = $request->all();
        $success      = true;
        $successCount = 0;
        $failureCount = 0;
        $failId       = [];
        foreach ($data as $id) {

            try {
                if ($this->crud->model->find($id)->delete()) {
                    $this->crud->model->doAfterD($id);
                    $successCount += 1;
                } else {
                    $failureCount += 1;
                    $failId[]     = $id;
                    $success      = false;
                }
            } catch (\Throwable $e) {
                $failureCount += 1;
                $failId[]     = $id;
                $success      = false;
            }
        }
        if ($failureCount == 0) {
            $message = '删除' . $successCount . '条记录成功。';
        } else {
            if ($successCount == 0) {
                $message = '删除' . count($data) . '条记录失败，请联系管理员。';
            } else {
                $message = '删除' . $successCount . '条记录成功，有' . $failureCount . '条记录失败，请联系管理员。';
            }
        }

        return json_encode(['success' => $success, 'message' => $message]);
    }
}