<template>
    <el-row type="flex" class="row-bg" justify="space-between" style="border-bottom: 1px solid #E5E5E5;">
        <el-col :xs="22" :sm="18" :md="10" :lg="12" :xl="12">
            <h4>{{ config.title }}
                <small>{{ config.subtitle }}</small>
            </h4>
        </el-col>
        <el-col>
            <el-breadcrumb separator-class="el-icon-arrow-right">
                <el-breadcrumb-item @click.native="gotoWelcome()">首页</el-breadcrumb-item>
                <el-breadcrumb-item v-for="(item,index) in config.items" :key="index"
                                    @click.native="handleClick(item.link)">{{ item.title }}
                </el-breadcrumb-item>
            </el-breadcrumb>
        </el-col>
    </el-row>
</template>
<script>
    export default {
        name: 'DdvCrudBreadcrumbs',
        props: {
            config: Object
        },
        methods: {
            handleClick(link) {
                this.$emit('onItemClick', link);
            },
            gotoWelcome() {
                const link = `${window.config.dashboard_url_prefix}/welcome`;
                this.$emit('onItemClick', link);
            }
        }
    }
</script>
<style scoped>
    .el-breadcrumb {
        float: right !important;
        line-height: 42px;
    }
</style>