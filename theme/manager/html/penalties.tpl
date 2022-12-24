1{$meta_title='Штрафы' scope=parent}

{capture name='page_scripts'}
    
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/orders.js?v=1.01"></script>
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js?v=1.16"></script>
    
    <script>
        $(function(){
            $('.js-open-show').hide();
            
            
            $(document).on('click', '.js-open-contract', function(e){
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

            $(document).on('change', '.js-collection-manager', function(){
                var manager_id = $(this).val();
                var contract_id = $(this).data('contract');
                
                var manager_name = $(this).find('option:selected').text();
                
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'collection_manager',
                        manager_id: manager_id,
                        contract_id: contract_id
                    },
                    success: function(resp){
                        if (manager_id == 0)
                            $('.js-collection-manager-block.js-dopinfo-'+contract_id).html('');
                        else
                            $('.js-collection-manager-block.js-dopinfo-'+contract_id).html(manager_name);
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

        $(document).on('click', '.js-open-sms-modal', function(e){
            e.preventDefault();
            
            var _user_id = $(this).data('user');
            var _order_id = $(this).data('order');
            var _yuk = $(this).hasClass('is-yuk') ? 1 : 0;
            
            $('#modal_send_sms [name=user_id]').val(_user_id)
            $('#modal_send_sms [name=order_id]').val(_order_id)
            $('#modal_send_sms [name=yuk]').val(_yuk)
            $('#modal_send_sms').modal();
        });
        
        $(document).on('submit', '.js-sms-form', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            var _user_id = $form.find('[name=user_id]').val();
            
            if ($form.hasClass('loading'))
                return false;
            
            $.ajax({
                url: '/ajax/communications.php',
                data: {
                    action: 'check',
                    user_id: _user_id,
                },
                success: function(resp){
                    if (!!resp)
                    {
                        $.ajax({
                            type: 'POST',
                            data: $form.serialize(),
                            beforeSend: function(){
                                $form.addClass('loading')
                            },
                            success: function(resp){
                                $form.removeClass('loading');
                                $('#modal_send_sms').modal('hide');
                                
                                if (!!resp.error)
                                {
                                    Swal.fire({
                                        timer: 5000,
                                        title: 'Ошибка!',
                                        text: resp.error,
                                        type: 'error',
                                    });
                                }
                                else
                                {
                                    Swal.fire({
                                        timer: 5000,
                                        title: '',
                                        text: 'Сообщение отправлено',
                                        type: 'success',
                                    });
                                    
                                    $.ajax({
                                        url: 'ajax/communications.php',
                                        data: {
                                            action: 'add',
                                            user_id: _user_id,
                                            type: 'sms',
                                            content: $('[name="template_id"] option:selected').text(    )
                                        }
                                    });
            
                                }
                            },
                        })        
                        
                    }
                    else
                    {
                        Swal.fire({
                            title: 'Ошибка!',
                            text: 'Исчерпан лимит коммуникаций',
                            type: 'error',
                        });
                        
                    }
                }
            })
            
        });

        $(document).on('change', '.js-workout-input', function(){
            var $this = $(this);
            
            var _contract = $this.val();
            var _workout = $this.is(':checked') ? 1 : 0;
                
            $.ajax({
                type: 'POST',
                data: {
                    action: 'workout',
                    contract_id: _contract,
                    workout: _workout
                },
                beforeSend: function(){
                    $('.jsgrid-load-shader').show();
                    $('.jsgrid-load-panel').show();
                },
                success: function(resp){
                    
                    if (_workout)
                        $this.closest('.js-contract-row').addClass('workout-row');
                    else
                        $this.closest('.js-contract-row').removeClass('workout-row');

                    $('.jsgrid-load-shader').hide();
                    $('.jsgrid-load-panel').hide();
                        
                    /*
                    $.ajax({
                        success: function(resp){
                            $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                            $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());
                            $('.js-period-filter').html($(resp).find('.js-period-filter').html());
                            $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                            $('.js-filter-client').html($(resp).find('.js-filter-client').html());
            
                            $('.jsgrid-pager-container').html($(resp).find('.jsgrid-pager-container').html());

                            $('.jsgrid-load-shader').hide();
                            $('.jsgrid-load-panel').hide();
                        }
                    });
                    */
                }
            })
                
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
        

        var _init_daterange = function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
                default:''
            });
            
            $(document).on('change', '.js-daterange-input', function(){
                app.filter()
            })
            
            $(document).on('click', '.js-open-daterange', function(e){
                e.preventDefault();
                
                $('#filter_period').val('optional')
                
                $('.js-period-filter button').html('<i class="fas fa-calendar-alt"></i> Произвольный')
                $('.js-period-filter .dropdown-item').removeClass('active');
                $(this).addClass('active')
                
                $('.js-daterange-filter').show();
            });        
            _init_daterange();
        }
        })
    </script>
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
        .workout-row > td {
            background:#f2f7f8!important;
        }
        .workout-row a, .workout-row small, .workout-row span {
            color:#555!important;
            font-weight:300;
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Штрафы</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Штрафы</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 ">
                <div class="row">
                
                    <div class="col-6 ">
                        {if in_array('add_penalty', $manager->permissions)}
                        <select name="manager_id" class="form-control">
                            <option value="">Все </option>
                            {foreach $managers as $m}
                            {if $m->role == 'user'}
                            <option value="{$m->id}" {if $m->id==$manager_id}selected{/if}>{$m->name}</option>
                            {/if}
                            {/foreach}
                        </select>
                        {/if}
                    </div>

                    <div class="col-6 dropdown text-right hidden-sm-down js-period-filter">
                    
                        <input type="hidden" value="{$period}" id="filter_period" />     
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                            <i class="fas fa-calendar-alt"></i>
                            {if $period == 'month'}В этом месяце
                            {elseif $period == 'year'}В этом году
                            {elseif $period == 'all'}За все время
                            {elseif $period == 'optional'}Произвольный
                            {else}{$period}{/if}
                            
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"> 
                            <a class="dropdown-item js-period-link {if $period == 'month'}active{/if}" href="{url period='month' page=null}">В этом месяце</a> 
                            <a class="dropdown-item js-period-link {if $period == 'year'}active{/if}" href="{url period='year' page=null}">В этом году</a> 
                            <a class="dropdown-item js-period-link {if $period == 'all'}active{/if}" href="{url period='all' page=null}">За все время</a> 
                            <a class="dropdown-item js-open-daterange {if $period == 'optional'}active{/if}" href="{url period='optional' page=null}">Произвольный</a> 
                        </div>
                    
                        <div class="js-daterange-filter input-group mb-3 mt-2" {if $period!='optional'}style="display:none"{/if}>
                            <input type="text" name="daterange" class="form-control daterange js-daterange-input" value="{if $from && $to}{$from}-{$to}{/if}">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <span class="ti-calendar"></span>
                                </span>
                            </div>
                        </div>
                    
                    </div>
                </div>
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
                            <h4 class="card-title  float-left">Штрафы</h4>
                            <div class="float-right js-filter-client">
                                {*}
                                {foreach $collection_statuses as $cs_id => $cs_name}
                                <a href="{if $filter_status==$cs_id}{url status=null page=null}{else}{url status=$cs_id page=null}{/if}" class="btn btn-xs {if $filter_status==$cs_id}btn-success{else}btn-outline-success{/if}">{$cs_name|escape}</a>
                                {/foreach}
                                {*}
{*}
                                <a href="{if $filter_status==1}{url status=null page=null}{else}{url status='1' page=null}{/if}" class="btn btn-xs {if $filter_status==1}btn-success{else}btn-outline-success{/if}">Нулевой день</a>
                                <a href="{if $filter_status==2}{url status=null page=null}{else}{url status='2' page=null}{/if}" class="btn btn-xs {if $filter_status==2}btn-primary{else}btn-outline-primary{/if}">1, 2 день</a>
                                <a href="{if $filter_status==3}{url status=null page=null}{else}{url status='3' page=null}{/if}" class="btn btn-xs {if $filter_status==3}btn-info{else}btn-outline-info{/if}">Ожидание-1</a>
                                <a href="{if $filter_status==4}{url status=null page=null}{else}{url status='4' page=null}{/if}" class="btn btn-xs {if $filter_status==4}btn-warning{else}btn-outline-warning{/if}">Предсофт</a>
                                <a href="{if $filter_status==5}{url status=null page=null}{else}{url status='5' page=null}{/if}" class="btn btn-xs {if $filter_status==5}btn-danger{else}btn-outline-danger{/if}">10-15 дней</a>
                                <a href="{if $filter_status==6}{url status=null page=null}{else}{url status='6' page=null}{/if}" class="btn btn-xs {if $filter_status==6}btn-inverse{else}btn-outline-inverse{/if}">15+ дней</a>
{*}
                            </div>

                        </div>
                        
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th style="width: 50px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'created_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'created_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'created_asc'}<a href="{url page=null sort='created_desc'}">Дата </a>
                                            {else}<a href="{url page=null sort='created_asc'}">Дата </a>{/if}
                                        </th>
                                        {if in_array($manager->role, ['developer', 'admin', 'quality_control'])}
                                        <th style="width:80px" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'manager_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'manager_id_asc'}<a href="{url page=null sort='manager_id_desc'}">Пользователь</a>
                                            {else}<a href="{url page=null sort='manager_id_asc'}">Пользователь</a>{/if}
                                        </th>
                                        {/if}
                                        <th style="width: 50px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'order_id_asc'}<a href="{url page=null sort='order_id_desc'}">Заявка</a>
                                            {else}<a href="{url page=null sort='order_id_asc'}">Заявка</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'created_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'created_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'created_asc'}<a href="{url page=null sort='created_desc'}">ФИО клиента</a>
                                            {else}<a href="{url page=null sort='created_asc'}">ФИО клиента</a>{/if}
                                        </th>
                                        <th style="width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'created_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'created_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'created_asc'}<a href="{url page=null sort='created_desc'}">Причина</a>
                                            {else}<a href="{url page=null sort='created_asc'}">Причина</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'order_id_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'order_id_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                            {if $sort == 'order_id_asc'}<a href="{url page=null sort='order_id_desc'}">Сумма</a>
                                            {else}<a href="{url page=null sort='order_id_asc'}">Сумма</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'created_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'created_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'created_asc'}<a href="{url page=null sort='created_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='created_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width: 70px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'created_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'created_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'created_asc'}<a href="{url page=null sort='created_desc'}">Проверяющий </a>
                                            {else}<a href="{url page=null sort='created_asc'}">Проверяющий </a>{/if}
                                        </th>                                        
                                    </tr>

                                    {*}
                                    <tr class="jsgrid-filter-row" id="search_form">
                                        <td style="width:20px;" class="jsgrid-cell"></td>                                    
                                        {if in_array($manager->role, ['developer', 'admin', 'chief_collector', 'team_collector'])}
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <select class="form-control" name="manager_id">
                                                <option value="0"></option>
                                                {foreach $managers as $m}
                                                {if (in_array($manager->role, ['developer', 'admin', 'chief_collector']) && $m->role=='collector') || ($manager->role == 'team_collector' && in_array($m->id, (array)$manager->team_id))}
                                                <option value="{$m->id}">{$m->name|escape} ({$collection_statuses[$m->collection_status_id]})</option>
                                                {/if}
                                                {/foreach}
                                            </select>
                                        </td>
                                        {/if}
                                        <td style="width: 120px;" class="jsgrid-cell">
                                            <input type="text" name="fio" value="{$search['fio']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                        </td>
                                        <td style="width: 70px;" class="jsgrid-cell">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <input type="text" name="phone" value="{$search['phone']}" class="form-control input-sm">
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <div class="row no-gutter">
                                                <div class="col-6 pr-0">
                                                    <input type="text" placeholder="c" name="delay_from" value="{$search['delay_from']}" class="form-control input-sm">                                                
                                                </div>
                                                <div class="col-6 pl-0">
                                                    <input type="text" name="delay_to" placeholder="по" value="{$search['delay_to']}" class="form-control input-sm">                                            
                                                </div>
                                            </div>
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <select class="form-control" name="tag_id">
                                                <option value="0"></option>
                                                {foreach $collector_tags as $t}
                                                <option value="{$t->id}">{$t->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td style="width: 140px;" class="jsgrid-cell">
                                        </td>
                                    </tr>
                                    {*}
                                    
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tbody>
                                    {foreach $penalties as $penalty}
                                    
                                        <tr class="jsgrid-row js-contract-row {if $contract->collection_workout}workout-row{/if}">
                                            <td style="width: 50px;" class="jsgrid-cell">                                                
                                                <small>{$penalty->created|date}</small> 
                                                <br />
                                                <small>{$penalty->created|time}</small>
                                            </td>
                                            {if in_array($manager->role, ['developer', 'admin', 'quality_control'])}
                                            <td style="width: 80px;" class="jsgrid-cell">
                                                <a href="manager/{$penalty->manager_id}">
                                                    {$managers[$penalty->manager_id]->name}
                                                </a>
                                            </td>
                                            {/if}
                                            <td style="width:50px" class="jsgrid-cell">
                                                <a href="order/{$penalty->order_id}">{$penalty->order_id}</a>
                                                <br />
                                                {if $penalty->order->status == 0}<span class="label label-warning">Новая</span>
                                                {elseif $penalty->order->status == 1}<span class="label label-info">Принята</span>
                                                {elseif $penalty->order->status == 2}<span class="label label-success">Одобрена</span>
                                                {elseif $penalty->order->status == 3}<span class="label label-danger">Отказ</span>
                                                {elseif $penalty->order->status == 4}<span class="label label-inverse">Подписан</span>
                                                {elseif $penalty->order->status == 5}<span class="label label-primary">Выдан</span>
                                                {elseif $penalty->order->status == 6}<span class="label label-danger">Не удалось выдать</span>
                                                {elseif $penalty->order->status == 7}<span class="label label-inverse">Погашен</span>
                                                {elseif $penalty->order->status == 8}<span class="label label-danger">Отказ клиента</span>
                                                {/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <a href="client/{$penalty->order->user_id}">
                                                    {$penalty->order->lastname}
                                                    {$penalty->order->firstname}
                                                    {$penalty->order->patronymic}
                                                </a>
                                            </td>
                                            <td style="width: 100px;" class="jsgrid-cell">
                                                <small><strong>{$penalty_types[$penalty->type_id]->name}</strong></small>
                                                <br />
                                                <small>{$penalty->comment}</small>
                                            </td>
                                            <td style="width: 50px;" class="jsgrid-cell">
                                                {$penalty->cost}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                {if $penalty->status == 1}<span class="label label-warning">{$penalty_statuses[$penalty->status]}</span>{/if}
                                                {if $penalty->status == 2}<span class="label label-success">{$penalty_statuses[$penalty->status]}</span>{/if}
                                                {if $penalty->status == 3}<span class="label label-primary">{$penalty_statuses[$penalty->status]}</span>{/if}
                                                {if $penalty->status == 4}<span class="label label-danger">{$penalty_statuses[$penalty->status]}</span>{/if}
                                            </td>
                                            <td style="width: 70px;" class="jsgrid-cell">
                                                <a href="manager/{$penalty->control_manager_id}">
                                                    {$managers[$penalty->control_manager_id]->name}
                                                </a>
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
                                <select class="form-control form-control-sm js-page-count" name="page-count">
                                    <option value="{url page_count=50}" {if $page_count==50}selected=""{/if}>Показывать 50</option>
                                    <option value="{url page_count=100}" {if $page_count==100}selected=""{/if}>Показывать 100</option>
                                    <option value="{url page_count=500}" {if $page_count==500}selected=""{/if}>Показывать 500</option>
                                    {*}
                                    <option value="{url page_count='all'}" {if $page_count=='all'}selected=""{/if}>Показывать все</option>
                                    {*}
                                </select>
                            </div>
                            
                            <div style="clear:both"></div>
                            
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