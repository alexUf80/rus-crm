{$meta_title="Договор №`$contract->number`" scope=parent}

{capture name='page_scripts'}
    
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
    $(function(){
        $('.singledate').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD.MM.YYYY'
            },
        });
    })
    </script>

    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/sudblock_contract.app.js"></script>
    
{/capture}

{capture name='page_styles'}

    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="theme/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />
    
    <style>
    .collapsed-icon:before {
        width:20px;
        height:20px;
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
        font-family:'Font Awesome 5 Free';
        content:"\f106";
        font-weight:900;
        color:#fff;
    }
    .collapsed .collapsed-icon:before {
        content:"\f107";
    }
    .comment-box {
        max-height: 500px;
        overflow-y:auto;
        overflow-x:hidden;
    }
    .comment-box a.open .mail-desc {
        white-space: normal!important;
    }
    </style>
{/capture}


<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Договор №{$contract->number}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="sudblock_contracts">Судблок</a></li>
                    <li class="breadcrumb-item active">Договор №{$contract->number}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                
            </div>
        </div>
        
        <div class="row" id="order_wrapper">
            <div class="col-lg-12">
                <div class="card card-outline-info">
                    
                    <div class="card-body">
                        
                        <div class="form-body">
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-2">
                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                        <input type="checkbox" class="custom-control-input js-workout-label" data-contract="{$contract->id}" id="workout_label" value="1" {if $contract->workout}checked{/if} />
                                        <label class="custom-control-label" for="workout_label">Отработан</label>
                                    </div>
                                </div>
                                <div class="col-4 col-md-3 col-lg-2">
                                    {*if $looker_link}
                                    <a href="{$looker_link}" target="_blank" class="btn btn-info float-right"><i class=" fas fa-address-book"></i> Смотреть ЛК</a>
                                    {/if*}                                        
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    
                                </div>
                                <div class="col-8 col-md-3 col-lg-3">

                                </div>
                                <div class="col-12 col-md-6 col-lg-3 ">
                                    <h5 class="js-order-manager text-right">
                                    {if in_array('change_sudblock_manager', $manager->permissions)}
                                        <select name="manager_id" data-contract="{$contract->id}" class="form-control js-manager-select">
                                            <option value="">Не выбран</option>
                                            {foreach $managers as $m}
                                            {if ($manager->role == 'cheif_exactor' && $m->role=='exactor') || ($manager->role == 'cheif_sudblock' && $m->role=='sudblock')}
                                            <option value="{$m->id}" {if $m->id == $contract->manager_id}selected{/if}>{$m->name|escape}</option>
                                            {elseif $manager->role != 'cheif_exactor' && $manager->role!='sudblock' && ($m->role=='exactor' || $m->role=='sudblock')}
                                            <option value="{$m->id}" {if $m->id == $contract->manager_id}selected{/if}>{$m->name|escape}</option>
                                            {/if}
                                            {/foreach}
                                        </select>
                                    {else}
                                        {$managers[$contract->manager_id]->name|escape}
                                    {/if}
                                    </h5>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col-12 col-md-4 col-lg-3">
                                    <form action="{url}" class="js-order-item-form" id="fio_form">

                                        <input type="hidden" name="action" value="fio" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />
                                    
                                        <div class="border p-2 view-block">
                                            <h5>
                                                <a href="client/{$contract->user_id}" title="Перейти в карточку клиента">
                                                    {$contract->lastname|escape}
                                                    {$contract->firstname|escape}
                                                    {$contract->patronymic|escape}
                                                </a>
                                            </h5>
                                            <h3>
                                                <span>{$contract->user->phone_mobile}</span>
                                                <button class="js-mango-call mango-call js-yuk" data-phone="{$contract->user->phone_mobile}" title="Выполнить звонок">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </button>                   
                                            </h3>
                                        </div>
                                        
                                        <div class="edit-block hide">
                                            <div class="form-group mb-1">
                                                <input type="text" name="lastname" value="{$order->lastname}" class="form-control" placeholder="Фамилия" />
                                            </div>
                                            <div class="form-group mb-1">
                                                <input type="text" name="firstname" value="{$order->firstname}" class="form-control" placeholder="Имя" />
                                            </div>
                                            <div class="form-group mb-1">
                                                <input type="text" name="patronymic" value="{$order->patronymic}" class="form-control" placeholder="Отчество" />
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                            </div>
                                        </div>
                                        
                                    </form>
                                </div>
                                <div class="col-12 col-md-8 col-lg-6">
                                    <form action="{url}" class="mb-3 p-2 border js-order-item-form" id="amount_form">

                                        <div class="row view-block ">
                                            <div class="col-4 text-center">
                                                <h5>Сумма</h5>
                                                <h3 class="text-primary">{$contract->contract->amount} руб</h3>
                                            </div>
                                            <div class="col-4 text-center">
                                                <h5>Срок</h5>
                                                <h3 class="text-primary">{$contract->contract->period} {$contract->contract->period|plural:"день":"дней":"дня"}</h3>
                                            </div>
                                            <div class="col-4 text-center">
                                                <h5>Дата возврата</h5>
                                                <h3 class="text-primary">{$contract->contract->return_date|date}</h3>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>ОД</h6>
                                                <h5 class="text-primary">{$contract->contract->loan_body_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>Проценты</h6>
                                                <h5 class="text-primary">{$contract->contract->loan_percents_summ*1} P</h5>
                                            </div>
                                            <div class="col-4 text-center">
                                                <h6>Ответственность</h6>
                                                <h5 class="text-primary">{$contract->contract->loan_charge_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>Пени</h6>
                                                <h5 class="text-primary">{$contract->contract->loan_peni_summ*1} P</h5>
                                            </div>
                                            <div class="col-2 text-center">
                                                <h6>ОСД</h6>
                                                <h5 class="text-primary">{$contract->contract->loan_body_summ + $contract->contract->loan_percents_summ + $contract->contract->loan_charge_summ + $contract->contract->loan_peni_summ} P</h5>
                                            </div>
                                        </div>                                            
                                    </form>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                        
                                    <div class="js-order-status">
                                        <div class="card card-primary mb-2">
                                            <div class="box text-center">
                                                <h6>Договор </h6>
                                                <h3 class="text-white">{$contract->number}</h3>
                                            </div>
                                        </div>
                                        
                                        <h5 class="js-order-manager text-right">
                                            <select name="status" data-contract="{$contract->id}" class="form-control js-status-select">
                                                <option value="">Не выбран</option>
                                                {foreach $statuses as $s}
                                                <option value="{$s->id}" {if $s->id == $contract->status}selected{/if}>{$s->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        <div class="row">
                            <div class="col-md-9">
                                <ul class="mt-2 nav nav-tabs" role="tablist" id="order_tabs">
    
                                    <li class="nav-item"> 
                                        <a class="nav-link active" data-toggle="tab" href="#documents" role="tab" aria-selected="true">
                                            <span class="hidden-sm-up"><i class="ti-email"></i></span> 
                                            <span class="hidden-xs-down">Судблок</span>
                                        </a> 
                                    </li>
                                    <li class="nav-item"> 
                                        <a class="nav-link" data-toggle="tab" href="#info" role="tab" aria-selected="false">
                                            <span class="hidden-sm-up"><i class="ti-home"></i></span> 
                                            <span class="hidden-xs-down">Персональная информация</span>
                                        </a> 
                                    </li>
                                    <li class="nav-item"> 
                                        <a class="nav-link" data-toggle="tab" href="#comments" role="tab" aria-selected="false">
                                            <span class="hidden-sm-up"><i class="ti-user"></i></span> 
                                            <span class="hidden-xs-down">
                                                Комментарии {if $comments|count > 0}<span class="label label-rounded label-primary">{$comments|count}</span>{/if}
                                            </span>
                                        </a> 
                                    </li>
                                    <li class="nav-item"> 
                                        <a class="nav-link" data-toggle="tab" href="#logs" role="tab" aria-selected="true">
                                            <span class="hidden-sm-up"><i class="ti-email"></i></span> 
                                            <span class="hidden-xs-down">Логирование</span>
                                        </a> 
                                    </li>
                                    <li class="nav-item"> 
                                        <a class="nav-link" data-toggle="tab" href="#operations" role="tab" aria-selected="true">
                                            <span class="hidden-sm-up"><i class="ti-email"></i></span> 
                                            <span class="hidden-xs-down">Операции</span>
                                        </a> 
                                    </li>
                                    <li class="nav-item"> 
                                        <a class="nav-link" data-toggle="tab" href="#connexions" role="tab" aria-selected="true">
                                            <span class="hidden-sm-up"><i class="ti-"></i></span> 
                                            <span class="hidden-xs-down">Связанные лица</span>
                                        </a> 
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content tabcontent-border" id="order_tabs_content">
                                    
                                    <!-- Документы -->
                                    <div class="tab-pane p-3 active" id="documents" role="tabpanel">

                                        <div class="card border js-block-documents">
                                            <div class=" card-header text-white">
                                                <h3 class="float-left text-white mb-0">1. Документы для отправки в суд</h3>
                                                <div class="float-right">
                                                    <a href="{url action='create' block='sud'}" class="btn btn-sm btn-warning" >
                                                        <span class="btn-label"><i class="fas fa-indent"></i></span>Сформировать документы
                                                    </a>
                                                    <a href="javascript.void(0)" class="btn btn-sm btn-success js-open-add-document" data-type="sud" data-contract="{$contract->id}">
                                                        <span class="btn-label"><i class="fas fa-plus-circle"></i></span>Добавить документ
                                                    </a>
                                                </div>
                                            </div>
                                        
                                            <table class="table js-sud-documents">
                                                {foreach $sud_documents as $document}
                                                <tr class="js-document-row">
                                                    <td>
                                                        <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                            <input name="doc[]" type="checkbox" class="custom-control-input" id="doc_{$document->id}" value="{$document->id}" data-href="{$config->root_url}/files/sudblock/{$contract->id}/{$document->filename}" />
                                                            <label class="custom-control-label" for="doc_{$document->id}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-info">
                                                        <a target="_blank" href="{$config->root_url}/files/sudblock/{$contract->id}/{$document->filename}">
                                                            <i class="fas fa-file-pdf fa-lg"></i>&nbsp;
                                                            {$document->name|escape}
                                                        </a>
                                                    </td>
                                                    <td class="text-right">
                                                        <button class="btn btn-sm btn-primary js-print-document waves-effect waves-light" data-user="{$document->user_id}" data-document="{$document->id}" type="button">
                                                            <span class="btn-label"><i class="fas fa-print"></i></span>Печать
                                                        </button>
                                                        {if !$contract->sud_post_number}
                                                        <button title="Документ распечатан и прикреплен" class="js-ready-document btn btn-sm {if $document->ready}btn-success js-is-ready{else}btn-outline-secondary{/if} waves-effect waves-light" data-document="{$document->id}" data-ready="{$document->ready}" type="button">
                                                            <span class="btn-label"><i class="fa fa-check"></i></span>Готов
                                                        </button>
                                                        <button type="button" class="js-remove-document btn btn-danger btn-sm waves-effect waves-light" data-document="{$document->id}">
                                                            <span class="btn-label"><i class="fas fa-trash"></i></span>Удалить
                                                        </button>
                                                        {/if}
                                                    </td>
                                                </tr>
                                                {/foreach}
                                            </table>
                                            
                                            {if $sud_documents|count>0}
                                            <hr class="m-1" />
                                            
                                            <div class="p-2">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <button type="button" class=" mb-1 btn btn-sm btn-primary js-print-check-documents waves-effect waves-light">
                                                            <span class="btn-label"><i class="fas fa-print"></i></span>Распечатать выделенное
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary js-print-all-documents waves-effect waves-light">
                                                            <span class="btn-label"><i class="fas fa-print"></i></span>Распечатать все
                                                        </button>
                                                    </div>
                                                    <div class="col-4">
                                                        Номер почтового отправления: {$contract->sud_post_number}
                                                        {*}
                                                        <input type="text" class="form-control js-send-sud-post-number" placeholder="Номер почтового отправления" value="{$contract->sud_post_number}" />
                                                        {*}
                                                    </div>
                                                    <div class="col-4 text-right">
                                                        <button class="btn {if $contract->sud_post_number}btn-success{else}btn-outline-success{/if} js-send-sud waves-effect waves-light" type="button">
                                                            {if $contract->sud_post_number}
                                                                <span class="btn-label"><i class="fas fa-envelope"></i></span>Документы отправлены в суд
                                                            {else}
                                                                <span class="btn-label"><i class="far fa-envelope"></i></span>Отправить документы в суд
                                                            {/if}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            {/if}
                                            
                                            {if $contract->sud_post_number}
                                            <hr class="m-2" />
                                            
                                            <div class="p-2 js-sudprikaz-block">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-sudprikaz-number" placeholder="Номер судебного приказа" value="{$contract->sudprikaz_number}" />                                                        
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control js-sudprikaz-date" placeholder="Дата судебного приказа" value="{$contract->sudprikaz_date}" />                                                        
                                                    </div>
                                                    <div class="col-md-3">
                                                        <a href="javascript.void(0)" class="btn btn-success js-open-add-document" data-type="fssp" data-contract="{$contract->id}">
                                                            <span class="btn-label"><i class="fas fa-plus-circle"></i></span>Скан суд. приказа
                                                        </a>                                                    
                                                    </div>
                                                    <div class="col-md-3 p-0">
                                                        <button type="submit" class="js-sudrikaz-add btn {if $contract->sudprikaz_added_date}btn-success{else}btn-outline-success{/if} waves-effect waves-light">
                                                            <span class="btn-label"><i class="fas fa-gavel"></i></span>
                                                            {if $contract->sudprikaz_added_date}
                                                                <span>Суд. приказ добавлен</span>
                                                            {else}
                                                                <span>Добавить суд. приказ</span>
                                                            {/if}
                                                        </button>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            {/if}
                                        </div>
                                        
                                        {if $contract->sudprikaz_added_date}
                                        <div class="card border js-block-documents">
                                            <div class=" card-header text-white">
                                                <h3 class="float-left text-white mb-0">2. Документы для отправки в ФССП</h3>
                                                <div class="float-right">
                                                    <a href="{url action='create' block='fssp'}" class="btn btn-sm btn-warning">
                                                        <span class="btn-label"><i class="fas fa-indent"></i></span>Сформировать документы
                                                    </a>
                                                    <a href="javascript.void(0)" class="btn btn-sm btn-success js-open-add-document" data-type="fssp" data-contract="{$contract->id}">
                                                        <span class="btn-label"><i class="fas fa-plus-circle"></i></span>Добавить документ
                                                    </a>
                                                </div>
                                            </div>
                                        
                                            <table class="table js-fssp-documents">
                                                {foreach $fssp_documents as $document}
                                                <tr class="js-document-row">
                                                    <td>
                                                        <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                            <input name="doc[]" type="checkbox" class="custom-control-input" id="doc_{$document->id}" value="{$document->id}" data-href="{$config->root_url}/files/sudblock/{$contract->id}/{$document->filename}" />
                                                            <label class="custom-control-label" for="doc_{$document->id}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-info">
                                                        <a target="_blank" href="{$config->root_url}/files/sudblock/{$contract->id}/{$document->filename}">
                                                            <i class="fas fa-file-pdf fa-lg"></i>&nbsp;
                                                            {$document->name|escape}
                                                        </a>
                                                    </td>
                                                    <td class="text-right">
                                                        <button class="btn btn-sm btn-primary js-print-document waves-effect waves-light" data-user="{$document->user_id}" data-document="{$document->id}" type="button">
                                                            <span class="btn-label"><i class="fas fa-print"></i></span>Печать
                                                        </button>
                                                        {if !$contract->fssp_post_number}
                                                        <button title="Документ распечатан и прикреплен" class="js-ready-document btn btn-sm {if $document->ready}btn-success js-is-ready{else}btn-outline-secondary{/if} waves-effect waves-light" data-document="{$document->id}" data-ready="{$document->ready}" type="button">
                                                            <span class="btn-label"><i class="fa fa-check"></i></span>Готов
                                                        </button>
                                                        <button type="button" class="js-remove-document btn btn-danger btn-sm waves-effect waves-light" data-document="{$document->id}">
                                                            <span class="btn-label"><i class="fas fa-trash"></i></span>Удалить
                                                        </button>
                                                        {/if}
                                                    </td>
                                                </tr>
                                                {/foreach}
                                            </table>
                                            
                                            {if $fssp_documents|count>0}
                                            <hr class="m-1" />
                                            
                                            <div class="p-2">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <button type="button" class=" mb-1 btn btn-sm btn-primary js-print-check-documents">
                                                            <span class="btn-label"><i class="fas fa-print"></i></span>Распечатать выделенное
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary js-print-all-documents">
                                                            <span class="btn-label"><i class="fas fa-print"></i></span>Распечатать все
                                                        </button>
                                                    </div>
                                                    <div class="col-4">
                                                        Номер почтового отправления: {$contract->fssp_post_number}
                                                        {*}
                                                        <input type="text" class="form-control js-send-fssp-post-number" placeholder="Номер почтового отправления" value="{$contract->fssp_post_number}" />
                                                        {*}
                                                    </div>
                                                    <div class="col-4 text-right">
                                                        <button class="btn {if $contract->fssp_post_number}btn-success{else}btn-outline-success{/if} js-send-fssp" type="button">
                                                            <i class="fas fa-retry"></i>
                                                            {if $contract->fssp_post_number}
                                                                <span class="btn-label"><i class="fas fa-envelope"></i></span>Документы отправлены в ФССП
                                                            {else}
                                                                <span class="btn-label"><i class="far fa-envelope"></i></span>Отправить документы в ФССП
                                                            {/if}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            {/if}
                                        </div>
                                        {/if}
                                    </div>
                                    <!-- /Документы -->
                                                                    
                                    <div class="tab-pane" id="info" role="tabpanel">
                                        <div class="form-body p-2 pt-3">
                                                    
                                            <div class="row">
                                                <div class="col-md-12">
                                                    
                                                    <!-- Контакты -->
                                                    <form action="{url}" class="mb-3 border js-order-item-form" id="personal_data_form">
                                                    
                                                        <input type="hidden" name="action" value="contactdata" />
                                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                        <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                        
                                                        <h5 class="card-header">
                                                            <span class="text-white ">Контакты</span>
                                                        </h5>
                                                        
                                                        <div class="row pt-2 view-block {if $contactdata_error}hide{/if}">
                                                            <div class="col-md-12">
                                                                <div class="form-group row m-0">
                                                                    <label class="control-label col-md-4">Email:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$contract->user->email|escape}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group row m-0">
                                                                    <label class="control-label col-md-4">Дата рождения:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$contract->user->birth|escape}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group row m-0">
                                                                    <label class="control-label col-md-4">Место рождения:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$contract->user->birth_place|escape}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group row m-0">
                                                                    <label class="control-label col-md-4">Паспорт:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$contract->user->passport_serial} {$order->subdivision_code}, от {$order->passport_date}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group row m-0">
                                                                    <label class="control-label col-md-4">Кем выдан:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">{$contract->user->passport_issued}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group row m-0">
                                                                    <label class="control-label col-md-4">Соцсети:</label>
                                                                    <div class="col-md-8">
                                                                        <ul class="list-unstyled form-control-static pl-0">
                                                                            {if $contract->user->social}
                                                                            <li>
                                                                                <a target="_blank" href="{$contract->user->social}">{$contract->user->social}</a>
                                                                            </li>
                                                                            {/if}
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </form>        
                                                    <!-- / Контакты-->
    
                                                    <!-- /Контактные лица -->
                                                    <form action="{url}" class="js-order-item-form mb-3 border" id="contact_persons_form">
                                                    
                                                        <input type="hidden" name="action" value="contacts" />
                                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                        <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                    
                                                        <h5 class="card-header">
                                                            <span class="text-white">Контактные лица</span>
                                                        </h5>
                                                        
                                                        <div class="row view-block m-0 {if $contacts_error}hide{/if}">
                                                            <table class="table table-hover mb-0">
                                                                <tr>
                                                                    <td>{$order->contact_person_name}</td>
                                                                    <td>{$order->contact_person_relation}</td>
                                                                    <td class="text-right">{$order->contact_person_phone}</td>
                                                                    <td>
                                                                        {*if $contract->collection_status != 8}
                                                                        <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}" data-phone="{$order->contact_person_phone}" title="Выполнить звонок">
                                                                            <i class="fas fa-mobile-alt"></i>
                                                                        </button>
                                                                        {/if*}
                                                                        <button class="js-contactperson mango-call js-open-comment-form" title="Добавить комментарий" data-contactperson="{$order->contactperson2_id}">
                                                                            <i class="fas fa-comment-dots"></i> 
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{$order->contact_person2_name}</td>
                                                                    <td>{$order->contact_person2_relation}</td>
                                                                    <td class="text-right">{$order->contact_person2_phone}</td>
                                                                    <td>
                                                                        {*if $contract->collection_status != 8}
                                                                        <button class="js-mango-call mango-call {if $contract->sold}js-yuk{/if}" data-phone="{$order->contact_person2_phone}" title="Выполнить звонок">
                                                                            <i class="fas fa-mobile-alt"></i>
                                                                        </button>
                                                                        {/if*}
                                                                        <button class="js-contactperson mango-call  js-open-comment-form" title="Добавить комментарий" data-contactperson="{$order->contact_person2_id}">
                                                                            <i class="fas fa-comment-dots"></i> 
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        
                                                        <div class="row m-0 pt-2 pb-2 edit-block {if !$contacts_error}hide{/if}">
                                                            {if $contacts_error}
                                                            <div class="col-md-12">
                                                                <ul class="alert alert-danger">
                                                                    {if in_array('empty_contact_person_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица!</li>{/if}
                                                                    {if in_array('empty_contact_person_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица!</li>{/if}
                                                                    {if in_array('empty_contact_person2_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 2!</li>{/if}
                                                                    {if in_array('empty_contact_person2_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 2!</li>{/if}
                                                                </ul>
                                                            </div>
                                                            {/if}
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_contact_person_name', (array)$contacts_error)}has-danger{/if}">
                                                                    <label class="control-label">ФИО контакного лица</label>
                                                                    <input type="text" class="form-control" name="contact_person_name" value="{$order->contact_person_name}" placeholder="" required="true" />
                                                                    {if in_array('empty_contact_person_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                                                                    <label class="control-label">Кем приходится</label>
                                                                    <select class="form-control custom-select" name="contact_person_relation">
                                                                        <option value="" {if $order->contact_person_relation == ''}selected=""{/if}>Выберите значение</option>
                                                                        <option value="мать/отец" {if $order->contact_person_relation == 'мать/отец'}selected=""{/if}>мать/отец</option>
                                                                        <option value="муж/жена" {if $order->contact_person_relation == 'муж/жена'}selected=""{/if}>муж/жена</option>
                                                                        <option value="сын/дочь" {if $order->contact_person_relation == 'сын/дочь'}selected=""{/if}>сын/дочь</option>
                                                                        <option value="коллега" {if $order->contact_person_relation == 'коллега'}selected=""{/if}>коллега</option>
                                                                        <option value="друг/сосед" {if $order->contact_person_relation == 'друг/сосед'}selected=""{/if}>друг/сосед</option>
                                                                        <option value="иной родственник" {if $order->contact_person_relation == 'иной родственник'}selected=""{/if}>иной родственник</option>
                                                                    </select>
                                                                    {if in_array('empty_contact_person_relation', (array)$contacts_error)}<small class="form-control-feedback">Укажите кем приходится контакное лицо!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_contact_person_phone', (array)$contacts_error)}has-danger{/if}">
                                                                    <label class="control-label">Тел. контакного лица</label>
                                                                    <input type="text" class="form-control" name="contact_person_phone" value="{$order->contact_person_phone}" placeholder="" required="true" />
                                                                    {if in_array('empty_contact_person_phone', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                                </div>
                                                            </div>
                                                            
                                                            
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_contact_person2_name', (array)$contacts_error)}has-danger{/if}">
                                                                    <label class="control-label">ФИО контакного лица 2</label>
                                                                    <input type="text" class="form-control" name="contact_person2_name" value="{$order->contact_person2_name}" placeholder="" required="true" />
                                                                    {if in_array('empty_contact_person2_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                                                                    <label class="control-label">Кем приходится</label>
                                                                    <select class="form-control custom-select" name="contact_person2_relation">
                                                                        <option value="" {if $order->contact_person2_relation == ''}selected=""{/if}>Выберите значение</option>
                                                                        <option value="мать/отец" {if $order->contact_person2_relation == 'мать/отец'}selected=""{/if}>мать/отец</option>
                                                                        <option value="муж/жена" {if $order->contact_person2_relation == 'муж/жена'}selected=""{/if}>муж/жена</option>
                                                                        <option value="сын/дочь" {if $order->contact_person2_relation == 'сын/дочь'}selected=""{/if}>сын/дочь</option>
                                                                        <option value="коллега" {if $order->contact_person2_relation == 'коллега'}selected=""{/if}>коллега</option>
                                                                        <option value="друг/сосед" {if $order->contact_person2_relation == 'друг/сосед'}selected=""{/if}>друг/сосед</option>
                                                                        <option value="иной родственник" {if $order->contact_person2_relation == 'иной родственник'}selected=""{/if}>иной родственник</option>
                                                                    </select>
                                                                    {if in_array('empty_contact_person_relation', (array)$contacts_error)}<small class="form-control-feedback">Укажите кем приходится контакное лицо!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_contact_person2_phone', (array)$contacts_error)}has-danger{/if}">
                                                                    <label class="control-label">Тел. контакного лица 2</label>
                                                                    <input type="text" class="form-control" name="contact_person2_phone" value="{$order->contact_person2_phone}" placeholder="" />
                                                                    {if in_array('empty_contact_person2_phone', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                                </div>
                                                            </div>
                                                            
                                                            
                                                            <div class="col-md-12">
                                                                <div class="form-actions">
                                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>        
    
                                                    <form action="{url}" class="js-order-item-form mb-3 border" id="address_form">
                                                    
                                                        <input type="hidden" name="action" value="addresses" />
                                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                        <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                        
                                                        <h5 class="card-header">
                                                            <span class="text-white">Адрес</span>
                                                            {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                            <a href="javascript:void(0);" class="text-white float-right js-edit-form"><i class=" fas fa-edit"></i></a></h3>
                                                            {/if}
                                                        </h5>
                                                        
                                                        <div class="row view-block {if $addresses_error}hide{/if}">
                                                            <div class="col-md-12">
                                                                <table class="table table-hover mb-0">
                                                                    <tr>
                                                                        <td>Адрес прописки</td>
                                                                        <td>
                                                                            {$contract->user->Regindex}
                                                                            {$contract->user->Regregion} {$contract->user->Regregion_shorttype},
                                                                            {if $contract->user->Regcity}{$contract->user->Regcity} {$contract->user->Regcity_sorttype},{/if}
                                                                            {if $contract->user->Regdistrict}{$contract->user->Regdistrict} {$contract->user->Regdistrict_sorttype},{/if}
                                                                            {$contract->user->Regstreet} {$contract->user->Regstreet_shorttype},
                                                                            {if $contract->user->Reglocality}{$contract->user->Reglocality} {$contract->user->Reglocality_sorttype},{/if}
                                                                            д.{$contract->user->Reghousing},
                                                                            {if $contract->user->Regbuilding}стр. {$contract->user->Regbuilding},{/if}
                                                                            {if $contract->user->Regroom}кв.{$contract->user->Regroom}{/if}                                                                
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Адрес проживания</td>
                                                                        <td>
                                                                            {$contract->user->Faktindex}
                                                                            {$contract->user->Faktregion} {$contract->user->Faktregion_shorttype},
                                                                            {if $contract->user->Faktcity}{$contract->user->Faktcity} {$contract->user->Faktcity_shorttype},{/if}
                                                                            {if $contract->user->Faktdistrict}{$contract->user->Faktdistrict} {$contract->user->Faktdistrict_sorttype},{/if}
                                                                            {if $contract->user->Faktstreet}{$contract->user->Faktstreet} {$contract->user->Faktstreet_shorttype},{/if}
                                                                            {if $contract->user->Faktlocality}{$contract->user->Faktlocality} {$contract->user->Faktlocality_sorttype},{/if}
                                                                            д.{$contract->user->Fakthousing},
                                                                            {if $contract->user->Faktbuilding}стр. {$contract->user->Faktbuilding},{/if}
                                                                            {if $contract->user->Faktroom}кв.{$contract->user->Faktroom}{/if}
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="edit-block m-0 {if !$addresses_error}hide{/if}">
                                                            
                                                            <div class="row m-0 mb-2 mt-2 js-dadata-address">
                                                                <h6 class="col-12 nav-small-cap">Фактический адрес</h6>
                                                                {if $addresses_error}
                                                                <div class="col-md-12">
                                                                    <ul class="alert alert-danger">
                                                                        {if in_array('empty_regregion', (array)$addresses_error)}<li>Укажите область!</li>{/if}
                                                                        {if in_array('empty_regcity', (array)$addresses_error)}<li>Укажите город!</li>{/if}
                                                                        {if in_array('empty_regstreet', (array)$addresses_error)}<li>Укажите улицу!</li>{/if}
                                                                        {if in_array('empty_reghousing', (array)$addresses_error)}<li>Укажите дом!</li>{/if}
                                                                        {if in_array('empty_faktregion', (array)$addresses_error)}<li>Укажите область!</li>{/if}
                                                                        {if in_array('empty_faktcity', (array)$addresses_error)}<li>Укажите город!</li>{/if}
                                                                        {if in_array('empty_faktstreet', (array)$addresses_error)}<li>Укажите улицу!</li>{/if}
                                                                        {if in_array('empty_fakthousing', (array)$addresses_error)}<li>Укажите дом!</li>{/if}
                                                                    </ul>
                                                                </div>
                                                                {/if}
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-1 {if in_array('empty_regregion', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Область</label>
                                                                        <input type="text" class="form-control js-dadata-region" name="Regregion" value="{$order->Regregion}" placeholder="" required="true" />
                                                                        {if in_array('empty_regregion', (array)$addresses_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-1 {if in_array('empty_regcity', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Город</label>
                                                                        <input type="text" class="form-control js-dadata-city" name="Regcity" value="{$order->Regcity}" placeholder="" />
                                                                        {if in_array('empty_regcity', (array)$addresses_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1 ">
                                                                        <label class="control-label">Район</label>
                                                                        <input type="text" class="form-control js-dadata-district" name="Regdistrict" value="{$order->Regdistrict}" placeholder=""/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1 ">
                                                                        <label class="control-label">Нас. пункт</label>
                                                                        <input type="text" class="form-control js-dadata-locality" name="Reglocality" value="{$order->Reglocality}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1 {if in_array('empty_regstreet', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Улица</label>
                                                                        <input type="text" class="form-control js-dadata-street" name="Regstreet" value="{$order->Regstreet}" placeholder="" />
                                                                        {if in_array('empty_regstreet', (array)$addresses_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group {if in_array('empty_reghousing', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Дом</label>
                                                                        <input type="text" class="form-control js-dadata-house" name="Reghousing" value="{$order->Reghousing}" placeholder="" />
                                                                        {if in_array('empty_reghousing', (array)$addresses_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Строение</label>
                                                                        <input type="text" class="form-control js-dadata-building" name="Regbuilding" value="{$order->Regbuilding}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Квартира</label>
                                                                        <input type="text" class="form-control js-dadata-room" name="Regroom" value="{$order->Regroom}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Индекс</label>
                                                                        <input type="text" class="form-control js-dadata-index" name="Regindex" value="{$order->Regindex}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row m-0 js-dadata-address">
                                                                <h6 class="col-12 nav-small-cap">Адрес регистрации</h6>
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-1 {if in_array('empty_faktregion', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Область</label>
                                                                        <input type="text" class="form-control js-dadata-region" name="Faktregion" value="{$order->Faktregion}" placeholder="" required="true" />
                                                                        {if in_array('empty_faktregion', (array)$addresses_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-1 {if in_array('empty_faktcity', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Город</label>
                                                                        <input type="text" class="form-control js-dadata-city" name="Faktcity" value="{$order->Faktcity}" placeholder=""  />
                                                                        {if in_array('empty_faktcity', (array)$addresses_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1 ">
                                                                        <label class="control-label">Район</label>
                                                                        <input type="text" class="form-control js-dadata-district" name="Faktdistrict" value="{$order->Faktdistrict}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1 ">
                                                                        <label class="control-label">Нас. пункт</label>
                                                                        <input type="text" class="form-control js-dadata-locality" name="Faktlocality" value="{$order->Faktlocality}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1 {if in_array('empty_faktstreet', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Улица</label>
                                                                        <input type="text" class="form-control js-dadata-street" name="Faktstreet" value="{$order->Faktstreet}" placeholder=""  />
                                                                        {if in_array('empty_faktstreet', (array)$addresses_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group {if in_array('empty_fakthousing', (array)$addresses_error)}has-danger{/if}">
                                                                        <label class="control-label">Дом</label>
                                                                        <input type="text" class="form-control js-dadata-house" name="Fakthousing" value="{$order->Fakthousing}" placeholder="" />
                                                                        {if in_array('empty_fakthousing', (array)$addresses_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Строение</label>
                                                                        <input type="text" class="form-control js-dadata-building" name="Faktbuilding" value="{$order->Faktbuilding}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Квартира</label>
                                                                        <input type="text" class="form-control js-dadata-room" name="Faktroom" value="{$order->Faktroom}" placeholder=""  />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Индекс</label>
                                                                        <input type="text" class="form-control js-dadata-index" name="Faktindex" value="{$order->Faktindex}" placeholder="" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row m-0 mt-2 mb-2">
                                                                <div class="col-md-12">
                                                                    <div class="form-actions">
                                                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                                        <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    
                                                    
                                                    
                                                    
                                                    <!-- Данные о работе -->
                                                    <form action="{url}" class="border js-order-item-form mb-3" id="work_data_form">
                                                    
                                                        <input type="hidden" name="action" value="work" />
                                                        <input type="hidden" name="order_id" value="{$contract->contract->order_id}" />
                                                        <input type="hidden" name="user_id" value="{$contract->user->id}" />
                                                        
                                                        <h5 class="card-header">
                                                            <span class="text-white">Данные о работе</span>
                                                        </h5>
                                                        
                                                        <div class="row m-0 pt-2 view-block {if $work_error}hide{/if}">
                                                            {if $contract->user->workplace || $contract->user->workphone}
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-0  row">
                                                                    <label class="control-label col-md-4">Название организации:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            <span class="clearfix">
                                                                                <span class="float-left">
                                                                                    {$contract->user->workplace}
                                                                                </span>
                                                                                <span class="float-right">
                                                                                    {$contract->user->workphone}
                                                                                    <button class="js-mango-call mango-call js-yuk" data-phone="{$contract->user->workphone}" title="Выполнить звонок">
                                                                                        <i class="fas fa-mobile-alt"></i>
                                                                                    </button>
                                                                                </span>
                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {/if}
                                                            {if $contract->user->workaddress}
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-0 row">
                                                                    <label class="control-label col-md-4">Адрес:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            {$contract->user->workaddress}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {/if}
                                                            {if $contract->user->profession}
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-0 row">
                                                                    <label class="control-label col-md-4">Должность:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            {$contract->user->profession}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {/if}
                                                            <div class="col-md-12">
                                                                <div class="form-group  mb-0 row">
                                                                    <label class="control-label col-md-4">Руководитель:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            {$contract->user->chief_name}, {$contract->user->chief_position}
                                                                            <br />
                                                                            {$contract->user->chief_phone}
                                                                            <button class="js-mango-call mango-call js-yuk" data-phone="{$contract->user->chief_phone}" title="Выполнить звонок">
                                                                                <i class="fas fa-mobile-alt"></i>
                                                                            </button>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group  mb-0 row">
                                                                    <label class="control-label col-md-4">Доход:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            {$contract->user->income}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group  mb-0 row">
                                                                    <label class="control-label col-md-4">Расход:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            {$contract->user->expenses}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {if $contract->user->workcomment}
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-0 row">
                                                                    <label class="control-label col-md-4">Комментарий к работе:</label>
                                                                    <div class="col-md-8">
                                                                        <p class="form-control-static">
                                                                            {$contract->user->workcomment}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {/if}
                                                        </div>
                                                        
                                                        <div class="row m-0 pt-2 edit-block js-dadata-address {if !$work_error}hide{/if}">
                                                            {if $work_error}
                                                            <div class="col-md-12">
                                                                <ul class="alert alert-danger">
                                                                
                                                                    {if in_array('empty_workplace', (array)$work_error)}<li>Укажите название организации!</li>{/if}
                                                                    {if in_array('empty_profession', (array)$work_error)}<li>Укажите должность!</li>{/if}
                                                                    {if in_array('empty_workphone', (array)$work_error)}<li>Укажите рабочий телефон!</li>{/if}
                                                                    {if in_array('empty_income', (array)$work_error)}<li>Укажите доход!</li>{/if}
                                                                    {if in_array('empty_expenses', (array)$work_error)}<li>Укажите расход!</li>{/if}
                                                                    {if in_array('empty_chief_name', (array)$work_error)}<li>Укажите ФИО начальника!</li>{/if}
                                                                    {if in_array('empty_chief_position', (array)$work_error)}<li>Укажите Должность начальника!</li>{/if}
                                                                    {if in_array('empty_chief_phone', (array)$work_error)}<li>Укажите Телефон начальника!</li>{/if}
                                                                    
                                                                </ul>
                                                            </div>
                                                            {/if}
                                                            <div class="col-md-6">
                                                                <div class="form-group mb-0 {if in_array('empty_workplace', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Название организации</label>
                                                                    <input type="text" class="form-control" name="workplace" value="{$order->workplace|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_workplace', (array)$work_error)}<small class="form-control-feedback">Укажите название организации!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group mb-0 {if in_array('empty_profession', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Должность</label>
                                                                    <input type="text" class="form-control" name="profession" value="{$order->profession|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_profession', (array)$work_error)}<small class="form-control-feedback">Укажите должность!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-0">
                                                                    <label class="control-label">Адрес</label>
                                                                    <input type="text" class="form-control" name="workaddress" value="{$order->workaddress|escape}" placeholder="" required="true" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-0 {if in_array('empty_workphone', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Pабочий телефон</label>
                                                                    <input type="text" class="form-control" name="workphone" value="{$order->workphone|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_workphone', (array)$work_error)}<small class="form-control-feedback">Укажите рабочий телефон!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-0 {if in_array('empty_income', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Доход</label>
                                                                    <input type="text" class="form-control" name="income" value="{$order->income|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_income', (array)$work_error)}<small class="form-control-feedback">Укажите доход!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-0 {if in_array('empty_expenses', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Расход</label>
                                                                    <input type="text" class="form-control" name="expenses" value="{$order->expenses|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_expenses', (array)$work_error)}<small class="form-control-feedback">Укажите расход!</small>{/if}
                                                                </div>
                                                            </div>
    
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-0 {if in_array('empty_chief_name', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">ФИО начальника</label>
                                                                    <input type="text" class="form-control" name="chief_name" value="{$order->chief_name|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_chief_name', (array)$work_error)}<small class="form-control-feedback">Укажите ФИО начальника!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-0 {if in_array('empty_chief_position', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Должность начальника</label>
                                                                    <input type="text" class="form-control" name="chief_position" value="{$order->chief_position|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_chief_position', (array)$work_error)}<small class="form-control-feedback">Укажите Должность начальника!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-0 {if in_array('empty_chief_phone', (array)$work_error)}has-danger{/if}">
                                                                    <label class="control-label">Телефон начальника</label>
                                                                    <input type="text" class="form-control" name="chief_phone" value="{$order->chief_phone|escape}" placeholder="" required="true" />
                                                                    {if in_array('empty_chief_phone', (array)$work_error)}<small class="form-control-feedback">Укажите Телефон начальника!</small>{/if}
                                                                </div>
                                                            </div>
                                                                                                                    
                                                            <div class="col-md-12">
                                                                <div class="form-group mb-0">
                                                                    <label class="control-label">Комментарий к работе</label>
                                                                    <input type="text" class="form-control" name="workcomment" value="{$order->workcomment|escape}" placeholder="" />
                                                                </div>
                                                            </div>
    
                                                            <div class="col-md-12 pb-2 mt-2">
                                                                <div class="form-actions">
                                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <!-- /Данные о работе -->
                                                    
                                                    
                                                    
                                                    <!--
                                                    <h3 class="box-title mt-5">UTM-метки</h3>
                                                    <hr>
                                                    -->
                                                </div>
                                                
                                            </div>
                                            <!-- -->
                                            <form action="{url}" class="border js-order-item-form mb-3" id="images_form">
                                            
                                                <input type="hidden" name="action" value="images" />
                                                <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                
                                                <h5 class="card-header">
                                                    <span class="text-white">Фотографии</span>
                                                    
                                                </h5>
                                                
                                                <div class="row p-2 view-block {if $socials_error}hide{/if}">
                                                    <ul class="col-md-12 list-inline">
                                                    {foreach $files as $file}
                                                        {if $file->status == 0}
                                                            {$item_class="border-warning"}
                                                            {$ribbon_class="ribbon-warning"}
                                                            {$ribbon_icon="fas fa-question"}
                                                        {elseif $file->status == 1}
                                                            {$item_class="border-primary"}
                                                            {$ribbon_class="ribbon-primary"}
                                                            {$ribbon_icon="fas fa-clock"}
                                                        {elseif $file->status == 2}
                                                            {$item_class="border-success border border-bg"}
                                                            {$ribbon_class="ribbon-success"}
                                                            {$ribbon_icon="fa fa-check-circle"}
                                                        {elseif $file->status == 3}
                                                            {$item_class="border-danger border"}
                                                            {$ribbon_class="ribbon-danger"}
                                                            {$ribbon_icon="fas fa-times-circle"}
                                                        {/if}
                                                        <li class="order-image-item ribbon-wrapper rounded-sm border {$item_class}">
                                                            <a class="image-popup-fit-width"  href="javascript:void(0);" onclick="window.open('{$config->front_url}/files/users/{$file->name}');">
                                                                
                                                                <div class="ribbon ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>
                                                                <img src="{$config->front_url}/files/users/{$file->name}" alt="" class="img-responsive" style="" />
                                                            </a>
                                                            {if $order->status == 1 && ($manager->id == $order->manager_id)}
                                                            <div class="order-image-actions">
                                                                <div class="dropdown mr-1 show ">
                                                                    <button type="button" class="btn {if $file->status==2}btn-success{elseif $file->status==3}btn-danger{else}btn-secondary{/if} dropdown-toggle" id="dropdownMenuOffset" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                                      {if $file->status == 2}Принят
                                                                      {elseif $file->status == 3}Отклонен
                                                                      {else}Статус
                                                                      {/if}
                                                                    </button>
                                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuOffset" x-placement="bottom-start" >
                                                                        <div class="p-1 dropdown-item">
                                                                            <button class="btn btn-sm btn-block btn-success js-image-accept" data-id="{$file->id}" type="button">
                                                                                <i class="fas fa-check-circle"></i>
                                                                                <span>Принят</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="p-1 dropdown-item">
                                                                            <button class="btn btn-sm btn-block btn-danger js-image-reject" data-id="{$file->id}" type="button">
                                                                                <i class="fas fa-times-circle"></i>
                                                                                <span>Отклонен</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {/if}
                                                        </li>
                                                    {/foreach}
                                                    </ul>
                                                </div>
                                                
                                                <div class="row edit-block {if !$images_error}hide{/if}">
                                                    {foreach $files as $file}
                                                    <div class="col-md-4 col-lg-3 col-xlg-3">
                                                        <div class="card card-body">
                                                            <div class="row">
                                                                <div class="col-md-6 col-lg-8">
                                                                    <div class="form-group">
                                                                        <label class="control-label">Статус</label>
                                                                        <input type="text" class="js-file-status" id="status_{$file->id}" name="status[{$file->id}]" value="{$file->status}" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {/foreach}
                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                            <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <!-- -->
                                            <form  method="POST" enctype="multipart/form-data">
                                                
                                                <div class="form_file_item">
                                                    <input type="file" name="new_file" id="new_file" data-user="{$order->user_id}" value="" style="display:none" />
                                                    <label for="new_file" class="btn btn-large btn-primary">
                                                        <i class="fa fa-plus-circle"></i>
                                                        <span>Добавить</span>
                                                    </label>
                                                </div>
                                            </form>
                                            
                                        </div>
                                    </div>
    
                                    <!-- Комментарии -->
                                    <div class="tab-pane p-3" id="comments" role="tabpanel">
                                        
                                        <div class="row">
                                            <div class="col-12">
                                                <h4>Комментарии к заявке</h4>
                                                <button class="btn float-right hidden-sm-down btn-success js-open-comment-form">
                                                    <i class="mdi mdi-plus-circle"></i> 
                                                    Добавить
                                                </button>
                                            </div>
                                            <hr class="m-3" />
                                            <div class="col-12">
                                                {if $comments}
                                                <div class="message-box">
                                                    <div class="message-widget">
                                                        {foreach $comments as $comment}
                                                        <a href="javascript:void(0);">
                                                            <div class="user-img"> 
                                                                <span class="round">{$comment->letter|escape}</span>
                                                            </div>
                                                            <div class="mail-contnet">
                                                                <h5>{$managers[$comment->manager_id]->name|escape}</h5> 
                                                                <span class="mail-desc">
                                                                    {$comment->text|nl2br}
                                                                </span> 
                                                                <span class="time">{$comment->created|date} {$comment->created|time}</span> 
                                                            </div>
                                            
                                                        </a>
                                                        {/foreach}
                                                    </div>
                                                </div>
                                                {/if}
                                                
                                                {if $comments_1c}
                                                <h3>Комментарии из 1С</h3>
                                                <table class="table">
                                                    <tr>
                                                        <th>Дата</th>
                                                        <th>Блок</th>
                                                        <th>Комментарий</th>
                                                    </tr>
                                                    {foreach $comments_1c as $comment}
                                                    <tr>
                                                        <td>{$comment->created|date} {$comment->created|time}</td>
                                                        <td>{$comment->block|escape}</td>
                                                        <td>{$comment->text|nl2br}</td>
                                                    </tr>
                                                    {/foreach}
                                                </table>
                                                {/if}
    
                                                {if !$comments && !$comments_1c}
                                                <h4>Нет комментариев</h4>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Комментарии -->
    
    
                                    <div class="tab-pane p-3" id="logs" role="tabpanel">
                                        {if $changelogs}
                                            <table class="table table-hover ">
                                                <tbody>
                                                    {foreach $changelogs as $changelog}
                                                    <tr class="">
                                                        <td >                                                
                                                            <div class="button-toggle-wrapper">
                                                                <button class="js-open-order button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                            </div>
                                                            <span>{$changelog->created|date}</span>
                                                            {$changelog->created|time}
                                                        </td>
                                                        <td >
                                                            {if $changelog_types[$changelog->type]}{$changelog_types[$changelog->type]}
                                                            {else}{$changelog->type|escape}{/if}
                                                        </td>
                                                        <td >
                                                            <a href="manager/{$changelog->manager->id}">{$changelog->manager->name|escape}</a>
                                                        </td>
                                                        <td >
                                                            <a href="client/{$changelog->user->id}">
                                                                {$changelog->user->lastname|escape} 
                                                                {$changelog->user->firstname|escape} 
                                                                {$changelog->user->patronymic|escape}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr class="order-details" id="changelog_{$changelog->id}" style="display:none">
                                                        <td colspan="4">
                                                            <div class="row">
                                                                <ul class="dtr-details col-md-6 list-unstyled">
                                                                    {foreach $changelog->old_values as $field => $old_value}
                                                                    <li>
                                                                        <strong>{$field}: </strong> 
                                                                        <span>{$old_value}</span>
                                                                    </li>
                                                                    {/foreach}
                                                                </ul>
                                                                <ul class="col-md-6 dtr-details list-unstyled">
                                                                    {foreach $changelog->new_values as $field => $new_value}
                                                                    <li>
                                                                        <strong>{$field}: </strong> 
                                                                        <span>{$new_value}</span>
                                                                    </li>
                                                                    {/foreach}
                                                                </ul> 
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    {/foreach}
                                                </tbody>
                                            </table>
                                        {/if}    
                                    </div>
                                    
                                    <div class="tab-pane p-3" id="operations" role="tabpanel">
                                        {if $contract_operations}
                                            <table class="table table-hover ">
                                                <tbody>
                                                    {foreach $contract_operations as $operation}
                                                    <tr class="
                                                        {if in_array($operation->type, ['PAY'])}table-success{/if} 
                                                        {if in_array($operation->type, ['PERCENTS', 'CHARGE', 'PENI'])}table-danger{/if} 
                                                        {if in_array($operation->type, ['P2P'])}table-info{/if} 
                                                        {if in_array($operation->type, ['INSURANCE'])}table-warning{/if}
                                                    ">
                                                        <td > 
                                                            {*}                                               
                                                            <div class="button-toggle-wrapper">
                                                                <button class="js-open-order button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                            </div>
                                                            {*}
                                                            <span>{$operation->created|date}</span>
                                                            {$operation->created|time}
                                                        </td>
                                                        <td >
                                                            {if $operation->type == 'P2P'}Выдача займа{/if}
                                                            {if $operation->type == 'PAY'}Оплата займа{/if}
                                                            {if $operation->type == 'RECURRENT'}Оплата займа{/if}
                                                            {if $operation->type == 'PERCENTS'}Начисление процентов{/if}
                                                            {if $operation->type == 'INSURANCE'}Страховка{/if}
                                                            {if $operation->type == 'CHARGE'}Ответственность{/if}
                                                            {if $operation->type == 'PENI'}Пени{/if}
                                                        </td>
                                                        <td >
                                                            {$operation->amount} руб
                                                        </td>
                                                    </tr>
                                                    <tr class="order-details" id="changelog_{$changelog->id}" style="display:none">
                                                        <td colspan="3">
                                                            <div class="row">
                                                                <ul class="dtr-details col-md-6 list-unstyled">
                                                                    {foreach $changelog->old_values as $field => $old_value}
                                                                    <li>
                                                                        <strong>{$field}: </strong> 
                                                                        <span>{$old_value}</span>
                                                                    </li>
                                                                    {/foreach}
                                                                </ul>
                                                                <ul class="col-md-6 dtr-details list-unstyled">
                                                                    {foreach $changelog->new_values as $field => $new_value}
                                                                    <li>
                                                                        <strong>{$field}: </strong> 
                                                                        <span>{$new_value}</span>
                                                                    </li>
                                                                    {/foreach}
                                                                </ul> 
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    {/foreach}
                                                </tbody>
                                            </table>
                                        {else}
                                            <h4>Нет операций</h4>
                                        {/if}    
                                    </div>
                                    
                                                                        
                                    <div class="tab-pane p-3" id="connexions" role="tabpanel">
                                        <div class="js-app-connexions" data-user="{$contract->user_id}">
                                            <h3 class="m-5 p-5 text-center">Загрузка</h3>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                            </div>
                        
                            <div class="col-md-3">                            
                                
                                <div class="card border js-notification-wrapper">
                                    <h5 class=" card-header text-white">
                                        <i class="mdi-note-multiple-outline mdi"></i> 
                                        <span>Напоминания</span>
                                    </h5>
                                
                                    <form method="POST" id="form_add_notification" class="p-2 border">
        
                                        <input type="hidden" name="contract_id" value="{$contract->id}" />
                                        <input type="hidden" name="action" value="add_notification" />
                                        
                                        <div class="alert" style="display:none"></div>
                                        
                                        <div class="form-group mb-1">
                                            <select name="event_id" id="block" class="form-control">
                                                <option value="">Выберите событие</option>
                                                {foreach $notification_events as $ne}
                                                <option value="{$ne->id}" >{$ne->name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-1">
                                            <div class="input-group">
                                                <input type="text" name="notification_date" class="form-control singledate" value="{$default_notification_date|date}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <span class="ti-calendar"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mb-1">
                                            <textarea placeholder="Комментарий" class="form-control" name="comment" style="resize:vertical"></textarea>
                                        </div>
                                        <div class="form-action">
                                            <button type="submit" class="btn float-right btn-success waves-effect waves-light">Добавить</button>
                                        </div>
                                    </form>
                                    <div class="message-box comment-box">
                                        <div class="message-widget">
                                            {foreach $notifications as $notification}
                                            <a href="javascript:void(0);">
                                                
                                                <div class="mail-content">
                                                    <h5>
                                                        <strong>{$managers[$notification->manager_id]->name|escape}</strong>
                                                        <span class="badge badge-info float-right">{$notification->notification_date|date}</span>
                                                    </h5> 
                                                    <h6>{$notification->event->action}</h6>
                                                    <span class="mail-desc">
                                                        {$notification->comment|nl2br}
                                                    </span> 
                                                    <span class="time">{$notification->created|date} {$notification->created|time}</span>
                                                </div>
                                
                                            </a>
                                            {/foreach}
                                        </div>
                                    </div>                                                
    
                                </div>
                                
                                <div class="card border">
                                    <h5 class=" card-header text-white">Комментарии</h5>
                                
                                    <form method="POST" id="form_add_comment" class="p-2 border" action="order/{$order->order_id}">
        
                                        <input type="hidden" name="order_id" value="{$contract->contract->order_id}" />
                                        <input type="hidden" name="user_id" value="{$contract->user->id}" />
                                        <input type="hidden" name="block" value="" />
                                        <input type="hidden" name="action" value="add_comment" />
                                        
                                        <div class="alert" style="display:none"></div>
                                        
                                        <div class="form-group mb-1">
                                            <textarea class="form-control" name="text" style="resize:vertical"></textarea>
                                        </div>
                                        <div class="form-action">
                                            <button type="button" class="btn float-left btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                                            <button type="submit" class="btn float-right btn-success waves-effect waves-light">Сохранить</button>
                                        </div>
                                    </form>
                                    <div class="message-box comment-box">
                                        <div class="message-widget">
                                            {foreach $comments as $comment}
                                            <a href="javascript:void(0);">
                                                
                                                <div class="mail-contnet">
                                                    <h5>{$managers[$comment->manager_id]->name|escape}</h5> 
                                                    <span class="mail-desc">
                                                        {$comment->text|nl2br}
                                                    </span> 
                                                    <span class="time">{$comment->created|date} {$comment->created|time}</span> 
                                                </div>
                                
                                            </a>
                                            {/foreach}
                                        </div>
                                    </div>                                                
    
                                </div>
                                </div>
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    
    {include file='footer.tpl'}
    
</div>







<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="">
                    
                    <input type="hidden" name="order_id" value="" />
                    <input type="hidden" name="contactperson_id" value="" />
                    <input type="hidden" name="action" value="" />
                    
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
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

<div id="modal_add_document" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title">Добавить документ</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_document"  enctype="multipart/form-data">
                    
                    <input type="hidden" name="action" value="add_document" />
                    <input type="hidden" name="base" value="0" />
                    <input type="hidden" name="block" value="" />
                    <input type="hidden" name="sudblock_contract_id" value="{$contract->id}" />
                    <input type="hidden" name="provider" value="{$contract->provider}" />
                    
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Название  документа:</label>
                        <input type="text" class="form-control" name="name" id="name" value="" />
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