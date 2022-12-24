{$meta_title = 'Интеграции' scope=parent}

{capture name='page_scripts'}
    <script type="text/javascript">

    </script>
    <script>
        $(function () {

            $(document).on('click', '.js-open-add-modal', function (e) {
                e.preventDefault();

                $('#modal_add_item').modal();
            });

            $('.cancel_modal').on('click', function (e) {
                $('.modal-input').val('');
            });

            $('#form_add_item').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    method: 'POST',
                    dataType: 'JSON',
                    data: $(this).serialize(),
                    success: function (ok) {

                        console.log(ok);

                        $('#modal_add_item').modal('hide');
                        $('.modal-input').val('');

                        if (ok.resp == 'success') {

                            Swal.fire({
                                text: 'Интеграция успешно добавлена',
                                type: 'success'
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Ошибка',
                                type: 'error'
                            })
                        }
                    }
                });
            });

            $('#form_update_item').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    method: 'POST',
                    dataType: 'JSON',
                    data: $(this).serialize(),
                    success: function (ok) {

                        console.log(ok);

                        $('#modal_update_item').modal('hide');
                        $('.modal-input').val('');

                        if (ok.resp == 'success') {

                            Swal.fire({
                                text: 'Интеграция успешно Обновлена',
                                type: 'success'
                            });
                        }
                        else {
                            Swal.fire({
                                text: 'Ошибка',
                                type: 'error'
                            })
                        }
                    }
                });
            });

            $('.delete_integration').on('click', function (e) {
                e.preventDefault();

                let integration_id = $(this).data('id');

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'delete_integration',
                        integration_id: integration_id
                    },
                    success: function (ok) {


                        Swal.fire({
                            text: 'Интеграция успешно удалена',
                            type: 'success'
                        });

                        $('tr[id=' + integration_id + ']').remove();
                    }
                });
            });

            $('.edit_integration').on('click', function (e) {
                e.preventDefault();

                let integration_id = $(this).data('id');
                $("#idinteg").attr('value', integration_id);

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'get_integration',
                        integration_id: integration_id
                    },
                    dataType: 'JSON',
                    success: function (integration) {

                        for(let element in integration)
                            {
                                $('input[name='+element+']').val(integration[element]);
                            }
                    }
                });

                $('#modal_update_item').modal();

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
                <h3 class="text-themecolor mb-0 mt-0">
                    Интеграции
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/tools">Инструменты</a></li>
                    <li class="breadcrumb-item active">Интеграции</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
        <form class="" method="POST">

            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Виды интеграций
                            </h3>
                        </div>

                        <table id="config-table" class="table display table-striped dataTable">
                            <thead>
                            <tr>
                                <th class="">Номер</th>
                                <th class="">Название интеграции</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $integrations as $integration}
                                <tr style="width: 100%" id="{$integration->id}">
                                    <td>
                                        {$integration->id}
                                    </td>
                                    <td>
                                        <div class="js-visible-view js-text-name">
                                            {$integration->name}
                                        </div>
                                    </td>
                                    <td><input type="button" data-id="{$integration->id}"
                                               class="btn btn-warning edit_integration" value="Изменить"></td>
                                    <td><input type="button" data-id="{$integration->id}"
                                               class="btn btn-danger delete_integration" value="Удалить"></td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <hr class="mb-3 mt-3"/>

            <div class="row">
                <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                    <div class="form-actions js-open-add-modal">
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Добавить</button>
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

<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <table class="table display table-striped dataTable">
                <thead>
                <tr>
                    <th>Параметр</th>
                    <th>Название UTM</th>
                    <th>Значение UTM</th>
                    <th>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </th>
                </tr>
                </thead>
                <tbody>
                <form method="POST" id="form_add_item">
                    <input type="hidden" name="action" value="add_integration">
                    <tr>
                        <td>Источник</td>
                        <td><input name="utm_source_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_source" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Канал</td>
                        <td><input name="utm_medium_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_medium" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Кампания</td>
                        <td><input name="utm_campaign_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_campaign" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Таргетинг</td>
                        <td><input name="utm_term_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_term" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Контент</td>
                        <td><input name="utm_content_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_content" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>
                            <button type="button" class="btn btn-danger waves-effect cancel_modal" data-dismiss="modal">Отмена
                            </button>
                        </td>
                        <td>

                        </td>
                        <td align="right">
                            <button type="submit" class="btn btn-success waves-effect waves-light float-right">
                                Сохранить
                            </button>
                        </td>
                    </tr>
                </form>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal_update_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <table class="table display table-striped dataTable">
                <thead>
                <tr>
                    <th>Параметр</th>
                    <th>Название UTM</th>
                    <th>Значение UTM</th>
                    <th>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </th>
                </tr>
                </thead>
                <tbody>
                <form method="POST" id="form_update_item">
                    <input type="hidden" name="action" value="update_integration">
                    <input id="idinteg" type="hidden" value="" name="integration_id">
                    <tr>
                        <td>Источник</td>
                        <td><input name="utm_source_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_source" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Канал</td>
                        <td><input name="utm_medium_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_medium" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Кампания</td>
                        <td><input name="utm_campaign_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_campaign" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Таргетинг</td>
                        <td><input name="utm_term_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_term" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>Контент</td>
                        <td><input name="utm_content_name" type="text" class="form-control modal-input"></td>
                        <td><input name="utm_content" type="text" class="form-control modal-input"></td>
                    </tr>
                    <tr>
                        <td>
                            <button type="button" class="btn btn-danger waves-effect cancel_modal" data-dismiss="modal">Отмена
                            </button>
                        </td>
                        <td>

                        </td>
                        <td align="right">
                            <button type="submit" class="btn btn-success waves-effect waves-light float-right">
                                Сохранить
                            </button>
                        </td>
                    </tr>
                </form>
                </tbody>
            </table>
        </div>
    </div>
</div>
