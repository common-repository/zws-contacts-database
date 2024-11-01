jQuery(document).ready(function ($) {
    $("div.zws_contacts_db_user_mod_outer").each(function (index, element) {
        $("div.zws_contacts_db_user_mod_" + index.toString()).dialog({
            autoOpen: false,
            modal: true,
            show: {effect: "fade", duration: 800},
            title: "Modify user details",
            width: 600,
            dialogClass:'zws-contacts-db-modal',
            position: {my: "center", at: "center", of: window}
        });
        $("button#user_mod_button_" + index.toString()).click(function () {
            $("div.zws_contacts_db_user_mod_" + index.toString()).dialog("open");
        });
    });
});