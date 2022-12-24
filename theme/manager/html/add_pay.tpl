{$meta_title = 'Провести платеж' scope=parent}

{capture name='page_scripts'}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="theme/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="theme/manager/assets/plugins/moment/moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="theme/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
        $(function () {
            moment.locale('ru');

            $('.daterange').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'DD.MM.YYYY'
                },
            });

            $('.send_pay').on('click', function (e) {
                e.preventDefault();

                let form = $('#pay_form').serialize();
                let order_id = $('input[name="order_id"]').val();

                $.ajax({
                    method: 'POST',
                    data: form,
                    success: function () {
                        Swal.fire({
                            timer: 3000,
                            title: 'Платеж успешно проведен',
                            type: 'success',
                        }).then((result) => {
                            location.replace('/order/' + order_id + '');
                        })
                    }
                })
            });
        });
    </script>
{/capture}

{capture name='page_styles'}
    <link href="theme/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="theme/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
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
                    Платеж
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Платеж</li>
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
        <form id="pay_form">
            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Параметры платежа
                            </h3>
                        </div>
                        <input type="hidden" name="action" value="send_pay">
                        <input type="hidden" name="user_id" value="{$user_id}">
                        <input type="hidden" name="order_id" value="{$order_id}">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="col-form-label">Вид поступления</label>
                                <select class="form-control" name="pay_source">
                                    <option value="1">Платежный агент</option>
                                    <option value="2">Расчетный счет</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-form-label">Дата платежа</label>
                                <input type="text" class="form-control daterange" name="date">
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-form-label">Вид платежа</label>
                                <select class="form-control" name="pay_type">
                                    <option value="1">Частичная оплата</option>
                                    <option value="2">Пролонгация</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-form-label">Сумма платежа</label>
                                <input type="text" class="form-control" name="sum">
                            </div>
                        </div>
        </form>
    </div>

</div>

<hr class="mb-3 mt-3"/>
<div class="col-12 grid-stack-item">
    <input type="submit" class="btn btn-success send_pay" value="Сохранить">
</div>
</div>

{include file='footer.tpl'}
<!-- ============================================================== -->
</div>




