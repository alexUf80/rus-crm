{$meta_title = 'Промокоды' scope=parent}

{capture name='page_styles'}
    <link href="theme/manager/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css"
          href="theme/manager/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/css/suggestions.min.css" rel="stylesheet"/>
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

{capture name='page_scripts'}
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="theme/manager/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/js/jquery.suggestions.min.js"></script>
    <script>
        $(function () {

            $('.add-promocode-modal').on('click', function () {
                $('#add_promocode_form')[0].reset();
                $('#add-promocode-modal').modal();

                $('.add_promocode').on('click', function () {
                    let form = $('#add_promocode_form').serialize();

                    $.ajax({
                        method: 'POST',
                        data: form,
                        success: function () {
                            location.reload();
                        }
                    })
                });
            });

            $('.delete').on('click', function () {

                let that = $(this);
                let code_id = that.attr('data-code');

                $.ajax({
                    method: 'POST',
                    data:{
                        action: 'delete',
                        code_id: code_id
                    },
                    success: function () {
                        that.closest('tr').remove();
                    }
                })
            });
        });
    </script>
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
                <h3 class="text-themecolor mb-0 mt-0">Промокоды</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/">Справочники</a></li>
                    <li class="breadcrumb-item active"><a href="/promocodes">Промокоды</a></li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right hidden-sm-down btn-success add-promocode-modal">
                    Добавить
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
                                <table id="config-table" class="table display table-striped dataTable"
                                       style="font-size: 14px">
                                    <thead>
                                    <tr>
                                        <th style="width: 90px"
                                            class="jsgrid-header-cell jsgrid-header-sortable{if $sort == 'id asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'id desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'id asc'}<a href="{url page=null sort='id desc'}">
                                                    ID</a>
                                            {else}<a href="{url page=null sort='id asc'}">ID</a>{/if}
                                        </th>
                                        <th style="width: 250px"
                                            class="jsgrid-header-cell jsgrid-header-sortable{if $sort == 'code asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'code desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'code asc'}<a href="{url page=null sort='code desc'}">
                                                    Код</a>
                                            {else}<a href="{url page=null sort='code asc'}">Код</a>{/if}
                                        </th>
                                        <th style="width: 200px"
                                            class="jsgrid-header-cell jsgrid-header-sortable{if $sort == 'term asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'term desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'term asc'}<a href="{url page=null sort='term desc'}">Срок
                                                действия</a>
                                            {else}<a href="{url page=null sort='term asc'}">Срок действия</a>{/if}
                                        </th>
                                        <th style="width: 150px"
                                            class="jsgrid-header-cell jsgrid-header-sortable{if $sort == 'is_active asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'is_active desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'is_active asc'}<a
                                                href="{url page=null sort='is_active desc'}">Активность</a>
                                            {else}<a href="{url page=null sort='is_active asc'}">Активность</a>{/if}
                                        </th>
                                        <th style="width: 150px"
                                            class="jsgrid-header-cell jsgrid-header-sortable{if $sort == 'discount asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'discount desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'discount asc'}<a
                                                href="{url page=null sort='discount desc'}">Скидка</a>
                                            {else}<a href="{url page=null sort='discount asc'}">Скидка</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable{if $sort == 'comment asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'comment desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'comment asc'}<a href="{url page=null sort='comment desc'}">
                                                    Комментарий</a>
                                            {else}<a href="{url page=null sort='comment asc'}">Комментарий</a>{/if}
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th style="width: 90px" class="jsgrid-header-cell">
                                            <input type="text" class="form-control searchable"></th>
                                        <th style="width: 250px" class="jsgrid-header-cell"><input type="text"
                                                                                                   class="form-control searchable">
                                        </th>
                                        <th style="width: 200px" class="jsgrid-header-cell"><input type="text"
                                                                                                   class="form-control searchable">
                                        </th>
                                        <th style="width: 150px" class="jsgrid-header-cell"><select
                                                    class="form-control">
                                                <option value="all">Все</option>
                                                <option value="all">Да</option>
                                                <option value="all">Нет</option>
                                            </select></th>
                                        <th style="width: 150px" class="jsgrid-header-cell">
                                            <input type="text" class="form-control searchable">
                                        </th>
                                        <th class="jsgrid-header-cell"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {if !empty($promocodes)}
                                        {foreach $promocodes as $code}
                                            <tr>
                                                <td class="jsgrid-header-cell" style="width: 90px">{$code->id}</td>
                                                <td class="jsgrid-header-cell" style="width: 250px">{$code->code}</td>
                                                <td class="jsgrid-header-cell" style="width: 200px">{$code->term}</td>
                                                <td class="jsgrid-header-cell"
                                                    style="width: 150px">{if $code->is_active == 1}Да{else}Нет{/if}</td>
                                                <td class="jsgrid-header-cell" style="width: 150px">{$code->discount}</td>
                                                <td class="jsgrid-header-cell">{$code->comment}</td>
                                            </tr>
                                        {/foreach}
                                    {/if}
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

<div id="add-promocode-modal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить промокод</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="alert" style="display:none"></div>
                <form method="POST" id="add_promocode_form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="code" class="control-label">Код</label>
                        <input type="text" class="form-control" name="code" id="code" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="term" class="control-label">Срок действия:</label>
                        <input type="text" class="form-control" name="term" id="term" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="is_active" class="control-label">Активность:</label>
                        <select id="is_active" name="is_active" class="form-control">
                            <option value="1">Да</option>
                            <option value="0">Нет</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discount" class="control-label">Скидка:</label>
                        <input type="text" class="form-control" name="discount" id="discount" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea style="height: 200px" type="text" class="form-control" name="comment"
                                  id="comment"></textarea>
                    </div>
                    <div>
                        <input type="button" class="btn btn-danger" data-dismiss="modal" value="Отмена">
                        <input type="button" class="btn btn-success add_promocode" value="Сохранить">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>