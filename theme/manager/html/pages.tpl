{$meta_title='Страницы' scope=parent}

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
                <h3 class="text-themecolor mb-0 mt-0">Страницы</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Страницы</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <a href="page" class="btn float-right hidden-sm-down btn-success">
                    <i class="mdi mdi-plus-circle"></i> 
                    Добавить страницу
                </a>
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
                        <h4 class="card-title">Страницы</h4>
                        
                        <table class="table">
                            {foreach $pages as $page}
                            <tr>
                                <td>
                                    <a href="page/{$page->id}">{$page->id}</a>
                                </td>
                                <td>
                                    <a href="page/{$page->id}">{$page->name|escape}</a>
                                </td>
                                <td>
                                    {$page->url|escape}
                                </td>
                            </tr>
                            {/foreach}
                        </table>
                        
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