<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 8:26
 */

namespace DDVue\Crud\Controllers\Features;

trait SaveActions
{
    //TODO:改为使用SaveOptionsEnum

    /**
     * Get the save configured save action or the one stored in a session variable.
     *
     * @return [type] [description]
     */
    public function getSaveAction()
    {
        $saveAction = session('save_action', config('crud.default_save_action', 'save_and_back'));
        $saveOptions = [];
        $saveCurrent = [
            'value' => $saveAction,
            'label' => $this->getSaveActionButtonName($saveAction),
        ];

        switch ($saveAction) {
            case 'save_and_edit':
                $saveOptions['save_and_back'] = $this->getSaveActionButtonName('save_and_back');
                $saveOptions['save_and_new'] = $this->getSaveActionButtonName('save_and_new');
                break;
            case 'save_and_new':
                $saveOptions['save_and_back'] = $this->getSaveActionButtonName('save_and_back');
                //$saveOptions['save_and_edit'] = $this->getSaveActionButtonName('save_and_edit');
                break;
            case 'save_and_back':
            default:
                //$saveOptions['save_and_edit'] = $this->getSaveActionButtonName('save_and_edit');
                $saveOptions['save_and_new'] = $this->getSaveActionButtonName('save_and_new');
                break;
        }

        return [
            'active' => $saveCurrent,
            'options' => $saveOptions,
        ];
    }

    /**
     * Change the session variable that remembers what to do after the "Save" action.
     *
     * @param [type] $forceSaveAction [description]
     */
    public function setSaveAction($forceSaveAction = null)
    {
        if ($forceSaveAction) {
            $saveAction = $forceSaveAction;
        } else {
            $saveAction = \Request::input('save_action', config('crud.default_save_action', 'save_and_back'));
        }

        if (session('save_action', 'save_and_back') !== $saveAction) {
            //\Alert::info(trans('backpack::crud.save_action_changed_notification'))->flash();
        }

        session(['save_action' => $saveAction]);
    }

    /**
     * Redirect to the correct URL, depending on which save action has been selected.
     *
     * @param  [type] $itemId [description]
     * @return [type]         [description]
     */
    public function performSaveAction($itemId = null)
    {
        $saveAction = \Request::input('save_action', config('backback.crud.default_save_action', 'save_and_back'));
        $itemId = $itemId ? $itemId : \Request::input('id');

        switch ($saveAction) {
            case 'save_and_new':
                $redirectUrl = route($this->crud->route.'.add');
                break;
            case 'save_and_edit':
                $redirectUrl = route($this->crud->route.'.edit', ['id' => $itemId]);
                if (\Request::has('locale')) {
                    $redirectUrl .= '?locale='.\Request::input('locale');
                }
                break;
            case 'save_and_back':
            default:
                $redirectUrl = $this->crud->getIndexUrl();
                break;
        }

        return \Redirect::to($redirectUrl);
    }

    /**
     * Get the translated text for the Save button.
     *
     * @param  string $actionValue [description]
     * @return [type]              [description]
     */
    private function getSaveActionButtonName($actionValue = 'save_and_back')
    {
        switch ($actionValue) {
            case 'save_and_edit':
                return '保存并编辑';
                break;
            case 'save_and_new':
                return '保存并新建';
                break;
            case 'save_and_back':
            default:
                return '保存并返回';
                break;
        }
    }
}