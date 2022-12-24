{$meta_title = 'Общие Настройки' scope=parent}

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
                    Общие настройки
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Общие настройки</li>
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
                                Кредитование
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Минимальная сумма</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_min_summ" value="{$settings->loan_min_summ}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Сумма по умолчанию</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_default_summ" value="{$settings->loan_default_summ}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Максимальная сумма</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_max_summ" value="{$settings->loan_max_summ}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Минимальный срок</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_min_period" value="{$settings->loan_min_period}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Срок по умолчанию</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_default_period" value="{$settings->loan_default_period}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Максимальный срок</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_max_period" value="{$settings->loan_max_period}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Процент %/ день</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_default_percent" value="{$settings->loan_default_percent}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Пени %/ год</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_peni" value="{$settings->loan_peni}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Ответственность %/ день</label>
                                <div class="">
                                    <input type="text" class="form-control" name="loan_charge_percent" value="{$settings->loan_charge_percent}" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                                                            
                    <hr class="mb-3 mt-3" />
                    
                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Пролонгация
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Срок пролонгации</label>
                                <div class="">
                                    <input type="text" class="form-control" name="prolongation_period" value="{$settings->prolongation_period}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Страховка, руб</label>
                                <div class="">
                                    <input type="text" class="form-control" name="prolongation_amount" value="{$settings->prolongation_amount}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            
                        </div>
                    </div>
                                                            
                    <hr class="mb-3 mt-3" />
                    
                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Цессия
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Срок просрочки, дней</label>
                                <div class="">
                                    <input type="text" class="form-control" name="cession_period" value="{$settings->cession_period}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Общая сумма выплат, % ОД</label>
                                <div class="">
                                    <input type="text" class="form-control" name="cession_amount" value="{$settings->cession_amount}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            
                        </div>
                    </div>
                    
                    <hr class="mb-3 mt-3" />

                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Порог возраста для заявки
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Минимальный возраст</label>
                                <div class="">
                                    <input type="text" class="form-control" name="min_age" value="{$settings->min_age}" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Максимальный возраст</label>
                                <div class="">
                                    <input type="text" class="form-control" name="max_age" value="{$settings->max_age}" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-3 mt-3" />
                    
                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Отчеты
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Email для отправки ежедневного отчета</label>
                                <div class="">
                                    <input type="text" class="form-control" name="report_email" value="{$settings->report_email}" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            
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




