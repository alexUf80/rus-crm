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

            $('#data').on('submit', function (e) {

                $.ajax({
                    data: $(this).serialize(),
                    success: function (ok) {

                    }
                });
            });

            let checkbox = {{json_encode($thead)}};

            if (checkbox) {

                $('input[class="metric"]').attr('checked', false);

                for (let element in checkbox) {
                    $('input[name="' + element + '"]').attr('checked', true);
                }
            }

            $('.page_count').on('change', function () {

                let count_items = $(this).val();

                let form = $('#data').serialize() + '&items_per_page='+count_items;

                $.ajax({
                    data: form,
                    success: function () {
                        location.replace(form);
                    }
                })
            })

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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-file-chart"></i> Конверсии</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Конверсии</li>
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
                                конверсиям {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                            <div class="row">
                                <div class="col-1 col-md-2">
                                    <div class="input-group mb-3">
                                        <select name="date_filter" class="form-control">
                                            <option value="1" {if $date_select == 1}selected{/if}>Дата выдачи</option>
                                            <option value="2" {if $date_select == 2}selected{/if}>Дата создания</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2 col-md-3">
                                    <div class="input-group mb-4">
                                        <div style="width: 300px!important; margin-left: 12px" id="calendar">
                                            <input type="text" name="daterange" class="form-control daterange"
                                                   value="{if $from && $to}{$from}-{$to}{/if}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-1 col-md-2">
                                    <button type="button" class="form-control dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Метрики
                                    </button>
                                    <div class="js-risk-op-check dropdown-menu" id="dropdown_managers">
                                        <ul class="list-unstyled m-2">
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="id" value="1" checked/>
                                                    <label>Заявка</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="utm_source" value="1"
                                                           checked/>
                                                    <label>Источник</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="utm_medium" value="1"
                                                           checked/>
                                                    <label>Канал</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="utm_campaign"
                                                           value="1"/>
                                                    <label>Кампания</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="utm_term" value="1"/>
                                                    <label>Таргетинг</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="click_hash" value="1"/>
                                                    <label>Контент</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="client_status" value="1"
                                                           checked/>
                                                    <label>Статус клиента</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="status" value="1"
                                                           checked/>
                                                    <label>Статус заявки</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" class="metric" name="leadcraft_postback_type"
                                                           value="1"
                                                           checked/>
                                                    <label>Постбэк</label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
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
                                                               id="utm_source_filter"
                                                               {if !empty($filtres) && isset($filtres['utm_source_filter'])}checked{/if}>
                                                        <label for="utm_source_filter">Источник</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_source_filter_val"
                                                               {if !empty($filtres) && isset($filtres['utm_source_filter'])}value="{$filtres['utm_source_filter']}"{/if}>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_medium_filter"
                                                               id="utm_medium_filter"
                                                               {if !empty($filtres) && isset($filtres['utm_medium_filter'])}checked{/if}>
                                                        <label for="utm_medium_filter">Канал</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_medium_filter_val"
                                                               {if !empty($filtres) && isset($filtres['utm_medium_filter'])}value="{$filtres['utm_medium_filter']}"{/if}>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_campaign_filter"
                                                               id="utm_campaign_filter"
                                                               {if !empty($filtres) && isset($filtres['utm_campaign_filter'])}checked{/if}>
                                                        <label for="utm_campaign_filter">Кампания</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_campaign_filter_val"
                                                               {if !empty($filtres) && isset($filtres['utm_campaign_filter'])}value="{$filtres['utm_campaign_filter']}"{/if}>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_term_filter"
                                                               id="utm_term_filter"
                                                               {if !empty($filtres) && isset($filtres['utm_term_filter'])}checked{/if}>
                                                        <label for="utm_term_filter">Таргетинг</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_term_filter_val"
                                                               {if !empty($filtres) && isset($filtres['utm_term_filter'])}value="{$filtres['utm_term_filter']}"{/if}>
                                                    </div>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" name="utm_content_filter"
                                                               id="utm_content_filter"
                                                               {if !empty($filtres) && isset($filtres['utm_content_filter'])}checked{/if}>
                                                        <label for="utm_content_filter">Контент</label><br>
                                                        <input type="text" class="form-control"
                                                               name="utm_content_filter_val"
                                                               {if !empty($filtres) && isset($filtres['utm_content_filter'])}value="{$filtres['utm_content_filter']}"{/if}>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
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
                    <div class="big-table" style="overflow: auto;position: relative;">
                        {if !empty($orders)}
                        <table class="table table-hover" id="basicgrid" style="display: inline-block;vertical-align: top;max-width: 100%;
                            overflow-x: auto;white-space: nowrap;-webkit-overflow-scrolling: touch;">
                            <thead>
                            <tr>
                                <th>Дата</th>
                                {foreach $thead as $key => $head}
                                    <th>{$head}</th>
                                {/foreach}
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $orders as $order}
                                <tr>
                                    {foreach $order as $key => $val}
                                        {if $key == 'status' || $key == 'os.status'}
                                            {foreach $orders_statuses as $k => $status}
                                                {if $k == $val}
                                                    <td>{$status}</td>
                                                {/if}
                                            {/foreach}
                                        {else}
                                            <td>{$val}</td>
                                        {/if}
                                    {/foreach}
                                </tr>
                            {/foreach}
                            </tbody>
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

                        <div class="jsgrid-pager-container float-left" style="">
                            <div class="jsgrid-pager">
                                Страницы:

                                {if $current_page_num == 2}
                                    <span class="jsgrid-pager-nav-button "><a href="{url page=null}">Пред.</a></span>
                                {elseif $current_page_num > 2}
                                    <span class="jsgrid-pager-nav-button "><a href="{url page=$current_page_num-1}">Пред.</a></span>
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
                                        {if $current_page_num==$total_pages_num}{$total_pages_num}{else}<a href="{url page=$total_pages_num}">{$total_pages_num}</a>{/if}
                                    </span>

                                {if $current_page_num<$total_pages_num}
                                    <span class="jsgrid-pager-nav-button"><a href="{url page=$current_page_num+1}">След.</a></span>
                                {/if}

                                &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
                            </div>
                        </div>
                    {/if}
                    <div class="float-right pt-1">
                        <select onchange="if (this.value) window.location.href = this.value" class="form-control form-control-sm page_count" name="page-count">
                            <option value="{url page_count=25}" {if $page_count==25}selected=""{/if}>Показывать 25</option>
                            <option value="{url page_count=50}" {if $page_count==50}selected=""{/if}>Показывать 50</option>
                            <option value="{url page_count=100}" {if $page_count==100}selected=""{/if}>Показывать 100</option>
                        </select>
                    </div>
                    {else}
                    <div class="alert alert-info">
                        <h4>Укажите параметры для отчета</h4>
                    </div>
                    {/if}

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