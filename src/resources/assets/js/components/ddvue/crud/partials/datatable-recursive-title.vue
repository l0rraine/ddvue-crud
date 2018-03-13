<template>
    <span>
        <span v-if="step>1" :style="style"></span>
        <span v-if="step>0" class="folder-line pixels"></span>
        <i :class="iconClass" v-if="showIcon"></i>
        <slot></slot>
    </span>

</template>

<script>
    export default {
        name: 'DdvDatatableRecursiveTitle',
        computed: {
            step: function () {
                return parseInt(this.item.class_layer) - 1;
            },
            style: function () {
                return {
                    display: 'inline-block',
                    width: 24 * (this.step - 1) + 'px'
                }
            },
            iconClass: function () {
                const that=this;
                for (let key in that.item) {
                    if (key === that.icon) {
                        let v = that.item[key] || '';
                        if (v.trim() !== '')
                            return that.item[key];
                    }
                }
                return 'folder-open';

            }
        },
        props: {
            item: Object,
            showIcon: {
                type: Boolean,
                default: true
            },
            icon: {
                type: String,
                default: 'icon'
            }
        }
    }
</script>

<style scoped>
    .folder-open {
        display: inline-block;
        margin-right: 2px;
        width: 20px;
        height: 20px;
        background: url(/images/skin_icons.png) -40px -196px no-repeat;
        vertical-align: middle;
        text-indent: -999em;
        *text-indent: 0;
    }

    .folder-line {
        display: inline-block;
        margin-right: 2px;
        width: 20px;
        height: 20px;
        background: url(/images/skin_icons.png) -80px -196px no-repeat;
        vertical-align: middle;
        text-indent: -999em;
        *text-indent: 0;
    }

</style>