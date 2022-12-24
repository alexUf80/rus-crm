{$wrapper='' scope=parent}
<!DOCTYPE html>
<html lang="ru">

<head>
    <base href="{$config->root_url}/"/>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="theme/{$settings->theme|escape}/assets/images/favicon.png">
    <title>Вход в панель управления</title>

    <!-- Bootstrap Core CSS -->
    <link href="theme/{$settings->theme|escape}/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="theme/{$settings->theme|escape}/css/style.css" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="theme/{$settings->theme|escape}/css/colors/default.css" id="theme" rel="stylesheet">
    <!-- Custom CSS -->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="">
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
<section id="wrapper" class="login-register login-sidebar"
         style="background-image:url(theme/{$settings->theme|escape}/assets/images/background/login-register.jpg);">
    <div class="login-box card">
        <div class="card-body">

            {if !$select_offline_point}
                <form class="form-horizontal form-material" id="loginform" method="POST">

                    <div class="text-center mt-3">
                        <img src="theme/{$settings->theme|escape}/assets/images/logo_bf.svg" alt="homepage"
                             class="dark-logo img-responsive"/>
                    </div>

                    {if $error}
                        <div class="alert alert-danger mt-5">
                            {if $error == 'login_incorrect'}Логин или пароль не совпадают
                            {else}{$error}{/if}
                        </div>
                    {/if}

                    <div class="form-group mt-5">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" required="" name="login" value="{$login|escape}"
                                   placeholder="Логин"></div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input class="form-control" type="password" required="" name="password"
                                   placeholder="Пароль"></div>
                    </div>
                    {*
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="checkbox checkbox-primary float-left pt-0">
                                <input id="checkbox-signup" type="checkbox" name="remember" value="1">
                                <label for="checkbox-signup"> Запомнить меня </label>
                            </div> 
                            <a href="/resset_password" id="to-recover" class="text-dark float-right"><i class="fa fa-lock mr-1"></i> Забыли пароль?</a>
                        </div>
                    </div>
                    *}
                    <div class="form-group text-center mt-3">
                        <div class="col-xs-12">
                            <button class="btn btn-outline-info btn-lg btn-block text-uppercase waves-effect waves-light"
                                    type="submit">Войти
                            </button>
                        </div>
                    </div>
                </form>
            {else}
                <form class="form-horizontal form-material" id="offlineform" method="POST">

                    <input type="hidden" name="offline_form" value="1"/>

                    <div class="text-center mt-3">
                        <img src="theme/{$settings->theme|escape}/assets/images/logo.png" alt="homepage"
                             class="dark-logo"/>
                    </div>

                    {if $error}
                        <div class="alert alert-danger mt-5">
                            {if $error == 'login_incorrect'}Логин или пароль не совпадают
                            {else}{$error}{/if}
                        </div>
                    {/if}

                    <div class="form-group mt-5">
                        <div class="col-xs-12">
                            <label>Выберите отделение</label>
                            <select name="offline_point_id" class="form-control">
                                <option value=""></option>
                                {foreach $offline_points as $point}
                                    <option value="{$point->id}">{$point->city} {$point->address}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group text-center mt-3">
                        <div class="col-xs-12">
                            <button class="btn btn-outline-info btn-lg btn-block text-uppercase waves-effect waves-light"
                                    type="submit">Выбрать
                            </button>
                        </div>
                    </div>
                </form>
            {/if}

        </div>
    </div>

</section>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<script src="theme/{$settings->theme|escape}/assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="theme/{$settings->theme|escape}/assets/plugins/bootstrap/js/popper.min.js"></script>
<script src="theme/{$settings->theme|escape}/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="theme/{$settings->theme|escape}/js/jquery.slimscroll.js"></script>
<!--Wave Effects -->
<script src="theme/{$settings->theme|escape}/js/waves.js"></script>
<!--Menu sidebar -->
<script src="theme/{$settings->theme|escape}/js/sidebarmenu.js"></script>
<!--stickey kit -->
<script src="theme/{$settings->theme|escape}/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
<!--Custom JavaScript -->
<script src="theme/{$settings->theme|escape}/js/custom.min.js"></script>
<!-- ============================================================== -->

<!-- Style switcher -->
<!-- ============================================================== -->
<script src="theme/{$settings->theme|escape}/assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
</body>

</html>