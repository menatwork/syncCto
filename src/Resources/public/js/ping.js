/* Copyright (c) MEN AT WORK 2014 :: LGPL license */

function sendNextRequest(_strToken, _objElement)
{
    var item = _objElement;
    var element = $(_objElement);

    if (typeof element == "undefined" || element == null)
    {
        console.log('Empty item for ping.');
        return;
    }

    element = element.getSiblings('span');
    if (typeof element == "undefined" || element == null)
    {
        console.log('Empty sibling for ping.');
        return;
    }

    element = element.getChildren('span.client-id');
    if (typeof element == "undefined" || element == null)
    {
        console.log('Empty children for ping.');
        return;
    }

    element = element[0];
    if (typeof element == "undefined" || element == null)
    {
        console.log('Empty sub element for ping.');
        return;
    }

    element = element.getProperty("text").toString();
    if (typeof element == "undefined" || element == null)
    {
        console.log('Empty string for ping.');
        return;
    }

    // Get client id
    var clientID = element;
    var data;

    // Check if the request token is set
    if (typeof(REQUEST_TOKEN) !== 'undefined')
    {
        data = {
            "isAjax":        1,
            "action":        "syncCtoPing",
            "clientID":      clientID,
            "REQUEST_TOKEN": _strToken
        }
    }
    else
    {
        data = {
            "isAjax":   1,
            "action":   "syncCtoPing",
            "clientID": clientID
        }
    }

    // Send new request for ping
    new Request.JSON({
        method:       'post',
        url:          window.location.href,
        data:         data,
        evalScripts:  false,
        evalResponse: false,
        onSuccess:    function (json)
                      {
                          if (json.success == false || json.value == 0)
                          {
                              item.setProperty('src', 'bundles/synccto/images/js/gray.png');
                              item.setProperty('title', json.msg);
                          }
                          else if (json.value == 1)
                          {
                              item.setProperty('src', 'bundles/synccto/images/js/red.png');
                              item.setProperty('title', json.msg);
                          }
                          else if (json.value == 2)
                          {
                              item.setProperty('src', 'bundles/synccto/images/js/blue.png');
                              item.setProperty('title', json.msg);
                          }
                          else if (json.value == 3)
                          {
                              item.setProperty('src', 'bundles/synccto/images/js/orange.png');
                              item.setProperty('title', json.msg);
                          }
                          else if (json.value == 4)
                          {
                              item.setProperty('src', 'bundles/synccto/images/js/green.png');
                              item.setProperty('title', json.msg);
                          }
                      }.bind(item),
        onFailure:    function (responseTree, responseElements, response, js)
                      {
                          // On error show red point.
                          item.setProperty('src', 'bundles/synccto/images/js/red.png');
                      }.bind(item)
    }).send();
}

window.addEvent("domready", function ()
{
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
        var elements = $$('img.ping');
        for (var key in elements)
        {
            // Skip loop if the property is from prototype.
            if (!elements.hasOwnProperty(key) || key == 'length')
            {
                continue;
            }

            sendNextRequest(REQUEST_TOKEN, elements[key]);
        }
    }
});
