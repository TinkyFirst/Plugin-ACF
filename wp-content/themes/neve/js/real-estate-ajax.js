jQuery(document).ready(function ($) {
    $('#real-estate-filter-form form').on('submit', function (e) {
        e.preventDefault();
        var searchParams = {
            'action': 'filter_real_estate',
            'name': $('input[name="name"]').val(),
            'coordinates': $('input[name="coordinates"]').val(),
            'floors': $('select[name="floors"]').val(),
            'building_type': []
        };
        $('input[name="building_type[]"]:checked').each(function () {
            searchParams.building_type.push($(this).val());
        });

        // Используйте переменную из wp_localize_script для URL
        $.post(realEstateAjax.ajaxurl, searchParams, function (response) {
            $('#real-estate-search-results').html(response);
        });
    });
});
