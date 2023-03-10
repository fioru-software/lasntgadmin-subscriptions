(function ($) {
    $(window).load(function () {
        $('.parent_cat').on('change', function(){
            
            const checked = $(this).is(":checked");
            const id = $(this).data('id');
            
            $('.cat-list').find('ul[data-id='+ id +']').find('input').each(function(k, checkbox){
                console.log("🚀 ~ file: lasntgadmin-subscription-list.js:8 ~ $ ~ checkbox", checkbox)
                console.log('input[data-parent='+id+']')
                $('input[data-parent='+id+']').prop('checked', checked)
            })
        })

        $('.select_all_list').on('change', function(){
            const checked = $(this).is(":checked");
            console.log("🚀 ~ file: lasntgadmin-subscription-list.js:18 ~ $ ~ $(this).closest('ul')", $(this).closest('ul'))
            $(this).closest('ul').find('input').each(function(k, checkbox){
                $(checkbox).prop('checked', checked)
            })
        })
    });
})(jQuery)