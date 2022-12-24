<!DOCTYPE html>
<html lang="en">

<head>
    <base href="{$config->root_url}/"/>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" href="/favicon.ico"/>
    <title>{$meta_title}</title>

    {if $canonical}
        <link rel="canonical" href="{$canonical}"/>
    {/if}
    <!-- Bootstrap Core CSS -->
    <link href="theme/{$settings->theme|escape}/assets/plugins/bootstrap/css/bootstrap.min.css?v=1.02" rel="stylesheet">
    <!--alerts CSS -->
    <link href="theme/{$settings->theme|escape}/assets/plugins/sweetalert2/dist/sweetalert2.min.css?v=1.02"
          rel="stylesheet">
    <!-- Custom CSS -->
    {$smarty.capture.page_styles}

    <link href="theme/{$settings->theme|escape}/css/style.css?v=1.02" rel="stylesheet">
    <link href="theme/{$settings->theme|escape}/css/colors/green.css?v=1.02" id="theme" rel="stylesheet">
    <link href="theme/{$settings->theme|escape}/css/custom.css?v=1.06" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">
        var _front_url = '{$config->front_url}';
    </script>
</head>

<body class="fix-header fix-sidebar card-no-border">
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
    </svg>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->


<div id="main-wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar">
        <nav class="navbar top-navbar navbar-expand-md navbar-light">
            <!-- ============================================================== -->
            <!-- Logo -->
            <!-- ============================================================== -->
            <div class="navbar-header">
                <a class="navbar-brand" href="/">
                    <b>
                        <img src="theme/{$settings->theme|escape}/assets/images/logo_bf.svg" alt="homepage"
                             class="dark-logo"/>
                    </b>
                </a>
            </div>
            <!-- ============================================================== -->
            <!-- End Logo -->
            <!-- ============================================================== -->
            <div class="navbar-collapse">
                <!-- ============================================================== -->
                <!-- toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav mr-auto mt-md-0 ">
                    <!-- This is  -->
                    <li class="nav-item"><a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark"
                                            href="javascript:void(0)"><i class="ti-menu"></i></a></li>
                    <li class="nav-item"><a
                                class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark"
                                href="javascript:void(0)"><i class="icon-arrow-left-circle"></i></a></li>

                </ul>

                <!-- ============================================================== -->
                <!-- User profile and search -->
                <!-- ============================================================== -->
                <ul class="navbar-nav my-lg-0">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-user-circle"></i>
                            {$manager->name|escape}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right animated flipInY">
                            <ul class="dropdown-user">
                                <li>
                                    <div class="dw-user-box">
                                        <div class="u-text">
                                            <h4>{$manager->name|escape}</h4>
                                            <p class="text-muted">{$manager->email}</p>
                                            {if $manager->role == 'developer'}<span
                                                    class="badge badge-danger">{$manager->role}</span>
                                            {elseif $manager->role == 'admin'}<span
                                                    class="badge badge-success">{$manager->role}</span>
                                            {elseif $manager->role == 'manager'}<span
                                                    class="badge badge-primary">{$manager->role}</span>
                                            {else}<span class="badge badge-info">{$manager->role}</span>{/if}
                                        </div>
                                    </div>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li><a href="manager/{$manager->id}"><i class="ti-user"></i> Профиль</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="logout"><i class="fa fa-power-off"></i> Выход</a></li>
                            </ul>
                        </div>
                    </li>

                </ul>
            </div>
        </nav>
    </header>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <aside class="left-sidebar">
        <!-- Sidebar scroll-->
        <div class="scroll-sidebar">
            <!-- Sidebar navigation-->
            <nav class="sidebar-nav">
                <ul id="sidebarnav">

                    {if in_array('orders', $manager->permissions) || in_array('clients', $manager->permissions) || in_array('offline', $manager->permissions) || in_array('penalties', $manager->permissions)}
                        <li class="nav-small-cap">Основные</li>
                        {if in_array('orders', $manager->permissions)}
                            <li {if !$offline && in_array($module, ['OrderController', 'OrdersController'])}class="active"{/if}>
                                <a class="" href="orders/" aria-expanded="false"><i class="mdi mdi-animation"></i><span
                                            class="hide-menu">Заявки</span></a>
                            </li>
                        {/if}

                        {*if in_array('offline', $manager->permissions)}
                        <li {if $offline}class="active"{/if}>
                            <a class="" href="orders/offline" aria-expanded="false"><i class="mdi mdi-animation"></i><span class="hide-menu">Оффлайн</span></a>
                        </li>
                        {/if*}

                        {if in_array('clients', $manager->permissions)}
                            <li {if in_array($module, ['ClientController', 'ClientsController'])}class="active"{/if}>
                                <a class="" href="clients/" aria-expanded="false"><i
                                            class="mdi mdi-chart-bubble"></i><span class="hide-menu">Клиенты</span></a>
                            </li>
                        {/if}
                    {/if}
                    {if  in_array('managers', $manager->permissions) ||  in_array('changelogs', $manager->permissions) ||  in_array('settings', $manager->permissions) ||  in_array('handbooks', $manager->permissions) ||  in_array('pages', $manager->permissions)}
                        <li class="nav-small-cap">Управление</li>
                        {if in_array('managers', $manager->permissions)}
                            <li {if in_array($module, ['ManagerController', 'ManagersController'])}class="active"{/if}>
                                <a class="" href="managers/" aria-expanded="false"><i
                                            class="mdi mdi-account-multiple-outline"></i><span class="hide-menu">Пользователи</span></a>
                            </li>
                        {/if}
                        {if in_array('changelogs', $manager->permissions)}
                            <li {if in_array($module, ['ChangelogsController'])}class="active"{/if}>
                                <a class="" href="changelogs/" aria-expanded="false"><i
                                            class="mdi mdi-book-open-page-variant"></i><span class="hide-menu">Логирование</span></a>
                            </li>
                        {/if}
                        {if in_array('settings', $manager->permissions) || in_array('offline_settings', $manager->permissions) || $manager->role == 'chief_collector'}
                            <li {if in_array($module, ['SettingsController', 'OfflinePointsController', 'ScoringsController', 'ApikeysController', 'WhitelistController', 'BlacklistController', 'PenaltyTypesController', 'RfmlistController'])}class="active"{/if}>
                                <a class="has-arrow" href="settings" aria-expanded="false"><i
                                            class="mdi mdi-settings"></i><span class="hide-menu">Настройки</span></a>
                                <ul aria-expanded="false" class="collapse">
                                        <li {if in_array($module, ['SettingsController'])}class="active"{/if}><a
                                                    href="settings/">Общие</a></li>
                                        {if $manager->role != 'chief_collector'}
                                            <li {if in_array($module, ['ScoringsController'])}class="active"{/if}><a
                                                        href="scorings/">Скоринги</a></li>
                                            <li {if in_array($module, ['ApikeysController'])}class="active"{/if}><a
                                                        href="apikeys/">Ключи для API</a></li>
                                            <li {if in_array($module, ['WhitelistController'])}class="active"{/if}><a
                                                        href="whitelist/">Whitelist</a></li>
                                            <li {if in_array($module, ['BlacklistController'])}class="active"{/if}><a
                                                        href="blacklist/">Blacklist</a></li>
                                            <li {if in_array($module, ['RfmlistController'])}class="active"{/if}><a
                                                        href="rfmlist/">RFMlist</a></li>
                                            <li {if in_array($module, ['SettingsController'])}class="active"{/if}><a
                                                        href="msg_zvonobot">IVR (-3,-2,-1)</a></li>
                                        {/if}
                                </ul>
                            </li>
                        {/if}
                        {if in_array('handbooks', $manager->permissions) || in_array('sms_templates', $manager->permissions) || in_array('tags', $manager->permissions) || in_array('communications', $manager->permissions)}
                            <li {if in_array($module, ['HandbooksController', 'ReasonsController', 'SmsTemplatesController', 'SettingsCommunicationsController', 'TicketStatusesController', 'TicketReasonsController'])}class="active"{/if}>
                                <a class="has-arrow" href="#" aria-expanded="false"><i
                                            class="mdi mdi-database"></i><span class="hide-menu">Справочники</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    {if in_array('handbooks', $manager->permissions)}
                                        <li {if in_array($module, ['ReasonsController'])}class="active"{/if}><a
                                                    href="reasons/">Причины отказа</a></li>
                                    {/if}
                                    {if in_array('sms_templates', $manager->permissions)}
                                        <li {if in_array($module, ['SmsTemplatesController'])}class="active"{/if}><a
                                                    href="sms_templates">Шаблоны сообщений</a></li>
                                    {/if}
                                    {if in_array('communications', $manager->permissions)}
                                        <li {if in_array($module, ['SettingsCommunicationsController'])}class="active"{/if}>
                                            <a href="settings_communications">Лимиты коммуникаций</a></li>
                                    {/if}
                                    {if in_array('handbooks', $manager->permissions)}
                                        <li {if in_array($module, ['PromocodesController'])}class="active"{/if}><a
                                                    href="promocodes/">Промокоды</a></li>
                                    {/if}
                                    {*if in_array('ticket_handbooks', $manager->permissions)}
                                    <li {if in_array($module, ['TicketStatusesController'])}class="active"{/if}><a href="ticket_statuses">Статусы тикетов</a></li>
                                    <li {if in_array($module, ['TicketReasonsController'])}class="active"{/if}><a href="ticket_reasons">Причины закрытия тикетов</a></li>
                                    {/if*}
                                </ul>
                            </li>
                        {/if}
                    {/if}

                    {if in_array('analitics', $manager->permissions) || in_array('penalty_statistics', $manager->permissions)}
                        <li class="nav-small-cap">Аналитика</li>
                        <li {if in_array($module, ['ToolsController'])}class="active"{/if}>
                            <a class="" href="tools" aria-expanded="false"><i class="mdi mdi-settings"></i><span
                                        class="hide-menu">Инструменты</span></a>
                        </li>
                        <li {if in_array($module, ['StatisticsController'])}class="active"{/if}>
                            <a class="" href="statistics" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span
                                        class="hide-menu">Статистика</span></a>
                        </li>
                    {/if}
                </ul>
            </nav>
            <!-- End Sidebar navigation -->
        </div>
        <!-- End Sidebar scroll-->
    </aside>
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->


    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    {$content}
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->

</div>

<div id="sms_code_modal"></div>

<script src="theme/{$settings->theme|escape}/assets/plugins/jquery/jquery.min.js?v=1.01"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="theme/{$settings->theme|escape}/assets/plugins/bootstrap/js/popper.min.js?v=1.02"></script>
<script src="theme/{$settings->theme|escape}/assets/plugins/bootstrap/js/bootstrap.js?v=1.01"></script>

<script src="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.min.js?v=1.01"></script>
<link rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css?v=1.01"/>

<!-- slimscrollbar scrollbar JavaScript -->
<script src="theme/{$settings->theme|escape}/js/jquery.slimscroll.js?v=1.01"></script>
<!--Wave Effects -->
<script src="theme/{$settings->theme|escape}/js/waves.js?v=1.01"></script>
<!--Menu sidebar -->
<script src="theme/{$settings->theme|escape}/js/sidebarmenu.js?v=1.01"></script>
<!--stickey kit -->
<script src="theme/{$settings->theme|escape}/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js?v=1.01"></script>

<script src="theme/{$settings->theme|escape}/assets/plugins/sweetalert2/dist/sweetalert2.all.min.js?v=1.01"></script>
<!--Custom JavaScript -->
<script src="theme/{$settings->theme|escape}/js/custom.min.js?v=1.01"></script>
<!-- ============================================================== -->

<link rel="stylesheet" href="theme/{$settings->theme|escape}/assets/plugins/autocomplete/styles.css?v=1.01"/>
<script src="theme/{$settings->theme|escape}/assets/plugins/autocomplete/jquery.autocomplete-min.js?v=1.01"></script>
<script src="theme/{$settings->theme|escape}/js/apps/dadata.app.js?v=1.03"></script>

<script src="theme/{$settings->theme|escape}/js/apps/run_scorings.app.js?v=1.01"></script>
<script src="theme/{$settings->theme|escape}/js/apps/sms.app.js?v=1.01"></script>

<script src="theme/{$settings->theme|escape}/js/apps/eventlogs.app.js?v=1.01"></script>

<script src="theme/{$settings->theme|escape}/js/apps/connexions.app.js?v=1.01"></script>

{$smarty.capture.page_scripts}
<!-- Style switcher -->
<!-- ============================================================== -->
<script src="theme/{$settings->theme|escape}/assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>


</body>

</html>
