var UtilsClass = function () {
    return;
};

UtilsClass.prototype = {
    setTimeout: function (callback, delay) {
        var ms = 50,
            loop,
            dueTo = new Date(new Date().getTime() + delay);

        loop = function () {
            if (new Date() < dueTo) {
                window.setTimeout(loop, ms);
            } else {
                callback();
            }
        };
        loop();
    }
};

var Utils = new UtilsClass();

