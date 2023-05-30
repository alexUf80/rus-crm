{$meta_title='Отчет по коллекторам' scope=parent}

{capture name='page_scripts'}
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/orders.js?v=1.02"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js"></script>
    <script>
        $(function () {
            $('.js-open-show').hide();

            $(document).on('click', '.js-mango-call', function (e) {
                e.preventDefault();

                var _phone = $(this).data('phone');

                Swal.fire({
                    title: 'Выполнить звонок?',
                    text: "Вы хотите позвонить на номер: " + _phone,
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Отменить',
                    confirmButtonText: 'Да, позвонить'
                }).then((result) => {
                    if (result.value) {

                        $.ajax({
                            url: 'ajax/mango_call.php',
                            data: {
                                phone: _phone
                            },
                            beforeSend: function () {

                            },
                            success: function (resp) {
                                if (!!resp.error) {
                                    if (resp.error == 'empty_mango') {
                                        Swal.fire(
                                            'Ошибка!',
                                            'Необходимо указать Ваш внутренний номер сотрудника Mango-office.',
                                            'error'
                                        )
                                    }

                                    if (resp.error == 'empty_mango') {
                                        Swal.fire(
                                            'Ошибка!',
                                            'Не хватает прав на выполнение операции.',
                                            'error'
                                        )
                                    }
                                }
                                else if (resp.success) {
                                    Swal.fire(
                                        '',
                                        'Выполняется звонок.',
                                        'success'
                                    )
                                }
                                else {
                                    console.error(resp);
                                    Swal.fire(
                                        'Ошибка!',
                                        '',
                                        'error'
                                    )
                                }
                            }
                        })

                    }
                })


            });

            $(document).on('click', '.js-open-contract', function (e) {
                e.preventDefault();
                var _id = $(this).data('id')
                if ($(this).hasClass('open')) {
                    $(this).removeClass('open');
                    $('.js-open-hide.js-dopinfo-' + _id).show();
                    $('.js-open-show.js-dopinfo-' + _id).hide();
                }
                else {
                    $(this).addClass('open');
                    $('.js-open-hide.js-dopinfo-' + _id).hide();
                    $('.js-open-show.js-dopinfo-' + _id).show();
                }
            })

            $(document).on('change', '.js-contact-status', function () {
                var contact_status = $(this).val();
                var contract_id = $(this).data('contract');
                var user_id = $(this).data('user');
                var $form = $(this).closest('form');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: {
                        action: 'contact_status',
                        user_id: user_id,
                        contact_status: contact_status
                    },
                    success: function (resp) {
                        if (contact_status == 1)
                            $('.js-contact-status-block.js-dopinfo-' + contract_id).html('<span class="label label-success">Контактная</span>')
                        else if (contact_status == 2)
                            $('.js-contact-status-block.js-dopinfo-' + contract_id).html('<span class="label label-danger">Не контактная</span>')
                        else if (contact_status == 0)
                            $('.js-contact-status-block.js-dopinfo-' + contract_id).html('<span class="label label-warning">Нет данных</span>')

                    }
                })
            })

            $(document).on('change', '.js-contactperson-status', function () {
                var contact_status = $(this).val();
                var contactperson_id = $(this).data('contactperson');
                var $form = $(this).closest('form');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: {
                        action: 'contactperson_status',
                        contactperson_id: contactperson_id,
                        contact_status: contact_status
                    }
                })
            })


            $(document).on('click', '.js-open-comment-form', function (e) {
                e.preventDefault();

                if ($(this).hasClass('js-contactperson')) {
                    var contactperson_id = $(this).data('contactperson');
                    $('#modal_add_comment [name=contactperson_id]').val(contactperson_id);
                    $('#modal_add_comment [name=action]').val('contactperson_comment');
                    $('#modal_add_comment [name=order_id]').val($(this).data('order'));
                }
                else {
                    var contactperson_id = $(this).data('contactperson');
                    $('#modal_add_comment [name=order_id]').val($(this).data('order'));
                    $('#modal_add_comment [name=action]').val('order_comment');
                }


                $('#modal_add_comment [name=text]').text('')
                $('#modal_add_comment').modal();
            });

            $(document).on('submit', '#form_add_comment', function (e) {
                e.preventDefault();

                var $form = $(this);

                $.ajax({
                    data: $form.serialize(),
                    type: 'POST',
                    success: function (resp) {
                        if (resp.success) {
                            $('#modal_add_comment').modal('hide');
                            $form.find('[name=text]').val('')


                            Swal.fire({
                                timer: 5000,
                                title: 'Комментарий добавлен.',
                                type: 'success',
                            });
                            location.reload();
                        }
                        else {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });

                        }
                    }
                })
            })

        })
    </script>
{/capture}

{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css"/>
    <link type="text/css" rel="stylesheet"
          href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css"/>
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        .jsgrid-table {
            margin-bottom: 0
        }

        .label {
            white-space: pre;
        }

        .js-open-hide {
            display: block;
        }

        .js-open-show {
            display: none;
        }

        .open.js-open-hide {
            display: none;
        }

        .open.js-open-show {
            display: block;
        }

        .form-control.js-contactperson-status,
        .form-control.js-contact-status {
            font-size: 12px;
            padding-left: 0px;
        }
    </style>
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Отчет по коллекторам</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет по коллекторам</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <div class="row">
                    <div class="col-6 ">
                        <div class="js-daterange-filter input-group mb-3"
                             {if $period!='optional'}style="display:none"{/if}>
                            <input type="text" name="daterange" class="form-control daterange js-daterange-input"
                                   value="{if $from && $to}{$from}-{$to}{/if}">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <span class="ti-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 dropdown hidden-sm-down js-period-filter">
                        <input type="hidden" value="{$period}" id="filter_period"/>
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-calendar-alt"></i>
                            {if $period == 'today'}Сегодня
                            {elseif $period == 'yesterday'}Вчера
                            {elseif $period == 'week'}На этой неделе
                            {elseif $period == 'month'}В этом месяце
                            {elseif $period == 'year'}В этом году
                            {elseif $period == 'all'}За все время
                            {elseif $period == 'optional'}Произвольный
                            {else}{$period}{/if}
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item js-period-link {if $period == 'today'}active{/if}"
                               href="{url period='today'}">Сегодня</a>
                            <a class="dropdown-item js-period-link {if $period == 'yesterday'}active{/if}"
                               href="{url period='yesterday'}">Вчера</a>
                            <a class="dropdown-item js-period-link {if $period == 'month'}active{/if}"
                               href="{url period='month'}">В этом месяце</a>
                            <a class="dropdown-item js-period-link {if $period == 'year'}active{/if}"
                               href="{url period='year'}">В этом году</a>
                            <a class="dropdown-item js-period-link {if $period == 'all'}active{/if}"
                               href="{url period='all'}">За все время</a>
                            <a class="dropdown-item js-open-daterange {if $period == 'optional'}active{/if}"
                               href="{url period='optional' page=null}">Произвольный</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix">
                            <h4 class="card-title  float-left">Отчет по коллекторам</h4>

                            <div class="float-right js-filter-status">
                                {foreach $collection_statuses as $cs_id => $cs_name}
                                    <a href="{if $filter_status==$cs_id}{url status=null page=null}{else}{url status=$cs_id page=null}{/if}"
                                       class="btn btn-xs {if $filter_status==$cs_id}btn-success{else}btn-outline-success{/if}">{$cs_name|escape}</a>
                                {/foreach}
                            </div>

                        </div>

                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header ">

                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 120px;"
                                            class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'manager_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'manager_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            Сотрудник
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'pay_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'pay_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            Кол-во оплат
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'pay_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'pay_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            Текущий долг по контрактам
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'closed_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'closed_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            Закрыто
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'prolongation_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'prolongation_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            Пролонгации
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'total_brutto_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'total_brutto_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            Сборы без ОД
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'total_netto_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'total_netto_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            Сборы
                                        </th>
                                    </tr>
                                </table>

                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tbody>
                                    {foreach $collectors as $collector}
                                        {if $collector->name == 'Нет ответственного'}
                                            {continue}
                                        {/if}
                                        {if $collector->total_netto > 0}
                                            <tr class="jsgrid-row js-contract-row">
                                                <td style="width: 120px;" class="jsgrid-cell jsgrid-align-right">
                                                    <div class="button-toggle-wrapper">
                                                        <button class="js-open-contract button-toggle"
                                                                data-id="{$collector->id}" type="button"
                                                                title="Подробнее"></button>
                                                    </div>
                                                    <strong>
                                                        <small>{$collector->name|escape}</small>
                                                    </strong>
                                                    <br/>
                                                    <span class="label {if $collector->collection_status_id==6}label-warning{elseif $collector->collection_status_id==8}label-danger{elseif $collector->collection_status_id==4}label-primary{/if}">{$collection_statuses[$collector->collection_status_id]}</span>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    <strong>{$collector->actions} / {$collector->totals|round}</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    <strong>{$collector->all_contracts}
                                                        / {$collector->all_debt_sum|round}</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    <strong>{$collector->closed|round}</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    <strong>{$collector->prolongation|round} </strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    <strong>{$collector->total_brutto|round} </strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell">
                                                    <strong>{$collector->total_netto|round} </strong>
                                                </td>
                                            </tr>
                                            <tr class="jsgrid-row {if $manager->role!='collector'}js-open-show{/if} js-dopinfo-{$collector->id}">
                                                <td style="width: 120px;" rowspan="{($collector->items|count)+1}"
                                                    class="jsgrid-cell jsgrid-align-right">
                                                    <ul class="list-unstyled">
                                                        <li><h5>ОД: <strong
                                                                        class="text-primary">{$collector->od|round}</strong>
                                                            </h5></li>
                                                        <li><h5>Проценты: <strong
                                                                        class="text-primary">{$collector->percents|round}</strong>
                                                            </h5></li>
                                                        <li><h5>Просрочка: <strong
                                                                        class="text-primary">{$collector->charge|round}</strong>
                                                            </h5></li>
                                                        <li><h5>Пени: <strong
                                                                        class="text-primary">{$collector->peni|round}</strong>
                                                            </h5></li>
                                                        <li><h5>Коммисия: <strong
                                                                        class="text-primary">{$collector->commision|round}</strong>
                                                            </h5></li>
                                                    </ul>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell bg-info text-white">
                                                    <strong>ФИО</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell bg-info text-white">
                                                    <strong>Статус</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell bg-info text-white">
                                                    <strong>День просрочки</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell bg-info text-white">
                                                    <strong>Сборы без ОД</strong>
                                                </td>
                                                <td style="width: 70px;" class="jsgrid-cell bg-info text-white">
                                                    <strong>Сборы</strong>
                                                </td>
                                            </tr>
                                            {foreach $collector->items as $item}
                                                <tr class="jsgrid-row {if $manager->role!='collector'}js-open-show{/if} js-dopinfo-{$collector->id}">
                                                    <td style="width: 70px;" class="jsgrid-cell">
                                                        <a href="client/{$item->user->id}" target="_blank">
                                                            <strong>
                                                                <small>
                                                                    {$item->user->lastname|escape}
                                                                    {$item->user->firstname|escape}
                                                                    {$item->user->patronymic|escape}
                                                                </small>
                                                            </strong>
                                                        </a>
                                                    </td>
                                                    <td style="width: 70px;" class="jsgrid-cell">
                                                        {if $item->closed}
                                                            <span class="label label-info">Закрыт</span>
                                                        {elseif $item->prolongation}
                                                            <span class="label label-success">Пролонгация</span>
                                                        {else}
                                                            <span class="label label-danger">Оплата</span>
                                                        {/if}
                                                    </td>
                                                    <td style="width: 70px;" class="jsgrid-cell">
                                                <span class="">
                                                    {if is_null($item->expired_days)}-
                                                    {else}{$item->expired_days}{/if}
                                                </span>
                                                    </td>
                                                    <td style="width: 70px;" class="jsgrid-cell">
                                                        <strong>{$item->percents_summ + $item->charge_summ + $item->peni_summ}</strong>
                                                    </td>
                                                    <td style="width: 70px;" class="jsgrid-cell">
                                                        <strong>{$item->percents_summ + $item->charge_summ + $item->peni_summ + $item->body_summ}</strong>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        {/if}
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>

                            <div class="jsgrid-grid-header ">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-footer-row">
                                        <th style="width: 120px;"
                                            class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            Итого:
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {$total->actions} / {$total->totals|round}
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {$total->total_contracts} / {$total->total_debt|round}
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {$total->closed|round}
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'body_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'body_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {$total->prolongation|round}
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'percents_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'percents_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {$total->total_brutto|round}
                                        </th>
                                        <th style="width: 70px;"
                                            class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'total_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'total_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {$total->total_netto|round}
                                        </th>
                                    </tr>
                                </table>

                            </div>

                            {if $total_pages_num>1}

                                {* Количество выводимых ссылок на страницы *}
                                {$visible_pages = 11}
                                {* По умолчанию начинаем вывод со страницы 1 *}
                                {$page_from = 1}

                                {* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
                                {if $current_page_num > floor($visible_pages/2)}
                                    {$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
                                {/if}

                                {* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
                                {if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
                                    {$page_from = max(1, $total_pages_num-$visible_pages-1)}
                                {/if}

                                {* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
                                {$page_to = min($page_from+$visible_pages, $total_pages_num-1)}
                                <div class="jsgrid-pager-container" style="">
                                    <div class="jsgrid-pager">
                                        Страницы:

                                        {if $current_page_num == 2}
                                            <span class="jsgrid-pager-nav-button "><a
                                                        href="{url page=null}">Пред.</a></span>
                                        {elseif $current_page_num > 2}
                                            <span class="jsgrid-pager-nav-button "><a
                                                        href="{url page=$current_page_num-1}">Пред.</a></span>
                                        {/if}

                                        <span class="jsgrid-pager-page {if $current_page_num==1}jsgrid-pager-current-page{/if}">
                                        {if $current_page_num==1}1{else}<a href="{url page=null}">1</a>{/if}
                                    </span>
                                        {section name=pages loop=$page_to start=$page_from}
                                            {* Номер текущей выводимой страницы *}
                                            {$p = $smarty.section.pages.index+1}
                                            {* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
                                            {if ($p == $page_from + 1 && $p != 2) || ($p == $page_to && $p != $total_pages_num-1)}
                                                <span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                            <a href="{url page=$p}">...</a>
                                        </span>
                                            {else}
                                                <span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                            {if $p==$current_page_num}{$p}{else}<a href="{url page=$p}">{$p}</a>{/if}
                                        </span>
                                            {/if}
                                        {/section}
                                        <span class="jsgrid-pager-page {if $current_page_num==$total_pages_num}jsgrid-pager-current-page{/if}">
                                        {if $current_page_num==$total_pages_num}{$total_pages_num}{else}<a
                                            href="{url page=$total_pages_num}">{$total_pages_num}</a>{/if}
                                    </span>

                                        {if $current_page_num<$total_pages_num}
                                            <span class="jsgrid-pager-nav-button"><a
                                                        href="{url page=$current_page_num+1}">След.</a></span>
                                        {/if}
                                        &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
                                    </div>
                                </div>
                            {/if}

                            <div class="jsgrid-load-shader"
                                 style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel"
                                 style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="">

                    <input type="hidden" name="order_id" value=""/>
                    <input type="hidden" name="contactperson_id" value=""/>
                    <input type="hidden" name="action" value=""/>

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
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