{$meta_title = 'Настройки рассылки Звонобота' scope=parent}

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
                    Настройки рассылки Звонобота
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки рассылки Звонобота</li>
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
                                Частота уведомлений
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <div class="col-md-6">
                                 <label class=" col-form-label">Раз в x часов</label>
                                <input type="text" class="form-control" name="zvonobot_period_calls" value="{$settings->zvonobot_period_calls}" placeholder="">                                    
                            </div>
                        </div>

                        <div class="col-12">
                            <br>
                            <h3 class="box-title">
                                Текст Сообщений
                            </h3>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">День (-3)</label>
                                <div class="">
                                    <textarea name="msg_zvonobot_d3" class="form-control" rows="4">{$settings->msg_zvonobot_d3}</textarea>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">День (-2)</label>
                                <div class="">
                                    <textarea name="msg_zvonobot_d2" class="form-control" rows="4">{$settings->msg_zvonobot_d2}</textarea>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">День (-1)</label>
                                <div class="">
                                    <textarea name="msg_zvonobot_d1" class="form-control" rows="4">{$settings->msg_zvonobot_d1}</textarea>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">День (0)</label>
                                <div class="">
                                    <textarea name="msg_zvonobot_d0" class="form-control" rows="4">{$settings->msg_zvonobot_d0}</textarea>
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




