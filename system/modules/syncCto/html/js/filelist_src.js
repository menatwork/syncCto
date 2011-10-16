/* Copyright (c) MEN AT WORK 2011 :: LGPL license */

window.addEvent("domready",function(){
	
	$each($$('th.checkbox input[type=checkbox]'), function(item){
		item.addEvents({
                click: function(){
                    console.log(item.getProperty('onclick'));
                }
            });	
		
	});

	$$('input[name=delete]').set('disabled', true);
});