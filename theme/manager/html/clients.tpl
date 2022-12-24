{$meta_title='Клиенты' scope=parent}

{capture name='page_scripts'}
    
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/clients.js"></script>
    

{/capture}

{capture name='page_styles'}
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-chart-bubble"></i> Клиенты</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Клиенты</li>
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
                        <h4 class="card-title">Клиенты</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'id_asc'}<a href="{url page=null sort='id_desc'}">ID</a>
                                            {else}<a href="{url page=null sort='id_asc'}">ID</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата</a>{/if}
                                        </th>
                                        <th style="width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'birth_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'birth_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'birth_asc'}<a href="{url page=null sort='birth_desc'}">Д/Р</a>
                                            {else}<a href="{url page=null sort='birth_asc'}">Д/Р</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'email_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'email_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'email_asc'}<a href="{url page=null sort='email_desc'}">Email</a>
                                            {else}<a href="{url page=null sort='email_asc'}">Email</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                    </tr>

                                    <tr class="jsgrid-filter-row" id="search_form">                                    
                                        <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="user_id" value="{$search['user_id']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="created" value="{$search['created']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 120px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="birth" value="{$search['birth']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="email" value="{$search['email']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $clients as $client}
                                        <tr class="jsgrid-row">
                                            <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">
                                                <a href="client/{$client->id}">{$client->id}</a>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <span>{$client->created|date}</span>
                                                {$client->created|time}
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                {$client->lastname|escape}
                                                {$client->firstname|escape}
                                                {$client->patronymic|escape}
                                                {if $client->loaded_from_1c}<span class="label label-primary">1C</span>{/if}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$client->birth}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {$client->email|escape}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                {$client->phone_mobile|escape}
                                            </td>
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
                            
                            <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
                            </div>
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