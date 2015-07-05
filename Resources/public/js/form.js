Klass.delay = function(fn, scope, delay){
    if( ! delay ) delay = 100 ;
    var iTimer  = null ;
    return function(){
        if( iTimer ) {
            clearTimeout(iTimer) ;
        }
        var args    = arguments ;
        iTimer  = setTimeout(function(){
            iTimer  = null ;
            fn.apply(scope, args );
        });
    }
};

var SymforceFormDynamic = (function(){
    var debug   = (function(){
        var i   = 0 ;
        return function(max){
            if( i++ > max ) {
                throw new Error('debug error') ;
            }
        }
    })() ;

    var Element = new Klass({
        Binds: [ 'onDepsChange', 'onDepsChanged', 'onChanged', 'onChange' ] ,
        initialize: function(form, dom ){
            this.form   = form ;
            this.name   = $(dom).attr('sf_form_name') ;
            this.type   = $(dom).attr('sf_form_type') ;
            this.dom    = $(dom) ;
            form.elements[ this.name ] = this ;
            this.iTimer = null ;
        } ,
        
        setup: function() {
            
            function setText(_this, el, input, empty_value ) {
                if( !input ) { 
                    input   = el ;
                } 
                _this.getValue  = function(){
                    return el.val() ;
                };
                var required    = input.attr('required') ;
                if( required ) {
                    _this.form.addEvent('submit', function() {
                        if( _this.display ) {
                            var val = _this.getValue() ;
                            if( !val || val == '' || val === empty_value ) {
                                if( _this.form.cancelSubmit() ) {
                                    input.focus() ;
                                    setTimeout(function(){
                                        el.trigger('sf_validate') ;
                                    }, 100);
                                }
                            }
                        }
                    });
                }
            };
            
            function setCheckbox(_this, es) {
                var required    = es.attr('required') ;
                if( required ) {
                    _this.form.addEvent('submit', function(){
                        if( _this.display ) {
                            var val = _this.getValue() ;
                            if( !val || val == '' ) {
                                if( _this.form.cancelSubmit() ) {
                                    $( es.get(0) ).focus() ;
                                }
                            }
                        }
                    });
                }
            }
            
            var type1 = {
                radio: function(_this){
                    var es = _this.dom.find('input[type="radio"]') ;
                    var value  ;
                    es.on('click', function(evt){
                        value   = this.value ;
                        _this.fireEvent('change') ;
                    });
                    var _es = es.toArray() ;
                    for(var i = _es.length ; i-- ; ) {
                        if( _es[i].checked ) {
                            value   = _es[i].value ;
                        }
                    }
                    _this.getValue  = function(){
                        return value ;
                    };
                    setCheckbox(_this, es) ;
                },
                integer: function(_this, es ) {
                    if( !es ) {
                        es = _this.dom.find('input[type="number"]') ;
                    }
                    _this.form.addEvent('submit', function(){
                        if( _this.disply ) {
                            var val = _this.getValue() ;
                            if( !val || '' == val ) {
                                return ;
                            }
                            val = parseInt(val) ;
                            var max = es.attr('max') ;
                            var valid   = true ;
                            if( max ) {
                                max = parseInt( max ) ;
                                if( val > max ) {
                                    valid   = false ;
                                } 
                            }
                            if( valid ) {
                                var min = es.attr('min') ;
                                if( min ) {
                                    min = parseInt( min ) ;
                                    if( val < min ) {
                                        valid   = false ;
                                    } 
                                }
                            }
                            if( !valid ) {
                                if( _this.form.cancelSubmit() ) {
                                    es.focus() ;
                                    setTimeout(function(){
                                        es.trigger('sf_validate') ;
                                    }, 100);
                                }
                            }
                        }
                    });
                    setText(_this, es ) ;
                } ,
                text: function(_this) {
                    setText(_this, _this.dom.find('input[type="text"]') );
                } ,
                textarea: function(_this){
                    setText(_this, _this.dom.find('textarea') );
                },
                html: function(_this) {
                    setText(_this, _this.dom.find('textarea') );
                } ,
                select: function(_this) {
                    var select = _this.dom.find('select') ;
                    _this.getValue  = function(){
                        return select.val() ;
                    };
                    select.on('change', function(evt){
                        _this.fireEvent('change') ;
                    });
                }, 
                sf_tree:  function(_this) {
                    setText(_this, _this.dom.find('input[type="hidden"]'), _this.dom.find('input[type="text"]') , '0' ) ;
                },
                sf_route: function(_this) {
                    setText(_this, _this.dom.find('input[type="text"]') ) ;
                },
                sf_group: function(_this){
                    _this.getValue   = function(){
                        console.log('unimplement', this.name, this.type );
                    };
                }
            };
            type1['sf_range'] = function(_this) {
                type1['integer'](_this, _this.dom.find('input') ) ;
            };
            var type2 = {
                sf_workflow: 'radio' ,
                sf_entity:'select' ,
                sf_html:'html' ,
                sf_owner:'select' ,
                sf_datetime: 'text' 
            };
    
            if( type2.hasOwnProperty(this.type) ) {
                type1[type2[this.type]](this) ;
            } else if( type1.hasOwnProperty(this.type) ) {
                type1[this.type](this) ;
            } else if( 'choice' === this.type ){
                if( this.dom.find('input[type="radio"]').length > 1 ) {
                    type1['radio'](this) ;
                } else if( this.dom.find('select').length > 0 ) {
                    type1['select'](this) ;
                } else {
                    console.log(this.name, this.type, this.dom.get(0) ) ;
                }
            }
            if( !this.getValue ) {
                // console.log('unimplement', this.name, this.type );
                this.getValue   = function(){
                    console.log('unimplement', this.name, this.type );
                };
            }

            this.addEvent('change', this.onChange ) ;
        } ,
        hide: function(){ 
            this.display = false ;
            this.dom.addClass('form-group-hide');
            this.form.dynamic_elements[this.name] = 1 ;
            this.fireEvent('hide') ;
        },
        show: function(){
            this.display = true ;
            this.dom.removeClass('form-group-hide') ;
            this.form.dynamic_elements[this.name] = 0 ;
            this.fireEvent('show') ;
        } ,
        onChange: function(){
            if( this.iTimer ) {
                clearTimeout( this.iTimer ) ;
            }
            this.iTimer = setTimeout( this.onChanged , 10 );
        } ,
        onChanged: function() {
            this.iTimer = null ;
            this.fireEvent('changed') ;
        },
        onDepsChanged:  function(){
            this.iTimer = null ;
            if( this.form.isElementVisible(this.name) ) {
                this.show() ;
            } else {
                this.hide() ;
            }
        },
        onDepsChange: function() {
            if( this.iTimer ) {
                clearTimeout( this.iTimer ) ;
            }
            this.iTimer = setTimeout( this.onDepsChanged , 10 ) ;
        }
    });
    var Class   = new Klass({
        Binds: [ ] ,
        options: {
           url: null 
        } ,
        initialize: function(form, options){
            this.dom        = $(form) ;
            this.dom.attr('novalidate', 'novalidate' );
            this.dynamic_input   = $(form).find('input[type="hidden"][id$="_sf_admin_form_dynamic"]') ;

            this.dynamic_elements   = {};
            this.deps_elements   = {} ;
            
            this.setOptions( options ) ;
            this.elements = {} ;
            
            (function(_this){
                $.each( _this.dom.find('div[sf_form_name]'), function(){
                    new Element( _this , this ) ;
                }) ;
            })(this);

            this.initDynamicElements() ;

            this.dom.submit( this.onSubmit ) ;
            this.iTimer   = null ;
            
            (function(_this){
                var iTimer  = null ;
                var submited  = false ;
                _this.cancelSubmit  = function() {
                    if( submited ) {
                        submited    = false ;
                        return true ;
                    }
                };
                function no_submit(){
                    return false ;
                }
                function on_submit(){
                    _this.dom.unbind('submit', on_submit) ;
                    _this.dom.bind('submit', no_submit) ;
                    setTimeout(function(){
                        submited   = true ;
                        _this.fireEvent('submit') ;
                        if( submited ) {
                            _this.dom.unbind('submit', no_submit) ;
                            _this.dom.submit() ;
                            _this.dom.bind('submit', no_submit) ;
                            setTimeout(function(){
                                _this.fireEvent('submited') ;
                                _this.dom.unbind('submit', no_submit) ;
                                _this.dom.bind('submit', on_submit) ;
                            }, 10);
                        } else {
                            setTimeout(function(){
                                _this.dom.unbind('submit', no_submit) ;
                                _this.dom.bind('submit', on_submit) ;
                            }, 10);
                        }
                    }, 10 );
                    return false ;
                };
                
                _this.dom.bind('submit', on_submit) ;
                
                _this.addEvent('submit', function(){
                    var elements    = [] ;
                    _.each( _this.dynamic_elements, function(value, key){
                        if( value ) {
                            var type    = _this.elements[key].type ;
                            if( 'sf_group' !== type ) {
                                elements.push(key) ;
                            }
                        }
                    });
                    _this.dynamic_input.val( elements.join(',') );
                });
            })(this);
            
        } ,

        initDynamicElements:function() {
            var dynamic_values   = (function(input){
                if( input.get(0) ) {
                    var val = input.val() ;
                    return  jQuery.parseJSON(val) ;
                }
                return {} ;
            })( this.dom.find('input[type="hidden"][id$="_sf_admin_form_dynamic_values"]')  );

            var dynamic_deps   = (function(input){
                if( input.get(0) ) {
                    var val = input.val() ;
                    return  jQuery.parseJSON(val) ;
                }
                return {} ;
            })( this.dom.find('input[type="hidden"][id$="_sf_admin_form_dynamic_deps"]')  ) ;

            console.log('dynamic value:', dynamic_values );
            console.log('dynamic deps:', dynamic_deps );

            var show_on = {} ;
            Klass.each(dynamic_deps, function( configs , element_name ){
                var removed = [] ;
                var element_deps    = [] ;
                Klass.each(configs, function(and, and_i ){
                    var len = 0 ;
                    Klass.each(and, function(values, dep_name ){
                        if( this.elements.hasOwnProperty(dep_name) ) {
                            if( !_.contains(element_deps, dep_name) ) {
                                element_deps.push(dep_name) ;
                            }
                            len++ ;
                        } else if ( dynamic_values.hasOwnProperty(dep_name) ) {
                            len++ ;
                            if( !_.contains(element_deps, dep_name) ) {
                                element_deps.push(dep_name) ;
                            }
                        } else {
                            delete dynamic_deps[element_name][i][dep_name] ;
                        }
                    }, this);
                    if( !len ) {
                        removed.push( and[i] ) ;
                    }

                }, this);
                for(var i = removed.length; i--; ) {
                    dynamic_deps[element_name].splice(i, 1) ;
                }
                if( this.elements.hasOwnProperty(element_name) ) {
                    show_on[element_name]   = element_deps ;
                }
            }, this );

            this.isElementVisible  = function(element_name) {
                if( !dynamic_deps.hasOwnProperty(element_name) ) {
                    return true ;
                }
                if( _.some( dynamic_deps[element_name], function(and){
                    return _.every(and, function(values, name){
                        if( this.elements.hasOwnProperty(name) ) {
                            var el  = this.elements[name] ;
                            if( !el.display ) {
                                return false ;
                            }
                            return _.contains(values, el.getValue() ) ;
                        } else {
                            if( !this.isElementVisible(name) ) {
                                return false ;
                            }
                            return _.contains(values, dynamic_values[name] ) ;
                        }
                    }, this);
                }, this) ) {
                    return true ;
                } else {
                    return false ;
                }
            }

            for(var name in this.elements) if( this.elements.hasOwnProperty(name) ) {
                this.elements[name].setup() ;
                if( dynamic_deps.hasOwnProperty(name) ) {
                    this.elements[name].hide() ;
                } else {
                    this.elements[name].show() ;
                }
            }

            (function(){

                var deps    = [] ;
                var hide_deps   = [] ;

                Klass.each( dynamic_deps , function(configs , element_name ) {
                    var el = null;
                    if ( this.elements.hasOwnProperty(element_name) ) {
                        el = this.elements[ element_name ] ;
                    }
                    var has_dep_elements    = false ;
                    Klass.each(configs, function(and, and_i ){
                        Klass.each(and, function(values, dep_name ){
                            var dep_el = null ;
                            if( this.elements.hasOwnProperty(dep_name) ) {
                                dep_el = this.elements[ element_name ] ;
                                has_dep_elements    = true ;
                                if( !_.contains(deps, dep_el) ) {
                                    deps.push(dep_el) ;
                                }
                                if( el ) {
                                    dep_el.addEvent('changed', el.onDepsChange ) ;
                                    dep_el.addEvent('show', el.onDepsChange ) ;
                                    dep_el.addEvent('hide', el.onDepsChange ) ;
                                }
                            }
                        }, this);
                    }, this);

                    if( el && !has_dep_elements ) {
                        if( !_.contains(hide_deps, el) ) {
                            hide_deps.push(el) ;
                        }
                    }
                }, this ) ;

                setTimeout(function(){
                    for(var i = hide_deps.length; i--;) {
                        var el  = hide_deps[i] ;
                        el.onDepsChange() ;
                    }

                    for(var i = deps.length; i--;) {
                        var el  = deps[i] ;
                        el.fireEvent('change') ;
                    }
                }, 200 );
            }).call(this);

        }
    });
    return Class ;
})();

$(function(){
   $.each($('form.sf-form-dynamic'), function(){
       var form = new SymforceFormDynamic(this) ; 
       var validator = new SymforceFormValidator(this, {
           
       }) ; 
   });
   $('.sf_form_btn_cancel').click(function(evt){
       var url  = null ;
        _.some($(this).closest('form').find('input[type="hidden"]').toArray(), function(el){
            if( /sf_admin_form_referer/.test( $(el).attr('name') ) ) {
                url = $(el).val() ;
                return true ;
            }
        });
        window.location = url ;
    });
});



var SymforceFormValidator   = (function(){
    
    var Element = new Klass({
        initialize: function(form, group, type, elements ){
            this.form    = form ;
            this.group  = group ;
            this.type   = type ;
            this.elements   = elements ;
        },

        setup: function() {
            if( ! this.elements.length ) {
                return ;
            }
            var iTimer  = null ;
            var klass   = this ;
            
            $.each(this.elements, function(el) {
                var _this   = $(this) ;
                _this.on("focus",  function(evt){
                    _this.trigger('sf_focus');
                });
                _this.on("sf_focus",  function(){
                    if( iTimer ) {
                       clearTimeout(iTimer) ;
                       iTimer   = null ;
                    }
                });
                
                _this.on("blur",  function(evt){
                   if( iTimer ) {
                       clearTimeout(iTimer) ;
                   }
                   iTimer = setTimeout(function(){
                       _this.trigger('sf_blur');
                   }, 200 ) ;
                });
                
                _this.on("sf_blur",  function(evt){
                    klass.validate(evt, _this);
                });
                
                _this.on("sf_validate",  function(evt){
                   if( iTimer ) {
                       clearTimeout(iTimer) ;
                       iTimer   = null ;
                   }
                   klass.validate(evt, _this);
                });
                
            })
        },

        setError: function( error ){
            var group   = $(this.group); // .closest('.form-group') ; 
            if( error ) {
                group.addClass('has-error') ;
                var help    = group.find('.help-block') ;
                if( !help.get(0) ) {
                    help    = $('<span></span>') ;
                    help.addClass('help-block');
                    var box = null ;
                    if( this.elements.length ) {
                        box = $(this.elements[0] ).closest('div') ; 
                    } else if( this._elements.length ) {
                        box = $(this._elements.get(0) ).closest('div') ; 
                    } else {
                        box = group.find('div').get(0) ; 
                    }
                    if( $(box).hasClass('input-group') ) {
                        box = $(box).closest('div:not(.input-group)') ;
                    }
                    help.appendTo(box) ;
                }
                help.html( error ) ;
            } else {
                group.find('.help-block').empty() ;
                group.removeClass('has-error') ;
            }
        },
        
        validate: function(evt, input) {
            var data    = this.form.getJsonData() ;
            var name    = input.attr('name') ;
            data['sf_validate_element'] =  name ;
            
            var validate_data  = $.param( data ) ;
            if( this.form.validate_cache.hasOwnProperty(name) && validate_data === this.form.validate_cache[name] ) {
                return ;
            }
            
            this.form.validate_cache[name]  = validate_data ;
            
            var klass   = this ;
            $.ajax( this.form.options.url , {
                cache: false ,
                data: data ,
                dataType: "json" ,
                type:  'POST' ,
                complete: function(xhr, status) {
                    if( 'success' == status ) {
                        var o   = xhr.responseJSON ;
                        if( o.error.length ) {
                           klass.setError( o.error.join('<br />') );
                        } else {
                           klass.setError(null) ;
                        }
                    } else {
                       // console.log( status, xhr.responseText ) ;
                    }
                }
            });
        } 
    });
    
    if (Object.hasOwnProperty.call(Array.prototype, 'indexOf')) {
		var $contains = function(array, item) {
			return -1 != array.indexOf(item);
		};
	} else {
		var $contains = function(array, item) {
			for ( var i = array.length; i--;) {
				if (array[i] == item)
					return true;
			}
			return false;
		};
	}
        
    var Class   = new Klass({
        options: {
           url: null ,
           elements: null , 
           skip: null 
        } ,
        initialize: function(form, options){
            this.dom    = $(form) ;
            this.setOptions( options ) ;
            this.validate_cache = {} ;
            
            if( !this.options.url ) {
                this.options.url    = this.dom.attr('action') ;
            }
            
            var force_elements  = null ;
            if( this.options.elements ) {
                force_elements = $(this.options.elements).toArray() ;
                if( ! force_elements.length ) {
                    force_elements  = null ;
                }
            }
            var skip_elements  = null ;
            if( this.options.skip ) {
                skip_elements = $(this.options.skip).toArray() ;
                if( ! skip_elements.length ) {
                    skip_elements  = null ;
                }
            }
            
            var groups      = this.dom.find('.form-group').toArray() ;
            var children    = [] ;
            var last_child  = null ;
            for(var i = 0; i < groups.length; i++ ) {
                var group_type  = $(groups[i]).attr('sf_form_type') ;
                if( 'sf_group' === group_type ) {
                    continue ;
                }
                var _elements  = $(groups[i]).find('input,email,select,textarea') ;
                var elements   = _elements.toArray() ;
                if( elements.length ) {
                    for(var j = elements.length; j-- ;) {
                        if( force_elements && !$contains(force_elements, elements[j]) ) {
                            elements.splice(j, 1) ;
                        }
                        if( skip_elements && $contains(skip_elements, elements[j])  ) {
                            elements.splice(j, 1) ;
                        }
                    }
                }
                var type    = 'null' ;
                if( elements.length ) {
                    type   = String(elements[0].tagName).toLowerCase() ;
                    if( 'input' == type ) {
                        type    = $(elements[0]).attr('type') ;
                    }
                } 
                
                var child   = null ;
                if( last_child && 'password' == type && type == last_child.type ) {
                    if( /^\w+\[\w+\]\[\w+\]/.test(elements[0].name) && /^\w+\[\w+\]\[\w+\]/.test( last_child.elements[0].name) ) {
                        child   = last_child ;
                        $.each(elements, function(el) {
                            child.elements.push(this) ;
                        });
                    }
                } 
                
                if( !child ){
                    child   = new Element(this, groups[i], type, elements ) ; 
                    child._elements = _elements ;
                    if( elements.length  ) {
                        child.name  = $(elements[0]).attr('name') ;
                    } else if( _elements.length ){
                        child.name  = $(_elements.get(0)).attr('name') ;
                    } else {
                        child.name  = null ;
                    }
                }
                if( elements.length  ) {
                    last_child  = child ;
                }
                children.push( child ) ;
            }
            for(var i = 0; i < children.length; i++ ) {
                var child   = children[i] ;
                child.setup() ;
            }
            this.children   = children ;
        },
        setError: function( name , error ){
            _.some(this.children, function(child, i){
                if( child.name == name ) {
                    child.setError( error ) ;
                    return true ;
                }
            }, this );
        },
        getJsonData: function(){
            var o = {};
            var a = this.dom.serializeArray() ;
            $.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        }
    });
    return Class ;
})();

var SymforceFormFile = new Klass({
    Binds: [ 'onImageLoad', 'onAdjustClick', 'onDeleteClick', 'onDefaultClick' ],
    options: {
        is_image: false ,
        types: 'txt' ,
        max: 10240 ,
        url: null ,
        type_error: null ,
        size_error: null ,
        id: null 
    } ,
    initialize: function(input, options){
        this.setOptions(options);
        this.input  = $(input) ;
        this.box    =  this.input.closest('div') ;
        this.handle  = this.box.find('input[type="file"]') ;
        this.view  = this.box.find('.sf_form_file_view') ;
        
        var _this   = this ;
        if( this.options.is_image ){
            this.setupImageTools() ;
        }
        this.onload = true ;
        this.setValue( this.options.value.url, this.options.value.name ) ;
        
        function format_size(fileSizeInBytes) {
                var i = -1;
                var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
                do {
                    fileSizeInBytes = fileSizeInBytes / 1024;
                    i++;
                } while (fileSizeInBytes > 1024);

                return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
            };
        
        $( this.handle ).fileupload({
            url: _this.options.url ,
            // replaceFileInput: false ,
            dataType: 'json' ,
            paramName: 'attachment' ,
            autoUpload: true ,
            formData: function() {
                 var data = [ 
                        { name:'id', value : _this.options.id } ,
                        { name:'url', value: _this.input.val() } 
                     ] ;
                 return  data ;
            } ,
            error: function( data , e){
                alert( data.responseText, e ) ;
            } ,
            add: function (e, data) {
                var type_reg = new RegExp('\.(' + _this.options.types + ')$'  ,  'i' ) ;
                if( !type_reg.test(data.originalFiles[0]['name'])   ) {
                    _this.showError( _this.options.type_error, {
                        types : _this.options.types.replace(/\|/, ', ')  ,
                        file: data.originalFiles[0]['name'] 
                    });
                } else if(data.originalFiles[0]['size'] > _this.options.max ) {
                    _this.showError( _this.options.size_error, {
                        max_size : format_size(_this.options.max) ,
                        size: format_size(data.originalFiles[0]['size']) 
                    });
                } else {
                    data.submit();
                }
            },
            done: function (e, data) {
                var o   = data.result ;
                if ( o.url ) {
                    _this.setValue( o.url, o.name ) ;
                } else {
                     if( o.error ) {
                        _this.showError(o.error, o.args) ;
                     }
                     console.log( o ) ;
                }
            }
        }) ;
    },
    
    onAdjustClick: function(evt){
        var width   = this.image_real_width ;
        if( width < this.image_box_width ) {
            width   = this.image_box_width ;
        }
        if( width > 976 ) {
            width   = 976 ;
        } else if ( width < 300 ) {
            width   = 300 ;
        }
        var html = '<img id="sf_form_image_resize_corp" width="'+ width +'" src="' + this.input.val() + '"/>' ;
        var new_image_crop_percent = null ;
        
        var _this = this ;
        function on_close(){
            _this.image_jcrop_api.destroy() ;
            _this.image_jcrop_api   = null ;
            // console.log('close') ;
        }
        
        var modal   = bootbox.dialog({
            message: html ,
            title: this.file_name ,
            buttons: {
              main: {
                label: "保存",
                className: "btn-primary",
                callback: function() {
                    on_close();
                    if( new_image_crop_percent ) {
                        _this.setImageCropValue( new_image_crop_percent, true ) ;
                    }
                }
              },
              danger: {
                label: "取消",
                className: "btn-danger",
                callback: on_close 
              }
            }, 
            "onEscape": on_close
          });
          var modal_width = width + 40  ;
          if( modal_width < 300 ) modal_width = 300 ;
          modal.find("div.modal-dialog").css('width',  modal_width );
          setTimeout(function(){
              var height  = $('#sf_form_image_resize_corp').height() ;
              var p   = _this.image_crop_percent ;
              
              var options   = {
                  setSelect: crop , 
                  aspectRatio: _this.image_ratio ,
                  onSelect: function( _p ){
                      new_image_crop_percent = {
                          'left': _p.x / width ,
                          'top': _p.y / height ,
                          'width': _p.w / width ,
                          'height': _p.h / height  ,
                      } ;
                  },
              };
              
              if( 1 !== p.width || 1 !== p.height  || 0 !== p.top || 0 !== p.left ) {
                  var crop    = [ p.left * width, p.top * height, p.width * width, p.height * height ] ;
                  crop[2]   += crop[0] ;
                  crop[3]   += crop[1] ;
                  options['setSelect']  = crop ;
              }
              
              $('#sf_form_image_resize_corp').Jcrop(options, function() {
                    _this.image_jcrop_api = this ;
                    new_image_crop_percent = null ; 
              });
              
          }, 250);
    } ,
    onDeleteClick: function(evt){
        alert('onDeleteClick')
    } ,
    onDefaultClick: function(evt){
        alert('onDefaultClick')
    } ,
    
    onImageLoad: function(evt){
        this.image_real_width   = this.image_element.width() ;
        this.image_real_height  = this.image_element.height() ;
        
        if( !this.options.config.use_crop ) {
            // reset this image size
            var _ratio  = this.image_real_width / this.image_real_height ;

            var config_width    =  this.options.config.width ;
            var config_height    =  this.options.config.height ;

            var image_box_width = this.image_element.closest('.sf_form_file_view').width() ;
            if( config_width > image_box_width ) {
               config_height   = image_box_width  / config_width * config_height ;
               config_width    = image_box_width ;
            }
            if( _ratio > this.image_ratio ) { 
                this.image_element.width( config_width );
            } else {
                this.image_element.height( config_height  );
            }
            return ;
        }
        
        $( this.image_element ).resizecrop({
            width: this.options.config.width ,
            height: this.options.config.height ,
            vertical:"top"
        });
        var width   = this.image_element.width() ;
        var height  = this.image_element.height() ;
        this.image_box_width   = width ;
        this.image_box_height  = height ;
        
        var _p = this.image_element.position() ;
        if( _p.left < 0 ) {
            _p.left = 0 - _p.left ;
            if( _.top < 0 ) {
                alert('resizecrop error')
            }
        } else if ( _.top < 0 ){
            _p.top = 0 - _p.top ;
            if( _.left < 0 ) {
                alert('resizecrop error')
            }
        }
        var p   = {
            'width': this.options.config.width / width , 
            'height': this.options.config.height / height , 
            'left': Math.round(_p.left) / width , 
            'top': Math.round(_p.top) / width , 
        } ;
        this.setImageCropValue(p) ;
        if( this.onload ) {
              this.onload = false ;
        }
    },
    
    setImageCropValue: function(p, resize ){
        this.image_crop_percent = p ;
        if( this.onload ) {
            if( 1 !== p.width || 1 !== p.height  || 0 !== p.top || 0 !== p.left ) {
                this.input.next().val( JSON.stringify(p) ) ;
            }
        } else {
            this.input.next().val( JSON.stringify(p) ) ;
        }
        if( resize ) {
            var width    = this.options.config.width  / p.width ;
            var height    = this.options.config.height  / p.height ;
            var left   = 0 - width * p.left ;
            var top    = 0 - height * p.top ;
            this.image_element.css({
                'width': width ,
                'height': height ,
                'left': left ,
                'top': top 
            });
        }
    } ,
    setupImageTools: function(){
        
        this.image_element  = $(this.view).find('img') ;
        this.image_ratio    = this.options.config.width / this.options.config.height ;
        this.image_element.on('load', this.onImageLoad) ;
        this.image_real_width   = 0 ;
        this.image_real_height  = 0 ;
        
        this.image_adjust_handle  = this.box.find('.sf_form_image_adjust') ;
        this.image_delete_handle  = this.box.find('.sf_form_image_delete') ;
        this.image_default_handle  = this.box.find('.sf_form_image_default') ; 
        
        this.image_delete_handle.click( this.onDeleteClick );
        this.image_default_handle.click( this.onDefaultClick );
        
        if( this.options.config.use_crop ) {
            this.image_adjust_handle.click( this.onAdjustClick );
        } else {
            this.image_adjust_handle.css('display', 'none');
        }
    },
            
    setValue: function( url , name ) {
        
        this.file_name  = name ;
        
        if( this.options.is_image ) {
            if( url ) {
                  this.box.removeClass('sf_form_image_hidden') ;
                  this.image_element.attr('src', url ) ;
                  this.image_element.attr('alt', name ) ;
                  this.image_element.css('width', 'auto' ) ;
                  this.image_element.css('height', 'auto' ) ;
                  this.input.val( url ) ;
              } else {
                  this.box.addClass('sf_form_image_hidden') ;
                  this.image_element.attr('src', '' ) ;
                  this.image_element.attr('alt', '' ) ;
                  this.input.val( '' ) ;
                  if( this.onload ) {
                        this.onload = false ;
                  }
              }
        } else {
            if( url ) {
                  this.box.addClass('sf_form_file_show') ;
                  this.view.attr('href', url ) ;
                  this.view.attr('target', '_blank' ) ;
                  this.view.text(name ) ;
                  this.input.val( url ) ;
            } else {
                  this.box.removeClass('sf_form_file_show') ;
                  this.view.attr('href', 'javascript:alert(1)' ) ;
                  this.view.attr('target', '_self' ) ;
                  this.view.text('' ) ;
                  this.input.val( '' ) ;
            }
        }
    } ,
            
    showError: function( error, args ){
        if( args ) {
            for(var key in args ) {
                var reg = new RegExp('\@\{' + key + '\}' ) ;
                error   = error.replace(reg, args[key]) ;
            }
        }
        alert( error )
    } 
}) ;