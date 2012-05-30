/* Copyright (c) MEN AT WORK 2012 :: LGPL license */

window.addEvent("domready",function(){
    
    if ($('db_form')) {
        $$('input[name=transfer]').set('disabled', true);
    } else {
        $$('input[name=delete]').set('disabled', true);
    }
        
    $each($$('.checkbox input[type=checkbox]'), function(item){
        item.addEvents({
            click: function(){
                var blnFound = ($$('.checkbox input[type=checkbox]:checked').length != 0) ? true : false;
                
                if (blnFound == true){
                    $$('input[name=delete]').set('disabled', false);
                    if ($('db_form')) {
                        $$('input[name=transfer]').set('disabled', false);
                    } else {
                        $$('input[name=transfer]').set('disabled', true);
                    }
                } else {
                    $$('input[name=delete]').set('disabled', true);
                    if ($('db_form')) {
                        $$('input[name=transfer]').set('disabled', true);
                    } else {
                        $$('input[name=transfer]').set('disabled', false);
                    }
                }
                
            }
        });	
    });

   var myHtmlTable = new HtmlTable($('filelist'), {sortable: true, sortIndex: 3, sortReverse: true});

});