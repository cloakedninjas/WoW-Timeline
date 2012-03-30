var Funcs = {
	cache: {
		method: 'js'
	},
	temp: {},

	init: function() {
		if (typeof(localStorage) != 'undefined') {
			this.cache.method = 'local';
		}
		else {
			this.cache.index = [];
			this.cache.data = [];
		}
	},

	setCookie: function (c_name,value,exdays) {
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	},

	getCookie: function (c_name) {
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++) {
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x=x.replace(/^\s+|\s+$/g,"");
			if (x==c_name) {
				return unescape(y);
			}
		}
	},

	bindRealmLookup: function() {
		$("#realm_name").keyup(function() {
			if ($("#realm_name").val().length >= 3 && $("#realm_name").val() != Funcs.temp.last_realm) {
				$("#realm_name_loading").show();
				Funcs.lookupRealm($("#realm_name").val());
				Funcs.temp.last_realm = $("#realm_name").val();
			}
			else if ($("#realm_name").val().length == 0) {
				$("#realm_suggest").hide().empty();
				$("#realm_name_loading").hide();
			}
		});
	},

	lookupRealm: function(prefix) {
		var cache_index = 'realm_lookup_' + prefix;
		var cache_result = this.getCache(cache_index);

		if (cache_result == null) {
			var lookup_result = ['Foo', 'Bar'];
		}
		else {
			var lookup_result = ['Foo', 'Bar'];
		}

		this.updateRealmList(lookup_result);

	},

	updateRealmList: function(rlist) {
		$("#realm_suggest").show().empty();

		for(i = 0; i < rlist.length; i++) {
			$("#realm_suggest").append('<li>' + rlist[i] + '</li>');
		}
		$("#realm_name_loading").hide();
	},

	getCache: function(name) {
		if (this.cache.method == 'local') {
			return localStorage.getItem("name");
		}
		else {
			var i = this.cache.index.indexOf(name);

			if (i != -1) {
				return this.cache.data[i];
			}
		}
	},

	setCache: function(name, data, method) {
		if (typeof method == 'undefined') {
			method = this.cache.method;
		}

		if (method == 'local') {
			try {
				localStorage.setItem(name, data);
			}
			catch (e) {
			}
		}
		else {
			this.cache.index.push(name);
			this.cache.index.data(data);
		}
	}

};