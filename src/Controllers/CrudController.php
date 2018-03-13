<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 9:59
 */

namespace DDVue\Crud\Controllers;

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
    }

    public function getIndex()
    {
        $this->data['crud'] = $this->crud;

        return view($this->crud->viewName . '.list', $this->data);
    }

    public function makeIndexJson($hasPaginator = false, $useEloquentModel = false)
    {

        if ($hasPaginator) {
            if ($useEloquentModel) {
                $this->data = $this->makePaginatorDataFromEloquent($this->data);
            } else {
                $this->data = $this->makePaginatorDataFromCollection($this->data);
            }
        }

        return json_encode($this->data);
    }

    private function makePaginatorDataFromEloquent($data)
    {
        // 进行分页
        if (isset($_GET['page'])) {

            $limit       = $_GET['limit'];
            $currentPage = $_GET['page'];
            $offset      = ($currentPage - 1) * $limit;

            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });


            $data       = $data->paginate($limit)->toArray();
            $r          = ['rows' => []];
            $r['total'] = $data['total'];

            $i = 1;

            // 生成最终数据
            foreach ($this->data['data'] as $d) {
                //rowNumber只能在服务器端生成，否则会全部都从1开始
                $d              = json_decode(json_encode($d), true);
                $d['rowNumber'] = $offset + $i;
                $r['rows'][]    = $d;
                $i++;
            }

            return $r;
        }
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
        $this->data['crud'] = $this->crud;
        $this->data['edit'] = true;
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

    public function getCustomEdit($id)
    {
        $this->data['crud'] = $this->crud;

        return view('crud::custom-update', $this->data);
    }

    public function postCustomEdit($id = null)
    {
        return $this->performSaveAction();
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