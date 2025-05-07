;(function ($) {

    $(document).ready(function () {
        let retryLicenceKeysButton = $('#coursesource-licence-generate-retry');
        if (retryLicenceKeysButton.length > 0) {
            $('#coursesource_order_keys').on('click', '#coursesource-licence-generate-retry', function (e) {
                let self = $(this);
                let buttonText = $(this).text();
                $(this).text('Fetching Keys');
                e.preventDefault();
                $.ajax({
                    url: Coursesource.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'cs_admin_order_licence-generate',
                        order_id: Coursesource.order_id,
                        nonce: Coursesource.cs_admin_order_nonce,
                    },
                }).done(function (result) {
                    $('#coursesource_order_keys-inner').replaceWith(JSON.parse(result));
                }).finally(function () {
                    self.text(buttonText);
                });
            })
        }
    });

})(jQuery);
