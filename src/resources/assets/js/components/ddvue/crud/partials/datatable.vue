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
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
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
                get () {
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
        methods: {
            getData() {
                const that = this;
                that.$http.get(that.dataUrl, that.param).then(function (response) {
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

                });
            },
            calculatePageSizes() {
                Array.prototype.push.apply(that.pageSizes, ps);
                that.pageSizes.splice();
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
                this.$emit('onSelection', val);
            },
            doRecursiveSelect(row,isCheck) {
                const that  = this,
                    table = that.$refs['multipleTable'];
                for (let i = 0; i < that.tableData.length; i++) {
                    const data = that.tableData[i];
                    if(isCheck){
                        if (data.class_list.indexOf(`,${row.id},`) !== -1 && data.class_layer > row.class_layer) {
                            table.toggleRowSelection(data, true);
                        }
                    }else{
                        if(row.class_list.indexOf(`,${data.id},`) !==-1 ){
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

                        this.doRecursiveSelect(row,isCheck);
                        this.popSelectEvent = true;
                    }
                }

            },
            handleSizeChange(val) {
                this.pageSize = val;
                this.getData();
            },
            handleCurrentChange(val) {
                this.currentPage = val;
                this.getData();
            }
        }
    }
</script>