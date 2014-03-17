/* Copyright (c) MEN AT WORK 2012 :: LGPL license */

function sendNextRequest(_strToken, _objElements, _intIndex)
{
    item = _objElements[_intIndex];

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
            "REQUEST_TOKEN" : _strToken
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
    new Request.JSON({
        method:'post',
        url:window.location.href,
        data: data,
        evalScripts:false,
        evalResponse:false,
        onSuccess:function(json){ 
            if(json.success == false)
            {
                item.setProperty('src','system/modules/syncCto/assets/js/images/gray.png');
                item.setProperty('title',json.msg);      
            }
            else if(json.value == 0)
            {
                item.setProperty('src','system/modules/syncCto/assets/js/images/gray.png');
                item.setProperty('title',json.msg);    
            }
            else if(json.value == 1)
            {   
                item.setProperty('src','system/modules/syncCto/assets/js/images/red.png');
                item.setProperty('title',json.msg);    
            }
            else if(json.value == 2)
            {   
                item.setProperty('src','system/modules/syncCto/assets/js/images/blue.png');
                item.setProperty('title',json.msg);    
            }
            else if(json.value == 3)
            {   
                item.setProperty('src','system/modules/syncCto/assets/js/images/orange.png');
                item.setProperty('title',json.msg);    
            }
            else if(json.value == 4)
            {   
                item.setProperty('src','system/modules/syncCto/assets/js/images/green.png');
                item.setProperty('title',json.msg);    
            }
            
            if((_objElements.length - 1 ) > _intIndex)
            {
                sendNextRequest(json.token, _objElements, (_intIndex + 1));
            }
        }.bind(_objElements).bind(_intIndex),
        onFailure:function(responseTree,responseElements,response,js){
            // On error show red point
            item.setProperty('src','system/modules/syncCto/assets/js/images/offline.png');
        }.bind(_objElements).bind(_intIndex)
    }).send();
}

window.addEvent("domready", function() {

    if (typeof Contao !== 'undefined' && typeof Contao.request_token !== 'undefined')
    {
        REQUEST_TOKEN = Contao.request_token;
    }

    if (typeof(REQUEST_TOKEN) == "undefined")
    {
        REQUEST_TOKEN = 0;
    }

    if ($$('img.ping').length != 0)
    {
        sendNextRequest(REQUEST_TOKEN, $$('img.ping'), 0);
    }
});