(function ($) {
    $(function () {

        $(document).off('click', '#error_handler_overlay_close');
        $(document).on('click', '#error_handler_overlay_close', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            $('#error_handler_overlay').remove();
        });

        $(document).off('submit', '[name=customer_order_details]');
        $(document).on('submit', '[name=customer_order_details]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            updateOrderCustomer('form[name=customer_order_details]');
        });

        $(document).off('submit', '[name=order_subscription_details]');
        $(document).on('submit', '[name=order_subscription_details]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            updateOrderCustomer('form[name=order_subscription_details]');
        });
        
        $(document).off('submit', '[name=creditcard_details]');
        $(document).on('submit', '[name=creditcard_details]', function (event) {
            console.log('hhh');
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            updateCreditCardDetails('form[name=creditcard_details]');
        });

        $(document).off('submit', '[name=login]');
        $(document).on('submit', '[name=login]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            memberLogin('form[name=login]');
        });

        $(document).off('submit', '[name=update-password]');
        $(document).on('submit', '[name=update-password]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            memberUpdate('form[name=update-password]');
        });
        
        $(document).off('submit', '[name=forgot-password]');
        $(document).on('submit', '[name=forgot-password]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            memberForgotPassword('form[name=forgot-password]');
        });
        
        $(document).off('submit', '[name=reset-password]');
        $(document).on('submit', '[name=reset-password]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            memberResetPassword('form[name=reset-password]');
        });
        
        $(document).off('submit', '[name=cancel-order]');
        $(document).on('submit', '[name=cancel-order]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            cancelOrder('form[name=cancel-order]');
        });
        
        $(document).off('submit', '[name=refund-order]');
        $(document).on('submit', '[name=refund-order]', function (event) {
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            if (!validateFormData($(this)))
            {
                return false;
            }
            var orderAmt = $('[name=order_amount]').val();
            var refundAmt = $('[name=refund_amount]').val();
            if(parseFloat(orderAmt) < parseFloat(refundAmt))
            {
                showMessage('#msg', 'alert alert-danger', 'Refund amount should be less than or equal to the order amount');
                clearMessage('#msg', 5000);
                return false;
            }
            refundOrder('form[name=refund-order]');
        });

        $(document).off('click', '[name=usps_track]');
        $(document).on('click', '[name=usps_track]', function (event) {
            $.ajax({
                url: '../' + AJAX_PATH + 'extensions/membership/track-order',
                method: 'post',
                data: {tracking_id: $('[name=tracking_no]').val()},
                success: function (data) {
                    console.log(data);
                    $('#track_details').html(data.trackDetails);
                    $('#status_code').val(data.statusCode);
                    $('#track_summary').html(data.trackSummary);
                }
            });
        });

        if ($('#order-lists').length)
        {
            getAllCustomerOrders($('.order-data'));
        }

        getCountries('shipping_country', 'shipping_state');
        getCountries('billing_country', 'billing_state');

        var cc_expiration_date = $('[name=cc_expiration_date]').val();

        if (typeof (cc_expiration_date) != 'undefined') {
            var exp_month = cc_expiration_date.substring(0, 2);
            var exp_year = cc_expiration_date.substring(2, 4);
            $('[id=expmonth]').val(exp_month);
            $('[id=expyear]').val(exp_year);
        }

        $(document).off('change', '[id=expmonth]');
        $(document).on('change', '[id=expmonth]', function (event) {
            var expMonth = $('[id=expmonth]').val();
            var expDate = expMonth + $('[id=expyear]').val();
            console.log(expDate);
            $('[name=cc_expiration_date]').val(expDate);
        });

        $(document).off('change', '[id=expyear]');
        $(document).on('change', '[id=expyear]', function (event) {
            var expYear = $('[id=expyear]').val();
            var expDate = $('[id=expmonth]').val() + expYear;
            console.log(expDate);
            $('[name=cc_expiration_date]').val(expDate);
        });

        window.onscroll = function () {
            fixedScroll()
        };

        $('.datepicker').datepicker({
            format: 'mm/dd/yyyy',
            weekStart: 1,
            daysOfWeekHighlighted: "6,0",
            autoclose: true,
            todayHighlight: true
        });

        $('.datepicker').datepicker().on('changeDate', function (ev) {
            $('[name=recurring_date]').val($(this).val());
        });

    });

})(jQuery);


var _self = $(this);
var errors = [];

var defaults = {
    errorModal: true,
    autoFillFormElement: false,
    countryDropdown: 'Select Country',
    ajaxLoader: {
        div: '',
        timeInOut: 0
    },
    responseLoader: {
        div: '',
        timeInOut: 0
    }
};
var _selfOptions = $.extend({}, defaults, _selfOptions);

var header = document.getElementById("myHeader");
if (header !== null)
{
    var sticky = header.offsetTop;
}


function fixedScroll() {
    if (window.pageYOffset >= sticky) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
}

function doAjaxCall(path, formData) {
    $.ajax({
        url: '../' + AJAX_PATH + 'extensions/membership/' + path,
        method: 'post',
        data: formData,
        beforeSend: function () {
            showLoader();
        },
        success: function (data) {
            hideLoader();
            modifyResponse(path, data);
        }
    });
}

function modifyResponse(path, data)
{
    switch (path)
    {
        case 'update-order':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', 'Order data has been updated successfully.');
            }
            else {
                showMessage('#msg', 'alert alert-danger', 'Something went wrong.');
            }
            clearMessage('#msg', 5000);
            break;
        case 'member-login':
            if (typeof data == 'object' && data.response_code == 100) {
                doAjaxCall('set-member-data', data);
            }
            else {
                if(typeof(data.message) == 'undefined') {
                    data.message = 'Email or Password Incorrect';
                }
                showMessage('#msg', 'alert alert-danger', data.message);
                clearMessage('#msg', 5000);
            }
            break;
        case 'set-member-data':
            window.location.href = OFFER_PATH + "member/dashboard.php";
            break;
        case 'member-update':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', data.message);
                lazyRedirect("member/login.php", 2000);
            } else {
                showMessage('#msg', 'alert alert-danger', data.message);
                clearMessage('#msg', 5000);
            }
            break;
        case 'forgot-password':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', data.message);
                lazyRedirect("member/reset-password.php", 2000);                
            } else {
                showMessage('#msg', 'alert alert-danger', data.message);
                clearMessage('#msg', 5000);
            }
            break;          
        case 'reset-password':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', data.message);
                lazyRedirect("member/login.php", 2000);                
            } else {
                showMessage('#msg', 'alert alert-danger', data.message);
                clearMessage('#msg', 5000);
            }
            break;
        case 'cancel-order':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', 'Your order has been cancelled successfully.');
            }
            else {
                showMessage('#msg', 'alert alert-danger', 'Something went wrong.');
            }
            clearMessage('#msg', 5000);
            lazyRedirect("member/orders.php", 2000); 
            break;
        case 'refund-order-request':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', 'Your order refund request has been initiated.');
            }
            else {
                showMessage('#msg', 'alert alert-danger', 'Something went wrong.');
            }
            clearMessage('#msg', 5000);
            lazyRedirect("member/orders.php", 2000); 
            break;
        case 'update-creditcard-details':
            if (typeof data == 'object' && data.response_code == 100) {
                showMessage('#msg', 'alert alert-success', 'Creditcard details updated successfully.');
                clearMessage('#msg', 5000);            
            } else {
                showMessage('#msg', 'alert alert-danger', 'Something went wrong.');
                clearMessage('#msg', 5000);
            }
            break;
    }
}

function lazyRedirect(url, time)
{
    setTimeout(function () {
        window.location.href = OFFER_PATH + url;
     }, time);
}

function updateOrderCustomer(form) {
    doAjaxCall('update-order', $(form).serialize());
}

function updateCreditCardDetails(form) {
    doAjaxCall('update-creditcard-details', $(form).serialize());
}

function clearMessage(selector, timeout)
{
    setTimeout(function () {
        $(selector).html('');
    }, timeout);
}

function memberLogin(form) {
    doAjaxCall('member-login', $(form).serialize());
}

function memberForgotPassword(form) {
    doAjaxCall('forgot-password', $(form).serialize());
}

function memberResetPassword(form) {
    doAjaxCall('reset-password', $(form).serialize());
}

function cancelOrder(form) {
    doAjaxCall('cancel-order', $(form).serialize());
}

function refundOrder(form) {
    doAjaxCall('refund-order-request', $(form).serialize());
}

function memberUpdate(form) {
    doAjaxCall('member-update', $(form).serialize());
}

function getAllCustomerOrders(obj) {
    showLoader();
    $.ajax({
        url: '../' + AJAX_PATH + 'extensions/membership/get-orders',
        method: 'post',
        success: function (data) {
            hideLoader();
            if (typeof data != 'undefined' && data != '')
            {
                $(obj).html(data);
            }
            else
            {
                $(obj).html('<div class="row" ><div class="col-sm-12">No Record found</div></div>');
            }

        }
    });
}

function showMessage(selector, type, message) {
    $(selector).html('<div class="' + type + ' custom-msg">' + message + '</div>');
}


function getCountries(countryElementName, stateElementName) {
    var countryElement = $('select[name=' + countryElementName + ']');
    var selectedCountryName = countryElement.data('selected');
    var countryElementHtml = '';
    var no_of_countries = 0;
    $.each(app_config.allowed_country_codes, function ($key, countryCode) {
        if (app_config.countries.hasOwnProperty(countryCode)) {
            no_of_countries++;
            countryElementHtml += '<option value="' + countryCode + '">' + app_config.countries[countryCode]['name'] + '</option>';
        }
    });
    if (no_of_countries != 1) {
        countryElementHtml = '<option value="">' + _selfOptions.countryDropdown + '</option>' + countryElementHtml;
    }

    countryElement.html(countryElementHtml).trigger('change');
    if (typeof selectedCountryName !== 'undefined' && selectedCountryName.length) {
        countryElement.val(selectedCountryName).trigger('change');
    }
    getStates(stateElementName, countryElementName);
}

function getStates(stateElementName, countryElementName) {
    var stateElement = $('input[name=' + stateElementName + ']');
    var selectedStateName = stateElement.data('selected');
    var countryElement = $('select[name=' + countryElementName + ']');
    if (countryElement.length === 0 || $(countryElement).val() === '') {
        return;
    }
    var stateElementHtml = '';
    var zipElementName = countryElementName.replace('Country', '') + 'Zip';
    if (app_config.country_lang_mapping.hasOwnProperty(countryElement.val())) {
        $('[name=' + stateElementName + ']').closest('p, div, tr').find('label').text(app_config.country_lang_mapping[countryElement.val()].state);
        $('[name=' + zipElementName + ']').closest('p, div, tr').find('label').text(app_config.country_lang_mapping[countryElement.val()].zip);
    } else {
        $('[name=' + stateElementName + ']').closest('p, div, tr').find('label').text('State: ');
        $('[name=' + zipElementName + ']').closest('p, div, tr').find('label').text('Zip: ');
    }
    $.each(app_config.countries[countryElement.val()]['states'], function (stateCode, value) {
        if (stateCode.length && countryElement.val() == 'US' && stateCode.match(/Armed Forces/) != null) {
            return;
        }
        stateElementHtml += '<option value="' + stateCode + '">' + value.name + '</option>';
    });
    if (stateElementHtml.length) {
        if (!$('select[name=' + stateElementName + ']').length) {
            var classes = stateElement.attr('class');
            $('<select name="' + stateElementName + '"/>').insertAfter(stateElement);
            var attributes = stateElement.get(0).attributes;
            stateElement.remove();
            addAttributesToElement($('select[name=' + stateElementName + ']'), attributes);
        }
        var stateLable = 'State';
        if (
                typeof (app_config.country_lang_mapping[countryElement.val()]) !== 'undefined' &&
                typeof (app_config.country_lang_mapping[countryElement.val()].state) !== 'undefined' &&
                (app_config.country_lang_mapping[countryElement.val()].state) != '')
        {
            stateLable = (app_config.country_lang_mapping[countryElement.val()].state).slice(0, -1);
        }
        var stateDefaultElementHtml = "<option value='' selected='selected'>Select " + stateLable + "</option>";
        $('select[name=' + stateElementName + ']').html(stateDefaultElementHtml + stateElementHtml);
        if (selectedStateName) {
            $('select[name=' + stateElementName + ']').val(selectedStateName);
        }
    } else {
        if ($('input[name=' + stateElementName + ']').length === 0) {
            stateElement = $('select[name=' + stateElementName + ']');
            $('<input type="text" name="' + stateElementName + '" readonly />').insertAfter(stateElement);
            var attributes = stateElement.get(0).attributes;
            stateElement.remove();
            addAttributesToElement($('input[name=' + stateElementName + ']'), attributes);
        }
        $('input[name=' + stateElementName + ']').removeAttr('readonly');
    }
}

function addAttributesToElement(element, attributes) {
    for (var i in attributes) {
        if (typeof attributes[i] !== 'object') {
            continue;
        }
        element.attr(attributes[i].name, attributes[i].value);
    }
}


function errorHandler(errors) {
    showMessage('#msg', 'alert alert-danger', getUI(errors));
    clearMessage('#msg', 5000);
    return;
}

function getUI(errors) {
    var msg = '';
    $.each(errors, function (key, value) {
        msg += value + '</br>';
    });
    return msg;
}

function validateFormData(form) {
    errors = [];
    $(form).find('input, select').not('[type=hidden], [type=submit]').each(function () {

        if ($(this).val() == '' && $(this).hasClass('required'))
        {
            errors.push($(this).data('error-message'));
        }
    });

    if (errors.length > 0)
    {
        errorHandler(errors);
        return false;
    }
    return true;
}

function showLoader() {
    $('#loading-indicator').show();
}

function hideLoader() {
    $('#loading-indicator').hide();
}
