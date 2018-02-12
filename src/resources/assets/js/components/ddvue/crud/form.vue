<template>
    <el-form label-width="80px">
        <slot name="fields"></slot>
        <el-form-item>
            <el-button type="primary" @click="onSubmit(url)">立即创建</el-button>
            <el-button>取消</el-button>
        </el-form-item>
    </el-form>
</template>
<script>
    export default {
        name: 'DdvCrudForm',
        props: {
            url: String,
        },
        methods: {
            onSubmit(link) {
                console.log(link);
                this.$emit('onSubmit',link);
            }
        }
    }
</script>