{$meta_title="Пользователи" scope=parent}

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
                    Пользователи
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Пользователи</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {if in_array('create_managers', $manager->permissions)}
                <a href="manager" class="btn float-right hidden-sm-down btn-success">
                    <i class="mdi mdi-plus-circle"></i> 
                    Создать пользователя
                </a>
                {/if}
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Список пользователей</h4>
                        <div class="table-responsive">
                            <table class="table no-wrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Пользователь</th>
                                        <th>IP адрес</th>
                                        <th>Активность</th>
                                        <th>Роль</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $managers as $m}
                                    <tr class="{if $m->blocked}bg-light-danger{/if}">
                                        <td>{$m->id}</td>
                                        <td>
                                            <a href="manager/{$m->id}">{$m->name}</a>
                                            {if $m->blocked}<br /><span class="badge badge-danger">Заблокирован</span>{/if}
                                        </td>
                                        <td>{$m->last_ip}</td>
                                        <td>
                                            {if $m->last_visit}
                                                {$m->last_visit|date} {$m->last_visit|time}
                                            {/if}
                                        </td>
                                        <td>
                                            {$label_class="info"}
                                            {if $m->role == 'developer' || $m->role == 'technic'}{$label_class="danger"}{/if}
                                            {if $m->role == 'admin' || $m->role == 'chief_collector' || $m->role == 'team_collector'}{$label_class="success"}{/if}
                                            {if $m->role == 'verificator' || $m->role == 'user'}{$label_class="warning"}{/if}
                                            {if $m->role == 'collector'}{$label_class="primary"}{/if}
                                            
                                            <span class="label label-{$label_class}">
                                                {if $roles[$m->role]}
                                                    {$roles[$m->role]}
                                                {else}
                                                    {$m->role}
                                                {/if}
                                            </span> 
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
        <!-- Row -->
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