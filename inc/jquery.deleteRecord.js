jQuery(document).ready(function ($) {
    $("div.zws_contacts_db_delete_modal_outer").each(function (index, element) {
        // get blurb value to ensure it is returned to default if changed to present failure message
        var deletionBlurb = $('#zws-contacts-db-delete-blurb').text();
        $("div#zws_contacts_db_delete_modal_" + index.toString()).dialog({
            autoOpen: false,
            modal: true,
            show: {effect: "fade", duration: 800},
            hide: "explode",
            close: function (event, ui) {
                $(this).dialog("close");
            },
            title: "Modify user details",
            width: 600,
            dialogClass: 'zws-contacts-db-modal-delete-record',
            position: {my: "center", at: "center", of: window}
        });
        $("button#zws-contacts-db-record-delete-button_" + index.toString()).click(function () {
            $("div#zws_contacts_db_delete_modal_" + index.toString()).dialog("open");
            $("button#zws-contacts-db-record-delete-record-button_" + index.toString()).click(function () {
                // assign userid to variable
                var userID = $(this).attr("value");
                var currentURL = window.location.href + '&delete=' + userID;
                // send the delete command
                $.ajax({
                    url: currentURL
                })
                        .done(function (data) {
                            // success
                            if (data.indexOf('DELETION_SUCCESSFUL') > -1) {
                                // remove the record's element
                                $(".zws-contacts-database-display-all-inner-div_" + index.toString()).hide("explode").remove();
                                // close the dialog
                                $("div#zws_contacts_db_delete_modal_" + index.toString()).dialog().dialog("close");
                                // ensure blurb has been replaced
                                $("#zws-contacts-db-delete-blurb_" + index.toString())
                                        .text(deletionBlurb);
                            } else {
                                // alert that the deletion failed
                                $("#zws-contacts-db-delete-blurb_" + index.toString())
                                        .text("Deletion of this record failed. Please contact your technical support.");
                            }
                        })
                        .fail(function () {
                            // failure
                            $("#zws-contacts-db-delete-blurb_" + index.toString())
                                    .text("Deletion of this record failed. Please contact your technical support.");
                        })
                        .always(function () {
                            // completed
                        });

            });
        });
        $("button.zws-contacts-db-delete-cancel-btn").click(function () {
            // close the dialog
            $("div#zws_contacts_db_delete_modal_" + index.toString()).dialog().dialog("close");
            // ensure blurb has been replaced
            $("#zws-contacts-db-delete-blurb_" + index.toString())
                    .text(deletionBlurb);
        });
    });
});