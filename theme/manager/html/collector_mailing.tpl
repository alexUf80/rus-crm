{$meta_title="Рассылка" scope=parent}

{capture name='page_scripts'}
    <script>
        $(function () {
            $('.js-collector-check').change(function () {
                var _collectors = [];
                $('.js-collector-check').each(function () {
                    if ($(this).is(':checked')) {
                        _collectors.push($(this).val());
                    }
                });
                console.log(_collectors)
                $.ajax({
                    data: {
                        action: 'calc',
                        collectors: _collectors
                    },
                    success: function (resp) {
                        console.info(resp)
                        $('.js-mkk-count').text(resp.mkk);
                        $('.js-yuk-count').text(resp.yuk);
                    }
                })
            })
        })
    </script>
{/capture}

{capture  name='page_styles'}
    <style>
        .onoffswitch {
            display: inline-block !important;
            vertical-align: top !important;
            width: 60px !important;
            text-align: left;
        }

        .onoffswitch-switch {
            right: 38px !important;
            border-width: 1px !important;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px !important;
        }

        .onoffswitch-label {
            margin-bottom: 0 !important;
            border-width: 1px !important;
        }

        .onoffswitch-inner::after,
        .onoffswitch-inner::before {
            height: 18px !important;
            line-height: 18px !important;
        }

        .onoffswitch-switch {
            width: 20px !important;
            margin: 1px !important;
        }

        .onoffswitch-inner::before {
            content: 'ВКЛ' !important;
            padding-left: 10px !important;
            font-size: 10px !important;
        }

        .onoffswitch-inner::after {
            content: 'ВЫКЛ' !important;
            padding-right: 6px !important;
            font-size: 10px !important;
        }

        .scoring-content {
            position: relative;
            z-index: 999;
            border: 1px solid rgba(120, 130, 140, 0.13);;
            border-top: 0;
            background: #fff;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
            margin-top: -5px;
        }

        .collapsed .fa-minus-circle::before {
            content: "\f055";
        }

        h4.text-white {
            display: inline-block
        }

        .move-zone {
            display: inline-block;
            color: #fff;
            padding-right: 15px;
            margin-right: 10px;
            border-right: 1px solid #30b2ff;
            cursor: move
        }

        .move-zone span {
            font-size: 24px;
        }

        .dd {
            max-width: 100%;
        }
    </style>
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
                    Рассылка
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Рассылка</li>
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
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">

                        {if $success}
                            <div class="col-12">
                                <div class="alert alert-success">
                                    Рассылка успешно запущена
                                </div>
                            </div>
                        {/if}

                        {if $errors}
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    <h3 class="pl-5">Ошибка</h3>
                                    <ul>
                                        {foreach $errors as $error}
                                            <li>{$error}</li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                        {/if}

                        <div class="col-md-6">
                            <div class="mb-2 border card-outline-primary">
                                <h5 class="card-header">
                                    <span class="text-white">Предварительная рассылка</span>
                                </h5>
                                <ul class="p-2">
                                    <li class="row">
                                        <div class="col-12" style="display: flex; flex-direction: row; justify-content: space-between">
                                            <div class="custom-checkbox">
                                                <input class="custom-checkbox-input" name="warning[]" value="3" type="checkbox">
                                                <label>
                                                    3 дня до просрочки
                                                </label>
                                            </div>
                                            <div class="custom-checkbox">
                                                <input class="custom-checkbox-input" name="warning[]" value="2" type="checkbox">
                                                <label>
                                                    2 дня до просрочки
                                                </label>
                                            </div>
                                            <div class="custom-checkbox">
                                                <input class="custom-checkbox-input" name="warning[]" value="1" type="checkbox">
                                                <label>
                                                    1 день до просрочки
                                                </label>
                                            </div>
                                            <div class="custom-checkbox">
                                                <input class="custom-checkbox-input" name="warning[]" value="0" type="checkbox">
                                                <label>
                                                    день возврата займа
                                                </label>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            {foreach $collectors as $c_status => $tc}
                                <div class="mb-2 border {if $c_status==4}card-outline-primary{elseif $c_status==6}card-outline-warning{elseif $c_status==8}card-outline-danger{/if}">
                                    <h5 class="card-header">
                                        <span class="text-white">{$collection_statuses[$c_status]}</span>
                                    </h5>
                                    <ul class="p-2">
                                        {foreach $tc as $c}
                                            <li class="row">
                                                <div class="col-12">
                                                    <div class="custom-checkbox">
                                                        <input class="js-collector-check custom-checkbox-input"
                                                               type="checkbox" name="collectors[]" value="{$c->id}"
                                                               id="collector_{$c->id}"
                                                               {if in_array($c->id, (array)$collectors)}checked="true"{/if} />
                                                        <label class="custom-checkbox-label" for="collector_{$c->id}">
                                                            {$c->name|escape}
                                                            {if $c->blocked}<span>Заблокирован</span>{/if}
                                                        </label>
                                                    </div>
                                                </div>

                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            {/foreach}
                        </div>

                        <div class="col-md-6">

                            <div class="clearfix">
                                <div class="float-left">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="zvonobot" value="1" class="onoffswitch-checkbox"
                                               id="zvonobot" {if $zvonobot}checked="true"{/if} >
                                        <label class="onoffswitch-label" for="zvonobot">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="float-left">
                                    <h4 class="pl-3"><label for="zvonobot">Звонобот</label></h4>
                                </div>
                            </div>
                            <div class="clearfix">
                                <div class="float-left">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="sms" value="1" class="onoffswitch-checkbox"
                                               id="sms" {if $sms}checked="true"{/if} />
                                        <label class="onoffswitch-label" for="sms">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="float-left">
                                    <h4 class="pl-3"><label for="sms">СМС рассылка</label></h4>
                                </div>
                            </div>


                            <div class="border">
                                <h5 class="card-header"><span class="">МКК</span></h5>
                                <div class="p-2">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-5 ">
                                                <label class="control-label">Проверочный номер</label>
                                            </div>
                                            <div class="col-7 ">
                                                <input type="text" class="form-control" name="mkk_check_number"
                                                       value="{$mkk_check_number}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-5 ">
                                                <label class="control-label">Сообщение</label>
                                            </div>
                                            <div class="col-7 ">
                                                <textarea name="mkk_text" class="form-control">{$mkk_text}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-12">
                            <hr class="m-2"/>
                            <div class="row">
                                <div class="col-8">
                                    <div class="text-right">Клиенты МКК: <strong
                                                class="js-mkk-count text-primary">0</strong></div>
                                    <div class="text-right">Клиенты ЮК: <strong
                                                class="js-yuk-count text-primary">0</strong></div>
                                </div>
                                <div class="col-4">
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-success btn-lg">Запустить рассылку</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
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


