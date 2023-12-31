{$meta_title = 'Шаблоны сообщений' scope=parent}

{capture name='page_styles'}
    <link href="theme/manager/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css"
          href="theme/manager/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
          href="theme/manager/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
    <style>
        .js-text-admin-name,
        .js-text-client-name {
        / / max-width: 300 px;
        }
    </style>
{/capture}

{capture name='page_scripts'}
    <script src="theme/manager/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/manager/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="theme/manager/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="theme/manager/js/apps/sms_templates.app.js"></script>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Шаблоны сообщений</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Шаблоны сообщений</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </button>

            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div id="accordion" class="nav-accordion" role="tablist" aria-multiselectable="true">
                    <div class="card">
                        <div class="card-header" role="tab" id="headingOne">
                            <h5 class="mb-0">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"
                                   aria-expanded="false" aria-controls="collapseOne">
                                    Переменные для шаблонов смс
                                </a>
                            </h5></div>
                        <div id="collapseOne" class="collapse" role="tabpanel" aria-labelledby="headingOne">
                            <div class="card-body">
                                <b>$accept_code</b> код подтверждения<br>
                                <b>$firstname</b> имя<br>
                                <b>$fio</b> фамилия имя отчество<br>
                                <b>$amount</b> запрашиваемая сумма в заявке<br>
                                <b>$final_sum</b> сумма для погашения<br>
                                <b>$prolongation_sum</b> сумма для пролонгации<br>
                                <b>$credit</b> одобренная сумма займа<br>
                                <b>$payment</b> сумма к оплате на текущую дату (тело + %)<br>
                                <b>$percent</b> проценты на текущую дату<br>
                                <b>$payday</b> дата платежа по займу<br>
                                <b>$contract</b> номер договора вида "0530-148805"<br>
                                <b>$loanid</b> номер заявки которую видит клиент<br>
                                <b>$crd1000</b> одобренная сумма последнего займа +1000<br>
                                <b>$crd2000</b> одобренная сумма последнего займа +2000<br>
                                <b>$crd3000</b> одобренная сумма последнего займа +3000<br>
                                <b>$crd4000</b> одобренная сумма последнего займа +4000<br>
                                <b>$crd5000</b> одобренная сумма последнего займа +5000<br>
                                <b>$crd6000</b>одобренная сумма последнего займа +6000<br>
                                <b>$crd7000</b> одобренная сумма последнего займа +7000<br>
                                <b>$crd8000</b> одобренная сумма последнего займа +8000<br>
                                <b>$crd9000</b> одобренная сумма последнего займа +9000<br>
                                <b>$crd10000</b> одобренная сумма последнего займа +10000<br>
                                <b>$today</b> дата = сегодня<br>
                                <b>$tomorrow</b> дата = сегодня + 1 день<br>
                                <b>$user_phone</b> номер телефона<br>
                                <b>$3days</b> дата = сегодня + 3 дня<br>
                                <b>$5days</b> дата = сегодня + 5 дней<br>
                                <b>$7days</b> дата = сегодня + 7 дней<br>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <h6 class="card-subtitle"></h6>
                        <div class="table-responsive m-t-40">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                    <tr>
                                        <th class="">ID</th>
                                        <th class="">Название</th>
                                        <th class="">Шаблон</th>
                                        <th class="">Тип</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">

                                    {foreach $sms_templates as $st}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$st->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-name">
                                                    {$st->name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$st->id}"/>
                                                    <input type="text" class="form-control form-control-sm" name="name"
                                                           value="{$st->name|escape}"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-template">
                                                    {$st->template|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm"
                                                           name="template" value="{$st->template|escape}"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-type">
                                                    {$template_types[$st->type]|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select name="type" class="form-control form-control-sm">
                                                        {foreach $template_types as $ttk => $ttv}
                                                            <option value="{$ttk}"
                                                                    {if $ttk == $st->type}selected{/if}>{$ttv|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" title="Редактировать"><i
                                                                class=" fas fa-edit"></i></a>
                                                    <a href="#" class="text-danger js-delete-item" title="Удалить"><i
                                                                class="far fa-trash-alt"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item"
                                                       title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item"
                                                       title="Отменить"><i class="fas fa-times-circle"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {include file='footer.tpl'}

</div>

<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить шаблон сообщения</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="name" id="name" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="template" class="control-label">Сообщение:</label>
                        <input type="text" class="form-control" name="template" id="template" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="type" class="control-label">Тип:</label>
                        <select name="type" class="form-control" id="type">
                            {foreach $template_types as $ttk => $ttv}
                                <option value="{$ttk}">{$ttv|escape}</option>
                            {/foreach}
                        </select>
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