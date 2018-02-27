<template>
    <div>
        <template v-for="item in items">
            <template v-if="rootSelectable==false">
                <template v-if="item.children.length">
                    <el-option-group :label="item.title"
                                     :style="makeStyle(item)">
                        <ddv-crud-select-recursive-option :items="item.children"
                                                          :rootSelectable="rootSelectable"></ddv-crud-select-recursive-option>
                    </el-option-group>
                </template>
                <template v-else>
                    <el-option :key="item.id"
                               :label="item.title"
                               :style="makeStyle(item)"
                               :value="item.id"></el-option>
                </template>
            </template>
            <template v-else>
                <el-option :key="item.id"
                           :label="item.title"
                           :value="item.id"
                           style="height:100%;"
                           class="no-background">
                        <span :style="[{display:'block'}, makeStyle(item)]">{{ item.title }}</span>
                    <template v-if="item.children.length">
                        <ddv-crud-select-recursive-option :items="item.children"
                                                          :rootSelectable="rootSelectable"></ddv-crud-select-recursive-option>
                    </template>
                </el-option>


            </template>
        </template>
    </div>
</template>
<script>
    export default {
        name: 'DdvCrudSelectRecursiveOption',
        props: {
            items: Array,
            rootSelectable: {
                type: Boolean,
                default: false
            }
        },
        methods: {
            makeStyle: function (item) {
                let step = parseInt(item.class_layer) - 1;
                if (this.items.length === 1) {
                    step++;
                }
                return {
                    marginLeft: 16 * step + 'px',
                }
            }
        }
    }
</script>
<style scoped>
    .optionGroup {
        font-weight: bold !important;
        font-style: italic !important;
    }

    ul>div>li:first-child {
        padding-right:20px !important;
    }
    .no-background {
        background-color: transparent !important;
        padding-right:0 !important;
    }

    li span:hover {
        background-color: #f5f7fa;
    }
</style>