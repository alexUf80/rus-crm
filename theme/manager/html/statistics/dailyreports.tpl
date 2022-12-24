{$meta_title='Отчет по дням' scope=parent}

{capture name='page_scripts'}

    <script src="theme/manager/assets/plugins/moment/moment.js"></script>

    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default:''
            });
        })
    </script>

    <script>
        function ReportPaymentsApp()
        {
            var app = this;

            app.init = function(){

                app.send_operation();

                app.init_search();
            };

            app.send_operation = function(){

                $('.js-send-operation').click(function(e){
                    e.preventDefault();

                    var operation_id = $(this).data('operation');

                    $.ajax({
                        data: {
                            operation_id: operation_id
                        },
                        success: function(resp){
                            if (!!resp.error)
                                Swal.fire('Ошибка', resp.error, 'error');
                            else
                                Swal.fire('Успешно', resp.success, 'success');
                        }
                    });
                });
            };

            app.load = function(_url, loading){
                $.ajax({
                    url: _url,
                    beforeSend: function(){
                        if (loading)
                        {
                            $('.jsgrid-load-shader').show();
                            $('.jsgrid-load-panel').show();
                        }
                    },
                    success: function(resp){


                        if (loading)
                        {
                            $('html, body').animate({
                                scrollTop: $("#basicgrid").offset().top-80
                            }, 1000);

                            $('.jsgrid-load-shader').hide();
                            $('.jsgrid-load-panel').hide();
                        }

                    }
                })
            };

            app.init_search = function(){
                $(document).on('change', '.js-search-block input', function(){

                    var _searches = {};
                    $('.js-search-block input').each(function(){
                        if ($(this).val() != '')
                        {
                            _searches[$(this).attr('name')] = $(this).val();
                        }
                    });
                    var _request = {

                    };
                    var _query = Object.keys(_request).map(
                        k => encodeURIComponent(k) + '=' + encodeURIComponent(_request[k])
                    ).join('&');

                    _request.search = _searches;
                    if (!$.isEmptyObject(_searches))
                    {
                        _query_searches = '';
                        for (key in _searches) {
                            _query_searches += '&search['+key+']='+_searches[key];
                        }
                        _query += _query_searches;
                    }

                    $.ajax({
                        data: _request,
                        beforeSend: function(){
                        },
                        success: function(resp){
                            var _table = $(resp).find('#table_content').html();
                            console.log(_table)
                            $('#table_content').html(_table)
                        }
                    })
                });
            };

            ;(function(){
            app.init();
        })();
        };
        $(function(){
            new ReportPaymentsApp();
        });
    </script>
{/capture}

{capture name='page_styles'}

    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
        .table th td {
            text-align:center!important;
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
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчет по дням</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Отчет по дням</li>
                </ol>
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
                        <h4 class="card-title">Отчет по дням за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $date_from || $date_to}
                                    <div class="col-12 col-md-4 text-right">
                                        <a href="{url download='excel'}" class="btn btn-success ">
                                            <i class="fas fa-file-excel"></i> Скачать
                                        </a>
                                    </div>
                                {/if}
                            </div>

                        </form>

                        <div class="big-table" style="overflow: auto;position: relative;">
                        {if $from}
                            <table class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                                <thead>
                                <tr>
                                    <th>Отчеты</th>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                    <th>{$date}</th>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <th>{$date}</th>
                                        {/if}
                                    {/foreach}
                                </tr>
                                </thead>
                                <tbody id="table_content">
                                <tr>
                                    <td>Выдано новых/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_new_orders']} шт/ {$operations['sum_new_orders']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_new_orders']} шт/ {$operations['sum_new_orders']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Выдано повторных/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_repeat_orders']} шт / {$operations['sum_repeat_orders']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_repeat_orders']} шт / {$operations['sum_repeat_orders']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Погашено</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_closed_contracts']} шт</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_closed_contracts']} шт</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Частично погашено</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                            <td>{$operations['count_partial_release']} шт</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_partial_release']} шт</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Продлено</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_prolongations']} шт</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_prolongations']} шт</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Получено ОД</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['loan_body_summ']|floatval|number_format:2:',':''} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['loan_body_summ']|floatval|number_format:2:',':''} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Получено %%</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['loan_charges_summ']|floatval|number_format:2:',':''} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['loan_charges_summ']|floatval|number_format:2:',':''} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Всего страховок/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_insurance']} шт/ {$operations['sum_insurance']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_insurance']} шт/ {$operations['sum_insurance']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Страховки при выдаче/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_insurance_inssuance']} шт/ {$operations['sum_insurance_inssuance']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_insurance_inssuance']} шт/ {$operations['sum_insurance_inssuance']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Страховки при продлении/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_insurance_prolongation']} шт / {$operations['sum_insurance_prolongation']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_insurance_prolongation']} шт / {$operations['sum_insurance_prolongation']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Страховки при закрытии/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        {if !empty($operations['count_insurance_close'])}
                                            <td>{$operations['count_insurance_close']} шт / {$operations['sum_insurance_close']} руб</td>
                                        {else}
                                            <td>-</td>
                                        {/if}
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            {if !empty($operations['count_insurance_close'])}
                                                <td>{$operations['count_insurance_close']} шт / {$operations['sum_insurance_close']} руб</td>
                                            {else}
                                                <td>-</td>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>"Будь в курсе"/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_sms_services']} шт / {$operations['sum_sms_services']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_sms_services']} шт / {$operations['sum_sms_services']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>"Узнай причину отказа"/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_reject_reason']} шт / {$operations['sum_reject_reason']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_reject_reason']} шт / {$operations['sum_reject_reason']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>"Привязка карты"/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_card_binding']} шт / {$operations['sum_card_binding']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_card_binding']} шт / {$operations['sum_card_binding']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Итого доп продуктов/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        <td>{$operations['count_insurance'] +
                                            $operations['count_sms_services'] +
                                            $operations['count_reject_reason'] +
                                            $operations['count_card_binding']} шт /
                                            {$operations['sum_insurance'] +
                                            $operations['sum_sms_services'] +
                                            $operations['sum_reject_reason'] +
                                            $operations['sum_card_binding']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_insurance'] +
                                                $operations['count_sms_services'] +
                                                $operations['count_reject_reason'] +
                                                $operations['count_card_binding']} шт /
                                                {$operations['sum_insurance'] +
                                                $operations['sum_sms_services'] +
                                                $operations['sum_reject_reason'] +
                                                $operations['sum_card_binding']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Отменено доп продуктов/Сумма</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                        {if !empty($operations['count_return'])}
                                            <td>{$operations['count_return']} шт / {$operations['sum_return']}</td>
                                        {else}
                                            <td>-</td>
                                        {/if}
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            {if !empty($operations['count_return'])}
                                                <td>{$operations['count_return']} шт / {$operations['sum_return']}</td>
                                            {else}
                                                <td>-</td>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Оплачено на р/сч ОД</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                            <td>{$operations['sum_cor_body']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['sum_cor_body']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Оплачено на р/сч %%</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                            <td>{$operations['sum_cor_percents']} руб</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['sum_cor_percents']} руб</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Продления по р/сч</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                            <td>{$operations['count_cor_prolongations']} шт</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_cor_prolongations']} шт</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                <tr>
                                    <td>Погашения по р/сч</td>
                                    {foreach $final_array as $date => $operations}
                                        {if $date != 'Итого'}
                                            <td>{$operations['count_cor_closed']} шт</td>
                                        {/if}
                                        {if $date == 'Итого' && count($final_array) > 2}
                                            <td>{$operations['count_cor_closed']} шт</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                                </tbody>
                            </table>
                        {else}
                            <div class="alert alert-info">
                                <h4>Укажите даты для формирования отчета</h4>
                            </div>
                        {/if}
                    </div>
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
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