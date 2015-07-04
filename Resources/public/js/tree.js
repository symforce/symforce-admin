var SymforceFormTree = (function(){
    
   var Tree = new Klass({
        Binds: [ 'onHandle', 'onEventSelect', 'onEventDelete', 'onEventClose', 'afterClosed', 'onEventReset', 'onEventChanged' ] ,
        options: {
            url: null ,
            required: null 
        } ,
        initialize: function( input, options ){
            this.setOptions(options) ;
            this.input  = $(input) ;
            var box = this.input.closest('.form-group') ;
            this.handle = box.find('input[type="text"]') ;
            
            (function(_this){
                _this.handle.click( function(){
                    if( iTimer ) {
                        clearTimeout(iTimer) ;
                        iTimer  = null ;
                    }
                    _this.onHandle();
                }) ;
                
                var iTimer  = null ;
                _this.handle.keydown( function(evt) {
                    if( iTimer ) {
                        clearTimeout(iTimer) ;
                    }
                    if( [ 8, 9, 13, 16, 27, 91 ].indexOf(evt.keyCode) >= 0 || evt.ctrlKey || evt.altKey ) {
                        iTimer  = setTimeout(function(){
                            iTimer  = null ;
                        }, 10 );
                    }  else {
                        iTimer  = setTimeout( function(){
                            iTimer  = null ;
                            _this.onHandle() ;
                        }, 10 ) ;
                    }
                }) ;
                
                _this.handle.on('blur', function(evt){
                    iTimer  = setTimeout(function(){
                        if( !_this.modal  ) {
                           _this.input.trigger('sf_blur'); 
                        } 
                    }, 300 );
                });
                
            })(this);
            
            this.default_value  = this.input.val() ;
            this.default_url    = this.options.url ;
            
            if( this.options.copy_property && this.options.copy_property[1] ) {
                this.copy_property  = $('#' + this.options.copy_property[1]) ;
                if( ! this.copy_property.get(0) ) {
                    this.copy_property  = null ;
                }
            } else {
                this.copy_property  = null ;
            }
        } ,
        
        onChanged: function(id, text ){
            this.input.val( id ) ;
            this.options.url = this.default_url.replace(/(\/\d+)+$/g, '') ;
            if( id ) {
                this.options.url += '/0/' + id + '/0' ;
            } else {
                text = '' ;
            } 
            this.handle.val( text ) ;
            if( this.copy_property ) {
                this.copy_property.val( text ) ;
            }
            this.input.trigger( "sf_change", [ id , text ] );
        },
        
        onEventSelect: function() {
            var link    = $( '#' + this.tree_selected_id).find('a[url]') ;
            if( !link.length ) {
                return false ;
            }
            /\/(\d+)\/\d+\/\d+$/.test( link.attr('url') ) ; 
            this.onChanged( RegExp.$1 , link.text() ) ;
            this.onEventClose();
        } ,
        
        onEventDelete: function() {
            this.onChanged(0) ;
            this.onEventClose() ;
        } ,
        onEventClose: function() {
            this.tree_root.jstree('destroy') ;
            this.tree_root = null ;
            this.modal_select_btn   = null ;
            this.modal  = null ;
            setTimeout(this.afterClosed, 10) ;
        } ,
        afterClosed: function() {
            this.handle.focus() ;
        },
        onEventReset:function(evt){
            this.modal_select_btn.prop('disabled', true ) ;
            this.tree_selected_id   = null ;
            this.tree_root.jstree('destroy') ;
            this.loadTree( this.default_url ) ;
            return false ;
        }, 
        onEventChanged: function(evt, node){
            if( node.selected.length ) {
                this.tree_selected_id   = node.selected[0] ;
                this.modal_select_btn.prop('disabled', false ) ;
            } else {
                this.tree_selected_id   = null ;
            }
        } ,
        loadTree: function(url) {
            this.tree_root.jstree( {
                "core" : {
                    'multiple': false ,
                    'data' : {
                        'url' : function (node) {
                            if( '#' === node.id ) {
                                return url ;
                            }
                            return node.a_attr.url ;
                        } ,
                        'data' : function (node) {
                            return node ;
                        }
                    }
                },
                "plugins" : [ "search" ] 
            }); 
            this.tree_root.on('changed.jstree', this.onEventChanged )
        } ,
        
        onHandle: function(evt, url ){
            
            this.input.trigger('sf_focus');
            this.handle.trigger('sf_focus');
            
            if(evt) {
                evt.stopPropagation();
                evt.preventDefault();
            } 
            if( this.modal ) {
                return false ; 
            }
            if( !url ) {
                url = this.options.url ;
            }
            var buttons = { } ;
            buttons['select'] =  {
                    label: "选择",
                    className: "btn-primary",
                    callback: this.onEventSelect
                } ;
                
            if( !this.options.required ) {
                buttons['delete']   = {
                    label: "删除",
                    className: "btn-danger",
                    callback:  this.onEventDelete 
                  } ;
            }
            
            if( url !== this.default_url ) {
                buttons['reset']   = {
                    label: "默认",
                    className: "btn-warning",
                    callback:  this.onEventReset 
                  } ;
            }
            
            buttons['cancel']   = {
                    label: "取消",
                    className: "btn-default",
                    callback: this.onEventClose
                  } ;
            
            this.modal   = bootbox.dialog({
                message: '<div class="sf_form_tree_root"></div>',
                title: this.options.title ,
                buttons: buttons , 
                backdrop: true,
                onEscape: this.onEventClose 
            });
            
            this.modal_select_btn   = this.modal.find('button[data-bb-handler="select"]') ;
            this.modal_select_btn.prop('disabled', true ) ;
            
            this.tree_root  = $( this.modal.find(".sf_form_tree_root").get(0) ) ;
            this.loadTree( url ) ;
        }
    });
    return Tree ;
})();


var SymforceFormSimpleTree = (function(){
    
   var Tree = new Klass({
        Binds: [ 'onHandle', 'onEventSelect', 'onEventDelete', 'onEventClose', 'afterClosed', 'onEventReset', 'onEventChanged' ] ,
        options: {
            url: null ,
            required: null 
        } ,
        initialize: function( input, options ){
            this.setOptions(options) ;
            this.handle  = $(input) ;
            
            (function(_this){
                var iTimer  = null ;
                _this.handle.click( function(){
                    if( iTimer ) {
                        clearTimeout(iTimer) ;
                        iTimer  = null ;
                    }
                    _this.onHandle();
                }) ;
                _this.handle.keydown( function(evt) {
                    if( iTimer ) {
                        clearTimeout(iTimer) ;
                    }
                    if( [ 8, 9, 13, 16, 27, 91 ].indexOf(evt.keyCode) >= 0 || evt.ctrlKey || evt.altKey ) {
                        iTimer  = setTimeout(function(){
                            iTimer  = null ;
                        }, 100 );
                    }  else {
                        iTimer  = setTimeout( function(){
                            iTimer  = null ;
                            _this.onHandle() ;
                        }, 100 ) ;
                    }
                }) ;
            })(this);
            
            this.default_value  = this.handle.val() ;
            this.default_url    = this.options.url ;
        } ,
        
        onChanged: function( route_name ){
            this.handle.val( route_name ) ;
            if( !route_name || '' === route_name ) {
                this.options.url = this.options.default_url ;
            } else {
                this.options.url = this.options.default_url + '/' + route_name ;
            }
            this.handle.trigger( "sf_change", [ route_name ] ) ;
        },
        
        onEventSelect: function() {
            this.onChanged( this.tree_selected_value ) ;
            this.onEventClose();
        } ,
        
        onEventDelete: function() {
            this.onChanged('') ;
            this.onEventClose() ;
        } ,
        onEventClose: function() {
            this.tree_root.jstree('destroy') ;
            this.tree_root = null ;
            this.modal_select_btn   = null ;
            this.modal  = null ;
            setTimeout(this.afterClosed, 10) ;
        } ,
        afterClosed: function() {
            this.handle.focus() ;
        },
        onEventReset:function(evt){
            this.modal_select_btn.prop('disabled', true ) ;
            this.tree_selected_value   = null ;
            this.tree_root.jstree('destroy') ;
            this.loadTree( this.default_url ) ;
            return false ;
        }, 
        onEventChanged: function(evt, node){
            if( node.selected.length ) {
                this.tree_selected_value   = $('#' + node.selected[0]).text() ;
                this.modal_select_btn.prop('disabled', false ) ;
            } else {
                this.tree_selected_value   = null ;
            }
        } ,
        loadTree: function(url) {
            this.tree_root.jstree( {
                "core" : {
                    'multiple': false ,
                    'data' : {
                        'url' : function (node) {
                            if( '#' === node.id ) {
                                return url ;
                            }
                            return node.a_attr.url ;
                        } ,
                        'data' : function (node) {
                            return node ;
                        }
                    }
                },
                "plugins" : [ "search" ] 
            }); 
            this.tree_root.on('changed.jstree', this.onEventChanged )
        } ,
        onHandle: function(evt, url ){
            if(evt) {
                evt.stopPropagation();
                evt.preventDefault();
            } 
            if( this.modal ) {
                return false ; 
            }
            
            (function(input){
                setTimeout(function(){
                    input.trigger('sf_focus');
                }, 500);
            })(this.handle); 
            
            if( !url ) {
                url = this.options.url ;
            }
            var buttons = {} ;
            buttons['select'] =  {
                    label: "选择",
                    className: "btn-primary",
                    callback: this.onEventSelect
                } ;
                
            if( !this.options.required ) {
                buttons['delete']   = {
                    label: "删除",
                    className: "btn-danger",
                    callback:  this.onEventDelete 
                  } ;
            }
            
            if( url !== this.default_url ) {
                buttons['reset']   = {
                    label: "默认",
                    className: "btn-warning",
                    callback:  this.onEventReset 
                  } ;
            }
            
            buttons['cancel']   = {
                    label: "取消",
                    className: "btn-default",
                    callback: this.onEventClose
                  } ;
            
            this.modal   = bootbox.dialog({
                message: '<div class="sf_form_tree_root"></div>',
                title: this.options.title ,
                buttons: buttons , 
                backdrop: true,
                onEscape: this.onEventClose 
            });
            
            this.modal_select_btn   = this.modal.find('button[data-bb-handler="select"]') ;
            this.modal_select_btn.prop('disabled', true ) ;
            
            this.tree_root  = $( this.modal.find(".sf_form_tree_root").get(0) ) ;
            this.loadTree( url ) ;
        }
    });
    return Tree ;
})();