jQuery(function ($) {
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            tags: true,
            tokenSeparators: [',', ' ']
        });
    });
});
