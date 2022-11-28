import $ from 'jquery';

var $testbed = $('.testbed');
var $endpoints = $('.endpoints');

var urlBase = $testbed.data().urlBase;
var requestTypesWithBody = ['post', 'put', 'patch'];

var filters = {};
	
/**
 * Filterform
 * 
 */
$('#hide-nnrestapi').change(function () {
	var hide = $(this).prop('checked');
	$('body').toggleClass('hide-nnrestapi', hide );
	filters.hideNnrestapi = hide;
	saveInStorage('filters', filters);
});

$('.search').keyup(function () {
	var sword = $(this).val();
	filters.sword = sword;
	saveInStorage('filters', filters);
	$('.search-icon').toggle( sword.length == 0 );
	$('.clear-icon').toggle( sword.length > 0 );

	$('.card').each(function () {
		var $el = $(this);
		$el.toggle( $el.text().indexOf(sword) > -1 );
	});
});

$('.clear-icon').click(() => {
	$('.search').val('').keyup();
});

// Restore last filter values from localStorage
getFromStorage( 'filters' ).then(( prevFilters ) => {
	filters = prevFilters;
	$('#hide-nnrestapi').prop('checked', filters.hideNnrestapi).change();
	$('.search').val(filters.sword || '').keyup();
});

// Restore last form values from localStorage
restoreLastFormData();

/**
 * Kickstarts
 * 
 */
$('.kickstarts-config input').change(function () {
	var obj = {};
	$('.kickstarts-config input').each(function () {
		var $me = $(this);
		obj[$me.data().field] = $me.val() || $me.data().default;
	});
	$('.kickstarts .item a').each(function() {
		var $me = $(this);
		if (!$me.data('a')) {
			$me.data('a', $me.attr('href'));
		}
		var href = $me.data().a;
		for (var i in obj) {
			href = href.replace( `=${i}`, `=${obj[i]}` );
		}
		$me.attr('href', href);
	});
	saveInStorage('kickstarts', obj);
});

/**
 * Open Kickstarts README.md
 * 
 */
$('.kickstart-packages .readme').click(function () {
	var $body = $('#nnrestapi-modal .modal-body');
	$.ajax($(this).attr('href')).done(function ( data ) {
		$('[data-bs-target="#nnrestapi-modal"]').click();
		$body.html( data );
		if (window.Prism) {
			Prism.highlightAll();
		}
	});
	return false;
});

getFromStorage( 'kickstarts' ).then(( prevConfig ) => {
	if (!prevConfig) {
		prevConfig = {};
	}
	$('.kickstarts-config [data-field]').each(function () {
		var $me = $(this);
		$me.val( prevConfig[$me.data().field] || '' );
	});
	$('.kickstarts-config input').first().change();
});

/**
 * Authenticate
 * 
 */	
$('.login-btn').click(() => {
	var url = $('[data-auth-url]').data().authUrl;
	var username = $('.auth-username').val();
	var password = $('.auth-password').val();

	$('.reqtoken, .reqcookie').val('');

	sendRequest( 'post', url, {username, password} ).then(( data ) => {
		saveInStorage('fe_typo_user_jwt', data.token || '').then(() => {
			updateCredentialsFromStorage().then( updateFeUserStatus );
		});
	});
});

/**
 * Logout
 * 
 */	
$('.logout-btn').click(() => {
	var url = $('[data-logout-url]').data().logoutUrl;
	sendRequest( 'get', url ).then(( data ) => {
		saveInStorage('fe_typo_user_jwt', '').then(() => {
			updateCredentialsFromStorage().then( updateFeUserStatus );
		});
	});	
});

/**
 * Update User Status
 * 
 */
function updateFeUserStatus() {
	$testbed.removeClass('feuser-loaded');
	var url = $('[data-user-url]').data().userUrl;
	sendRequest( 'get', url, null, true ).then(( data ) => {
		console.log( data );
		$testbed.addClass('feuser-loaded');
		$('.logout .username').text( data.username );
		$testbed.toggleClass('feuser-exists', data.uid > 0);
	});	
}

/**
 * Testformular: 
 * Request an API senden
 * 
 */
$testbed.find('.request-form button').click(() => {

	var uid 	= $('.requid').val();
	var reqType = $('.reqtype').val();
	var url 	= $('.requrl').val();
	var body 	= $('.reqbody').val();

	saveInStorage(uid, {url:'', type:'', body:body});
	
	if (url.indexOf('http') == -1) url = `${urlBase}${url}`;
	sendRequest( reqType, url, body );
	
	return false;
});

$testbed.find('.reqtype').change(function () {
	var classes = 'req-type-get req-type-post req-type-put req-type-patch req-type-path req-type-delete';
	var reqType = $(this).val();
	$testbed.removeClass(classes);
	$testbed.addClass('req-type-' + reqType);
}).change();


/**
 * 
 */
function updateCredentialsFromStorage() {
	return new Promise((resolve, reject) => {
		var cookieName = 'fe_typo_user';
		var cookie = document.cookie.match('(^|;)\\s*' + cookieName + '\\s*=\\s*([^;]+)')?.pop() || '';
		getFromStorage('fe_typo_user_jwt').then((token) => {
			$('.reqtoken').val( token );
			$('.reqcookie').val( cookie );	
			resolve();
		});	
	});
}


/**
 * Request senden.
 * 
 */
function sendRequest( reqType, url, body, silent = false ) {

	saveCurrentFormData();

	return new Promise((resolve, reject) => {

		var token	= $('.reqtoken').val();
		var cookie	= $('.reqcookie').val();
		var basicAuth = $.trim($('.reqbasicauth').val()).split(':');

		$testbed.addClass('loading');

		var config 	= {
			headers: {
				'Content-Type': 'multipart/form-data'
			},
			onUploadProgress: function (e) {
				var percentCompleted = Math.round((e.loaded * 100) / e.total);
				$('.progress-bar').css({width: `${percentCompleted}%`});
			}
		};
	
		if (token) {
			config.headers.Authorization = `Bearer ${token}`; 
		}
		if (cookie) {
			document.cookie = `fe_typo_user=${cookie}; Path=/`;
		}
		if (basicAuth.length > 1) {
			config.auth = {
				username: basicAuth[0],
				password: basicAuth[1]
			};
		}

		$('[data-request-header]').each(function () {
			var val = $(this).val();
			if (val || $(this).filter('[data-add-header-if-empty]').length ) {
				config.headers[$(this).data().requestHeader] = val;
			}
		});

		axios.defaults.withCredentials = true;
		axios.defaults.headers.common = config.headers;

		var formData = new FormData();
		var imagefile = $('.reqfiles')[0];
		var fileprefix = $('.reqfilekey').val();
		
		formData.append('json', JSON.stringify(body));

		var uploadImages = [];

		for (var i in imagefile.files) {

			var file = imagefile.files[i];
			var fileName = file.name;

			var fileIdentifier = `${fileprefix}-${i}`;
			formData.append( fileIdentifier, file);

		}
		
		body = formData;

		var params = requestTypesWithBody.indexOf(reqType) > -1 ? [url, body, config] : [url, config];

		axios[reqType]( ...params )
			.then((response) => {

				var showHtml = response.status >= 500 || (typeof response.data == 'string' && response.data.trim().substr(0, 1) == '<');
				$('.rescode span').text( response.status );
				$testbed.toggleClass('show-exception', showHtml);

				if (!silent) {
					showResult( response.data );
				}
				resolve( response.data );	
			})
			.catch(({response}) => {

				var showHtml = response.status >= 500 || (typeof response.data == 'string' && response.data.trim().substr(0, 1) == '<');
				$testbed.toggleClass('show-exception', showHtml);

				$('.rescode span').text( `[${response.status}] ${response.statusText}` );

				if (!silent) {
					showResult( response.data );
				}
				resolve( response.data );
			}).finally(() => {
				$testbed.removeClass('loading');
			});
			
	});

}

$endpoints.find('.compose').click(function (e) {
	var reqData = $(this).closest('[data-reqtype]').data();
	var uid = reqData.uid;
	getFromStorage( uid ).then(( prevReq ) => {
		$('.requid').val( reqData.uid );
		$('.reqtype').val( prevReq.type || reqData.reqtype ).change();
		$('.requrl').val( prevReq.url || reqData.requrl );	
		var body = prevReq.body || (reqData.example ? JSON.stringify(reqData.example) : '');
		$('.reqbody').val( body );	
	});
	
	$('.request-form, .compose').removeClass('pump');
	setTimeout(() => {
		$('.request-form').add($(this)).addClass('pump');
	}, 10);

	return false;
});


function showResult( json ) {
	$('.exception').html( json );
	if (typeof json != 'string') {
			json = JSON.stringify(json, undefined, 2);
	}
	$('.resbody').text( json );
	Prism.highlightElement( $('.resbody')[0] );
}

/**
 * Save all form settings and data for current request.
 * 
 * @returns Promise
 */
function saveCurrentFormData() {
	var data = [];
	$('.testbed').find('input, select, textarea').each(function () {
		data.push({
			selector: '[class="' + $(this).attr('class') + '"]',
			value: $(this).val()
		});
	});
	return saveInStorage('lastRequest', data);
}

/**
 * Restore all values from last request.
 * 
 * @returns Promise
 */
function restoreLastFormData() {
	getFromStorage( 'lastRequest' ).then(( data ) => {
		for (var i in data) {
			var obj = data[i];
			var $el = $(obj.selector);
			if ($el.length == 1) {
				$el.val( obj.value );
				$el.change();
			}
		}
	});
}

/**
 * Daten aus der localStorage laden.
 * 
 * @returns Promise 
 */
function getFromStorage( key = '' ) {
	return new Promise(( resolve, reject ) => {
		var result = (JSON.parse(localStorage.getItem( key )) || {_:{}})._;
		resolve( result );
	});
}

/**
 * Daten in der localStorage speichern.
 * 
 * @returns Promise 
 */
function saveInStorage( key = '', val = '' ) {
	return new Promise(( resolve, reject ) => {
		localStorage.setItem( key, JSON.stringify({_:val}) );
		resolve();
	});
}

/**
 * Init
 * 
 */
updateCredentialsFromStorage().then(() => {
	updateFeUserStatus();
});

if ($.fn.tooltip) {
	$('[data-toggle="tooltip"]').tooltip();
}