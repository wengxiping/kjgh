// (function ($) {
//
//     $(document).ready(function () {
//         // Popup Gallery Images
//         $("#action_page").on('click', function () {
//             console.log('plll');
//         });
//     });
// })(jQuery);
(function ($) {
    var _current = [];
    var _class = "";
    var _id = [];
    function action_page(total, id,status) {
        console.log(total,id,status);
        if (_current.length > 0) {
            _current.forEach(function(item,index){
                //循环数据存在和不存在 更新和添加
                if(_id.indexOf(id)<0){//表示数组中没有该id存在
                    _id.push(id);
                    _current.push({id: id, current_page: 2});
                    _class = 2;
                    return;
                }else{//更新，并结束
                    if(item.id == id){
                        item.current_page = pageNumStatus(total,status,item.current_page);
                        _class = item.current_page;
                        return;
                    }
                }
            });
        } else {
            _id.push(id);
            _class = 2;
            _current.push({id: id, current_page: 2});
        }
        displayShowHiddenAction(id,_class);
    }
    function pageNumStatus(total,status,num){
        var totalPage = Math.ceil(total/2);
        if(status == 1){
            if(num<totalPage){
                num = parseInt(num) +1;
            }else{
                num = totalPage;
            }
        }else{
            if(num == 1){
                num = 1;
            }else{
                num = num - 1;
            }
        }
        return num;
    }
    function displayShowHiddenAction(objectClassId,num){

        var object = jQuery(".xp-content-border-new"+objectClassId).find(".list-content");
        object.each(function(index,item){
            jQuery(item).removeClass('show');
            if(jQuery(item).hasClass('xp-row-gallery'+num)){
                jQuery(item).addClass('show');
            }
            // jQuery(index).find(".show"+num).addClass('show');
        });
    }
})(jQuery);
