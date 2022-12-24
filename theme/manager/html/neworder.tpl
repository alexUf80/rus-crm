{$meta_title="Новая заявка" scope=parent}

{capture name='page_scripts'}
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/neworder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.maskedinput@1.4.1/src/jquery.maskedinput.min.js"
            type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/js/jquery.suggestions.min.js"></script>
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{/capture}

{capture name='page_styles'}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/css/suggestions.min.css" rel="stylesheet"/>
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">

        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Новая заявка</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="orders/offline">Заявки оффлайн</a></li>
                    <li class="breadcrumb-item active">Новая заявка</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
        </div>

        <div class="row" id="order_wrapper">
            <div class="col-lg-12">
                <div class="card card-outline-info">
                    <div class="card-header">
                        <h4 class="mb-0 text-white float-left">Заявка</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="create_order_form">
                            <input type="hidden" name="action" value="create_order">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row edit-block ">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Клиент:</label>
                                                    <div class="col-md-8">
                                                        <input type="hidden" name="user_id"/>
                                                        <select style="width: 500px" class="search_user">
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Сумма:</label>
                                                    <div class="col-md-8">
                                                        <input type="text" name="amount" value="{$amount}"
                                                               class="form-control" placeholder="Сумма заявки" required
                                                               data-validation-required-message="This field is required"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Срок, дней:</label>
                                                    <div class="col-md-8">
                                                        <select name="period" class="form-control">
                                                            {section name=amounts start=1 loop=31 step=1}
                                                                <option value="{$smarty.section.amounts.index}">{$smarty.section.amounts.index}</option>
                                                            {/section}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <hr class="mt-3 mb-3"/>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="col-4">
                                                        <label class="control-label">Телефон</label>
                                                    </div>
                                                    <div class="col-8">
                                                        <input type="text" name="phone" value="{$order->phone}"
                                                               class="form-control phone_num" required/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row {if in_array('empty_email', (array)$personal_error)}has-danger{/if}">
                                                    <div class="col-4">
                                                        <label class="control-label">Email</label>
                                                    </div>
                                                    <div class="col-8">
                                                        <input type="text" name="email" value="{$order->email}"
                                                               class="form-control js-email-input"
                                                               placeholder="user@mail.ru" required/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="border">
                                            <h5 class="card-header"><span
                                                        class="text-white">Персональная информация</span></h5>
                                            <div class="row edit-block m-0 mb-2 mt-2 ">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Фамилия</label>
                                                        <input type="text" name="lastname"
                                                               class="form-control"
                                                               placeholder="Фамилия" required="true"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Пол</label>
                                                        <select class="form-control custom-select js-gender-input"
                                                                name="gender" id="gender">
                                                            <option value="male">
                                                                Мужской
                                                            </option>
                                                            <option value="female">
                                                                Женский
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Имя</label>
                                                        <input type="text" name="firstname" class="form-control"
                                                               placeholder="Имя"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Дата рождения</label>
                                                        <input type="text"
                                                               class="form-control daterange"
                                                               name="birth"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Отчество</label>
                                                        <input type="text" name="patronymic"
                                                               class="form-control"
                                                               placeholder="Отчество"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Место рождения</label>
                                                        <input type="text" class="form-control"
                                                               name="birth_place"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="border">
                                            <h5 class="card-header"><span class="text-white">Паспортные данные</span>
                                            </h5>
                                            <div class="row edit-block m-0 mb-2 mt-2 ">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Серия и номер паспорта</label>
                                                        <input type="text"
                                                               class="form-control"
                                                               name="passport_serial"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Дата выдачи</label>
                                                        <input type="text"
                                                               class="form-control daterange"
                                                               name="passport_date"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Код подразделения</label>
                                                        <input type="text"
                                                               class="form-control"
                                                               name="subdivision_code"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label">Кем выдан</label>
                                                        <textarea class="form-control "
                                                                  name="passport_issued"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-6 mb-2">
                                        <div class="border">
                                            <h5 class="card-header"><span class="text-white">Адрес прописки</span></h5>
                                            <div class="row m-0 mb-2 mt-2 js-dadata-address ">
                                                <div class="col-md-12">
                                                    <div class="form-group mb-1 {if in_array('empty_regregion', (array)$addresses_error)}has-danger{/if}">
                                                        <input type="text" class="form-control search_regaddress"
                                                               name="regaddress" placeholder=""/>
                                                        <input type="hidden" class="form-control"
                                                               name="regaddressfull" placeholder=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="equal" id="equal_address"/>
                                                        <label class="" for="equal_address">Адрес проживания совпадает с адресом прописки</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="border">
                                            <h5 class="card-header"><span class="text-white">Адрес проживания</span>
                                            </h5>
                                            <div class="row m-0 mb-2 mt-2 js-dadata-address">
                                                <div class="col-md-12">
                                                    <div class="form-group mb-1 faktaddress">
                                                        <input type="text" class="form-control search_regaddress" name="faktaddress" placeholder=""/>
                                                        <input type="hidden" class="form-control"
                                                               name="faktaddressfull" placeholder=""/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="border">
                                            <h5 class="card-header"><span class="text-white">Данные о работе</span></h5>
                                            <div class="row m-0 pt-2 edit-block js-dadata-work  js-dadata-address">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0 {if in_array('empty_workplace', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Название организации</label>
                                                        <input type="text" class="form-control search_workplace" name="workplace" placeholder=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0 {if in_array('empty_profession', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Должность</label>
                                                        <input type="text" class="form-control js-profession-input"
                                                               name="profession" value="{$order->profession|escape}"
                                                               placeholder="" required="true"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group mb-0">
                                                        <label class="control-label">Адрес</label>
                                                        <input type="text"
                                                               class="form-control"
                                                               name="workaddress" placeholder=""/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0 {if in_array('empty_workphone', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Pабочий телефон</label>
                                                        <input type="text"
                                                               class="form-control"
                                                               name="workphone"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0 {if in_array('empty_income', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Доход</label>
                                                        <input type="text" class="form-control js-income-input"
                                                               name="income" value="{$order->income|escape}"
                                                               placeholder="" required="true"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0 {if in_array('empty_expenses', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Расход</label>
                                                        <input type="text" class="form-control js-expenses-input"
                                                               name="expenses" value="{$order->expenses|escape}"
                                                               placeholder="" required="true"/>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group mb-0 {if in_array('empty_chief_name', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">ФИО начальника</label>
                                                        <input type="text"
                                                               class="form-control"
                                                               name="chief_name"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0 {if in_array('empty_chief_position', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Должность начальника</label>
                                                        <input type="text"
                                                               class="form-control" name="chief_position"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0 {if in_array('empty_chief_phone', (array)$work_error)}has-danger{/if}">
                                                        <label class="control-label">Телефон начальника</label>
                                                        <input type="text"
                                                               class="form-control"
                                                               name="chief_phone"/>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 mb-2">
                                                    <div class="form-group mb-0">
                                                        <label class="control-label">Комментарий к работе</label>
                                                        <input type="text" class="form-control js-workcomment-input"
                                                               name="workcomment" value="{$order->workcomment|escape}"
                                                               placeholder=""/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="border">
                                            <h5 class="card-header">
                                                <span class="text-white">Контактные лица</span>
                                                <button type="button"
                                                        class="btn-xs float-right btn btn-success btn-rounded add_person">
                                                    <i class="fas fa-plus"></i> Добавить
                                                </button>
                                            </h5
                                            <div class="row m-0 pt-2 pb-2 edit-block">
                                                <div class="col-md-12" id="contactperson_edit_block">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0">
                                                                <label class="control-label">ФИО контакного лица</label>
                                                                <input type="text" class="form-control"
                                                                       name="contact_person_name[]" value=""
                                                                       placeholder=""/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Кем приходится</label>
                                                                <select class="form-control custom-select"
                                                                        name="contact_person_relation[]">
                                                                    <option value="" selected="">Выберите значение
                                                                    </option>
                                                                    <option value="мать/отец">мать/отец</option>
                                                                    <option value="муж/жена">муж/жена</option>
                                                                    <option value="сын/дочь">сын/дочь</option>
                                                                    <option value="коллега">коллега</option>
                                                                    <option value="друг/сосед">друг/сосед</option>
                                                                    <option value="иной родственник">иной родственник
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">Тел. контакного
                                                                    лица</label>
                                                                <input type="text" class="form-control phone_num"
                                                                       name="contact_person_phone[]" value=""
                                                                       placeholder="7(999)999-99-99"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="text-right">
                                            <div class="btn btn-success send_sms">
                                                Сохранить
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
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
                    <input type="text" class="form-control sms_code"
                           placeholder="SMS код"/>
                    <div class="sent_code badge badge-danger"
                         style="position: absolute; margin-left: 350px; margin-top: 5px; right: 150px;display: none">
                    </div>
                    <button class="btn btn-info confirm_code" type="button"
                            data-user="{$order->user_id}"
                            data-order="{$order->order_id}"
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
                            class="btn btn-primary btn-block send_sms">
                        Отправить смс повторно
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>