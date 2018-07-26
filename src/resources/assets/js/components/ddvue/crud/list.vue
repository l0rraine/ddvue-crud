<template>
    <div style="margin-top:10px;">
        <el-row style="margin-bottom: 10px;">
            <el-col :span="18">
                <!--<div style="min-height: 20px;display:inline-block;"></div>-->
                <el-button type="primary" @click="handleExcel" v-if="showImportBtn">导入</el-button>
                <el-button type="primary" @click="handleAdd" v-if="showAddBtn">新增</el-button>
                <div class="check-toggle" v-show="showToggle">
                    <el-button type="danger" @click="handleDelete" v-if="showDelBtn">删除</el-button>
                    <slot name="check-toggle"></slot>
                </div>
                <div class="fix-slot">
                    <slot name="fix-slot"></slot>
                </div>

            </el-col>
            <el-col :span="6">
                <!--<el-autocomplete v-model="search" ref="auto"-->
                <!--placeholder="请输入搜索内容"-->
                <!--prefix-icon="el-icon-search"-->
                <!--@select="handleSearch"-->
                <!--:debounce="500"-->
                <!--:trigger-on-focus="false"-->
                <!--:fetch-suggestions="querySearch">-->
                <!--<template slot="suffix">-->
                <!--<a href="#" @click="clearQuery" v-show="showResetIcon"><i-->
                <!--class="el-input__icon el-icon-circle-close el-input__clear"></i></a>-->
                <!--</template>-->
                <!--</el-autocomplete>-->
                <!--TODO: 搜索时直接筛选表格  高级搜索-->
                <el-select v-if="searchMode=='filter'"
                           v-model="search" ref="search" class="pull-right"
                           filterable
                           remote
                           clearable
                           reserve-keyword
                           placeholder="请输入关键词"
                           :remote-method="querySearch"
                           @change="handleSearch"
                           @focus="onSearchFocus"
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

                <el-input v-if="searchMode == 'query'"
                          placeholder="请输入关键词并回车"
                          v-model="search"
                          @clear="clearQuery"
                          @change="handleQuery"
                          clearable>
                </el-input>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="24" style="margin-bottom:10px;">
                <ddv-crud-datatable :dataUrl="tableDataUrl"
                                    :paginate="showTablePagination"
                                    :canSelect="tableCanSelect"
                                    :isRecursive="tableIsRecursive"
                                    :eventName="tableEventName"
                                    @onSelection="handleTableSelectionChange">
                    <slot></slot>
                </ddv-crud-datatable>
            </el-col>
        </el-row>
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
                filtered: false,
                searchResult: [],
                searchLoading: false

            }
        },
        props: {
            tableDataUrl: String,
            showTablePagination: {
                type: Boolean,
                default: true
            },
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
            },
            tableCanSelect: {
                type: Boolean,
                default: true
            },
            queryUrl: {
                type: String,
                default: ''
            },
            tableEventName: {
                type: String,
                default: 'crudListTableChanged'
            },
            searchMode: {
                type: String,
                default: 'query' // query or filter
            }

        },
        watch: {
            search: function (val) {
                if (val === '') {
                    this.searchResult = [];
                }
            }
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
                this.showToggle = val.length;

                this.$emit('onTableSelectionChange', val);

            },
            handleExcel: function () {
                this.$emit('onImport');
            },
            handleSearch: function (item) {
                this.filtered = true;
                if (item) {//否则事件会触发2次
                    this.$eventHub.$emit(this.tableEventName, {
                        params: {
                            searchParams: item
                        }
                    });
                }

            },
            onSearchFocus: function () {
                if (this.search === '') {
                    this.searchResult = null;
                }

            },
            querySearch: _.debounce(function (queryString) {
                if (queryString === '') return;
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
            handleQuery: _.debounce(function (queryString) {
                this.filtered = true;
                if (queryString) {//否则事件会触发2次
                    this.$eventHub.$emit(this.tableEventName, {
                        params: {
                            searchParams: queryString
                        }
                    });
                }
            }, 300),
            clearQuery: function () {
                this.searchResult = [];
                if (this.filtered) {
                    this.$eventHub.$emit(this.tableEventName, {
                        params: {
                            searchParams: ''
                        }
                    });
                    this.filtered = false;
                }
            }

        }
    }
</script>

<style scoped>
    .check-toggle {
        display: inline;
        /*margin-left: 10px;*/
    }

    .fix-slot {
        display: inline;
        /*margin-left: 10px; */
    }
</style>