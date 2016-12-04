var MessagesClass = function () {
    return;
};
MessagesClass.prototype = {
	last_name: '',
	last_format_address: '',
	last_address: '',
	last_device_id: '',
	last_sync: 0,
	last_sync_mess: 0,
	last_sync_mess_unread: 0,
	nb_unread: 0,
	refresh_started: false,
	last_contact_page: '',
	contact_img_list: null,
	
	init: function() {
		Messages.getLastSync();
		window.addEventListener("resize", Messages.resizeSoResetSwitch);
		
	},
	getLastSync: function() {
		var opt;
		
		opt = {
			'url': '/api/messages/getLastSync',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key')
			},
			'callback': Messages.getLastSyncRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	getLastSyncRes: function(data) {
		var json_data;
		
		json_data = JSON.parse(data);
		
		if (json_data.last_message_unread > 0 && json_data.last_message_unread > Messages.last_sync_mess_unread) {
	        Messages.last_sync_mess_unread = json_data.last_message_unread;
	    }
		    
		if (json_data.last_message > Messages.last_sync || json_data.messages_unread != Messages.nb_unread) {

			Messages.last_sync = json_data.last_message;
			Messages.nb_unread = json_data.messages_unread;
			Messages.getActiveContacts();
		}
		Utils.setTimeout(function(){ Messages.getLastSync(); }, 10000);
	},
	
	getActiveContacts: function() {
		var opt;
		
		opt = {
			'url': '/Api/Contacts/GetActive',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key')
			},
			'callback': Messages.getActiveContactsRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	getActiveContactsRes: function(data) {
		var html = '',
			json_data,
			addr,
			img_params,
			i;
			
		json_data = JSON.parse(data);
		Messages.contact_img_list = {};
		
		for (i in json_data.address) {
			addr = json_data.address[i];
			img_params = 'user='+getCookie('user')+'&token='+getCookie('token')+'&key='+getCookie('key')+'&format_address='+addr.format_address+'&device_id='+addr.device_id;
			html += '<div class="messages_contact_item" onclick="Messages.switchView(true); Messages.launchContactRefresh(\'' + addr.address + '\', \'' + addr.format_address + '\', \'' + addr.device_id + '\', \'' + addr.name + '\')">';
			
			if (addr.have_img) {
			    Messages.contact_img_list[addr.format_address] = true;
			    html += '   <div class="messages_contact_item_img"><img src="/api/Contacts/getContactImg?'+img_params+'" /></div>';
			} else {
				html += '	<div class="messages_contact_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			}

			html += '	<div class="messages_contact_item_infos">';
			html += '		<div class="messages_contact_item_name">' + addr.name + '</div>';
			if (addr.unread > 0) {
				html += '		<div class="messages_contact_item_subname mdl-badge" data-badge="'+addr.unread+'">' + addr.model + ' - ' + timeToDate(addr.time) + '</div>';
			} else {
				html += '		<div class="messages_contact_item_subname">' + addr.model + ' - ' + timeToDate(addr.time) + '</div>';
			}
			html += '	</div>';
			html += '</div>';
		}	
		document.getElementById('messages_contacts').innerHTML = html;
	},
	
	launchContactRefresh: function(address, format_address, device_id, name) {
		
		document.getElementById('messages_list').innerHTML = '<div class="mdl-progress mdl-js-progress mdl-progress__indeterminate center100"></div>';
		
		Messages.last_format_address = format_address;
		Messages.last_address = address;
		Messages.last_device_id = device_id;
		Messages.last_name = name;
		Messages.last_sync_mess = 0;
		Messages.last_contact_page = '';
		
		componentHandler.upgradeDom();
		
		if (!Messages.refresh_started) {
			Messages.getLastSyncMess();
			Messages.refresh_started = true;
		}
	},
	getLastSyncMess: function() {
		var opt;
		
		opt = {
			'url': '/api/messages/getLastSyncMessage',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'device_id': Messages.last_device_id,
				'format_address': encodeURI(Messages.last_format_address)
			},
			'callback': Messages.getLastSyncMessRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	
	getLastSyncMessRes: function(data) {
		var json_data;
		
		json_data = JSON.parse(data);
		if (json_data.last_message_unread > 0 && json_data.last_message_unread > Messages.last_sync_mess_unread) {
	        Messages.last_sync_mess_unread = json_data.last_message_unread;
	    }
		    
		if (json_data.last_message > Messages.last_sync_mess) {
			Messages.last_sync_mess = json_data.last_message;
			Messages.getMessages();
			
		}
		Utils.setTimeout(function(){ Messages.getLastSyncMess(); }, 3000);
	},
	
	getMessages: function() {
		var opt;
		
		opt = {
			'url': '/Api/Messages/GetMessages',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'format_address': encodeURI(Messages.last_format_address),
				'device_id': Messages.last_device_id
			},
			'callback': Messages.getMessagesRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	
	getMessagesRes: function(data) {
		var json_data = JSON.parse(data);

		if (Messages.last_contact_page !== (Messages.last_address + Messages.last_device_id)) {
			document.getElementById('messages_list').innerHTML = Messages.formatMessagePage();
		}
		
		Messages.last_contact_page = (Messages.last_address + Messages.last_device_id);
		document.getElementById('messages_list_items').innerHTML =  Messages.formatMessageList(json_data);
		componentHandler.upgradeDom();
		document.getElementById("messages_list_items").scrollTop = document.getElementById("messages_list_items").scrollHeight;
	},
	
	formatMessagePage: function() {
		var html = '';
		
		html += '<div class="message_list_title">' + Messages.last_name + '</div>';
		html += '<div id="messages_list_items"></div>';
		html += '<div id="messages_inputs">';
		html += '	<form action="#" onsubmit="Messages.sendMessage(\'' + Messages.last_address + '\', \'' + Messages.last_device_id + '\'); return false;">';
		html += '		<div id="messages_input_block" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">';
		html += '			<input class="mdl-textfield__input" autocomplete="off" type="text" id="messages_input">';
		html += '			<label class="mdl-textfield__label" for="messages_input">' + window.lang.messages_new_message_input + '</label>';
		html += '		</div>';
		
		html += '		<button id="messages_send_btn" class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored">';
		html += '			<i class="material-icons">send</i>';
		html += '		</button>';
		
		html += '		<button onclick="Messages.showEmojis(); return false;" id="messages_emoji_btn" class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab">';
		html += '			<i class="material-icons">mood</i>';
		html += '		</button>';
		
		html += '	</form>';
		html += '</div>';
		return html;
	},
	
	formatMessageList: function(json_data) {
		var html = '',
			mess,
			part,
			img_contact,
			img_mms,
			i;
			
		for (i in json_data.messages) {
			mess = json_data.messages[i];
			html += '<div class="messages_list_item emoji_like type_' + mess.type + '">';
			// Type 1 : received
			img_contact = 'user='+getCookie('user')+'&token='+getCookie('token')+'&key='+getCookie('key')+'&format_address='+Messages.last_format_address+'&device_id='+Messages.last_device_id;
			
			if (Messages.contact_img_list[Messages.last_format_address] === undefined || mess.type != '1') {
				html += '	<div class="messages_list_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
			} else {
				html += '   <div class="messages_contact_item_img"><img src="/api/Contacts/getContactImg?'+img_contact+'" /></div>';
			}
			html += '	<div class="messages_list_item_infos">';
			for (part in mess.parts) {
    			img_mms = 'user='+getCookie('user')+'&token='+getCookie('token')+'&key='+getCookie('key')+'&device_id='+Messages.last_device_id+'&message_id='+mess.message_id+'&part_nb='+mess.parts[part];
			    html += '<div class="messages_list_item_mms">';
			    html += '   <img src="/api/Messages/getPart?'+img_mms+'" />';
                html += '   <a href="/api/Messages/getPart?'+img_mms+'" title="'+window.lang.messages_download+'" download="my_mms.png">';
                html += '       <button class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-button--colored">';
                html += '           <i class="material-icons">file_download</i>';
                html += '       </button>';
                html += '   </a>';
			    
			    html += '</div>';
			}
			html += '		<div class="messages_list_item_body">' + mess.body + '</div>';
			html += '		<div class="messages_list_item_date">';
			html +=         	timeToDate(mess.time);
			if (mess.type == '-1') {
				html += 		' - ' + window.lang.messages_sent;	
			} else if (mess.type == '-2') {
				html += 		' - ' + window.lang.messages_todo_send;	
			}
			html += '		</div>';
			html += '	</div>';
			html += '</div>';
		}
		return html;
	},
	
	sendNewMessage: function() {
		var opt,
			message,
			input_mess,
			input_dev,
			input_pho;
		
		input_mess = document.getElementById('messages_new_input_message');
		input_dev = document.getElementById('messages_new_input_device');
		input_pho = document.getElementById('messages_new_input_address');
		if (input_mess.value === '' || input_mess.value === null ||
			input_dev.value === '' || input_dev.value === null ||
			input_pho.value === '' || input_pho.value === null) {
			return;
		}
		message = {
			'id': (new Date()).getTime(),
			'date': (new Date()).getTime(),
			'read': 1,
			'body': encodeURI(input_mess.value),
			'address': encodeURI(input_pho.value),
			'type': -2,
			'date_sent': (new Date()).getTime()
		};
		opt = {
			'url': '/Api/Messages/Sync',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'address': input_pho.value,
				'device_id': input_dev.value,
				'messages': JSON.stringify([message])
			},
			'callback': Messages.sendMessageRes,
			'checkErrors': false,
			'decode': false
		};
		Ajax.post(opt);
		input_mess.value = null;
		input_dev.value = null;
		input_pho.value = null;
		document.getElementById('messages_new_box').style.display = 'none';
	},
	
	sendMessage: function(address, device_id) {
		var opt,
			message,
			input;
		
		input = document.getElementById('messages_input');
		if (input.value === '' || input.value === null) {
			return;
		}
		message = {
			'id': (new Date()).getTime(),
			'date': (new Date()).getTime(),
			'read': 1,
			'body': encodeURI(input.value),
			'address': encodeURI(address),
			'type': -2,
			'date_sent': (new Date()).getTime()
		};
		opt = {
			'url': '/Api/Messages/Sync',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key'),
				'address': address,
				'device_id': device_id,
				'messages': JSON.stringify([message])
			},
			'callback': Messages.sendMessageRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.post(opt);
		input.value = '';
	},
	
	sendMessageRes: function(data) {
		var json_data;
		
		json_data = JSON.parse(data);
		var snackbarContainer = document.querySelector('#messages_confirm_send');
		if (json_data.error === 0) {
			snackbarContainer.MaterialSnackbar.showSnackbar({message: window.lang.messages_send});
		} else {
			snackbarContainer.MaterialSnackbar.showSnackbar({message: window.lang.messages_send_error});
		}
	},
	
	showEmojis: function() {
		var dialog = document.getElementById('emoji_list');
		dialog.style.display = 'block';
	    dialog.querySelector('.close').addEventListener('click', function() {
	    	dialog.style.display = 'none';
	    });
	},
	
	showNewMessages: function() {
		var dialog = document.getElementById('messages_new_box');
		dialog.style.display = 'block';
	    dialog.querySelector('.close').addEventListener('click', function() {
	    	dialog.style.display = 'none';
	    });
	    Messages.getAllContacts();
	},
	
	getAllContacts: function() {
	    var opt;
		
		opt = {
			'url': '/Api/Contacts/GetContacts',
			'data': {
				'user': getCookie('user'),
				'token': getCookie('token'),
				'key': getCookie('key')
			},
			'callback': Messages.getAllContactsRes,
			'checkErrors': false,
			'decode': false
		};
		
		Ajax.get(opt);
	},
	
	getAllContactsRes: function(data) {
	    var json_data,
	        html = '',
	        i;
		
		json_data = JSON.parse(data);
		
		if (json_data.error === 0) {
		    json_data.address.sort(compareByName);
    		for (i in json_data.address) {
    			addr = json_data.address[i];
    			img_params = 'user='+getCookie('user')+'&token='+getCookie('token')+'&key='+getCookie('key')+'&format_address='+addr.format_address+'&device_id='+addr.device_id;
    			html += '<div class="messages_contact_item" onclick="document.getElementById(\'messages_new_input_address\').value = \'' + (addr.address).replace(/[^0-9/+]+/g, '') + '\'; ">';
    			
    			if (addr.have_img) {
    			    html += '   <div class="messages_contact_item_img"><img src="/api/Contacts/getContactImg?'+img_params+'" /></div>';
    			} else {
    				html += '	<div class="messages_contact_item_img"><i class="material-icons mdl-list__item-avatar">person</i></div>';
    			}
    
    			html += '	<div class="messages_contact_item_infos">';
    			html += '		<div class="messages_contact_item_name">' + addr.name + '</div>';
    			html += '		<div class="messages_contact_item_subname">' + addr.model + ' - ' + addr.address + '</div>';
    			html += '	</div>';
    			html += '</div>';
    		}	
    		document.getElementById('messages_new_contacts').innerHTML = html;
		}
	},
	
	addEmoji:function(span) {
		document.getElementById('messages_input').value += span.innerHTML;
		componentHandler.upgradeDom();
	},
	
	/* Switch view, for small screen */
	switchView: function(toMessage) {
		var contacts = document.getElementById('messages_contacts_bloc'),
			messages = document.getElementById('messages_messages_bloc'),
			width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		if (width >= 960) {
			return;
		}
		if (toMessage) {
			contacts.style.display = 'none';
			messages.style.display = 'flex';
			if (document.getElementById("messages_list_items") !== null) {
				document.getElementById("messages_list_items").scrollTop = document.getElementById("messages_list_items").scrollHeight;
			}
		} else {
			contacts.style.display = 'flex';
			messages.style.display = 'none';
		}
	},
	
	resizeSoResetSwitch: function() {
		var contacts = document.getElementById('messages_contacts_bloc'),
			messages = document.getElementById('messages_messages_bloc'),
			width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

		if (width >= 960) {
			contacts.style.display = 'flex';
			messages.style.display = 'flex';
		} else {
// 			contacts.style.display = 'flex';
// 			messages.style.display = 'none';
		}
	}
	
};
var Messages = new MessagesClass();

window.nb_script_to_load--;