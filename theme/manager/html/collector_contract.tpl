{$meta_title="Заявка №`$order->order_id`" scope=parent}

{capture name='page_scripts'}
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {
            $('.singledate').daterangepicker({
                singleDatePicker: true,
                timePicker: true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'DD.MM.YYYY hh:mm'
                },
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

            $(document).on('click', '.js-open-sms-modal', function (e) {
                e.preventDefault();

                var _user_id = $(this).data('user');
                var _order_id = $(this).data('order');
                var _yuk = $(this).hasClass('is-yuk') ? 1 : 0;

                $('#modal_send_sms').modal();
                $('#modal_send_sms[name="user_id"]').val(_user_id);
                $('#modal_send_sms[name="order_id"]').val(_order_id);
                $('#modal_send_sms[name="yuk"]').val(_yuk);
            });

            $(document).on('submit', '.js-sms-form', function (e) {
                e.preventDefault();

                var $form = $(this);

                var _user_id = $form.find('[name=user_id]').val();

                if ($form.hasClass('loading'))
                    return false;

                $.ajax({
                    url: '/ajax/communications.php',
                    data: {
                        action: 'check',
                        user_id: _user_id,
                    },
                    success: function (resp) {
                        if (!!resp) {
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
                                    }
                                },
                            })

                        }
                        else {
                            Swal.fire({
                                title: 'Ошибка!',
                                text: 'Исчерпан лимит коммуникаций',
                                type: 'error',
                            });

                        }
                    }
                })

            });

            $('#casual_sms').on('click', function (e) {
                e.preventDefault();

                $('.casual-sms-form').toggle('slow');
            });

            $('.add_to_blacklist').on('click', function () {

                let user_id = $(this).attr('data-user');

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'blacklist',
                        user_id: user_id
                    }
                })
            });
        })
    </script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/collector_contract.app.js"></script>
{/capture}

{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link href="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet"/>
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        .collapsed-icon:before {
            width: 20px;
            height: 20px;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
            font-family: 'Font Awesome 5 Free';
            content: "\f106";
            font-weight: 900;
            color: #fff;
        }

        .collapsed .collapsed-icon:before {
            content: "\f107";
        }

        .comment-box {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .comment-box a.open .mail-desc {
            white-space: normal !important;
        }

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
                    <li class="breadcrumb-item"><a href="my_contracts">Мои договоры</a></li>
                    <li class="breadcrumb-item active">Заявка №{$order->order_id}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
            {if !empty($user_risk_op)}
                <div class="alert alert-danger" style="width: 80%">
                    Внимание!
                    {foreach $user_risk_op as $operation => $value}
                        {foreach $risk_op as $risk => $val}
                            {if $operation == $risk && $value == 1}
                                <p>Получение информации {$val}</p>
                            {/if}
                        {/foreach}
                    {/foreach}
                </div>
            {/if}
        </div>

        <div class="row" id="order_wrapper">
            <div class="col-lg-12">
                <div class="card card-outline-info">

                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">
                                <div class="col-4 col-md-3 col-lg-2">
                                    <h4 class="form-control-static">
                                        {if $order->loan_history|count > 0}
                                            <span class="label label-success" title="Клиент уже имеет погашенные займы">ПК</span>
                                        {elseif $order->first_loan}
                                            <span class="label label-info" title="Новый клиент">Новая</span>
                                        {else}
                                            <span class="label label-warning" title="Клиент уже подавал ранее заявки">Повтор</span>
                                        {/if}
                                        {if $looker_link}
                                            <a href="{$looker_link}" target="_blank" class="btn btn-info float-right"><i
                                                        class=" fas fa-address-book"></i> Смотреть ЛК</a>
                                        {/if}
                                    </h4>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3" style="display: flex;">
                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                        <input type="checkbox" class="custom-control-input js-workout-label"
                                               data-contract="{$contract->id}" id="workout_label" value="1"
                                               data-contract="{$contract->id}"
                                               {if $contract->collection_workout}checked{/if} />
                                        <label class="custom-control-label" for="workout_label">Отработан</label>
                                    </div>
                                    {if in_array($manager->role, ['chief_collector', 'team_collector', 'developer', 'admin'])}
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" data-user="{$order->user_id}" id="add_to_blacklist"
                                                   class="custom-control-input add_to_blacklist"
                                                   {if $blacklisted}checked{/if}>
                                            <label class="custom-control-label" for="add_to_blacklist">
                                                Находится в ч/с
                                            </label>
                                        </div>
                                    {/if}
                                </div>

                                <div class="col-12 col-md-6 col-lg-1">
                                    {if in_array($manager->role, ['chief_collector', 'team_collector'])}
                                        <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                            <input type="checkbox" class="custom-control-input js-sud-label"
                                                   data-contract="{$contract->id}" id="sud_label" value="1"
                                                   data-contract="{$contract->id}" {if $contract->sud}checked{/if} />
                                            <label class="custom-control-label" for="sud_label">Передан в суд</label>
                                        </div>
                                    {/if}
                                </div>

                                <div class="col-8 col-md-3 col-lg-3">
                                    <h5 class="form-control-static">
                                        дата заявки: {$order->date|date} {$order->date|time}
                                    </h5>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <h5 class="js-order-manager text-right">
                                        {if in_array($manager->role, ['admin', 'developer', 'chief_collector'])}
                                            <select name="collection_manager_id" data-contract="{$contract->id}"
                                                    class="form-control js-collection-manager-select">
                                                <option value="">Не выбран</option>
                                                {foreach $managers as $m}
                                                    {if $m->role == 'collector'}
                                                        <option value="{$m->id}"
                                                                {if $m->id == $contract->collection_manager_id}selected{/if}>{$m->name|escape}</option>
                                                    {/if}
                                                {/foreach}
                                            </select>
                                        {else}
                                            {$managers[$contract->collection_manager_id]->name|escape}
                                        {/if}
                                    </h5>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col-12 col-md-4 col-lg-3">
                                    <form action="{url}" class="js-order-item-form" id="fio_form">

                                        <input type="hidden" name="action" value="fio"/>
                                        <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                        <input type="hidden" name="user_id" value="{$order->user_id}"/>

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
                                                <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}"
                                                        data-phone="{$order->phone_mobile}"
                                                        title="Выполнить звонок">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </button>
                                                <button class="js-open-sms-modal mango-call {if $contract->sold}js-yuk{/if}"
                                                        data-user="{$contract->user_id}"
                                                        data-order="{$contract->order_id}">
                                                    <i class=" far fa-share-square"></i>
                                                </button>
                                                {if $contract->sold}
                                                    <!--span class="label label-danger">ЮК</span-->
                                                {/if}
                                            </h3>
                                            {if $contract->sold}
                                                <i>
                                                    <small class="alert alert-danger mb-0"
                                                           style="display:block;line-height:1">
                                                        "менеджер по работе с просроченной
                                                        задолженностью {$managers[$contract->collection_manager_id]->name|escape}
                                                        .
                                                        Уведомляем, что разговор записывается."
                                                    </small>
                                                </i>
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
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"><i
                                                            class="fa fa-check"></i> Сохранить
                                                </button>
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена
                                                </button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="col-12 col-md-8 col-lg-6">
                                    <form action="{url}" class="mb-3 p-2 border js-order-item-form" id="amount_form">

                                        <input type="hidden" name="action" value="amount"/>
                                        <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                        <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                        <div class="row view-block ">
                                            <div class="col-4 text-center">
                                                <h5>Сумма</h5>
                                                <h3 class="text-primary">{$order->amount} руб</h3>
                                            </div>
                                            <div class="col-4 text-center">
                                                <h5>Срок</h5>
                                                <h3 class="text-primary">{$order->period} {$order->period|plural:"день":"дней":"дня"}</h3>
                                            </div>
                                            <div class="col-4 text-center">
                                                <h5>Дата возврата</h5>
                                                <h3 class="text-primary">{$contract->return_date|date}</h3>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>ОД</h6>
                                                <h5 class="text-primary">{$contract->loan_body_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>Проц-ы</h6>
                                                <h5 class="text-primary">{$contract->loan_percents_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>Отв-ть</h6>
                                                <h5 class="text-primary">{$contract->loan_charge_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>Пени</h6>
                                                <h5 class="text-primary">{$contract->loan_peni_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>ОСД</h6>
                                                <h5 class="text-primary">{$contract->loan_body_summ + $contract->loan_percents_summ + $contract->loan_charge_summ + $contract->loan_peni_summ}
                                                    P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>Пр-я</h6>
                                                <h5 class="text-primary">
                                                    {if $contract->hide_prolongation}
                                                        -
                                                    {else}
                                                        {$contract->loan_percents_summ + $contract->loan_charge_summ} P
                                                    {/if}
                                                </h5>
                                            </div>
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
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    {if !$order->manager_id && $order->status == 0}
                                        <div class="pt-3 js-accept-order-block">
                                            <button class="btn btn-info btn-lg btn-block js-accept-order"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-hospital-symbol"></i>
                                                <span>Принять</span>
                                            </button>
                                        </div>
                                    {/if}

                                    {if $order->status < 2}
                                        <div class="js-approve-reject-block {if !$order->manager_id}hide{/if}">
                                            <button class="btn btn-success btn-block js-approve-order"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Одобрить</span>
                                            </button>
                                            <button class="btn btn-danger btn-block js-reject-order"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Отказать</span>
                                            </button>
                                        </div>
                                    {/if}

                                    <div class="js-order-status">
                                        {if $order->status == 2}
                                            <div class="card card-success mb-1">
                                                <div class="box text-center">
                                                    <h3 class="text-white mb-0">Одобрена</h3>
                                                </div>
                                            </div>
                                            <button class="btn btn-danger btn-block js-reject-order"
                                                    data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                <i class="fas fa-times-circle"></i>
                                                <span>Отказать</span>
                                            </button>
                                            <form class=" pt-1 js-confirm-contract">
                                                <div class="input-group">
                                                    <input type="hidden" name="contract_id" class="js-contract-id"
                                                           value="{$order->contract_id}"/>
                                                    <input type="hidden" name="phone" class="js-contract-phone"
                                                           value="{$order->phone_mobile}"/>
                                                    <input type="text" class="form-control js-contract-code"
                                                           placeholder="SMS код"
                                                           value="{if $is_developer}{$contract->accept_code}{/if}"/>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-info " type="submit">Подтвердить</button>
                                                    </div>
                                                </div>
                                                <a href="javascript:void(0);" class="js-sms-send"
                                                   data-contract="{$order->contract_id}">
                                                    <span>Отправить смс код</span>
                                                    <span class="js-sms-timer"></span>
                                                </a>
                                            </form>
                                        {/if}
                                        {if $order->status == 3}
                                            <div class="card card-danger">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Отказ</h3>
                                                    <small title="Причина отказа"><i>{$order->reject_reason}</i></small>
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
                                                <div class="card card-danger mb-2">
                                                    <div class="box text-center">
                                                        <h3 class="text-white">Просрочен</h3>
                                                        <h6>Договор {$contract->number}
                                                            от {$contract->inssuance_date|date}</h6>
                                                        {if $contract->outer_id}<h6>{$contract->outer_id}</h6>{/if}
                                                        <h5>
                                                            Просрочен: {$contract->delay} {$contract->delay|plural:'день':'дней':'дня'}</h5>
                                                    </div>
                                                </div>
                                            {elseif $contract->status == 7}
                                                <div class="card card-primary mb-2">
                                                    <div class="box text-center">
                                                        <h3 class="text-white">Продан</h3>
                                                        <h6>Договор {$contract->number}</h6>
                                                        {if $contract->outer_id}<h6>{$contract->outer_id}</h6>{/if}
                                                    </div>
                                                </div>
                                            {else}
                                                <div class="card card-primary mb-2">
                                                    <div class="box text-center">
                                                        <h3 class="text-white">Выдан</h3>
                                                        <h6>Договор {$contract->number}</h6>
                                                        {if $contract->outer_id}<h6>{$contract->outer_id}</h6>{/if}
                                                    </div>
                                                </div>
                                            {/if}
                                        {/if}
                                        {if $order->status == 6}
                                            <div class="card card-danger">
                                                <div class="box text-center">
                                                    <h3 class="text-white">Не удалось выдать</h3>
                                                    <h6>Договор {$contract->number}</h6>
                                                </div>
                                            </div>
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
                                                    <small title="Причина отказа"><i>{$order->reject_reason}</i></small>
                                                </div>
                                            </div>
                                        {/if}

                                        {if in_array($manager->role, ['admin', 'developer', 'chief_collector'])}
                                            <div class="{if $contract->collection_status < 8}hide{/if}">
                                                <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                    <input type="checkbox"
                                                           class="custom-control-input js-hide-prolongation-label"
                                                           data-contract="{$contract->id}" id="hide_prolongation_label"
                                                           value="1"
                                                           {if $contract->hide_prolongation}checked="true"{/if}>
                                                    <label class="custom-control-label" for="hide_prolongation_label">Закрыть
                                                        пролонгацию</label>
                                                </div>
                                            </div>
                                        {/if}

                                        <div>
                                            {if in_array($manager->role, ['admin', 'developer', 'chief_collector'])}
                                                <select class="form-control mt-2 js-collection-status-select"
                                                        data-contract="{$contract->id}" name="collection_status">
                                                    {foreach $collection_statuses as $cs_id => $cs_name}
                                                        <option value="{$cs_id}"
                                                                {if $cs_id == $contract->collection_status}selected{/if}>{$cs_name|escape}</option>
                                                    {/foreach}
                                                </select>
                                            {else}
                                                <div class="alert alert-danger text-center">
                                                    {$collection_statuses[$contract->collection_status]}
                                                </div>
                                            {/if}
                                        </div>

                                        {if in_array('close_contract', $manager->permissions)}
                                            <div class="pt-2">
                                                <button class="btn btn-danger btn-block js-open-close-form js-event-add-click"
                                                        data-event="15" data-user="{$order->user_id}"
                                                        data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                    Закрыть договор
                                                </button>
                                            </div>
                                        {/if}

                                    </div>
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
                                </div>
                            </div>
                        </div>


                        <ul class="mt-2 nav nav-tabs" role="tablist" id="order_tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#info" role="tab"
                                   aria-selected="false">
                                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                                    <span class="hidden-xs-down">Персональная информация</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#comments" role="tab" aria-selected="false">
                                    <span class="hidden-sm-up"><i class="ti-user"></i></span>
                                    <span class="hidden-xs-down">
                                            Комментарии {if $comments|count > 0}<span
                                                class="label label-rounded label-primary">{$comments|count}</span>{/if}
                                        </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#documents" role="tab" aria-selected="true">
                                    <span class="hidden-sm-up"><i class="ti-email"></i></span>
                                    <span class="hidden-xs-down">Документы</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#logs" role="tab" aria-selected="true">
                                    <span class="hidden-sm-up"><i class="ti-email"></i></span>
                                    <span class="hidden-xs-down">Логирование</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#operations" role="tab"
                                   aria-selected="true">
                                    <span class="hidden-sm-up"><i class="ti-email"></i></span>
                                    <span class="hidden-xs-down">Операции</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#authorizations" role="tab"
                                   aria-selected="true">
                                    <span class="hidden-sm-up"><i class="ti-email"></i></span>
                                    <span class="hidden-xs-down">Входы в ЛК</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#connexions" role="tab"
                                   aria-selected="true">
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
                                            <form action="{url}" class="mb-3 border js-order-item-form"
                                                  id="personal_data_form">

                                                <input type="hidden" name="action" value="contactdata"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white ">Контакты</span>
                                                    {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                        <a href="javascript:void(0);"
                                                           class="float-right text-white js-edit-form"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </h3>
                                                    {/if}
                                                </h5>

                                                <div class="row pt-2 view-block {if $contactdata_error}hide{/if}">
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
                                                                <p class="form-control-static">{$order->birth|escape}</p>
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
                                                            <button type="submit" class="btn btn-success"><i
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

                                            <!-- /Контактные лица -->
                                            <form action="{url}" class="js-order-item-form mb-3 border"
                                                  id="contact_persons_form">

                                                <input type="hidden" name="action" value="contacts"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Контактные лица</span>
                                                    {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                        <a href="javascript:void(0);"
                                                           class="text-white float-right js-edit-form"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </h3>
                                                    {/if}
                                                </h5>

                                                <div class="row view-block m-0 {if $contacts_error}hide{/if}">
                                                    <table class="table table-hover mb-0">
                                                        <tr>
                                                            <td>{$order->contact_person_name}</td>
                                                            <td>{$order->contact_person_relation}</td>
                                                            <td class="text-right">{$order->contact_person_phone}</td>
                                                            <td>
                                                                {*if $contract->collection_status != 8}
                                                                <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}" data-phone="{$order->contact_person_phone}" title="Выполнить звонок">
                                                                    <i class="fas fa-mobile-alt"></i>
                                                                </button>
                                                                {/if*}
                                                                <button class="js-contactperson mango-call js-open-comment-form"
                                                                        title="Добавить комментарий"
                                                                        data-contactperson="{$order->contactperson2_id}">
                                                                    <i class="fas fa-comment-dots"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>{$order->contact_person2_name}</td>
                                                            <td>{$order->contact_person2_relation}</td>
                                                            <td class="text-right">{$order->contact_person2_phone}</td>
                                                            <td>
                                                                {*if $contract->collection_status != 8}
                                                                <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}" data-phone="{$order->contact_person2_phone}" title="Выполнить звонок">
                                                                    <i class="fas fa-mobile-alt"></i>
                                                                </button>
                                                                {/if*}
                                                                <button class="js-contactperson mango-call  js-open-comment-form"
                                                                        title="Добавить комментарий"
                                                                        data-contactperson="{$order->contact_person2_id}">
                                                                    <i class="fas fa-comment-dots"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <div class="row m-0 pt-2 pb-2 edit-block {if !$contacts_error}hide{/if}">
                                                    {if $contacts_error}
                                                        <div class="col-md-12">
                                                            <ul class="alert alert-danger">
                                                                {if in_array('empty_contact_person_name', (array)$contacts_error)}
                                                                    <li>Укажите ФИО контакного лица!</li>
                                                                {/if}
                                                                {if in_array('empty_contact_person_phone', (array)$contacts_error)}
                                                                    <li>Укажите тел. контакного лица!</li>
                                                                {/if}
                                                                {if in_array('empty_contact_person2_name', (array)$contacts_error)}
                                                                    <li>Укажите ФИО контакного лица 2!</li>
                                                                {/if}
                                                                {if in_array('empty_contact_person2_phone', (array)$contacts_error)}
                                                                    <li>Укажите тел. контакного лица 2!</li>
                                                                {/if}
                                                            </ul>
                                                        </div>
                                                    {/if}
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_contact_person_name', (array)$contacts_error)}has-danger{/if}">
                                                            <label class="control-label">ФИО контакного лица</label>
                                                            <input type="text" class="form-control"
                                                                   name="contact_person_name"
                                                                   value="{$order->contact_person_name}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_contact_person_name', (array)$contacts_error)}
                                                                <small class="form-control-feedback">Укажите ФИО
                                                                    контакного лица!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                                                            <label class="control-label">Кем приходится</label>
                                                            <select class="form-control custom-select"
                                                                    name="contact_person_relation">
                                                                <option value=""
                                                                        {if $order->contact_person_relation == ''}selected=""{/if}>
                                                                    Выберите значение
                                                                </option>
                                                                <option value="мать/отец"
                                                                        {if $order->contact_person_relation == 'мать/отец'}selected=""{/if}>
                                                                    мать/отец
                                                                </option>
                                                                <option value="муж/жена"
                                                                        {if $order->contact_person_relation == 'муж/жена'}selected=""{/if}>
                                                                    муж/жена
                                                                </option>
                                                                <option value="сын/дочь"
                                                                        {if $order->contact_person_relation == 'сын/дочь'}selected=""{/if}>
                                                                    сын/дочь
                                                                </option>
                                                                <option value="коллега"
                                                                        {if $order->contact_person_relation == 'коллега'}selected=""{/if}>
                                                                    коллега
                                                                </option>
                                                                <option value="друг/сосед"
                                                                        {if $order->contact_person_relation == 'друг/сосед'}selected=""{/if}>
                                                                    друг/сосед
                                                                </option>
                                                                <option value="иной родственник"
                                                                        {if $order->contact_person_relation == 'иной родственник'}selected=""{/if}>
                                                                    иной родственник
                                                                </option>
                                                            </select>
                                                            {if in_array('empty_contact_person_relation', (array)$contacts_error)}
                                                                <small class="form-control-feedback">Укажите кем
                                                                    приходится контакное лицо!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_contact_person_phone', (array)$contacts_error)}has-danger{/if}">
                                                            <label class="control-label">Тел. контакного лица</label>
                                                            <input type="text" class="form-control"
                                                                   name="contact_person_phone"
                                                                   value="{$order->contact_person_phone}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_contact_person_phone', (array)$contacts_error)}
                                                                <small class="form-control-feedback">Укажите тел.
                                                                    контакного лица!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_contact_person2_name', (array)$contacts_error)}has-danger{/if}">
                                                            <label class="control-label">ФИО контакного лица 2</label>
                                                            <input type="text" class="form-control"
                                                                   name="contact_person2_name"
                                                                   value="{$order->contact_person2_name}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_contact_person2_name', (array)$contacts_error)}
                                                                <small class="form-control-feedback">Укажите ФИО
                                                                    контакного лица!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                                                            <label class="control-label">Кем приходится</label>
                                                            <select class="form-control custom-select"
                                                                    name="contact_person2_relation">
                                                                <option value=""
                                                                        {if $order->contact_person2_relation == ''}selected=""{/if}>
                                                                    Выберите значение
                                                                </option>
                                                                <option value="мать/отец"
                                                                        {if $order->contact_person2_relation == 'мать/отец'}selected=""{/if}>
                                                                    мать/отец
                                                                </option>
                                                                <option value="муж/жена"
                                                                        {if $order->contact_person2_relation == 'муж/жена'}selected=""{/if}>
                                                                    муж/жена
                                                                </option>
                                                                <option value="сын/дочь"
                                                                        {if $order->contact_person2_relation == 'сын/дочь'}selected=""{/if}>
                                                                    сын/дочь
                                                                </option>
                                                                <option value="коллега"
                                                                        {if $order->contact_person2_relation == 'коллега'}selected=""{/if}>
                                                                    коллега
                                                                </option>
                                                                <option value="друг/сосед"
                                                                        {if $order->contact_person2_relation == 'друг/сосед'}selected=""{/if}>
                                                                    друг/сосед
                                                                </option>
                                                                <option value="иной родственник"
                                                                        {if $order->contact_person2_relation == 'иной родственник'}selected=""{/if}>
                                                                    иной родственник
                                                                </option>
                                                            </select>
                                                            {if in_array('empty_contact_person_relation', (array)$contacts_error)}
                                                                <small class="form-control-feedback">Укажите кем
                                                                    приходится контакное лицо!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group {if in_array('empty_contact_person2_phone', (array)$contacts_error)}has-danger{/if}">
                                                            <label class="control-label">Тел. контакного лица 2</label>
                                                            <input type="text" class="form-control"
                                                                   name="contact_person2_phone"
                                                                   value="{$order->contact_person2_phone}"
                                                                   placeholder=""/>
                                                            {if in_array('empty_contact_person2_phone', (array)$contacts_error)}
                                                                <small class="form-control-feedback">Укажите тел.
                                                                    контакного лица!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>


                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit" class="btn btn-success"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>

                                            <form action="{url}" class="js-order-item-form mb-3 border"
                                                  id="address_form">

                                                <input type="hidden" name="action" value="addresses"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Адрес</span>
                                                    {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                        <a href="javascript:void(0);"
                                                           class="text-white float-right js-edit-form"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </h3>
                                                    {/if}
                                                </h5>

                                                <div class="row view-block {if $addresses_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <table class="table table-hover mb-0">
                                                            <tr>
                                                                <td>Адрес прописки</td>
                                                                <td>
                                                                    {$order->Regindex}
                                                                    {$order->Regregion} {$order->Regregion_shorttype},
                                                                    {if $order->Regcity}{$order->Regcity} {$order->Regcity_sorttype},{/if}
                                                                    {if $order->Regdistrict}{$order->Regdistrict} {$order->Regdistrict_sorttype},{/if}
                                                                    {$order->Regstreet} {$order->Regstreet_shorttype},
                                                                    {if $order->Reglocality}{$order->Reglocality} {$order->Reglocality_sorttype},{/if}
                                                                    д.{$order->Reghousing},
                                                                    {if $order->Regbuilding}стр. {$order->Regbuilding},{/if}
                                                                    {if $order->Regroom}кв.{$order->Regroom}{/if}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Адрес проживания</td>
                                                                <td>
                                                                    {$order->Faktindex}
                                                                    {$order->Faktregion} {$order->Faktregion_shorttype},
                                                                    {if $order->Faktcity}{$order->Faktcity} {$order->Faktcity_shorttype},{/if}
                                                                    {if $order->Faktdistrict}{$order->Faktdistrict} {$order->Faktdistrict_sorttype},{/if}
                                                                    {if $order->Faktstreet}{$order->Faktstreet} {$order->Faktstreet_shorttype},{/if}
                                                                    {if $order->Faktlocality}{$order->Faktlocality} {$order->Faktlocality_sorttype},{/if}
                                                                    д.{$order->Fakthousing},
                                                                    {if $order->Faktbuilding}стр. {$order->Faktbuilding},{/if}
                                                                    {if $order->Faktroom}кв.{$order->Faktroom}{/if}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="edit-block m-0 {if !$addresses_error}hide{/if}">

                                                    <div class="row m-0 mb-2 mt-2 js-dadata-address">
                                                        <h6 class="col-12 nav-small-cap">Фактический адрес</h6>
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
                                                                <input type="text" class="form-control js-dadata-region"
                                                                       name="Regregion" value="{$order->Regregion}"
                                                                       placeholder="" required="true"/>
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
                                                                <input type="text" class="form-control js-dadata-city"
                                                                       name="Regcity" value="{$order->Regcity}"
                                                                       placeholder=""/>
                                                                {if in_array('empty_regcity', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        город!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Район</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-district"
                                                                       name="Regdistrict" value="{$order->Regdistrict}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Нас. пункт</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-locality"
                                                                       name="Reglocality" value="{$order->Reglocality}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 {if in_array('empty_regstreet', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Улица</label>
                                                                <input type="text" class="form-control js-dadata-street"
                                                                       name="Regstreet" value="{$order->Regstreet}"
                                                                       placeholder=""/>
                                                                {if in_array('empty_regstreet', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        улицу!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
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
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Строение</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-building"
                                                                       name="Regbuilding" value="{$order->Regbuilding}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Квартира</label>
                                                                <input type="text" class="form-control js-dadata-room"
                                                                       name="Regroom" value="{$order->Regroom}"
                                                                       placeholder=""/>
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
                                                    </div>

                                                    <div class="row m-0 js-dadata-address">
                                                        <h6 class="col-12 nav-small-cap">Адрес регистрации</h6>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_faktregion', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Область</label>
                                                                <input type="text" class="form-control js-dadata-region"
                                                                       name="Faktregion" value="{$order->Faktregion}"
                                                                       placeholder="" required="true"/>
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
                                                                <input type="text" class="form-control js-dadata-city"
                                                                       name="Faktcity" value="{$order->Faktcity}"
                                                                       placeholder=""/>
                                                                {if in_array('empty_faktcity', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        город!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Район</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-district"
                                                                       name="Faktdistrict"
                                                                       value="{$order->Faktdistrict}" placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Нас. пункт</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-locality"
                                                                       name="Faktlocality"
                                                                       value="{$order->Faktlocality}" placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 {if in_array('empty_faktstreet', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Улица</label>
                                                                <input type="text" class="form-control js-dadata-street"
                                                                       name="Faktstreet" value="{$order->Faktstreet}"
                                                                       placeholder=""/>
                                                                {if in_array('empty_faktstreet', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        улицу!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
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
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Строение</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-building"
                                                                       name="Faktbuilding"
                                                                       value="{$order->Faktbuilding}" placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Квартира</label>
                                                                <input type="text" class="form-control js-dadata-room"
                                                                       name="Faktroom" value="{$order->Faktroom}"
                                                                       placeholder=""/>
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
                                                    </div>

                                                    <div class="row m-0 mt-2 mb-2">
                                                        <div class="col-md-12">
                                                            <div class="form-actions">
                                                                <button type="submit" class="btn btn-success"><i
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
                                            <form action="{url}" class="border js-order-item-form mb-3"
                                                  id="work_data_form">

                                                <input type="hidden" name="action" value="work"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Данные о работе</span>
                                                    {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                        <a href="javascript:void(0);"
                                                           class="text-white float-right js-edit-form"><i
                                                                    class=" fas fa-edit"></i></a>
                                                        </h3>
                                                    {/if}
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
                                                                                {if $contract->collection_status != 8}
                                                                                    <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}"
                                                                                            data-phone="{$order->workphone}"
                                                                                            title="Выполнить звонок">
                                                                                    <i class="fas fa-mobile-alt"></i>
                                                                                </button>
                                                                                {/if}
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
                                                                    {if $contract->collection_status != 8}
                                                                        <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}"
                                                                                data-phone="{$order->chief_phone}"
                                                                                title="Выполнить звонок">
                                                                            <i class="fas fa-mobile-alt"></i>
                                                                        </button>
                                                                    {/if}
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
                                                                   value="{$order->chief_phone|escape}" placeholder=""
                                                                   required="true"/>
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
                                                            <button type="submit" class="btn btn-success"><i
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

                                            <div class="mb-2">
                                                <form action="order/{$order->order_id}">

                                                    <select class="form-control js-contact-status"
                                                            data-user="{$order->user_id}"
                                                            data-contract="{$contract->id}" name="contact_status">
                                                        <option value="0" {if !$order->contact_status}selected{/if}>Нет
                                                            данных
                                                        </option>
                                                        {foreach $collector_tags as $t}
                                                            <option value="{$t->id}"
                                                                    {if $order->contact_status == $t->id}selected{/if}>{$t->name|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                </form>
                                            </div>


                                            <div class="card border">
                                                <h5 class=" card-header text-white">Комментарии</h5>

                                                <form method="POST" id="form_add_comment" class="p-2 border"
                                                      action="order/{$order->order_id}">

                                                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                    <input type="hidden" name="user_id" value="{$order->user_id}"/>
                                                    <input type="hidden" name="block" value=""/>
                                                    <input type="hidden" name="action" value="add_comment"/>

                                                    <div class="alert" style="display:none"></div>

                                                    <div class="form-group mb-1">
                                                        <textarea class="form-control" name="text"
                                                                  style="resize:vertical"></textarea>
                                                    </div>
                                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                        <input type="checkbox" class="custom-control-input"
                                                               id="official" name="official" value="1" placeholder="">
                                                        <label for="official"
                                                               class="custom-control-label">Официальный</label>
                                                    </div>
                                                    <div class="form-action">
                                                        <button type="button"
                                                                class="btn float-left btn-danger waves-effect"
                                                                data-dismiss="modal">Отмена
                                                        </button>
                                                        <button type="submit"
                                                                class="btn float-right btn-success waves-effect waves-light">
                                                            Сохранить
                                                        </button>
                                                    </div>
                                                </form>
                                                <div class="message-box comment-box">
                                                    <div class="message-widget">
                                                        {foreach $comments as $comment}
                                                            <div class="a">

                                                                <div class="mail-contnet">
                                                                    <h5>
                                                                        {$managers[$comment->manager_id]->name|escape}
                                                                        {if $comment->official && !$settings->display_only_official_comments}
                                                                            <span class="label label-success float-right">Официальный</span>
                                                                        {/if}
                                                                    </h5>
                                                                    <span class="mail-desc">
                                                                        {$comment->text|nl2br}
                                                                    </span>
                                                                    <span class="time">{$comment->created|date} {$comment->created|time}</span>
                                                                </div>

                                                            </div>
                                                        {/foreach}
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="card border js-notification-wrapper">
                                                <h5 class="bg-primary card-header text-white">Напоминания</h5>

                                                <form method="POST" id="form_add_notification" class="p-2 border">

                                                    <input type="hidden" name="contract_id" value="{$contract->id}"/>
                                                    <input type="hidden" name="action" value="add_notification"/>
                                                    <input type="hidden" name="event_id" value="10"/>

                                                    <div class="alert" style="display:none"></div>

                                                    {*}
                                                    <div class="form-group mb-1">
                                                        <select name="event_id" id="block" class="form-control">
                                                            <option value="">Выберите событие</option>
                                                            {foreach $notification_events as $ne}
                                                            <option value="{$ne->id}" >{$ne->name|escape}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                    {*}

                                                    <div class="form-group mb-1">
                                                        <div class="input-group">
                                                            <input type="text" name="notification_date"
                                                                   class="form-control singledate"
                                                                   value="{$default_notification_date|date}">
                                                            <div class="input-group-append">
                                                                    <span class="input-group-text">
                                                                        <span class="ti-calendar"></span>
                                                                    </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group mb-1">
                                                        <textarea placeholder="Комментарий" class="form-control"
                                                                  name="comment" style="resize:vertical"></textarea>
                                                    </div>
                                                    <div class="form-action">
                                                        <button type="submit"
                                                                class="btn float-right btn-success waves-effect waves-light">
                                                            Добавить
                                                        </button>
                                                    </div>
                                                </form>
                                                <div class="message-box comment-box">
                                                    <div class="message-widget">
                                                        {foreach $notifications as $notification}
                                                            <div class="a" href="javascript:void(0);">

                                                                <div class="mail-content">
                                                                    <h5>
                                                                        <strong>{$managers[$notification->manager_id]->name|escape}</strong>
                                                                        <span class="badge badge-info float-right">{$notification->notiification_date|date}</span>
                                                                    </h5>
                                                                    <h6>{$notification->event->action}</h6>
                                                                    <span class="mail-desc">
                                                                        {$notification->comment|nl2br}
                                                                    </span>
                                                                    <span class="time">{$notification->created|date} {$notification->created|time}</span>
                                                                </div>

                                                            </div>
                                                        {/foreach}
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="mb-3 border">
                                                <h5 class="collapsed card-header" data-toggle="collapse"
                                                    data-target="#scorings">
                                                    <span class="collapsed-icon"></span>
                                                    <span class="text-white ">Скоринги</span>
                                                    {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                        <a class="text-white float-right js-run-scorings"
                                                           data-type="all" data-order="{$order->order_id}"
                                                           href="javascript:void(0);">
                                                            <i class="far fa-play-circle"></i>
                                                        </a>
                                                    {/if}
                                                </h5>
                                                <div class="message-box js-scorings-block collapse {if $need_update_scorings}js-need-update{/if}"
                                                     data-order="{$order->order_id}" id="scorings">

                                                    {foreach $scoring_types as $scoring_type}
                                                        <div class="pl-2 pr-2 {if $scorings[$scoring_type->name]->status == 'new'}bg-light-warning{elseif $scorings[$scoring_type->name]->success}bg-light-success{else}bg-light-danger{/if}">
                                                            <div class="row {if !$scoring_type@last}border-bottom{/if}">
                                                                <div class="col-12 col-sm-12 pt-2">
                                                                    <h5 class="float-left">
                                                                        {$scoring_type->title}
                                                                        {if $scoring_type->name == 'fssp'}
                                                                            {if $scorings[$scoring_type->name]->found_46}
                                                                                <span class="label label-danger">46</span>
                                                                            {/if}
                                                                            {if $scorings[$scoring_type->name]->found_47}
                                                                                <span class="label label-danger">47</span>
                                                                            {/if}
                                                                        {/if}
                                                                    </h5>

                                                                    {if $scorings[$scoring_type->name]->status == 'new'}
                                                                        <span class="label label-warning float-right">Ожидание</span>
                                                                    {elseif $scorings[$scoring_type->name]->status == 'process'}
                                                                        <span class="label label-info label-sm float-right">Выполняется</span>
                                                                    {elseif $scorings[$scoring_type->name]->status == 'error' || $scorings[$scoring_type->name]->status == 'stopped'}
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
                                                                    <span class="time">
                                                                            {if $scorings[$scoring_type->name]->created}
                                                                                {$scorings[$scoring_type->name]->created|date} {$scorings[$scoring_type->name]->created|time}
                                                                            {/if}
                                                                        {if $scoring_type->name == 'fssp'}
                                                                            <a href="javascript:void(0);"
                                                                               class="js-get-fssp-info float-right"
                                                                               data-scoring="{$scorings[$scoring_type->name]->id}">Подробнее</a>
                                                                        {/if}
                                                                        </span>
                                                                </div>
                                                                <div class="col-4 col-sm-4 pb-2">
                                                                    {if $order->status < 2}
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
                                                                            <a class="btn-load {if in_array(, $audit_types)}loading{/if} text-info js-run-scorings run-scoring-btn float-right"
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

                                            <form action="{url}" class="mb-3 border js-order-item-form"
                                                  id="services_form">

                                                <input type="hidden" name="action" value="services"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>


                                                <h5 class="card-header text-white collapsed" data-toggle="collapse"
                                                    data-target="#services_list">
                                                    <span class="collapsed-icon"></span>
                                                    <span>Услуги</span>
                                                    {if $order->status < 2}
                                                        <a href="javascript:void(0);"
                                                           class="js-edit-form float-right text-white"><i
                                                                    class=" fas fa-edit"></i></a>
                                                    {/if}
                                                </h5>

                                                <div class="row view-block p-2 {if $services_error}hide{/if} collapse"
                                                     id="services_list">
                                                    <div class="col-md-12">
                                                        {*}
                                                        <div class="form-group mb-0 row">
                                                            <label class="control-label col-md-8 col-7">Смс информирование:</label>
                                                            <div class="col-md-4 col-5">
                                                                <p class="form-control-static text-right">
                                                                    {if $order->service_sms}
                                                                        <span class="label label-success">Вкл</span>
                                                                    {else}
                                                                        <span class="label label-danger">Выкл</span>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        {*}
                                                        <div class="form-group mb-0 row">
                                                            <label class="control-label col-md-8 col-7">Причина
                                                                отказа:</label>
                                                            <div class="col-md-4 col-5">
                                                                <p class="form-control-static text-right">
                                                                    {if $order->service_reason}
                                                                        <span class="label label-success">Вкл</span>
                                                                    {else}
                                                                        <span class="label label-danger">Выкл</span>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb-0 row">
                                                            <label class="control-label col-md-8 col-7">Страхование:</label>
                                                            <div class="col-md-4 col-5">
                                                                <p class="form-control-static text-right">
                                                                    {if $order->service_insurance}
                                                                        <span class="label label-success">Вкл</span>
                                                                    {else}
                                                                        <span class="label label-danger">Выкл</span>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>
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
                                                                       {if $order->service_reason}checked="true"{/if} />
                                                                <label class="custom-control-label"
                                                                       for="service_reason">Причина отказа</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input"
                                                                       name="service_insurance" id="service_insurance"
                                                                       value="1"
                                                                       {if $order->service_insurance}checked="true"{/if} />
                                                                <label class="custom-control-label"
                                                                       for="service_insurance">Страхование</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit" class="btn btn-success"><i
                                                                        class="fa fa-check"></i> Сохранить
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-inverse js-cancel-edit">Отмена
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>


                                            </form>

                                            <form action="{url}" class="mb-3 border js-order-item-form" id="cards_form">

                                                <input type="hidden" name="action" value="cards"/>
                                                <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                                <input type="hidden" name="user_id" value="{$order->user_id}"/>


                                                <h5 class="card-header text-white collapsed" data-toggle="collapse"
                                                    data-target="#cards_list">
                                                    <span class="collapsed-icon"></span>
                                                    <span>Карта</span>
                                                    {if $order->status < 2}
                                                        <a href="javascript:void(0);"
                                                           class="js-edit-form float-right text-white"><i
                                                                    class=" fas fa-edit"></i></a>
                                                    {/if}
                                                </h5>

                                                <div class="row view-block p-2 {if $card_error}hide{/if} collapse"
                                                     id="cards_list">
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-0 row">
                                                            <label class="control-label col-md-8 col-7">{$cards[$order->card_id]->pan}</label>
                                                            <div class="col-md-4 col-5">
                                                                <p class="form-control-static text-right">
                                                                    {$cards[$order->card_id]->expdate}
                                                                </p>
                                                            </div>
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
                                                            <button type="submit" class="btn btn-success"><i
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
                                        <h4>Комментарии к заявке</h4>
                                        <button class="btn float-right hidden-sm-down btn-success js-open-comment-form">
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
                                                        <div style="display: flex;">
                                                            <div class="user-img">
                                                                <span class="round">{$comment->letter|escape}</span>
                                                            </div>
                                                            <div class="mail-contnet" style="margin-left: 20px">
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
                                                        </div><br>
                                                    {/foreach}
                                                </div>
                                            </div>
                                        {/if}

                                        {if $comments_1c}
                                            <h3>Комментарии из 1С</h3>
                                            <table class="table">
                                                <tr>
                                                    <th>Дата</th>
                                                    <th>Блок</th>
                                                    <th>Комментарий</th>
                                                </tr>
                                                {foreach $comments_1c as $comment}
                                                    <tr>
                                                        <td>{$comment->created|date} {$comment->created|time}</td>
                                                        <td>{$comment->block|escape}</td>
                                                        <td>{$comment->text|nl2br}</td>
                                                    </tr>
                                                {/foreach}
                                            </table>
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
                                {/if}
                            </div>

                            <div class="tab-pane p-3" id="operations" role="tabpanel">
                                {if $contract_operations}
                                    <table class="table table-hover ">
                                        <tbody>
                                        {foreach $contract_operations as $operation}
                                            <tr class="
                                                    {if in_array($operation->type, ['PAY'])}table-success{/if}
                                                    {if in_array($operation->type, ['PERCENTS', 'CHARGE', 'PENI'])}table-danger{/if}
                                                    {if in_array($operation->type, ['P2P'])}table-info{/if}
                                                    {if in_array($operation->type, ['INSURANCE'])}table-warning{/if}
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
                                                    {if $operation->type == 'PAY'}Оплата займа{/if}
                                                    {if $operation->type == 'RECURRENT'}Оплата займа{/if}
                                                    {if $operation->type == 'PERCENTS'}Начисление процентов{/if}
                                                    {if $operation->type == 'INSURANCE'}Страховка{/if}
                                                    {if $operation->type == 'CHARGE'}Ответственность{/if}
                                                    {if $operation->type == 'PENI'}Пени{/if}
                                                </td>
                                                <td>
                                                    {$operation->amount} руб
                                                </td>
                                            </tr>
                                            <tr class="order-details" id="changelog_{$changelog->id}"
                                                style="display:none">
                                                <td colspan="3">
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
                                    <h4>Нет операций</h4>
                                {/if}
                            </div>

                            <div class="tab-pane p-3" id="authorizations" role="tabpanel">
                                {if $authorizations}
                                    <table class="table table-hover ">
                                        <tbody>
                                        <tr>
                                            <th>Дата</th>
                                            <th>IP</th>
                                            <th>UserAgent</th>
                                        </tr>
                                        {foreach $authorizations as $auth}
                                            <tr class="">
                                                <td>
                                                    <span>{$auth->created|date}</span>
                                                    {$auth->created|time}
                                                </td>
                                                <td>
                                                    {$auth->ip|escape}
                                                </td>
                                                <td>
                                                    {$auth->user_agent}
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {else}
                                    <h4>Нет входов</h4>
                                {/if}
                            </div>

                            <div class="tab-pane p-3" id="connexions" role="tabpanel">
                                <div class="js-app-connexions" data-user="{$contract->user_id}">
                                    <h3 class="m-5 p-5 text-center">Загрузка</h3>
                                </div>
                            </div>

                            <div class="tab-pane p-3" id="communications" role="tabpanel">

                                <h3>Коммуникации с клиентом</h3>
                                {if $communications}
                                    <table class="table table-hover table-bordered">
                                        <tbody>
                                        <tr class="table-grey">
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
                                                    {$communication->from_number}
                                                </td>
                                                <td>
                                                    {$communication->to_number}
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
                                                    <option value="{$reject_reason->client_name|escape}">{$reject_reason->admin_name|escape}</option>
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
                                                    <option value="{$reject_reason->client_name|escape}">{$reject_reason->admin_name|escape}</option>
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
                    <input type="hidden" name="contactperson_id" value=""/>
                    <input type="hidden" name="action" value="add_comment"/>

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label">Комментарий:</label>
                        <textarea class="form-control" name="text" rows="6"></textarea>
                    </div>
                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                        <input type="checkbox" class="custom-control-input" id="official_2" name="official" value="1"
                               placeholder="">
                        <label for="official_2" class="custom-control-label">Официальный</label>
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
                                <form class="js-sms-form">
                                    <input type="hidden" name="manager_id" value="{$manager->id}"/>
                                    <input type="hidden" name="user_id" value="{$order->user_id}"/>
                                    <input type="hidden" name="order_id" value="{$order->order_id}"/>
                                    <input type="hidden" name="yuk" value=""/>
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
                                                    {if $sms_template->type == 'collection'}
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
                </div>
                <div style="margin-left: 90px;" class="form-group">
                    <button class="btn btn-info btn-lg waves-effect waves-light" id="casual_sms">Свободное сообщение
                    </button>
                </div>
                <form class="js-sms-form" data-manager-id="{$manager->id}">
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
                        <li>Примечание: "ООО МКК Финансовый Аспект https://ecozaym24.ru/lk/login" дописывается
                            автоматически в любом сообщении
                        </li>
                    </ul>
                    <button class="btn btn-success btn-lg waves-effect waves-light casual-sms-form" id="send_casual_sms"
                            style="display: none;">Отправить свободное сообщение
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

