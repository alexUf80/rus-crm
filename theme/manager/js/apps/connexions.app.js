function ConnexionsApp()
{
    var app = this;
    
    app.run = function(){
        var _user = $('.js-app-connexions').data('user');
        $.ajax({
            url: 'ajax/connexions.php',
            data: {
                user_id: _user
            },
            success: function(resp){
                $('.js-app-connexions').html(resp)
            }
        })
    }
    
    ;(function(){
        app.run();
    })();
}

$(function(){
    if ($('.js-app-connexions').length > 0)
        new ConnexionsApp();
})