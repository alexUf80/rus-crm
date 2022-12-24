{$meta_title="Судблок документы" scope=parent}

{capture name='page_styles'}
<link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="theme/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="theme/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
<style>
    .js-text-admin-name,
    .js-text-client-name {
//        max-width:300px
    }
</style>
{/capture}

{capture name='page_scripts'}

    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    
    <script src="theme/{$settings->theme|escape}/js/apps/sudblock_documents.js"></script>
    
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
                    Судблок документы
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Судблок</li>
                    <li class="breadcrumb-item active">Документы</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <!--button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </button-->                
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
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
                                            <th class="">Документ</th>
                                            <th class="">Поставщик</th>
                                            <th class="">Назначение</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        
                                        {foreach $documents as $doc}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$doc->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-name">
                                                    {$doc->name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$doc->id}" />
                                                    <input type="hidden" name="base" value="1" />
                                                    <input type="text" class="form-control form-control-sm" name="name" value="{$doc->name|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-filename">
                                                    <a href="{$config->root_url}/files/sudblock/{$doc->filename}" target="_blank">{$doc->filename|escape}</a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="{$config->root_url}/files/sudblock/{$doc->filename}" target="_blank">{$doc->filename|escape}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-provider">
                                                    {$doc->provider}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select name="provider" class="form-control form-control-sm">
                                                        <option value="Наличное плюс" {if $doc->provider == 'Наличное плюс'}selected{/if}>Наличное плюс</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-block">
                                                    {if $doc->block == 'sud'}Суд
                                                    {elseif $doc->block == 'fssp'}ФССП
                                                    {/if}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select name="block" class="form-control form-control-sm">
                                                        <option value="sud" {if $doc->block == 'sud'}selected{/if}>Суд</option>
                                                        <option value="fssp" {if $doc->block == 'fssp'}selected{/if}>ФССП</option>
                                                    </select>
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


<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Добавить документ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item"  enctype="multipart/form-data">
                    
                    <input type="hidden" name="action" value="add" />
                    <input type="hidden" name="base" value="1" />
                    
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Название  документа:</label>
                        <input type="text" class="form-control" name="name" id="name" value="" />
                    </div>
                    <div class="form-group">
                        <label for="provider" class="control-label">Поставщик:</label>
                        <select name="provider" id="provider" class="form-control">
                            <option value="Наличное плюс" >Наличное плюс</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="block" class="control-label">Поставщик:</label>
                        <select name="block" id="block" class="form-control">
                            <option value="sud" >Суд</option>
                            <option value="fssp">ФССП</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="hidden" name="run" value="1" />
                                <input type="file" name="file" class="custom-file-input" id="file_input" />
                                <label style="white-space: nowrap;overflow: hidden;" class="custom-file-label" for="">Выбрать</label>
                            </div>
                        </div>
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