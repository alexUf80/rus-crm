{$meta_title='Отчет по портфелю займов' scope=parent}

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
                    <span>Отчет по портфелю займов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Отчет по портфелю займов</li>
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
                        <h4 class="card-title">Отчет по портфелю займов за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                                {*}
                                {if $date_from || $date_to}
                                    <div class="col-12 col-md-4 text-right">
                                        <a href="{url download='excel'}" class="btn btn-success ">
                                            <i class="fas fa-file-excel"></i> Скачать
                                        </a>
                                    </div>
                                {/if}
                                {*}
                            </div>

                        </form>

                        <div class="big-table" style="overflow: auto;position: relative;">
                        {if $from}
                            <table class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                                <thead>
                                    <tr>
                                        <th>Наименование</th>
                                        <th>Значение</th>
                                    </tr>
                                </thead>
                                <tbody id="table_content">
                                    <tr>
                                        <td>Выдано</td>
                                        <td style="text-align: end;">{number_format($issued_all, 2, '.',' ')}</td>
                                    </tr>
                                    <tr>
                                        <td>Просрочка по бакетам</td>
                                        <td style="text-align: end;">{$count_delay_contracts}</td>
                                    </tr>
                                    <tr>
                                        <td>Закрытые договоры</td>
                                        <td style="text-align: end;">{$count_closed_contracts}</td>
                                    </tr>
                                    <tr>
                                        <td>Продленные договоры</td>
                                        <td style="text-align: end;">{$count_prolongation_contracts}</td>
                                    </tr>
                                    <tr>
                                        <td>Итого собрано (ОД + проценты)</td>
                                        <td style="text-align: end;">{number_format($pay_all, 2, '.',' ')}</td>
                                    </tr>
                                    <tr>
                                        <td>Остаток ОД</td>
                                        <td style="text-align: end;">{number_format($od, 2, '.',' ')}</td>
                                    </tr>
                                    <tr>
                                        <td>Начисленные и неоплаченные проценты</td>
                                        <td style="text-align: end;">{number_format($percents, 2, '.',' ')}</td>
                                    </tr>
                                    <tr>
                                        <td>Остаток ОД + проценты</td>
                                        <td style="text-align: end;">{number_format($od + $percents, 2, '.',' ')}</td>
                                    </tr>
                                    <tr>
                                        <td>Сумма дополнительных услуг</td>
                                        <td style="text-align: end;">{number_format($services_all, 2, '.',' ')}</td>
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