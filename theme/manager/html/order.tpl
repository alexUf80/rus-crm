{$meta_title="Заявка №`$order->order_id`" scope=parent}

{capture name='page_scripts'}
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js?v=1.20"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/movements.app.js"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/penalty.app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {

            init();

            $(document).on('click', '.js-open-sms-modal', function (e) {
                e.preventDefault();

                var _user_id = $(this).data('user');
                var _order_id = $(this).data('order');
                var _yuk = $(this).hasClass('js-yuk') ? 1 : 0;

                $('#modal_send_sms [name=user_id]').val(_user_id);
                $('#modal_send_sms [name=order_id]').val(_order_id);
                $('#modal_send_sms [name=yuk]').val(_yuk);
                $('#modal_send_sms').modal();
            });

            $('#local_zone').on('change', function (e) {
                e.preventDefault();

                let value = $(this).val();
                let user_id = $(this).attr('data-user');

                if (value != 'nothing') {
                    $.ajax({
                        method: 'POST',
                        data: {
                            value: value,
                            user_id: user_id
                        }
                    })
                }
            });

            $('.send_sms').on('click', function () {

                let order = $(this).attr('data-order');

                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'send_sms',
                        order: order
                    },
                    success: function (resp) {
                        $('.sent_code').text(resp['code']);
                        $('.sent_code').fadeIn();
                    }
                });
            });

            $(document).on('submit', '.js-sms-form', function (e) {
                e.preventDefault();

                var $form = $(this);

                var _user_id = $form.find('[name=user_id]').val();

                var manager_id = $(this).attr('data-manager-id');

                if ($form.hasClass('loading'))
                    return false;


                $.ajax({
                    type: 'POST',
                    data: $form.serialize(),
                    beforeSend: function () {
                        $form.addClass('loading')
                    },
                    success: function (resp) {
                        $form.removeClass('loading');
                        $('#modal_send_sms').modal('hide');

                        if (!!resp.error) {
                            Swal.fire({
                                timer: 5000,
                                title: 'Ошибка!',
                                text: resp.error,
                                type: 'error',
                            });
                        }
                        else {
                            Swal.fire({
                                timer: 5000,
                                title: '',
                                text: 'Сообщение отправлено',
                                type: 'success',
                            });

                            $.ajax({
                                url: 'ajax/communications.php',
                                data: {
                                    action: 'add',
                                    user_id: _user_id,
                                    type: 'sms',
                                    content: $('[name="template_id"] option:selected').text(),
                                    manager_id: manager_id
                                }
                            });

                        }
                    },
                })

            });

            $('#casual_sms').on('click', function (e) {
                e.preventDefault();

                $('.casual-sms-form').toggle('slow');
            });

            $('.add_receipt').on('click', function (e) {
                e.preventDefault();

                let type = $(this).attr('data-type');
                let issuance_flag = $(this).attr('data-issuance');
                let order_id = $(this).attr('data-order');
                let operation_id = $(this).attr('data-operation');

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'add_receipt',
                        type: type,
                        issuance_flag: issuance_flag,
                        order_id: order_id,
                        operation_id: operation_id
                    },
                    success: function () {
                        Swal.fire({
                            title: 'Успешно!',
                            text: 'Чек успешно пробит',
                        });
                    }
                })

            })

            $('.accept_contract').on('click', function () {

                let order = $(this).attr('data-order');
                let code = $('.sms_code').val();

                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'confirm_contract',
                        order: order,
                        code: code
                    },
                    success: function (resp) {
                        if (resp['error']) {
                            Swal.fire({
                                title: resp['error'],
                                confirmButtonText: 'ОК'
                            });
                        }
                        if (resp['success']) {
                            location.reload();
                        }
                    }
                });
            });
            $('.show_edit_buttons').on('click', function () {

                $('.contact_edit_buttons').toggle();
            });
            $('.add_contact').on('click', function (e) {
                e.preventDefault();
                $('#contacts_form')[0].reset();

                $('#contacts_modal').modal();
                $('.contacts_modal_title').text('Добавить контакт');
                $('#contacts_actions').addClass('save_contact');
                $('#contacts_form').find('input[name="action"]').attr('value', 'add_contact');

                $('.close_contacts_modal').on('click', function () {
                    $('#contacts_modal').modal('hide');
                });
            });
            $('.edit_contact').on('click', function (e) {
                e.preventDefault();
                $('#contacts_form')[0].reset();

                let id = $(this).attr('data-id');

                $.ajax({
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'get_contact',
                        id: id
                    },
                    success: function (contact) {

                        let relation = contact['relation'];

                        $('#contacts_form').find('input[name="fio"]').val(contact['name']);
                        $('#contacts_form').find('input[name="phone"]').val(contact['phone']);
                        $('#contacts_form').find('textarea[name="comment"]').val(contact['comment']);

                        if (relation === null) {
                            $('#contacts_form').find('select option[value="none"]').prop('selected', true);
                        } else {
                            $('#contacts_form').find('select option[value="' + relation + '"]').prop('selected', true);
                        }
                    }
                });

                $('#contacts_modal').modal();
                $('.contacts_modal_title').text('Редактировать контакт');
                $('#contacts_actions').addClass('confirm_edit_contact');
                $('#contacts_form').find('input[name="action"]').attr('value', 'edit_contact');

                $('.close_contacts_modal').on('click', function () {
                    $('#contacts_modal').modal('hide');
                });

                $(document).on('click', '.confirm_edit_contact', function (e) {
                    let form = $('#contacts_form').serialize() + '&id=' + id;

                    $.ajax({
                        method: 'POST',
                        data: form,
                        success: function () {
                            location.reload();
                        }
                    })
                })
            });
            $(document).on('click', '.save_contact', function () {

                let form = $('#contacts_form').serialize();

                $.ajax({
                    method: 'POST',
                    data: form,
                    success: function (resp) {
                        location.reload();
                    }
                })
            });
            $('.delete_contact').on('click', function () {
                let id = $(this).attr('data-id');

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'delete_contact',
                        id: id
                    },
                    success: function () {
                        location.reload();
                    }
                })
            });

            $('.restruct').on('click', function () {
                $('#restruct_modal').modal();

                $('.addPeriod').on('click', function () {

                    let form = $('<div class="form-group" style="display: flex">' +
                        '<input class="form-control daterange" name="date[][date]">' +
                        '<input placeholder="Платеж" style="margin-left: 5px" class="form-control" name="payment[][payment]">' +
                        '<input placeholder="ОД" style="margin-left: 5px" class="form-control" name="payOd[][payOd]">' +
                        '<input placeholder="Процент" style="margin-left: 5px" class="form-control" name="payPrc[][payPrc]">' +
                        '<input placeholder="Пени" style="margin-left: 5px" class="form-control" name="payPeni[][payPeni]">' +
                        '<div style="margin-left: 5px" class="btn btn-danger deletePeriod"> - </div></div>');

                    $('#payments_schedules').append(form);

                    form.find('.daterange').daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        locale: {
                            format: 'DD.MM.YYYY'
                        },
                    });

                    //init();
                });

                $(document).on('click', '.deletePeriod', function () {
                    $(this).closest('.form-group').remove();
                });

                $('.saveRestruct').on('click', function () {
                    let form = $('#restruct_form').serialize();

                    $.ajax({
                        method: 'POST',
                        data: form,
                        success: function () {
                            location.reload();
                        }
                    });
                });
            });

            $('.confirm_restruct').on('click', function () {
                let order = $(this).attr('data-order');

                $('#sms_confirm_modal').modal();

                send_sms(order);

                $('.send_asp_code').on('click', function () {
                    send_sms(order);
                });

                $('.confirm_asp').on('click', function () {
                    let phone = $(this).attr('data-phone');
                    let user = $(this).attr('data-user');
                    let contract = $(this).attr('data-contract');
                    let code = $('.code_asp').val();

                    $.ajax({
                        method: 'POST',
                        dataType: 'JSON',
                        data: {
                            action: 'confirm_asp',
                            user: user,
                            phone: phone,
                            code: code,
                            contract: contract,
                        },
                        success: function (response) {
                            if (response['error'] == 1) {
                                Swal.fire({
                                    title: 'Неверный код',
                                    confirmButtonText: 'ОК'
                                });
                            } else {
                                location.reload();
                            }
                        }
                    });
                });
            });

            $('.editLoanProfit').on('click', function () {
                $('#editLoanProfitModal').modal();
            });

            $('.saveEditLoanProfit').on('click', function () {
                let form = $(this).closest('form').serialize();

                $.ajax({
                    method: 'POST',
                    data: form,
                    success: function () {
                        location.reload();
                    }
                });
            });
        })
    </script>
    <script>
        function init() {
            moment.locale('ru');

            $('.daterange').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
            });
        }

        function send_sms(order) {

            $.ajax({
                method: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'send_sms',
                    order: order
                },
                success: function (resp) {
                    if (resp['error']) {
                        Swal.fire({
                            title: resp['error'],
                            confirmButtonText: 'Да'
                        })
                    } else {
                        $('.phone_send_code').text(resp['success']);
                        $('.phone_send_code').fadeIn();
                    }
                }
            });
        }
    </script>
{/capture}

{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link href="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet"/>
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        .md-comment {
            max-width: 1000px;
        }
    </style>
    <style>
        .js-open-popup-image .label {
            position: absolute;
            bottom: 2px;
            left: 2px;
        }

        .js-fancybox-approve.btn-success {
            background: #55ce63;
            border: 1px solid #55ce63;
        }

        .js-fancybox-approve.btn-outline-success {
            color: #55ce63;
            background-color: transparent;
            border-color: #55ce63
        }

        .js-fancybox-reject.btn-danger {
            background: #f62d51;
            border: 1px solid #f62d51
        }

        .js-fancybox-reject.btn-outline-danger {
            color: #f62d51;
            background-color: transparent;
            border-color: #f62d51;
        }

        .fancybox-container {
            width: 40% !important
        }

        .fancybox-inner {
            width: 100% !important
        }
    </style>
{/capture}

{function name='penalty_button'}

    {if in_array('add_penalty', $manager->permissions)}
        {if !$penalties[$penalty_block]}
            <button type="button" class="pb-0 pt-0 mr-2 btn btn-sm btn-danger waves-effect js-add-penalty "
                    data-block="{$penalty_block}">
                <i class="fas fa-ban"></i>
                <span>Штраф</span>
            </button>
        {elseif $penalties[$penalty_block] && in_array($penalties[$penalty_block]->status, [1,2])}
            <button type="button" class="pb-0 pt-0 mr-2 btn btn-sm btn-primary waves-effect js-reject-penalty "
                    data-penalty="{$penalties[$penalty_block]->id}">
                <i class="fas fa-ban"></i>
                <span>Отменить</span>
            </button>
            <button type="button" class="pb-0 pt-0 mr-2 btn btn-sm btn-warning waves-effect js-strike-penalty "
                    data-penalty="{$penalties[$penalty_block]->id}">
                <i class="fas fa-ban"></i>
                <span>Страйк</span>
            </button>
        {/if}
        {if in_array($penalties[$penalty_block]->status, [4])}
            <span class="label label-warning">Страйк ({$penalties[$penalty_block]->cost} руб)</span>
        {/if}
    {elseif $penalties[$penalty_block]->manager_id == $manager->id}
        {if in_array($penalties[$penalty_block]->status, [1])}
            <button class="pb-0 pt-0 mr-2 btn btn-sm btn-primary js-correct-penalty"
                    data-penalty="{$penalties[$penalty_block]->id}" type="button">Исправить
            </button>
        {/if}
        {if in_array($penalties[$penalty_block]->status, [4])}
            <span class="label label-warning">Страйк ({$penalties[$penalty_block]->cost} руб)</span>
        {/if}
    {/if}

{/function}

<div class="page-wrapper js-event-add-load" data-event="1" data-manager="{$manager->id}" data-order="{$order->order_id}"
     data-user="{$order->user_id}">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">

        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Заявка №{$order->order_id}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="orders">Заявки</a></li>
                    <li class="breadcrumb-item active">Заявка №{$order->order_id}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
        </div>

        <div class="row" id="order_wrapper">
            <div class="col-lg-12">
                <div class="card card-outline-info">

                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">
                                <div class="col-4 col-md-3 col-lg-2">
                                    <h4 class="form-control-static">
                                        {if $order->client_status}
                                            {if $order->client_status == 'pk'}
                                                <span class="label label-success"
                                                      title="Клиент уже имеет погашенные займы">ПК</span>
                                            {elseif $order->client_status == 'crm'}
                                                <span class="label label-primary"
                                                      title="Клиент уже имеет погашенные займы в CRM">ПК CRM</span>
                                            {elseif $order->client_status == 'rep'}
                                                <span class="label label-warning"
                                                      title="Клиент уже подавал ранее заявки">Повтор</span>
                                            {elseif $order->client_status == 'nk'}
                                                <span class="label label-info" title="Новый клиент">Новая</span>
                                            {/if}
                                        {else}
                                            {if $order->have_crm_closed}
                                                <span class="label label-primary"
                                                      title="Клиент уже имеет погашенные займы в CRM">ПК CRM</span>
                                            {elseif $order->first_loan}
                                                <span class="label label-info" title="Новый клиент">Новая</span>
                                            {else}
                                                <span class="label label-warning"
                                                      title="Клиент уже подавал ранее заявки">Повтор</span>
                                            {/if}
                                        {/if}
                                    </h4>
                                </div>
                                <div class="col-8 col-md-3 col-lg-4">
                                    <h5 class="form-control-static float-left  text-center pr-2 pl-2">
                                        дата заявки: <br/>{$order->date|date} {$order->date|time}
                                    </h5>
                                    {if $contract->close_date}
                                        <h5 class="form-control-static  text-center float-left pl-2  pr-2">
                                            закрыт: <br/>{$contract->close_date|date}
                                        </h5>
                                    {elseif $contract->return_date}
                                        <h5 class="form-control-static  text-center float-left pl-2 pr-2">
                                            дата оплаты: <br/>{$contract->return_date|date}
                                        </h5>
                                    {/if}
                                    {if $order->penalty_date}
                                        <h5 class="form-control-static float-left text-center pr-2 pl-2">
                                            дата решения: <br/>{$order->penalty_date|date} {$order->penalty_date|time}
                                        </h5>
                                    {/if}
                                </div>
                                <div class="col-12 col-md-6 col-lg-1">
                                    <h5 class="form-control-static">
                                        Источник:
                                        {if $order->utm_source}
                                            {$order->utm_source|escape}
                                        {else}
                                            не определен
                                        {/if}
                                    </h5>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <a href="{$looker_link}" target="_blank" class="btn btn-info float-right"><i
                                                class=" fas fa-address-book"></i> Смотреть ЛК</a>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3 ">
                                    <h5 class="js-order-manager text-right">
                                        {if in_array($manager->role, ['developer', 'admin'])}
                                            <select class="js-order-manager form-control"
                                                    data-order="{$order->order_id}" name="manager_id">
                                                <option value="0" {if !$order->manager_id}selected="selected"{/if}>Не
                                                    принята
                                                </option>
                                                {foreach $managers as $m}
                                                    <option value="{$m->id}"
                                                            {if $m->id == $order->manager_id}selected="selected"{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        {else}
                                            {if $order->manager_id}
                                                {$managers[$order->manager_id]->name|escape}
                                            {/if}
                                        {/if}
                                    </h5>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col-12 col-md-4 col-lg-3">
                                    <form action="{url}" class="js-order-item-form " id="fio_form">

                                        <input type="hidden" name="action" value="fio"/>
                                        <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                        <input type="hidden" name="user_id" id="user_id" value="{$order->user_id}"/>

                                        <div class="border p-2 view-block">
                                            <h5>
                                                <a href="client/{$order->user_id}" title="Перейти в карточку клиента">
                                                    {$order->lastname|escape}
                                                    {$order->firstname|escape}
                                                    {$order->patronymic|escape}
                                                </a>
                                            </h5>
                                            <h3>
                                                <span>{$order->phone_mobile}</span>
                                                <button class="js-mango-call mango-call js-event-add-click"
                                                        data-phone="{$order->phone_mobile}" title="Выполнить звонок"
                                                        data-event="60" data-manager="{$manager->id}"
                                                        data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </button>
                                                <button class="js-open-sms-modal mango-call {if $contract->sold}js-yuk{/if}"
                                                        data-user="{$order->user_id}" data-order="{$order->order_id}">
                                                    <i class=" far fa-share-square"></i>
                                                </button>
                                            </h3>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1">
                                                <label class="form-check-label" for="flexCheckDefault">
                                                    Находится в ч/с
                                                </label>
                                            </div>
                                            {if !empty($user_risk_op)}
                                                {foreach $user_risk_op as $operation => $value}
                                                    {foreach $risk_op as $risk => $val}
                                                        {if $operation == $risk && $value == 1}
                                                            <span class="label label-danger">{$val}</span>
                                                        {/if}
                                                    {/foreach}
                                                {/foreach}
                                            {/if}
                                            {if in_array($manager->role, ['developer', 'admin', 'user'])}
                                                <a href="javascript:void(0);"
                                                   class="text-info js-edit-form edit-amount js-event-add-click"
                                                   data-event="30" data-manager="{$manager->id}"
                                                   data-order="{$order->order_id}" data-user="{$order->user_id}"><i
                                                            class=" fas fa-edit"></i></a>
                                            {/if}
                                        </div>

                                        <div>
                                            <br>
                                            <h5 class="form-control-static float-left  text-center pr-2 pl-2">
                                                &#8986; {$client_time}
                                            </h5>

                                        </div>
                                        {if $need_to_select_local_zone}
                                            <div class="form-control-static float-left  text-center pr-2 pl-2">
                                                <select class="form-control" data-user="{$order->user_id}"
                                                        id="local_zone">
                                                    <option value="nothing" selected>Часовой пояс</option>
                                                    <option value="-1">Калининградская область</option>
                                                    <option value="msk">(МСК, московское время) — большая часть
                                                        европейской
                                                        территории России и вся Российская железная дорога
                                                    </option>
                                                    <option value="1">Удмуртская Республика, Астраханская область,
                                                        Самарская
                                                        область, Саратовская область и Ульяновская область
                                                    </option>
                                                    <option value="2">Республика Башкортостан, Пермский край, Курганская
                                                        область, Оренбургская область, Свердловская область
                                                    </option>
                                                    <option value="2">Тюменская область, Челябинская область,
                                                        Ханты-Мансийский автономный округ — Югра и Ямало-Ненецкий
                                                        автономный
                                                        округ
                                                    </option>
                                                    <option value="3">Омская область</option>
                                                    <option value="4">Республика Алтай, Республика Тыва, Республика
                                                        Хакасия,
                                                        Алтайский край, Красноярский край, Кемеровская область,
                                                        Новосибирская область и Томская область
                                                    </option>
                                                    <option value="5">Республика Бурятия и Иркутская область</option>
                                                    <option value="6">Республика Саха (Якутия) (западные и центральные
                                                        районы), Забайкальский край и Амурская область
                                                    </option>
                                                    <option value="7">Республика Саха (Якутия) (ряд районов), Приморский
                                                        край, Хабаровский край и Еврейская автономная область
                                                    </option>
                                                    <option value="8">Республика Саха (Якутия) (северо-восточные
                                                        районы),
                                                        Магаданская область и Сахалинская область
                                                    </option>
                                                    <option value="9">Камчатский край и Чукотский автономный округ
                                                    </option>
                                                </select>
                                            </div>
                                        {/if}

                                        <div class="edit-block hide">
                                            <div class="form-group mb-1">
                                                <input type="text" name="lastname" value="{$order->lastname}"
                                                       class="form-control" placeholder="Фамилия"/>
                                            </div>
                                            <div class="form-group mb-1">
                                                <input type="text" name="firstname" value="{$order->firstname}"
                                                       class="form-control" placeholder="Имя"/>
                                            </div>
                                            <div class="form-group mb-1">
                                                <input type="text" name="patronymic" value="{$order->patronymic}"
                                                       class="form-control" placeholder="Отчество"/>
                                            </div>
                                            <div class="form-group mb-1">
                                                <input type="text" name="phone_mobile" value="{$order->phone_mobile}"
                                                       class="form-control" placeholder="Телефон"/>
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success js-event-add-click"
                                                        data-event="40" data-manager="{$manager->id}"
                                                        data-order="{$order->order_id}" data-user="{$order->user_id}"><i
                                                            class="fa fa-check"></i> Сохранить
                                                </button>
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена
                                                </button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="col-12 col-md-8 col-lg-6">
                                    <form action="{url}" class="mb-3 p-2 border js-order-item-form js-check-amount"
                                          id="amount_form">

                                        <input type="hidden" name="action" value="amount"/>
                                        <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                        <input type="hidden" name="user_id" value="{$order->user_id}"/>
                                        {if $amount_error}
                                            <div class="text-danger pt-3">
                                                <ul>
                                                    {foreach $amount_error as $er}
                                                        <li>{$er}</li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="row view-block ">
                                            <div class="col-6 text-center">
                                                <h5>Сумма</h5>
                                                <h3 class="text-primary">{$order->amount} руб</h3>
                                            </div>
                                            <div class="col-6 text-center">
                                                <h5>Срок</h5>
                                                <h3 class="text-primary">{$order->period} {$order->period|plural:"день":"дней":"дня"}</h3>
                                            </div>
                                            {if $order->status <= 2 || in_array($manager->role, ['admin','developer'])}
                                                <a href="javascript:void(0);"
                                                   class="text-info js-edit-form edit-amount js-event-add-click"
                                                   data-event="31" data-manager="{$manager->id}"
                                                   data-order="{$order->order_id}" data-user="{$order->user_id}"><i
                                                            class=" fas fa-edit"></i></a>
                                                </h3>
                                            {/if}
                                            {if isset($promocode)}
                                                <br>
                                                <div class="col-6 text-center">
                                                    <h5>Промокод</h5>
                                                    <h3 class="text-primary">{$promocode->code}</h3>
                                                </div>
                                                <div class="col-6 text-center">
                                                    <h5>Скидка</h5>
                                                    <h3 class="text-primary">{$promocode->discount}%</h3>
                                                </div>
                                            {/if}
                                        </div>

                                        <div class="row edit-block hide">
                                            <div class="col-6 col-md-3 text-center">
                                                <h5>Сумма</h5>
                                                <input type="text" class="form-control" name="amount"
                                                       value="{$order->amount}"/>
                                            </div>
                                            <div class="col-6 col-md-3 text-center">
                                                <h5>Период</h5>
                                                <input type="text" class="form-control" name="period"
                                                       value="{$order->period}"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-actions">
                                                    <h5>&nbsp;</h5>
                                                    <button type="submit" class="btn btn-success js-event-add-click"
                                                            data-event="41" data-manager="{$manager->id}"
                                                            data-order="{$order->order_id}"
                                                            data-user="{$order->user_id}"><i class="fa fa-check"></i>
                                                        Сохранить
                                                    </button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">
                                                        Отмена
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    {if $order->status == 0}
                                        <div class="pt-3 js-accept-order-block">
                                            <button class="btn btn-info btn-block js-accept-order js-event-add-click"
                                                    data-event="10" data-manager="{$manager->id}"
                                                    data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                <i class="fas fa-hospital-symbol"></i>
                                                <span>Принять</span>
                                            </button>
                                            <button class="btn btn-danger btn-block js-reject-order js-event-add-click"
                                                    data-event="13" data-user="{$order->user_id}"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Отказать</span>
                                            </button>
                                        </div>
                                    {/if}
                                    {if $order->status == 1 && in_array('approve_contract', $manager->permissions)}
                                        <div class="js-approve-reject-block">
                                            <button class="btn btn-success btn-block js-approve-order js-event-add-click"
                                                    data-event="12" data-user="{$order->user_id}"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Подтвердить одобрение</span>
                                            </button>
                                            <button class="btn btn-danger btn-block js-reject-order js-event-add-click"
                                                    data-event="13" data-user="{$order->user_id}"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Отказать</span>
                                            </button>
                                        </div>
                                    {elseif $order->status == 1 && !in_array('approve_contract', $manager->permissions)}
                                        <div class="card card-info mb-1">
                                            <div class="box text-center">
                                                <h3 class="text-white mb-0">На рассмотрении старшего менеджера</h3>
                                            </div>
                                        </div>
                                    {/if}
                                    <div class="js-order-status">
                                        {if $order->status == 2}
                                            <div class="card card-success mb-1">
                                                <div class="box text-center">
                                                    <h3 class="text-white mb-0">Одобрена</h3>
                                                </div>
                                            </div>
                                            <br>
                                            <button class="btn btn-danger btn-block js-reject-order js-event-add-click"
                                                    data-event="13" data-user="{$order->user_id}"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Отказать</span>
                                            </button>
                                            {if in_array('approve_contract', $manager->permissions)}
                                                <form class="pt-1">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control sms_code"
                                                               placeholder="SMS код"/>
                                                        <div class="sent_code badge badge-danger"
                                                             style="position: absolute; margin-left: 350px; margin-top: 5px; right: 120px;display: none">
                                                        </div>
                                                        <div class="input-group-append">
                                                            <div class="btn btn-info accept_contract"
                                                                 data-order="{$order->order_id}">Подтвердить
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <small class="btn btn-outline-primary btn-xs send_sms"
                                                           data-order="{$order->order_id}">
                                                        <span>Отправить смс код</span>
                                                    </small>
                                                </form>
                                            {/if}
                                        {/if}
                                        {if $order->status == 3}
                                            <div class="card card-danger">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Отказ</h3>
                                                    <small title="Причина отказа">
                                                        <i>{$reject_reasons[$order->reason_id]->admin_name}</i></small>
                                                    {if $order->antirazgon_date}
                                                        <br/>
                                                        <strong class="text-white">
                                                            <small>Мараторий до {$order->antirazgon_date|date}</small>
                                                        </strong>
                                                    {/if}
                                                </div>
                                            </div>
                                        {/if}
                                        {if $order->status == 4}
                                            <div class="card card-primary">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Подписан</h3>
                                                    <h6>Договор {$contract->number}</h6>
                                                </div>
                                            </div>
                                        {/if}
                                        {if $order->status == 5}
                                            {if $contract->status == 4}
                                                <div class="card card-danger mb-1">
                                                    <div class="box text-center">
                                                        <h3 class="text-white">Просрочен</h3>
                                                        <h6>Договор {$contract->number}</h6>
                                                        {if $contract->outer_id}<h6>{$contract->outer_id}</h6>{/if}
                                                        <h5>
                                                            Просрочен: {$contract->delay} {$contract->delay|plural:'день':'дней':'дня'}</h5>
                                                        <h6 class="text-center text-white">
                                                            Погашение: {$contract->loan_body_summ+$contract->loan_percents_summ+$contract->loan_charge_summ+$contract->loan_peni_summ}
                                                            руб
                                                        </h6>
                                                        <h6 class="text-center text-white">
                                                            Продление:
                                                            {if $contract->stop_profit}
                                                                достигнут порог
                                                            {else}
                                                                {if $contract->prolongation > 0 && !$contract->sold}
                                                                    {$settings->prolongation_amount+$contract->loan_percents_summ+$contract->loan_charge_summ} руб
                                                                {else}
                                                                    {$contract->loan_percents_summ+$contract->loan_charge_summ} руб
                                                                {/if}
                                                            {/if}
                                                        </h6>
                                                    </div>
                                                </div>
                                            {elseif $contract->status == 7}
                                                <div class="card card-primary mb-1">
                                                    <div class="box text-center">
                                                        <h3 class="text-white">Продан</h3>
                                                        <h6>Договор {$contract->number}</h6>
                                                        {if $contract->outer_id}<h6>{$contract->outer_id}</h6>{/if}
                                                        <h6 class="text-center text-white">
                                                            Погашение: {$contract->loan_body_summ+$contract->loan_percents_summ+$contract->loan_charge_summ+$contract->loan_peni_summ}
                                                            руб
                                                        </h6>
                                                        <h6 class="text-center text-white">
                                                            Продление:
                                                            {if $contract->stop_profit}
                                                                достигнут порог
                                                            {else}
                                                                {if $contract->prolongation > 0 && !$contract->sold}
                                                                    {$settings->prolongation_amount+$contract->loan_percents_summ+$contract->loan_charge_summ} руб
                                                                {else}
                                                                    {$contract->loan_percents_summ+$contract->loan_charge_summ} руб
                                                                {/if}
                                                            {/if}
                                                        </h6>
                                                    </div>
                                                </div>
                                            {else}
                                                <div class="card card-primary mb-1">
                                                    <div class="box text-center">
                                                        <h6>Договор {$contract->number}</h6>
                                                        {if $contract->outer_id}<h6>{$contract->outer_id}</h6>{/if}
                                                        {if $contract->status == 11}
                                                            <h4 class="text-white">Реструктуризирован</h4>
                                                            <h5 class="text-white">Дата следующей
                                                                оплаты: {$contract->next_pay|date}</h5>
                                                        {/if}
                                                        {if !in_array($contract->status, [10,11])}
                                                            <h3 class="text-white">Выдан</h3>
                                                            <h6 class="text-center text-white">
                                                                Погашение: {$contract->loan_body_summ+$contract->loan_percents_summ+$contract->loan_charge_summ+$contract->loan_peni_summ}
                                                                руб
                                                            </h6>
                                                            <h6 class="text-center text-white">
                                                                Продление:
                                                                {if $contract->stop_profit}
                                                                    достигнут порог
                                                                {else}
                                                                    {if $contract->prolongation > 0}
                                                                        {$settings->prolongation_amount+$contract->loan_percents_summ} руб
                                                                    {else}
                                                                        {$contract->loan_percents_summ} руб
                                                                    {/if}
                                                                {/if}
                                                            </h6>
                                                        {/if}
                                                    </div>
                                                </div>
                                            {/if}
                                            {if in_array($contract->status, [2,4])}
                                                <div class="btn btn-block btn-info restruct">
                                                    Реструктуризировать
                                                </div>
                                            {/if}
                                            {if $contract->status == 10}
                                                <div data-order="{$order->order_id}"
                                                     class="btn btn-block btn-success confirm_restruct">
                                                    Отправить смс и подтвердить реструктуризацию
                                                </div>
                                            {/if}
                                            {if in_array($contract->status, [2,4])}
                                                <div data-order="{$order->order_id}"
                                                     class="btn btn-block btn-success editLoanProfit">
                                                    Скорректировать долг/Остановить начисления
                                                </div>
                                            {/if}
                                            {if in_array('close_contract', $manager->permissions)}
                                                <button class="btn btn-danger btn-block js-open-close-form js-event-add-click"
                                                        data-event="15" data-user="{$order->user_id}"
                                                        data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                    Закрыть договор
                                                </button>
                                            {/if}
                                            <br>
                                            <a href="/add_pay?user_id={$order->user_id}&order_id={$order->order_id}">
                                                <button class="btn btn-info btn-block add_pay">
                                                    <span>Провести платеж</span>
                                                </button>
                                            </a>
                                        {/if}
                                        {if $order->status == 6}
                                            <div class="card card-danger mb-1">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Не удалось выдать</h3>
                                                    <h6>Договор {$contract->number}</h6>
                                                    {if $p2p->response_xml}
                                                        <i>
                                                            <small>B2P: {$p2p->response_xml->message}</small>
                                                        </i>
                                                    {else}
                                                        <i>
                                                            <small>Нет ответа от B2P</small>
                                                        </i>
                                                    {/if}
                                                </div>
                                            </div>
                                            {if $have_newest_order}
                                                <div class="text-center">
                                                    <a href="order/{$have_newest_order}"><strong
                                                                class="text-danger text-center">У клиента есть новая
                                                            заявка</strong></a>
                                                </div>
                                            {else}
                                                {if in_array('repay_button', $manager->permissions)}
                                                    <button type="button"
                                                            class="btn btn-primary btn-block js-repay-contract js-event-add-click"
                                                            data-event="16" data-user="{$order->user_id}"
                                                            data-order="{$order->order_id}"
                                                            data-manager="{$manager->id}"
                                                            data-contract="{$contract->id}">Повторить выдачу
                                                    </button>
                                                {/if}
                                            {/if}
                                        {/if}

                                        {if $order->status == 7}
                                            <div class="card card-primary">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Погашен</h3>
                                                    <h6>Договор #{$contract->number}</h6>
                                                </div>
                                            </div>
                                        {/if}
                                        {if $order->status == 8}
                                            <div class="card card-danger">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Отказ клиента</h3>
                                                    <small title="Причина отказа">
                                                        <i>{$reject_reasons[$order->reason_id]->admin_name}</i></small>
                                                </div>
                                            </div>
                                        {/if}
                                        {*
                                        <br>
                                        <select class="js-risk-lvl form-control" data-user_id="{$order->user_id}"
                                                    style="width: 100%">
                                            <option selected value="0">Уровень риска - обычный</option>
                                            <option value="1">Уровень риска - высокий</option>
                                        </select><br>
                                        <br>
                                        <button type="button" class="btn btn-light dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Рисковые операции
                                        </button>
                                        <div class="js-risk-op-check dropdown-menu" id="dropdown_managers">
                                            <ul class="list-unstyled m-2" data-user="{$order->user_id}">
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="complaint"/>
                                                        <label>Жалоба</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="bankrupt"/>
                                                        <label>Банкрот</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="refusal"/>
                                                        <label>Отказ от взаимодействия</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="refusal_thrd"/>
                                                        <label>Отказ от взаимодействия с 3 лицами</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="death"/>
                                                        <label>Смерть</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="anticollectors"/>
                                                        <label>Антиколлекторы</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="mls"/>
                                                        <label>Находится в МЛС</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="bankrupt_init"/>
                                                        <label>Инициировано банкротство</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="fraud"/>
                                                        <label>Мошенничество</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="js-risk-op input-custom"
                                                               value="canicule"/>
                                                        <label>Кредитные каникулы</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        *}
                                    </div>
                                </div>
                            </div>
                        </div>


                        <ul class="mt-2 nav nav-tabs" role="tablist" id="order_tabs">
                            <li class="nav-item">
                                <a class="nav-link active js-event-add-click" data-toggle="tab" href="#info" role="tab"
                                   aria-selected="false" data-event="20" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                                    <span class="hidden-xs-down">Персональная информация</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#comments" role="tab"
                                   aria-selected="false" data-event="21" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-user"></i></span>
                                    <span class="hidden-xs-down">
                                            Комментарии {if $comments|count > 0}<span
                                                class="label label-rounded label-primary">{$comments|count}</span>{/if}
                                        </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#documents" role="tab"
                                   aria-selected="true" data-event="22" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-layers"></i></span>
                                    <span class="hidden-xs-down">Документы</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#logs" role="tab"
                                   aria-selected="true" data-event="23" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-server"></i></span>
                                    <span class="hidden-xs-down">Логирование</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#operations" role="tab"
                                   aria-selected="true" data-event="24" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-list-ol"></i></span>
                                    <span class="hidden-xs-down">Операции</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#history" role="tab"
                                   aria-selected="true" data-event="25" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-save-alt"></i></span>
                                    <span class="hidden-xs-down">Кредитная история</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#connexions"
                                   role="tab" aria-selected="true" data-event="25" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-email"></i></span>
                                    <span class="hidden-xs-down">Связанные лица</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link js-event-add-click" data-toggle="tab" href="#communications"
                                   role="tab" aria-selected="true" data-event="25" data-user="{$order->user_id}"
                                   data-order="{$order->order_id}" data-manager="{$manager->id}">
                                    <span class="hidden-sm-up"><i class="ti-mobile"></i></span>
                                    <span class="hidden-xs-down">Коммуникации</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content tabcontent-border" id="order_tabs_content">
                            <div class="tab-pane active" id="info" role="tabpanel">
                                <div class="form-body p-2 pt-3">

                                    <div class="row">
                                        <div class="col-md-8 ">

                                            <!-- Контакты -->
                                            <form action="{url}"
                                                  class="mb-3 border js-order-item-form {if $penalties['personal'] && $penalties['personal']->status!=3}card-outline-danger{/if}"
                                                  id="personal_data_form">

                                                <input type="hidden" name="action" value="contactdata"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header card-success">
                                                    <span class="text-white ">Контакты</span>
                                                    <span class="float-right">
                                                            {penalty_button penalty_block='personal'}
                                                        <a href="javascript:void(0);"
                                                           class=" text-white js-edit-form js-event-add-click"
                                                           data-event="32" data-manager="{$manager->id}"
                                                           data-order="{$order->order_id}"
                                                           data-user="{$order->user_id}"><i
                                                                    class=" fas fa-edit"></i></a></h3>
                                                        </span>
                                                </h5>

                                                <div class="row pt-2 view-block {if $contactdata_error}hide{/if}">

                                                    {if $penalties['personal'] && (in_array($manager->permissions, ['add_penalty']) || $penalties['personal']->manager_id==$manager->id)}
                                                        <div class="col-md-12">
                                                            <div class="alert alert-danger m-2">
                                                                <h5 class="text-danger mb-1">{$penalty_types[$penalties['personal']->id]->name}</h5>
                                                                <small>{$penalties['personal']->comment}</small>
                                                            </div>
                                                        </div>
                                                    {/if}

                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Email:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$order->email|escape}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Дата рождения:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$order->birth|escape} -
                                                                    <b>{$client_age}</b></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Место
                                                                рождения:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$order->birth_place|escape}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Паспорт:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$order->passport_serial} {$order->subdivision_code}
                                                                    , от {$order->passport_date}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Кем выдан:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$order->passport_issued}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Соцсети:</label>
                                                            <div class="col-md-8">
                                                                <ul class="list-unstyled form-control-static pl-0">
                                                                    {if $order->social}
                                                                        <li>
                                                                            <a target="_blank"
                                                                               href="{$order->social}">{$order->social}</a>
                                                                        </li>
                                                                    {/if}
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row p-2 edit-block {if !$contactdata_error}hide{/if}">
                                                    {if $contactdata_error}
                                                        <div class="col-md-12">
                                                            <ul class="alert alert-danger">
                                                                {if in_array('empty_email', (array)$contactdata_error)}
                                                                    <li>Укажите Email!</li>
                                                                {/if}
                                                                {if in_array('empty_birth', (array)$contactdata_error)}
                                                                    <li>Укажите Дату рождения!</li>
                                                                {/if}
                                                                {if in_array('empty_passport_serial', (array)$contactdata_error)}
                                                                    <li>Укажите серию и номер паспорта!</li>
                                                                {/if}
                                                                {if in_array('empty_passport_date', (array)$contactdata_error)}
                                                                    <li>Укажите дату выдачи паспорта!</li>
                                                                {/if}
                                                                {if in_array('empty_subdivision_code', (array)$contactdata_error)}
                                                                    <li>Укажите код подразделения выдавшего паспорт!
                                                                    </li>
                                                                {/if}
                                                                {if in_array('empty_passport_issued', (array)$contactdata_error)}
                                                                    <li>Укажите кем выдан паспорт!</li>
                                                                {/if}
                                                            </ul>
                                                        </div>
                                                    {/if}

                                                    <div class="col-md-6">
                                                        <div class="form-group mb-1 {if in_array('empty_email', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Email</label>
                                                            <input type="text" name="email" value="{$order->email}"
                                                                   class="form-control" placeholder=""/>
                                                            {if in_array('empty_email', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите Email!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-1 {if in_array('empty_birth', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Дата рождения</label>
                                                            <input type="text" name="birth" value="{$order->birth}"
                                                                   class="form-control" placeholder="" required="true"/>
                                                            {if in_array('empty_birth', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите дату
                                                                    рождения!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-2">
                                                            <label class="control-label">Соцсети</label>
                                                            <input type="text" class="form-control" name="social"
                                                                   value="{$order->social}" placeholder=""/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-2 {if in_array('empty_birth_place', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Место рождения</label>
                                                            <input type="text" name="birth_place"
                                                                   value="{$order->birth_place|escape}"
                                                                   class="form-control" placeholder="" required="true"/>
                                                            {if in_array('empty_birth_place', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите место
                                                                    рождения!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group mb-1 {if in_array('empty_passport_serial', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Серия и номер паспорта</label>
                                                            <input type="text" class="form-control"
                                                                   name="passport_serial"
                                                                   value="{$order->passport_serial}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_passport_serial', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите серию и
                                                                    номер паспорта!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-1 {if in_array('empty_passport_date', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Дата выдачи</label>
                                                            <input type="text" class="form-control" name="passport_date"
                                                                   value="{$order->passport_date}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_passport_date', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите дату выдачи
                                                                    паспорта!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-1 {if in_array('empty_subdivision_code', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Код подразделения</label>
                                                            <input type="text" class="form-control"
                                                                   name="subdivision_code"
                                                                   value="{$order->subdivision_code}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_subdivision_code', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите код
                                                                    подразделения выдавшего паспорт!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group {if in_array('empty_passport_issued', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Кем выдан</label>
                                                            <input type="text" class="form-control"
                                                                   name="passport_issued"
                                                                   value="{$order->passport_issued}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_passport_issued', (array)$contactdata_errors)}
                                                                <small class="form-control-feedback">Укажите кем выдан
                                                                    паспорт!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>


                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit"
                                                                    class="btn btn-success js-event-add-click"
                                                                    data-event="42" data-manager="{$manager->id}"
                                                                    data-order="{$order->order_id}"
                                                                    data-user="{$order->user_id}"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <!-- / Контакты-->

                                            <form action="{url}"
                                                  class="js-order-item-form mb-3 border {if $penalties['addresses'] && $penalties['addresses']->status!=3}card-outline-danger{/if}"
                                                  id="address_form">

                                                <input type="hidden" name="action" value="addresses"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Адрес</span>
                                                    <span class="float-right">
                                                            {penalty_button penalty_block='addresses'}
                                                        <a href="javascript:void(0);"
                                                           class="text-white js-edit-form js-event-add-click"
                                                           data-event="34" data-manager="{$manager->id}"
                                                           data-order="{$order->order_id}"
                                                           data-user="{$order->user_id}"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </span>
                                                </h5>

                                                <div class="row view-block {if $addresses_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <table class="table table-hover mb-0">
                                                            <tr>
                                                                <td>Адрес прописки</td>
                                                                <td>{$faktaddress}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Адрес проживания</td>
                                                                <td>{$regaddress}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="edit-block m-0 {if !$addresses_error}hide{/if}">

                                                    <div class="row m-0 mb-2 mt-2 js-dadata-address">
                                                        <h6 class="col-12 nav-small-cap">Адрес прописки</h6>
                                                        {if $addresses_error}
                                                            <div class="col-md-12">
                                                                <ul class="alert alert-danger">
                                                                    {if in_array('empty_regregion', (array)$addresses_error)}
                                                                        <li>Укажите область!</li>
                                                                    {/if}
                                                                    {if in_array('empty_regcity', (array)$addresses_error)}
                                                                        <li>Укажите город!</li>
                                                                    {/if}
                                                                    {if in_array('empty_regstreet', (array)$addresses_error)}
                                                                        <li>Укажите улицу!</li>
                                                                    {/if}
                                                                    {if in_array('empty_reghousing', (array)$addresses_error)}
                                                                        <li>Укажите дом!</li>
                                                                    {/if}
                                                                    {if in_array('empty_faktregion', (array)$addresses_error)}
                                                                        <li>Укажите область!</li>
                                                                    {/if}
                                                                    {if in_array('empty_faktcity', (array)$addresses_error)}
                                                                        <li>Укажите город!</li>
                                                                    {/if}
                                                                    {if in_array('empty_faktstreet', (array)$addresses_error)}
                                                                        <li>Укажите улицу!</li>
                                                                    {/if}
                                                                    {if in_array('empty_fakthousing', (array)$addresses_error)}
                                                                        <li>Укажите дом!</li>
                                                                    {/if}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_regregion', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Область</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-region"
                                                                               name="Regregion"
                                                                               value="{$order->Regregion}"
                                                                               placeholder="" required="true"/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-region-type"
                                                                               name="Regregion_shorttype"
                                                                               value="{$order->Regregion_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                {if in_array('empty_regregion', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        область!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_regcity', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Город</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-city"
                                                                               name="Regcity" value="{$order->Regcity}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-city-type"
                                                                               name="Regcity_shorttype"
                                                                               value="{$order->Regcity_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                {if in_array('empty_regcity', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        город!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Район</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-district"
                                                                               name="Regdistrict"
                                                                               value="{$order->Regdistrict}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-district-type"
                                                                               name="Regdistrict_shorttype"
                                                                               value="{$order->Regdistrict_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Нас. пункт</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-locality"
                                                                               name="Reglocality"
                                                                               value="{$order->Reglocality}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-locality-type"
                                                                               name="Reglocality_shorttype"
                                                                               value="{$order->Reglocality_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_regstreet', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Улица</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-street"
                                                                               name="Regstreet"
                                                                               value="{$order->Regstreet}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-street-type"
                                                                               name="Regstreet_shorttype"
                                                                               value="{$order->Regstreet_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                {if in_array('empty_regstreet', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        улицу!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Индекс</label>
                                                                <input type="text" class="form-control js-dadata-index"
                                                                       name="Regindex" value="{$order->Regindex}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_reghousing', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Дом</label>
                                                                <input type="text" class="form-control js-dadata-house"
                                                                       name="Reghousing" value="{$order->Reghousing}"
                                                                       placeholder=""/>
                                                                {if in_array('empty_reghousing', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите дом!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Строение</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-building"
                                                                       name="Regbuilding" value="{$order->Regbuilding}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Квартира</label>
                                                                <input type="text" class="form-control js-dadata-room"
                                                                       name="Regroom" value="{$order->Regroom}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="row m-0 js-dadata-address">
                                                        <h6 class="col-12 nav-small-cap">Адрес проживания</h6>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_faktregion', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Область</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-region"
                                                                               name="Faktregion"
                                                                               value="{$order->Faktregion}"
                                                                               placeholder="" required="true"/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-region-type"
                                                                               name="Faktregion_shorttype"
                                                                               value="{$order->Faktregion_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                {if in_array('empty_faktregion', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        область!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_faktcity', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Город</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-city"
                                                                               name="Faktcity"
                                                                               value="{$order->Faktcity}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-city-type"
                                                                               name="Faktcity_shorttype"
                                                                               value="{$order->Faktcity_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                {if in_array('empty_faktcity', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        город!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Район</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-district"
                                                                               name="Faktdistrict"
                                                                               value="{$order->Faktdistrict}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-district-type"
                                                                               name="Faktdistrict_shorttype"
                                                                               value="{$order->Faktdistrict_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Нас. пункт</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-locality"
                                                                               name="Faktlocality"
                                                                               value="{$order->Faktlocality}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-locality-type"
                                                                               name="Faktlocality_shorttype"
                                                                               value="{$order->Faktlocality_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_faktstreet', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Улица</label>
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-street"
                                                                               name="Faktstreet"
                                                                               value="{$order->Faktstreet}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <input type="text"
                                                                               class="form-control js-dadata-street-type"
                                                                               name="Faktstreet_shorttype"
                                                                               value="{$order->Faktstreet_shorttype}"
                                                                               placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                {if in_array('empty_faktstreet', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        улицу!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Индекс</label>
                                                                <input type="text" class="form-control js-dadata-index"
                                                                       name="Faktindex" value="{$order->Faktindex}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_fakthousing', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Дом</label>
                                                                <input type="text" class="form-control js-dadata-house"
                                                                       name="Fakthousing" value="{$order->Fakthousing}"
                                                                       placeholder=""/>
                                                                {if in_array('empty_fakthousing', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите дом!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Строение</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-building"
                                                                       name="Faktbuilding"
                                                                       value="{$order->Faktbuilding}" placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Квартира</label>
                                                                <input type="text" class="form-control js-dadata-room"
                                                                       name="Faktroom" value="{$order->Faktroom}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row m-0 mt-2 mb-2">
                                                        <div class="col-md-12">
                                                            <div class="form-actions">
                                                                <button type="submit"
                                                                        class="btn btn-success js-event-add-click"
                                                                        data-event="44" data-manager="{$manager->id}"
                                                                        data-order="{$order->order_id}"
                                                                        data-user="{$order->user_id}"><i
                                                                            class="fa fa-check"></i> Сохранить
                                                                </button>
                                                                <button type="button"
                                                                        class="btn btn-inverse js-cancel-edit">Отмена
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>


                                            <!-- Данные о работе -->
                                            <form action="{url}"
                                                  class="border js-order-item-form mb-3 {if $penalties['work'] && $penalties['work']->status!=3}card-outline-danger{/if}"
                                                  id="work_data_form">

                                                <input type="hidden" name="action" value="work"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Данные о работе</span>
                                                    <span class="float-right">
                                                            {penalty_button penalty_block='work'}
                                                        <a href="javascript:void(0);"
                                                           class="text-white float-right js-edit-form js-event-add-click"
                                                           data-event="35" data-manager="{$manager->id}"
                                                           data-order="{$order->order_id}"
                                                           data-user="{$order->user_id}"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </span>
                                                </h5>

                                                <div class="row m-0 pt-2 view-block {if $work_error}hide{/if}">
                                                    {if $order->workplace || $order->workphone}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0  row">
                                                                <label class="control-label col-md-4">Название
                                                                    организации:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        <span class="clearfix">
                                                                            <span class="float-left">
                                                                                {$order->workplace}
                                                                            </span>
                                                                            <span class="float-right">
                                                                                {$order->workphone}
                                                                                <button class="js-mango-call mango-call js-event-add-click"
                                                                                        data-event="62"
                                                                                        data-manager="{$manager->id}"
                                                                                        data-order="{$order->order_id}"
                                                                                        data-user="{$order->user_id}"
                                                                                        data-phone="{$order->workphone}"
                                                                                        title="Выполнить звонок">
                                                                                    <i class="fas fa-mobile-alt"></i>
                                                                                </button>
                                                                            </span>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if $order->workaddress}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-4">Адрес:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$order->workaddress}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if $order->profession}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-4">Должность:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$order->profession}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    <div class="col-md-12">
                                                        <div class="form-group  mb-0 row">
                                                            <label class="control-label col-md-4">Руководитель:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$order->chief_name}, {$order->chief_position}
                                                                    <br/>
                                                                    {$order->chief_phone}
                                                                    <button class="js-mango-call mango-call js-event-add-click"
                                                                            data-event="63"
                                                                            data-manager="{$manager->id}"
                                                                            data-order="{$order->order_id}"
                                                                            data-user="{$order->user_id}"
                                                                            data-phone="{$order->chief_phone}"
                                                                            title="Выполнить звонок">
                                                                        <i class="fas fa-mobile-alt"></i>
                                                                    </button>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group  mb-0 row">
                                                            <label class="control-label col-md-4">Доход:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$order->income}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group  mb-0 row">
                                                            <label class="control-label col-md-4">Расход:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$order->expenses}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {if $order->workcomment}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-4">Комментарий к
                                                                    работе:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$order->workcomment}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                </div>

                                                <div class="row m-0 pt-2 edit-block js-dadata-address {if !$work_error}hide{/if}">
                                                    {if $work_error}
                                                        <div class="col-md-12">
                                                            <ul class="alert alert-danger">

                                                                {if in_array('empty_workplace', (array)$work_error)}
                                                                    <li>Укажите название организации!</li>
                                                                {/if}
                                                                {if in_array('empty_profession', (array)$work_error)}
                                                                    <li>Укажите должность!</li>
                                                                {/if}
                                                                {if in_array('empty_workphone', (array)$work_error)}
                                                                    <li>Укажите рабочий телефон!</li>
                                                                {/if}
                                                                {if in_array('empty_income', (array)$work_error)}
                                                                    <li>Укажите доход!</li>
                                                                {/if}
                                                                {if in_array('empty_expenses', (array)$work_error)}
                                                                    <li>Укажите расход!</li>
                                                                {/if}
                                                                {if in_array('empty_chief_name', (array)$work_error)}
                                                                    <li>Укажите ФИО начальника!</li>
                                                                {/if}
                                                                {if in_array('empty_chief_position', (array)$work_error)}
                                                                    <li>Укажите Должность начальника!</li>
                                                                {/if}
                                                                {if in_array('empty_chief_phone', (array)$work_error)}
                                                                    <li>Укажите Телефон начальника!</li>
                                                                {/if}

                                                            </ul>
                                                        </div>
                                                    {/if}
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-0 {if in_array('empty_workplace', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Название организации</label>
                                                            <input type="text" class="form-control" name="workplace"
                                                                   value="{$order->workplace|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_workplace', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите название
                                                                    организации!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-0 {if in_array('empty_profession', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Должность</label>
                                                            <input type="text" class="form-control" name="profession"
                                                                   value="{$order->profession|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_profession', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите
                                                                    должность!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-0">
                                                            <label class="control-label">Адрес</label>
                                                            <input type="text" class="form-control" name="workaddress"
                                                                   value="{$order->workaddress|escape}" placeholder=""
                                                                   required="true"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_workphone', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Pабочий телефон</label>
                                                            <input type="text" class="form-control" name="workphone"
                                                                   value="{$order->workphone|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_workphone', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите рабочий
                                                                    телефон!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_income', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Доход</label>
                                                            <input type="text" class="form-control" name="income"
                                                                   value="{$order->income|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_income', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите доход!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_expenses', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Расход</label>
                                                            <input type="text" class="form-control" name="expenses"
                                                                   value="{$order->expenses|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_expenses', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите расход!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_chief_name', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">ФИО начальника</label>
                                                            <input type="text" class="form-control" name="chief_name"
                                                                   value="{$order->chief_name|escape}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_chief_name', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите ФИО
                                                                    начальника!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_chief_position', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Должность начальника</label>
                                                            <input type="text" class="form-control"
                                                                   name="chief_position"
                                                                   value="{$order->chief_position|escape}"
                                                                   placeholder="" required="true"/>
                                                            {if in_array('empty_chief_position', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите Должность
                                                                    начальника!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_chief_phone', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Телефон начальника</label>
                                                            <input type="text" class="form-control" name="chief_phone"
                                                                   value="{$order->chief_phone|escape}" placeholder=""/>
                                                            {if in_array('empty_chief_phone', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите Телефон
                                                                    начальника!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-group mb-0">
                                                            <label class="control-label">Комментарий к работе</label>
                                                            <input type="text" class="form-control" name="workcomment"
                                                                   value="{$order->workcomment|escape}" placeholder=""/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 pb-2 mt-2">
                                                        <div class="form-actions">
                                                            <button type="submit"
                                                                    class="btn btn-success js-event-add-click"
                                                                    data-event="45" data-manager="{$manager->id}"
                                                                    data-order="{$order->order_id}"
                                                                    data-user="{$order->user_id}"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <!-- /Данные о работе -->


                                            <!--
                                            <h3 class="box-title mt-5">UTM-метки</h3>
                                            <hr>
                                            -->
                                        </div>
                                        <div class="col-md-4 ">
                                            <div class="mb-3 border  {if $penalties['scorings'] && $penalties['scorings']->status!=3}card-outline-danger{/if}">
                                                <h5 class=" card-header">
                                                    <span class="text-white ">Скоринги</span>
                                                    <span class="float-right">
                                                            {penalty_button penalty_block='scorings'}
                                                        {if ($order->status == 1 && ($manager->id == $order->manager_id)) || $is_developer}
                                                            <a class="text-white js-run-scorings" data-type="all"
                                                               data-order="{$order->order_id}"
                                                               href="javascript:void(0);">
                                                                <i class="far fa-play-circle"></i>
                                                            </a>
                                                        {/if}
                                                        </span>
                                                </h5>
                                                <div class="message-box js-scorings-block {if $need_update_scorings}js-need-update{/if}"
                                                     data-order="{$order->order_id}">

                                                    {foreach $scoring_types as $scoring_type}
                                                        <div class="pl-2 pr-2 {if $scorings[$scoring_type->name]->status == 'new'}bg-light-warning{elseif $scorings[$scoring_type->name]->success}bg-light-success{else}bg-light-danger{/if}">
                                                            <div class="row {if !$scoring_type@last}border-bottom{/if}">
                                                                <div class="col-12 col-sm-12 pt-2">
                                                                    <h5 class="float-left">
                                                                        {$scoring_type->title}
                                                                    </h5>

                                                                    {if $scorings[$scoring_type->name]->status == 'new'}
                                                                        <span class="label label-warning float-right">Ожидание</span>
                                                                    {elseif $scorings[$scoring_type->name]->status == 'process'}
                                                                        <span class="label label-info label-sm float-right">Выполняется</span>
                                                                    {elseif $scorings[$scoring_type->name]->status == 'stopped'}
                                                                        <span class="label label-warning label-sm float-right">Остановлен</span>
                                                                    {elseif $scorings[$scoring_type->name]->status == 'error'}
                                                                        <span class="label label-danger label-sm float-right">Ошибка</span>
                                                                    {elseif $scorings[$scoring_type->name]->status == 'completed'}
                                                                        {if $scorings[$scoring_type->name]->success}
                                                                            <span class="label label-success label-sm float-right">Пройден</span>
                                                                        {else}
                                                                            <span class="label label-danger float-right">Не пройден</span>
                                                                        {/if}
                                                                    {/if}
                                                                </div>
                                                                <div class="col-8 col-sm-8 pb-2">
                                                                        <span class="mail-desc"
                                                                              title="{$scorings[$scoring_type->name]->string_result}">
                                                                            {$scorings[$scoring_type->name]->string_result}
                                                                        </span>
                                                                    {if $scoring_type->name == 'nbki'}
                                                                        {if isset($number_of_active)}
                                                                            <span class="mail-desc"
                                                                                  title="{$number_of_active}">
                                                                                        Количество активных займов: <b>{$number_of_active}</b>
                                                                                </span>
                                                                        {/if}
                                                                        {if isset($open_to_close_ratio)}
                                                                            <span class="mail-desc"
                                                                                  title="{$open_to_close_ratio}">
                                                                                       Cоотношение открытых к закрытым за 30 дней: <b>{$open_to_close_ratio}</b>
                                                                                </span>
                                                                        {/if}
                                                                    {/if}
                                                                    <span class="time">
                                                                        {if $scoring_type->name == 'fssp'}
                                                                            <span>Сумма долга: {$scorings[$scoring_type->name]->body['amount']}</span>
                                                                            <br>












{if isset($scorings[$scoring_type->name]->body['badArticles'])}
                                                                            <span>{$scorings[$scoring_type->name]->body['badArticles']}</span>
                                                                            <br>
                                                                        {/if}
                                                                        {/if}
                                                                        {if $scorings[$scoring_type->name]->created}
                                                                            {$scorings[$scoring_type->name]->created|date} {$scorings[$scoring_type->name]->created|time}
                                                                        {/if}
                                                                        {if $scoring_type->name == 'fssp2'}
                                                                            <a href="/ajax/show_fssp2.php?id={$scorings[$scoring_type->name]->id}&password=Hjkdf8d"
                                                                               target="_blank">Подробнее</a>
                                                                        {/if}
                                                                        {if $scoring_type->name == 'efrsb' && $scorings[$scoring_type->name]->body}
                                                                            {foreach $scorings[$scoring_type->name]->body as $efrsb_link}
                                                                                <a href="{$efrsb_link}"
                                                                                   target="_blank"
                                                                                   class="float-right">Подробнее</a>
                                                                            {/foreach}
                                                                        {/if}
                                                                        {if $scoring_type->name == 'nbki'}
                                                                            <a href="http://51.250.101.109/eco-nbki/{$scorings[$scoring_type->name]->id}?api=F1h1Hdf9g_h&site=eco"
                                                                               target="_blank">Подробнее</a>
                                                                        {/if}
                                                                        </span>
                                                                </div>
                                                                <div class="col-4 col-sm-4 pb-2">
                                                                    {if $order->status < 2 || $is_developer}
                                                                        {if $scorings[$scoring_type->name]->status == 'new' || $scorings[$scoring_type->name]->status == 'process' }
                                                                            <a class="btn-load text-info run-scoring-btn float-right"
                                                                               data-type="{$scoring_type->name}"
                                                                               data-order="{$order->order_id}"
                                                                               href="javascript:void(0);">
                                                                                <div class="spinner-border text-info"
                                                                                     role="status"></div>
                                                                            </a>
                                                                        {elseif $scorings[$scoring_type->name]}
                                                                            <a class="btn-load text-info js-run-scorings run-scoring-btn float-right"
                                                                               data-type="{$scoring_type->name}"
                                                                               data-order="{$order->order_id}"
                                                                               href="javascript:void(0);">
                                                                                <i class="fas fa-undo"></i>
                                                                            </a>
                                                                        {else}
                                                                            <a class="btn-load {if in_array($audit_types)}loading{/if} text-info js-run-scorings run-scoring-btn float-right"
                                                                               data-type="{$scoring_type->name}"
                                                                               data-order="{$order->order_id}"
                                                                               href="javascript:void(0);">
                                                                                <i class="far fa-play-circle"></i>
                                                                            </a>
                                                                        {/if}
                                                                    {/if}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/foreach}
                                                </div>
                                            </div>

                                            <div class="mb-3 border">
                                                <h5 class="card-header text-white">
                                                    <span>Расчет ПДН</span>
                                                </h5>

                                                <div class="row view-block p-2 {if $card_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-0 row">
                                                            <label class="control-label col-md-8 col-7">
                                                                Значение: {$pdn}
                                                            </label>
                                                            <div class="col-md-4 col-5">

                                                                <p class="form-control-static text-right">

                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <form action="{url}"
                                                  class="mb-3 border js-order-item-form {if $penalties['services'] && $penalties['services']->status!=3}card-outline-danger{/if}"
                                                  id="services_form">

                                                <input type="hidden" name="action" value="services"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>
                                                <input type="hidden" name="contract_id" value="{$contract->id}"/>


                                                <h5 class="card-header text-white">
                                                    <span>Услуги</span>
                                                    <span class="float-right ">
                                                            {penalty_button penalty_block='services'}
                                                        <a href="javascript:void(0);"
                                                           class="js-edit-form text-white js-event-add-click"
                                                           data-event="36" data-manager="{$manager->id}"
                                                           data-order="{$order->order_id}"
                                                           data-user="{$order->user_id}"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </span>
                                                </h5>

                                                <div class="row view-block p-2 {if $services_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <div style="display: flex; justify-content: space-between">
                                                            <label class="control-label">
                                                                Будь в курсе:
                                                                {if true}
                                                                    {if $contract->bud_v_kurse_returned}
                                                                        <small class="text-danger">Услуга возвращена
                                                                        </small>
                                                                    {else}
                                                                        <button class="btn btn-xs btn-danger js-return-bud-v-kurse"
                                                                                data-contract="{$contract->id}"
                                                                                type="button">Вернуть
                                                                        </button>
                                                                    {/if}
                                                                {/if}
                                                            </label>
                                                            <div>
                                                                <p class="form-control-static">
                                                                    {if $order->service_sms}
                                                                        <span class="label label-success">Вкл</span>
                                                                    {else}
                                                                        <span class="label label-danger">Выкл</span>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div style="display: flex; justify-content: space-between">
                                                            <label class="control-label">
                                                                Причина отказа:
                                                            </label>
                                                            <div>
                                                                <div data-type="reject_reason"
                                                                     data-order="{$order->order_id}"
                                                                     class="btn btn-xs btn-primary add_receipt">
                                                                    Выбить чек
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="form-control-static">
                                                                    {if $order->service_reason}
                                                                        <span class="label label-success">Вкл</span>
                                                                    {else}
                                                                        <span class="label label-danger">Выкл</span>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {foreach $polises as $polise}
                                                            <div style="display: flex; justify-content: space-between">
                                                                <label class="control-label">
                                                                    {if $polise->type == 'POLIS_STRAHOVANIYA'}
                                                                        {if isset($polise->issuance_flag)}
                                                                            Полис при выдаче {$polise->created|date}
                                                                        {else}
                                                                            Полис {$polise@iteration} при продлении {$polise->created|date}
                                                                        {/if}
                                                                    {else}
                                                                        Полис при закрытии {$polise->created|date}
                                                                    {/if}
                                                                </label>
                                                                <div>
                                                                    <div data-order="{$order->order_id}"
                                                                         data-type="{$polise->type}" {if isset($polise->issuance_flag)}
                                                                        data-issuance="1"{/if}
                                                                         data-operation="{$polise->params['insurance']->operation_id}"
                                                                         class="btn btn-xs btn-primary add_receipt">
                                                                        Выбить чек
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {/foreach}
                                                    </div>
                                                </div>

                                                <div class="row p-2 edit-block {if !$services_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        {*}
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input" name="service_sms" id="service_sms" value="1" {if $order->service_sms}checked="true"{/if} />
                                                                <label class="custom-control-label" for="service_sms">Смс информирование</label>
                                                            </div>
                                                        </div>
                                                        {*}
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input"
                                                                       name="service_reason" id="service_reason"
                                                                       value="1"
                                                                       {if $order->service_reason == 1}checked="true"{/if} />
                                                                <label class="custom-control-label"
                                                                       for="service_reason">Причина отказа</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input"
                                                                       name="service_insurance" id="service_insurance"
                                                                       value="1"
                                                                       {if $order->service_insurance == 1}checked="true"{/if} />
                                                                <label class="custom-control-label"
                                                                       for="service_insurance">Страхование</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit"
                                                                    class="btn btn-success js-event-add-click"
                                                                    data-event="46" data-manager="{$manager->id}"
                                                                    data-order="{$order->order_id}"
                                                                    data-user="{$order->user_id}"
                                                                    data-contract="{$contract->id}"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>


                                            </form>

                                            <form action="{url}"
                                                  class="mb-3 border js-order-item-form {if $penalties['cards'] && $penalties['cards']->status!=3}card-outline-danger{/if}"
                                                  id="cards_form">

                                                <input type="hidden" name="action" value="cards"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>


                                                <h5 class="card-header text-white">
                                                    <span>Карта</span>
                                                    <span class="float-right">
                                                            {penalty_button penalty_block='cards'}
                                                        {if !in_array($order->status, [3,4,5,7,8])}
                                                            <a href="javascript:void(0);"
                                                               class="js-edit-form text-white js-event-add-click"
                                                               data-event="37" data-manager="{$manager->id}"
                                                               data-order="{$order->order_id}"
                                                               data-user="{$order->user_id}"><i
                                                                        class=" fas fa-edit"></i></a>
                                                        {/if}
                                                        </span>
                                                </h5>

                                                <div class="row view-block p-2 {if $card_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-0 row {if $cards[$order->card_id]->duplicates}text-danger{/if}">
                                                            <label class="control-label col-md-8 col-7">
                                                                {$cards[$order->card_id]->pan}
                                                                <p>{$cards[$order->card_id]->bin_issuer}</p>
                                                            </label>
                                                            <div class="col-md-4 col-5">
                                                                <p class="form-control-static text-right">
                                                                    {$cards[$order->card_id]->expdate}
                                                                </p>
                                                            </div>
                                                            {if $cards[$order->card_id]->duplicates}
                                                                <div class="col-12">
                                                                    {foreach $cards[$order->card_id]->duplicates as $dupl}
                                                                        <a href="client/{$dupl->user_id}"
                                                                           class="text-danger" target="_blank">Найдено
                                                                            совпадение</a>
                                                                    {/foreach}
                                                                </div>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row p-2 edit-block {if !$card_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-4 {if in_array('empty_card', (array)$card_error)}has-danger{/if}">
                                                            <select class="form-control" name="card_id">
                                                                {foreach $cards as $card}
                                                                    <option value="{$card->id}"
                                                                            {if $card->id == $order->card_id}selected{/if}>
                                                                        {$card->pan|escape} {$card->expdate}
                                                                        {if $card->base_card}(основная){/if}
                                                                    </option>
                                                                {/foreach}
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit"
                                                                    class="btn btn-success js-event-add-click"
                                                                    data-event="47" data-manager="{$manager->id}"
                                                                    data-order="{$order->order_id}"
                                                                    data-user="{$order->user_id}"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>


                                            </form>

                                        </div>
                                    </div>
                                    <!-- -->

                                    <form action="{url}"
                                          class="border js-order-item-form mb-3 js-check-images {if $penalties['images'] && $penalties['images']->status!=3}card-outline-danger{/if}"
                                          id="images_form" data-user="{$order->user_id}">

                                        <input type="hidden" name="action" value="images"/>
                                        <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                        <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                        <h5 class="card-header">
                                            <span class="text-white">Фотографии</span>
                                            <span class="float-right">
                                                    {penalty_button penalty_block='images'}
                                                </span>
                                        </h5>

                                        <div class="row p-2 view-block {if $socials_error}hide{/if}">
                                            <ul class="col-md-12 list-inline order-images-list">
                                                {foreach $files as $file}
                                                    {if $file->status == 0}
                                                        {$item_class="border-warning"}
                                                        {$ribbon_class="ribbon-warning"}
                                                        {$ribbon_icon="fas fa-question"}
                                                    {elseif $file->status == 1}
                                                        {$item_class="border-primary"}
                                                        {$ribbon_class="ribbon-primary"}
                                                        {$ribbon_icon="fas fa-clock"}
                                                    {elseif $file->status == 2}
                                                        {$item_class="border-success border border-bg"}
                                                        {$ribbon_class="ribbon-success"}
                                                        {$ribbon_icon="fa fa-check-circle"}
                                                    {elseif $file->status == 3}
                                                        {$item_class="border-danger border"}
                                                        {$ribbon_class="ribbon-danger"}
                                                        {$ribbon_icon="fas fa-times-circle"}
                                                    {elseif $file->status == 4}
                                                        {$item_class="border-info border"}
                                                        {$ribbon_class="ribbon-info"}
                                                        {$ribbon_icon="fab fa-cloudversify"}
                                                    {/if}
                                                    <li class="order-image-item ribbon-wrapper rounded-sm border {$item_class} js-image-item"
                                                        data-status="{$file->status}" id="file_{$file->id}"
                                                        data-id="{$file->id}" data-status="{$file->status}">
                                                        <a class="js-open-popup-image image-popup-fit-width js-event-add-click"
                                                           data-event="50" data-manager="{$manager->id}"
                                                           data-order="{$order->order_id}" data-user="{$order->user_id}"
                                                           data-fancybox="user_image"
                                                           href="{$config->front_url}/files/users/{$file->name}">
                                                            <div class="ribbon ribbon-corner {$ribbon_class}"><i
                                                                        class="{$ribbon_icon}"></i></div>
                                                            <img src="{$config->front_url}/files/users/{$file->name}"
                                                                 alt="" class="img-responsive" style=""/>
                                                            <span class="label label-primary  image-label" style="">
                                                                {if $file->type == 'passport1'}Паспорт1
                                                                {elseif $file->type == 'passport2'}Паспорт2
                                                                {elseif $file->type == 'card'}Карта
                                                                {elseif $file->type == 'face'}Селфи
                                                                {else}Нет типа{/if}
                                                            </span>
                                                            {if !empty($file->sent_date)}
                                                                <span class="label label-danger" style="bottom: -25px;">
                                                                    {$file->sent_date|date_format:"%d.%m.%Y в %H:%M"}
                                                                </span>
                                                            {/if}
                                                        </a>
                                                        {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                            <div class="order-image-actions">
                                                                <div class="dropdown mr-1 show ">
                                                                    <button type="button"
                                                                            class="btn {if $file->status==2}btn-success{elseif $file->status==3}btn-danger{else}btn-secondary{/if} dropdown-toggle"
                                                                            id="dropdownMenuOffset"
                                                                            data-toggle="dropdown" aria-haspopup="true"
                                                                            aria-expanded="true">
                                                                        {if $file->status == 2}Принят
                                                                        {elseif $file->status == 3}Отклонен
                                                                        {else}Статус
                                                                        {/if}
                                                                    </button>
                                                                    <div class="dropdown-menu"
                                                                         aria-labelledby="dropdownMenuOffset"
                                                                         x-placement="bottom-start">
                                                                        <div class="p-1 dropdown-item">
                                                                            <button class="btn btn-sm btn-block btn-outline-success js-image-accept js-event-add-click"
                                                                                    data-event="51"
                                                                                    data-manager="{$manager->id}"
                                                                                    data-order="{$order->order_id}"
                                                                                    data-user="{$order->user_id}"
                                                                                    data-id="{$file->id}" type="button">
                                                                                <i class="fas fa-check-circle"></i>
                                                                                <span>Принять</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="p-1 dropdown-item">
                                                                            <button class="btn btn-sm btn-block btn-outline-danger js-image-reject js-event-add-click"
                                                                                    data-event="52"
                                                                                    data-manager="{$manager->id}"
                                                                                    data-order="{$order->order_id}"
                                                                                    data-user="{$order->user_id}"
                                                                                    data-id="{$file->id}" type="button">
                                                                                <i class="fas fa-times-circle"></i>
                                                                                <span>Отклонить</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="p-1 pt-3 dropdown-item">
                                                                            <button class="btn btn-sm btn-block btn-danger js-image-remove js-event-add-click"
                                                                                    data-event="53"
                                                                                    data-manager="{$manager->id}"
                                                                                    data-order="{$order->order_id}"
                                                                                    data-user="{$order->user_id}"
                                                                                    data-user="{$order->user_id}"
                                                                                    data-id="{$file->id}" type="button">
                                                                                <i class="fas fa-trash"></i>
                                                                                <span>Удалить</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {/if}
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </div>

                                        <div class="row edit-block {if !$images_error}hide{/if}">
                                            {foreach $files as $file}
                                                <div class="col-md-4 col-lg-3 col-xlg-3">
                                                    <div class="card card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 col-lg-8">
                                                                <div class="form-group">
                                                                    <label class="control-label">Статус</label>
                                                                    <input type="text" class="js-file-status"
                                                                           id="status_{$file->id}"
                                                                           name="status[{$file->id}]"
                                                                           value="{$file->status}"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/foreach}
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"><i
                                                                class="fa fa-check"></i> Сохранить
                                                    </button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">
                                                        Отмена
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- -->
                                    <form method="POST" enctype="multipart/form-data">

                                        <div class="form_file_item">
                                            <input type="file" name="new_file" id="new_file"
                                                   data-user="{$order->user_id}" value="" style="display:none"/>
                                            <label for="new_file" class="btn btn-large btn-primary">
                                                <i class="fa fa-plus-circle"></i>
                                                <span>Добавить</span>
                                            </label>
                                        </div>
                                    </form>

                                </div>
                            </div>

                            <!-- Комментарии -->
                            <div class="tab-pane p-3" id="comments" role="tabpanel">

                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="float-left">Комментарии к клиенту</h4>
                                        <button class="btn float-right btn-success js-open-comment-form">
                                            <i class="mdi mdi-plus-circle"></i>
                                            Добавить
                                        </button>
                                    </div>
                                    <hr class="m-3"/>
                                    <div class="col-12">
                                        {if $comments}
                                            <div class="message-box">
                                                <div class="message-widget">
                                                    {foreach $comments as $comment}
                                                        <a href="javascript:void(0);">
                                                            <div class="user-img">
                                                                <span class="round">{$comment->letter|escape}</span>
                                                            </div>
                                                            <div class="mail-contnet">
                                                                <h5>
                                                                    {$managers[$comment->manager_id]->name|escape}
                                                                    {if $comment->official && !$settings->display_only_official_comments}
                                                                        <span class="label label-success ">Официальный</span>
                                                                    {/if}
                                                                </h5>
                                                                <span class="mail-desc">
                                                                {$comment->text|nl2br}
                                                            </span>
                                                                <span class="time">{$comment->created|date} {$comment->created|time}</span>
                                                            </div>

                                                        </a>
                                                    {/foreach}
                                                </div>
                                            </div>
                                        {/if}


                                        {if !$comments && !$comments_1c}
                                            <h4>Нет комментариев</h4>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            <!-- /Комментарии -->

                            <!-- Документы -->
                            <div class="tab-pane p-3" id="documents" role="tabpanel">
                                {if $documents}
                                    <table class="table">
                                        {foreach $documents as $document}
                                            <tr>
                                                <td class="text-info">
                                                    <a target="_blank"
                                                       href="{$config->front_url}/document/{$document->user_id}/{$document->id}">
                                                        <i class="fas fa-file-pdf fa-lg"></i>&nbsp;
                                                        {$document->name|escape}
                                                    </a>
                                                </td>
                                                <td class="text-right">
                                                    {$document->created|date}
                                                    {$document->created|time}
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                {else}
                                    <h4>Нет доступных документов</h4>
                                {/if}
                            </div>
                            <!-- /Документы -->


                            <div class="tab-pane p-3" id="logs" role="tabpanel">

                                <ul class="nav nav-pills mt-4 mb-4">
                                    <li class=" nav-item"><a href="#eventlogs" class="nav-link active" data-toggle="tab"
                                                             aria-expanded="false">События</a></li>
                                    <li class="nav-item"><a href="#changelogs" class="nav-link" data-toggle="tab"
                                                            aria-expanded="false">Данные</a></li>
                                </ul>

                                <div class="tab-content br-n pn">
                                    <div id="eventlogs" class="tab-pane active">
                                        <h3>События</h3>
                                        {if $eventlogs}
                                            <table class="table table-hover ">
                                                <tbody>
                                                {foreach $eventlogs as $eventlog}
                                                    <tr class="">
                                                        <td>
                                                            <span>{$eventlog->created|date}</span>
                                                            {$eventlog->created|time}
                                                        </td>
                                                        <td>
                                                            {$events[$eventlog->event_id]|escape}
                                                        </td>
                                                        <td>
                                                            <a href="manager/{$eventlog->manager_id}">{$managers[$eventlog->manager_id]->name|escape}</a>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                            <a href="http://45.147.176.183/get/html_to_sheet?name={$order->order_id}&code=3Tfiikdfg6">...</a>
                                        {else}
                                            Нет записей
                                        {/if}

                                    </div>

                                    <div id="changelogs" class="tab-pane">
                                        <h3>Изменение данных</h3>
                                        {if $changelogs}
                                            <table class="table table-hover ">
                                                <tbody>
                                                {foreach $changelogs as $changelog}
                                                    <tr class="">
                                                        <td>
                                                            <div class="button-toggle-wrapper">
                                                                <button class="js-open-order button-toggle"
                                                                        data-id="{$changelog->id}" type="button"
                                                                        title="Подробнее"></button>
                                                            </div>
                                                            <span>{$changelog->created|date}</span>
                                                            {$changelog->created|time}
                                                        </td>
                                                        <td>
                                                            {if $changelog_types[$changelog->type]}{$changelog_types[$changelog->type]}
                                                            {else}{$changelog->type|escape}{/if}
                                                        </td>
                                                        <td>
                                                            <a href="manager/{$changelog->manager->id}">{$changelog->manager->name|escape}</a>
                                                        </td>
                                                        <td>
                                                            <a href="client/{$changelog->user->id}">
                                                                {$changelog->user->lastname|escape}
                                                                {$changelog->user->firstname|escape}
                                                                {$changelog->user->patronymic|escape}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr class="order-details" id="changelog_{$changelog->id}"
                                                        style="display:none">
                                                        <td colspan="4">
                                                            <div class="row">
                                                                <ul class="dtr-details col-md-6 list-unstyled">
                                                                    {foreach $changelog->old_values as $field => $old_value}
                                                                        <li>
                                                                            <strong>{$field}: </strong>
                                                                            <span>{$old_value}</span>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                                <ul class="col-md-6 dtr-details list-unstyled">
                                                                    {foreach $changelog->new_values as $field => $new_value}
                                                                        <li>
                                                                            <strong>{$field}: </strong>
                                                                            <span>{$new_value}</span>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        {else}
                                            Нет записей
                                        {/if}

                                    </div>

                                </div>

                            </div>

                            <div class="tab-pane p-3" id="operations" role="tabpanel">
                                {if $contract_operations}
                                    <table class="table table-hover ">
                                        <tbody>
                                        {foreach $contract_operations as $operation}
                                            <tr class="
                                                    {if in_array($operation->type, ['PAY'])}table-success{/if} 
                                                    {if in_array($operation->type, ['PERCENTS', 'CHARGE', 'PENI'])}table-danger{/if} 
                                                    {if in_array($operation->type, ['P2P', 'IMPORT'])}table-info{/if} 
                                                    {if in_array($operation->type, ['INSURANCE', 'BUD_V_KURSE', 'REJECT_REASON', 'RETURN_INSURANCE'])}table-warning{/if}
                                                ">
                                                <td>
                                                    {*}
                                                    <div class="button-toggle-wrapper">
                                                        <button class="js-open-order button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                    </div>
                                                    {*}
                                                    <span>{$operation->created|date}</span>
                                                    {$operation->created|time}
                                                </td>
                                                <td>
                                                    {if $operation->type == 'P2P'}Выдача займа{/if}
                                                    {if $operation->type == 'PAY'}
                                                        {if $operation->transaction->prolongation}
                                                            Пролонгация
                                                        {else}
                                                            Оплата займа
                                                        {/if}
                                                    {/if}
                                                    {if $operation->type == 'RECURRENT'}Оплата займа{/if}
                                                    {if $operation->type == 'RETURN_REJECT_REASON'}Возврат услуги "Причина отказа"{/if}
                                                    {if $operation->type == 'RETURN_INSURANCE'}Возврат страховки{/if}
                                                    {if $operation->type == 'PERCENTS'}Начисление процентов{/if}
                                                    {if $operation->type == 'INSURANCE'}Страховка{/if}
                                                    {if $operation->type == 'BUD_V_KURSE'}Будь в курсе{/if}
                                                    {if $operation->type == 'REJECT_REASON'}Причина отказа{/if}
                                                    {if $operation->type == 'CHARGE'}Ответственность{/if}
                                                    {if $operation->type == 'PENI'}Пени{/if}
                                                    {if $operation->type == 'IMPORT'}Импорт{/if}
                                                </td>
                                                <td>
                                                    {$operation->amount} руб
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {else}
                                    <h4>Нет операций</h4>
                                {/if}
                            </div>

                            <div id="history" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="tab-content br-n pn">
                                            <div id="navpills-orders" class="tab-pane active">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h3>Заявки</h3>
                                                        <table class="table">
                                                            <tr>
                                                                <th>Дата</th>
                                                                <th>Заявка</th>
                                                                <th>Договор</th>
                                                                <th class="text-center">Сумма</th>
                                                                <th class="text-center">Период</th>
                                                                <th class="text-center">Срок использования</th>
                                                                <th class="text-right">Статус</th>
                                                            </tr>
                                                            {foreach $orders as $o}
                                                                {if $o->contract->type != 'onec'}
                                                                    <tr>
                                                                        <td>{$o->date|date} {$o->date|time}</td>
                                                                        <td>
                                                                            <a href="order/{$o->order_id}"
                                                                               target="_blank">{$o->order_id}</a>
                                                                        </td>
                                                                        <td>
                                                                            {$o->contract->number}
                                                                        </td>
                                                                        <td class="text-center">{$o->amount}</td>
                                                                        <td class="text-center">{$o->period}</td>
                                                                        <td class="text-center">
                                                                            {if $o->contract->usage_time}
                                                                                {$o->contract->usage_time}
                                                                            {/if}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {$order_statuses[$o->status]}
                                                                            {if $o->contract->status==3}
                                                                                <br/>
                                                                                <small>{$o->contract->close_date|date} {$o->contract->close_date|time}</small>{/if}
                                                                        </td>
                                                                    </tr>
                                                                {/if}
                                                                {if $o->contract_id == $contract->id && $contract->status == 7}
                                                                    <tr>
                                                                        <td>{$contract->sold_date}</td>
                                                                        <td>
                                                                            <a href="order/{$o->order_id}"
                                                                               target="_blank">{$o->order_id}</a>
                                                                        </td>
                                                                        <td>
                                                                            {$o->contract->number}
                                                                        </td>
                                                                        <td class="text-center">{$o->amount}</td>
                                                                        <td class="text-center">{$o->period}</td>
                                                                        <td class="text-center">
                                                                            {if $o->contract->usage_time}
                                                                                {$o->contract->usage_time}
                                                                            {/if}
                                                                        </td>
                                                                        <td class="text-right">Продан</td>
                                                                    </tr>
                                                                {/if}
                                                            {/foreach}
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>


                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane p-3" id="connexions" role="tabpanel">
                                <div class="js-app-connexions" data-user="{$order->user_id}">
                                    <h3 class="m-5 p-5 text-center">Загрузка</h3>
                                </div>
                            </div>


                            <div class="tab-pane p-3" id="communications" role="tabpanel">

                                <h3>Коммуникации с клиентом</h3>
                                {if $communications}
                                    <table class="table table-hover table-bordered">
                                        <tbody>
                                        <tr class="table-success">
                                            <th>Дата</th>
                                            <th>Тип</th>
                                            <th>Пользователь</th>
                                            <th>Орг-я</th>
                                            <th>Номер</th>
                                            <th>Исходящий</th>
                                            <th>Содержание</th>
                                        </tr>
                                        {foreach $communications as $communication}
                                            <tr class="">
                                                <td>
                                                    <small>{$communication->created|date}</small>
                                                    <br/>
                                                    <small>{$communication->created|time}</small>
                                                </td>
                                                <td>
                                                    {if $communication->type == 'sms'}Смс{/if}
                                                    {if $communication->type == 'zvonobot'}Звонобот{/if}
                                                    {if $communication->type == 'call'}Звонок{/if}
                                                </td>
                                                <td>
                                                    {$managers[$communication->manager_id]->name|escape}
                                                </td>
                                                <td>
                                                    {if $communication->yuk}
                                                        <span class="label label-info">ЮК</span>
                                                    {else}
                                                        <span class="label label-success">МКК</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {$communication->number_to}
                                                </td>
                                                <td>
                                                    {$communication->number_from}
                                                </td>
                                                <td>
                                                    {$communication->content}
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {else}
                                    <h4>Нет коммуникаций</h4>
                                {/if}
                            </div>

                        </div>


                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->

    {include file='footer.tpl'}

</div>


<div id="modal_reject_reason" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отказать в выдаче кредита?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#reject_mko" role="tab"
                                   aria-controls="home5" aria-expanded="true" aria-selected="true">
                                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                                    <span class="hidden-xs-down">Отказ МКО</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#reject_client" role="tab"
                                   aria-controls="profile" aria-selected="false">
                                    <span class="hidden-sm-up"><i class="ti-user"></i></span>
                                    <span class="hidden-xs-down">Отказ клиента</span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="reject_mko"
                                 aria-labelledby="home-tab">
                                <form class="js-reject-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                    <input type="hidden" name="action" value="reject_order"/>
                                    <input type="hidden" name="status" value="3"/>
                                    <div class="form-group">
                                        <label for="admin_name" class="control-label">Выберите причину отказа:</label>
                                        <select name="reason" class="form-control">
                                            {foreach $reject_reasons as $reject_reason}
                                                {if $reject_reason->type == 'mko'}
                                                    <option value="{$reject_reason->id|escape}">{$reject_reason->admin_name|escape}</option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect"
                                                data-dismiss="modal">Отменить
                                        </button>
                                        <button type="submit"
                                                class="btn btn-success btn-lg float-right waves-effect waves-light">Да,
                                            отказать
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="reject_client" role="tabpanel" aria-labelledby="profile-tab">
                                <form class="js-reject-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                    <input type="hidden" name="action" value="reject_order"/>
                                    <input type="hidden" name="status" value="8"/>
                                    <div class="form-group">
                                        <label for="admin_name" class="control-label">Выберите причину отказа:</label>
                                        <select name="reason" class="form-control">
                                            {foreach $reject_reasons as $reject_reason}
                                                {if $reject_reason->type == 'client'}
                                                    <option value="{$reject_reason->id|escape}">{$reject_reason->admin_name|escape}</option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect"
                                                data-dismiss="modal">Отменить
                                        </button>
                                        <button type="submit"
                                                class="btn btn-success btn-lg float-right waves-effect waves-light">Да,
                                            отказать
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md md-comment">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                    <input type="hidden" name="user_id" value="{$order->user_id}"/>
                    <input type="hidden" name="block" value=""/>
                    <input type="hidden" name="action" value="add_comment"/>

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label">Комментарий:</label>
                        <textarea class="form-control" name="text" rows="6"></textarea>
                    </div>
                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                        <input type="checkbox" name="official" class="custom-control-input" id="official_check"
                               value="1">
                        <label class="custom-control-label" for="official_check">Официальный</label>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect js-event-add-click" data-event="70"
                                data-manager="{$manager->id}" data-order="{$order->order_id}"
                                data-user="{$order->user_id}" data-dismiss="modal">Отмена
                        </button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_close_contract" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Закрыть договор</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_close_contract" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                    <input type="hidden" name="user_id" value="{$order->user_id}"/>
                    <input type="hidden" name="action" value="close_contract"/>

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="close_date" class="control-label">Дата закрытия:</label>
                        <input type="text" class="form-control" name="close_date" required="" placeholder="ДД.ММ.ГГГГ"
                               value="{''|date}"/>
                    </div>
                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea class="form-control" id="comment" name="comment" required=""></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_fssp_info" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Результаты проверки</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tr>
                        <th>Номер, дата</th>
                        <th>Документ</th>
                        <th>Производство</th>
                        <th>Департамент</th>
                        <th>Закрыт</th>
                    </tr>
                    <tbody class="js-fssp-info-result">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loan_operations" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loan_operations_title">Операции по договору</h5>
                <button type="button" class="btn-close btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<div id="modal_add_penalty" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Оштрафовать</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_penalty" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                    <input type="hidden" name="manager_id" value="{$order->manager_id}"/>
                    <input type="hidden" name="control_manager_id" value="{$manager->id}"/>
                    <input type="hidden" name="block" value=""/>
                    <input type="hidden" name="action" value="add_penalty"/>

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="close_date" class="control-label">Причина:</label>
                        <select name="type_id" class="form-control">
                            <option value=""></option>
                            {foreach $penalty_types as $t}
                                <option value="{$t->id}">{$t->name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea class="form-control" id="comment" name="comment"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason"
                                 aria-labelledby="home-tab">
                                <form class="js-sms-form" data-manager-id="{$manager->id}">
                                    <input type="hidden" name="user_id" value="{$order->user_id}"/>
                                    <input type="hidden" name="order_id" value="{$order->id}"/>
                                    <input type="hidden" name="role" value=""/>
                                    <input type="hidden" name="action" value="send_sms"/>
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                                {if in_array($manager->role, ['developer', 'admin'])}
                                                    <option value="{$sms_template->id}"
                                                            title="{$sms_template->template|escape}">
                                                        {$sms_template->name|escape} ({$sms_template->template})
                                                    </option>
                                                {else}
                                                    {if $sms_template->type == 'sms_sales' ||  $sms_template->type == 'order' }
                                                        <option value="{$sms_template->id}"
                                                                title="{$sms_template->template|escape}">
                                                            {$sms_template->name|escape} ({$sms_template->template})
                                                        </option>
                                                    {/if}
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect"
                                                data-dismiss="modal">Отменить
                                        </button>
                                        <button type="submit"
                                                class="btn btn-success btn-lg float-right waves-effect waves-light">Да,
                                            отправить
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div style="margin-left: 90px;" class="form-group">
                        <button class="btn btn-info btn-lg waves-effect waves-light" id="casual_sms">Свободное
                            сообщение
                        </button>
                    </div>
                    <form class="js-sms-form" name="manager_id" data-manager-id="{$manager->id}">
                        <input type="hidden" name="manager_id" value="{$manager->id}"/>
                        <input type="hidden" name="user_id" value="{$order->user_id}"/>
                        <input type="hidden" name="order_id" value="{$order->id}"/>
                        <input type="hidden" name="role" value="{$manager->role}"/>
                        <input type="hidden" name="action" value="send_sms"/>
                        <textarea name="text_sms" class="form-control casual-sms-form"
                                  style="display: none; height: 250px;"></textarea>
                        <ul class="casual-sms-form" style="display: none; margin-top: 5px">
                            <li>$firstname = Имя</li>
                            <li>$fio = ФИО</li>
                            <li>$prolongation_sum = Сумма для пролонгации</li>
                            <li>$final_sum = Сумма для погашения займа</li>
                        </ul>
                        <button class="btn btn-success btn-lg waves-effect waves-light casual-sms-form"
                                id="send_casual_sms" style="display: none;">Отправить свободное сообщение
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="contacts_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-x">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title contacts_modal_title"></h5>
            </div>
            <div class="modal-body">
                <form id="contacts_form">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="user_id" value="{$client->id}">
                    <div style="display: flex; flex-direction: column">
                        <div class="form-group">
                            <label class="custom-label">ФИО</label>
                            <input type="text" class="form-control" name="fio">
                        </div>
                        <div class="form-group">
                            <label class="custom-label">Номер телефона</label>
                            <input type="text" class="form-control" placeholder="Например 79966225511" name="phone">
                        </div>
                        <div class="form-group">
                            <label class="custom-label">Кем приходится</label>
                            <select class="form-control" name="relation">
                                <option value="none" selected>Выберите из списка</option>
                                <option value="мать/отец">мать/отец</option>
                                <option value="муж/жена">муж/жена</option>
                                <option value="сын/дочь">сын/дочь</option>
                                <option value="коллега">коллега</option>
                                <option value="друг/сосед">друг/сосед</option>
                                <option value="иной родственник">иной родственник</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="custom-label">Комментарий</label>
                            <textarea class="form-control" name="comment"></textarea>
                        </div>
                        <div style="display: flex; justify-content: space-between">
                            <div id="contacts_actions" class="btn btn-success">Сохранить</div>
                            <div class="btn btn-danger close_contacts_modal">Отменить</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="restruct_modal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Реструктуризация</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="alert" style="display:none"></div>
                <form method="POST" id="restruct_form">
                    <input type="hidden" name="action" value="restruct">
                    <input type="hidden" name="userId" value="{$order->user_id}">
                    <input type="hidden" name="orderId" value="{$order->order_id}">
                    <input type="hidden" name="contractId" value="{$contract->id}">
                    <div class="form-group">
                        <label class="control-label">Новый график платежей:</label>
                    </div>
                    <div id="payments_schedules">
                        <div class="form-group" style="display: flex">
                            <input class="form-control daterange" name="date[][date]">
                            <input placeholder="Платеж" style="margin-left: 5px" class="form-control"
                                   name="payment[][payment]">
                            <input placeholder="ОД" style="margin-left: 5px" class="form-control" name="payOd[][payOd]">
                            <input placeholder="Процент" style="margin-left: 5px" class="form-control"
                                   name="payPrc[][payPrc]">
                            <input placeholder="Пени" style="margin-left: 5px" class="form-control"
                                   name="payPeni[][payPeni]">
                            <div style="margin-left: 5px" class="btn btn-success addPeriod">
                                +
                            </div>
                        </div>
                    </div>
                    <input type="button" class="btn btn-danger" data-dismiss="modal" value="Отмена">
                    <input type="button" class="btn btn-success saveRestruct" value="Сохранить">
                </form>
            </div>
        </div>
    </div>
</div>
<div id="sms_confirm_modal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Подтвердить смс</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="alert" style="display:none"></div>
                <div style="display: flex;" class="col-md-12">
                    <input type="text" class="form-control code_asp"
                           placeholder="SMS код"
                           value="{if $is_developer}{$contract->accept_code}{/if}"/>
                    <div class="phone_send_code badge badge-danger"
                         style="position: absolute; margin-left: 350px; margin-top: 5px; right: 150px;display: none">
                    </div>
                    <button class="btn btn-info confirm_asp" type="button"
                            data-user="{$order->user_id}"
                            data-contract="{$contract->id}"
                            style="margin-left: 15px;"
                            data-phone="{$order->phone_mobile}">Подтвердить
                    </button>
                </div>
                <br>
                <div class="col-md-12">
                    <button data-user="{$order->user_id}"
                            id="send_asp"
                            data-phone="{$order->phone_mobile}"
                            data-order="{$order->order_id}"
                            class="btn btn-primary btn-block send_asp_code">
                        Отправить смс повторно
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editLoanProfitModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Скорректировать долг / Остановить начисления</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="alert" style="display:none"></div>
                <form method="POST" id="editLoanProfitForm">
                    <input type="hidden" name="action" value="editLoanProfit">
                    <input type="hidden" name="contractId" value="{$contract->id}">
                    <div class="form-group">
                        <label class="control-label">Основной долг:</label>
                        <input type="text" class="form-control" name="body" value="{$contract->loan_body_summ}">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Процент:</label>
                        <input type="text" class="form-control" name="prc" value="{$contract->loan_percents_summ}">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Пени:</label>
                        <input type="text" class="form-control" name="peni" value="{$contract->loan_peni_summ}">
                    </div>
                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                        <input type="checkbox" id="stopProfit" name="stopProfit" class="custom-control-input" {if $contract->stop_profit == 1}checked{/if}>
                        <label class="custom-control-label" for="stopProfit">
                            Остановить начисления
                        </label>
                    </div>
                    <input type="button" class="btn btn-danger" data-dismiss="modal" value="Отмена">
                    <input type="button" class="btn btn-success saveEditLoanProfit" value="Сохранить">
                </form>
            </div>
        </div>
    </div>
</div>
