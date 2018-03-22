<template>
    <el-autocomplete v-model="search" ref="auto"
                     popper-class="my-autocomplete"
                     placeholder="请输入搜索内容"
                     prefix-icon="el-icon-search"
                     @select="handleSearch"
                     :debounce="500"
                     :trigger-on-focus="false"
                     :fetch-suggestions="querySearch">
        <template slot="suffix">
            <a href="#" @click="clearQuery" v-show="showResetIcon"><i
                    class="el-input__icon el-icon-circle-close el-input__clear"></i></a>
        </template>
        <template slot-scope="props">
            <ddv-autocomplete-dropdown-item
                    :item="props.item"
                    :topLeft="topLeft"
                    :topRight="topRight"
                    :bottomLeft="bottomLeft"
                    :bottomRight="bottomRight"
            ></ddv-autocomplete-dropdown-item>
        </template>

    </el-autocomplete>
</template>
<script>
    export default {
        name: 'DdvAutocomplete',
        data() {
            return {
                search: '',
                showResetIcon: false,
                searchResult: [],
            }
        },
        props: {
            queryUrl: String,
            topLeft: String,
            topRight: String,
            bottomLeft: String,
            bottomRight: String
        },
        methods: {
            handleSearch: function (item) {
                if (item) {//否则事件会触发2次
                    this.$emit('onSelect',item);
                }

            },
            querySearch: function (queryString, cb) {
                if (queryString === '') return;
                const that = this;
                that.showResetIcon = true;
                that.$http.post(that.queryUrl, {
                    queryString: queryString
                }).then(function (response) {
                    that.searchResult = response.data;
                    cb(that.searchResult);
                });
            },
            clearQuery: function () {
                this.search='';
                this.showResetIcon=false;
                this.$refs.auto.suggestions = [];
            }
        }
    }
</script>

<style>
    .my-autocomplete li{
        line-height: normal;
        padding: 7px;
    }
</style>