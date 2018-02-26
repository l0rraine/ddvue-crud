export const ListEditButton = {
    methods: {
        handleEdit:function(index,value){
            let that = this,
                url = `${that.getMainUrl()}/add`;
            that.insertEl(url);
        }
    }
};
