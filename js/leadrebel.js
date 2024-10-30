jQuery(document).ready(function ($) {

    $('.account_no_exist').on('click', function (e) {
        e.preventDefault();
        $('#form_sign_up').addClass('hide');
        $('#form_register').removeClass('hide');
    })

    $('.login_leadrebel').on('click', function (e) {
        e.preventDefault()
        $('#form_register').addClass('hide');
        $('#form_sign_up').removeClass('hide');
    })

    const first_name = document.getElementById('firstname');
    const password_sign = document.getElementById('password_sign');
    const email_sign = document.getElementById('email_sign');
    const last_name = document.getElementById('lastname');
    const email = document.getElementById('email');
    const password = document.getElementById('password');

//Show input error messages
    function showError(input, message) {
        const formControl = input.parentElement;
        formControl.className = 'form-control custom_error';
        const small = formControl.querySelector('small');
        small.innerText = message;
        return false;
    }

//show success colour
    function showSucces(input) {
        const formControl = input.parentElement;
        formControl.className = 'form-control success';
        return true;
    }

//check email is valid
    function checkEmail(input) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (re.test(input.value.trim())) {
            showSucces(input)
        } else {
            showError(input, 'Email is not invalid');
        }
    }


//checkRequired fields
    function checkRequired(inputArr) {
        inputArr.forEach(function (input) {
            if (input.value.trim() === '') {
                showError(input, `${getFieldName(input)} is required`)
            } else {
                showSucces(input);
            }
        });
    }


//check input Length
    function checkLength(input, min, max) {
        if (input.value.length < min) {
            showError(input, `${getFieldName(input)} must be at least ${min} characters`);
        } else if (input.value.length > max) {
            showError(input, `${getFieldName(input)} must be les than ${max} characters`);
        } else {
            showSucces(input);
        }
    }

//get FieldName
    function getFieldName(input) {
        return input.id.charAt(0).toUpperCase() + input.id.slice(1).replace('_sign', '');
    }

    $('#form_sign_up').on('submit', function (e) {
        e.preventDefault();

        checkRequired([email_sign, password_sign]);
        checkLength(password_sign, 6, 50);
        checkEmail(email_sign);
        let email_input = $('#email_sign');
        let password_input = $('#password_sign');
        let domain_url = $('#domain_url');
        let user_ip = $('#user_ip');

        if (
            email_input.parent().hasClass('success') &&
            password_input.parent().hasClass('success')) {
            $.ajax({
                url: 'admin-ajax.php',
                method: 'POST',
                data: {
                    'action': 'leadrebel_login',
                    'email': email_input.val(),
                    'password': password_input.val(),
                    'domain_url': domain_url.val(),
                    'user_ip': user_ip.val(),
                },
                success: function (response) {
                    if (response['data']['lead_rebel_code'] === true) {
                        // $('.incorrect_data').hide();
                        // $('#leadrebel_code').text(response['data']['lead_rebel_code_value']);
                        // $('.script_code').show();
                        // $('#form_register').hide();
                        // $('#form_sign_up').hide();
                        window.location.reload();
                    }
                    if (response['data']['not_authorized'] === true) {
                        email_input.parent().removeClass('success');
                        password_input.parent().removeClass('success');
                        $('.incorrect_data').show();
                    }
                },
            });
        }
    })

    $('#form_register').on('submit', function (e) {
        e.preventDefault();

        checkRequired([first_name, email, password, last_name]);
        checkLength(first_name, 3, 50);
        checkLength(last_name, 3, 50);
        checkLength(password, 6, 50);
        checkEmail(email);
        let first_name_input = $('#firstname');
        let last_name_input = $('#lastname');
        let email_input = $('#email');
        let password_input = $('#password');
        let domain_url = $('#domain_url');
        let user_ip = $('#user_ip');

        if (
            first_name_input.parent().hasClass('success') &&
            last_name_input.parent().hasClass('success') &&
            email_input.parent().hasClass('success') &&
            password_input.parent().hasClass('success')) {
            $.ajax({
                url: 'admin-ajax.php',
                method: 'POST',
                data: {
                    'action': 'leadrebel_signup',
                    'first_name': first_name_input.val(),
                    'last_name': last_name_input.val(),
                    'email': email_input.val(),
                    'password': password_input.val(),
                    'domain_url': domain_url.val(),
                    'user_ip': user_ip.val(),
                },
                success: function (response) {
                    if (response['data']['lead_rebel_code'] === true) {
                        // $('.email_taken').hide();
                        // $('#leadrebel_code').text(response['data']['lead_rebel_code_value']);
                        // $('.script_code').show();
                        // $('#form_register').hide();
                        // $('#form_sign_up').hide();
                        window.location.reload();
                    }
                    if (response['data']['email_taken'] === true) {
                        email_input.parent().removeClass('success');
                        password_input.parent().removeClass('success');
                        $('.email_taken').show();
                    }
                },
            });
        }
    })

    $('#sign-out-btn').on('click', function (e) {
        e.preventDefault();
        console.log("click")

        $.ajax({
            url: 'admin-ajax.php',
            method: 'POST',
            data: {
                'action': 'leadrebel_signout',
            },
            success: function (response) {
                window.location.reload();
            },
        });
    })
})


