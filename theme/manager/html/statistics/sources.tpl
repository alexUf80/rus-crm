{$meta_title='Источники' scope=parent}

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
                    "applyLabel": "Применить",
                    "cancelLabel": "Отменить",
                },
                default: '',
                ranges: {
                    'Cегодня': [moment(), moment()],
                    'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
                    'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                    'Текущая неделя': [moment().startOf('week'), moment()],
                    'Прошлая неделя': [moment().startOf('week').subtract(7, 'days'), moment().startOf('week').subtract(1, 'days')],
                    'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                    'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                    'Прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Текущий год': [moment().startOf('year'), moment()]
                }
            });

            let checkbox = {{json_encode($thead)}};

            console.log(checkbox);

            if (checkbox) {
                $('input').prop('checked', false);
            }

            for (let element in checkbox) {

                $('input[name="'+element+'"]').prop('checked', true);
            }

            $('input[name="utm_source"]').prop('checked', true);

            let group = {{json_encode($group_by)}};

            if (group) {
                $('select[name="group_by"] option[value=' + group + ']').attr('selected', 'selected');
            }

            let filtres = {{json_encode($filtres)}};

            if (filtres) {
                for (let filter in filtres) {
                    console.log(filtres);
                    $('input[name="' + filter + '"]').prop('checked', true);
                    $('input[name="' + filter + '_val"]').val(filtres[filter]);
                }
            }

            $('#data').on('submit', function (e) {

                $.ajax({
                    data: $(this).serialize(),
                    success: function (ok) {

                    }
                });
            });

            $(".drop").on("click", function (e) {
                e.stopPropagation();
            });

            $('.closed').on("click", function () {
                $('.drop').removeClass('show');
                $('.metrix').removeClass('show');

            });

            $('input[name="prop_all_checkbox"]').on('click', function () {
                $('.metrix').find('input[type="checkbox"]').each(function (e) {
                    $(this).prop('checked', true)
                });

                $('input[name="unprop_all_checkbox"]').prop('checked', false);
            });

            $('input[name="unprop_all_checkbox"]').on('click', function () {
                $('.metrix').find('input[type="checkbox"]').each(function (e) {
                    $(this).prop('checked', false)
                });

                $(this).prop('checked', true);
            });
        })
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

        div #listener {
            display: flex;
        }

        label {
            font-size: 12px !important;
            margin-bottom: 0 !important;
        }

        button, select, input {
            font-size: 12px !important;
        }

        select {
            font: inherit;
            letter-spacing: inherit;
            word-spacing: inherit;
            -moz-appearance: none;
            -webkit-appearance: none;
            appearance: none;
            text-align: left;
        }

        .dropdown-toggle::after {
            content: none;
        }

        select::-ms-expand {
            display: none;
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
                    <span>Источники</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Источники</li>
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
                            <h4 class="card-title">Отчет по
                                источникам {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                            <div class="row">
                                <div class="col-1 col-md-2">
                                    <select class="form-control" name="date_group_by" style="text-align: center">
                                        <option value="created" {if $date_group_by && $date_group_by == 'created'}selected{/if}>Дата создания</option>
                                        <option value="issuance" {if $date_group_by && $date_group_by == 'issuance'}selected{/if}>Дата выдачи</option>
                                    </select>
                                </div>
                                <div class="col-2 col-md-2 metrix">
                                    <button type="button" class="form-control dropdown-toggle"
                                            data-toggle="dropdown" data-bs-auto-close="false">
                                        Метрики
                                    </button>
                                    <div class="dropdown-menu drop" data-bs-auto-close="false">
                                        <div id="listener" style="margin-left: 5px">
                                            <ul class="list-unstyled m-2" style="width: 150px">
                                                <li class="metrix">
                                                    <div>
                                                        <input type="checkbox" name="utm_source" value="1" checked/>
                                                        <label>Источник</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_medium" value="1"/>
                                                        <label>Канал</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_campaign" value="1"/>
                                                        <label>Кампания</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_term" value="1"/>
                                                        <label>Таргетинг</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_content" value="1"/>
                                                        <label>Контент</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="visits" value="1" checked/>
                                                        <label>Визиты</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="list-unstyled m-2" style="width: 150px">
                                                <li class="metrix">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="CR" value="1" checked/>
                                                        <label>CR %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="all_orders" value="1" checked/>
                                                        <label>Заявки</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="orders_nk" value="1"/>
                                                        <label>Заявки НК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="orders_pk" value="1"/>
                                                        <label>Заявки ПК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="orders_bk" value="1"/>
                                                        <label>Заявки ПБ</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="accept_all" value="1" checked/>
                                                        <label>Выдано</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="list-unstyled m-2" style="width: 150px">
                                                <li class="metrix">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="accept_nk" value="1"/>
                                                        <label>Выдано НК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="accept_pk" value="1"/>
                                                        <label>Выдано ПК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="accept_bk" value="1" checked/>
                                                        <label>Выдано ПБ</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="ar_all" value="1" checked/>
                                                        <label>AR %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="ar_nk" value="1"/>
                                                        <label>AR НК %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="ar_pk" value="1"/>
                                                        <label>AR ПК %</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="list-unstyled m-2" style="width: 150px">
                                                <li class="metrix">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="ar_bk" value="1" checked/>
                                                        <label>AR ПБ %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_all" value="1" checked/>
                                                        <label>Отказы</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_all_prc" value="1"
                                                               checked/>
                                                        <label>Отказы %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_nk" value="1"/>
                                                        <label>Отказы НК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_nk_prc" value="1"/>
                                                        <label>Отказы НК %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_pk" value="1"/>
                                                        <label>Отказы ПК</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="list-unstyled m-2" style="width: 150px">
                                                <li class="metrix">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_pk_prc" value="1"/>
                                                        <label>Отказы ПК %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_bk" value="1"/>
                                                        <label>Отказы ПБ</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="reject_bk_prc" value="1"/>
                                                        <label>Отказы ПБ %</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="check_all_summ" value="1"/>
                                                        <label>Сумма</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="check_nk_summ" value="1"/>
                                                        <label>Сумма НК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="check_pk_summ" value="1"/>
                                                        <label>Сумма ПК</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="list-unstyled m-2" style="width: 150px">
                                                <li class="metrix">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="check_srch" value="1"/>
                                                        <label>СРЧ</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="check_srch_nk" value="1"/>
                                                        <label>СРЧ НК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="check_srch_pk" value="1"/>
                                                        <label>СРЧ ПК</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="orders_on_check" value="1"/>
                                                        <label>Проверка</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="prop_all_checkbox" value="1"/>
                                                        <label>Выбрать все</label>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="unprop_all_checkbox" value="1"/>
                                                        <label>Убрать все</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="list-unstyled" style="width: 50px">
                                                <li class="metrix">
                                                    <button type="button" class="closed btn btn-dark">×</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group" style="width: 20%">
                                    <div style="margin-left: 12px; width: 100%" id="calendar">
                                        <input type="text" name="daterange" style="text-align: center; width: 100%"
                                               class="form-control daterange"
                                               value="{if $from && $to}{$from}-{$to}{/if}">
                                    </div>
                                </div>
                                <div class="col-1 col-md-1">
                                    <select class="form-control" name="group_by" style="text-align: center">
                                        <option value="day">День</option>
                                        <option value="week">Неделя</option>
                                        <option value="month">Месяц</option>
                                        <option value="year">Год</option>
                                    </select>
                                </div>
                                <div class="col-2 col-md-2">
                                    <button type="button" class="form-control dropdown-toggle integrations"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Фильтр
                                    </button>
                                    <div class="dropdown-menu drop"
                                         id="dropdown_managers">
                                        <div id="listener">
                                            <ul class="list-unstyled m-2" style="width: 200px">
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_source_filter"
                                                               id="utm_source_filter"/>
                                                        <label for="utm_source_filter">Источник</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_source_filter_val">
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_medium_filter"
                                                               id="utm_medium_filter"/>
                                                        <label for="utm_medium_filter">Канал</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_medium_filter_val">
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_campaign_filter"
                                                               id="utm_campaign_filter"/>
                                                        <label for="utm_campaign_filter">Кампания</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_campaign_filter_val">
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_term_filter"
                                                               id="utm_term_filter"/>
                                                        <label for="utm_term_filter">Таргетинг</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_term_filter_val">
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_content_filter"
                                                               id="utm_content_filter"/>
                                                        <label for="utm_content_filter">Контент</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_content_filter_val">
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 col-md-2">
                                    <button type="submit" class="btn btn-info">Применить</button>
                                </div>
                            </div>
                            <br/>

                    </form>

                    <div class="big-table" style="overflow: auto; position: relative;">
                        {if $from}
                        <table class="table table-hover" id="basicgrid" style="display: inline-block; vertical-align: top; max-width: 100%;
                            overflow-x: auto; white-space: nowrap;-webkit-overflow-scrolling: touch;">
                            <thead>
                            <tr>
                                <th>Дата</th>
                                {foreach $thead as $head}
                                    <th>{$head}</th>
                                {/foreach}
                            </tr>
                            </thead>
                            <tbody id="table_content">
                            {foreach $results as $date => $source}
                                {foreach $source as $source_name => $value}
                                    <tr>
                                        <td>{$date}</td>
                                        {foreach $value as $val}
                                            <td>{$val}</td>
                                        {/foreach}
                                    </tr>
                                {/foreach}
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    {else}
                    <div class="alert alert-info">
                        <h4>Укажите параметры для отчета</h4>
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