{$meta_title="Заявка №`$order->order_id`" scope=parent}

{capture name='page_scripts'}
    
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    
    <script type="text/javascript" src="theme/{$settings->theme|escape}/js/apps/order.js"></script>
    
{/capture}

{capture name='page_styles'}
    <link href="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
{/capture}


<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-animation"></i> Заявка №{$order->order_id}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="orders">Заявки</a></li>
                    <li class="breadcrumb-item active">Заявка №{$order->order_id}</li>
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
                                    <div class="col-4 col-md-3 col-lg-2">
                                        <h4 class="form-control-static">
                                            {if $order->first_loan}<span class="badge badge-success">Новый клиент</span>
                                            {else}<span class="label label-info">Повтор</span>{/if}
                                        </h4>
                                    </div>
                                    <div class="col-8 col-md-3 col-lg-4">
                                        <h5 class="form-control-static">
                                            дата заявки: {$order->date|date} {$order->date|time}
                                        </h5>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6">
                                        <h5 class="js-order-manager text-right">
                                            {if $order->manager_id}
                                                {$managers[$order->manager_id]->name|escape}
                                            {/if}
                                        </h5>
                                    </div>
                                </div>
                                <div class="row pt-2">
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="border p-2">
                                            <h5>
                                                <a href="client/{$order->user_id}" title="Перейти в карточку клиента">
                                                    {$order->lastname|escape}
                                                    {$order->firstname|escape}
                                                    {$order->patronymic|escape}
                                                </a>
                                            </h5>
                                            <h3>
                                                <span>{$order->phone_mobile}</span>
                                                <button class="js-mango-call mango-call" data-phone="{$order->phone_mobile}" title="Выполнить звонок">
                                                    <i class="fas fa-mobile-alt"></i>
                                                </button>                                                
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8 col-lg-6">
                                        <div class="border p-2">
                                            <div class="row">
                                                <div class="col-6 text-center">
                                                    <h5>Сумма</h5>
                                                    <h3 class="text-primary">{$order->amount} руб</h3>
                                                </div>
                                                <div class="col-6 text-center">
                                                    <h5>Срок</h5>
                                                    <h3 class="text-primary">{$order->period} {$order->period|plural:"день":"дней":"дня"}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-3">
                                            {if !$order->manager_id}
                                            <div class="pt-3 js-accept-order-block">
                                                <button class="btn btn-info btn-lg btn-block js-accept-order" data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                    <i class="fas fa-hospital-symbol"></i>
                                                    <span>Принять</span>
                                                </button>
                                            </div>
                                            {/if}
                                            {if $order->status < 2}
                                            <div class="js-approve-reject-block {if !$order->manager_id}hide{/if}">
                                                <button class="btn btn-success btn-block js-approve-order" data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Одобрить</span>
                                                </button>
                                                <button class="btn btn-danger btn-block js-reject-order" data-order="{$order->order_id}" data-manager="{$manager->id}">
                                                    <i class="fas fa-times-circle"></i>
                                                    <span>Отказать</span>
                                                </button>
                                            </div>
                                            {/if}
                                            
                                            <div class="js-order-status">
                                                {if $order->status == 2}
                                                <div class="card card-success">
                                                    <div class="box text-center">
                                                        <h3 class="text-white">Одобрена</h3>
                                                        <h6><a href="contract/{$order->contract_id}">Договор #{$order->contract_id}</a></h6>
                                                    </div>
                                                </div>
                                                {/if}
                                            </div>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <ul class="mt-2 nav nav-tabs" role="tablist">
                                <li class="nav-item"> 
                                    <a class="nav-link active" data-toggle="tab" href="#info" role="tab" aria-selected="false">
                                        <span class="hidden-sm-up"><i class="ti-home"></i></span> 
                                        <span class="hidden-xs-down">Персональная информация</span>
                                    </a> 
                                </li>
                                <li class="nav-item"> 
                                    <a class="nav-link" data-toggle="tab" href="#comments" role="tab" aria-selected="false">
                                        <span class="hidden-sm-up"><i class="ti-user"></i></span> 
                                        <span class="hidden-xs-down">Коментарии</span>
                                    </a> 
                                </li>
                                <li class="nav-item"> 
                                    <a class="nav-link" data-toggle="tab" href="#documents" role="tab" aria-selected="true">
                                        <span class="hidden-sm-up"><i class="ti-email"></i></span> 
                                        <span class="hidden-xs-down">Документы</span>
                                    </a> 
                                </li>
                                <li class="nav-item"> 
                                    <a class="nav-link" data-toggle="tab" href="#logs" role="tab" aria-selected="true">
                                        <span class="hidden-sm-up"><i class="ti-email"></i></span> 
                                        <span class="hidden-xs-down">Логирование</span>
                                    </a> 
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content tabcontent-border">
                                <div class="tab-pane active" id="info" role="tabpanel">
                                    <div class="form-body p-2 pt-3">
                                                
                                        <div class="row">
                                            <div class="col-md-8 ">
                                                
                                                <!-- Контакты -->
                                                <form action="{url}" class="mb-3 border js-order-item-form" id="personal_data_form">
                                                
                                                    <input type="hidden" name="action" value="contactdata" />
                                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                    
                                                    <h5 class="card-header">
                                                        <span class="text-white ">Контакты</span>
                                                        {if $order->status < 2}
                                                        <a href="javascript:void(0);" class="float-right text-white js-edit-form"><i class=" fas fa-edit"></i></a></h3>
                                                        {/if}
                                                    </h5>
                                                    
                                                    <div class="row pt-2 view-block {if $contactdata_error}hide{/if}">
                                                        <div class="col-md-12">
                                                            <div class="form-group row m-0">
                                                                <label class="control-label col-md-4">Email:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">{$order->email|escape}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group row m-0">
                                                                <label class="control-label col-md-4">Дата рождения:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">{$order->birth|escape}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group row m-0">
                                                                <label class="control-label col-md-4">Паспорт:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">{$order->passport_serial} {$order->subdivision_code}, от {$order->passport_date}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group row m-0">
                                                                <label class="control-label col-md-4">Кем выдан:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">{$order->passport_issued}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group row m-0">
                                                                <label class="control-label col-md-4">Соцсети:</label>
                                                                <div class="col-md-8">
                                                                    <ul class="list-unstyled form-control-static pl-0">
                                                                        {if $order->social}
                                                                        <li>
                                                                            <a target="_blank" href="{$order->social}">{$order->social}</a>
                                                                        </li>
                                                                        {/if}
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="row p-2 edit-block {if !$contactdata_error}hide{/if}">
                                                        {if $contactdata_error}
                                                        <div class="col-md-12">
                                                            <ul class="alert alert-danger">
                                                                {if in_array('empty_email', (array)$contactdata_error)}<li>Укажите Email!</li>{/if}
                                                                {if in_array('empty_birth', (array)$contactdata_error)}<li>Укажите Дату рождения!</li>{/if}
                                                                {if in_array('empty_passport_serial', (array)$contactdata_error)}<li>Укажите серию и номер паспорта!</li>{/if}
                                                                {if in_array('empty_passport_date', (array)$contactdata_error)}<li>Укажите дату выдачи паспорта!</li>{/if}
                                                                {if in_array('empty_subdivision_code', (array)$contactdata_error)}<li>Укажите код подразделения выдавшего паспорт!</li>{/if}
                                                                {if in_array('empty_passport_issued', (array)$contactdata_error)}<li>Укажите кем выдан паспорт!</li>{/if}
                                                            </ul>
                                                        </div>
                                                        {/if}
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group {if in_array('empty_email', (array)$contactdata_error)}has-danger{/if}">
                                                                <label class="control-label">Email</label>
                                                                <input type="text" name="email" value="{$order->email}" class="form-control" placeholder="" required="true" />
                                                                {if in_array('empty_email', (array)$contactdata_error)}<small class="form-control-feedback">Укажите Email!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group {if in_array('empty_birth', (array)$contactdata_error)}has-danger{/if}">
                                                                <label class="control-label">Дата рождения</label>
                                                                <input type="text" name="birth" value="{$order->birth}" class="form-control" placeholder="" required="true" />
                                                                {if in_array('empty_birth', (array)$contactdata_error)}<small class="form-control-feedback">Укажите дату рождения!</small>{/if}
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_passport_serial', (array)$contactdata_error)}has-danger{/if}">
                                                                <label class="control-label">Серия и номер паспорта</label>
                                                                <input type="text" class="form-control" name="passport_serial" value="{$order->passport_serial}" placeholder="" required="true" />
                                                                {if in_array('empty_passport_serial', (array)$contactdata_error)}<small class="form-control-feedback">Укажите серию и номер паспорта!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_passport_date', (array)$contactdata_error)}has-danger{/if}">
                                                                <label class="control-label">Дата выдачи</label>
                                                                <input type="text" class="form-control" name="passport_date" value="{$order->passport_date}" placeholder="" required="true" />
                                                                {if in_array('empty_passport_date', (array)$contactdata_error)}<small class="form-control-feedback">Укажите дату выдачи паспорта!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_subdivision_code', (array)$contactdata_error)}has-danger{/if}">
                                                                <label class="control-label">Код подразделения</label>
                                                                <input type="text" class="form-control" name="subdivision_code" value="{$order->subdivision_code}" placeholder="" required="true" />
                                                                {if in_array('empty_subdivision_code', (array)$contactdata_error)}<small class="form-control-feedback">Укажите код подразделения выдавшего паспорт!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group {if in_array('empty_passport_issued', (array)$contactdata_error)}has-danger{/if}">
                                                                <label class="control-label">Кем выдан</label>
                                                                <input type="text" class="form-control" name="passport_issued" value="{$order->passport_issued}" placeholder="" required="true" />
                                                                {if in_array('empty_passport_issued', (array)$contactdata_errors)}<small class="form-control-feedback">Укажите кем выдан паспорт!</small>{/if}
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0 ">
                                                                <label class="control-label">Соцсети</label>
                                                                <input type="text" class="form-control" name="social" value="{$order->social}" placeholder="" />
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
                                                <!-- / Контакты-->

                                                <!-- /Контактные лица -->
                                                <form action="{url}" class="js-order-item-form mb-3 border" id="contact_persons_form">
                                                
                                                    <input type="hidden" name="action" value="contacts" />
                                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                
                                                    <h5 class="card-header">
                                                        <span class="text-white">Контактные лица</span>
                                                        {if $order->status < 2}
                                                        <a href="javascript:void(0);" class="text-white float-right js-edit-form"><i class=" fas fa-edit"></i></a></h3>
                                                        {/if}
                                                    </h5>
                                                    
                                                    <div class="row view-block m-0 {if $contacts_error}hide{/if}">
                                                        <table class="table table-hover mb-0">
                                                            <tr>
                                                                <td>{$order->contact_person_name}</td>
                                                                <td class="text-right">{$order->contact_person_phone}</td>
                                                                <td>
                                                                    <button class="js-mango-call mango-call" data-phone="{$order->contact_person_phone}" title="Выполнить звонок">
                                                                        <i class="fas fa-mobile-alt"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>{$order->contact_person2_name}</td>
                                                                <td class="text-right">{$order->contact_person2_phone}</td>
                                                                <td>
                                                                    <button class="js-mango-call mango-call" data-phone="{$order->contact_person2_phone}" title="Выполнить звонок">
                                                                        <i class="fas fa-mobile-alt"></i>
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
                                                        <div class="col-md-6">
                                                            <div class="form-group {if in_array('empty_contact_person_name', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">ФИО контакного лица</label>
                                                                <input type="text" class="form-control" name="contact_person_name" value="{$order->contact_person_name}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group {if in_array('empty_contact_person_phone', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Тел. контакного лица</label>
                                                                <input type="text" class="form-control" name="contact_person_phone" value="{$order->contact_person_phone}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person_phone', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group {if in_array('empty_contact_person2_name', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">ФИО контакного лица 2</label>
                                                                <input type="text" class="form-control" name="contact_person2_name" value="{$order->contact_person2_name}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person2_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
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
                                                        {if $order->status < 2}
                                                        <a href="javascript:void(0);" class="text-white float-right js-edit-form"><i class=" fas fa-edit"></i></a></h3>
                                                        {/if}
                                                    </h5>
                                                    
                                                    <div class="row view-block {if $addresses_error}hide{/if}">
                                                        <div class="col-md-12">
                                                            <table class="table table-hover mb-0">
                                                                <tr>
                                                                    <td>Адрес прописки</td>
                                                                    <td>
                                                                        {$order->Regregion},
                                                                        {$order->Regcity},
                                                                        {$order->Regstreet},
                                                                        д.{$order->Reghousing},
                                                                        {if $order->Regbuilding}стр. {$order->Regbuilding},{/if}
                                                                        {if $order->Regroom}кв.{$order->Regroom}{/if}                                                                
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Адрес проживания</td>
                                                                    <td>
                                                                        {$order->Faktregion},
                                                                        {$order->Faktcity},
                                                                        {$order->Faktstreet},
                                                                        д.{$order->Fakthousing},
                                                                        {if $order->Faktbuilding}стр. {$order->Faktbuilding},{/if}
                                                                        {if $order->Faktroom}кв.{$order->Faktroom}{/if}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="edit-block m-0 {if !$addresses_error}hide{/if}">
                                                        
                                                        <div class="row m-0 mb-2 mt-2 js-dadata-address">
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
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-1 {if in_array('empty_regregion', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Область</label>
                                                                    <input type="text" class="form-control js-dadata-region" name="Regregion" value="{$order->Regregion}" placeholder="" required="true" />
                                                                    {if in_array('empty_regregion', (array)$addresses_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-1 {if in_array('empty_regcity', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Город</label>
                                                                    <input type="text" class="form-control js-dadata-city" name="Regcity" value="{$order->Regcity}" placeholder="" required="true" />
                                                                    {if in_array('empty_regcity', (array)$addresses_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-1 {if in_array('empty_regstreet', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Улица</label>
                                                                    <input type="text" class="form-control js-dadata-street" name="Regstreet" value="{$order->Regstreet}" placeholder="" required="true" />
                                                                    {if in_array('empty_regstreet', (array)$addresses_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_reghousing', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Дом</label>
                                                                    <input type="text" class="form-control js-dadata-house" name="Reghousing" value="{$order->Reghousing}" placeholder="" required="true" />
                                                                    {if in_array('empty_reghousing', (array)$addresses_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="control-label">Строение</label>
                                                                    <input type="text" class="form-control js-dadata-building" name="Regbuilding" value="{$order->Regbuilding}" placeholder="" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="control-label">Квартира</label>
                                                                    <input type="text" class="form-control js-dadata-room" name="Regroom" value="{$order->Regroom}" placeholder="" required="true" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row m-0 js-dadata-address">
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-1 {if in_array('empty_faktregion', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Область</label>
                                                                    <input type="text" class="form-control js-dadata-region" name="Faktregion" value="{$order->Faktregion}" placeholder="" required="true" />
                                                                    {if in_array('empty_faktregion', (array)$addresses_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-1 {if in_array('empty_faktcity', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Город</label>
                                                                    <input type="text" class="form-control js-dadata-city" name="Faktcity" value="{$order->Faktcity}" placeholder="" required="true" />
                                                                    {if in_array('empty_faktcity', (array)$addresses_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group mb-1 {if in_array('empty_faktstreet', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Улица</label>
                                                                    <input type="text" class="form-control js-dadata-street" name="Faktstreet" value="{$order->Faktstreet}" placeholder="" required="true" />
                                                                    {if in_array('empty_faktstreet', (array)$addresses_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group {if in_array('empty_fakthousing', (array)$addresses_error)}has-danger{/if}">
                                                                    <label class="control-label">Дом</label>
                                                                    <input type="text" class="form-control js-dadata-house" name="Fakthousing" value="{$order->Fakthousing}" placeholder="" required="true" />
                                                                    {if in_array('empty_fakthousing', (array)$addresses_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="control-label">Строение</label>
                                                                    <input type="text" class="form-control js-dadata-building" name="Faktbuilding" value="{$order->Faktbuilding}" placeholder="" />
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="control-label">Квартира</label>
                                                                    <input type="text" class="form-control js-dadata-room" name="Faktroom" value="{$order->Faktroom}" placeholder="" required="true" />
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
                                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                    
                                                    <h5 class="card-header">
                                                        <span class="text-white">Данные о работе</span>
                                                        {if $order->status < 2}
                                                        <a href="javascript:void(0);" class="text-white float-right js-edit-form"><i class=" fas fa-edit"></i></a></h3>
                                                        {/if}
                                                    </h5>
                                                    
                                                    <div class="row m-0 pt-2 view-block {if $work_error}hide{/if}">
                                                        {if $order->workplace || $order->workphone}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0  row">
                                                                <label class="control-label col-md-4">Название организации:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        <span class="clearfix">
                                                                            <span class="float-left">
                                                                                {$order->workplace}
                                                                            </span>
                                                                            <span class="float-right">
                                                                                {$order->workphone}
                                                                                <button class="js-mango-call mango-call" data-phone="{$order->workphone}" title="Выполнить звонок">
                                                                                    <i class="fas fa-mobile-alt"></i>
                                                                                </button>
                                                                            </span>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {/if}
                                                        {if $order->profession}
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-4">Должность:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$order->profession}
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
                                                                        {$order->chief_name}, {$order->chief_position}
                                                                        <br />
                                                                        {$order->chief_phone}
                                                                        <button class="js-mango-call mango-call" data-phone="{$order->chief_phone}" title="Выполнить звонок">
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
                                                                        {$order->income}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group  mb-0 row">
                                                                <label class="control-label col-md-4">Расход:</label>
                                                                <div class="col-md-8">
                                                                    <p class="form-control-static">
                                                                        {$order->expenses}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
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
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_workplace', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Название организации</label>
                                                                <input type="text" class="form-control" name="workplace" value="{$order->workplace}" placeholder="" required="true" />
                                                                {if in_array('empty_workplace', (array)$work_error)}<small class="form-control-feedback">Укажите название организации!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_profession', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Должность</label>
                                                                <input type="text" class="form-control" name="profession" value="{$order->profession}" placeholder="" required="true" />
                                                                {if in_array('empty_profession', (array)$work_error)}<small class="form-control-feedback">Укажите должность!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_workphone', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Pабочий телефон</label>
                                                                <input type="text" class="form-control" name="workphone" value="{$order->workphone}" placeholder="" required="true" />
                                                                {if in_array('empty_workphone', (array)$work_error)}<small class="form-control-feedback">Укажите рабочий телефон!</small>{/if}
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_chief_name', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">ФИО начальника</label>
                                                                <input type="text" class="form-control" name="chief_name" value="{$order->chief_name}" placeholder="" required="true" />
                                                                {if in_array('empty_chief_name', (array)$work_error)}<small class="form-control-feedback">Укажите ФИО начальника!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_chief_position', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Должность начальника</label>
                                                                <input type="text" class="form-control" name="chief_position" value="{$order->chief_position}" placeholder="" required="true" />
                                                                {if in_array('empty_chief_position', (array)$work_error)}<small class="form-control-feedback">Укажите Должность начальника!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0 {if in_array('empty_chief_phone', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Телефон начальника</label>
                                                                <input type="text" class="form-control" name="chief_phone" value="{$order->chief_phone}" placeholder="" required="true" />
                                                                {if in_array('empty_chief_phone', (array)$work_error)}<small class="form-control-feedback">Укажите Телефон начальника!</small>{/if}
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0 {if in_array('empty_income', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Доход</label>
                                                                <input type="text" class="form-control" name="income" value="{$order->income}" placeholder="" required="true" />
                                                                {if in_array('empty_income', (array)$work_error)}<small class="form-control-feedback">Укажите доход!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0 {if in_array('empty_expenses', (array)$work_error)}has-danger{/if}">
                                                                <label class="control-label">Расход</label>
                                                                <input type="text" class="form-control" name="expenses" value="{$order->expenses}" placeholder="" required="true" />
                                                                {if in_array('empty_expenses', (array)$work_error)}<small class="form-control-feedback">Укажите расход!</small>{/if}
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
                                            <div class="col-md-4 ">
                                                <div class="mb-3 border">
                                                    <h5 class=" card-header">
                                                        <span class="text-white ">Скоринги</span>
                                                        {if $order->status < 2}
                                                        <a class="text-white float-right js-run-scorings" data-type="all" data-order="{$order->order_id}" href="javascript:void(0);">
                                                            <i class="far fa-play-circle"></i>
                                                        </a>
                                                        {/if}
                                                    </h2>
                                                    <div class="message-box">
                                                            
                                                            {foreach $scoring_types as $scoring_type}
                                                            <div class="pl-2 pr-2 {if is_null($scorings[$scoring_type->name]->success)}bg-light-warning{elseif $scorings[$scoring_type->name]->success}bg-light-success{else}bg-light-danger{/if}">
                                                                <div class="row {if !$scoring_type@last}border-bottom{/if}">
                                                                    <div class="col-12 col-sm-12 pt-2">
                                                                        <h5 class="float-left">{$scoring_type->title}</h5>
                                                                        {if is_null($scorings[$scoring_type->name]->success)}
                                                                            <span class="label label-warning float-right">Нет результата</span> 
                                                                        {elseif $scorings[$scoring_type->name]->success}
                                                                            <span class="label label-success label-sm float-right">Пройден</span>
                                                                        {else}
                                                                            <span class="label label-danger float-right">Не пройден</span>
                                                                        {/if}                                                                    
                                                                    </div>
                                                                    <div class="col-8 col-sm-8 pb-2">
                                                                        <span class="mail-desc" title="{$scorings[$scoring_type->name]->string_result}">{$scorings[$scoring_type->name]->string_result}</span>
                                                                        <span class="time">
                                                                            {$scorings[$scoring_type->name]->created|date} {$scoring->created|time}
                                                                        </span>
                                                                    </div>
                                                                    <div class="col-4 col-sm-4 pb-2">
                                                                    {if $order->status < 2}
                                                                        {if is_null($scorings[$scoring_type->name]->success)}
                                                                            <a class="btn-load {if in_array(, $audit_types)}loading{/if} text-info js-run-scorings run-scoring-btn float-right" data-type="{$scoring_type->name}" data-order="{$order->order_id}" href="javascript:void(0);">
                                                                                <i class="far fa-play-circle"></i>
                                                                            </a>
                                                                        {else}
                                                                            <a class="btn-load text-info js-run-scorings run-scoring-btn float-right" data-type="{$scoring_type->name}" data-order="{$order->order_id}" href="javascript:void(0);">
                                                                                <i class="fas fa-undo"></i>
                                                                            </a>
                                                                        {/if}                                                                    
                                                                    {/if}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {/foreach}
                                                    </div>
                                                </div>
                                            
                                                <form action="{url}" class="border js-order-item-form" id="services_form">
                
                                                    <input type="hidden" name="action" value="services" />
                                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                                                    
                                                    
                                                    <h5 class="card-header text-white">
                                                        <span>Услуги</span>
                                                        {if $order->status < 2}
                                                        <a href="javascript:void(0);" class="js-edit-form float-right text-white"><i class=" fas fa-edit"></i></a>
                                                        {/if}
                                                    </h5>
                                                    
                                                    <div class="row view-block p-2 {if $services_error}hide{/if}">
                                                        <div class="col-md-12">
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-8 col-7">Смс информирование:</label>
                                                                <div class="col-md-4 col-5">
                                                                    <p class="form-control-static text-right">
                                                                        {if $order->service_sms}
                                                                            <span class="label label-success">Вкл</span>
                                                                        {else}
                                                                            <span class="label label-danger">Выкл</span>
                                                                        {/if}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-8 col-7">Причина отказа:</label>
                                                                <div class="col-md-4 col-5">
                                                                    <p class="form-control-static text-right">
                                                                        {if $order->service_reason}
                                                                            <span class="label label-success">Вкл</span>
                                                                        {else}
                                                                            <span class="label label-danger">Выкл</span>
                                                                        {/if}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-0 row">
                                                                <label class="control-label col-md-8 col-7">Страхование:</label>
                                                                <div class="col-md-4 col-5">
                                                                    <p class="form-control-static text-right">
                                                                        {if $order->service_insurance}
                                                                            <span class="label label-success">Вкл</span>
                                                                        {else}
                                                                            <span class="label label-danger">Выкл</span>
                                                                        {/if}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row p-2 edit-block {if !$services_error}hide{/if}">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox" class="custom-control-input" name="service_sms" id="service_sms" value="1" {if $order->service_sms}checked="true"{/if} />
                                                                    <label class="custom-control-label" for="service_sms">Смс информирование</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox" class="custom-control-input" name="service_reason" id="service_reason" value="1" {if $order->service_reason}checked="true"{/if} />
                                                                    <label class="custom-control-label" for="service_reason">Причина отказа</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox" class="custom-control-input" name="service_insurance" id="service_insurance" value="1" {if $order->service_insurance}checked="true"{/if} />
                                                                    <label class="custom-control-label" for="service_insurance">Страхование</label>
                                                                </div>
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
                                                        <a class="js-open-popup-image  image-popup-fit-width" href="{$config->front_url}/files/users/{$file->name}">
                                                            
                                                            <div class="ribbon ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>
                                                            <img src="{$config->front_url}/files/users/{$file->name}" alt="" class="img-responsive" style="" />
                                                        </a>
                                                        {if $order->status < 2}
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
                                        
                                        
                                    </div>
                                </div>
                                <div class="tab-pane p-3" id="comments" role="tabpanel">
                                    
                                </div>
                                <div class="tab-pane p-3" id="documents" role="tabpanel">
                                    
                                </div>
                                
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