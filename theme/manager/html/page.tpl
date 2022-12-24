{if $page->id}
    {$meta_title="Страница `$page->name`" scope=parent}
{else}
    {$meta_title="Новая страница" scope=parent}
{/if}


{capture name='page_styles'}
<link rel="stylesheet" href="theme/manager/assets/plugins/html5-editor/bootstrap-wysihtml5.css" />
{/capture}


{capture name='page_scripts'}

<script src="theme/manager/assets/plugins/tinymce/tinymce.min.js"></script>

<script>
    
    function PageApp()
    {
        var app = this;
        
        var _init_mce = function(){
            if ($("#mymce2").length > 0) 
            {
                tinymce.init({
                    selector: "textarea#mymce2",
                    theme: "modern",
                    height: 300,
                    plugins: [
                        "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                        "save table contextmenu directionality emoticons template paste textcolor"
                    ],
                    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
    
                });
            }
        };
        
        
        ;(function(){
            _init_mce();
        })();
    };
    
    $(function(){
        new PageApp();
    });
    
</script>

{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
    
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">{$page->name|escape}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="pages">Страницы</a></li>
                    <li class="breadcrumb-item active">
                    {if $page->id}
                         {$page->name}
                    {else}
                         Новая страница
                     {/if}
                    </li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                {if $page->id}
                <a href="page/{$page->id}?action=delete" class="btn float-right hidden-sm-down btn-danger">
                    <i class="mdi mdi-close-circle"></i> 
                    Удалить страницу
                </a>
                {/if}
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-outline-info">
                    <div class="card-body">
                        <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data">
                            
                            <input type="hidden" name="id" value="{$page->id}" />
                            
                            <div class="form-body">
                                
                                <div class="row">
                                    
                                    {if $message_success}
                                    <div class="col-12">
                                        <div class="alert alert-success">{$message_success}</div>
                                    </div>
                                    {/if}
                                    
                                    {if $message_error}
                                    <div class="col-12">
                                        <div class="alert alert-danger">{$message_error}</div>
                                    </div>
                                    {/if}
                                    
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Заголовок</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="name" value="{$page->name|escape}" placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Адрес</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="url" value="{$page->url|escape}" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 col-md-12">
                                        
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Мета-заголовок</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="meta_title" value="{$page->meta_title}" placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Ключевые слова</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="meta_keywords" value="{$page->meta_keywords}" placeholder="">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-1">
                                            <label class="col-sm-12 col-form-label">Мета-описание</label>
                                            <div class="col-md-12">
                                                <input type="text" class="form-control" name="meta_description" value="{$page->meta_description}" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group row mb-1 mt-3">
                                            <label class="col-sm-12 col-form-label">Текст страницы</label>
                                            <div class="col-md-12">
                                                <textarea id="mymce2" class="form-control" name="body">{$page->body}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" class="btn btn-success">Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6"> </div>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>





