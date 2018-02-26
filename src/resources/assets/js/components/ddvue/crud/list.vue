<template>
    <div style="margin-top:10px;">
        <el-row style="margin-bottom: 10px;">
            <el-col :span="18">
                <el-button type="primary" @click="handleAdd">新增</el-button>
                <div class="check-toggle">
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
                <ddv-crud-datatable :dataUrl="tableDataUrl" :paginate="showTablepagination">
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
                showEdit: false
            }
        },
        props: {
            tableDataUrl: String,
            showTablepagination: Boolean
        },
        created: function () {

        },
        methods: {
            handleAdd: function () {
                console.log('pressed add button');
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