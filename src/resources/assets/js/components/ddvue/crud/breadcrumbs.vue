<template>
    <el-row type="flex" class="row-bg" justify="space-between">
        <el-col :xs="22" :sm="18" :md="10" :lg="12" :xl="12">
            <h3 style="padding:0 20px;">
                {{ title }}
            </h3>
        </el-col>
        <el-col :span="4">
            <el-breadcrumb separator-class="el-icon-arrow-right" v-for="(item,index) in items" :key="index">
                <el-breadcrumb-item @click.native="handleClick(item.link)">首页</el-breadcrumb-item>
            </el-breadcrumb>
        </el-col>
    </el-row>
</template>
<script>
    export default {
        name: 'DdvCrudBreadcrumbs',
        props: {
            items: Array,
            title: String
        },
        methods: {
            handleClick(link) {
                this.$emit('onClick',link);
            }
        }
    }
</script>
<style>
</style>