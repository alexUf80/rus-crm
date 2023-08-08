var application = new Vue({
    el: '#app',
    data: function() {
        return {
            disabled_remove: false,
            days: '',
            hour_type: 'morning',
            hour_time: '08:00',
            count_months: 1,
            max_count: 20,
            day_avans: '',
            day_zp: '',
            hour_types: {
                morning: '08:00',
                day: '14:00',
                night: '00:00',
            },
            attempts: [],
            empty_attempt_error: '',
            errors: {
                days: '',
                day_avans: '',
                day_zp: '',
                max_count: '',
            }
        }
    },
    methods: {
        addAttempt() {
            Vue.set(this.attempts, this.attempts.length, {
                number: this.attempts.length + 1,
                summ: 0,
                type: 'price',
                error: '',
            })
            this.validate();
        },
        removeAttempt(number) {
            this.attempts.forEach((item, key) => {
                if (item.number == number) {
                    Vue.delete(this.attempts, key);
                }
            });

            this.setNumbers();
            this.validate();
        },
        setNumbers() {
            this.attempts.forEach((item, key) => {
                this.attempts[key].number = key + 1;
            });
        },
        setAttemptType(type, number) {
            this.attempts.forEach((item, key) => {
                if (item.number == number) {
                    this.attempts[key].type = type;
                }
            });
        },
        setHourType(type) {
            Vue.set(this, 'hour_type', type);
            Vue.set(this, 'hour_time', this.hour_types[type]);
        },
        isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                evt.preventDefault();;
            } else {
                return true;
            }
        },
        saveConfig() {
            if (this.validate()) {
                $.ajax({
                    type: "post",
                    dataType: 'json',
                    data: {
                        action: 'create',
                        hour_time: this.hour_time,
                        day_avans: this.day_avans,
                        day_zp: this.day_zp,
                        days: this.days,
                        count_months: this.count_months,
                        max_count: this.max_count,
                        attempts: this.attempts,
                    },
                    success: function() {
                        Swal.fire({
                            title: 'Успешно!',
                            text: 'Новая конфигурация рекурентных платежей создана.',
                            type: 'success',
                        });
                    }
                })
            }
        },
        validate() {
            if (this.day_avans.length <= 0) {
                this.errors.day_avans = 'Укажите число месяца.';
                return false;
            } else {
                this.errors.day_avans = '';
            }
            if (this.day_avans <= 0) {
                this.errors.day_avans = 'Минимальное значение 1.';
                return false;
            } else {
                this.errors.day_avans = '';
            }
            if (this.day_avans > 31) {
                this.errors.day_avans = 'Минимальное значение 31.';
                return false;
            } else {
                this.errors.day_avans = '';
            }

            if (this.day_zp.length <= 0) {
                this.errors.day_zp = 'Укажите число месяца.';
                return false;
            } else {
                this.errors.day_zp = '';
            }
            if (this.day_zp <= 0) {
                this.errors.day_zp = 'Минимальное значение 1.';
                return false;
            } else {
                this.errors.day_zp = '';
            }
            if (this.day_zp > 31) {
                this.errors.day_zp = 'Минимальное значение 31.';
                return false;
            } else {
                this.errors.day_zp = '';
            }

            if (this.day_zp === this.day_avans) {
                this.errors.day_zp = 'Нельзя использовать один и тот же день.';
                this.errors.day_avans = 'Нельзя использовать один и тот же день.';
                return false;
            } else {
                this.errors.day_zp = '';
                this.errors.day_avans = '';
            }

            if (this.days.length <= 0) {
                this.errors.days = 'Укажите кол-во дней.';
                return false;
            } else {
                this.errors.days = '';
            }
            if (this.days <= 0) {
                this.errors.days = 'Укажите минимум 1 день.';
                return false;
            } else {
                this.errors.days = '';
            }

            if (this.max_count.length <= 0) {
                this.errors.max_count = 'Укажите кол-во попыток списания.';
                return false;
            } else {
                this.errors.max_count = '';
            }
            if (this.max_count <= 0) {
                this.errors.max_count = 'Минимальное значение 1.';
                return false;
            } else {
                this.errors.max_count = '';
            }
            if (this.max_count > 20) {
                this.errors.max_count = 'Максимальное значение 20.';
                return false;
            } else {
                this.errors.max_count = '';
            }

            if (this.attempts.length <= 0) {
                this.empty_attempt_error = 'Укажите хотя бы одну попытку списания.';
                return false;
            } else {
                this.empty_attempt_error = '';
            }

            let returnValue = true;

            this.attempts.forEach((item, key) => {
                if (item.summ <= 0) {
                    this.attempts[key].error = 'Укажите значение для списания.';
                    returnValue = false;
                } else {
                    this.attempts[key].error = '';
                }
            });

            return returnValue;
        },
    },
    watch: {
        attempts: function(newValue) {
            if (newValue.length <= 1) {
                Vue.set(this, 'disabled_remove', true)
            } else {
                Vue.set(this, 'disabled_remove', false)
            }
        }
    },
    mounted: function() {
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                action: 'get_actual_config',
            },
            success: (response) => {
                if (response.status == 'ok') {
                    this.days = response.config.days;
                    this.day_avans = response.config.day_avans;
                    this.day_zp = response.config.day_zp;
                    this.count_months = response.config.count_months;
                    this.hour_time = response.config.hour_time;
                    if (this.hour_time == '08:00') {
                        this.hour_type = 'morning';
                    }
                    if (this.hour_time == '14:00') {
                        this.hour_type = 'day';
                    }
                    if (this.hour_time == '00:00') {
                        this.hour_type = 'night';
                    }
                    this.max_count = response.config.max_count;
                    this.attempts = response.config.attempts;
                }
            }
        })
    }
});