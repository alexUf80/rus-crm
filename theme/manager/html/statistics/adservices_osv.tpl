{$meta_title='Доп услуги в виде ОСВ' scope=parent}

{capture name='page_scripts'}
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default: ''
            });
        })
    </script>
{/capture}

{capture name='page_styles'}
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        .table td, .table th{
            text-align: center;
            border: 1px solid black !important;
        }

        .table th.table_underline{
            border-bottom: 2px solid black !important;
        }

        .table_underline{
            border-bottom: 2px solid black !important;
        }
        tr:hover{
            background: white !important;
        }
        tr:nth-child(4n-1):hover{
            background: #f4f4f4 !important;
        }
        tr:nth-child(4n):hover{
            background: #f4f4f4 !important;
        }
        tr:nth-child(4n-1) {
            background: #f4f4f4 !important;
        }
        tr:nth-child(4n) {
            background: #f4f4f4 !important;
        }
        th {
            background: #dedede !important;
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
                    <span>Доп услуги в виде ОСВ</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Доп услуги в виде ОСВ</li>
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
                        <h4 class="card-title">Отчет по доп услугам за
                            период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange"
                                               value="{if $from && $to}{$from}-{$to}{/if}">
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
                            <table  class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                                <thead>
                                <tr>
                                    <th width=25%>ФИО клиента</th>
                                    <th colspan="2" width=25%>Сальдо на начало периода</th>
                                    <th colspan="2" width=25%>Обороты за период</th>
                                    <th colspan="2" width=25%>Сальдо на конец  периода</th>
            
                                </tr>
                                <tr>
                                    <th class="table_underline">Идентификатор услуги</th>
                                    <th class="table_underline">Дебет</th>
                                    <th class="table_underline">Кредит</th>
                                    <th class="table_underline">Дебет</th>
                                    <th class="table_underline">Кредит</th>
                                    <th class="table_underline">Дебет</th>
                                    <th class="table_underline">Кредит</th>
    
                                </tr>
                                </thead>

                                <tbody id="table_content">
                                {foreach $ad_services as $ad_service}
                                    <tr>
                                        <td>{$ad_service->lastname} {$ad_service->firstname} {$ad_service->patronymic}</td>
                                        <td rowspan="2" class="table_underline"></td>
                                        <td rowspan="2" class="table_underline"></td>
                                        <td rowspan="2" class="table_underline">{$ad_service->amount_insurance}</td>
                                        <td rowspan="2" class="table_underline"></td>
                                        <td rowspan="2" class="table_underline"></td>
                                        <td rowspan="2" class="table_underline"></td>
                                    </tr>
                                    <tr class="table_underline">
                                        <td>{$ad_service->service_number}</td>
                                        {if $ad_service->type == 'INSURANCE'}
                                            <td>{*}Страхование от НС - 60332810000000000005 - НС{*}</td>
                                        {elseif $ad_service->type == 'BUD_V_KURSE'}
                                            <td>{*}СМС-информирование - 60332810000000000006 - СМС{*}</td>
                                        {elseif $ad_service->type == 'REJECT_REASON'}
                                            <td>{*}Причина отказа -  60332810000000000007 - ОТКАЗ{*}</td>
                                        {else}
                                            <td>{*}Страхование от БК -  60332810000000000008 - БК{*}</td>
                                        {/if}
                                        
                                    </tr>
                                {/foreach}
                                
                                </tbody>
                            </table>
                        </div>
                        {else}
                        <div class="alert alert-info">
                            <h4>Укажите даты для формирования отчета</h4>
                        </div>
                        {/if}

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