{$meta_title="`$client->lastname` `$client->firstname` `$client->patronymic`" scope=parent}

{capture name='page_scripts'}
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js?v=1.16"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/movements.app.js"></script>
    <script>
        $(function () {
            $(document).on('click', '.js-blocked-input', function () {
                var _blocked = $(this).is(':checked') ? 1 : 0;
                var _user = $(this).data('user');

                $.ajax({
                    data: {
                        action: 'blocked',
                        user_id: _user,
                        blocked: _blocked
                    },
                    type: 'POST'
                })
            });

            $('.edit_phone').on('click', function () {
                $('.show_phone').toggle();
                $('.edit_phone_form').toggle();

                $('.cancel_edit').on('click', function () {
                    $('.show_phone').show();
                    $('.edit_phone_form').hide();
                });

                $('.accept_edit').on('click', function () {
                    let phone = $('input[name="new_number"]').val();
                    let user = $(this).attr('data-user');

                    $.ajax({
                        method: 'POST',
                        data: {
                            action: 'edit_phone',
                            phone: phone,
                            user: user
                        },
                        success: function () {
                            location.reload();
                        }
                    });
                });
            });
        })
    </script>
{/capture}


{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link href="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet"/>
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


<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">

        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i
                            class="mdi mdi-account-card-details"></i> {$client->lastname|escape} {$client->firstname|escape} {$client->patronymic|escape}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="clients">Клиенты</a></li>
                    <li class="breadcrumb-item active">Карточка клиента</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-top">
                <div class="float-right">{$client->UID}</div>
            </div>
        </div>


        <div class="row" id="order_wrapper">
            <div class="col-lg-12">
                <div class="card card-outline-info">

                    <div class="card-body">

                        <div class="form-body">

                            <div class="row pt-2">
                                <div class="col-12">
                                    <div class="border p-2">
                                        <div class="row">
                                            <h3 class="form-control-static col-md-2">
                                                {if $client->loaded_from_1c}
                                                    <span class="label label-primary">1С</span>
                                                {/if}
                                                {if $client->have_crm_closed}
                                                    <span class="label label-primary"
                                                          title="Клиент уже имеет погашенные займы в CRM">ПК CRM</span>
                                                {elseif $client->loan_history|count > 0}
                                                    <span class="label label-success"
                                                          title="Клиент уже имеет погашенные займы в CRM">ПК</span>
                                                {elseif $client->orders|count == 1}
                                                    <span class="badge badge-success">Новый клиент</span>
                                                {elseif $client->orders|count > 1}
                                                    <span class="label label-warning">Повтор</span>
                                                {else}<span class="label label-info">Лид {$client->stages}/6</span>
                                                {/if}
                                            </h3>
                                            <h3 class="col-md-4">
                                                {$client->lastname|escape}
                                                {$client->firstname|escape}
                                                {$client->patronymic|escape}
                                            </h3>
                                            <div class="col-md-2">
                                                <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                    <input type="checkbox" class="custom-control-input js-blocked-input"
                                                           id="blocked" value="1" data-user="{$client->id}"
                                                           {if $client->blocked}checked{/if}>
                                                    <label class="custom-control-label" for="blocked"><strong
                                                                class="text-danger">Заблокирован</strong></label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <small>Дата регистрации:
                                                    {$client->created|date}</small>
                                            </div>
                                            <h3 class="col-md-2 text-right">
                                                <input type="text" class="form-control edit_phone_form"
                                                       style="width: 150px; display: none"
                                                       name="new_number"
                                                       value="{$client->phone_mobile}">
                                                <span class="show_phone">{$client->phone_mobile}</span>
                                                <button class="js-mango-call mango-call"
                                                        data-phone="{$client->phone_mobile}" title="Выполнить звонок">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </button>
                                                <a data-user="{$client->id}"
                                                   class="text-info edit_phone"><i
                                                            class=" fas fa-edit"></i></a>
                                                <div>
                                                    <br>
                                                    <input type="button" style="display: none"
                                                           data-user="{$client->id}"
                                                           class="btn btn-success edit_phone_form accept_edit"
                                                           value="Сохранить">
                                                    <input type="button" style="display: none"
                                                           class="btn btn-danger edit_phone_form cancel_edit"
                                                           value="Отмена">
                                                </div>
                                            </h3>
                                        </div>
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
                                    </div>
                                </div>
                            </div>
                        </div>


                        <ul class="mt-2 nav nav-tabs" role="tablist">
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
                                            Комментарии {if $comments|count>0}<span
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
                                <a class="nav-link" data-toggle="tab" href="#history" role="tab" aria-selected="true">
                                    <span class="hidden-sm-up"><i class="ti-email"></i></span>
                                    <span class="hidden-xs-down">Кредитная история</span>
                                </a>
                            </li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content tabcontent-border">
                            <div class="tab-pane active" id="info" role="tabpanel">
                                <div class="form-body p-2 pt-3">


                                    <div class="row">
                                        <div class="col-md-8 ">

                                            <!-- Контакты -->
                                            <form action="{url}" class="mb-3 border js-order-item-form"
                                                  id="personal_data_form">

                                                <input type="hidden" name="action" value="contactdata"/>
                                                <input type="hidden" name="user_id" id="user_id" value="{$client->id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white ">Контакты</span>
                                                    <a href="javascript:void(0);"
                                                       class="float-right text-white js-edit-form"><i
                                                                class=" fas fa-edit"></i></a></h3>
                                                </h5>

                                                <div class="row pt-2 view-block {if $contactdata_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Email:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->email|escape}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Дата рождения:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->birth|escape} -
                                                                    <b>{$client_age}</b></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Место
                                                                рождения:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->birth_place|escape}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Паспорт:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->passport_serial}
                                                                    ,
                                                                    от {$client->passport_date} {$client->subdivision_code}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Кем выдан:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">{$client->passport_issued}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row m-0">
                                                            <label class="control-label col-md-4">Соцсети:</label>
                                                            <div class="col-md-8">
                                                                <ul class="list-unstyled form-control-static pl-0">
                                                                    {if $client->social}
                                                                        <li>
                                                                            <a target="_blank"
                                                                               href="{$client->social}">{$client->social}</a>
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
                                                            <input type="text" name="email" value="{$client->email}"
                                                                   class="form-control" placeholder="" required="true"/>
                                                            {if in_array('empty_email', (array)$contactdata_error)}
                                                                <small class="form-control-feedback">Укажите Email!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-1 {if in_array('empty_birth', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Дата рождения</label>
                                                            <input type="text" name="birth" value="{$client->birth}"
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
                                                                   value="{$client->social}" placeholder=""/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-2 {if in_array('empty_birth_place', (array)$contactdata_error)}has-danger{/if}">
                                                            <label class="control-label">Место рождения</label>
                                                            <input type="text" name="birth_place"
                                                                   value="{$client->birth_place|escape}"
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
                                                                   value="{$client->passport_serial}" placeholder=""
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
                                                                   value="{$client->passport_date}" placeholder=""
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
                                                                   value="{$client->subdivision_code}" placeholder=""
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
                                                                   value="{$client->passport_issued}" placeholder=""
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

                                            <form action="{url}" class="js-order-item-form mb-3 border"
                                                  id="address_form">

                                                <input type="hidden" name="action" value="addresses"/>
                                                <input type="hidden" name="user_id" value="{$client->id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Адрес</span>
                                                    <a href="javascript:void(0);"
                                                       class="text-white float-right js-edit-form"><i
                                                                class=" fas fa-edit"></i></a></h3>
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
                                                                <input type="text" class="form-control js-dadata-region"
                                                                       name="Regregion" value="{$client->Regregion}"
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
                                                                       name="Regcity" value="{$client->Regcity}"
                                                                       placeholder="" required="true"/>
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
                                                                       name="Regdistrict" value="{$client->Regdistrict}"
                                                                       placeholder="" required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 ">
                                                                <label class="control-label">Нас. пункт</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-locality"
                                                                       name="Reglocality" value="{$client->Reglocality}"
                                                                       placeholder="" required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 {if in_array('empty_regstreet', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Улица</label>
                                                                <input type="text" class="form-control js-dadata-street"
                                                                       name="Regstreet" value="{$client->Regstreet}"
                                                                       placeholder="" required="true"/>
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
                                                                       name="Reghousing" value="{$client->Reghousing}"
                                                                       placeholder="" required="true"/>
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
                                                                       name="Regbuilding" value="{$client->Regbuilding}"
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Квартира</label>
                                                                <input type="text" class="form-control js-dadata-room"
                                                                       name="Regroom" value="{$client->Regroom}"
                                                                       placeholder="" required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Индекс</label>
                                                                <input type="text" class="form-control js-dadata-index"
                                                                       name="Regindex" value="{$client->Regindex}"
                                                                       placeholder="" required="true"/>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row m-0 js-dadata-address">
                                                        <h6 class="col-12 nav-small-cap">Адрес проживания</h6>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-1 {if in_array('empty_faktregion', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Область</label>
                                                                <input type="text" class="form-control js-dadata-region"
                                                                       name="Faktregion" value="{$client->Faktregion}"
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
                                                                       name="Faktcity" value="{$client->Faktcity}"
                                                                       placeholder="" required="true"/>
                                                                {if in_array('empty_faktcity', (array)$addresses_error)}
                                                                    <small class="form-control-feedback">Укажите
                                                                        город!
                                                                    </small>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1">
                                                                <label class="control-label">Район</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-district"
                                                                       name="Faktdistrict"
                                                                       value="{$client->Faktdistrict}" placeholder=""
                                                                       required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1">
                                                                <label class="control-label">Нас. пункт</label>
                                                                <input type="text"
                                                                       class="form-control js-dadata-locality"
                                                                       name="Faktlocality"
                                                                       value="{$client->Faktlocality}" placeholder=""
                                                                       required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-1 {if in_array('empty_faktstreet', (array)$addresses_error)}has-danger{/if}">
                                                                <label class="control-label">Улица</label>
                                                                <input type="text" class="form-control js-dadata-street"
                                                                       name="Faktstreet" value="{$client->Faktstreet}"
                                                                       placeholder="" required="true"/>
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
                                                                       name="Fakthousing" value="{$client->Fakthousing}"
                                                                       placeholder="" required="true"/>
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
                                                                       value="{$client->Faktbuilding}" placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Квартира</label>
                                                                <input type="text" class="form-control js-dadata-room"
                                                                       name="Faktroom" value="{$client->Faktroom}"
                                                                       placeholder="" required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">Индекс</label>
                                                                <input type="text" class="form-control js-dadata-index"
                                                                       name="Faktindex" value="{$client->Faktindex}"
                                                                       placeholder="" required="true"/>
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
                                                <input type="hidden" name="user_id" value="{$client->id}"/>

                                                <h5 class="card-header">
                                                    <span class="text-white">Данные о работе</span>
                                                    <a href="javascript:void(0);"
                                                       class="text-white float-right js-edit-form"><i
                                                                class=" fas fa-edit"></i></a></h3>
                                                </h5>

                                                <div class="row m-0 pt-2 view-block {if $work_error}hide{/if}">
                                                    {if $client->workplace || $client->workphone}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0  row">
                                                                <label class="control-label col-md-4">Название
                                                                    организации:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        <span class="clearfix">
                                                                            <span class="float-left">
                                                                                {$client->workplace}
                                                                            </span>
                                                                            <span class="float-right">
                                                                                {$client->workphone}
                                                                                <button class="js-mango-call mango-call"
                                                                                        data-phone="{$client->workphone}"
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
                                                    {if $client->workaddress}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-4">Адрес:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$client->workaddress|escape}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if $client->profession}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-4">Должность:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$client->profession}
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
                                                                    {$client->chief_name}, {$client->chief_position}
                                                                    <br/>
                                                                    {$client->chief_phone}
                                                                    <button class="js-mango-call mango-call"
                                                                            data-phone="{$client->chief_phone}"
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
                                                                    {$client->income}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group  mb-0 row">
                                                            <label class="control-label col-md-4">Расход:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$client->expenses}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group  mb-0 row">
                                                            <label class="control-label col-md-4">Комментарий к
                                                                работе:</label>
                                                            <div class="col-md-8">
                                                                <p class="form-control-static">
                                                                    {$client->workcomment}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                                   value="{$client->workplace|escape}" placeholder=""
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
                                                                   value="{$client->profession}" placeholder=""
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
                                                            <label class="control-label">Адрес организации</label>
                                                            <input type="text" class="form-control" name="workaddress"
                                                                   value="{$client->workaddress|escape}" placeholder=""
                                                                   required="true"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 {if in_array('empty_workphone', (array)$work_error)}has-danger{/if}">
                                                            <label class="control-label">Pабочий телефон</label>
                                                            <input type="text" class="form-control" name="workphone"
                                                                   value="{$client->workphone}" placeholder=""
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
                                                                   value="{$client->income}" placeholder=""
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
                                                                   value="{$client->expenses}" placeholder=""
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
                                                                   value="{$client->chief_name}" placeholder=""
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
                                                                   value="{$client->chief_position}" placeholder=""
                                                                   required="true"/>
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
                                                                   value="{$client->chief_phone}" placeholder=""
                                                                   required="true"/>
                                                            {if in_array('empty_chief_phone', (array)$work_error)}
                                                                <small class="form-control-feedback">Укажите Телефон
                                                                    начальника!
                                                                </small>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-0 ">
                                                            <label class="control-label">Комментарий к работе</label>
                                                            <input type="text" class="form-control" name="workcomment"
                                                                   value="{$client->workcomment}" placeholder=""/>
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
                                            <div class="mb-3 border">
                                                <h5 class=" card-header">
                                                    <span class="text-white ">Скоринги</span>
                                                    <a class="text-white float-right js-run-scorings" data-type="all"
                                                       data-order="{$client->order_id}" href="javascript:void(0);">
                                                        <i class="far fa-play-circle"></i>
                                                    </a>
                                                    </h2>
                                                    <div class="message-box">

                                                        {foreach $scoring_types as $scoring_type}
                                                            <div class="pl-2 pr-2 {if is_null($scorings[$scoring_type->name]->success)}bg-light-warning{elseif $scorings[$scoring_type->name]->success}bg-light-success{else}bg-light-danger{/if}">
                                                                <div class="row {if !$scoring_type@last}border-bottom{/if}">
                                                                    <div class="col-12 col-sm-12 pt-2">
                                                                        <h5 class="float-left">{$scoring_type->title}</h5>
                                                                        {if is_null($scorings[$scoring_type->name]->success)}
                                                                            <span class="label label-warning float-right">Нет результата</span>
                                                                        {elseif $scorings[$scoring_type->name]->success}
                                                                            <span class="label label-success label-sm float-right">Пройден</span>
                                                                        {else}
                                                                            <span class="label label-danger float-right">Не пройден</span>
                                                                        {/if}
                                                                    </div>
                                                                    <div class="col-8 col-sm-8 pb-2">
                                                                        <span class="mail-desc"
                                                                              title="{$scorings[$scoring_type->name]->string_result}">{$scorings[$scoring_type->name]->string_result}</span>
                                                                        <span class="time">
                                                                        {$scorings[$scoring_type->name]->created|date} {$scoring->created|time}
                                                                    </span>
                                                                    </div>
                                                                    <div class="col-4 col-sm-4 pb-2">
                                                                        {if is_null($scorings[$scoring_type->name]->success)}
                                                                            <a class="load-btn text-info js-run-scorings run-scoring-btn float-right"
                                                                               data-type="{$scoring_type->name}"
                                                                               data-order="{$client->order_id}"
                                                                               href="javascript:void(0);">
                                                                                <i class="far fa-play-circle"></i>
                                                                            </a>
                                                                        {else}
                                                                            <a class="text-info load-btn js-run-scorings run-scoring-btn float-right"
                                                                               data-type="{$scoring_type->name}"
                                                                               data-order="{$client->order_id}"
                                                                               href="javascript:void(0);">
                                                                                <i class="fas fa-undo"></i>
                                                                            </a>
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
                                                <input type="hidden" name="user_id" value="{$client->id}"/>


                                                <h5 class="card-header text-white">
                                                    <span>Услуги</span>
                                                    <a href="javascript:void(0);"
                                                       class="js-edit-form float-right text-white"><i
                                                                class=" fas fa-edit"></i></a>
                                                </h5>

                                                <div class="row view-block p-2 {if $services_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        {*}
                                                        <div class="form-group mb-0 row">
                                                            <label class="control-label col-md-8 col-7">Смс информирование:</label>
                                                            <div class="col-md-4 col-5">
                                                                <p class="form-control-static text-right">
                                                                    {if $client->service_sms}
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
                                                                    {if $client->service_reason}
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
                                                                    {if $client->service_insurance}
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
                                                                <input type="checkbox" class="custom-control-input" name="service_sms" id="service_sms" value="1" {if $client->service_sms}checked="true"{/if} />
                                                                <label class="custom-control-label" for="service_sms">Смс информирование</label>
                                                            </div>
                                                        </div>
                                                        {*}
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input"
                                                                       name="service_reason" id="service_reason"
                                                                       value="1"
                                                                       {if $client->service_reason}checked="true"{/if} />
                                                                <label class="custom-control-label"
                                                                       for="service_reason">Причина отказа</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input"
                                                                       name="service_insurance" id="service_insurance"
                                                                       value="1"
                                                                       {if $client->service_insurance}checked="true"{/if} />
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
                                                <input type="hidden" name="user_id" value="{$client->id}"/>

                                                <h5 class="card-header text-white">
                                                    <span>Карты</span>
                                                </h5>

                                                <div class="row view-block p-2 {if $services_error}hide{/if}">
                                                    <div class="col-md-12">
                                                        <table class="table table-stripped">
                                                            {foreach $cards as $card}
                                                                <tr>
                                                                    <td>
                                                                        <div>
                                                                            <strong>{$card->pan}</strong>
                                                                            <p>{$card->bin_issuer}</p>
                                                                        </div>
                                                                        {if $card->base_card}
                                                                            <span class="label label-primary">Основная</span>
                                                                        {/if}
                                                                    </td>
                                                                    <td>
                                                                        <div>{$card->expdate}</div>
                                                                    </td>
                                                                </tr>
                                                            {/foreach}
                                                        </table>
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


                                </div>
                            </div>

                            <div class="tab-pane p-3" id="comments" role="tabpanel">

                                <div class="row">
                                    <div class="col-12">

                                    </div>
                                    <hr class="m-3"/>
                                    <div class="col-12">
                                        {if $comments}
                                            <h4>Комментарии к заявкам</h4>
                                            <div class="message-box">
                                                <div class="message-widget">
                                                    {foreach $comments as $comment}
                                                        <a href="order/{$comment->order_id}">
                                                            <div class="user-img">
                                                                <span class="round">{$comment->letter|escape}</span>
                                                            </div>
                                                            <div class="mail-contnet">
                                                                <h5>{$managers[$comment->manager_id]->name|escape}</h5>
                                                                <span class="mail-desc">
                                                                {$comment->text|nl2br}
                                                            </span>
                                                                <span class="time">
                                                                {$comment->created|date} {$comment->created|time}
                                                                    <i>Комментарий оставлен к заявке №{$comment->order_id}</i>
                                                            </span>
                                                            </div>

                                                        </a>
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

                            <div id="history" class="tab-pane" role="tabpanel">
                                <div class="row">
                                    <div class="col-12">
                                        {*}
                                        <ul class="nav nav-pills mt-4 mb-4">
                                            <li class=" nav-item"> <a href="#navpills-orders" class="nav-link active" data-toggle="tab" aria-expanded="false">Заявки</a> </li>
                                            <li class="nav-item"> <a href="#navpills-loans" class="nav-link" data-toggle="tab" aria-expanded="false">Кредиты</a> </li>
                                        </ul>
                                        {*}
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
                                                                <th class="text-right">Статус</th>
                                                            </tr>
                                                            {foreach $client->orders as $order}
                                                                {if $order->contract->type != 'onec'}
                                                                    <tr>
                                                                        <td>{$order->date|date} {$order->date|time}</td>
                                                                        <td>
                                                                            <a href="order/{$order->order_id}"
                                                                               target="_blank">{$order->order_id}</a>
                                                                        </td>
                                                                        <td>{$order->contract->number}</td>
                                                                        <td class="text-center">{$order->amount}</td>
                                                                        <td class="text-center">{$order->period}</td>
                                                                        <td class="text-right">
                                                                            {$order_statuses[$order->status]}
                                                                            {if $order->contract->status==3}
                                                                                <br/>
                                                                                <small>{$order->contract->close_date|date} {$order->contract->close_date|time}</small>{/if}
                                                                        </td>
                                                                    </tr>
                                                                {/if}
                                                            {/foreach}
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="navpills-loans" class="tab-pane active">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h3>Кредитная история 1C</h3>
                                                        {if $client->loan_history|count > 0}
                                                            <table class="table">
                                                                <tr>
                                                                    <th>Дата</th>
                                                                    <th>Договор</th>
                                                                    <th class="text-right">Статус</th>
                                                                    <th class="text-center">Сумма</th>
                                                                    <th class="text-center">Остаток ОД</th>
                                                                    <th class="text-right">Остаток процентов</th>
                                                                    <th>&nbsp;</th>
                                                                </tr>
                                                                {foreach $client->loan_history as $loan_history_item}
                                                                    <tr>
                                                                        <td>
                                                                            {$loan_history_item->date|date}
                                                                        </td>
                                                                        <td>
                                                                            {$loan_history_item->number}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {if $loan_history_item->loan_percents_summ > 0 || $loan_history_item->loan_body_summ > 0}
                                                                                <span class="label label-success">Активный</span>
                                                                            {else}
                                                                                <span class="label label-danger">Закрыт</span>
                                                                            {/if}
                                                                        </td>
                                                                        <td class="text-center">{$loan_history_item->amount}</td>
                                                                        <td class="text-center">{$loan_history_item->loan_body_summ}</td>
                                                                        <td class="text-right">{$loan_history_item->loan_percents_summ}</td>
                                                                        <td>
                                                                            <button type="button"
                                                                                    class="btn btn-xs btn-info js-get-movements"
                                                                                    data-number="{$loan_history_item->number}">
                                                                                Операции
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                {/foreach}
                                                            </table>
                                                        {else}
                                                            <h4>Нет кредитов</h4>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                            {*}
                            <div class="tab-pane p-3" id="history" role="tabpanel">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Дата</th>
                                            <th>Сумма</th>
                                            <th>Срок</th>
                                            <th>Менеджер</th>
                                            <th>Статус</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        {foreach $client->orders as $order}
                                        <tr>
                                            <td><a href="order/{$order->order_id}">{$order->order_id}</a></td>
                                            <td>{$order->date|date} {$order->date|time}</td>
                                            <td>{$order->amount} руб</td>
                                            <td>{$order->period} {$order->period|plural:'день':'дней':'дня'}</td>
                                            <td>{$managers[$order->manager_id]->name}</td>
                                            <td>
                                                {if $order->status == 0}Новый
                                                {elseif $order->status == 1}Принят
                                                {elseif $order->status == 2}Одобрен
                                                {elseif $order->status == 3}Отказ
                                                {elseif $order->status == 4}
                                                {/if}
                                            </td>
                                        </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            {*}
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