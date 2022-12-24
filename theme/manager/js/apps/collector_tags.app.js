function CollectorTagsApp()
{
    var app = this;            
    
    var _init_events = function(){
        
        // редактирование записи
        $(document).on('click', '.js-edit-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
            
            _init_colorpicker();
        });
        
        // Удаление записи
        $(document).on('click', '.js-delete-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            var _id = $item.find('[name=id]').val();
            var _name = $item.find('[name=name]').val();
            
            Swal.fire({
                text: "Вы действительно хотите удалить тег "+_name+"?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Да, удалить!",
                cancelButtonText: "Отмена",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                },
                allowOutsideClick: () => !Swal.isLoading()

            }).then((result) => {

                if (result.value) 
                {
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: _id
                        },
                        success: function(){

                            $item.remove();

                            Swal.fire({
                              timer: 5000,
                              text: 'Тег удален!',
                              type: 'success',
                            });                                
                        }
                    })
                }
            });
        });
        
        
        // Сохранение редактируемой записи
        $(document).on('click', '.js-confirm-edit-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            var _id = $item.find('[name=id]').val();
            var _name = $item.find('[name=name]').val();
            var _color = $item.find('[name=color]').val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    name: _name,
                    color: _color,
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });                        
                    }
                    else
                    {
                        $item.find('[name=name]').val(resp.name);
                        $item.find('.js-text-name').html(resp.name);
                        
                        $item.find('[name=color]').val(resp.color);
                        $item.find('.js-text-color').html(resp.color+' <div class="color-badge" style="background:'+resp.color+'"></div>');

                        $item.find('.js-visible-edit').hide();
                        $item.find('.js-visible-view').fadeIn();

                    }
                }
            });
            
        });
        
        // Отмена редактирования записи
        $(document).on('click', '.js-cancel-edit-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            $item.find('.js-visible-edit').hide();
            $item.find('.js-visible-view').fadeIn();
        });
        
        // Открытие окна для добавления
        $(document).on('click', '.js-open-add-modal', function(e){
            e.preventDefault();
            
            $('#modal_add_item').find('.alert').hide();
            $('#modal_add_item').find('[name=name]').val('');
            $('#modal_add_item').find('[name=color]').val('');
            
            $('#modal_add_item').modal();
            
            $('#modal_add_item').find('[name=name]').focus();
        
            _init_colorpicker();
        });
        
        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            var _name = $form.find('[name=name]').val();
            var _color = $form.find('[name=color]').val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    name: _name,
                    color: _color,
                },
                beforeSend: function(){
                    
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    }
                    else
                    {
                        var new_row = '<tr class="js-item">';
                        new_row += '<td><div class="js-text-id">'+resp.id+'</div></td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-name">'+resp.name+'</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="hidden" name="id" value="'+resp.id+'" />';
                        new_row += '<input type="text" class="form-control form-control" name="name" value="'+resp.name+'" />';
                        new_row += '</div>';
                        new_row += '</td>';

                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-color">';
                        new_row += '<div class="color-badge" style="background:'+resp.color+'"></div>';
                        new_row += resp.color;
                        new_row += '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="text" class="form-control form-control colorpicker" name="color" value="'+resp.color+'" />';
                        new_row += '</div>';
                        new_row += '</td>';

                        new_row += '<td class="text-right">';
                        new_row += '<div class="js-visible-view">';
                        new_row += '<a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a> ';
                        new_row += '<a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>';
                        new_row += '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a> ';
                        new_row += '<a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '</tr>';
                        
                        $('#table-body').append(new_row);
                        
                        _init_colorpicker();
                        
                        $('#modal_add_item').modal('hide');
                        Swal.fire({
                            timer: 5000,
                            text: 'Тег "'+resp.name+'" добавлен!',
                            type: 'success',
                        });                                

                    }
                }
            })
        });
    };
    
    var _init_colorpicker = function(){
        $(".colorpicker").asColorPicker({
            
        });
    }
    
    ;(function(){
        _init_events();
//        _init_colorpicker();
    })();
};
$(function(){
    new CollectorTagsApp();
})
