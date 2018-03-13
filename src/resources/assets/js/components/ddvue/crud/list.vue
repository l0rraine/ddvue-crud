<template>
    <div style="margin-top:10px;">
        <el-row style="margin-bottom: 10px;">
            <el-col :span="18">
                <el-button type="primary" @click="handleExcel" v-if="showImportBtn">导入</el-button>
                <el-button type="primary" @click="handleAdd" v-if="showAddBtn">新增</el-button>
                <div class="check-toggle" v-show="showToggle">
                    <el-button type="danger" @click="handleDelete" v-if="showDelBtn">删除</el-button>
                    <slot name="check-toggle"></slot>
                </div>
                <div class="fix-slot">
                    <slot name="fix"></slot>
                </div>

            </el-col>
            <el-col :span="6">
                <el-input v-model="search" placeholder="请输入搜索内容"></el-input>
            </el-col>
            <el-col :span="24" style="margin-top:10px;">
                <ddv-crud-datatable :dataUrl="tableDataUrl"
                                    :paginate="showTablepagination"
                                    :isRecursive="tableIsRecursive"
                                    @onDataLoad="onTableLoadData"
                                    @onSelection="handleTableSelectionChange">
                    <slot></slot>
                </ddv-crud-datatable>
            </el-col>
        </el-row>
        <div id="dialogContainer"></div>
    </div>
</template>
<script>
    export default {
        name: 'DdvCrudList',
        data() {
            return {
                search: '',
                showEdit: false,
                showToggle: false,
                tableSelection: [],
            }
        },
        props: {
            tableDataUrl: String,
            showTablepagination: Boolean,
            tableIsRecursive: {
                type: Boolean,
                default: false
            },
            showImportBtn: {
                type: Boolean,
                default: true
            },
            showAddBtn: {
                type: Boolean,
                default: true
            },
            showDelBtn: {
                type: Boolean,
                default: true
            }

        },
        created: function () {

        },
        methods: {
            handleAdd: function () {
                this.$emit('onAdd');
            },
            handleDelete: function () {
                const s = this.tableSelection;
                this.$emit('onDelete', s);
            },
            handleTableSelectionChange: function (val) {
                this.tableSelection = val;
                if (val.length) this.showToggle = true;
                else this.showToggle = false;

                this.$emit('onTableSelectionChange', val);

            },
            handleExcel: function () {
                this.$emit('onImport');
            },
            onTableLoadData(data){
                this.$emit('onTableLoadData',data);
            }

        }
    }
</script>

<style scoped>
    .check-toggle {
        display: inline;
        margin-left: 10px;
    }

    .fix-slot {
        display: inline;
        margin-left: 10px;
    }
</style>