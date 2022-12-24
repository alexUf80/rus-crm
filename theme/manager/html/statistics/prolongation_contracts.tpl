{$meta_title='К оплате в ближайшие 5 дней ' scope=parent}

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

    <script>
        function ReportPaymentsApp()
        {
            var app = this;
           
            app.init = function(){
                
                app.send_operation();
                
                app.init_search();
            };
            
            app.send_operation = function(){

                $('.js-send-operation').click(function(e){
                    e.preventDefault();
                    
                    var operation_id = $(this).data('operation');
                    
                    $.ajax({
                        data: {
                            operation_id: operation_id
                        },
                        success: function(resp){
                            if (!!resp.error)
                                Swal.fire('Ошибка', resp.error, 'error');
                            else
                                Swal.fire('Успешно', resp.success, 'success');
                        }
                    });
                });
            };
            
            app.load = function(_url, loading){
                $.ajax({
                    url: _url,
                    beforeSend: function(){
                        if (loading)
                        {
                            $('.jsgrid-load-shader').show();
                            $('.jsgrid-load-panel').show();
                        }
                    },
                    success: function(resp){
                        
                        
                        if (loading)
                        {
                            $('html, body').animate({
                                scrollTop: $("#basicgrid").offset().top-80  
                            }, 1000);
                            
                            $('.jsgrid-load-shader').hide();
                            $('.jsgrid-load-panel').hide();
                        }
                        
                    }
                })
            };
            
            app.init_search = function(){
                $(document).on('change', '.js-search-block input', function(){

                    var _searches = {};
                    $('.js-search-block input').each(function(){
                        if ($(this).val() != '')
                        {
                            _searches[$(this).attr('name')] = $(this).val();
                        }
                    });     
                    var _request = {

                    };
                    var _query = Object.keys(_request).map(
                        k => encodeURIComponent(k) + '=' + encodeURIComponent(_request[k])
                    ).join('&');
            
                    _request.search = _searches;
                    if (!$.isEmptyObject(_searches))
                    {
                        _query_searches = '';
                        for (key in _searches) {
                          _query_searches += '&search['+key+']='+_searches[key];
                        }
                        _query += _query_searches;
                    }
                    
                    $.ajax({
                        data: _request,
                        beforeSend: function(){
                        },
                        success: function(resp){
                            var _table = $(resp).find('#table_content').html();
console.log(_table)
                            $('#table_content').html(_table)
                        }
                    })
                });
            };
            
            ;(function(){
                app.init();
            })();
        };
        $(function(){
            new ReportPaymentsApp();
        });
    </script>
{/capture}

{capture name='page_styles'}
    
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
    .table th td {
        text-align:center!important;
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
                    <span>К оплате в ближайшие {$count_days} {$count_days|plural:'день':'дней':'дня'} </span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="statistics">Статистика</a></li>
                    <li class="breadcrumb-item active">Ближайшие оплаты</li>
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
                        <h4 class="card-title clearfix">
                            <div class="float-left">
                            К оплате в ближайшие {$count_days} {$count_days|plural:'день':'дней':'дня'} 
                            </div>
                            <div class="float-right">
                                <a href="{url download='excel'}" class="btn btn-success ">
                                    <i class="fas fa-file-excel"></i> Скачать
                                </a>
                            </div>
                        </h4>
                             
                        
                        {if $contracts}                   
                        <table class="table table-hover" id="basicgrid">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Дата платежа</th>
                                <th>Фамилия</th>
                                <th>Имя</th>
                                <th>Отчество</th>
                                <th>Номер телефона</th>
                                <th>Город</th>
                                <th>Всего продлений</th>
                                <th>ID договора</th>
                                <th>Сумма к погашению</th>
                                <th>Сумма к продлению</th>
                            </tr>
                            
                            </thead>
                            
                            <tbody id="table_content">
                                {foreach $contracts as $contract}
                                <tr>
                                    <td>{$contract@iteration}</td>
                                    <td>
                                        {$contract->return_date|date} 
                                    </td>
                                    <td>
                                        <a href="client/{$contract->user_id}" target="_blank">
                                            {$contract->user->lastname|escape}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="client/{$contract->user_id}" target="_blank">
                                            {$contract->user->firstname|escape}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="client/{$contract->user_id}" target="_blank">
                                            {$contract->user->patronymic|escape}
                                        </a>
                                    </td>
                                    <td>
                                        {$contract->user->phone_mobile|escape}
                                    </td>
                                    <td>
                                        <small>
                                            {$contract->user->Regregion|escape}
                                        </small>
                                    </td>
                                    <td>
                                        {$contract->prolongation}
                                    </td>
                                    <td>
                                        <a href="order/{$contract->order->order_id}" target="_blank">
                                            {$contract->number}
                                        </a>
                                    </td>
                                    <td>
                                        {$contract->loan_body_summ+$contract->loan_percents_summ}
                                    </td>
                                    <td>
                                        {$contract->loan_percents_summ+$settings->prolongation_amount}
                                    </td>
                                </tr>
                                {/foreach}
                            </tbody>
                            
                        </table>
                        
                        <p class="text-danger">
                            * данные по количеству продлений доступны с 01.01.2022
                        </p>
                        {else}
                            <div class="alert alert-info">
                                <h4>Нет данных для отображения</h4>
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