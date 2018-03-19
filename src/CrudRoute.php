<?php
/**
 * Created by PhpStorm.
 * User: idn-lee
 * Date: 18-3-19
 * Time: 上午8:15
 */

namespace DDVue\Crud;


use Illuminate\Support\Facades\Route;

class CrudRoute
{
    /**
     * 生成crud模式的默认路由
     *
     * @param $url_prefix  string url前缀
     * @param $action      string controller名
     * @param $name_prefix string 路由名前缀
     */
    public static function make($url_prefix, $action, $name_prefix)
    {
        Route::get($url_prefix . '/', $action . '@getIndex')->name($name_prefix . '.index');
        Route::get($url_prefix . '/indexJson', $action . '@indexJson')->name($name_prefix . '.indexJson');
        Route::post($url_prefix . '/query', $action . '@query')->name($name_prefix . '.query');
        Route::get($url_prefix . '/add', $action . '@getAdd')->name($name_prefix . '.add');
        Route::post($url_prefix . '/add', $action . '@postAdd')->name($name_prefix . '.add.post');
        Route::get($url_prefix . '/edit/{id}', $action . '@getEdit')->name($name_prefix . '.edit');
        Route::post($url_prefix . '/edit', $action . '@postEdit')->name($name_prefix . '.edit.post');
        Route::post($url_prefix . '/del', $action . '@del')->name($name_prefix . '.del');
    }

}