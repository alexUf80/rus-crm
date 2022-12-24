$(function () {
    init();

    $('.add_person').on('click', function () {
        let html = '<div class="row">\n' +
            '<div class="col-md-4">\n' +
            '<div class="form-group mb-0">\n' +
            '<label class="control-label">ФИО контакного лица</label>\n' +
            '<input type="text" class="form-control" name="contact_person_name[]" value="" placeholder="" />\n' +
            '</div>\n' +
            '</div>\n' +
            '<div class="col-md-4">\n' +
            '<div class="form-group">\n' +
            '<label class="control-label">Кем приходится</label>\n' +
            '<select class="form-control custom-select " name="contact_person_relation[]">\n' +
            '<option value="" selected="">Выберите значение</option>\n' +
            '<option value="мать/отец">мать/отец</option>\n' +
            '<option value="муж/жена">муж/жена</option>\n' +
            '<option value="сын/дочь">сын/дочь</option>\n' +
            '<option value="коллега">коллега</option>\n' +
            '<option value="друг/сосед">друг/сосед</option>\n' +
            '<option value="иной родственник">иной родственник</option>\n' +
            '</select>\n' +
            '</div>\n' +
            '</div>\n' +
            '<div class="col-md-4">\n' +
            '<div class="form-group">\n' +
            '<label class="control-label">Тел. контакного лица</label>\n' +
            '<input type="text" class="form-control phone_num" name="contact_person_phone[]" value="" placeholder="7(999)999-99-99"/>\n' +
            '</div>\n' +
            '</div>\n' +
            '</div>';

        $('#contactperson_edit_block').append(html);

        init();
    });
    $('#equal_address').on('click', function () {

        let html = '<input type="text" class="form-control search_regaddress" name="faktaddress" placeholder=""/>';

        if ($(this).is(':checked'))
            html = '<small class="badge badge-success">Совпадает с адресом регистрации</small>';

        $('.faktaddress').html(html);
        init();
    });
    $('.search_user').select2({
        minimumInputLength: 3,
        language: {
            noResults: function () {
                return "Нет результатов";
            }
        },
        ajax: {
            method: 'POST',
            dataType: 'json',
            data: function (params) {
                let query = {
                    action: 'search_user',
                    fio: params.term
                };

                return query;
            },
            processResults: function (data) {
                return {
                    results: data.items
                };
            },
        }
    });
    $('.search_user').on('change', function () {
        let user_id = $(this).val();

        $.ajax({
            method: 'post',
            dataType: 'json',
            data:{
                action: 'get_user',
                user_id: user_id
            },
            success: function (user) {
                let gender = user['gender'];

                $('input[name="user_id"]').val(user['id']);
                $('input[name="phone"]').val(user['phone_mobile']);
                $('input[name="email"]').val(user['email']);
                $('input[name="lastname"]').val(user['lastname']);
                $('input[name="firstname"]').val(user['firstname']);
                $('input[name="patronymic"]').val(user['patronymic']);
                $('#gender option[value="'+gender+'"]').prop('selected', true);
                $('input[name="birth_place"]').val(user['birth_place']);
                $('input[name="birth"]').val(user['birth']);
                $('input[name="passport_serial"]').val(user['passport_serial']);
                $('input[name="passport_date"]').val(user['passport_date']);
                $('input[name="subdivision_code"]').val(user['subdivision_code']);
                $('textarea[name="passport_issued"]').val(user['passport_issued']);
            }
        })
    });
    $('.send_sms').on('click', function () {

        let phone = $('input[name="phone"]').val();

        $.ajax({
            method: 'POST',
            dataType: 'JSON',
            data:{
                action: 'send_sms',
                phone: phone,
            },
            success: function (resp) {
                $('.sent_code').text(resp['code']);
                $('.sent_code').fadeIn();
            }
        });

        $('#sms_confirm_modal').modal();
    });
    $('.confirm_code').on('click', function () {
        let code = $('.sms_code').val();
        let phone = $('input[name="phone"]').val();

        $.ajax({
            method: 'POST',
            dataType: 'JSON',
            data:{
                action: 'confirm_sms',
                phone: phone,
                code: code
            },
            success: function (resp) {
                if(resp['error'])
                {
                    Swal.fire({
                        title: resp['error'],
                        confirmButtonText: 'ОК'
                    });
                }
                if(resp['success'])
                    create_order();
            }
        });
    })
});

function init() {
    let token_dadata = "25c845f063f9f3161487619f630663b2d1e4dcd7";

    moment.locale('ru');

    $('.daterange').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: 'DD.MM.YYYY'
        },
    });
    $('.phone_num').click(function () {
    }).mask('+7(999)999-99-99');
    $('input[name="passport_serial"]').click(function () {
    }).mask('9999-999999');
    $('.search_regaddress').suggestions({
        token: token_dadata,
        type: "ADDRESS",
        minChars: 3,
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function (suggestion) {
            $(this).val(suggestion.value);
            $(this).next().val(JSON.stringify(suggestion));
        }
    });
    $('.search_workplace').suggestions({
        token: token_dadata,
        type: "party",
        minChars: 3,
        onSelect: function (suggestion) {
            $(this).val(suggestion.value);
            $('input[name="workaddress"]').val(suggestion.data.address.value);
            $('input[name="chief_name"]').val(suggestion.data.management.name);
            $('input[name="chief_position"]').val(suggestion.data.management.post);
        }
    });
    $('input[name="subdivision_code"]').suggestions({
        token: token_dadata,
        type: "fms_unit",
        minChars: 3,
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function (suggestion) {
            $(this).empty();
            $(this).val(suggestion.data.code);
            $('input[name="passport_issued"]').empty();
            $('textarea[name="passport_issued"]').val(suggestion.value);
        }
    });
}
function create_order() {

    let form = $('#create_order_form').serialize();

    $.ajax({
        method: 'POST',
        dataType: 'JSON',
        data: form,
        success: function (resp) {
            if(resp['success'])
            {
                location.replace('order/'+resp['success']);
            }
            if(resp['error'])
            {
                Swal.fire({
                    title: resp['error'],
                    confirmButtonText: 'ОК'
                });
            }
        }
    })
}