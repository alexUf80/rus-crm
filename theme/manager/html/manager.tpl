{if $user->id}
    {$meta_title="Профиль пользователя `$user->name`" scope=parent}
{else}
    {$meta_title="Новый пользователь" scope=parent}
{/if}

{capture name='page_scripts'}
    
    <style type="text/css">
        .btn-outline-success.focus,.btn-outline-success:focus,.btn-outline-success:hover {
        background: #ffffff;
        border-color: #55ce63;
        color: #55ce63
    }
    </style>

    <script>

        
        $('.js-filter-status').click(function(e){
            e.preventDefault();
        
            var _id = $(this).data('status');
            
            if ($(this).hasClass('active'))
            {
                $(this).removeClass('active')

                $('.js-status-item').fadeIn();

            }
            else
            {
                $('.js-filter-status.active').removeClass('active')
            
                $(this).addClass('active');
                
                $('.js-status-item').hide();
                $('.js-status-'+_id).fadeIn();
            }
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
                    {if $user->id}Профиль {$user->name|escape}
                    {else}Создать нового пользователя{/if}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="managers">Пользователи</a></li>
                    <li class="breadcrumb-item active">Профиль</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
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
            <!-- Column -->
            <div class="col-md-12 col-lg-4 col-xlg-3">
                <div class="card">
                    <div class="card-body">
                        <center class="mt-4"> 
                        
                            <h4 class="card-title mt-2">{$user->name}</h4>
                            <h6 class="card-subtitle">
                                {$roles[$user->role]}
                            </h6>
                            {*}
                            <div class="row text-center justify-content-md-center">
                                <div class="col-4"><a href="javascript:void(0)" class="link"><i class="icon-people"></i> <font class="font-medium">254</font></a></div>
                                <div class="col-4"><a href="javascript:void(0)" class="link"><i class="icon-picture"></i> <font class="font-medium">54</font></a></div>
                            </div>
                            {*}
                        </center>
                    </div>
                    <div>
                        <hr> </div>
                    <div class="card-body"> 
                        <small class="text-muted">Последний IP адрес</small>
                        <h6>{$user->last_ip}</h6> 
                        <small class="text-muted p-t-30 db">Последняя активность</small>
                        <h6>
                        {if $user->last_visit}
                            {$user->last_visit|date} {$user->last_visit|time}
                        {/if}
                        </h6>                         
                    </div>
                    
                    {if $user->id && in_array('block_manager', $manager->permissions)}
                    <div class="mt-2 pt-2 pb-2 text-center">
                        <button {if $user->blocked}style="display:none"{/if} class="btn btn-danger btn-lg js-block-button" data-manager="{$user->id}">Заблокировать</button>
                        <button {if !$user->blocked}style="display:none"{/if} class="btn btn-success btn-lg js-unblock-button" data-manager="{$user->id}">Разблокировать</button>
                    </div>
                    {/if}
                </div>
            </div>
            <!-- Column -->
            <!-- Column -->
            <div class="col-md-12 col-lg-8 col-xlg-9">
                <div class="card">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#settings" role="tab">Основные</a> </li>
                        <li class="nav-item" {if $user->role!='team_collector'}style="display:none"{/if}> 
                            <a class="nav-link" data-toggle="tab" href="#team" role="tab">Команда</a> 
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <form class="form-horizontal" method="POST">
                    <div class="tab-content">
                        
                        <div class="tab-pane active" id="settings" role="tabpanel">
                            <div class="card-body">
                                
                                    <input type="hidden" name="id" value="{$user->id}" />
                                    
                                    {if $errors}
                                    <div class="col-md-12">
                                        <ul class="alert alert-danger">
                                            {if in_array('empty_role', (array)$errors)}<li>Выберите роль пользователя!</li>{/if}
                                            {if in_array('empty_name', (array)$errors)}<li>Укажите имя пользователя!</li>{/if}
                                            {if in_array('empty_login', (array)$errors)}<li>Укажите логин для входа!</li>{/if}
                                            {if in_array('empty_password', (array)$errors)}<li>Укажите пароль!</li>{/if}
                                            {if in_array('name_1c_not_found', (array)$errors)}<li>Имя для обмена В 1С не найдено, проверьте правильность написания!</li>{/if}
                                        </ul>
                                    </div>
                                    {/if}
                                    
                                    {if $message_success}
                                    <div class="col-md-12">
                                        <div class="alert alert-success">
                                            {if $message_success == 'added'}Новый пользователь добавлен{/if}
                                            {if $message_success == 'updated'}Данные сохранены{/if}
                                        </div>
                                    </div>
                                    {/if}
                                    
                                    <div class="form-group {if in_array('empty_role', (array)$errors)}has-danger{/if}">
                                            {if $user->id != $manager->id || ($user->id == $manager->id && in_array($manager->role, ['admin', 'developer']))}
                                        <label class="col-sm-12">Роль</label>
                                        <div class="col-sm-12">
                                            <select name="role" class="form-control form-control-line" required="true">
                                                <option value=""></option>
                                                {foreach $roles as $role}
                                                    <option value="{$role->id}"
                                                            {if $user->role == $role->name}selected{/if}>{$role->translate}</option>
                                                {/foreach}
                                            </select>
                                            {if in_array('empty_role', (array)$errors)}<small class="form-control-feedback">Выберите роль!</small>{/if}
                                        </div>
                                        {else}
                                        <input type="hidden" name="role" value="{$user->role}" />
                                        {/if}
                                    </div>
                                    <div class="form-group {if in_array('empty_name', (array)$errors)}has-danger{/if}">
                                        <label class="col-md-12">Пользователь</label>
                                        <div class="col-md-12">
                                            <input type="text" name="name" value="{$user->name|escape}" class="form-control form-control-line" required="true" />
                                            {if in_array('empty_name', (array)$errors)}<small class="form-control-feedback">Укажите имя!</small>{/if}
                                        </div>
                                    </div>
                                    <div class="form-group {if in_array('empty_login', (array)$errors)}has-danger{/if}">
                                        <label for="login" class="col-md-12">Логин для входа</label>
                                        <div class="col-md-12">
                                            <input type="text" id="login" name="login" value="{$user->login|escape}" class="form-control form-control-line" required="true" />
                                            {if in_array('empty_login', (array)$errors)}<small class="form-control-feedback">Укажите логин!</small>{/if}
                                        </div>
                                    </div>
                                    <div class="form-group {if in_array('empty_password', (array)$errors)}has-danger{/if}">
                                        <label class="col-md-12">{if $user->id}Новый пароль{else}Пароль{/if}</label>
                                        <div class="col-md-12">
                                            <input type="password" name="password" value="" class="form-control form-control-line" {if !$user->id}required="true"{/if} />
                                            {if in_array('empty_password', (array)$errors)}<small class="form-control-feedback">Укажите пароль!</small>{/if}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Email</label>
                                        <div class="col-md-12">
                                            <input type="text" name="email" value="{$user->email}" class="form-control form-control-line" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-12">Mango-office внутренний номер</label>
                                        <div class="col-md-12">
                                            <input type="text" name="mango_number" value="{$user->mango_number}" class="form-control form-control-line" />
                                        </div>
                                    </div>
                                    {if in_array($user->role, ['collector', 'team_collector'])}
                                        {if in_array($manager->role, ['senior collector','admin','developer'])}
                                            <div class="form-group">
                                                <label class="col-md-12">Статусы договоров для коллекторов</label>
                                                <div class="col-sm-12">
                                                    <select name="collection_status_id"
                                                            class="form-control form-control-line">
                                                        {foreach $periods as $period}
                                                            <option value="{$period->id}"
                                                                    {if $user->collection_status_id == $period->id}selected{/if}>{$period->name}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        {else}
                                            <div class="form-group">
                                                <label class="col-md-12">Статусы договоров для коллекторов</label>
                                                <div class="col-sm-12">
                                                    <p>{$collection_statuses[$user->collection_status_id]}</p>
                                                    <input type="hidden" name="collection_status_id"
                                                           value="{$user->collection_status_id}"/>
                                                </div>
                                            </div>
                                        {/if}
                                    {/if}
                                    {if in_array($manager->role, ['developer', 'admin'])}
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <button class="btn btn-success" type="submit">Сохранить</button>
                                            </div>
                                        </div>
                                    {/if}
                            </div>
                        </div>

                        <div class="tab-pane" id="team" role="tabpanel">
                            <div class="card-body">
                                <div class="form-group pl-3 pr-3">
                                    <div class="clearfix pb-3">
                                        <div class="float-left">
                                            <h5>Команда коллектора</h5>
                                        </div>
                                        <div class="float-right">
                                            {foreach $collection_statuses as $cs_id => $cs}
                                                {*}
                                                {if in_array($cs_id, (array)$collection_manager_statuses)}
                                                {*}
                                                    <a data-status="{$cs_id+1}" class="js-filter-status btn btn-sm btn-outline-success" href="javascript:void();">{$cs->name}</a>
                                                {*}
                                                {/if}
                                                {*}
                                            {/foreach}
                                        </div>
                                    </div>
                                    
                                    <table class="table">
                                    {foreach $managers as $m}
                                        {if $m->role == 'collector'}
                                        <tr class="js-status-item js-status-{$m->collection_status_id}">
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input {if !in_array($manager->role, ['chief_collector','admin','developer'])}disabled="true"{/if} class="custom-control-input" type="checkbox" name="team_id[]" id="team_id_{$m->id}" value="{$m->id}" {if in_array($m->id, (array)$user->team_id)}checked="true"{/if} />
                                                    <label class="custom-control-label" for="team_id_{$m->id}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <a class="{if $m->blocked}text-danger{/if}" target="_blank" href="manager/{$m->id}">
                                                    <strong>{$m->name|escape}</strong>
                                                </a>                                        
                                                {if $m->blocked}<span class="label label-danger">Заблокирован</span>{/if}
                                            </td>
                                            <td>
                                                <label class="label label-primary">
                                                    {foreach $collection_statuses as $cs_id => $cs}
                                                        {if $cs_id+1 == $m->collection_status_id}
                                                            {$cs->name}
                                                        {/if}    
                                                    {/foreach}
                                                </label>
                                            </td>
                                            <td>
                                                {foreach $managers as $man}
                                                    {if $man->role=='team_collector' && in_array($m->id, (array)$man->team_id)}
                                                    <small>{$man->name|escape}</small>
                                                    <br />
                                                    {/if}
                                                {/foreach}
                                                
                                            </td>
                                        </tr>
                                        {/if}
                                    {/foreach}    
                                    </table>

                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <button class="btn btn-success" type="submit">Сохранить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    </form>
                </div>
            </div>
            <!-- Column -->
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