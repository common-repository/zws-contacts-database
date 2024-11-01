jQuery(document).ready(function ($) {
    $("div.zws-contacts-db-times-available").each(function (index, element) {
        $("ul.contact-info-list-inner_" + index.toString()).dialog({
            autoOpen: false,
            modal: true,
            show: {effect: "fade", duration: 800},
            title: "Available Times",
            width: 500
        });
        $("button.modal_opener_" + index.toString()).click(function () {
            $("ul.contact-info-list-inner_" + index.toString()).dialog("open");
        });
    });
});