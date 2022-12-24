{$meta_title='Логирование событий' scope=parent}

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
                    <span>Логирование событий</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Логирование событий</li>
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
                        <h4 class="card-title">Логирование событий за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <select name="manager_id" class="form-control">
                                            <option value="all"{if !$filter_manager_id}selected{/if}>Все</option>
                                            {foreach $managers as $m}
                                            {if $m->role == 'user'}
                                            <option value="{$m->id}" {if $filter_manager_id==$m->id}selected{/if}>{$m->name|escape}</option>
                                            {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $date_from || $date_to}
                                <div class="col-12 col-md-3 text-right">
                                    <a href="{url download='excel'}" class="btn btn-success ">
                                        <i class="fas fa-file-excel"></i> Скачать
                                    </a>
                                </div>
                                {/if}
                            </div>
                            
                        </form>     
                        
                        {if $from}                   
                        <table class="table-bordered table ">
                                <col width="50"/>
                                <col width="200" />
                                <col  width="700" />
                            <tr>
                                <th>#</th>
                                <th>Заявка</th>
                                <th>События</th>
                            </tr>
                            
                            {foreach $orders as $order}
                            <tr>
                                <td>{$order@iteration}</td>
                                <td>
                                    <small>{$order->date|date} {$order->date|time}</small>
                                    <br />
                                    <small>{$order->lastname} {$order->firstname} {$order->patronymic}</small>
                                    <br />
                                    <a href="order/{$order->order_id}" target="_blank"><strong>{$order->order_id}</strong></a>
                                    {if $order->status == 0}<span class="label label-warning">Новая</span>
                                    {elseif $order->status == 1}<span class="label label-info">Принята</span>
                                    {elseif $order->status == 2}<span class="label label-success">Одобрена</span>
                                    {elseif $order->status == 3}
                                        <span class="label label-danger">Отказ</span>
                                        <br />
                                        <small>Причина отказа: {$reasons[$order->reason_id]->admin_name}</small>
                                    {elseif $order->status == 4}<span class="label label-inverse">Подписан</span>
                                    {elseif $order->status == 5}<span class="label label-primary">Выдан</span>
                                    {elseif $order->status == 6}<span class="label label-danger">Не удалось выдать</span>
                                    {elseif $order->status == 7}<span class="label label-inverse">Погашен</span>
                                    {elseif $order->status == 8}<span class="label label-danger">Отказ клиента</span>
                                    {/if}

                                    <br />
                                    <br />
                                    <i title="Менеджер">{$managers[$order->manager_id]->name|escape}</i>
                                </td>
                                <td class="p-0 ">
                                    <table class="table table-striped mb-0">
                                        <col width="15%" />
                                        <col width="10%" />
                                        <col width="40%" />
                                        <col width="35%" />
                                        {foreach $order->eventlogs as $ev}
                                        <tr>
                                            <td class="p-2">
                                                <small>{$ev->created|date}</small>
                                            </td>
                                            <td class="p-2">
                                                <small>{$ev->created|time}</small>
                                            </td>
                                            <td class="p-2">
                                                {$events[$ev->event_id]}
                                            </td>
                                            <td class="p-2 text-right">
                                                <i>{$managers[$ev->manager_id]->name|escape}</i>
                                            </td>
                                        </tr>
                                        {/foreach}                                        
                                    </table>
                                </td>
                                
                            </tr>
                            {/foreach}
                            
                        </table>

                        {include file='pagination.tpl'}

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