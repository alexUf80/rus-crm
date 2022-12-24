{$meta_title='Инструменты' scope=parent}

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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-file-chart"></i> Инструменты</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Инструменты</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-inverse card-danger">
                    <a href="tools/integrations" class="box text-center">
                        <h1 class="font-light text-white">Интеграции</h1>
                        <h6 class="text-white">Источники</h6>
                    </a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 col-xlg-3">
                <div class="card card-inverse card-info">
                    <a href="tools/short_link" class="box text-center">
                        <h1 class="font-light text-white">Короткие ссылки</h1>
                        <h6 class="text-white">Сокращение ссылок</h6>
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