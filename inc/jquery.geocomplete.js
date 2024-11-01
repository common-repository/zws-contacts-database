jQuery(document).ready(function ($) {
    var country_code = $('input#target_postcode').data('country');
    $('input#target_postcode').geocomplete({
        details: 'form',
        detailsAttribute: 'data-geo',
        componentRestrictions: {
            country: country_code
        }
    });
});
