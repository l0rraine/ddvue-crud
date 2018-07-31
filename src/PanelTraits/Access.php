<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 15:39
 */

namespace DDVue\Crud\PanelTraits;

trait Access
{
    /*
    |--------------------------------------------------------------------------
    |                                   CRUD ACCESS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a permission is enabled for a Crud Panel. Fail if not.
     *
     * @param string|array $permission
     *
     * @return bool|null
     */
    public function hasAccessOrFail($permission)
    {
        if (!empty($permission)) {
            if (!\Auth::user()->hasAnyPermission($permission)) {
                abort(401, '无访问权限');
            }
        }

        return true;
    }
}