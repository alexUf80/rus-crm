{$meta_title='Тикеты' scope=parent}

{capture name='page_scripts'}
    
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>

    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/orders.js?v=1.06"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js?v=1.01"></script>
    

{/capture}

{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />

    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
        .jsgrid-table { margin-bottom:0}
        .label { white-space: pre; }
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Тикеты</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Тикеты</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {*}
                <div class="row">
                
                    <div class="col-6 ">
                        <div class="js-daterange-filter input-group mb-3" {if $period!='optional'}style="display:none"{/if}>
                            <input type="text" name="daterange" class="form-control daterange js-daterange-input" value="{if $from && $to}{$from}-{$to}{/if}">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <span class="ti-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 dropdown float-right hidden-sm-down js-period-filter">
                        <input type="hidden" value="{$period}" id="filter_period" />     
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                            <i class="fas fa-calendar-alt"></i>
                            {if $period == 'today'}Сегодня
                            {elseif $period == 'yesterday'}Вчера
                            {elseif $period == 'week'}На этой неделе
                            {elseif $period == 'month'}В этом месяце
                            {elseif $period == 'year'}В этом году
                            {elseif $period == 'all'}За все время
                            {elseif $period == 'optional'}Произвольный
                            {else}{$period}{/if}
                            
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"> 
                            <a class="dropdown-item js-period-link {if $period == 'today'}active{/if}" href="{url period='today' page=null}">Сегодня</a> 
                            <a class="dropdown-item js-period-link {if $period == 'yesterday'}active{/if}" href="{url period='yesterday' page=null}">Вчера</a> 
                            <a class="dropdown-item js-period-link {if $period == 'month'}active{/if}" href="{url period='month' page=null}">В этом месяце</a> 
                            <a class="dropdown-item js-period-link {if $period == 'year'}active{/if}" href="{url period='year' page=null}">В этом году</a> 
                            <a class="dropdown-item js-period-link {if $period == 'all'}active{/if}" href="{url period='all' page=null}">За все время</a> 
                            <a class="dropdown-item js-open-daterange {if $period == 'optional'}active{/if}" href="{url period='optional' page=null}">Произвольный</a> 
                        </div>
                    </div>
                </div>
                {*}
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
                        <h4 class="card-title">Тикеты</h4>
                        
                        
                        {*}
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'order_id_asc'}<a href="{url page=null sort='order_id_desc'}">ID</a>
                                            {else}<a href="{url page=null sort='order_id_asc'}">ID</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата / Время</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата / Время</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'amount_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'amount_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'amount_asc'}<a href="{url page=null sort='amount_desc'}">Сумма, руб</a>
                                            {else}<a href="{url page=null sort='amount_asc'}">Сумма, руб</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'period_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'period_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'period_asc'}<a href="{url page=null sort='period_desc'}">Срок</a>
                                            {else}<a href="{url page=null sort='period_asc'}">Срок</a>{/if}
                                        </th>
                                        <th style="width: 150px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'birth_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'birth_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'birth_asc'}<a href="{url page=null sort='birth_desc'}">Д/Р</a>
                                            {else}<a href="{url page=null sort='birth_asc'}">Д/Р</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'region_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'region_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'region_asc'}<a href="{url page=null sort='region_desc'}">Регион</a>
                                            {else}<a href="{url page=null sort='region_asc'}">Регион</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'manager_asc'}<a href="{url page=null sort='manager_desc'}">Менеджер</a>
                                            {else}<a href="{url page=null sort='manager_asc'}">Менеджер</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell {if $sort == 'scoring_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'scoring_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'scoring_asc'}<a href="javascript:void(0);">Скоринг</a>
                                            {else}<a href="javascript:void(0);">Скоринг</a>{/if}
                                        </th>
                                    </tr>
                                    <tr class="jsgrid-filter-row" id="search_form">
                                    
                                        <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="order_id" value="{$search['order_id']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="date" value="{$search['date']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="amount" value="{$search['amount']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            <input type="text" name="period" value="{$search['period']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 150px;" class="jsgrid-cell">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                            <input type="text" name="birth" value="{$search['birth']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="region" value="{$search['region']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            <select name="manager_id" class="form-control">
                                                <option value="0"></option>
                                                <option value="none">Без менеджера</option>
                                                {foreach $managers as $m}
                                                <option value="{$m->id}" {if $search['manager_id'] == $m->id}selected{/if}>{$m->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tbody>
                                    {foreach $orders as $order}
                                        <tr class="jsgrid-row">
                                            <td style="width: 70px;" class="jsgrid-cell jsgrid-align-right">
                                                <a href="order/{$order->order_id}">{$order->order_id}</a>
                                                {if $order->contract}<div><small>{$order->contract->number}</small></div>{/if}
                                                <small>
                                                    {if $order->status == 0}<span class="label label-warning">Новая</span>
                                                    {elseif $order->status == 1}<span class="label label-info">Принята</span>
                                                    {elseif $order->status == 2}<span class="label label-success">Одобрена</span>
                                                    {elseif $order->status == 3}<span class="label label-danger">Отказ</span>
                                                    {elseif $order->status == 4}<span class="label label-inverse">Подписан</span>
                                                    {elseif $order->status == 5}<span class="label label-primary">Выдан</span>
                                                    {elseif $order->status == 6}<span class="label label-danger">Не удалось выдать</span>
                                                    {elseif $order->status == 7}<span class="label label-inverse">Погашен</span>
                                                    {elseif $order->status == 8}<span class="label label-danger">Отказ клиента</span>
                                                    {/if}
                                                </small>
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->date|date} 
                                                {$order->date|time}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->amount} 
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {if $order->period}
                                                    {$order->period} {$order->period|plural:'день':'дней':'дня'}
                                                {/if}
                                            </td>
                                            <td style="width: 150px;" class="jsgrid-cell">
                                                <a href="client/{$order->user_id}">
                                                {$order->lastname} 
                                                {$order->firstname} 
                                                {$order->patronymic}
                                                </a>
                                                {if $order->client_status}
                                                    {if $order->client_status == 'pk'}<span class="label label-success" title="Клиент уже имеет погашенные займы">ПК</span>
                                                    {elseif $order->client_status == 'crm'}<span class="label label-primary" title="Клиент уже имеет погашенные займы в CRM">ПК CRM</span>
                                                    {elseif $order->client_status == 'rep'}<span class="label label-warning" title="Клиент уже подавал ранее заявки">Повтор</span>
                                                    {elseif $order->client_status == 'nk'}<span class="label label-info" title="Новый клиент">Новая</span>
                                                    {/if}
                                                {else}
                                                    {if $order->have_crm_closed}
                                                        <span class="label label-primary" title="Клиент уже имеет погашенные займы в CRM">ПК CRM</span>
                                                    {elseif $order->loan_history|count > 0}
                                                        <span class="label label-success" title="Клиент уже имеет погашенные займы">ПК</span>
                                                    {elseif $order->first_loan}
                                                        <span class="label label-info" title="Новый клиент">Новая</span>
                                                    {else}
                                                    <span class="label label-warning" title="Клиент уже подавал ранее заявки">Повтор</span>
                                                    {/if}
                                                {/if}
                                                {if $order->autoretry}
                                                <span class="label label-danger" title="">Автоповтор</span>
                                                {/if}
                                                {if $order->antirazgon}
                                                <span class="label label-danger" title="">АвтоАнтиРазгон {if $order->antirazgon == 1}0-2{elseif $order->antirazgon == 2}3-5{elseif $order->antirazgon == 3}6-10{/if}</span>
                                                {/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {$order->birth}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$order->phone_mobile}
                                                <button class="js-mango-call mango-call" data-phone="{$order->phone_mobile}" title="Выполнить звонок"><i class="fas fa-mobile-alt"></i></button>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {$order->Regregion}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$managers[$order->manager_id]->name|escape}
                                                
                                            </td>
                                            <td style="width: 100px;padding:0" class="jsgrid-cell">
                                                <div style="max-height:128px;padding:5px 0 5px 5px;;overflow-y:auto;overflow-x:hidden">
                                                    {foreach $order->scorings as $sc}
                                                    <span {if $sc->string_result}data-toggle="tooltip" title="{$sc->string_result|escape} {if $sc->type == 'scorista'}{$sc->scorista_ball}{/if}"{/if} class="label label-sm {if in_array($sc->status, ['new','process'])}label-warning{elseif $sc->success}label-success{else}label-danger{/if}">{$scoring_types[$sc->type]->short_title|escape}</span>
                                                    {/foreach}
                                                </div>
                                            
                                                
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            
                            {if $total_pages_num>1}
                           	
                        	{$visible_pages = 11}
                        	{$page_from = 1}
                        	
                        	{if $current_page_num > floor($visible_pages/2)}
                        		{$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
                        	{/if}	
                        	
                        	{if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
                        		{$page_from = max(1, $total_pages_num-$visible_pages-1)}
                        	{/if}
                        	
                        	{$page_to = min($page_from+$visible_pages, $total_pages_num-1)}
                        
                            <div class="jsgrid-pager-container" style="">
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
                                		{$p = $smarty.section.pages.index+1}	
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
                            
                            <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
                            </div>
                        </div>
                        {*}
                        
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
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