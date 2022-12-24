{$meta_title = 'Настройки периодов коллекшина' scope=parent}

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
                    Настройки периодов коллекшина
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Периоды коллекшина</li>
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
                        <div class="col-md-4">
                            {foreach $collection_statuses as $cs_id => $cs}
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class=" col-form-label">{$cs}</label>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="collection_periods[{$cs_id}]" value="{$collection_periods[$cs_id]}" placeholder="">
                                    </div>
                                </div>
                            </div>
                            {/foreach}
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




