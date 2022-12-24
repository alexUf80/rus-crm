function OrdersApp()
{
    var app = this;
    
    app.update_page_interval;
    
    var _init = function(){
        _init_open_order();
        _init_pagination();
        _init_sortable();
        _init_filter();
        _init_period();
        _init_filter_status();
        _init_filter_client();
        
        _init_scorista_run();
        _init_juice_run();
        
        _init_image_rotate();

        _init_daterange();
        
        _init_workout();
        
//        _init_open_image_popup();
        
/*
        app.update_page_interval = setInterval(function(){
            $.ajax({
                success: function(resp){
                    $('#basicgrid').html($(resp).find('#basicgrid').html());
                }
            })
        }, 60000);
*/
    };
    
    var _init_daterange = function(){
        $('.daterange').daterangepicker({
            autoApply: true,
            locale: {
                format: 'DD.MM.YYYY'
            },
            default:''
        });
        
        $(document).on('change', '.js-daterange-input', function(){
            app.filter()
        })
        
        $(document).on('click', '.js-open-daterange', function(e){
            e.preventDefault();
            
            $('#filter_period').val('optional')
            
            $('.js-period-filter button').html('<i class="fas fa-calendar-alt"></i> Произвольный')
            $('.js-period-filter .dropdown-item').removeClass('active');
            $(this).addClass('active')
            
            $('.js-daterange-filter').show();
        });        
    }
    
    var _init_image_rotate = function(){
        $(document).on('click', '.mpf-rotate', function(e){
            e.preventDefault();
            e.stopPropagation();
            
            var $img = $('.mpf-img');
            if ($img.hasClass('rotate90'))
            {
                $img.removeClass('rotate90').addClass('rotate180');
            }
            else if ($img.hasClass('rotate180'))
            {
                $img.removeClass('rotate180').addClass('rotate270');
            }
            else if ($img.hasClass('rotate270'))
            {
                $img.removeClass('rotate270');
            }
            else
            {
                $img.addClass('rotate90');
            }
        });
    }
    
    var _init_scorista_run = function(){
        $(document).on('click', '.js-scorista-run', function(e){
            
            var _order_id = $(this).data('order')
            
            $.ajax({
                url: 'ajax/scorista.php',
                data: {
                    action: 'create',
                    order_id: _order_id
                },
                beforeSend: function(){
                    
                },
                success: function(resp){

                    if (!!resp.status)
                    {
                        if (resp.status == 'ERROR')
                        {
                            Swal.fire(
                                'Ошибка',
                                'Не достаточно данных для проведения скоринг-тестов.',
                                'error'
                            )
                        }
                        else
                        {
                            
                            Swal.fire(
                                'Запрос отправлен',
                                'Время получения ответа от 30 секунд до 2 минут.<br />Идентификатор запроса: '+resp.requestid,
                                'success'
                            );
                            setTimeout(function(){
                                _scorista_get_result(resp.requestid);
                            }, 15000);
                            
                        }
                    }
                    else
                    {
                        Swal.fire(
                            'Ошибка',
                            '',
                            'error'
                        )
                    }
                }
            })
            
        })
    };
    
    var _scorista_get_result = function(request_id){
        $.ajax({
            url: 'ajax/scorista.php',
            data: {
                action: 'result',
                request_id : request_id
            },
            beforeSend: function(){
                
            },
            success: function(resp){
                if (!!resp.status)
                {
                    if (resp.status == 'ERROR')
                    {
                        Swal.fire(
                            'Ошибка',
                            resp.error.message,
                            'error'
                        );
                    }
                    else if (resp.status == 'DONE')
                    {
                        if (resp.data.decision.decisionName == 'Отказ')
                        {
                            Swal.fire(
                                'Скоринг тест завершен',
                                'Результат: Отказ<br />Скорбалл: '+resp.data.additional.summary.score,
                                'warning'
                            );
                        }
                        else
                        {
                            Swal.fire(
                                'Скоринг тест завершен',
                                'Результат: '+resp.data.decision.decisionName+'<br />Скорбалл: '+resp.data.additional.summary.score,
                                'success'
                            );
                        }
                        
                    }
                    else
                    {
                        setTimeout(function(){
                            _scorista_get_result(request_id);
                        }, 5000);
                    }
                }
                else
                {
                    
console.log(resp);   
                }             
            }
        })
    };
    
    var _init_juice_run = function(){
        $(document).on('click', '.js-juice-run', function(e){
            
            var _order_id = $(this).data('order')
            
            $.ajax({
                url: 'ajax/juicescore.php',
                data: {
                    action: 'run',
                    order_id: _order_id
                },
                beforeSend: function(){
                    
                },
                success: function(resp){

                    if (!!resp.error)
                    {
                        Swal.fire('Ошибка', resp.error, 'error');                        
                    }
                    else
                    {
                        Swal.fire(
                                'Скоринг тест завершен',
                                'Результат: '+resp["AntiFraud score"],
                                'success'
                            );
                    }
                }
            })
            
        })
    };


    
    var _init_open_order = function(){
        $(document).on('click', '.js-open-order', function(e){
            e.preventDefault();
            
            if ($(this).hasClass('open'))
            {
                $(this).removeClass('open')
                $('.order-details').remove();
            }
            else
            {
                $(this).addClass('open')
                
                var _id = $(this).data('id');
                var _row = $(this).closest('.jsgrid-row');
                
                $('.order-details').remove();
                
                $.ajax({
                    url: 'order/'+_id,
                    data: {
                        ajax: 1
                    },
                    beforeSend: function(){
                        
                    },
                    success: function(resp){
                        _row.after('<tr class="order-details"><td colspan="10"></td></tr>');
                        $('.order-details td').html($(resp).find('#order_wrapper'));
                        
                        
                        
                        new OrderApp()
                    }
                })
            }
        })
    }
    
    var _init_period = function(){
        $(document).on('click', '.js-period-link', function(e){
            e.preventDefault();
            
            $('.js-daterange-filter').hide();
            
            var _url = $(this).attr('href');
            
            app.load(_url, false);
        });
    };
    
    var _init_pagination = function(){
        $(document).on('click', '.jsgrid-pager a', function(e){
            e.preventDefault();
            
            var _url = $(this).attr('href');
            
            app.load(_url, true);
        });
        
        $(document).on('change', '.js-page-count', function(e){
            e.preventDefault();
            
            var _url = $(this).val();
            
            app.load(_url, true);
        });
        
    };
    
    var _init_sortable = function(){
        $(document).on('click', '.jsgrid-header-sortable a', function(e){
            e.preventDefault();
            
            var _url = $(this).attr('href');
            
            app.load(_url, true);
        });
    };
    
    var _init_filter_status = function(){
        $(document).on('click', '.js-filter-status a', function(e){
            e.preventDefault();
            
            var _url = $(this).attr('href');
            
            app.load(_url, false);
        });
    };
    
    var _init_filter_client = function(){
        $(document).on('click', '.js-filter-client a', function(e){
            e.preventDefault();
            
            var _url = $(this).attr('href');
            
            app.load(_url, false);
        });
    };
    
    var _init_filter = function(){
//        $(document).on('blur', '.jsgrid-filter-row input', app.filter);
        $(document).on('keyup', '.jsgrid-filter-row input', function(e){
            if (e.keyCode == 13){
                app.filter();
            }   
        });
        $(document).on('change', '.jsgrid-filter-row input', app.filter);
        $(document).on('change', '.jsgrid-filter-row select', app.filter);
    };
    
    app.filter = function(){
        var $form = $('#search_form');
        var _sort = $form.find('[name=sort]').val()
        var _searches = {};
        $form.find('input[type=text], select').each(function(){
            if ($(this).val() != '')
            {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });     
        var filter_status = $('#filter_status').val() || '';
        var filter_client = $('#filter_client').val() || '';
        var filter_period = $('#filter_period').val() || '';
        var daterange = $('.js-daterange-input').val() || '';
        
        var _request = {
//            search: _searches,
            sort: _sort,
            status: filter_status,
            client: filter_client,
            period: filter_period,
            daterange: daterange,
        };
        var _query = Object.keys(_request).map(
            k => encodeURIComponent(k) + '=' + encodeURIComponent(_request[k])
        ).join('&');

        _request.search = _searches;
        if (!$.isEmptyObject(_searches))
        {
            _query_searches = '';
            for (key in _searches) {
              _query_searches += '&search['+key+']='+_searches[key];
            }
            _query += _query_searches;
        }
        
        $.ajax({
            data: _request,
            beforeSend: function(){
            },
            success: function(resp){
                
                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());
                $('#basicgrid .jsgrid-footer-row').html($(resp).find('#basicgrid .jsgrid-footer-row').html());
                $('.js-period-filter').html($(resp).find('.js-period-filter').html());
                $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                $('.js-filter-client').html($(resp).find('.js-filter-client').html());

                $('.jsgrid-pager-container').html($(resp).find('.jsgrid-pager-container').html() || '');
                
                if (!!_query)
                    location.hash = _query;
            }
        })
    
    };
    
    var _init_workout = function(){
        $(document).on('change', '.js-workout-input', function(){
            var $this = $(this);
            
            var _order = $this.val();
            var _workout = $this.is(':checked') ? 1 : 0;
                
            $.ajax({
                url: 'order/'+_order,
                type: 'POST',
                data: {
                    action: 'workout',
                    order_id: _order,
                    workout: _workout
                },
                beforeSend: function(){
                    $('.jsgrid-load-shader').show();
                    $('.jsgrid-load-panel').show();
                },
                success: function(resp){
                    
                    if (_workout)
                        $this.closest('.js-order-row').addClass('workout-row');
                    else
                        $this.closest('.js-order-row').removeClass('workout-row');

                    $('.jsgrid-load-shader').hide();
                    $('.jsgrid-load-panel').hide();
                        
                    /*
                    $.ajax({
                        success: function(resp){
                            $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                            $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());
                            $('.js-period-filter').html($(resp).find('.js-period-filter').html());
                            $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                            $('.js-filter-client').html($(resp).find('.js-filter-client').html());
            
                            $('.jsgrid-pager-container').html($(resp).find('.jsgrid-pager-container').html());

                            $('.jsgrid-load-shader').hide();
                            $('.jsgrid-load-panel').hide();
                        }
                    });
                    */
                }
            })
                
        });
    }
    
    app.load = function(_url, loading){

        var _split = _url.split('?');

        $.ajax({
            url: _url,
            beforeSend: function(){
                if (loading)
                {
                    $('.jsgrid-load-shader').show();
                    $('.jsgrid-load-panel').show();
                }
            },
            success: function(resp){
                
                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());
                $('#basicgrid .jsgrid-footer-row').html($(resp).find('#basicgrid .jsgrid-footer-row').html());
                $('.js-period-filter').html($(resp).find('.js-period-filter').html());
                $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                $('.js-filter-client').html($(resp).find('.js-filter-client').html());

                $('.jsgrid-pager-container').html($(resp).find('.jsgrid-pager-container').html() || '');
                
                if (!!_split[1])
                    location.hash = _split[1];

                if (loading)
                {
                    $('html, body').animate({
                        scrollTop: $("#basicgrid").offset().top-80  
                    }, 1000);
                    
                    $('.jsgrid-load-shader').hide();
                    $('.jsgrid-load-panel').hide();
                }
                 
            }
        })
    };
    
    ;(function(){
        
        _init();
        
        if (location.hash != '#' && location.hash != '')
        {
            app.load((location.href).replace('#', '?'));
        }
        
    })();
};

new OrdersApp();