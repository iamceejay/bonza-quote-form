(function ($) {
    "use strict";

    var BonzaQuoteAdmin = {
        init: function () {
            this.bindEvents();
            this.initTooltips();
            this.handleBulkActions();
        },

        bindEvents: function () {
            $(document).on(
                "change",
                ".bonza-quote-status-select",
                this.handleStatusChange.bind(this)
            );

            $(document).on(
                "click",
                ".bonza-delete-quote",
                this.confirmDeletion.bind(this)
            );

            $(document).on(
                "submit",
                "#quotes-filter",
                this.handleSearch.bind(this)
            );

            this.autoDismissNotices();

            $(document).on(
                "click",
                ".bonza-quick-edit",
                this.showQuickEdit.bind(this)
            );
            $(document).on(
                "click",
                ".bonza-quick-edit-cancel",
                this.hideQuickEdit.bind(this)
            );
            $(document).on(
                "click",
                ".bonza-quick-edit-save",
                this.saveQuickEdit.bind(this)
            );
        },

        handleStatusChange: function (e) {
            var $select = $(e.target);
            var $form = $select.closest("form");

            $select.prop("disabled", true);

            var newStatus = $select.val();
            var confirmMessage = "";

            if (newStatus === "rejected") {
                confirmMessage = "Are you sure you want to reject this quote?";
            } else if (newStatus === "approved") {
                confirmMessage = "Are you sure you want to approve this quote?";
            }

            if (confirmMessage && !confirm(confirmMessage)) {
                $select.val($select.data("original-value") || "pending");
                $select.prop("disabled", false);
                return false;
            }

            if (!$select.data("original-value")) {
                $select.data("original-value", $select.val());
            }

            $form.submit();
        },

        confirmDeletion: function (e) {
            var confirmText = bonza_quote_admin_ajax.messages.confirm_delete;

            if (!confirm(confirmText)) {
                e.preventDefault();
                return false;
            }

            $(e.target).text("Deleting...").prop("disabled", true);
        },

        handleSearch: function (e) {
            var $form = $(e.target);
            var $searchInput = $form.find('input[name="s"]');
            var searchValue = $searchInput.val().trim();

            if (searchValue === "") {
                $searchInput.prop("name", "");
            }

            $form.addClass("bonza-admin-loading");
        },

        initTooltips: function () {
            $(".column-status span").each(function () {
                var $badge = $(this);
                var status = $badge.text().toLowerCase();
                var tooltipText = "";

                switch (status) {
                    case "pending":
                        tooltipText = "Quote is awaiting review";
                        break;
                    case "approved":
                        tooltipText = "Quote has been approved";
                        break;
                    case "rejected":
                        tooltipText = "Quote has been rejected";
                        break;
                }

                if (tooltipText) {
                    $badge.attr("title", tooltipText);
                }
            });
        },

        handleBulkActions: function () {
            $(document).on("submit", "#posts-filter", function (e) {
                var $form = $(this);
                var action =
                    $form.find('select[name="action"]').val() ||
                    $form.find('select[name="action2"]').val();
                var $checkedBoxes = $form.find('input[name="quote[]"]:checked');

                if (action === "-1" || action === "") {
                    return true;
                }

                if ($checkedBoxes.length === 0) {
                    alert(
                        "Please select at least one quote to perform this action."
                    );
                    e.preventDefault();
                    return false;
                }

                if (action === "delete") {
                    var count = $checkedBoxes.length;
                    var confirmText =
                        "Are you sure you want to delete " +
                        count +
                        " quote(s)? This action cannot be undone.";

                    if (!confirm(confirmText)) {
                        e.preventDefault();
                        return false;
                    }
                }

                $form.addClass("bonza-admin-loading");
                $form
                    .find('input[type="submit"]')
                    .prop("disabled", true)
                    .val("Processing...");
            });
        },

        autoDismissNotices: function () {
            setTimeout(function () {
                $(".notice.is-dismissible").each(function () {
                    var $notice = $(this);
                    if (!$notice.hasClass("notice-error")) {
                        $notice.fadeOut(300, function () {
                            $(this).remove();
                        });
                    }
                });
            }, 5000);
        },

        showQuickEdit: function (e) {
            e.preventDefault();

            var $link = $(e.target);
            var $row = $link.closest("tr");
            var quoteId = $row.find('input[name="quote[]"]').val();

            var $quickEditRow = this.createQuickEditRow($row, quoteId);
            $row.hide().after($quickEditRow);
        },

        createQuickEditRow: function ($originalRow, quoteId) {
            var currentStatus = $originalRow
                .find(".column-status span")
                .text()
                .toLowerCase();
            var colspanCount = $originalRow.find("td").length;

            var $quickEditRow = $('<tr class="bonza-quick-edit-row">');
            var $cell = $('<td colspan="' + colspanCount + '">');

            var quickEditHtml =
                '<div class="bonza-quick-edit-form">' +
                "<h3>Quick Edit Quote #" +
                quoteId +
                "</h3>" +
                '<div class="bonza-quick-edit-fields">' +
                "<label>Status: " +
                '<select name="quick_edit_status">' +
                '<option value="pending"' +
                (currentStatus === "pending" ? " selected" : "") +
                ">Pending</option>" +
                '<option value="approved"' +
                (currentStatus === "approved" ? " selected" : "") +
                ">Approved</option>" +
                '<option value="rejected"' +
                (currentStatus === "rejected" ? " selected" : "") +
                ">Rejected</option>" +
                "</select>" +
                "</label>" +
                "</div>" +
                '<div class="bonza-quick-edit-actions">' +
                '<button type="button" class="button button-primary bonza-quick-edit-save" data-quote-id="' +
                quoteId +
                '">Update</button>' +
                '<button type="button" class="button bonza-quick-edit-cancel">Cancel</button>' +
                "</div>" +
                "</div>";

            $cell.html(quickEditHtml);
            $quickEditRow.append($cell);

            return $quickEditRow;
        },

        hideQuickEdit: function (e) {
            e.preventDefault();

            var $quickEditRow = $(e.target).closest(".bonza-quick-edit-row");
            var $originalRow = $quickEditRow.prev("tr");

            $quickEditRow.remove();
            $originalRow.show();
        },

        saveQuickEdit: function (e) {
            e.preventDefault();

            var $button = $(e.target);
            var $quickEditRow = $button.closest(".bonza-quick-edit-row");
            var quoteId = $button.data("quote-id");
            var newStatus = $quickEditRow
                .find('select[name="quick_edit_status"]')
                .val();

            $button.prop("disabled", true).text("Updating...");

            $.ajax({
                url: bonza_quote_admin_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "bonza_quote_quick_edit",
                    quote_id: quoteId,
                    status: newStatus,
                    nonce: bonza_quote_admin_ajax.nonce,
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(
                            response.data.message ||
                                bonza_quote_admin_ajax.messages.error
                        );
                        $button.prop("disabled", false).text("Update");
                    }
                },
                error: function () {
                    alert(bonza_quote_admin_ajax.messages.error);
                    $button.prop("disabled", false).text("Update");
                },
            });
        },

        showLoading: function ($element) {
            $element.addClass("bonza-admin-loading");
        },

        hideLoading: function ($element) {
            $element.removeClass("bonza-admin-loading");
        },
    };

    $(document).on(
        "click",
        'a[href*="action=approve"], a[href*="action=reject"], a[href*="action=delete"]',
        function (e) {
            var href = $(this).attr("href");
            var confirmMessage = "";

            if (href.indexOf("action=approve") !== -1) {
                confirmMessage = "Are you sure you want to approve this quote?";
            } else if (href.indexOf("action=reject") !== -1) {
                confirmMessage = "Are you sure you want to reject this quote?";
            }

            if (confirmMessage && !confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        }
    );

    $(document).ready(function () {
        BonzaQuoteAdmin.init();
    });

    window.BonzaQuoteAdmin = BonzaQuoteAdmin;
})(jQuery);
