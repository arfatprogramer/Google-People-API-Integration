
$(function () {
    const synccontacts = $('#synccontacts-table').DataTable();
    let isProcessingSync = false;

    SyncStatus();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    // syncContacts table  Buttons
    $('#synccontacts-reload').on('click', function () {
        synccontacts.ajax.reload(null, false); // Reloads data without resetting pagination
    });

    // this function update data to google single
    $(document).on('click', '.singleSyncContact', function (e) {
        e.preventDefault();
        let id = $(this).data('sync-id'); // Get contact ID dynamically
        let syncStatus = $(this).data('sync-status'); // Get contact ID dynamically

        // sync function
        const performSync = (DeletedConfirmation) => {

            $(this).css({ 'animation': 'spin 2s linear infinite', 'color': 'blue' });
            $.ajax({
                url: '/singleSyncById',
                method: 'post',
                data: {
                    Cliet_id: id,
                    deletedReSync: DeletedConfirmation,
                },
                success: function (response) {
                    console.log(response);
                    if (response.status) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            synccontacts.ajax.reload(null, false);
                        }, 2000);
                    } else {
                        toastr.error(response.message);
                        synccontacts.ajax.reload(null, false);
                    }

                }
            });
        }

        if (syncStatus === 'Deleted') {
            $('#ReCreateDeletedContactConfirmBox').removeAttr('hidden')
            // deletedReSync=confirm("This contact was deleted from Google. Do you want to re-create it on Google Contacts?");
            // deletedReSync ? (performSync()):(console.log("Canceld"));
            $('#ReCreateConfirm').off().on('click', function () {
                performSync(true);
                $('#ReCreateDeletedContactConfirmBox').attr('hidden', true);
            });

            $('#ReCreateCancel').off().on('click', function () {
                $('#ReCreateDeletedContactConfirmBox').attr('hidden', true);
            });

            $('#ReCreateColse').off().on('click', function () {
                $('#ReCreateDeletedContactConfirmBox').attr('hidden', true);
            });


        } else {
            performSync(false);
        }


    });


    // data tabe related functions to keep date out side of table
    $('#historyTable-pagination').html($('.clietssyncedhistory-table '));
    $('#historyTable-search').html($('.clietssyncedhistory-search'));

    $('#synccontacts-table-pagination').html($('.synccontacts-table'));
    $('#synccontacts-table-search').html($('.synccontacts-table-search'));


    // To swith table
    $('.tab-button').on('click', function () {
        // Deactivate all tabs
        $('.tab-button').attr('aria-selected', 'false');
        // $('.tab-button').removeAtt('style');
        $('.tab-button').removeAttr('style');

        // Hide all panels
        $('.tab-panel').attr('hidden', true);

        // Activate the clicked tab
        $(this).attr('aria-selected', 'true');
        $(this).css({
            'color': 'oklch(70.7% 0.165 254.624)',
            'font-weight': '600',
            'border-color': 'oklch(70.7% 0.165 254.624)',
            'box-shadow': '0 0 5px oklch(70.7% 0.165 254.624)'
        });



        // Show the corresponding panel
        var panelId = $(this).attr('aria-controls');
        $('#' + panelId).removeAttr('hidden');
    });

    // for Refreh data
    $("#refresh").click(function () {
        console.log("Refresh Button was Clicked");
        refresh()
        loader(true);
    });


    $("#pushToGoogle").click(function () {
        console.log("pushToGoogle Button was Clicked");
        pushToGoogle()
    });

    $("#importFromGoogle").click(function () {
        console.log("importFromGoogle Button was Clicked");

        importFromGoogle();

    });

    $(".syncNow").click(function () {
        synNow();
    });

    // this for cancel an ProcessWhile in pending state
    $('#cancelProcessing').click(function () {
        $.ajax({
            // url: "{{route('ajax.cancelPendingGoogleSync')}}", // Use this inside a Blade template
            url: "/cancelPendingGoogleSync", // Use this inside a Blade template
            method: 'DELETE',
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    console.log(response);
                }

            },
            error: function (error) {
                console.log(error);

            }
        })
    });


    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  Only Function Defination <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    function refresh() {
        $("#refresh").attr('disabled', true);

        $.ajax({
            url: "/refreshUrl",
            // method:'post',
            success: function (result) {
                console.log(result);
                if (result.status) {
                    $("#refresh").attr('disabled', false);
                    $('#contactsInCrm').text(result?.data?.crm);
                    $('#contactsInCrm1').text(result?.data?.crm);
                    $('#crmTotalClientSynced').text(result?.data?.crmTotalClientSynced);
                    $('#contactsInGoogle').text(result?.data?.TotalcontactInGoogle);
                    $('#contactsInGoogle1').text(result?.data?.TotalcontactInGoogle);
                    $('#contactsInGoogle2').text(result?.data?.TotalcontactInGoogle);
                    $('#pendingChangesOnCRM').text(result?.data?.pendingChangesOnCRM);
                    $('#pendingChangesOnGoogle').text(result?.data?.pendingChangesOnGoogle);
                    $('#remanigToImportFromGoogle').text(result?.data?.remanigToImportFromGoogle);
                    $('#contactsInError').text(result?.data?.lastSync?.error);
                    $('#lastSyncDate').text(timeToDateFormater(result?.data?.lastSync?.created_at));
                    $('#lastSyncNewContact').text(result?.data?.lastSync?.created);
                    $('#lastSyncNewContact1').text((result?.data?.lastSync?.created) + (result?.data?.lastSync?.createdAtGoogle));
                    $('#lastSyncUpdatedContact').text((result?.data?.lastSync?.updated) + (result?.data?.lastSync?.updatedAtGoogle));
                    $('#lastSyncDeletedContact').text(result?.data?.lastSync?.deleted);
                    $('#lastSyncChangesDeteted').text(result?.data?.lastSyncChangesDeteted);

                    loader(); // hide Loder
                    setTimeout(() => {
                        refresh();
                        console.log("auto Refresh Is Runnig in Refresh Function every 30seconds");
                    }, 30000);
                }
            },
            error: function (error) {
                console.log(error);
                loader();
            },
        });
    }

    function pushToGoogle() {
        console.log("pushToGoogle Started");
        $.ajax({
            url: 'pushToGoogle',
            method: 'get',
            success: function (response) {
                if (response.status) {
                    SyncStatus();
                    toastr.success(response.message);
                }
            },
            error: function (error) {
                console.log(error);
                toastr.error(response.error);
            },

        })

    }

    function importFromGoogle() {
        console.log("importFromGoogle Started");
        $.ajax({
            url: 'importFromGoogle',
            method: 'get',
            success: function (response) {
                if (response.status) {

                    SyncStatus();
                    toastr.success(response.message);
                }
            },
            error: function (error) {
                console.log(error);
                toastr.error(response.error);
            },

        })

    }

    function synNow() {
        console.log("SynNow Started");
        $.ajax({
            url: 'synNow',
            method: 'get',
            success: function (response) {
                if (response.status) {
                    SyncStatus();
                    toastr.success(response.message);
                }

            },
            error: function (error) {
                console.log(error);
                toastr.error(response.error);
            },

        })

    }

    function SyncStatus() {
        // button disable
        $("#pushToGoogle").prop("disabled", true);
        $("#importFromGoogle").prop("disabled", true);
        $(".syncNow").prop("disabled", true);

        // // Initial state
        $("#processBar").css("width", 0 + "%");
        $("#processPersentage").text(0 + "%");

        // Set interval to fetch status
        const interval = setInterval(async () => {
            console.log("Progress Bar Running");

            $.ajax({
                url: "syncStatus",
                method: 'get',
                success: function (response) {
                    if (response.status) {

                        let processing = response.data?.processing;
                        let width = response.data.progress;
                        let extimatedTime = response.data.extimetedTime;
                        console.log(processing);
                        loader();

                        // Animate the progress bar width with CSS transition
                        $("#processBar").css("transition", "width 1s ease-out");
                        $("#processBarSyned").text(response.data.lastSync?.synced ?? 0);
                        $("#processBarPending").text(response.data.lastSync?.pending ?? 0);
                        $("#processBarErros").text(response.data.lastSync?.errors ?? 0);
                        $('#cancelProcessing').removeAttr('hidden')

                        // Change colors and text based on sync status
                        if (width == 0 && processing) {
                            // $("#processBar").css("width", 100 + "%");
                            $("#processBar").css('backgroundColor', 'yellow');
                            $("#processBarText").text("Pending");
                            $("#processBarText").css('color', '#ffa225');

                        } else {
                            $("#processBar").css("width", width + "%");
                            $("#processPersentage").text(width + "%");
                            $("#processBar").css('backgroundColor', '#00C951');
                            $("#processBarText").text("Sync in Process Extimard Time : " + extimatedTime);
                            $("#processBarText").css('color', '#00C951');
                        }

                        // Handle completion of sync
                        if (!processing) {
                            $("#processBar").css("width", 100 + "%");
                            $("#processPersentage").text("100%");
                            $("#processBar").css('backgroundColor', '#00C951');

                            $("#processBarText").css('color', '#00C951');
                            clearInterval(interval);
                            refresh();

                            // Button Enable
                            $("#pushToGoogle").prop("disabled", false);
                            $("#importFromGoogle").prop("disabled", false);
                            $(".syncNow").prop("disabled", false);
                            $('#cancelProcessing').attr('hidden', true)

                            let lastSynced = response.data.lastSync?.created_at ?? null;
                            console.log(lastSynced);

                            if (lastSynced == null) {
                                $("#processBarText").text("Never Syned");

                            } else {
                                let now = new Date();
                                lastSynced = new Date(lastSynced);
                                let diffMinutes = Math.floor((now - lastSynced) / 60000); // Difference in minutes
                                let diffHours = Math.floor(diffMinutes / 60);
                                let minutesOnly = diffMinutes % 60;

                                let text = diffHours > 0
                                    ? `Last Synced ${diffHours}h ${minutesOnly}m ago`
                                    : `Last Synced ${diffMinutes} minutes ago`;
                                $("#processBarText").text(text);
                            }

                        }

                    }

                    if (response.error) {
                        console.log("Error fetching sync status:");
                        clearInterval(interval);
                        $("#pushToGoogle").prop("disabled", false);
                        $("#importFromGoogle").prop("disabled", false);
                        $(".syncNow").prop("disabled", false);
                    }

                },
                error: function (error) {
                    console.log("Error fetching sync status:", error);
                    clearInterval(interval);
                    SyncButtonEnableDisAble(true)
                },
            });
        }, 4000);
    }

    // for conver from time to date
    function timeToDateFormater(data) {
        if (!data) {
            return "No Data Found"
        }
        const date = new Date(data);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    //Loder Function
    function loader(params=false) {
        if (params) {
            $('#loader').show();
        } else {
            $('#loader').hide();
        }
    }

})
