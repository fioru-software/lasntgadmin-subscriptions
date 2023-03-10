(function ($) {
    $(window).load(function () {
        let wp_editor = 'my-wp-editor';
        const status = $('.misc-pub-post-status');
        if (!$('#' + wp_editor).length) {
            return;
        }
        const post_status = $('select[name="post_status"]');
        status.after(
            '<div class="misc-pub-section" id="open-custom-cancellation-msg-div">' +
            '<span>' +
            '<a id="open-custom-cancellation-msg" href="#TB_inline?&width=600&height=500&inlineId=my-content-id" class="thickbox">Custom Cancellation Message</a>' +
            '</</span>' +
            '</div>'
        );

        function show_hide_parent() {
            const parent_div = $('#open-custom-cancellation-msg-div');
            console.log('post_status', post_status.val())
            if (post_status.val() === 'cancelled') {
                parent_div.show();
            } else {
                parent_div.hide();
            }
        }
        show_hide_parent();
        $('.save-post-status').on('click', function () {
            show_hide_parent();
        })

        $("#open-custom-cancellation-msg").click(function () {
            setTimeout(function () {
                console.log('initialize')
                wp.editor.remove(wp_editor);
                wp.editor.initialize(wp_editor, {
                    tinymce: {
                        wpautop: true,
                        plugins: 'charmap colorpicker hr lists paste tabfocus textcolor fullscreen wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv,listbuttons',
                        toolbar2: 'styleselect,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        textarea_rows: 20
                    },
                    quicktags: {
                        buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'
                    },
                    mediaButtons: false,
                });

                $("#TB_ajaxContent").animate({
                    height: "90%",
                    width: "90%"
                }, {
                    duration: 800
                });
            }, 100)


        })

        $('#course_cancellation_form').on('submit', function (e) {
            e.preventDefault();
            const val = tinymce.get(wp_editor).getContent();
            const $this = $(this);
            $.ajax({
                url: lasntgadmin_custom_messages_admin_localize.adminurl,
                data: {
                    message: val,
                    subject: $('#custom_cancellation_subject').val(),
                    product_id: lasntgadmin_custom_messages_admin_localize.id,
                    security: lasntgadmin_custom_messages_admin_localize.nonce,
                    action: 'lasntgadmin_custom_cancel_message',
                },
                method: 'POST',
                beforeSend: function () {
                    console.log('before_send')
                    $this.prop('disabled', true);
                },
                success: function(resp){
                    $this.prop('disabled', false);
                    if(resp.status == 1){
                        alert('saved');
                        return;
                    }
                    alert(resp.msg);
                },
                error: function(){
                    alert('An error occurred. Please try again.')
                }
            })
            return false;
        })
    })
})(jQuery)