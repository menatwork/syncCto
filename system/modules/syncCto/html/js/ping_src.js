/* Copyright (c) MEN AT WORK 2011 :: LGPL license */

window.addEvent("domready",function(){
    $$('img.ping').each(function(item){
        var url=item.getSiblings('span').getElement('span').getProperty('html')+'GPL.txt';
        try {
            var req=new Request.HTML({
                method:'post',
                url:window.location.href,
                data: {
                    "isAjax"    : 1,
                    "action"    : "syncCtoPing",
                    "hostIP"   	: url
                },
                evalScripts:false,
                evalResponse:false,
                onSuccess:function(responseTree,responseElements,response,js){

                    if(response=='true'){
                        item.setProperty('src','system/modules/syncCto/html/js/images/missing.png');
                        var req2=new Request.HTML({
                            method:'post',
                            url:window.location.href,
                            data: {
                                "isAjax"    : 1,
                                "action"    : "syncCtoPing",
                                "hostIP"    : url.replace(/GPL.txt/g,"syncCto.php")
                            },
                            evalScripts:false,
                            evalResponse:false,
                            onSuccess:function(responseTree,responseElements,response,js){
                                if(response=='true'){
                                    item.setProperty('src','system/modules/syncCto/html/js/images/online.png');
                                }
                            }
                        }).send();
                    }else{
                        item.setProperty('src','system/modules/syncCto/html/js/images/offline.png');
                    }
                },
                onFailure:function(responseTree,responseElements,response,js){
                    item.setProperty('src','system/modules/syncCto/html/js/images/offline.png');
                }
            }).send();
        } catch (exception) {
            return false;
        }
    });
});