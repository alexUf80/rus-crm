;function RunScoringsApp()
{
    var app = this;
    
    app.timeout;
    
    var _init_run_link = function(){
        $(document).on('click', '.js-run-scorings', function(e){
            e.preventDefault();
            
            var $this = $(this);
            
            var order_id = $(this).data('order');
            var type = $(this).data('type');
            
            $.ajax({
                url: 'ajax/run_scorings.php',
                data: {
                    'order_id': order_id,
                    'type': type,
                    'action': 'create'
                },
                beforeSend: function(){
                    $this.html('<div class="spinner-border text-info" role="status"></div>').addClass('btn-loading');
                },
                success: function(resp){
                    $('.js-scorings-block').addClass('js-need-update')
                    _init_scorings_block();
                }
            })
        });
    };
    
    
    var _init_scorings_block = function(){
        clearTimeout(app.timeout);
        if ($('.js-scorings-block').hasClass('js-need-update'))
        {
            var _order_id = $('.js-scorings-block').data('order');
            app.timeout = setTimeout(function(){
                update_scoring_block(_order_id);
            }, 10000);
        }
    };
    
    var update_scoring_block = function(_order_id){
        $.ajax({
            url: 'order/'+_order_id+'?open_scorings=1',
            success: function(resp){
                $('.js-scorings-block').html($(resp).find('.js-scorings-block').html());
console.log($(resp).find('.js-scorings-block').hasClass('js-need-update'))
                if (!$(resp).find('.js-scorings-block').hasClass('js-need-update'))
                {
                    $('.js-scorings-block').removeClass('js-need-update')
                }
                _init_scorings_block()
            }
        })
    };
    
    ;(function(){
        _init_run_link();
        _init_scorings_block();
    })();
};

$(function(){
    new RunScoringsApp();
});