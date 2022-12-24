function SudblockContractApp()
{
    var app = this;    
    
    app.sms_timer;
    
    app.image_deg = 0;
    
    app.PrintAll = function(pages) {
        for (var i = 0; i < pages.length; i++) {
            var oWindow = window.open(pages[i], 'document'+i);
            oWindow.print();
//            oWindow.close();
        }
    }    
    
    app.printPdf = function (url) {
      var iframe = this._printIframe;
      if (!this._printIframe) {
        iframe = this._printIframe = document.createElement('iframe');
        document.body.appendChild(iframe);
    
        iframe.style.display = 'none';
        iframe.onload = function() {
          setTimeout(function() {
            iframe.focus();
            iframe.contentWindow.print();
          }, 1);
        };
      }
    
      iframe.src = url;
    }
    
    var _init_print_button = function(){
        $(document).on('click', '.js-print-document', function(){
            var _href = $(this).closest('.js-document-row').find('[type=checkbox]').data('href');

            app.printPdf(_href);
        });
        
        $(document).on('click', '.js-print-all-documents', function(){
            var _pages = [];
            $(this).closest('.js-block-documents').find('[type=checkbox]').each(function(){
                _pages.push($(this).data('href'));
            });
            
            app.PrintAll(_pages)
        });

        $(document).on('click', '.js-print-check-documents', function(){
            var _pages = [];
            $(this).closest('.js-block-documents').find('[type=checkbox]').each(function(){
                if ($(this).is(':checked'))
                {
                    var _href = $(this).data('href');                    
                    _pages.push(_href);
                }
            });
            
            app.PrintAll(_pages)
        });
    }
    
    var _init_send_sud = function(){
        $(document).on('click', '.js-send-sud', function(e){
            e.preventDefault();
            
            var _not_ready = 0;
            $('.js-sud-documents .js-ready-document').each(function(){
                if (!$(this).hasClass('js-is-ready'))
                    _not_ready = 1;
            });
            
            if (!!_not_ready)
            {
                Swal.fire({
                    title: 'Все документы должны быть готовы.',
                    type: 'error',
                });
            }
            else
            {
                var sud_post_number = $('.js-send-sud-post-number').val();
                if (0 && sud_post_number == '')
                {
                    Swal.fire({
                        title: 'Укажите номер почтового отправления.',
                        type: 'error',
                    });
                    $('.js-send-sud-post-number').focus();
                }
                else
                {
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'send_sud',
                            post_number: sud_post_number
                        },
                        success: function(resp){
                            app.update_page();
                        }
                    })
                }
                console.log('is-ready')
            }
            
        })
    }

    var _init_send_fssp = function(){
        $(document).on('click', '.js-send-fssp', function(e){
            e.preventDefault();
            
            var _not_ready = 0;
            $('.js-fssp-documents .js-ready-document').each(function(){
                if (!$(this).hasClass('js-is-ready'))
                    _not_ready = 1;
            });
            
            if (!!_not_ready)
            {
                Swal.fire({
                    title: 'Все документы должны быть готовы.',
                    type: 'error',
                });
            }
            else
            {
                var fssp_post_number = $('.js-send-fssp-post-number').val();
                if (0 && fssp_post_number == '')
                {
                    Swal.fire({
                        title: 'Укажите номер почтового отправления.',
                        type: 'error',
                    });
                    $('.js-send-fssp-post-number').focus();
                }
                else
                {
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'send_fssp',
                            post_number: fssp_post_number
                        },
                        success: function(resp){
                            app.update_page();
                        }
                    })
                }
                console.log('is-ready')
            }
            
        })
    }

    var _init_add_document = function(){
        // Открытие окна для добавления
        $(document).on('click', '.js-open-add-document', function(e){
            e.preventDefault();
            
            var _block = $(this).data('type');
            $('#modal_add_document').find('[name=block]').val(_block);
            
            $('#modal_add_document').find('.alert').hide();
            $('#modal_add_document').find('[name=name]').val('');
            
            $('#modal_add_document').modal();
            
            $('#modal_add_document').find('[name=name]').focus();
        });

        // Сохранение нового документа
        $(document).on('submit', '#form_add_document', function(e){            
            e.preventDefault();
            
            var $form = $(this);
            
            var input = document.getElementById('file_input');
            
            var _name = $form.find('[name=name]').val();
            var _provider = $form.find('[name=provider]').val();
            var _base = $form.find('[name=base]').val();
            var _block = $form.find('[name=block]').val();
            var _sudblock_contract_id = $form.find('[name=sudblock_contract_id]').val();
            
            var form_data = new FormData();
                        
            form_data.append('file', input.files[0])
            form_data.append('name', _name)
            form_data.append('provider', _provider);        
            form_data.append('base', _base);        
            form_data.append('block', _block);        
            form_data.append('sudblock_contract_id', _sudblock_contract_id);        
            form_data.append('action', 'add');        


            $.ajax({
                url: '/sudblock_documents',
                type: 'POST',
                data: form_data,
                processData : false,
                contentType : false, 
                beforeSend: function(){
                    
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    }
                    else
                    {                                                        
                        $('#modal_add_document').modal('hide');
                        Swal.fire({
                            timer: 5000,
                            text: 'Документ "'+resp.name+'" добавлен!',
                            type: 'success',
                        });                                
                        
                        location.reload();
                    }
                }
            })
        });
    }
    
    var _init_remove_document = function(){
        
        $(document).on('click', '.js-remove-document', function(e){
            e.preventDefault();
            
            var document_id = $(this).data('document');
        
            Swal.fire({
                title: 'Вы действительно хотите удалить файл?',
                text: "",
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Отменить',
                confirmButtonText: 'Да, удалить'
            }).then((result) => {
                if (result.value) {    
                    location.href = location.href+'?action=remove&document='+document_id;
                }
            });

        })
                
    };
    
    var _init_workout_change = function(){
        $(document).on('change', '.js-workout-label', function(){
            var contract_id = $(this).data('contract');
            var workout = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: '/sudblock_contracts',
                type: 'POST',
                data: {
                    action: 'workout',
                    contract_id: contract_id,
                    workout: workout
                },
                
            });
        })
    }
    
    var _init_manager_select = function()
    {
        $(document).on('change', '.js-manager-select', function(){
            var _contract = $(this).data('contract');
            var _manager = $(this).val();
            
            $.ajax({
                url: '/sudblock_contracts',
                type: 'POST',
                data: {
                    action: 'manager',
                    contract_id: _contract,
                    manager_id: _manager,
                },
                success: function(resp){
                    if (!!resp.success)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Менеджер изменен.',
                            type: 'success',
                        });

                    }
                }
            });
        });
    }

    var _init_status_select = function()
    {
        $(document).on('change', '.js-status-select', function(){
            var _contract = $(this).data('contract');
            var _status = $(this).val();
            
            $.ajax({
                url: '/sudblock_contracts',
                type: 'POST',
                data: {
                    action: 'status',
                    contract_id: _contract,
                    status_id: _status,
                },
                success: function(resp){
                    if (!!resp.success)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Статус изменен.',
                            type: 'success',
                        });

                    }
                }
            });
        });
    }
    
    var _init_ready_document_button = function(){
        $(document).on('click', '.js-ready-document', function(){
            
            var $this = $(this);
            
            if ($this.hasClass('loading'))
                return false;
            
            var document = $this.data('document');
            var ready = $this.data('ready') ? 0 : 1;
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'ready_document',
                    document_id: document,
                    ready: ready
                },
                beforeSend: function(){
                    $this.addClass('loading');
                },
                success: function(resp){
                    $this.data('ready', resp.ready);
                    if (!!resp.ready)
                    {
                        $this.removeClass('btn-outline-secondary').addClass('btn-success').addClass('js-is-ready');
                    }
                    else
                    {
                        $this.removeClass('btn-success').addClass('btn-outline-secondary').removeClass('js-is-ready');
                    }
                    $this.removeClass('loading');
                }
            })
            
        });
    };
    
    var _init_notification_form = function(){
        
        $(document).on('submit', '#form_add_notification', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            $.ajax({
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $form.find('[name=comment]').val('')
            
                        app.update_page();
                        
                        Swal.fire({
                            timer: 5000,
                            title: 'Напоминание добавлено.',
                            type: 'success',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                        
                    }
                }
            })
        })
    }
    
    var _init_sudprikaz = function(){
        $(document).on('click', '.js-sudrikaz-add', function(e){
            e.preventDefault();
            
            var _sudprikaz_number = $('.js-sudprikaz-number').val();
            var _sudprikaz_date = $('.js-sudprikaz-date').val();
            
            if (_sudprikaz_number == '')
            {
                Swal.fire({
                    title: 'Укажите номер судебного приказа.',
                    type: 'error',
                });
            }
            else if (_sudprikaz_date == '')
            {
                Swal.fire({
                    title: 'Укажите дату судебного приказа.',
                    type: 'error',
                });
            }
            else
            {
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'sudprikaz',
                        sudprikaz_number: _sudprikaz_number,
                        sudprikaz_date: _sudprikaz_date,
                    },
                    success: function(resp){
                        app.update_page();
                    }
                })
            }
            
        })
    }
    
    
    
    
    
    
    
    
    
    
    var _init_upload_file = function(){
        $(document).on('change', '#new_file', function(){
            app.upload(this);
        });

    };
    
    app.upload = function(input){
        
        var $this = $(input);
        
        var $fileblock = $this.closest('.form_file_item');
        var _type = $this.data('type');
        
        var form_data = new FormData();
                    
        form_data.append('file', input.files[0])
        form_data.append('user_id', $this.data('user'))
        form_data.append('type', 'document');        
        form_data.append('action', 'add');        
        form_data.append('notreplace', '1');        

        $.ajax({
            url: '//nalichnoeplus.com/ajax/upload.php',
            data: form_data,
            type: 'POST',
            dataType: 'json',
            processData : false,
            contentType : false, 
            beforeLoad: function(){
                $fileblock.addClass('loading');
            },
            success: function(resp){
                if (!!resp.error)
                {
                    var error_text = '';
                    if (resp.error == 'max_file_size')
                        error_text = 'Превышен максимально допустимый размер файла.';
                    else if (resp.error == 'error_uploading')
                        error_text = 'Файл не удалось загрузить, попробуйте еще.';
                    else
                        error_text = resp.error;
                        
                    $fileblock.append('<div class="error_text">'+error_text+'</div>');
                }
                else
                {
                    $fileblock.find('.error_text').remove();

                    app.update_page();
                }
                
            }
        });                
    };
    
    var _init_fssp_info = function(){
        $(document).on('click', '.js-get-fssp-info', function(e){
            e.preventDefault();
            
            var $this = $(this);
            
            if ($this.hasClass('loading'))
                return false;
            
            $.ajax({
                url: 'ajax/get_info.php',
                data: {
                    action: 'fssp',
                    'scoring_id': $this.data('scoring')
                },
                beforeSend: function(){
                    $this.addClass('loading');
                },
                success: function(resp){
                    $this.removeClass('loading');
                    
                    
                    if (!!resp.body.result[0].result)
                    {
                        var _html = '';
                        $.each(resp.body.result[0].result, function(k, item){
                            var _row = '<tr>';
                            _row += '<td>'+item.exe_production+'</td>';
                            _row += '<td>'+item.details+'</td>';
                            _row += '<td>'+item.subject+'</td>';
                            _row += '<td>'+item.department+'</td>';
                            _row += '<td>'+item.ip_end+'</td>';
                            _row += '</tr>';
                            
                            _html += _row;
                        })
                    }
                    else
                    {
                        var _html = '<h4>Производства не найдены</h4>';
                    }
                    
                    $('.js-fssp-info-result').html(_html);
                    
                    $('#modal_fssp_info').modal();
console.info(resp);
                }
            })
        });
    };
    
    var _init_open_image_popup = function(){

        $.fancybox.defaults.btnTpl.rotate_left = '<button class="js-fancybox-rotate-left fancybox-button " ><i class="mdi mdi-rotate-left"></i></button>';
        $.fancybox.defaults.btnTpl.rotate_right = '<button class="js-fancybox-rotate-right fancybox-button " ><i class="mdi mdi-rotate-right"></i></button>';
        

        $('.js-open-popup-image').fancybox({
            buttons: [
                "zoom",
                "rotate_right",
                "rotate_left",
                "close",
            ],
            loop: true,
            hideScrollbar: false,
            autoFocus: false,
            hash: false,
            touch: false,
        });
        
        app.image_deg = 0
        $(document).on('click', '.js-fancybox-rotate-left', function(e){
            e.preventDefault();
            
            var $img = $('.fancybox-content img');
            
            new_deg = app.image_deg == 360 ? 0 : app.image_deg - 90;
            $img.css({'transform':'rotate('+new_deg+'deg)'})

            app.image_deg = new_deg
            //$img.attr('data-deg', new_deg);
        });
        $(document).on('click', '.js-fancybox-rotate-right', function(e){
            e.preventDefault();
            
            var $img = $('.fancybox-content img');

            new_deg = app.image_deg == 270 ? 0 : app.image_deg + 90;
            $img.css({'transform':'rotate('+new_deg+'deg)'})

            app.image_deg = new_deg
        });
    }
    
    
    var _init_confirm_contract = function(){
        
        var _set_timer = function(_seconds){
            app.sms_timer = setInterval(function(){
                _seconds--;
                if (_seconds > 0)
                {
                    $('.js-sms-timer').text(_seconds+'сек');
                    $('.js-sms-send').addClass('disable');
                }
                else
                {
                    $('.js-sms-timer').text('');                
                    $('.js-sms-send').removeClass('disable');
                    clearInterval(app.sms_timer);
                }
            }, 1000);

        };
        
        $(document).on('click', '.js-sms-send', function(e){
            e.preventDefault();
            
            if ($(this).hasClass('disable'))
                return false;
            
            var _contract_id = $(this).data('contract');
            
            $.ajax({
                url: 'ajax/sms_code.php',
                data: {
                    action: 'send_accept_code',
                    contract_id: _contract_id
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        if (resp.error == 'sms_time')
                            _set_timer(resp.time_left);
                        else
                            console.log(resp);
                    }
                    else
                    {
                        _set_timer(resp.time_left);
                        app.sms_sent = 1;                        
                        
                        
                    }
                    if (!!resp.developer_code)
                    {
                        $('.js-contract-code').val(resp.developer_code);
                    }
                }
            });


        })
        
        $(document).on('submit', '.js-confirm-contract', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            if ($form.hasClass('loading'))
                return false;
            
            var contract_id = $form.find('.js-contract-id').val();
            var phone = $form.find('.js-contract-phone').val();
            var code = $form.find('.js-contract-code').val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'confirm_contract',
                    contract_id: contract_id,
                    phone: phone,
                    code: code,
                },
                beforeSend: function(){
                    $form.addClass('loading');
                },
                success: function(resp){
                    $form.removeClass('loading');
                    if (!!resp.error)
                    {
                        Swal.fire({
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else if (!!resp.success)
                    {
                        app.update_page();
console.log(resp);
                    }
                    else
                    {
                        console.error(resp);
                    }
                    
                }
            })
        });
    };
    
    var _init_accept_order = function(){
        $(document).on('click', '.js-accept-order', function(e){
            e.preventDefault();

            var $btn = $(this);            
            if ($btn.hasClass('loading'))
                return false;
            
            var order_id = $(this).data('order');
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'accept_order',
                    order_id: order_id,
                }, 
                beforeSend: function(){
                    $btn.addClass('loading')
                },
                success: function(resp){
                    $btn.removeClass('loading');

                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'orders';
                        }, 5000);
                    }
                    else if (!!resp.success)
                    {
                        $('.js-order-manager').html(resp.manager);
                        $('.js-accept-order-block').remove();
                        $('.js-approve-reject-block').removeClass('hide');                        
                    
                        app.update_page();
                    }
                    else
                    {
                        console.error(resp);
                    }
                },
            })
        });        
    };
    
    var _init_approve_order = function(){
        $(document).on('click', '.js-approve-order', function(e){
            e.preventDefault();

            var $btn = $(this);            
            if ($btn.hasClass('loading'))
                return false;
            
            var order_id = $(this).data('order');
            
            
            // проверяем фото
            var files_ready = 1;
            $('.js-file-status').each(function(){
                if ($(this).val() != 2)
                    files_ready = 0;
            });
            
            if (!files_ready)
            {
                
                Swal.fire({
                    timer: 5000,
                    type: 'error',
                    title: 'Ошибка!',
                    text: 'Необходимо принять файлы клиента!',
                    onClose: () => {
                        $('html, body').animate({
                            scrollTop: $("#images_form").offset().top-100  // класс объекта к которому приезжаем
                        }, 1000);
                    }
                });
                
                return false;
            }
            
            
            Swal.fire({
                title: 'Одобрить выдачу кредита?',
                text: "",
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Отменить',
                confirmButtonText: 'Да, одобрить'
            }).then((result) => {
                if (result.value) {
                    
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'approve_order',
                            order_id: order_id,
                        },
                        beforeSend: function(){
                            $btn.addClass('loading')
                        },
                        success: function(resp){
                            $btn.removeClass('loading');
        
                            if (!!resp.error)
                            {
                                Swal.fire({
                                    timer: 5000,
                                    title: 'Ошибка!',
                                    text: resp.error,
                                    type: 'error',
                                });
                                setTimeout(function(){
                                    location.href = 'orders';
                                }, 5000);
                            }
                            else
                            {
                                app.update_page();
        
                            }
                        },
                    })        
                }
            });
        });        
    };
    
    var _init_reject_order = function(){
        $(document).on('click', '.js-reject-order', function(e){
            e.preventDefault();

            $('#modal_reject_reason').modal();
        });
        
        $(document).on('submit', '.js-reject-form', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            if ($form.hasClass('loading'))
                return false;
            
            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    $form.removeClass('loading');
                    $('#modal_reject_reason').modal('hide');
                    
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'orders';
                        }, 5000);
                    }
                    else
                    {
                        app.update_page();

                    }
                },
            })        
        });
    };



    var _init_change_status = function(){
        $(document).on('change', '.js-status-select', function(e){
            
            var $this = $(this);
            var $option = $this.find('option:selected');
            
            var order_id = $this.data('order');
            var status = $option.val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'status',
                    order_id: order_id,
                    status: status
                },
                beforeSend: function(){
                    $this.attr('disabled', true);
                }, 
                success: function(resp){
                    $this.removeAttr('disabled');
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    
                }
            })
            
        });
    }
    
    var _init_take_order = function(){
        $(document).on('click', '.js-take-order', function(e){
            e.preventDefault();

            var $btn = $(this);            
            if ($btn.hasClass('loading'))
                return false;
            
            var order_id = $(this).data('order');
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'status',
                    order_id: order_id,
                    status: 1 
                }, 
                beforeSend: function(){
                    $btn.addClass('loading')
                },
                success: function(resp){
                    $btn.removeClass('loading');
                    
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        setTimeout(function(){
                            location.href = 'orders';
                        }, 5000);
                    }
                    else
                    {
                        $btn.remove();
                        $('.js-status-select-wrapper').removeClass('hide');                        
                        
                        app.update_page();
                    }
                    
                    
                }
            })
        });
    };
    
    var _init_autoretry_accept = function(){
        $(document).on('click', '.js-autoretry-accept', function(e){
            e.preventDefault();

            var $btn = $(this);            
            if ($btn.hasClass('loading'))
                return false;
            
            var order_id = $(this).data('order');
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'autoretry_accept',
                    order_id: order_id,
                    status: 1 
                }, 
                beforeSend: function(){
                    $btn.addClass('loading')
                },
                success: function(resp){
                    $btn.removeClass('loading');
                    
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {                        
                        app.update_page();
                    }
                    
                    
                }
            })
        });
    };
    


    var _init_toggle_form = function(){
        
        // редактирование формы
        $(document).on('click', '.js-edit-form', function(e){
            e.preventDefault();
            
            var $form = $(this).closest('form');
            $form.find('.view-block').addClass('hide');
            $form.find('.edit-block').removeClass('hide');
        });
        
        // отмена редактирования
        $('.js-cancel-edit').click(function(e){
            e.preventDefault();

            var $form = $(this).closest('form');
            $form.find('.edit-block').addClass('hide');
            $form.find('.view-block').removeClass('hide');
        });
    };
    
    var _init_open_order = function(){
        $(document).on('click', '.js-open-order', function(e){
            e.preventDefault();
            
            var _id = $(this).data('id');
            
            if ($(this).hasClass('open'))
            {
                $(this).removeClass('open');
                $('.order-details').fadeOut();
            }
            else
            {
                $('.js-open-order.open').removeClass('open')
                $(this).addClass('open')

                $('.order-details').hide();                
                $('#changelog_'+_id).fadeIn();
            }
        })
    };

    var _init_mango_call = function(){
        
        $(document).on('click', '.js-mango-call', function(e){
            e.preventDefault();
        
            var _phone = $(this).data('phone');
            
            Swal.fire({
                title: 'Выполнить звонок?',
                text: "Вы хотите позвонить на номер: "+_phone,
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Отменить',
                confirmButtonText: 'Да, позвонить'
            }).then((result) => {
                if (result.value) {
                    
                    $.ajax({
                        url: 'ajax/mango_call.php',
                        data: {
                            phone: _phone
                        },
                        beforeSend: function(){
                            
                        },
                        success: function(resp){
                            if (!!resp.error)
                            {
                                if (resp.error == 'empty_mango')
                                {
                                    Swal.fire(
                                        'Ошибка!',
                                        'Необходимо указать Ваш внутренний номер сотрудника Mango-office.',
                                        'error'
                                    )
                                }
                                
                                if (resp.error == 'empty_mango')
                                {
                                    Swal.fire(
                                        'Ошибка!',
                                        'Не хватает прав на выполнение операции.',
                                        'error'
                                    )
                                }
                            }
                            else if (resp.success)
                            {
                                Swal.fire(
                                    '',
                                    'Выполняется звонок.',
                                    'success'
                                )
                                
                                $.ajax({
                                    url: 'ajax/communications.php',
                                    data: {
                                        action: 'add',
                                        user_id: _user,
                                        type: 'call',
                                    }
                                });
                                
                            }
                            else
                            {
                                console.error(resp);
                                Swal.fire(
                                    'Ошибка!',
                                    '',
                                    'error'
                                )
                            }
                        }
                    })
                    
                }
            })
            
            
        });
        
    };


    var _init_submit_form = function(){
        $(document).on('submit', '.js-order-item-form', function(e){
            e.preventDefault();
            
            var $form = $(this);
            var _id = $form.attr('id');
            
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading');
                },
                success: function(resp){
                    
                    var $content = $(resp).find('#'+_id).html();
                    
                    $form.html($content);
                    
                    $form.removeClass('loading');
                    
                    _init_toggle_form();
                }
            })
        });
    }
    
    var _init_change_image_status = function(){
        
        $(document).on('click', '.js-image-reject, .js-image-accept', function(e){
            var _id = $(this).data('id');
            if ($(this).hasClass('js-image-reject'))
                var _status = 3;
            else if ($(this).hasClass('js-image-accept'))
                var _status = 2;
            
            $('#status_'+_id).val(_status);

            $(this).closest('form').submit();
        });
        
    };
    
    var _init_contact_status = function(){
        $(document).on('change', '.js-contact-status', function(){
            var contact_status = $(this).val();
            var user_id = $(this).data('user');
            var $form = $(this).closest('form');
            
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    action: 'contact_status',
                    user_id: user_id,
                    contact_status: contact_status
                }
            })
        })
    }
    
    var _init_contactperson_status = function(){
        $(document).on('change', '.js-contactperson-status', function(){
            var contact_status = $(this).val();
            var contactperson_id = $(this).data('contactperson');
            var $form = $(this).closest('form');
            
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    action: 'contactperson_status',
                    contactperson_id: contactperson_id,
                    contact_status: contact_status
                }
            })
        })
    }
    
    var _init_comment_form = function(){
        
        $(document).on('click', '.comment-box a', function(e){
            e.preventDefault();
            
            $(this).toggleClass('open');
        });
        
        $(document).on('click', '.js-open-comment-form', function(e){
            e.preventDefault();
            
            $('#modal_add_comment [name=text]').text('')
            $('#modal_add_comment').modal();
        });

        $(document).on('submit', '#form_add_comment', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            $.ajax({
                url: $form.attr('action'),
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $('#modal_add_comment').modal('hide');
                        $form.find('[name=text]').val('')
            
                        app.update_page();
                        
                        Swal.fire({
                            timer: 5000,
                            title: 'Комментарий добавлен.',
                            type: 'success',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                        
                    }
                }
            })
        })
    }
    
    
    app.update_page = function(active_tab){
        $.ajax({
            success: function(resp){

                $('#order_wrapper').html($(resp).find('#order_wrapper').html());
                if (!!active_tab)
                {
                    $('#order_tabs .active').removeClass('active');
                    $('#order_tabs [href="#'+active_tab+'"]').addClass('active');
                
                    $('#order_tabs_content .tab-pane').removeClass('active');
                    $('#order_tabs_content').find('#'+active_tab).addClass('active');
                    
                }           
                     
                if ($('.js-dadata-address').length > 0)
                {

                    $('.js-dadata-address').each(function(){
                        new DadataAddressApp($(this));
                    });
                }
                if ($('.js-dadata-work').length > 0)
                {
                    $('.js-dadata-work').each(function(){
                        new DadataWorkApp($(this));
                    });
                }

            }

        })
    }
    
    var _init_sud_change = function(){
        $(document).on('change', '.js-sud-label', function(){
            var contract_id = $(this).data('contract');
            var sud = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: '/my_contracts',
                type: 'POST',
                data: {
                    action: 'sud_label',
                    contract_id: contract_id,
                    sud: sud
                },
                
            });
        })
    }
    
    var _init_hide_prolongation_change = function(){
        $(document).on('change', '.js-hide-prolongation-label', function(){
            var contract_id = $(this).data('contract');
            var hide_prolongation = $(this).is(':checked') ? 1 : 0;
            
            $.ajax({
                url: '/my_contracts',
                type: 'POST',
                data: {
                    action: 'hide_prolongation',
                    contract_id: contract_id,
                    hide_prolongation: hide_prolongation
                },
                
            });
        })
    }
    
    
    ;(function(){
        
        _init_manager_select();
        _init_status_select();
        _init_workout_change();
        
        _init_print_button();
        
        _init_ready_document_button();
        
        _init_add_document();
        _init_remove_document();

        _init_notification_form();
        
        _init_send_sud();
        _init_send_fssp();
        _init_sudprikaz();
        
        
        _init_toggle_form();
        _init_submit_form();
        _init_open_image_popup();
        _init_mango_call();
        _init_open_order();
//        _init_take_order();
//        _init_change_status();
        _init_change_image_status();

        _init_accept_order();
        _init_approve_order();
        _init_reject_order();
        
        _init_confirm_contract();
        
        _init_comment_form();
        _init_fssp_info();
        
        _init_upload_file();
        
        _init_autoretry_accept();
        
        _init_contact_status();
        _init_contactperson_status();
        
        
        
        _init_sud_change()
        _init_hide_prolongation_change();
        
    })();
};
new SudblockContractApp();