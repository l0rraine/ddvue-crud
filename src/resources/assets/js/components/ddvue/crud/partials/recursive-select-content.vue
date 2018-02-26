<template>
    <div>
        <el-option
                v-for="item in items"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            <span style="float: left">{{ item.label }}</span>
            <span style="float: right; color: #8492a6; font-size: 13px">{{ item.value }}</span>
        </el-option>
    </div>
</template>
<script>
    export default {
        name:'DdvCrudRecursiveSelectContent',
        props:{
            items:Object
        }
    }
</script>