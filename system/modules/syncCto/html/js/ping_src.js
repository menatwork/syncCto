/* Copyright (c) MEN AT WORK 2011 :: LGPL license */

window.addEvent("domready",function(){
    $$('img.ping').each(function(item){
        var clientID = item.getSiblings('span').getChildren('span.client-id')[0].getProperty("text").toString();
        var req=new Request.HTML({
            method:'post',
            url:window.location.href,
            data: {
                "isAjax"    : 1,
                "action"    : "syncCtoPing",
                "clientID"  : clientID
            },
            evalScripts:false,
            evalResponse:false,
            onSuccess:function(responseTree,responseElements,response,js){                    
                if(response=='0' || response=='false')
                {
                    item.setProperty('src','system/modules/syncCto/html/js/images/offline.png');         
                }
                else if(response=='1')
                {   
                    item.setProperty('src','system/modules/syncCto/html/js/images/missing.png');   
                }
                else if(response=='2' || response=='3')
                {   
                    item.setProperty('src','system/modules/syncCto/html/js/images/online.png');
                }
            },
            onFailure:function(responseTree,responseElements,response,js){
                item.setProperty('src','system/modules/syncCto/html/js/images/offline.png');
            }
        }).send();
    });
});