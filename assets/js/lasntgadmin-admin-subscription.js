
(function ($) {
    $(window).load(function () {
        $('.lasntgadmin_subscription').click(function () {
            const $this = $(this);
            $.ajax({
                security: lasntgadmin_subscription_localize.subscribe_nonce,
                url: lasntgadmin_subscription_localize.adminurl,
                method: 'POST',
                data: {
                    action: 'lasntgadmin_subscribe',
                    id: $(this).data('id'),
                    security: lasntgadmin_subscription_localize.subscribe_nonce,
                },
                success: function(response){
                    if(response.status === 1){
                        $this.html('Subscribe');
                        return;
                    }
                    $this.html('Unsubscribe');
                },
                error: function(e){
                    console.log('e', e)
                }
            });

        });
    });

})(jQuery);