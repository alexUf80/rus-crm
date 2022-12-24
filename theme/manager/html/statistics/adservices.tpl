{$meta_title='Доп услуги' scope=parent}

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
        .table th td {
            text-align: center !important;
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
                    <span>Доп услуги</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Доп услуги</li>
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
                            <table class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                                <thead>
                                <tr>
                                    <th>Дата продажи/Договор займа</th>
                                    <th>ID клиента</th>
                                    <th>Номер полиса</th>
                                    <th>Продукт</th>
                                    <th>ID операции</th>
                                    <th>УИД договора</th>
                                    <th>ФИО, дата рождения</th>
                                    <th>Номер телефона</th>
                                    <th>Пол</th>
                                    <th>Паспорт, серия номер</th>
                                    <th>Адрес</th>
                                    <th>Дата начала / завершения ответственности</th>
                                    <th>Страховая сумма</th>
                                    <th>Сумма оплаты/Страховая премия</th>
                                </tr>
                                </thead>

                                <tbody id="table_content">
                                {foreach $ad_services as $ad_service}
                                    <tr>
                                        <td>{$ad_service->created} <p>{$ad_service->contract_id}</p></td>
                                        <td>{$ad_service->user_id}</td>
                                        <td>{$ad_service->number}</td>
                                        {if $ad_service->type == 'INSURANCE'}
                                            <td>Страхование от НС</td>
                                        {else}
                                            <td>Страхование от БК</td>
                                        {/if}
                                        <td>{$ad_service->id}</td>
                                        <td>{$ad_service->uid}</td>
                                        <td>{$ad_service->lastname} {$ad_service->firstname} {$ad_service->patronymic}
                                            <p>{$ad_service->birth}</p></td>
                                        <td>{$ad_service->phone_mobile}</td>
                                        <td>{$gender[$ad_service->gender]}</td>
                                        <td>{$ad_service->passport_serial}</td>
                                        <td>{$ad_service->regAddr}</td>
                                        {if $ad_service->start_date}
                                            <td>{$ad_service->start_date} / {$ad_service->end_date} </td>
                                        {else}
                                            <td></td>
                                        {/if}
                                        {if ($ad_service->number)}
                                            <td>{$ad_service->amount_contract * 3} руб</td>
                                        {else}
                                            <td></td>
                                        {/if}
                                        <td>{$ad_service->amount_insurance} руб</td>
                                    </tr>
                                {/foreach}
                                {foreach $card_binding as $card}
                                    <tr>
                                        <td>{$card->operation_date}</td>
                                        <td>{$card->user_id}</td>
                                        <td>-</td>
                                        <td>{$card->description}</td>
                                        <td>{$card->id}</td>
                                        <td>-</td>
                                        <td>{$card->lastname} {$card->firstname} {$card->patronymic}
                                            <p>{$card->birth}</p></td>
                                        <td>{$card->phone_mobile}
                                        <td>
                                        <td>{$gender[$card->gender]}</td>
                                        <td>{$card->passport_serial}</td>
                                        <td>{$card->Regindex} {if $card->Regcity}{$card->Regcity}{else}{$card->Reglocality}{/if}{$card->Regstreet_shorttype} {$card->Regstreet}
                                            {$card->Reghousing} {$card->Regroom}</td>
                                        <td>-</td>
                                        <td>1 руб</td>
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