<template>
    <el-select v-model="model" ref="depQuery"
               filterable
               remote
               :clearable="true"
               reserve-keyword
               :placeholder="placeholder"
               :remote-method="querySearch"
               @focus="onFocus"
               @change="onChange"
               @clear="clearQuery"
               :loading="searchLoading">
        <el-option-group ref="resultPopper"
                         v-for="group in searchResult"
                         :key="group.group"
                         :label="group.group">
            <el-option
                    v-for="item in group.items"
                    :key="item.id"
                    :label="item.value"
                    :value="item">
            </el-option>
        </el-option-group>
    </el-select>
</template>

<script>
    export default {
        name: "DdvDepartmentSelect",
        data() {
            return {
                search: '',
                filtered: false,
                searchResult: [],
                searchLoading: false
            }
        },
        props: {
            queryUrl: {
                type: String,
                default: ''
            },
            value: Object,
            placeholder: {
                type: String,
                default: '输入拼音首字母进行搜索'
            },
            clearAfterSelect: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            model: {
                get() {
                    return this.search;
                },
                set(v) {
                    this.search = v;
                }
            }
        },
        mounted() {
            this.search = this.value;
            this.querySearch();
        },
        methods: {
            onChange(s) {
                this.$emit('onChange', s);
                if (this.clearAfterSelect)
                {
                    this.model = '';
                    this.$refs.depQuery.blur();
                }
            },
            onFocus() {
                if (this.searchResult.length == 0) {
                    this.querySearch();
                }
            },
            querySearch: _.debounce(function (queryString) {
                const that = this;
                that.showResetIcon = true;
                that.searchLoading = true;
                that.$http.post(that.queryUrl || `${that.getMainUrl()}/query`, {
                    queryString: queryString
                }).then(function (response) {
                    // callback(response.data);
                    that.searchResult = response.data;
                }).finally(function () {
                    that.searchLoading = false;
                });
            }, 300),
            clearQuery: function () {
                this.searchResult = [];
                if (this.filtered) {
                    this.$emit('afterClear');
                    this.filtered = false;
                }
            }
        }
    }
</script>

<style scoped>

</style>