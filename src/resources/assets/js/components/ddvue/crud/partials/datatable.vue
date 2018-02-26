<template>
    <div>
        <el-row>
            <el-col :span="24">
                <el-table
                        :data="tableData"
                        ref="multipleTable"
                        tooltip-effect="dark"
                        @selection-change="handleSelectionChange"
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
                        :page-sizes="[30, 50, 100, all]"
                        :page-size="30"
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
                currentPage: 1,
                total: 0
            }
        },
        props: {
            dataUrl: String,
            paginate: Boolean

        },
        created: function () {
            let that = this;
            that.$http.get(that.dataUrl).then(function (response) {
                that.tableData = response.data;
            }).catch(function (response) {

            });
        },
        methods: {
            onSubmit(link) {
                console.log(link);
                this.$emit('onSubmit', link);
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            handleSizeChange(val) {
                console.log(`每页 ${val} 条`);
            },
            handleCurrentChange(val) {
                console.log(`当前页: ${val}`);
            }
        }
    }
</script>