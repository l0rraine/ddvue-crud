<?php
/**
 * Created by PhpStorm.
 * User: idn-lee
 * Date: 18-3-16
 * Time: 下午3:16
 */

namespace DDVue\Crud\app\Models;


class QueryParam
{
    /**
     * @var string 组名，同时也是前台select下拉列表中的group title
     */
    public $title;

    /**
     * @var string 需要查询的数据在模型的外联里时设置，默认为空
     */
    public $join = '';

    /**
     * @var string 外联的foreign key
     */
    public $key = '';

    /**
     * @var array 需要查询的列名数组
     */
    public $columns = [];

    /**
     * @var array 映射的列，必须含有key为value的项，该项用于前台下拉列表显示。某一项不一定有值时，可以用||隔开的方式定义多列对应
     */
    public $maps = [];


    /**
     * QueryParam constructor.
     *
     * @param string $title   组名，同时也是前台select下拉列表中的group title
     * @param string $join    需要查询的数据在模型的外联里时设置，默认为空，值要取模型的$with属性内的值
     * @param string $key     外联的foreign key
     * @param array  $columns 需要查询的列名数组
     * @param array  $maps    映射的列，必须含有key为value的项，该项用于前台下拉列表显示。某一项不一定有值时，可以用||隔开的方式定义多列对应
     */
    public function __construct(string $title, array $columns, array $maps, string $join = '', string $key = '')
    {
        $this->title   = $title;
        $this->join    = $join;
        $this->key     = $key;
        $this->columns = $columns;
        $this->maps    = $maps;

    }

}