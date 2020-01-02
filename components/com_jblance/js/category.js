var category_text=[];
jQuery(document).ready(function($){

    $(".item").click(function(){
        if($(".list-category-group .select-item").length==15 && $(this).attr("data-category-type") == "1"){
            return false;
        }
        addCategoryInput($(this).attr('data-category-id'),$(this).attr("data-category-txt"));
        if($(this).find("div").hasClass("choose-img")){//增加
            $(this).find(".choose-img").removeClass('choose-img').addClass('select-img');
            $(this).addClass('select-item');$(this).find(".txt").addClass('select-txt');
            $(this).attr('data-category-type',0);
        }else{//减少
            $(this).find(".select-img").removeClass('select-img').addClass('choose-img');
            $(this).removeClass('select-item');$(this).find(".txt").removeClass('select-txt');
            $(this).attr('data-category-type',1);
        }
        $("#skill_left_span").text("请添加("+$(".list-category-group .select-item").length+"/15"+")");
        if($(".list-category-group .select-item").length>0){
            $(".category-add").html("<div class='txt'>完成添加</div>");

        }else{
            $('.category-add').html("<div class='add-img'></div><div class='txt'>添加</div>")
        }
    })
    $(".category-add").click(function(){
        //添加显示列表
        if($("#list-category").hasClass("list-category-hidden")){
            $("#list-category").removeClass("list-category-hidden");
            if($(".list-category-group .select-item").length>0){
                $(".category-add").html("<div class='txt'>完成添加</div>");
            }else{
                $(this).html("<div class='add-img'></div><div class='txt'>添加</div>")
            }
        }else{
            $("#list-category").addClass("list-category-hidden");
            if($(".list-category-group .select-item").length>0){
                $(".category-add").html("<div class='txt'>编辑</div>");
                var t=" : ";
                category_text.forEach(function(item){
                    t +=item.category_name+"、";
                    console.log(item);
                });
                t = t.substring(0, t.length - 1);

                $("#category-text").html(t);
            }else{
                $(this).html("<div class='add-img'></div><div class='txt'>添加</div>")
            }
        }
    });
});
function addCategoryInput(value,txt){
    if(jQuery("#id-category-hidden-list").find("input").hasClass("id_category"+value)){//存在就去掉
        jQuery("#id-category-hidden-list").find("input[class=id_category"+value+"]").remove();
        category_text.forEach(function(item,index){
            if(item.id === value){
                category_text.splice(index,1);
            }
        });
    }else{//添加
        jQuery("#id-category-hidden-list").append("<input type='hidden' name='id_category[]' value="+value+" class='id_category"+value+"'>");
        category_text.push({'id':value,'category_name':txt});
    }
}
