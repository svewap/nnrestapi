<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Access
 * 
 * Checks if current fe_user has rights to call the desired endpoint.
 * As a non-logged-in user, only methods that are marked as `@Api\Access("public")` in the 
 * the annotations as `@Api\Access("public")`.
 * 
 * The following permissions exist for `@Api\Access(...)`:
 * 
 * | ---------------------------------------|-------------------------------------------------------|
 * | annotation							    | permissions: Callable from...							|
 * | ---------------------------------------|-------------------------------------------------------|
 * | @Api\Access("*")					    | anyone, without authentication (same as public)	 	|
 * | @Api\Access("public")				    | anyone, without authentication						|
 * | @Api\Access("fe_users")			    | every logged in frontend user							|
 * | @Api\Access("fe_users[1]")			    | only logged in frontend user with uid 1			  	|
 * | @Api\Access("fe_users[david]")		    | Only logged in frontend user with username `david`	|
 * | @Api\Access("be_users")			    | every logged in backend user						 	|
 * | @Api\Access("be_admins")			    | every logged in backend admin							|
 * | @Api\Access("fe_groups[1,2]")		    | fe_user-group with uid 1 and 2						|
 * | @Api\Access("fe_groups[api]")		    | fe_user-group 'api'								  	|
 * | @Api\Access("ip[89.19.*,89.20.*]")     | Limit to certain IPs (ADDITIONALLY to fe_user etc.)   |
 * | @Api\Access("ipUsers[89.19.*,89.*]")   | Allow certain IPs (ALTERNATIVELY to fe_user etc.)		|
 * | @Api\Access("config[myconf]")		    | use Yaml config for the site/API						|
 * | @Api\Access({"...", "..."})		    | You can add muliple users using this syntax		  	|
 * | ---------------------------------------|-------------------------------------------------------|
 *
 * @Annotation
 */
class Access
{
	public $value;

	public function __construct( $arr ) {
		$value = is_array( $arr['value'] ) ? $arr['value'] : [$arr['value']];
		$this->value = \nn\rest::Access()->parse( $value );
	}

	public function mergeDataForEndpoint( &$data ) {
		$data['access'] = $this->value;
	}
}