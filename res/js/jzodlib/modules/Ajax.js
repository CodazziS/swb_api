/*global CHECK_NETWORK, NETWORK_OK, NETWORK_FAIL, DEFAULT_DECODE, DEFAULT_ENCODE, ActiveXObject, console */

var AjaxClass = function () {
    return;
};

AjaxClass.prototype = {

    /*
        checkNetworkErrors(function callback, string json)

        - def:
            This function was called after an ajax request if checkerror was true
            The function check if the result of the request contain the field "error"


        - args:
            callback: function called after this function
            json: result of ajax request

        - result:

     */
    checkNetworkErrors: function (callback, json) {
        var error;

        if (CHECK_NETWORK) {
            error = JSON.parse(json).error;
            if (error !== undefined) {
                callback(json);
                this.network_ok();
            } else {
                this.network_fail();
                callback();
            }
        } else {
            callback(json);
        }
    },


    /*
     network_ok()

     - def:
        This function was called after a good ajax request for exemple, remove an error message.
        "NETWORK_OK" must be define in configs.js

     - args:

     - result:

     */
    network_ok: function () {
        if (typeof NETWORK_OK === 'function') {
            NETWORK_OK();
        }
    },

    /*
     network_fail()

     - def:
        This function was called after a bad ajax request for exemple, put an error message.
        "NETWORK_FAIL" must be define in configs.js

     - args:

     - result:

     */
    network_fail: function () {
        if (CHECK_NETWORK) {
            if (typeof NETWORK_FAIL === 'function') {
                NETWORK_FAIL();
            }
        }
        console.warn("Network Fail");
    },

    /*
     execute(string method, string url, object data, function callback, boolean checkErrors, boolean encode)

     - def:
        This function create an AJAX request.
        Called by function post or get in this class

     - args:
        method: GET or POST
        url: url to call (api)
        data: data to send (encrypted)
        callback: function to call after the request
        checkErrors: boolean to check, or not if the request is good.
        decode: if the datas received was encoded or not

     - result:

     */
    execute: function (method, url, data, callback, checkErrors, decode) {
        var xhr = this.getXMLHttpRequest(),
            self = this,
            response;

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.responseText !== "") {
                if (xhr.status === 200 || xhr.status === 0) {
                    if (callback !== null) {
                        if (decode === true) {
                            response = DEFAULT_DECODE(xhr.responseText);
                        } else {
                            response = xhr.responseText;
                        }
                        if (checkErrors) {
                            self.checkNetworkErrors(callback, response);
                        } else {
                            callback(response);
                        }
                    }
                } else {
                    if (checkErrors) {
                        self.network_fail();
                    }
                    callback();
                }
            } else {
                if (xhr.readyState > 3 || xhr.readyState === 4) {
                    if (checkErrors) {
                        self.network_fail();
                    }
                    callback();
                }
            }
        };

        xhr.open(method, url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        try {
            xhr.send(data);
        } catch (e) {
            if (checkErrors) {
                self.network_fail();
            }
        }

    },

    /*
     prepare_args(object args)

     - def:
        Create all options fields for an ajax request with default values

     - args:
        args: options for ajax request

     - result:
        args

     */
    prepare_args: function (args) {
        if (args === undefined || args === null) {
            args = {};
        }
        if (args.url === undefined) {
            args.url = null;
        }
        if (args.callback === undefined) {
            args.callback = null;
        }
        if (args.data === undefined) {
            args.data = null;
        }
        if (args.checkErrors === undefined) {
            args.checkErrors = false;
        }
        if (args.toEncode === undefined) {
            args.toEncode = false;
        }
        return args;
    },

    /*
     get(object args)

     - def:
        Create an get ajax request

     - args:
        args: options for ajax request

     - result:

     */
    get: function (args) {
        var paramsString = "",
            i;

        args = this.prepare_args(args);
        for (i in args.data) {
            if (args.data.hasOwnProperty(i)) {
                if (paramsString.length === 0) {
                    paramsString += "?";
                } else {
                    paramsString += "&";
                }
                paramsString += i;
                paramsString += "=";
                paramsString += DEFAULT_ENCODE(args.data[i]);
            }
        }

        this.execute('GET', args.url + paramsString, null, args.callback, args.checkErrors, args.toEncode);
    },

    /*
     post(object args)

     - def:
        Create an get ajax request

     - args:
        args: options for ajax request

     - result:

     */
    post: function (args) {
        var paramsString = "",
            i;

        args = this.prepare_args(args);
        for (i in args.data) {
            if (args.data.hasOwnProperty(i)) {
                if (paramsString.length > 0) {
                    paramsString += "&";
                }
                paramsString += i;
                paramsString += "=";
                paramsString += DEFAULT_ENCODE(args.data[i]);
            }
        }

        this.execute('POST', args.url, paramsString, args.callback, args.checkErrors, args.toEncode);
    },

    /*
        @DEPRECATED
     */
    /*include: function (url, callback) {
        this.execute('GET', url, null, callback, false, false);
    },*/

    /*
     getXMLHttpRequest(object args)

     - def:
        Create an get ajax request for the browser

     - args:

     - result:

     */
    getXMLHttpRequest: function () {
        var xhr = null;
        if (window.XMLHttpRequest || window.ActiveXObject) {
            if (window.ActiveXObject) {
                try {
                    xhr = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
            } else {
                xhr = new XMLHttpRequest();
            }
        } else {
            return null;
        }
        return xhr;
    }
};

var Ajax = new AjaxClass();
