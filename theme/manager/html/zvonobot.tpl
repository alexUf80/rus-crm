{$meta_title='Звонки звонобота' scope=parent}

{capture name='page_scripts'}
    
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/orders.js"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js"></script>
    
    <script>
        $(function(){
            $('.js-open-show').hide();
            
            $(document).on('click', '.js-mango-call', function(e){
                e.preventDefault();
            
                var _phone = $(this).data('phone');
                var _yuk = $(this).hasClass('js-yuk') ? 1 : 0;
                
                Swal.fire({
                    title: 'Выполнить звонок?',
                    text: "Вы хотите позвонить на номер: "+_phone,
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Отменить',
                    confirmButtonText: 'Да, позвонить'
                }).then((result) => {
                    if (result.value) {
                        
                        $.ajax({
                            url: 'ajax/mango_call.php',
                            data: {
                                phone: _phone
                            },
                            beforeSend: function(){
                                
                            },
                            success: function(resp){
                                if (!!resp.error)
                                {
                                    if (resp.error == 'empty_mango')
                                    {
                                        Swal.fire(
                                            'Ошибка!',
                                            'Необходимо указать Ваш внутренний номер сотрудника Mango-office.',
                                            'error'
                                        )
                                    }
                                    
                                    if (resp.error == 'empty_mango')
                                    {
                                        Swal.fire(
                                            'Ошибка!',
                                            'Не хватает прав на выполнение операции.',
                                            'error'
                                        )
                                    }
                                }
                                else if (resp.success)
                                {
                                    Swal.fire(
                                        '',
                                        'Выполняется звонок.',
                                        'success'
                                    )
                                }
                                else
                                {
                                    console.error(resp);
                                    Swal.fire(
                                        'Ошибка!',
                                        '',
                                        'error'
                                    )
                                }
                            }
                        })
                        
                    }
                })
                
                
            });

            
            $('.js-open-contract').click(function(e){
                e.preventDefault();
                var _id = $(this).data('id')
                if ($(this).hasClass('open'))
                {
                    $(this).removeClass('open');
                    $('.js-open-hide.js-dopinfo-'+_id).show();
                    $('.js-open-show.js-dopinfo-'+_id).hide();
                }
                else
                {
                    $(this).addClass('open');
                    $('.js-open-hide.js-dopinfo-'+_id).hide();
                    $('.js-open-show.js-dopinfo-'+_id).show();
                }
            })

            $(document).on('change', '.js-contact-status', function(){
                var contact_status = $(this).val();
                var contract_id = $(this).data('contract');
                var user_id = $(this).data('user');
                var $form = $(this).closest('form');
                
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: {
                        action: 'contact_status',
                        user_id: user_id,
                        contact_status: contact_status
                    },
                    success: function(resp){
                        if (contact_status == 1)
                            $('.js-contact-status-block.js-dopinfo-'+contract_id).html('<span class="label label-success">Контактная</span>')
                        else if (contact_status == 2)
                            $('.js-contact-status-block.js-dopinfo-'+contract_id).html('<span class="label label-danger">Не контактная</span>')                            
                        else if (contact_status == 0)
                            $('.js-contact-status-block.js-dopinfo-'+contract_id).html('<span class="label label-warning">Нет данных</span>')
                            
                    }
                })
            })

            $(document).on('change', '.js-contactperson-status', function(){
                var contact_status = $(this).val();
                var contactperson_id = $(this).data('contactperson');
                var $form = $(this).closest('form');
                
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: {
                        action: 'contactperson_status',
                        contactperson_id: contactperson_id,
                        contact_status: contact_status
                    }
                })
            })


        $(document).on('click', '.js-open-comment-form', function(e){
            e.preventDefault();
            
            if ($(this).hasClass('js-contactperson'))
            {
                var contactperson_id = $(this).data('contactperson');
                $('#modal_add_comment [name=contactperson_id]').val(contactperson_id);
                $('#modal_add_comment [name=action]').val('contactperson_comment');
                $('#modal_add_comment [name=order_id]').val($(this).data('order'));
            }
            else
            {
                var contactperson_id = $(this).data('contactperson');
                $('#modal_add_comment [name=order_id]').val($(this).data('order'));
                $('#modal_add_comment [name=action]').val('order_comment');                
            }
            
            
            $('#modal_add_comment [name=text]').text('')
            $('#modal_add_comment').modal();
        });

        $(document).on('submit', '#form_add_comment', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            $.ajax({
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $('#modal_add_comment').modal('hide');
                        $form.find('[name=text]').val('')
            
                        
                        Swal.fire({
                            timer: 5000,
                            title: 'Комментарий добавлен.',
                            type: 'success',
                        });
                        location.reload();
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                        
                    }
                }
            })
        })

        })
    </script>
{/capture}

{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}
        .label { white-space: pre; }

        .js-open-hide {
            display:block;
        }
        .js-open-show {
            display:none;
        }
        .open.js-open-hide {
            display:none;
        }
        .open.js-open-show {
            display:block;
        }
        .form-control.js-contactperson-status,
        .form-control.js-contact-status {
            font-size: 12px;
            padding-left: 0px;
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Звонки звонобота</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Звонки звонобота</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
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
                        <div class="clearfix">
                            <h4 class="card-title  float-left">Список звонков</h4>

                        </div>
                        
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'order_id_asc'}<a href="{url page=null sort='order_id_desc'}">Заявка</a>
                                            {else}<a href="{url page=null sort='order_id_asc'}">Заявка</a>{/if}
                                        </th>
                                        <th style="width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'result_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'result_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'result_asc'}<a href="{url page=null sort='result_desc'}">Время звонка</a>
                                            {else}<a href="{url page=null sort='result_asc'}">Время звонка</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'sms_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'sms_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'sms_asc'}<a href="{url page=null sort='sms_desc'}">Смс</a>
                                            {else}<a href="{url page=null sort='sms_asc'}">Смс</a>{/if}
                                        </th>
                                    </tr>
                                    {*}
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
                                            <input type="text" name="status" value="{$search['manager']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                        </td>
                                    </tr>
                                    {*}
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tbody>
                                    {foreach $zvonobot_items as $item}
                                        <tr class="jsgrid-row js-contract-row">
                                            <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">                                                
                                                {$item->create_date|date} {$item->create_date|time}
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell jsgrid-align-right">                                                
                                                <a href="my_contract/{$item->contract->order_id}">{$item->contract->order_id}</a>
                                            </td>
                                            <td style="width: 120px;" class="jsgrid-cell">
                                                <a href="client/{$item->user->id}">
                                                    {$item->user->lastname} 
                                                    {$item->user->firstname} 
                                                    {$item->user->patronymic}
                                                </a>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                {$item->user->phone_mobile}
                                                <button class="js-mango-call mango-call" data-phone="{$item->user->phone_mobile}" title="Выполнить звонок"><i class="fas fa-mobile-alt"></i></button>
                                            </td>
                                            <td style="width: 60px;" class="jsgrid-cell">
                                                {$item->result*1}c
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <div class="">
                                                    <span class="label label-warning">{$item->status}</span>
                                                </div>
                                            </td>
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <div class="">
                                                    {if $item->sms_sent}
                                                    {$item->sms_sent|date} {$item->sms_sent|time}
                                                    {/if}
                                                </div>
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

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="">
                    
                    <input type="hidden" name="order_id" value="" />
                    <input type="hidden" name="contactperson_id" value="" />
                    <input type="hidden" name="action" value="" />
                    
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>