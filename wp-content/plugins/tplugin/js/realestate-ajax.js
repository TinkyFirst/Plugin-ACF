jQuery(document).ready(function($) {
    $('#realestate-filter-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url : realestateAjax.ajaxurl,
            type : 'post',
            data : {
                action : 'realestate_filter',
                query : form.serialize()
            },
            success : function( response ) {
                $('#realestate-filter-results').html( response );
            }
        });
    });
});