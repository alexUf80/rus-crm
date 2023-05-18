{$meta_title='Отвалы клиентов' scope=parent}

{capture name='page_scripts'}

    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/clients.js"></script>

    <script>
        function MissingsApp()
        {
            var app = this;

            app.init_set_manager = function(){
                $(document).on('click', '.js-set-manager', function(){
                     var $this = $(this);

                     var _user_id = $(this).data('user');

                     $.ajax({
                        type: 'POST',
                        data: {
                            action: 'set_manager',
                            user_id: _user_id
                        },
                        success: function(resp){
                            if (!!resp.error)
                            {
                                Swal.fire({
                                    text: resp.error,
                                    type: 'error',
                                });
                            }
                            else
                            {
console.log($this.closest('.jsgrid-row'))
                                $this.closest('.jsgrid-row').find('.js-close-missing').show();
                                $this.closest('.jsgrid-row').find('.js-missing-manager-name').text(resp.manager_name);
                            }
                        }
                     })
                });
            };

            app.init_close_missing = function(){
            }

            ;(function(){
                app.init_set_manager();
                app.init_close_missing();
            })();
        }
        $(function(){
            new MissingsApp();
        })
    </script>
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
            <div class="col-md-6 col-4 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-sleep"></i>
                    <span>Отвалы клиентов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отвалы</li>
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
                        <h4 class="card-title">Отвалы клиентов</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'id_asc'}<a href="{url page=null sort='id_desc'}">ID</a>
                                            {else}<a href="{url page=null sort='id_asc'}">ID</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Регистрация</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Регистрация</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Посл. действие</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Посл. действие</a>{/if}
                                        </th>
                                        <th style="width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width:150px" class="jsgrid-header-cell ">
                                            <a href="javascript:void(0);">Этапы</a>
                                        </th>
                                    </tr>

                                    <tr class="jsgrid-filter-row" id="search_form">
                                        <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="hidden" name="sort" value="{$sort}" />
                                            <input type="text" name="user_id" value="{$search['user_id']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">

                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="created" value="{$search['created']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="created" value="{$search['created']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 120px;" class="jsgrid-cell jsgrid-align-right">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 100px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $clients as $client}
                                        <tr class="jsgrid-row">
                                            <td style="width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                                <a href="client/{$client->id}">{$client->id}</a>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell jsgrid-align-left">
                                                {if !$client->missing_status}
                                                <span class="label label-danger">Новая</span>
                                                <a class="js-close-missing float-right btn-xs btn-success btn-rounded" data-user="{$client->id}" style="cursor:pointer"><i class="text-white fas fa-check"></i> Закрыть</a>
                                                {elseif $client->missing_status == 1}
                                                <span class="label label-info">Закрыта</span>
                                                {/if}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <span>{$client->created|date}</span>
                                                {$client->created|time}
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <span>{$client->last_stage_date|date}</span>
                                                {$client->last_stage_date|time}
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                {$client->lastname|escape}
                                                {$client->firstname|escape}
                                                {$client->patronymic|escape}
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <span class="phone-cell">{$client->phone_mobile|escape}</span>
                                                <button class="js-mango-call mango-call"  data-phone="{$client->phone_mobile}"
                                                        title="Выполнить звонок">
                                                    <i class="fas fa-mobile-alt"></i></button>
                                                <button class="js-open-sms-modal mango-call" title="Отправить смс" data-user="{$client->id}">
                                                    <i class="far fa-share-square"></i>
                                                </button>

                                            </td>
                                            <td style="width: 150px;" class="jsgrid-cell">
                                                <span class="label label-success">Регистрация</span>
                                                <span class="label {if $client->stage_personal}label-success{else}label-inverse{/if}">Перс. инфо</span>
                                                <span class="label {if $client->stage_passport}label-success{else}label-inverse{/if}">Паспорт</span>
                                                <span class="label {if $client->stage_address}label-success{else}label-inverse{/if}">Адрес</span>
                                                <span class="label {if $client->stage_work}label-success{else}label-inverse{/if}">Работа</span>
                                                <span class="label {if $client->stage_files}label-success{else}label-inverse{/if}">Файлы</span>
                                                <span class="label {if $client->stage_card}label-success{else}label-inverse{/if}">Карта</span>
                                                {if $client->stage_sms_sended}
                                                <span class="label label-primary" title="СМС сообщение отправлено">СМС</span>
                                                {/if}
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

<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-sms-form">
                                    <input type="hidden" name="user_id" value="" />
                                    <input type="hidden" name="order_id" value="" />
                                    <input type="hidden" name="yuk" value="" />
                                    <input type="hidden" name="action" value="send_sms" />
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                                <option value="{$sms_template->id}" title="{$sms_template->template|escape}">
                                                    {$sms_template->name|escape} ({$sms_template->template})
                                                </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, отправить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
