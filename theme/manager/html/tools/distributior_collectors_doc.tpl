{$meta_title='Распределение по коллекторам' scope=parent}

{capture name='page_scripts'}

<script>
        $(function () {

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
                    <li class="breadcrumb-item"><a href="/tools/distributior_collectors">Распределение по коллекторам</a></li>
                    <li class="breadcrumb-item active">Список распределения</li>
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

                        <b>{$timestamp}</b>
                        <table class="table" style="margin-top: 20px;">
                            <tr>
                                <td>
                                    <b>№</b>
                                </td>
                                <td>
                                    <b>Кто распредилил</b>
                                </td>
                                <td>
                                   <b>Отправитель</b>
                                </td>
                                <td>
                                   <b>Получатель</b>
                                </td>
                                <td>
                                   <b>Клиент</b>
                                </td>
                                <td>
                                   <b>№ договора</b>
                                </td>
                                <td>
                                   <b>Дней просрочки</b>
                                </td>
                            </tr>

                            {$i = 1}
                            {foreach $movings_groups_items as $movings_groups_item}
                            <tr>
                                <td>
                                    {$i++}
                                </td>
                                <td>
                                    {$movings_groups_item->initiator_name}
                                </td>
                                <td>
                                   {$movings_groups_item->from_manager_name} - 
                                   ({$collection_statuses[$movings_groups_item->from_manager_collection_status_id]})
                                </td>
                                <td>
                                   {$movings_groups_item->manager_name} -
                                   ({$collection_statuses[$movings_groups_item->manager_collection_status_id]})
                                </td>
                                <td>
                                    {$movings_groups_item->user->lastname} {$movings_groups_item->user->firstname} 
                                    {$movings_groups_item->user->patronymic}
                                </td>
                                <td>
                                    {$movings_groups_item->contract->number}
                                </td>
                                <td>
                                    {$movings_groups_item->expired_days}
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

