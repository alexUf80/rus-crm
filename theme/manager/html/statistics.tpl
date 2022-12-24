{$meta_title='Статистика' scope=parent}

{capture name='page_scripts'}
    
    

{/capture}

{capture name='page_styles'}


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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-file-chart"></i> Статистика</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Статистика</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                
            </div>
        </div>
        
        <div class="row ">
            <!-- Column -->
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-inverse card-info">
                    <a href="statistics/report" class="box bg-info text-center">
                        <h1 class="font-light text-white">Выдача</h1>
                        <h6 class="text-white">Оперативная отчетность</h6>
                    </a>
                </div>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-primary card-inverse">
                    <a href="statistics/conversion" class="box text-center">
                        <h1 class="font-light text-white">Конверсия</h1>
                        <h6 class="text-white">Конверсии в выдачу</h6>
                    </div>
                </a>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <a href="statistics/expired" class="card card-inverse card-success">
                    <div class="box text-center">
                        <h1 class="font-light text-white">Просрочка</h1>
                        <h6 class="text-white">Статистика просрочки</h6>
                    </div>
                </a>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-inverse card-warning">
                    <a href="statistics/free_pk" class="box text-center">
                        <h1 class="font-light text-white">Свободные ПК</h1>
                        <h6 class="text-white">ПК без открытых договоров</h6>
                    </a>
                </div>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-info card-warning">
                    <a href="statistics/prolongation_contracts" class="box text-center">
                        <h1 class="font-light text-white">Ближайшие оплаты</h1>
                        <h6 class="text-white">К оплате в ближайшие 5 дней</h6>
                    </a>
                </div>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-info card-warning">
                    <a href="statistics/dailyreports" class="box text-center">
                        <h1 class="font-light text-white">Отчет по дням</h1>
                        <h6 class="text-white">Отчеты по датам</h6>
                    </a>
                </div>
            </div>
        </div>

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