<template>
    <div>
        <div class="pull-left">
            <div>{{ item[topLeft] }}</div>
            <span style="font-size: 12px;color: #b4b4b4;" v-if="bottomLeft">{{ item[bottomLeft] }}</span>
        </div>
        <div class="pull-right" v-if="topRight || bottomRight">
            <div v-if="topRight" style="text-align: center;vertical-align: middle">{{ item[topRight] }}</div>
            <span style="font-size: 12px;color: #b4b4b4;" v-if="bottomRight">{{ item[bottomRight] }}</span>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'DdvAutocompleteDropdownItem',
        props: {
            item: Object,
            topLeft: String,
            topRight: String,
            bottomLeft: String,
            bottomRight: String
        }
    }
</script>