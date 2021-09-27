

define(['jquery', 'TYPO3/CMS/Nnrestapi/Axios'], function($, axios) {

	var $testbed = $('.testbed');
	var $endpoints = $('.endpoints');

	var urlBase = $testbed.data().urlBase;
	var requestTypesWithBody = ['post', 'put', 'patch'];

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
		var classes = 'req-type-get req-type-post req-type-put req-type-path req-type-delete';
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

		return new Promise((resolve, reject) => {

			var token	= $('.reqtoken').val();
			var cookie	= $('.reqcookie').val();

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
			
			axios.defaults.withCredentials = true;
			axios.defaults.headers.common = config.headers;
//*---	

			var formData = new FormData();
			var imagefile = $('.reqfiles')[0];
			var fileprefix = $('.reqfilekey').val();
			
			var uploadImages = [];

			for (var i in imagefile.files) {

				var file = imagefile.files[i];
				var fileName = file.name;

				var fileIdentifier = `${fileprefix}-${i}`;
				formData.append( fileIdentifier , file);
/*
				if (typeof file == 'object') {
					var reader = new FileReader();
					reader.onload = function() {
						uploadImages.push({
							fileName: fileName,
							fileData: reader.result
						});
						saveInStorage( 'images', uploadImages );
					}
					reader.readAsDataURL(file);
				}
*/
			}
			formData.append('json', JSON.stringify(body));
			body = formData;

//---*/
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
	 * Daten aus der localStorage laden.
	 * 
	 * @returns array 
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
	 * @returns array 
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

});