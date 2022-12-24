{$meta_title = 'Причины закрытия тикетов' scope=parent}

{capture name='page_styles'}
<link href="theme/manager/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="theme/manager/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="theme/manager/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">

<!-- Color picker plugins css -->
<link href="theme/manager/assets/plugins/jquery-asColorPicker-master/dist/css/asColorPicker.css" rel="stylesheet">
    
<style>
    .js-text-admin-name,
    .js-text-client-name {
//        max-width:300px
    }
    .color-badge {
        display:inline-block;
        width:64px;
        height:24px;
        margin-right:20px;
        vertical-align:top;
    }
</style>
{/capture}

{capture name='page_scripts'}

    <script src="theme/manager/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/manager/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="theme/manager/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    

    <!-- Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/moment/moment.js?v=1.1"></script>
    <script src="theme/manager/assets/plugins/jquery-asColor/dist/jquery-asColor.js?v=1.1"></script>
    <script src="theme/manager/assets/plugins/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js?v=1.1"></script>
    
    <script src="theme/manager/js/apps/collector_tags.app.js?v=1.1"></script>
    
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
                <h3 class="text-themecolor mb-0 mt-0">Причины закрытия тикетов</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Причины закрытия тикетов</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </button>

            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
            
            <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <h6 class="card-subtitle"></h6>
                        <div class="table-responsive m-t-40">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">    
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                        <tr>
                                            <th class="">ID</th>
                                            <th class="">Название</th>
                                            <th class="">Цвет</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        
                                        {foreach $tags as $st}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$st->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-name">
                                                    {$st->name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$st->id}" />
                                                    <input type="text" class="form-control form-control" name="name" value="{$st->name|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-color">
                                                    <div class="color-badge" style="background:{$st->color}"></div>
                                                    {$st->color|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="colorpicker form-control form-control" name="color" value="{$st->color}" />
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                    <a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        {/foreach}
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>

    {include file='footer.tpl'}

</div>

<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Добавить новый тег</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="name" id="name" value="" />
                    </div>
                    <div class="form-group">
                        <label for="color" class="control-label">Цвет:</label>
                        <input type="text" class="form-control colorpicker" name="color" id="color" value="#000000" />
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>