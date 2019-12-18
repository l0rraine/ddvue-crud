<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 9:59
 */

namespace DDVue\Crud\Controllers;

use DDVue\Crud\app\Models\QueryParam;
use DDVue\Crud\Controllers\Features\SaveActions;
use DDVue\Crud\CrudPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


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
        $this->crud->queryParams['model'] = $this->crud->modelName;
    }

    public function getIndex()
    {
        $this->data['crud'] = $this->crud;

        return view($this->crud->viewName . '.list', $this->data);
    }

    public function makeIndexJson($hasPaginator = false)
    {
        $this->data = $this->data ?? $this->crud->model->newQuery();

        if (isset($_GET['searchParams'])) {
            $param = json_decode($_GET['searchParams']) ?? $_GET['searchParams'];
            if (is_object($param)) { // 从前台的filter来的
                $this->data = $this->data->where($param->key, $param->id);
            } else { // 从前台的search来的
                $queryString = $param;
                if ($queryString) {
                    if (is_array($this->data)) {  // 数组只提供简单搜索

                        $this->data = collect($this->data)->filter(function ($value, $key) use ($queryString) {
                            $result  = false;
                            $columns = $this->crud->queryParams['groups'][0]->columns;
                            foreach ($columns as $p) {
                                $result = $result || strpos($value[ $p ], $queryString) !== false;
                            }

                            return $result;

                        });
                    }
                    if ($this->data instanceof Builder) {

                        $join_params = collect($this->crud->queryParams['groups'])->where('join', '!=', '');

                        if ($join_params->count()) {
                            $this->data = $this->data->WhereRaw('(1=0');
                            foreach ($join_params as $param) {
                                $this->data = $this->data->orWhereHas($param->join, function (Builder $query) use ($param, $queryString) {
                                    $query->where($param->columns[0], 'like', '%' . $queryString . '%');
                                    for ($i = 1; $i < count($param->columns); $i++) {
                                        $query->orWhere($param->columns[ $i ], 'like', '%' . $queryString . '%');
                                    }
                                });
                            }
                            $this->data = $this->data->OrWhereRaw(' 1=0');
                        }


                        $no_join_params = collect($this->crud->queryParams['groups'])->where('join', '');
                        if ($no_join_params->count()) {
                            $sql = '(1=0 or ';
                            foreach ($no_join_params as $param) {
                                $sql .= $param->columns[0] . ' like "%' . $queryString . '%"';

                                for ($i = 1; $i < count($param->columns); $i++) {
                                    $sql .= ' or ' . $param->columns[ $i ] . ' like "%' . $queryString . '%"';
                                }

                            }
                            $sql = $sql . ')';
                            if ($join_params->count())
                                $sql = $sql . ')';
                            $this->data = $this->data->OrWhereRaw($sql);
                        }


                    }

                }
            }
//            if ($param->key == 'id')
//                $this->data = collect([$this->data->toArray()]);
        }

        if (isset($_GET['filter'])) {

            $filters = json_decode(($_GET['filter']), true);

            $this->data = $this->data->where(function ($query) use ($filters) {
                $filtered = false;
                if (!is_array($filters))
                    return $query;
                foreach ($filters as $k => $filter) {
                    $not_null_filter = collect($filter)->reject(function ($v) {
                        return $v == 'null';
                    });

                    if (count($filter) > 1) { // 多个筛选条件
                        if (count($not_null_filter)) {
                            if (!$filtered) {
                                $filtered = true;
                                $query    = $query->whereRaw('1=0 ');
                                $query    = $query->orWhereIn($k, $not_null_filter);
                            } else {
                                $query = $query->orWhereIn($k, $not_null_filter);
                            }

                            $query = $query->orWhereRaw('1=0');
                        }
                        if (collect($filter)->contains('null')) {
                            if (!$filtered) {
                                $filtered = true;
                                $query    = $query->whereNull($k);
                            } else {
                                $query = $query->orWhereNull($k);
                            }

                        }
                    } else {// 单个筛选
                        if (count($filter) > 0) {
                            if ($filter[0] == 'not null') {
                                $query = $query->whereNotNull($k);
                            } else if ($filter[0] == 'null') {
                                $query = $query->whereNull($k);
                            } else {
                                $query = $query->where($k, $filter[0]);
                            }
                        }


                    }


                    $query->orderBy($k);

                }

                return $query;
            });

        }

        if (is_array($this->data)) {
            $this->data = collect($this->data);

        }

        return $hasPaginator ?
            $this->makePaginatorData($this->data) :
            json_encode($this->data);
    }

    /**
     * @param mixed $data
     *
     * @return array|Collection
     */
    protected function makePaginatorData($data)
    {
        if (isset($_GET['page'])) {

            $total = $data->count();

            $temp = $data;

            $limit       = $_GET['limit'];
            $currentPage = $_GET['page'];
            $offset      = ($currentPage - 1) * $limit;

            if (get_class($temp) == 'Illuminate\Database\Eloquent\Builder') {
                $temp = $temp->forPage($currentPage, $limit)->get();
            } else if ($temp instanceof Collection) {
                $temp = $temp->forPage($currentPage, $limit);
            }


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
        $data = $this->beforeSendIndexJson($data);


        return $data;
    }

    protected function beforeSendIndexJson($data)
    {
        return $data;
    }

    /**
     * 实现单表和一对一，一对多表（有外键）的查询
     *
     * @param Request $request
     *
     * @return string
     */
    public function query(Request $request)
    {
        $queryString = request('queryString');
        $data        = [];
        foreach ($this->crud->queryParams['groups'] as $param) {
            /** @var QueryParam $param */

            /** @var Model $model */
            $model = app($this->crud->queryParams['model']);

            if (is_string($queryString)) {
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
        $this->data      = $this->data ?? $request->all();
        $data            = $this->data;
        $this->validator = \Validator::make($data, $this->crud->model::rules(), $this->crud->model::messages());
        $this->validator->validate();

        $model = $this->crud->model->newInstance();
        $saved = $model->fill($data)->save();
        if ($saved) {
            $id                    = $model->id;
            $this->data['crud_id'] = $id;
            $model->doAfterCU($this->doAfterCrudData ?? $this->data);

            return json_encode(['success' => true, 'id' => $id]);
        } else {
            abort(401, '新建' . $this->crud->title . '时出现错误，请联系管理员');
//            return redirect()->to($this->getRedirectUrl())->withInput()->withErrors(['0' => '新建' . $this->crud->title . '时出现错误，请联系管理员'],
//                                                                                    $this->errorbag());
        }
    }

    public function getEdit($id)
    {
        $this->data['crud']  = $this->crud;
        $this->data['edit']  = true;
        $this->data['title'] = '编辑' . $this->crud->title;
        $this->data['data']  = $this->data['data'] ?? $this->crud->model->find($id);

        return view($this->crud->viewName . '.store', $this->data);
    }


    public function updateCrud(Request $request)
    {
        $this->data = $this->data ?? $request->all();
        $data       = $this->data;
        if (method_exists($this->crud->model, 'rules')) {
            $this->validator = \Validator::make($data, $this->crud->model::rules(), $this->crud->model::messages());
            $this->validator->validate();
        }


        $model = $this->crud->model->find($data['id']);
        $saved = $model->update($data);
        if ($saved) {
            $id = $model->id;
            if (method_exists($this->crud->model, 'doAfterCU')) {
                $model->doAfterCU($this->doAfterCrudData ?? $this->data);
            }

            return json_encode(['success' => true]);//$this->performSaveAction($id);
        } else {
            abort(401, '修改' . $this->crud->title . '时出现错误，请联系管理员');
//            return redirect()->to($this->getRedirectUrl())->withInput()->withErrors(['0' => '修改' . $this->crud->title . '信息时出现错误，请联系管理员'],
//                                                                                    $this->errorbag());
        }
    }


    public function del(Request $request)
    {
        $this->data   = $this->data ?? $request->all();
        $data         = $this->data;
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

    public function changeField($id, $field, $value)
    {
        DB::beginTransaction();
        try {
            $model = $this->crud->model->find($id);
            $model->update([$field => $value]);
            DB::commit();

            return json_encode(['success' => true, 'message' => '修改成功']);
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, '修改失败');
        }

    }

}