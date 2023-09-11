{$meta_title='Отчет по просрочке' scope=parent}

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
        window.onload = function() {
            if(!$("#date").val()){
                var date = new Date();
                var fullDate = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
                $("#date").val(fullDate);
            }
        };
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
        .right {
            text-align: end;
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
                    <span>Отчет по просрочке</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Отчет по просрочке</li>
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
                        <h4 class="card-title">Отчет по просрочке</h4>
                        <form>
                            <div class="row">
                                <div class="col-2 col-md-2">
                                    <div class="input-group mb-3">
                                        <input type="date" name="date" id="date" class="form-control"period value="{if $date}{$date}{/if}">
                                    </div>
                                </div>
                                <div class="col-2 col-md-2">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $date}
                                    <div class="col-8 col-md-8 text-right">
                                        <a href="{url download='excel'}" class="btn btn-success ">
                                            <i class="fas fa-file-excel"></i> Скачать
                                        </a>
                                    </div>
                                {/if}
                            </div>

                        </form>

                        <div class="big-table" style="overflow: auto;position: relative;">
                        {if $date}
                            <table class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                                <thead>
                                    <tr>
                                        <th>№ контракта</th>
                                        <th>ФИО</th>
                                        <th>Cумма долга</th>
                                        <th>Дней просрочки</th>
                                        <th>Коллектор</th>
                                        <th>Тег</th>
                                        <th>Риск-статус</th>
                                    </tr>
                                </thead>
                                <tbody id="table_content">
                                    {foreach $contracts as $contract}
                                        <tr>
                                            <td><a href="collector_contract/{$contract->id}">{$contract->number}</a></td>
                                            <td>{$contract->lastname} {$contract->firstname} {$contract->patronymic}</td>
                                            <td>{$contract->loan_body_summ+$contract->loan_percents_summ+$contract->loan_peni_summ}</td>
                                            <td>{$contract->expired_days}</td>

                                            {foreach $managers as $m}
                                                {$mn = ''}
                                                {if ($m->id == $contract->collection_manager_id)}
                                                    {$mn = $m->name}
                                                {/if}
                                            {/foreach}
                                            <td>{$mn}</td>
                                            <td>
                                                {if !$contract->order->contact_status}
                                                    <span class="label label-warning">Нет данных</span>
                                                {else}
                                                    <span class="label"
                                                            style="background:{$collector_tags[$contract->order->contact_status]->color}">{$collector_tags[$contract->order->contact_status]->name|escape}</span>
                                                {/if}
                                        </td>
                                            
                                            <td style="display: flex; flex-direction: column; align-items: flex-start">
                                            {if !empty($contract->risk)}
                                                {foreach $contract->risk as $operation => $value}
                                                    {foreach $risk_op as $risk => $val}
                                                        {if $operation == $risk && $value == 1}
                                                            <span class="label label-danger" style="margin:2px">{$val}</span>
                                                        {/if}
                                                    {/foreach}
                                                {/foreach}
                                            {/if}
                                            </td>


                                        </tr>
                                    {/foreach}    
                                </tbody>
                            </table>
                        {else}
                            <div class="alert alert-info">
                                <h4>Укажите дату для формирования отчета</h4>
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