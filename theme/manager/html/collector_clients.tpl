{$meta_title='Перебросы клиентов' scope=parent}

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
                    <span>Перебросы клиентов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Коллекшин</li>
                    <li class="breadcrumb-item active">Перебросы клиентов</li>
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
                        <h4 class="card-title">Перебросы клиентов за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                                            {foreach $managers as $m}
                                            {if $m->role == 'collector'}
                                            <option value="{$m->id}" {if $m->id==$manager_id}selected{/if}>{$m->name}</option>
                                            {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                                {if $from}
                                <div class="col-4">
                                    <div class="card card-inverse card-info">
                                        
                                        <div class="box bg-info text-center">
                                            <div class="row">
                                                <div class="col-4">
                                                    <h5 class="text-center pt-2">Договоров:</h5> 
                                                    <h3 class="text-white text-center">{$contracts|count}</h3>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="text-center pt-2">ОД: </h5>
                                                    <h3 class="text-white text-center">{$count_od}</h3>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="text-center pt-2">Проценты: </h5>
                                                    <h3 class="text-white text-center">{$count_percents}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {/if}
                            </div>
                            
                        </form>     
                        
                        {if $from}                   
                        
                        <div class="row">
                            <div class="col-8"></div>
                        </div>
                        
                        <table class="table table-hover table-bordered">
                            
                            <tr>
                                <th>#</th>
                                <th>Дата</th>
                                <th>ФИО</th>
                                <th>Договор</th>
                                <th>ОД</th>
                                <th>Проценты</th>
                            </tr>
                            
                            {foreach $contracts as $contract}
                            <tr>
                                <td>{$contract@iteration}</td>
                                <td></td>
                                <td>
                                    {$contract->lastname|escape} {$contract->firstname|escape} {$contract->patronymic|escape}
                                </td>
                                <td>
                                    <a target="_blank" href="order/{$contract->order_id}">
                                        {$contract->number}
                                    </a>
                                    <br />
                                    <small>{$contract->inssuance_date|date}</small>
                                </td>
                                <td>
                                    {$contract->loan_body_summ}
                                </td>
                                <td>
                                    {$contract->loan_percents_summ+$contract->loan_charge_summ+$contract->loan_peni_summ}
                                </td>
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