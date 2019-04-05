/* Copyright (c) MEN AT WORK 2014 :: LGPL license */

window.addEvent("domready",function(){

    $$('input[name=delete]').set('disabled', true);

    $$('.checkbox input[type=checkbox]').each(function(item){
        item.addEvents({
            click: function(){
                var blnFound = ($$('.checkbox input[type=checkbox]:checked').length != 0) ? true : false;
                
                if (blnFound == true){
                    $$('input[name=delete]').set('disabled', false);
                    $$('input[name=transfer]').set('disabled', true);
                } else {
                    $$('input[name=delete]').set('disabled', true);
                    $$('input[name=transfer]').set('disabled', false);
                }
            }
        });
    });

    if(window.HtmlTable)
    {
        $$('body').addClass('table-sort');
        
        HtmlTable.defineParsers({
            dimension: {
                match: '.*(Bytes|KiB|MiB|GiB)',
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
            sortIndex: 3,
            parsers: ['string', 'dimension', 'number', 'string', 'string'],
            sortable: true
        }).enableSort({
            sortable: true, 
            sortIndex: 3
        });
        
        var myHtmlTableBig = new HtmlTable($('bigfilelist'), {
            sortIndex: 3,
            parsers: ['string', 'dimension', 'number', 'string', 'string'],
            sortable: true
        }).enableSort({
            sortable: true, 
            sortIndex: 3
        });
    }

});