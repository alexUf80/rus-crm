{$meta_title = 'Лимиты коммуникаций' scope=parent}

{capture name='page_scripts'}

    <script type="text/javascript">

    </script>

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
                <h3 class="text-themecolor mb-0 mt-0">
                    Лимиты коммуникаций
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Лимиты коммуникаций</li>
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
        <!-- Row -->
        <form class="" method="POST" >
            
        <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Максимальное количество контактов за период
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">В день</label>
                                <div class="">
                                    <input type="text" class="form-control" name="limit_communications[day]" value="{$settings->limit_communications['day']}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">В неделю</label>
                                <div class="">
                                    <input type="text" class="form-control" name="limit_communications[week]" value="{$settings->limit_communications['week']}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">В месяц</label>
                                <div class="">
                                    <input type="text" class="form-control" name="limit_communications[month]" value="{$settings->limit_communications['month']}" placeholder="">
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                    
                </div>
            </div>
        
        <hr class="mb-3 mt-3" />
        
        <div class="row">
            <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                </div>
            </div>
        </form>
        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>




