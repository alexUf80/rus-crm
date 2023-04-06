{$meta_title = 'Интеграции' scope=parent}

{capture name='page_scripts'}
    <script src="https://cdn.jsdelivr.net/npm/jquery.maskedinput@1.4.1/src/jquery.maskedinput.min.js" type="text/javascript"></script>
    <script>
        $('input[name="timeToSend"]').mask('99:99');

        $(function () {
            $('.addReminderModal, .editReminderModal').on('click', function () {
                $('#ReminderModal').modal();

                if ($(this).hasClass('addReminderModal')) {
                    $('.modal-title').text('Добавить ремайндер');
                    $('#addReminderForm').find('input[class="btn btn-success float-right"]').removeClass('editReminder');
                    $('#addReminderForm').find('input[class="btn btn-success float-right"]').addClass('addReminder');
                    $('#addReminderForm').find('input[name="action"]').attr('value', 'addReminder');
                } else {

                    let id = $(this).attr('data-id');

                    $.ajax({
                        method: 'POST',
                        dataType: 'JSON',
                        data: {
                            id: id,
                            action: 'getReminder'
                        },
                        success: function (reminder) {
                            $('#addReminderForm').find('select[name="event"] option[value="' + reminder['eventId'] + '"]').prop('selected', true);
                            $('#addReminderForm').find('select[name="segment"] option[value="' + reminder['segmentId'] + '"]').prop('selected', true);
                            $('#addReminderForm').find('select[name="typeTime"] option[value="' + reminder['timeType'] + '"]').prop('selected', true);
                            $('#addReminderForm').find('input[name="count"]').val(reminder['countTime']);
                            $('#addReminderForm').find('input[name="msgSms"]').val(reminder['msgSms']);
                            $('#addReminderForm').find('input[name="msgZvon"]').val(reminder['msgZvon']);
                            $('#addReminderForm').find('input[name="timeToSend"]').val(reminder['timeToSend']);
                            $('#addReminderForm').find('input[name="id"]').attr('value', reminder['id']);
                        }
                    });

                    $('.modal-title').text('Редактировать событие');
                    $('#addReminderForm').find('input[class="btn btn-success float-right"]').removeClass('addReminder');
                    $('#addReminderForm').find('input[class="btn btn-success float-right"]').addClass('editReminder');
                    $('#addReminderForm').find('input[name="action"]').attr('value', 'updateReminder');
                    $('#addReminderForm').find('input[name="id"]').attr('value', id);
                }
            });
            $(document).on('click', '.addReminder, .editReminder', function () {
                let form = $(this).closest('form').serialize();

                $.ajax({
                    method: 'POST',
                    data: form,
                    success: function () {
                        location.reload();
                    }
                });
            });
            $('.deleteReminder').on('click', function () {
                let id = $(this).attr('data-id');

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'deleteReminder',
                        id: id
                    },
                    success: function () {
                        location.reload();
                    }
                });
            });
            $('.reminderSwitcher').on('change', function () {
                let id = $(this).attr('data-id');

                if ($(this).is(':checked'))
                    $(this).val(1);
                else
                    $(this).val(0);

                let value = $(this).val();

                $.ajax({
                    method: 'POST',
                    data: {
                        id: id,
                        value: value,
                        action: 'switchReminder'
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
                    Интеграции
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/tools">Инструменты</a></li>
                    <li class="breadcrumb-item active">Ремайндеры</li>
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
                               class="table table-hover"
                               style="display: inline-block; vertical-align: top; max-width: 100%; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch;">
                            <thead style="background-color: #009efb">
                            <tr>
                                <th class="col-2 jsgrid-header-cell jsgrid-align-right jsgrid-header-sortable {if $sort == 'index_number_desc'}jsgrid-header-sort jsgrid-header-sort-desc{elseif $sort == 'index_number_asc'}jsgrid-header-sort jsgrid-header-sort-asc{/if}">
                                    {if $sort == 'index_number_asc'}
                                        <a href="{url page=null sort='index_number_desc'}">#</a>
                                    {else}
                                        <a style="color: white" href="{url page=null sort='index_number_asc'}">#</a>
                                    {/if}
                                </th>
                                <th class="col-2">
                                    {if $sort == 'segment_asc'}
                                        <a style="color: white"
                                           href="{url page=null sort='segment_desc'}">Сегмент</a>
                                    {else}
                                        <a style="color: white"
                                           href="{url page=null sort='segment_asc'}">Сегмент</a>
                                    {/if}
                                </th>
                                <th class="col-2">
                                    {if $sort == 'event_asc'}
                                        <a style="color: white" href="{url page=null sort='event_desc'}">Событие</a>
                                    {else}
                                        <a style="color: white" href="{url page=null sort='event_asc'}">Событие</a>
                                    {/if}
                                </th>
                                <th class="col-2">
                                    {if $sort == 'message_asc'}
                                        <a style="color: white"
                                           href="{url page=null sort='message_desc'}">Сообщение СМС</a>
                                    {else}
                                        <a style="color: white"
                                           href="{url page=null sort='message_asc'}">Сообщение СМС</a>
                                    {/if}
                                </th>
                                <th class="col-2">
                                    {if $sort == 'message_asc'}
                                        <a style="color: white"
                                           href="{url page=null sort='message_desc'}">Сообщение Звонобот</a>
                                    {else}
                                        <a style="color: white"
                                           href="{url page=null sort='message_asc'}">Сообщение Звонобот</a>
                                    {/if}
                                </th>
                                <th class="col-4">
                                    {if $sort == 'updated_at_asc'}
                                        <a style="color: white"
                                           href="{url page=null sort='updated_at_desc'}">Изменен</a>
                                    {else}
                                        <a style="color: white"
                                           href="{url page=null sort='updated_at_asc'}">Изменен</a>
                                    {/if}
                                </th>
                                <th>

                                </th>
                                <th>

                                </th>
                                <th>

                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $reminders as $reminder}
                                <tr>
                                    <td>{$reminder->id}</td>
                                    <td>{$reminder->segment->name}</td>
                                    <td>{$reminder->event->name}</td>
                                    <td>{$reminder->msgSms}</td>
                                    <td>{$reminder->msgZvon}</td>
                                    <td>{$reminder->updated|date} {$reminder->updated|time}</td>
                                    <td>
                                        <div class="btn btn-outline-warning editReminderModal" data-id="{$reminder->id}"><i
                                                    class=" fas fa-edit"></i></div>
                                    </td>
                                    <td>
                                        <div class="btn btn-outline-danger deleteReminder" data-id="{$reminder->id}"><i
                                                    class=" fas fa-trash"></i></div>
                                    </td>
                                    <td>
                                        <div class="onoffswitch">
                                            <input
                                                    type="checkbox"
                                                    class="onoffswitch-checkbox reminderSwitcher"
                                                    {if $reminder->is_on}checked{/if}
                                                    id="reminder-{$reminder->id}"
                                                    data-id="{$reminder->id}"/>
                                            <label class="onoffswitch-label" for="reminder-{$reminder->id}">
                                                <span class="onoffswitch-inner"></span>
                                                <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>
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
                    <div class="btn btn-outline-success addReminderModal">Добавить</div>
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

<div id="ReminderModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <form id="addReminderForm">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="id">
                    <div class="form-group" style="display:flex; flex-direction: column">
                        <div class="form-group">
                            <label>Сегмент</label>
                            <select type="text" name="segment"
                                    class="form-control">
                                <option value="0">Выберите сегмент</option>
                                {foreach $remindersSegments as $segment}
                                    <option value="{$segment->id}">{$segment->name}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Событие</label>
                            <select type="text" name="event"
                                    class="form-control">
                                <option value="0">Выберите событие</option>
                                {foreach $remindersEvents as $event}
                                    <option value="{$event->id}">{$event->name}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Дней/Часов</label>
                            <select type="text" name="typeTime"
                                    class="form-control">
                                <option value="0">Выберите тип</option>
                                <option value="days">Дней</option>
                                <option value="hours">Часов</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Количество</label>
                            <input type="text" name="count"
                                   class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label>Время отправки (для просроченных)</label>
                            <input type="text" name="timeToSend"
                                   class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label>Сообщение (СМС)</label>
                            <input type="text" name="msgSms"
                                   class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label>Сообщение (Звонобот)</label>
                            <input type="text" name="msgZvon"
                                   class="form-control"/>
                        </div>
                        <div>
                            <input type="button" class="btn btn-danger" data-dismiss="modal" value="Отмена">
                            <input type="button" class="btn btn-success float-right" value="Сохранить">
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
