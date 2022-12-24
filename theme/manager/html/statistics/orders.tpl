{$meta_title='' scope=parent}

{capture name='page_scripts'}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function () {

            moment.locale('ru');

            $('.daterange').daterangepicker({
                locale: {
                    format: 'DD.MM.YYYY',
                    "customRangeLabel": "Произвольно",
                },
                default: '',
                ranges: {
                    'Cегодня': [moment(), moment()],
                    'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
                    'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                    'Текущая неделя': [moment().startOf('week'), moment()],
                    'Прошлая неделя': [moment().startOf('week').subtract(7, 'days'), moment().startOf('week').subtract(1, 'days')],
                    'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                    'Прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Текущий год': [moment().startOf('year'), moment()]
                }
            });
        });
    </script>
{/capture}

{capture name='page_styles'}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <style>
        .table th td {
            text-align: center !important;
        }

        table {
            font-size: 11px !important;
        }

        label {
            font-size: 12px !important;
            margin-bottom: 0 !important;
        }

        .btn, button, select, input {
            font-size: 12px !important;
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-file-chart"></i>Отчет по заявкам (Риски)</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Отчет по заявкам (Риски)</li>
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
                    <form id="data">
                        <input type="hidden" name="to-do" value="report">
                        <div class="card-body">
                            <h4 class="card-title">Отчет по заявкам (Риски) {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                            <div class="row">
                                <div class="input-group" style="width: 20%">
                                    <div style="margin-left: 12px; width: 100%" id="calendar">
                                        <input type="text" name="daterange" style="text-align: center; width: 100%"
                                               class="form-control daterange"
                                               value="{if $from && $to}{$from}-{$to}{/if}">
                                    </div>
                                </div>
                                <div class="col-2 col-md-1">
                                    <button type="submit" class="btn btn-info">Применить</button>
                                </div>
                                {if $from || $to}
                                    <div class="col-1 col-md-2">
                                        <a href="{url download='excel'}" class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Скачать
                                        </a>
                                    </div>
                                {/if}
                            </div>
                            <br/>
                    </form>
                    <div class="alert alert-info">
                        <h4>Укажите параметры для отчета</h4>
                    </div>
                </div>
            </div>
            <!-- Column -->
        </div>
    </div>
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