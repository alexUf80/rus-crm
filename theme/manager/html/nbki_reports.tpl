{$meta_title='Отчеты НБКИ' scope=parent}

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
                    <li class="breadcrumb-item active">Отчеты НБКИ</li>
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
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Отчеты НБКИ</h4>
                        
                        <table class="table">
                            <tr>
                                <th class="text-left">Создано</th>
                                <th class="text-left">Название</th>
                                <th class="text-left">Период</th>
                                <th></th>
                            </tr>
                            {foreach $reports as $report}
                            <tr>
                                <td class="text-left">
                                    <small>{$report->created|date} {$report->created|time}</small>
                                </td>
                                <td class="text-left">
                                    <strong>{$report->name|escape}</strong>
                                </td>
                                <td class="text-left">
                                    {$report->date_from|date} - {$report->date_to|date}
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-info" target="_blank" href="files/nbki/{$report->filename}" download="{$report->filename}.rutdf">
                                        <i class="fas fa-download"></i>
                                        <span> Скачать</span>
                                    </a>
                                </td>
                            </tr>
                            {/foreach}
                        </table>
                        
                        {include file='pagination.tpl'}
            
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