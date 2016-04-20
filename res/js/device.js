var DeviceClass = function () {
    return;
};
DeviceClass.prototype = {
	changeName: function(name, android_id) {
		var newname = prompt("New name", name);
		if (newname === null) {
			return;
		}
		opt = {
			'url': '/Api/Devices/ChangeName',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'new_name': newname,
				'android_id': android_id
			},
			'callback': function() {
				document.location.href = '/account';
			},
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.post(opt);
		
	},
	deleteDevice: function (android_id) {
		var dialog = document.getElementById('delete_device');
		dialog.style.display = 'block';
	    //dialog.showModal();
	    dialog.querySelector('.close').addEventListener('click', function() {
	    	dialog.style.display = 'none';
	    });
	    dialog.querySelector('.remove').addEventListener('click', function() {
	    	opt = {
				'url': '/Api/Devices/Remove',
				'data': {
					'user': getCookie('user'),
					'token': getCookie('token'),
					'key': getCookie('key'),
					'android_id': android_id
				},
				'callback': function() {
					document.location.href = '/account';
				},
				'checkErrors': false,
				'decode': false
			};
			
			Ajax.post(opt);
	    	dialog.style.display = 'block';
	    });
	}
};
var Device = new DeviceClass();
window.nb_script_to_load--;