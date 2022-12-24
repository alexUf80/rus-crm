{$meta_title='Штрафы' scope=parent}

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
                    <span>Штрафы</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Штрафы</li>
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
                        <h4 class="card-title">Штрафы {if $filter_manager_id && $filter_manager_id!='all'}"{$managers[$filter_manager_id]->name}"{/if} за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                                    {if $manager->role == 'user'}
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
                                    {/if}
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $total_count > 0}
                                <div class="col-md-3">
                                    <div class="card card-inverse card-info">

                                        <div class="box bg-info text-center">
                                            <div class="row">
                                                <div class="col-6">
                                                    <h5 class="text-center pt-2">Договоров:</h5> 
                                                    <h3 class="text-white text-center">{$total_count}</h3>
                                                </div>
                                                <div class="col-6">
                                                    <h5 class="text-center pt-2">Сумма штрафов: </h5>
                                                    <h3 class="text-white text-center">{$total_summ} P</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                
                                </div>
                                {/if}
                                {*if $date_from || $date_to}
                                <div class="col-12 col-md-3 text-right">
                                    <a href="{url download='excel'}" class="btn btn-success ">
                                        <i class="fas fa-file-excel"></i> Скачать
                                    </a>
                                </div>
                                {/if*}
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
                                <th>Штрафы</th>
                            </tr>
                            
                            {foreach $orders as $order}
                            <tr>
                                <td>{$order@iteration}</td>
                                <td>
                                    <small>{$order->date|date} {$order->date|time}</small>
                                    <br />
                                    <a href="order/{$order->order_id}" target="_blank">
                                        <small>{$order->lastname} {$order->firstname} {$order->patronymic}</small>
                                    </a>
                                    <br />
                                    <a href="order/{$order->order_id}" target="_blank"><strong>{$order->order_id}</strong></a>
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
                                    
                                    <h5 class="pt-2 text-danger">
                                    Штраф:
                                    {$order->penalty_summ} Р
                                    </h5>
                                </td>
                                <td class="p-0 ">
                                    <table class="table table-striped mb-0">
                                        <col width="10%" />
                                        <col width="20%" />
                                        <col width="30%" />
                                        <col width="10%" />
                                        <col width="10%" />
                                        <col width="20%" />
                                        {foreach $order->penalties as $p}
                                        <tr>
                                            <td class="p-2">
                                                <small>{$p->created|date}</small>
                                                <br />
                                                <small>{$p->created|time}</small>
                                            </td>
                                            <td class="p-2">
                                                <small><strong>{$managers[$p->manager_id]->name|escape}</strong></small>
                                            </td>
                                            <td class="p-2">
                                                <small><strong>{$penalty_types[$p->type_id]->name}</strong></small>
                                                <br />
                                                <small>{$p->comment}</small>
                                            </td>
                                            <td class="p-2">
                                                {if $p->status == 1}<span class="label label-warning">{$penalty_statuses[$p->status]}</span>{/if}
                                                {if $p->status == 2}<span class="label label-success">{$penalty_statuses[$p->status]}</span>{/if}
                                                {if $p->status == 3}<span class="label label-primary">{$penalty_statuses[$p->status]}</span>{/if}
                                                {if $p->status == 4}<span class="label label-danger">{$penalty_statuses[$p->status]}</span>{/if}
                                            </td>
                                            <td class="p-2 text-right">
                                                <strong>{$p->cost} Р</strong>
                                            </td>
                                            <td class="p-2 text-right">
                                                <i>{$managers[$p->control_manager_id]->name|escape}</i>
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