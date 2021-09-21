

define(['jquery', 'TYPO3/CMS/Nnrestapi/Axios'], function($, axios) {

	var $testbed = $('.testbed');
	var $endpoints = $('.endpoints');

	var urlBase = $testbed.data().urlBase;
	var requestTypesWithBody = ['post', 'put', 'patch'];

	/**
	 * Authenticate
	 * 
	 */	
	$('.login-form button').click(() => {
		var url = $('[data-auth-url]').data().authUrl;
		var username = $('.auth-username').val();
		var password = $('.auth-password').val();

		$('.reqtoken, .reqcookie').val('');

		sendRequest( 'post', url, {username, password} ).then(( data ) => {
			saveInStorage('fe_typo_user_jwt', data.token || '').then(() => {
				updateCredentialsFromStorage();
			});
		});
	});

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
		var reqType = $(this).val();
		$(this).attr({class:'reqtype'});
		$(this).addClass('reqtype-' + reqType);
	}).change();


	/**
	 * 
	 */
	function updateCredentialsFromStorage() {
		var cookieName = 'fe_typo_user';
		var cookie = document.cookie.match('(^|;)\\s*' + cookieName + '\\s*=\\s*([^;]+)')?.pop() || '';
		getFromStorage('fe_typo_user_jwt').then((token) => {
			$('.reqtoken').val( token );
			$('.reqcookie').val( cookie );	
		});
	}


	/**
	 * Request senden.
	 * 
	 */
	function sendRequest( reqType, url, body ) {

		return new Promise((resolve, reject) => {

			var token	= $('.reqtoken').val();
			var cookie	= $('.reqcookie').val();

			document.cookie = `fe_typo_user=${cookie}; Path=/`;
	
			var config 	= {
				headers: {
					'Content-Type': 'multipart/form-data',
					Authorization: `Bearer ${token}`,
				},
				onUploadProgress: function (e) {
					var percentCompleted = Math.round((e.loaded * 100) / e.total);
					console.log(percentCompleted);
				}
			};
	 
			axios.defaults.withCredentials = true;
			axios.defaults.headers.common = config.headers;
//*---	

			var formData = new FormData();
			var imagefile = $('.reqfiles')[0];
			var uploadImages = [];

			for (var i in imagefile.files) {

				var file = imagefile.files[i];
				var fileName = file.name;

				var fileIdentifier = `file-${i}`;
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

					showResult( response.data );
					resolve( response.data );
				})
				.catch(({response}) => {

					var showHtml = response.status >= 500 || (typeof response.data == 'string' && response.data.trim().substr(0, 1) == '<');
					$testbed.toggleClass('show-exception', showHtml);
	
					$('.rescode span').text( `[${response.status}] ${response.statusText}` );
	
					showResult(  response.data );
					resolve( response.data );
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

	updateCredentialsFromStorage();


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
});