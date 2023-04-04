{$meta_title = 'Интеграции' scope=parent}

{capture name='page_scripts'}
    <script>
        $(function () {
            $('.addEventModal, .editEventModal').on('click', function () {
                $('#addEventModal').modal();

                if ($(this).hasClass('addEventModal')) {
                    $('.modal-title').text('Добавить событие');
                    $('#addEventForm').find('input[class="btn btn-success float-right"]').removeClass('editEvent');
                    $('#addEventForm').find('input[class="btn btn-success float-right"]').addClass('addEvent');
                    $('#addEventForm').find('input[name="action"]').attr('value', 'addEvent');
                } else {

                    let id = $(this).attr('data-id');

                    $.ajax({
                        method: 'POST',
                        data: {
                            id: id,
                            action: 'getEvent'
                        },
                        success: function (eventName) {
                            $('#addEventForm').find('input[name="name"]').val(eventName);
                        }
                    });

                    $('.modal-title').text('Редактировать событие');
                    $('#addEventForm').find('input[class="btn btn-success float-right"]').removeClass('addEvent');
                    $('#addEventForm').find('input[class="btn btn-success float-right"]').addClass('editEvent');
                    $('#addEventForm').find('input[name="action"]').attr('value', 'editEvent');
                    $('#addEventForm').find('input[name="id"]').attr('value', id);
                }
            });

            $(document).on('click', '.addEvent, .editEvent', function () {
                let form = $(this).closest('form').serialize();

                $.ajax({
                    method: 'POST',
                    data: form,
                    success: function () {
                        location.reload();
                    }
                });
            });

            $('.deleteEvent').on('click', function () {
                let id = $(this).attr('data-id');

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'deleteEvent',
                        id: id
                    },
                    success: function () {
                        location.reload();
                    }
                });
            });
        });
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
                    События для ремайнедров
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">События для ремайнедров</li>
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
                    <div class="big-table">
                        <table id="config-table"
                               class="table table-hover">
                            <thead style="background-color: #009efb">
                            <tr>
                                <th style="width: 20%"
                                    class="sgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'index_number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'index_number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                    {if $sort == 'index_number_asc'}
                                        <a href="{url page=null sort='index_number_desc'}">#</a>
                                    {else}
                                        <a style="color: white" href="{url page=null sort='index_number_asc'}">#</a>
                                    {/if}
                                </th>
                                <th style="width: 30%"
                                    class="jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'index_number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'index_number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                    {if $sort == 'segment_asc'}
                                        <a style="color: white"
                                           href="{url page=null sort='name desc'}">Событие</a>
                                    {else}
                                        <a style="color: white"
                                           href="{url page=null sort='name asc'}">Событие</a>
                                    {/if}
                                </th>
                                <th style="width: 1%"></th>
                                <th style="width: 30%"></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $remindersEvents as $event}
                                <tr>
                                    <td>{$event->id}</td>
                                    <td>{$event->name}</td>
                                    <td>
                                        <div class="btn btn-outline-warning editEventModal" data-id="{$event->id}"><i
                                                    class=" fas fa-edit"></i></div>
                                    </td>
                                    <td>
                                        <div class="btn btn-outline-danger deleteEvent" data-id="{$event->id}"><i
                                                    class=" fas fa-trash"></i></div>
                                    </td>
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
                    <div class="btn btn-outline-success addEventModal">Добавить</div>
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

<div id="addEventModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <form id="addEventForm">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="id">
                    <div class="form-group" style="display:flex; flex-direction: column">
                        <div class="form-group">
                            <label>Название</label>
                            <input type="text" name="name"
                                   class="form-control"/>
                        </div>
                        <div>
                            <input type="button" class="btn btn-danger cancel" data-dismiss="modal" value="Отмена">
                            <input type="button" class="btn btn-success float-right" value="Сохранить">
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
