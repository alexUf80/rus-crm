{$meta_title='Отношение оплаченных договоров к выданным' scope=parent}

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
                    <span>Отношение оплаченных договоров к выданным</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Отношение оплаченных договоров к выданным</li>
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
                        <h4 class="card-title">Отчет Recovery rate</h4>
                        <form>
                            <div class="row">
                                {*}
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>

                                <div class="col-12 col-md-4 text-right">
                                    <a href="{url download='excel'}" class="btn btn-success ">
                                        <i class="fas fa-file-excel"></i> Скачать
                                    </a>
                                </div>
                                {*}
                            </div>

                        </form>

                        <div class="big-table" style="overflow: auto;position: relative;">
                            <table  class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                                <thead>
                                <tr>
                                    <th>Месяц выдачи</th>
                                    {foreach $operations_by_date as $key => $operation_by_date}
                                        {foreach $operation_by_date as $key_pay => $payment_by_date}
                                            {if $key_pay == "date_contract"}
                                                {continue}
                                            {/if}
                                            <th>{$payment_by_date['date_payment']}</th>
                                        {/foreach}
                                        {break}
                                    {/foreach}
                                </tr>
       
                                </thead>

                                <tbody id="table_content">
                                    {foreach $operations_by_date as $key => $operation_by_date}
                                        <tr>
                                            <td>{$operation_by_date[0]['date_contract']}</td>
                                            {$p2p = 0}
                                            {$pay = 0}
                                            {foreach $operation_by_date as $key_pay => $payment_by_date}
                                                {if $key_pay == "date_contract"}
                                                    {continue}
                                                {/if}

                                                {$p2p = $p2p + $payment_by_date['P2P']}
                                                {$pay = $pay + $payment_by_date['PAY']}
                                                {if $p2p > 0 && $pay > 0}
                                                    <td>{($pay / $p2p * 100)|number_format:2:".":""}%</td>
                                                {else}
                                                    <td></td>
                                                {/if}
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
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