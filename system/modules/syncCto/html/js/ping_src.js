/* Copyright (c) MEN AT WORK 2011 :: LGPL license */

window.addEvent("domready",function(){
    // For each image 
    $$('img.ping').each(function(item){
        // Get client id
        var clientID = item.getSiblings('span').getChildren('span.client-id')[0].getProperty("text").toString();
        var data;
        
        // Check if the request token is set
        if( typeof(REQUEST_TOKEN) !== 'undefined' )
        {
            data = {
                "isAjax"    : 1,
                "action"    : "syncCtoPing",
                "clientID"  : clientID,
                "REQUEST_TOKEN" : REQUEST_TOKEN
            }
        }
        else
        {
             data = {
                "isAjax"    : 1,
                "action"    : "syncCtoPing",
                "clientID"  : clientID
            }
        }
        
        // Send new request for ping
        new Request.HTML({
            method:'post',
            url:window.location.href,
            data: data,
            evalScripts:false,
            evalResponse:false,
            onSuccess:function(responseTree,responseElements,response,js){                    
                // On this position we have a error or no response from client
                if(response=='0' || response=='false')
                {
                    item.setProperty('src','system/modules/syncCto/html/js/images/offline.png');         
                }
                // We have a response but no ctoCommunication.php found
                else if(response=='1')
                {   
                    item.setProperty('src','system/modules/syncCto/html/js/images/missing.png');   
                }
                // We have found ctoCommunication
                else if(response=='2' || response=='3')
                {   
                    item.setProperty('src','system/modules/syncCto/html/js/images/online.png');
                }
            },
            onFailure:function(responseTree,responseElements,response,js){
                // On error show red point
                item.setProperty('src','system/modules/syncCto/html/js/images/offline.png');
            }
        }).send();
    });
});