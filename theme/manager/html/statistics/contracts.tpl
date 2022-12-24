{$meta_title='Статистика выданных займов' scope=parent}

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
{/capture}

{capture name='page_styles'}

    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
    .table td {
//        text-align:center!important;
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
                    <span>Статистика выданных займов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Выданные займы</li>
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
                        <h4 class="card-title">Выданные займы за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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

                        {if $from}
                        <table class="table table-hover">

                            <tr>
                                <th>Дата</th>
                                <th>Договор</th>
                                <th>Дата возврата</th>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Сумма</th>
                                <th>ПК/НК</th>
                                <th>Менеджер</th>
                                <th>Статус</th>
                                <th>Дата возврата</th>
                                <th>ПДН</th>
                                <th>Дней займа</th>
                            </tr>

                            {foreach $contracts as $contract}
                            <tr>
                                <td>{$contract->date|date}</td>
                                <td>
                                    <a target="_blank" href="order/{$contract->order_id}">{$contract->number}</a>
                                </td>
                                <td>
                                    {$contract->return_date|date}
                                </td>
                                <td>
                                    <a href="client/{$contract->user_id}" target="_blank">
                                        {$contract->lastname|escape}
                                        {$contract->firstname|escape}
                                        {$contract->patronymic|escape}
                                        {$contract->birth|escape}
                                    </a>
                                </td>
                                <td>{$contract->phone_mobile}</td>
                                <td><small>{$contract->email}</small></td>
                                <td>{$contract->amount*1}</td>
                                <td>
                                    {if $contract->client_status == 'pk'}ПК{/if}
                                    {if $contract->client_status == 'nk'}НК{/if}
                                    {if $contract->client_status == 'crm'}ПК CRM{/if}
                                    {if $contract->client_status == 'rep'}Повтор{/if}
                                </td>
                                <td>
                                    {$managers[$contract->manager_id]->name|escape}
                                </td>
                                <td>

                                    {if $contract->collection_status}
                                    {if $contract->sold}
                                        ЮК
                                    {else}
                                        МКК
                                    {/if}
                                        {$collection_statuses[$contract->collection_status]}
                                    {else}
                                        {$statuses[$contract->status]}
                                    {/if}
                                </td>
                                <td>
                                    {$contract->return_date}
                                </td>
                                <td>
                                    {$contract->pdn}
                                </td>
                                <td>
                                    {$contract->period}
                                </td>
                            </tr>
                            {/foreach}

                        </table>
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