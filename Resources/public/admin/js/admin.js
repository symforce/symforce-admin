jQuery(function(){
    $('#form_locale').change(function(evt){
            $(this).closest('form').submit() ;
    });
    
    var is_call_running = false ;
    
    
    $('.api_call').each(function(){
        $(this).click(function(){
            var td  = this ;
           var uri = $(this).attr('uri') ;
           if( is_call_running ) {
               return ;
           }
           var msg  = 'Are you sure?' ;
           bootbox.confirm(msg, function(ok){
               if( !ok ) return ;
               
                is_call_running  = true ;
                $.ajax({
                 type: "GET",
                 url: uri ,
                 dataType: 'json' ,
                 success: function(o){
                   is_call_running   = false ;
                   if( o.error ) {
                       alert( o.error ) ;
                   } else if( o.message ) {
                       alert( o.message ) ;
                   } 
                   if( o.removed ) {
                        $(td).closest( 'tr').remove() ;
                   }
                   if( o.refresh ) {
                       window.location  = window.location ;
                   }
                 },
                 error: function(xhr, status, res){
                   is_call_running   = false ;
                   alert(status)
                 }
               });
           });
        });
    })
});
