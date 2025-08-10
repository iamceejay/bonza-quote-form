/**
 * Public-facing JavaScript for Bonza Quote Form
 *
 * @package    Bonza_Quote_Form
 * @subpackage Bonza_Quote_Form/public/js
 * @since      1.0.0
 */

(function ($) {
    "use strict";

    var BonzaQuoteForm = {
        init: function () {
            this.bindEvents();
            this.setupValidation();
        },

        bindEvents: function () {
            $(document).on(
                "submit",
                '.bonza-quote-form[data-ajax="true"]',
                this.handleAjaxSubmit.bind(this)
            );
            $(document).on(
                "input",
                ".bonza-quote-form input, .bonza-quote-form textarea",
                this.clearFieldError.bind(this)
            );

            $(document).on(
                "blur",
                ".bonza-quote-form input, .bonza-quote-form select, .bonza-quote-form textarea",
                function (e) {
                    if (
                        $(this).val().trim() !== "" ||
                        $(this).data("was-focused")
                    ) {
                        BonzaQuoteForm.validateField.call(BonzaQuoteForm, e);
                    }
                }
            );

            $(document).on(
                "focus",
                ".bonza-quote-form input, .bonza-quote-form select, .bonza-quote-form textarea",
                function () {
                    $(this).data("was-focused", true);
                }
            );
        },

        setupValidation: function () {
            $(
                ".bonza-quote-form input[required], .bonza-quote-form select[required], .bonza-quote-form textarea[required]"
            ).each(function () {
                var $field = $(this);
                var $label = $field.siblings("label").first();

                if ($label.find(".required").length === 0) {
                    $label.append(' <span class="required">*</span>');
                }
            });
        },

        handleAjaxSubmit: function (e) {
            e.preventDefault();

            var $form = $(e.target);
            var $container = $form.closest(".bonza-quote-form-container");
            var $submitButton = $form.find(".bonza-quote-form-submit");
            var $loading = $form.find(".bonza-quote-form-loading");

            if (!this.validateForm($form)) {
                this.showMessage(
                    $container,
                    bonza_quote_ajax.messages.validation,
                    "error"
                );
                return false;
            }

            this.clearMessages($container);

            this.setLoadingState($submitButton, $loading, true);

            var formData = new FormData($form[0]);
            formData.append("action", "bonza_quote_submit");

            $.ajax({
                url: bonza_quote_ajax.ajax_url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleAjaxSuccess.bind(
                    this,
                    $form,
                    $container,
                    $submitButton,
                    $loading
                ),
                error: this.handleAjaxError.bind(
                    this,
                    $form,
                    $container,
                    $submitButton,
                    $loading
                ),
            });

            return false;
        },

        handleAjaxSuccess: function (
            $form,
            $container,
            $submitButton,
            $loading,
            response
        ) {
            this.setLoadingState($submitButton, $loading, false);

            if (response.success) {
                this.showMessage($container, response.data.message, "success");

                $form[0].reset();
                this.clearAllFieldErrors($form);

                $(document).trigger("bonza_quote_form_success", [
                    response.data,
                ]);

                if (response.data.redirect) {
                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 2000);
                }
            } else {
                var message =
                    response.data.message || bonza_quote_ajax.messages.error;
                this.showMessage($container, message, "error");

                if (
                    response.data.errors &&
                    Array.isArray(response.data.errors)
                ) {
                    this.showFieldErrors($form, response.data.errors);
                }
            }
        },

        handleAjaxError: function (
            $form,
            $container,
            $submitButton,
            $loading,
            xhr,
            status,
            error
        ) {
            this.setLoadingState($submitButton, $loading, false);

            var message = bonza_quote_ajax.messages.error;

            if (
                xhr.responseJSON &&
                xhr.responseJSON.data &&
                xhr.responseJSON.data.message
            ) {
                message = xhr.responseJSON.data.message;
            }

            this.showMessage($container, message, "error");

            console.error("Bonza Quote Form AJAX Error:", {
                status: status,
                error: error,
                response: xhr.responseText,
            });
        },

        setLoadingState: function ($submitButton, $loading, isLoading) {
            if (isLoading) {
                $submitButton
                    .prop("disabled", true)
                    .data("original-text", $submitButton.text())
                    .text(bonza_quote_ajax.messages.processing);
                $loading.addClass("show");
            } else {
                $submitButton
                    .prop("disabled", false)
                    .text(
                        $submitButton.data("original-text") ||
                            bonza_quote_ajax.messages.submit
                    );
                $loading.removeClass("show");
            }
        },

        validateForm: function ($form) {
            var isValid = true;
            var $fields = $form.find(
                "input[required], select[required], textarea[required]"
            );

            $fields.each(function () {
                if (!BonzaQuoteForm.validateField({ target: this })) {
                    isValid = false;
                }
            });

            return isValid;
        },

        validateField: function (e) {
            var $field = $(e.target);
            var $row = $field.closest(".bonza-quote-form-row");
            var value = $field.val().trim();
            var isValid = true;
            var errorMessage = "";

            this.clearFieldError($field);

            if ($field.prop("required") && !value) {
                isValid = false;
                errorMessage = this.getFieldLabel($field) + " is required.";
            } else if (
                $field.attr("type") === "email" &&
                value &&
                !this.isValidEmail(value)
            ) {
                isValid = false;
                errorMessage = "Please enter a valid email address.";
            } else if (
                $field.attr("maxlength") &&
                value.length > parseInt($field.attr("maxlength"))
            ) {
                isValid = false;
                errorMessage =
                    this.getFieldLabel($field) +
                    " must be less than " +
                    $field.attr("maxlength") +
                    " characters.";
            }

            if (!isValid) {
                this.showFieldError($field, errorMessage);
            }

            return isValid;
        },

        showFieldError: function ($field, message) {
            var $row = $field.closest(".bonza-quote-form-row");
            $row.addClass("has-error");

            $row.find(".bonza-quote-form-error-message").remove();

            $(
                '<div class="bonza-quote-form-error-message">' +
                    this.escapeHtml(message) +
                    "</div>"
            ).insertAfter($field);
        },

        clearFieldError: function (e) {
            var $field = $(e.target);
            var $row = $field.closest(".bonza-quote-form-row");

            $row.removeClass("has-error");
            $row.find(".bonza-quote-form-error-message").remove();
        },

        clearAllFieldErrors: function ($form) {
            $form.find(".bonza-quote-form-row").removeClass("has-error");
            $form.find(".bonza-quote-form-error-message").remove();
        },

        showFieldErrors: function ($form, errors) {
            errors.forEach(function (error) {
                var $errorContainer = $(
                    '<div class="bonza-quote-form-error-message">' +
                        BonzaQuoteForm.escapeHtml(error) +
                        "</div>"
                );
                $form
                    .find(".bonza-quote-form-submit-row")
                    .before($errorContainer);
            });
        },

        showMessage: function ($container, message, type) {
            this.clearMessages($container);

            var $message = $(
                '<div class="bonza-quote-form-message bonza-quote-form-' +
                    type +
                    '">' +
                    this.escapeHtml(message) +
                    "</div>"
            );
            $container.prepend($message);

            // Scroll to message
            $("html, body").animate(
                {
                    scrollTop: $message.offset().top - 20,
                },
                300
            );
        },

        clearMessages: function ($container) {
            $container.find(".bonza-quote-form-message").remove();
        },

        getFieldLabel: function ($field) {
            var $label = $field.siblings("label").first();
            var labelText = $label
                .text()
                .replace(/\s*\*\s*$/, "")
                .trim();
            return labelText || "Field";
        },

        isValidEmail: function (email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        escapeHtml: function (text) {
            var map = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            };

            return text.replace(/[&<>"']/g, function (m) {
                return map[m];
            });
        },
    };

    $(document).ready(function () {
        BonzaQuoteForm.init();
    });

    window.BonzaQuoteForm = BonzaQuoteForm;
})(jQuery);
