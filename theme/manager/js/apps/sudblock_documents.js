function SudblockDocumentsApp()
{
    var app = this;            
    
    var _init_events = function(){
        
        // редактирование записи
        $(document).on('click', '.js-edit-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
        });
        
        // Удаление записи
        $(document).on('click', '.js-delete-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            var _id = $item.find('[name=id]').val();
            var _name = $item.find('[name=name]').val();
            
            Swal.fire({
                text: "Вы действительно хотите удалить документ "+_name+"?",
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
                              text: 'Документ удален!',
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
            var _provider = $item.find('[name=provider]').val();
            var _block = $item.find('[name=block]').val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    name: _name,
                    provider: _provider,
                    block: _block,
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
                        $item.find('[name=provider]').val(resp.provider);
                        $item.find('.js-text-provider').html(resp.provider);
                        
                        if (resp.block == 'sud')
                        {
                            $item.find('.js-text-block').html('Суд');
                            $item.find('[name=provider] [value="sud"]').attr('selected', true);
                        }
                        else if (resp.block == 'fssp')
                        {
                            $item.find('.js-text-block').html('ФССП');
                            $item.find('[name=provider] [value="fssp"]').attr('selected', true);
                        }
                        

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
            
            $('#modal_add_item').modal();
            
            $('#modal_add_item').find('[name=name]').focus();
        });
        
        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){            
            e.preventDefault();
            
            var $form = $(this);
            
            var input = document.getElementById('file_input');
            
            var _name = $form.find('[name=name]').val();
            var _provider = $form.find('[name=provider]').val();
            var _base = $form.find('[name=base]').val();
            var _block = $form.find('[name=block]').val();
            
            var form_data = new FormData();
                        
            form_data.append('file', input.files[0])
            form_data.append('name', _name)
            form_data.append('provider', _provider);        
            form_data.append('base', _base);        
            form_data.append('block', _block);        
            form_data.append('action', 'add');        


            $.ajax({
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
                        var new_row = '<tr class="js-item">';
                        new_row += '<td><div class="js-text-id">'+resp.id+'</div></td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-name">'+resp.name+'</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="hidden" name="id" value="'+resp.id+'" />';
                        new_row += '<input type="hidden" name="base" value="1" />';
                        new_row += '<input type="text" class="form-control form-control-sm" name="name" value="'+resp.name+'" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-document"><a href="/files/sudblock/'+resp.filename+'" target="_blank">'+resp.filename+'</a></div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<a href="/files/sudblock/'+resp.filename+'" target="_blank">'+resp.filename+'</a>';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-provider">'+resp.provider+'</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<select name="provider" class="form-control form-control-sm">';
                        new_row += '<option value="Наличное плюс" '+(resp.provider == "Наличное плюс" ? 'selected' : '') +'>Наличное плюс</option>';
                        new_row += '</select>';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-block">';
                        if (resp.block == 'sud')
                            new_row += 'Суд';
                        else if (resp.block == 'fssp')
                            new_row += 'ФССП';
                        new_row += '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<select name="block" class="form-control form-control-sm">';
                        new_row += '<option value="sud" '+(resp.block == "sud" ? 'selected' : '') +'>Суд</option>';
                        new_row += '<option value="fssp" '+(resp.block == "fssp" ? 'selected' : '') +'>ФССП</option>';
                        new_row += '</select>';
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
                                                        
                        $('#modal_add_item').modal('hide');
                        Swal.fire({
                            timer: 5000,
                            text: 'Документ "'+resp.name+'" добавлен!',
                            type: 'success',
                        });                                

                    }
                }
            })
        });
    };
    
    ;(function(){
        _init_events();
    })();
};
$(function(){
    new SudblockDocumentsApp();
})
