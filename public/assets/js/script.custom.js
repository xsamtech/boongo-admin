/**
 * Custom scripts
 * 
 * @author Xanders Samoth
 * @see https://team.xsamtech.com/xanderssamoth
 */
/* Some variables */
const navigator = window.navigator;
const currentHost = $('[name="bng-url"]').attr('content');
const apiHost = $('[name="bng-api-url"]').attr('content');
const currentUser = $('[name="bng-visitor"]').attr('content');
const currentLanguage = $('html').attr('lang');
const headers = { 'Authorization': 'Bearer ' + $('[name="bng-ref"]').attr('content'), 'Accept': $('.mime-type').val(), 'X-localization': navigator.language };
const modalUser = $('#cropModalUser');
const retrievedAvatar = document.getElementById('retrieved_image');
const retrievedImageOther = document.getElementById('retrieved_image_other');
const currentImageOther = document.querySelector('#otherImageWrapper img');
let cropper;

/* Mobile user agent */
const userAgent = navigator.userAgent;
const normalizedUserAgent = userAgent.toLowerCase();
const standalone = navigator.standalone;

const isIos = /ip(ad|hone|od)/.test(normalizedUserAgent) || navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
const isAndroid = /android/.test(normalizedUserAgent);
const isSafari = /safari/.test(normalizedUserAgent);
const isWebview = (isAndroid && /; wv\)/.test(normalizedUserAgent)) || (isIos && !standalone && !isSafari);

/**
 * If the window is webview, hide some elements
 */
if (isWebview) {
    $('.detect-webview').addClass('d-none');

} else {
    $('.detect-webview').removeClass('d-none');
}

/**
 * Get cookie by name
 * 
 * @param string cname
 */
function getCookie(cname) {
    let name = cname + '=';
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');

    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];

        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }

    return '';
}

/**
 * Toggle Password Visibility
 * 
 * @param string current
 * @param string element
 */
function passwordVisible(current, element) {
    var el = document.getElementById(element);

    if (el.type === 'password') {
        el.type = 'text';
        current.innerHTML = '<i class="fa-solid fa-eye-slash"></i>'

    } else {
        el.type = 'password';
        current.innerHTML = '<i class="fa-solid fa-eye"></i>'
    }
}

/**
 * Switch between two elements visibility
 * 
 * @param string current
 * @param string element1
 * @param string element2
 * @param string message1
 * @param string message2
 */
function switchDisplay(current, form_id, element1, element2, message1, message2) {
    var _form = document.getElementById(form_id);
    var el1 = document.getElementById(element1);
    var el2 = document.getElementById(element2);

    _form.reset();
    el1.classList.toggle('d-none');
    el2.classList.toggle('d-none');

    if (el1.classList.contains('d-none')) {
        current.innerHTML = message1;
    }

    if (el2.classList.contains('d-none')) {
        current.innerHTML = message2;
    }
}

/**
 * Token writter
 * 
 * @param string id
 */
function tokenWritter(id) {
    var _val = document.getElementById(id).value;
    var _splitId = id.split('_');
    var key = event.keyCode || event.charCode;

    if (key === 8 || key === 46 || key === 37) {
        if (_splitId[2] !== '1') {
            var previousElement = document.getElementById('check_digit_' + (parseInt(_splitId[2]) - 1));

            previousElement.focus();
        }

    } else {
        var nextElement = document.getElementById('check_digit_' + (parseInt(_splitId[2]) + 1));

        if (key === 39) {
            nextElement.focus();
        }

        if (_splitId[2] !== '7') {
            if (_val !== undefined && Number.isInteger(parseInt(_val))) {
                nextElement.focus();
            }
        }
    }
}

/**
 * Dynamically load JS files
 */
function loadAllJS() {
    // $.getScript('/assets/...');
}

$(document).ready(function () {
    /* Bootstrap Tooltip */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    /* Custom Boostrap stylesheet */
    $('input, select, textarea, .navbar, .card, .btn').addClass('shadow-0');
    $('.btn').css({ textTransform: 'inherit', paddingBottom: '0.5rem' });

    /* Click to back to top */
    let btnBackTop = document.getElementById('btnBackTop');

    $(btnBackTop).click(function (e) { e.stopPropagation(); $('html, body').animate({ scrollTop: '0' }); });

    /* When the user scrolls down 20px from the top of the document, show the button */
    window.onscroll = function() { scrollFunction() };

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            btnBackTop.classList.remove('d-none');

        } else {
            btnBackTop.classList.add('d-none');
        }
    }

    /* When the user clicks on the button, scroll to the top of the document */
    function backToTop() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    }

    /* Auto-resize textarea */
    autosize($('textarea'));

    /* jQuery UI date picker */
    $('#register_birthdate, #register_otherdate').datepicker({
        dateFormat: currentLanguage.startsWith('fr') ? 'dd/mm/yy' : 'mm/dd/yy',
        onSelect: function () {
            $(this).focus();
        }
    });

    /* Card hover effect */
    $('.card .stretched-link').each(function () {
        $(this).hover(function () {
            $(this).addClass('changed');

        }, function () {
            $(this).removeClass('changed');
        });
    });

    /* Upload cropped user image */
    $('#avatar').on('change', function (e) {
        var files = e.target.files;
        var done = function (url) {
            retrievedAvatar.src = url;
            var modal = new bootstrap.Modal(document.getElementById('cropModalUser'), { keyboard: false });

            modal.show();
        };

        if (files && files.length > 0) {
            var reader = new FileReader();

            reader.onload = function () {
                done(reader.result);
            };
            reader.readAsDataURL(files[0]);
        }
    });

    $(modalUser).on('shown.bs.modal', function () {
        cropper = new Cropper(retrievedAvatar, {
            aspectRatio: 1,
            viewMode: 3,
            preview: '#cropModalUser .preview',
            done: function (data) { console.log(data); },
            error: function (data) { console.log(data); }
        });

    }).on('hidden.bs.modal', function () {
        cropper.destroy();

        cropper = null;
    });

    $('#cropModalUser #crop_avatar').click(function () {
        // Ajax loading image to tell user to wait
        $('.user-image').attr('src', currentHost + '/assets/img/ajax-loading.gif');

        var canvas = cropper.getCroppedCanvas({
            width: 700,
            height: 700
        });

        canvas.toBlob(function (blob) {
            URL.createObjectURL(blob);

            var reader = new FileReader();

            reader.readAsDataURL(blob);
            reader.onloadend = function () {
                var base64_data = reader.result;
                var userId = document.getElementById('user_id').value;
                var mUrl = apiHost + '/user/update_avatar_picture/' + parseInt(currentUser.split('-')[1]);
                var datas = JSON.stringify({ 'id': parseInt(currentUser.split('-')[1]), 'user_id': userId, 'image_64': base64_data, 'account_owner_id': userId });

                $.ajax({
                    headers: headers,
                    type: 'PUT',
                    contentType: 'application/json',
                    url: mUrl,
                    dataType: 'json',
                    data: datas,
                    success: function (res) {
                        $('.user-image').attr('src', res);
                        window.location.reload();
                    },
                    error: function (xhr, error, status_description) {
                        console.log(xhr.responseJSON);
                        console.log(xhr.status);
                        console.log(error);
                        console.log(status_description);
                    }
                });
            };
        });
    });

    /* Display cropped image */
    $('#image_other').on('change', function (e) {
        var files = e.target.files;
        var done = function (url) {
            retrievedImageOther.src = url;
            var modal = new bootstrap.Modal(document.getElementById('cropModalOther'), { keyboard: false });

            modal.show();
        };

        if (files && files.length > 0) {
            var reader = new FileReader();

            reader.onload = function () {
                done(reader.result);
            };
            reader.readAsDataURL(files[0]);
        }
    });

    $('#cropModalOther').on('shown.bs.modal', function () {
        cropper = new Cropper(retrievedImageOther, {
            aspectRatio: 1,
            viewMode: 3,
            preview: '#cropModalOther .preview'
        });

    }).on('hidden.bs.modal', function () {
        cropper.destroy();

        cropper = null;
    });

    $('#cropModalOther #crop_other').on('click', function () {
        var canvas = cropper.getCroppedCanvas({
            width: 700,
            height: 700
        });

        canvas.toBlob(function (blob) {
            URL.createObjectURL(blob);
            var reader = new FileReader();

            reader.readAsDataURL(blob);
            reader.onloadend = function () {
                var base64_data = reader.result;

                $(currentImageOther).attr('src', base64_data);
                $('#image_64').attr('value', base64_data);
                $('#otherImageWrapper p').removeClass('d-none');
            };
        });
    });
});

/* Set all notifications/messages status as read */
function markAllRead(entity, id) {
    var datas = entity == 'notification' ? JSON.stringify({ 'user_id': parseInt(id) }) : JSON.stringify({ 'message_id': parseInt(id) });

    $.ajax({
        headers: headers,
        type: 'PUT',
        contentType: 'application/json',
        url: apiHost + '/' + entity + '/mark_all_read/' + parseInt(id),
        dataType: 'json',
        data: datas,
        success: function () {
            window.location.reload();
        },
        error: function (xhr, error, status_description) {
            console.log(xhr.responseJSON);
            console.log(xhr.status);
            console.log(error);
            console.log(status_description);
        }
    });
}

/* Set notification/message status as read */
function switchRead(entity, element) {
    var element_id = element.id;
    var id = element_id.split('-')[1];

    $.ajax({
        headers: headers,
        type: 'PUT',
        contentType: 'application/json',
        url: apiHost + '/' + entity + '/switch_status/' + parseInt(id),
        dataType: 'json',
        data: JSON.stringify({ 'id': parseInt(id) }),
        success: function () {
            window.location.reload();
        },
        error: function (xhr, error, status_description) {
            console.log(xhr.responseJSON);
            console.log(xhr.status);
            console.log(error);
            console.log(status_description);
        }
    });
}
