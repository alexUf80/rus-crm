{$meta_title = 'Настройка рекурентных платежей' scope=parent}

{capture name='page_styles'}
    <style>
        .row-attempts {
            margin-bottom: 20px;
        }
        .input-group-prepend .label{
            line-height: 34px;
        }
    </style>
{/capture}

{capture name='page_scripts'}
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.0/dist/vue.js"></script>
    <script src="theme/{$settings->theme|escape}/js/apps/recurrents.js"></script>
{/capture}

<div class="page-wrapper" id="app">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Рекурентные платежи</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Рекурентные платежи</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <h6 class="card-subtitle"></h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="day">Время попыток списания</label>
                                        <input type="text" disabled v-model="hour_time" class="form-control">
                                        <br><br>
                                        <div class="btn-group">
                                            <button type="button" :class="{
                                                'btn': true,
                                                'btn-primary': hour_type !== 'night',
                                                'btn-success': hour_type === 'night'
                                                }" @click="setHourType('night')">Ночь</button>
                                            <button type="button" :class="{
                                                'btn': true,
                                                'btn-primary': hour_type !== 'morning',
                                                'btn-success': hour_type === 'morning'
                                                }" @click="setHourType('morning')">Утро</button>
                                            <button type="button" :class="{
                                                'btn': true,
                                                'btn-primary': hour_type !== 'day',
                                                'btn-success': hour_type === 'day'
                                                }" @click="setHourType('day')">День</button>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Важные дни для списания</label>
                                            <div class="input-group form-group">
                                                <div class="input-group-prepend">
                                                    <span class="label label-success">Аванс</span>
                                                </div>
                                                <input @blur="validate" v-model="day_avans" type="text" @blur="validate" maxlength="2" @keypress="isNumber($event)" :class='{'{"form-control": true, "is-invalid": errors.day_avans.length}'}'>
                                                <div v-show="errors.day_avans.length" class="invalid-feedback">
                                                    {'{{errors.day_avans}}'}
                                                </div>
                                            </div>
                                            <div class="input-group form-group">
                                                <div class="input-group-prepend">
                                                    <span class="label label-success">Зарплата</span>
                                                </div>
                                                <input @blur="validate" v-model="day_zp" type="text" @blur="validate" maxlength="2" @keypress="isNumber($event)" :class='{'{"form-control": true, "is-invalid": errors.day_zp.length}'}'>
                                                <div v-show="errors.day_zp.length" class="invalid-feedback">
                                                    {'{{errors.day_zp}}'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button @click="saveConfig" class="btn btn-success">Сохранить</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="day">День на который включать автосписание</label>
                                            <input @blur="validate" :class='{'{"form-control": true, "is-invalid": errors.days.length}'}' id="day" @keypress="isNumber($event)" type="text" v-model="days">
                                            <div v-show="errors.days.length" class="invalid-feedback">
                                                {'{{errors.days}}'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Срок попыток списания просрочки</label>
                                            <select class="form-control" v-model="count_months">
                                                <option value="1">1 месяц</option>
                                                <option value="2">2 месяца</option>
                                                <option value="3">3 месяца</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Максимальное число неуспешных попыток в месяц</label>
                                            <input :class='{'{"form-control": true, "is-invalid": errors.max_count.length}'}' @keypress="isNumber($event)" @blur="validate" type="text" v-model="max_count">
                                            <div v-show="errors.max_count.length" class="invalid-feedback">
                                                {'{{errors.max_count}}'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row row-attempts" v-for="attempt in attempts">
                                    <div class="col-md-12">
                                        <label>Попытка {'{{attempt.number}}'}</label>
                                        <div class="btn-group">
                                            <button type="button" :class="{
                                    'btn btn-xs': true,
                                    'btn-primary': attempt.type === 'price',
                                    'btn-success': attempt.type === 'percent'
                                    }" @click="setAttemptType('percent', attempt.number)">%</button>
                                            <button type="button" :class="{
                                    'btn btn-xs': true,
                                    'btn-primary': attempt.type === 'percent',
                                    'btn-success': attempt.type === 'price'
                                    }" @click="setAttemptType('price', attempt.number)">Руб.</button>
                                        </div>
                                        <div class="input-group form-group">
                                            <div class="input-group-prepend">
                                                <span v-if="attempt.type == 'percent'" class="label label-info">%</span>
                                                <span v-else class="label label-info">Рублей</span>
                                            </div>
                                            <input @blur="validate" type="text"  @keypress="isNumber($event)" :class='{'{"form-control": true, "is-invalid": attempt.error.length}'}' v-model="attempt.summ">
                                            <div class="input-group-append">
                                                <button v-if="disabled_remove === false" @click="removeAttempt(attempt.number)" class="btn btn-xs btn-danger">X</button>
                                            </div>
                                            <div v-show="attempt.error.length" class="invalid-feedback">
                                                {'{{attempt.error}}'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div v-show="empty_attempt_error.length" class="label label-danger">
                                            {'{{empty_attempt_error}}'}
                                        </div>
                                        <button class="btn btn-info" @click="addAttempt">Добавить попытку</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {include file='footer.tpl'}

</div>