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
     * @param string $url_prefix  url前缀
     * @param string $action      controller名
     * @param string $name_prefix 路由名前缀
     * @param array  $urls        想要生成的路由列表
     */
    public static function make($url_prefix, $action, $name_prefix, $urls = ['index' => 1, 'indexJson' => 1, 'query' => 1, 'add' => 1, 'add.post' => 1, 'edit' => 1, 'edit.post' => 1, 'del' => 1])
    {

        if (isset($urls['index']) && $urls['index'] > 0) {
            Route::get($url_prefix . '/', $action . '@getIndex')->name($name_prefix . '.index');
        }
        if (isset($urls['indexJson']) && $urls['indexJson'] > 0) {
            Route::get($url_prefix . '/indexJson', $action . '@indexJson')->name($name_prefix . '.indexJson');
        }
        if (isset($urls['query']) && $urls['query'] > 0) {
            Route::post($url_prefix . '/query', $action . '@query')->name($name_prefix . '.query');
        }
        if (isset($urls['add']) && $urls['add'] > 0) {
            Route::get($url_prefix . '/add', $action . '@getAdd')->name($name_prefix . '.add');
        }
        if (isset($urls['add.post']) && $urls['add.post'] > 0) {
            Route::post($url_prefix . '/add', $action . '@postAdd')->name($name_prefix . '.add.post');
        }
        if (isset($urls['edit']) && $urls['edit'] > 0) {
            Route::get($url_prefix . '/edit/{id}', $action . '@getEdit')->name($name_prefix . '.edit');
        }
        if (isset($urls['edit.post']) && $urls['edit.post'] > 0) {
            Route::post($url_prefix . '/edit', $action . '@postEdit')->name($name_prefix . '.edit.post');
        }
        if (isset($urls['del']) && $urls['del'] > 0) {
            Route::post($url_prefix . '/del', $action . '@del')->name($name_prefix . '.del');
        }
    }

}