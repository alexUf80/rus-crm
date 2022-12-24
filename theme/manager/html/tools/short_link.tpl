{$meta_title='Короткие ссылки' scope=parent}

{capture name='page_scripts'}

<script>
        $(function () {

            $(document).on('click', '.js-open-add-modal', function (e) {
                var iditem = $(this).attr('data-id');
                var urlitem = $(this).attr('data-url');
                var linkitem = $(this).attr('data-link');

                $("#new-url").attr('value', urlitem);
                $("#new-link").attr('value', linkitem);
                $("#new-idlink").attr('value', iditem);

                e.preventDefault();

                $('#modal_edit_link').modal();
            });

            $(document).on('click', '.js-del-link', function (e) {
                var iditem = $(this).attr('data-id');

                $.ajax({
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                    "action": "del_link",
                    "id_link": iditem 
                    },
                    success: function (ok) {

                        
                    }
                });
                location.reload();
            });

            $('#form_add_item').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    method: 'POST',
                    dataType: 'JSON',
                    data: $(this).serializeArray(),
                    success: function (ok) {

                        $('#modal_edit_link').modal('hide');

                        if (ok.resp == 'success') {

                            Swal.fire({
                                text: 'Ссылка успешно обновлена',
                                type: 'success'
                            });
                            location.reload();
                        }
                        else {
                            Swal.fire({
                                text: 'Ошибка',
                                type: 'error'
                            })
                        }
                    }
                });
            })
        })
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
                <h3 class="text-themecolor mb-0 mt-0">Ссылки</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/tools">Инструменты</a></li>
                    <li class="breadcrumb-item active">Ссылки</li>
                </ol>
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

                        <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data">

                            <div class="form-body">

                                <div class="row">

                                    {if $message_success}
                                    <div class="col-12">
                                        <div class="alert alert-success">{$message_success}</div>
                                    </div>
                                    {/if}

                                    {if $message_error}
                                    <div class="col-12">
                                        <div class="alert alert-danger">{$message_error}</div>
                                    </div>
                                    {/if}

                                    <div class="col-12">
                                        <div class="alert alert-secondary">Преобразование ссылки вида <b>https://www.youtube.com/watch?v=dQw4w9WgXcQ</b> в <b>https://barents-finans.ru/go/XXXXXX</b></div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Сокращение</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="url" value="{$page->url}" placeholder="XXXXXX">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Ссылка</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="link" value="{$page->link}" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                                            </div>
                                        </div>
                                    </div>



                                </div>
                            </div>
                            <br>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" class="btn btn-success">Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6"> </div>
                                </div>
                            </div>

                        </form>

                        <br>

                        <h4 class="card-title">Ссылки</h4>

                        <table class="table">
                            {$i = 1}
                            {foreach $pages as $key => $page}
                            <tr>
                                <td>
                                    {$i++}
                                </td>
                                <td>
                                    https://barents-finans.ru/go/{$page->url}
                                </td>
                                <td>
                                    {$page->link}
                                </td>
                                <td>
                                    <button class="btn float-right hidden-sm-down btn-warning js-open-add-modal" data-id="{$page->id}" data-url="{$page->url}" data-link="{$page->link}">
                                    Редактировать
                                    </button>
                                </td>
                                <td>
                                
                                    <button class="btn float-right hidden-sm-down btn-danger js-del-link" data-id="{$page->id}">
                                        Удалить
                                    </button>
                                    
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

<div id="modal_edit_link" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Изменить короткую ссылку</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">

                    <input type="hidden" name="action" value="change_link">

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="url" class="control-label">Сокращение:</label>
                        <input type="text" class="form-control" name="url" id="new-url" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="link" class="control-label">Ссылка</label>
                        <input type="text" class="form-control" name="link" id="new-link" value=""/>
                    </div>

                    <input id="new-idlink" type="hidden" value="" name="idlink">

                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light float-right">Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>