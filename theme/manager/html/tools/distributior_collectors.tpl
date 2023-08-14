{$meta_title='Распределение по коллекторам' scope=parent}

{capture name='page_scripts'}

<script>

        function reload_func() {
            location.reload()
        }

        $(function () {            

            $('.cancel_distributions').on('click', function (e) {
                e.preventDefault();

                let ts = $('.cancel_distributions').data("cancel");

                $.ajax({
                    method: 'POST',
                    data: {
                        action: 'cancel_distributions',
                        ts: ts
                    },
                    success: function (ok) {


                        Swal.fire({
                            text: 'Распределения успешно отменены',
                            type: 'success'
                        });
                        setTimeout(reload_func, 2500);
                    }
                });
            });

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
                <h3 class="text-themecolor mb-0 mt-0">Распределение по коллекторам</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/tools">Инструменты</a></li>
                    <li class="breadcrumb-item active">Распределение по коллекторам</li>
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

                        <table class="table">
                            <tr>
                                <td>
                                    <b>№</b>
                                </td>
                                <td>
                                    <b>Кто распредилил</b>
                                </td>
                                <td>
                                   <b>Когда распределил</b>
                                </td>
                                <td>
                                   <b>Количество распределенных</b>
                                </td>
                                <td>
                                   <b>Отмена</b>
                                </td>
                                <td>
                                   <b>Отчет</b>
                                </td>
                            </tr>

                            {$i = 1}
                            {foreach $movings_groups as $movings_group}
                            <tr>
                                <td>
                                    {$i++}
                                </td>
                                <td>
                                    {$movings_group->initiator_name}
                                </td>
                                <td>
                                   {$movings_group->timestamp_group_movings}
                                </td>
                                <td>
                                   {$movings_group->cou}
                                </td>
                                <td>
                                    {if $i == 2}
                                        <span class="cancel_distributions" data-cancel="{str_replace(" ", "_", $movings_group->timestamp_group_movings)}" style="color: red; cursor:pointer;">Отменить</span>
                                    {/if}
                                </td>
                                <td>
                                    <a href="tools/distributior_collectors_doc/?ts={str_replace(" ", "_", $movings_group->timestamp_group_movings)}" style="cursor:pointer; background: #7460ee; border-radius: 5px; padding: 7px; color: white;">Показать отчет</a>
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

