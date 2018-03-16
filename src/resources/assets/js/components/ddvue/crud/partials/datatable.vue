<template>
    <div>
        <el-row>
            <el-col :span="24">
                <el-table
                        :data="tableData"
                        ref="multipleTable"
                        tooltip-effect="dark"
                        @selection-change="handleSelectionChange"
                        @select="handleSelect"
                        style="width: 100%">
                    <el-table-column
                            type="selection"
                            width="55">
                    </el-table-column>
                    <slot></slot>
                </el-table>
            </el-col>
            <el-col :span="24">
                <el-pagination
                        v-if="paginate"
                        @current-change="handleCurrentChange"
                        @size-change="handleSizeChange"
                        :current-page="currentPage"
                        :page-sizes="pageSizes"
                        :page-size="pageSize"
                        layout="total, sizes, prev, pager, next, jumper"
                        :total="total">
                </el-pagination>
            </el-col>
        </el-row>
    </div>
</template>
<script>
    export default {
        name: 'DdvCrudDatatable',
        data() {
            return {
                tableData: [],
                multipleSelection: [],
                oldSelection: [],
                currentPage: 1,
                total: -1,
                pageSize: 10,
                popSelectEvent: true,
                pageSizesOrigin: [10, 20, 50],

            }
        },
        props: {
            dataUrl: String,
            paginate: Boolean,
            isRecursive: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            param: function () {
                if (this.paginate) {
                    return {
                        params: {
                            page: this.currentPage,
                            limit: this.pageSize
                        }
                    }
                }
                return '';
            },
            pageSizes: {
                get() {
                    const that = this;
                    let ps = that.pageSizesOrigin.slice();

                    ps.push(that.total);
                    let i = 0;

                    that.pageSizesOrigin.forEach(function (size) {
                        if (size > that.total) {
                            ps.splice(i, 1);
                        } else {
                            i++;
                        }
                    });
                    return ps;
                }
            }
        },
        created: function () {
            this.getData();
        },
        beforeDestroy() {
            this.$eventHub.$off('crudListTableDataLoad');
        },
        methods: {
            getData(p) {
                const that = this;
                //在created里面添加事件会一直不停地增加事件
                that.$eventHub.$off('crudListTableDataLoad');
                const param = p !== undefined ? that.deepMerge(p, that.param) : that.param;

                that.$http.get(that.dataUrl, param).then(function (response) {
                    const data = response.data;
                    Object.keys(data).forEach(function (key) {
                        if (key === 'total') {
                            that.tableData = data['rows'];
                            that.total = data['total'];
                        }
                    });
                    if (that.total === -1)
                        that.tableData = response.data;
                }).catch(function (response) {
                    console.log(response);
                });
                that.$eventHub.$on('crudListTableDataLoad', that.getData)
            },
            deepMerge: function (obj1, obj2) {
                var key;
                for (key in obj2) {
                    // 如果target(也就是obj1[key])存在，且是对象的话再去调用deepMerge，否则就是obj1[key]里面没这个对象，需要与obj2[key]合并
                    obj1[key] = obj1[key] && obj1[key].toString() === "[object Object]" ?
                                this.deepMerge(obj1[key], obj2[key]) : obj1[key] = obj2[key];
                }
                return obj1;
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
                this.$emit('onSelection', val);
            },
            doRecursiveSelect(row, isCheck) {
                const that  = this,
                      table = that.$refs['multipleTable'];
                for (let i = 0; i < that.tableData.length; i++) {
                    const data = that.tableData[i];
                    if (isCheck) {
                        if (data.class_list.indexOf(`,${row.id},`) !== -1 && data.class_layer > row.class_layer) {
                            table.toggleRowSelection(data, true);
                        }
                    } else {
                        if (row.class_list.indexOf(`,${data.id},`) !== -1) {
                            table.toggleRowSelection(data, false);
                        }
                        if (data.class_list.indexOf(`,${row.id},`) !== -1 && data.class_layer > row.class_layer) {
                            table.toggleRowSelection(data, false);
                        }
                    }

                }


            },
            handleSelect(selection, row) {
                if (this.isRecursive) {
                    if (this.popSelectEvent) {
                        this.popSelectEvent = false;
                        let isCheck = false;
                        selection.forEach(function (s) {
                            if (s === row) {
                                isCheck = true;
                            }
                        });

                        this.doRecursiveSelect(row, isCheck);
                        this.popSelectEvent = true;
                    }
                }

            },
            handleSizeChange(val) {
                this.pageSize = val;
            },
            handleCurrentChange(val) {
                this.currentPage = val;
            }
        }
    }
</script>