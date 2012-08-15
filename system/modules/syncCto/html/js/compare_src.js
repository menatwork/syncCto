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

    if(window.HtmlTable)
    {
        $$('body').addClass('table-sort');
        
        HtmlTable.defineParsers({
            dimension: {
                match: '.*(Bytes|kB|mB|gB)',
                convert: function(){
                    var strDimension = this.get('text').replace(/.*(\d|,| )/, '').toString().toLowerCase();
                    var floatVal = this.get('text').replace(',', '.').toFloat();

                    if(strDimension == "kb")
                    {
                        floatVal = floatVal * 1024;
                    }
                    else if(strDimension == "mb")
                    {
                        floatVal = floatVal*[Math.pow(1024,2)];
                    }
                    else if(strDimension == "gb")
                    {
                        floatVal = floatVal*[Math.pow(1024,3)];
                    }

                    return floatVal;
                },
                number: true
            }
        });    

        var myHtmlTableNormal = new HtmlTable($('normalfilelist'), {
            sortIndex: 0,
            parsers: ['string', 'dimension', 'string'],
            sortable: true
        }).enableSort({
            sortable: true, 
            sortIndex: 0
        });
        
        var myHtmlTableBig = new HtmlTable($('bigfilelist'), {
            sortIndex: 0,
            parsers: ['string', 'dimension', 'string'],
            sortable: true
        }).enableSort({
            sortable: true, 
            sortIndex: 0
        });
    }

});