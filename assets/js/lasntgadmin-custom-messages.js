(function ($) {
    $(window).load(function () {
        let wp_editor = 'my-wp-editor';
        const status = $('.misc-pub-post-status');
        if (!$('#' + wp_editor).length) {
            return;
        }
        status.after(
            '<div class="misc-pub-section">' +
            '<span>' +
            '<a id="open-custom-cancellation-msg" href="#TB_inline?&width=600&height=500&inlineId=my-content-id" class="thickbox">Custom Cancellation Message</a>' +
            '</</span>' +
            '</div>'
        );

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
            })
            return false;
        })
    })
})(jQuery)