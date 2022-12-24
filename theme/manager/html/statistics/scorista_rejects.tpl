{$meta_title='Статистика отказных заявок' scope=parent}

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
                    <span>Статистика отказных заявок</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Статистика отказных заявок</li>
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
                        <h4 class="card-title">Отказы за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                                        <select name="scoring" class="form-control">
                                            <option value="all" {if !$filter_scoring || $filter_scoring == 'all'}selected{/if}>Все</option>
                                            <option value="499-" {if $filter_scoring == '499-'}selected{/if}>499-</option>
                                            <option value="500-549" {if $filter_scoring == '500-549'}selected{/if}>500-549</option>
                                            <option value="550+" {if $filter_scoring == '550+'}selected{/if}>550+</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <select name="reason_id" class="form-control">
                                            <option value="all"{if !$filter_reason || $filter_reason=='all'}selected{/if}>Все</option>
                                            {foreach $reasons as $r}
                                            <option value="{$r->id}" {if $filter_reason==$r->id}selected{/if}>{$r->admin_name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
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
                        
                        {if $from}                   
                        <table class="table table-hover">
                            
                            <tr>
                                <th>#</th>
                                <th>Дата</th>
                                <th>Заявка</th>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Менеджер</th>
                                <th>Причина</th>
                                <th>Скориста</th>
                                <th>Источник</th>
                            </tr>
                            
                            {foreach $orders as $order}
                            <tr>
                                <td>{$order@iteration}</td>
                                <td>{$order->date|date}</td>
                                <td><a target="_blank" href="order/{$order->order_id}">{$order->order_id}</a></td>
                                <td>
                                    <a href="client/{$order->user_id}" target="_blank">
                                        {$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}
                                    </a>
                                </td>
                                <td>
                                    {$order->phone_mobile}
                                </td>
                                <td>
                                    {$order->email}
                                </td>
                                <td>{$managers[$order->manager_id]->name|escape}</td>
                                <td>
                                    {if $order->reason_id}
                                        {$reasons[$order->reason_id]->admin_name|escape}
                                    {else}
                                        {$order->reject_reason|escape}
                                    {/if}
                                </td>
                                <td>{$order->scoring->scorista_ball}</td>
                                <td>{$order->utm_source}</td>
                            </tr>
                            {/foreach}
                            
                        </table>
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