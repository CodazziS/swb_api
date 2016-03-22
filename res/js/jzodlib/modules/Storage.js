/*
 *   Put and get information of localStorage
 */

var StorageClass = function () {
    return;
};

StorageClass.prototype = {

    put: function (name, data) {
        var uncompressData = JSON.stringify(data);
        localStorage.setItem(name, uncompressData);
    },

    get: function (name, default_value) {
        var data,
            uncompressData;
        data = localStorage.getItem(name);
        if (data !== null && data !== undefined) {
            uncompressData = JSON.parse(data);
            return uncompressData;
        }
        return default_value;
    },
    cleanAll: function () {
        localStorage.clear();
    }
};

var Storage = new StorageClass();
