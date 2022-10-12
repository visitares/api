<?php

namespace Visitares\Router;

use Visitares\Components\Http\Routing\Router;

/**
 * Router configuration
 *
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
$router = new Router;


/**
 * Debug
 */
if(APP_DEBUG){
	$router['GET /debug/{token:string}'] = [
		'ctrl' => 'Visitares\API\DebugController :: get'
	];
	$router['POST /debug/upload'] = [
		'ctrl' => 'Visitares\API\DebugController :: upload'
	];
	$router['POST /debug/{token:string}'] = [
		'ctrl' => 'Visitares\API\DebugController :: post'
	];
}

/**
 * Test
 */
$router['GET /test/email'] = [
	'ctrl' => 'Visitares\API\TestController :: testEmail',
];
$router['GET /test/send-mail-queue'] = [
	'ctrl' => 'Visitares\API\TestController :: testEmailQueue',
];

/**
 * Base data
 */
$router['GET /bootstrap'] = [
	'ctrl' => 'Visitares\API\Bootstrap\BootstrapController :: initialize'
];

/**
 * Jobs
 */
$router['POST /jobs/recalc-maxscores'] = [
	'ctrl' => 'Visitares\API\JobsController :: recalcMaxScores'
];
$router['POST /jobs/import-images'] = [
	'ctrl' => 'Visitares\API\JobsController :: importImages'
];
$router['POST /jobs/create-usercache'] = [
	'ctrl' => 'Visitares\API\JobsController :: createUserCache'
];
$router['POST /jobs/create-groupcache'] = [
	'ctrl' => 'Visitares\API\JobsController :: createGroupCache'
];

/**
 * Download Proxy
 */
$router['GET /download/{filename:*}'] = [
	'ctrl' => 'Visitares\API\DownloadProxyController :: download'
];

/**
 * Authentication
 */
$router['OPTIONS /{path:*}'] = [
	'ctrl' => 'Visitares\API\CORSController :: validate'
];
$router['POST /login'] = [
	'ctrl' => 'Visitares\API\LoginController :: login'
];
$router['POST /login/anonymous'] = [
	'ctrl' => 'Visitares\API\LoginController :: loginAnonymous'
];
$router['POST /logout'] = [
	'ctrl' => 'Visitares\API\LoginController :: logout'
];
$router['POST /register/{domain:alnum}'] = [
	'ctrl' => 'Visitares\API\LoginController :: register'
];
$router['POST /forgot-password'] = [
	'ctrl' => 'Visitares\API\LoginController :: forgotPassword'
];
$router['POST /change-password'] = [
	'ctrl' => 'Visitares\API\LoginController :: changePassword'
];


/**
 * System Config
 */
$router['GET /config/{name:alnum}'] = [
	'ctrl' => 'Visitares\API\ConfigController :: get'
];
$router['POST /config/{name:alnum}'] = [
	'ctrl' => 'Visitares\API\ConfigController :: store'
];


/**
 * Instances
 */
$router['GET /instances'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getAll'
];
$router['GET /instances/templates'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getTemplates'
];
$router['GET /instances/{id:int}'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getById'
];
$router['GET /instances/{id:int}/userscount'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getUsersCount'
];
$router['GET /instances/{domain:alnum}'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getByDomain'
];
$router['POST /instances/list'] = [
	'ctrl' => 'Visitares\API\Lists\InstancesListController :: get'
];
$router['GET /instances/reg/{token:alnum}'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getByRegToken'
];
$router['GET /instances/{token:alnum}/languages'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getLanguages'
];
$router['GET /instances/{token:alnum}/metagroups'] = [
	'ctrl' => 'Visitares\API\MetaGroupController :: getByInstanceToken'
];
$router['POST /instances'] = [
	'ctrl' => 'Visitares\API\InstanceController :: store'
];
$router['POST /instances/{id:int}'] = [
	'ctrl' => 'Visitares\API\InstanceController :: update'
];
$router['POST /instances/{id:int}/settings'] = [
	'ctrl' => 'Visitares\API\InstanceController :: updateSettings'
];
$router['DELETE /instances/many'] = [
	'ctrl' => 'Visitares\API\InstanceController :: removeMany'
];
$router['DELETE /instances/{id:int}'] = [
	'ctrl' => 'Visitares\API\InstanceController :: remove'
];

$router['GET /instances/{token:alnum}/logo'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getLogoImage',
	'format' => 'custom'
];
$router['GET /instances/{token:alnum}/background'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getBackgroundImage',
	'format' => 'custom'
];

/**
 * Languages
 */
$router['GET /instances/{token:alnum}/languages'] = [
	'ctrl' => 'Visitares\API\LanguageController :: getAll'
];
$router['GET /instances/{token:alnum}/languages/{id:int}'] = [
	'ctrl' => 'Visitares\API\LanguageController :: getById'
];
$router['POST /instances/{token:alnum}/languages'] = [
	'ctrl' => 'Visitares\API\LanguageController :: store'
];
$router['POST /instances/{token:alnum}/languages/{id:int}'] = [
	'ctrl' => 'Visitares\API\LanguageController :: update'
];
$router['DELETE /instances/{token:alnum}/languages/{id:int}'] = [
	'ctrl' => 'Visitares\API\LanguageController :: remove'
];


/**
 * Dirty Words
 */
$router['GET /instances/{token:alnum}/dirtywords'] = [
	'ctrl' => 'Visitares\API\DirtyWordController :: getAll'
];
$router['GET /instances/{token:alnum}/dirtywords/{id:int}'] = [
	'ctrl' => 'Visitares\API\DirtyWordController :: getById'
];
$router['POST /instances/{token:alnum}/dirtywords'] = [
	'ctrl' => 'Visitares\API\DirtyWordController :: store'
];
$router['POST /instances/{token:alnum}/dirtywords/{id:int}'] = [
	'ctrl' => 'Visitares\API\DirtyWordController :: update'
];
$router['DELETE /instances/{token:alnum}/dirtywords/{id:int}'] = [
	'ctrl' => 'Visitares\API\DirtyWordController :: remove'
];


/**
 * Units
 */
$router['GET /instances/{token:alnum}/units'] = [
	'ctrl' => 'Visitares\API\UnitController :: getAll'
];
$router['GET /instances/{token:alnum}/units/{id:int}'] = [
	'ctrl' => 'Visitares\API\UnitController :: getById'
];
$router['POST /instances/{token:alnum}/units'] = [
	'ctrl' => 'Visitares\API\UnitController :: store'
];
$router['POST /instances/{token:alnum}/units/{id:int}'] = [
	'ctrl' => 'Visitares\API\UnitController :: update'
];
$router['DELETE /instances/{token:alnum}/units/{id:int}'] = [
	'ctrl' => 'Visitares\API\UnitController :: remove'
];



/**
 * Lists
 */
$router['GET /instances/{domain:alnum}/list/{name:alnum}'] = [
	'ctrl' => 'Visitares\API\DataListController :: getList'
];
$router['POST /instances/{domain:alnum}/list/{name:alnum}'] = [
	'ctrl' => 'Visitares\API\DataListController :: store'
];
$router['POST /instances/{domain:alnum}/list/{name:alnum}/{id:int}'] = [
	'ctrl' => 'Visitares\API\DataListController :: update'
];
$router['DELETE /instances/{domain:alnum}/list/{name:alnum}/{id:int}'] = [
	'ctrl' => 'Visitares\API\DataListController :: remove'
];



/**
 * Clients
 */
$router['GET /instances/{token:alnum}/clients'] = [
	'ctrl' => 'Visitares\API\ClientController :: getAll'
];
$router['GET /instances/{token:alnum}/clients/{id:int}'] = [
	'ctrl' => 'Visitares\API\ClientController :: getById'
];
$router['POST /instances/{token:alnum}/clients/list'] = [
	'ctrl' => 'Visitares\API\Lists\ClientsListController :: get'
];
$router['POST /instances/{token:alnum}/clients'] = [
	'ctrl' => 'Visitares\API\ClientController :: store'
];
$router['POST /instances/{token:alnum}/clients/{id:int}'] = [
	'ctrl' => 'Visitares\API\ClientController :: update'
];
$router['DELETE /instances/{token:alnum}/clients/many'] = [
	'ctrl' => 'Visitares\API\ClientController :: removeMany'
];
$router['DELETE /instances/{token:alnum}/clients/{id:int}'] = [
	'ctrl' => 'Visitares\API\ClientController :: remove'
];


/**
 * Groups
 */
$router['GET /instances/{token:alnum}/groups'] = [
	'ctrl' => 'Visitares\API\GroupController :: getAll'
];
$router['GET /instances/{token:alnum}/groups/{id:int}'] = [
	'ctrl' => 'Visitares\API\GroupController :: getById'
];
$router['POST /instances/{token:alnum}/groups/list'] = [
	'ctrl' => 'Visitares\API\Lists\GroupsListController :: get'
];
$router['POST /instances/{token:alnum}/groups'] = [
	'ctrl' => 'Visitares\API\GroupController :: store'
];
$router['POST /instances/{token:alnum}/groups/{id:int}'] = [
	'ctrl' => 'Visitares\API\GroupController :: update'
];
$router['DELETE /instances/{token:alnum}/groups/many'] = [
	'ctrl' => 'Visitares\API\GroupController :: removeMany'
];
$router['DELETE /instances/{token:alnum}/groups/{id:int}'] = [
	'ctrl' => 'Visitares\API\GroupController :: remove'
];


/**
 * Users
 */
$router['GET /instances/{token:alnum}/users'] = [
	'ctrl' => 'Visitares\API\UserController :: getAll'
];
$router['POST /instances/{token:alnum}/users/list'] = [
	'ctrl' => 'Visitares\API\Lists\UsersListController :: get'
];
$router['GET /instances/{token:alnum}/anonymous-users'] = [
	'ctrl' => 'Visitares\API\UserController :: getAllAnonymous'
];
$router['GET /instances/{token:alnum}/users/{id:int}'] = [
	'ctrl' => 'Visitares\API\UserController :: getById'
];
$router['GET /instances/{token:alnum}/users/{username:alnum}'] = [
	'ctrl' => 'Visitares\API\UserController :: getByUsername'
];
$router['POST /instances/{token:alnum}/users'] = [
	'ctrl' => 'Visitares\API\UserController :: store'
];
$router['POST /instances/{token:alnum}/users/{username:username}'] = [
	'ctrl' => 'Visitares\API\UserController :: update'
];
$router['POST /instances/{token:alnum}/users/{username:alnum}/reset-password'] = [
	'ctrl' => 'Visitares\API\UserController :: resetPassword'
];
$router['DELETE /instances/{token:alnum}/users/many'] = [
	'ctrl' => 'Visitares\API\UserController :: removeMany'
];
$router['DELETE /instances/{token:alnum}/users/{username:alnum}'] = [
	'ctrl' => 'Visitares\API\UserController :: remove'
];
$router['POST /instances/{domain:alnum}/recover-password'] = [
	'ctrl' => 'Visitares\API\UserController :: recoverPassword'
];
$router['POST /instances/{token:alnum}/users/{id:int}/upload-photo'] = [
	'ctrl' => 'Visitares\API\UserController :: uploadPhoto'
];
$router['POST /instances/{token:alnum}/users/{id:int}/settings'] = [
	'ctrl' => 'Visitares\API\UserController :: updateSettings'
];

// submit instances
$router['GET /instances/{token:alnum}/users/{userId:int}/submit-instances'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: getAll'
];
$router['GET /instances/{token:alnum}/users/{userId:int}/submit-instances/by-category/{categoryId:int}'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: getAllByCategory'
];
$router['GET /instances/{token:alnum}/users/{userId:int}/submit-instances/{id:int}/submits'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: getSubmits'
];
$router['GET /instances/{token:alnum}/users/{userId:int}/submit-instances/{id:int}'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: getById'
];
$router['POST /instances/{token:alnum}/users/{userId:int}/submit-instances'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: store'
];
$router['POST /instances/{token:alnum}/users/{userId:int}/submit-instances/{id:int}'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: update'
];
$router['DELETE /instances/{token:alnum}/users/{userId:int}/submit-instances/{id:int}'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: remove'
];
$router['DELETE /instances/{token:alnum}/submit-instances/many'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: removeMany'
];
$router['GET /instances/{token:alnum}/users/{userId:int}/submit-instances/{id:int}/export'] = [
	'ctrl' => 'Visitares\API\UserSubmitInstancesController :: export'
];

$router['POST /instances/{token:alnum}/submit-instances/list'] = [
	'ctrl' => 'Visitares\API\Lists\SubmitInstancesListController :: get'
];


/**
 * Categories
 */
$router['GET /instances/{token:alnum}/categories'] = [
	'ctrl' => 'Visitares\API\CategoryController :: getAll'
];
$router['POST /instances/{token:alnum}/categories/list'] = [
	'ctrl' => 'Visitares\API\Lists\CategoriesListController :: get'
];
$router['GET /instances/{token:alnum}/categories/{id:int}'] = [
	'ctrl' => 'Visitares\API\CategoryController :: getById'
];
$router['POST /instances/{token:alnum}/categories'] = [
	'ctrl' => 'Visitares\API\CategoryController :: store'
];
$router['POST /instances/{token:alnum}/categories/{id:int}'] = [
	'ctrl' => 'Visitares\API\CategoryController :: update'
];
$router['DELETE /instances/{token:alnum}/categories/{id:int}'] = [
	'ctrl' => 'Visitares\API\CategoryController :: remove'
];
$router['DELETE /instances/{token:alnum}/categories/many'] = [
	'ctrl' => 'Visitares\API\CategoryController :: removeMany'
];

/**
 * Category-Processes
 */
$router['POST /instances/{token:alnum}/processes/query'] = [
	'ctrl' => 'Visitares\API\CategoryProcessController :: query'
];
$router['GET /instances/{token:alnum}/categories/{categoryId:int}/processes/{processId:alnum}/submits'] = [
	'ctrl' => 'Visitares\API\CategoryProcessController :: getSubmits'
];
$router['GET /instances/{token:alnum}/categories/{categoryId:int}/processes/{processId:alnum}/pdf'] = [
	'ctrl' => 'Visitares\API\CategoryProcessController :: exportAsPdf'
];
$router['POST /instances/{token:alnum}/categories/{categoryId:int}/processes'] = [
	'ctrl' => 'Visitares\API\CategoryProcessController :: store'
];
$router['POST /instances/{token:alnum}/categories/{categoryId:int}/processes/{processId:alnum}'] = [
	'ctrl' => 'Visitares\API\CategoryProcessController :: update'
];
$router['DELETE /instances/{token:alnum}/categories/{categoryId:int}/processes/{processId:alnum}'] = [
	'ctrl' => 'Visitares\API\CategoryProcessController :: remove'
];


/**
 * Forms
 */
$router['GET /instances/{token:alnum}/forms'] = [
	'ctrl' => 'Visitares\API\FormController :: getAll'
];
$router['POST /instances/{token:alnum}/forms/list'] = [
	'ctrl' => 'Visitares\API\Lists\FormsListController :: get'
];
$router['POST /instances/{token:alnum}/forms/search'] = [
	'ctrl' => 'Visitares\API\FormSearchController :: search'
];
$router['GET /instances/{token:alnum}/forms/{id:int}'] = [
	'ctrl' => 'Visitares\API\FormController :: getById'
];
$router['GET /instances/{token:alnum}/forms/{id:int}/stats/{language:string}'] = [
	'ctrl' => 'Visitares\API\FormController :: getStats'
];
$router['POST /instances/{token:alnum}/forms'] = [
	'ctrl' => 'Visitares\API\FormController :: store'
];
$router['POST /instances/{token:alnum}/forms/{id:int}'] = [
	'ctrl' => 'Visitares\API\FormController :: update'
];
$router['POST /instances/{token:alnum}/forms/{id:int}/submit'] = [
	'ctrl' => 'Visitares\API\FormController :: submit'
];
$router['DELETE /instances/{token:alnum}/forms/{id:int}'] = [
	'ctrl' => 'Visitares\API\FormController :: remove'
];
$router['DELETE /instances/{token:alnum}/forms/many'] = [
	'ctrl' => 'Visitares\API\FormController :: removeMany'
];
$router['POST /instances/{token:alnum}/forms/{id:int}/share'] = [
	'ctrl' => 'Visitares\API\FormController :: share'
];
$router['POST /instances/{token:alnum}/forms/{id:int}/copy'] = [
	'ctrl' => 'Visitares\API\FormController :: copy'
];


/**
 * Form Documents
 */
$router['GET /instances/{token:alnum}/forms/{fid:int}/documents/{did:int}/{filename:*}'] = [
	'ctrl' => 'Visitares\API\FormDocumentsController :: download'
];
$router['POST /instances/{token:alnum}/forms/{fid:int}/documents'] = [
	'ctrl' => 'Visitares\API\FormDocumentsController :: store'
];
$router['DELETE /instances/{token:alnum}/forms/{fid:int}/documents/{did:int}'] = [
	'ctrl' => 'Visitares\API\FormDocumentsController :: remove'
];


/**
 * Catalogs
 */
// catalogs
$router['POST /instances/{token:alnum}/catalogs/list'] = [
	'ctrl' => 'Visitares\API\Lists\CatalogsListController :: get'
];
$router['GET /instances/{token:alnum}/catalogs/{id:int}'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: getCatalogById'
];
$router['POST /instances/{token:alnum}/catalogs'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: saveCatalog'
];
$router['POST /instances/{token:alnum}/catalogs/delete/{id:int}'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: removeCatalog'
];
$router['POST /instances/{token:alnum}/catalogs/delete/many'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: removeManyCatalogs'
];
$router['POST /instances/{token:alnum}/catalogs/test'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: test'
];

$router['POST /instances/catalogs/csv-import/preview'] = [
	'ctrl' => 'Visitares\API\CatalogsCsvImportController :: loadPreview',
	'input' => 'json-form-data',
];
$router['POST /instances/{token:alnum}/catalogs/csv-import'] = [
	'ctrl' => 'Visitares\API\CatalogsCsvImportController :: import',
	'input' => 'json-form-data',
];

// entries
$router['POST /instances/{token:alnum}/catalogs/entries/list'] = [
	'ctrl' => 'Visitares\API\Lists\CatalogsEntriesListController :: get'
];
$router['GET /instances/{token:alnum}/catalogs/entries/{id:int}'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: getCatalogEntryById'
];
$router['POST /instances/{token:alnum}/catalogs/entries'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: saveCatalogEntry'
];
$router['POST /instances/{token:alnum}/catalogs/entries/delete/{id:int}'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: removeCatalogEntry'
];
$router['POST /instances/{token:alnum}/catalogs/entries/delete/many'] = [
	'ctrl' => 'Visitares\API\CatalogsController :: removeManyCatalogEntries'
];

/**
 * Messages
 */
$router['GET /instances/{token:alnum}/users/{id:int}/messages'] = [
	'ctrl' => 'Visitares\API\MessageController :: getByUser'
];
$router['GET /instances/{token:alnum}/users/{id:int}/messages/unread'] = [
	'ctrl' => 'Visitares\API\MessageController :: unread'
];
$router['GET /instances/{token:alnum}/users/{id:int}/messages/{submitId:int}'] = [
	'ctrl' => 'Visitares\API\MessageController :: getBySubmit'
];
$router['POST /instances/{token:alnum}/users/{id:int}/messages/{submitId:int}'] = [
	'ctrl' => 'Visitares\API\MessageController :: reply'
];
$router['POST /instances/{token:alnum}/users/{userId:int}/messages/read/{unreadId:int}'] = [
	'ctrl' => 'Visitares\API\MessageController :: read'
];
$router['POST /instances/{token:alnum}/messages/{id:int}/published'] = [
	'ctrl' => 'Visitares\API\MessageController :: setMessagePublished'
];


/**
 * Statistics
 */
$router['GET /instances/{token:alnum}/statistics/form'] = [
	'ctrl' => 'Visitares\API\Statistics\StatisticsFormController :: getForm'
];
$router['POST /instances/{token:alnum}/statistics'] = [
	'ctrl' => 'Visitares\API\Statistics\StatisticsController :: getStatistics'
];

$router['POST /instances/{token:alnum}/reports'] = [
	'ctrl' => 'Visitares\API\Statistics\ReportsController :: getReports'
];
$router['POST /instances/{token:alnum}/reports/form/{id:int}'] = [
	'ctrl' => 'Visitares\API\Statistics\ReportsController :: getFormReport'
];
$router['POST /instances/{token:alnum}/reports/form/{id:int}/trend'] = [
	'ctrl' => 'Visitares\API\Statistics\ReportsController :: getFormTrend'
];

$router['POST /instances/{token:alnum}/reports/usersubmitinstances/export'] = [
	'ctrl' => 'Visitares\API\Statistics\UserSubmitInstance\Export :: go'
];


/**
 * Contextual
 */
$router['GET /instances/{token:alnum}/users/{id:int}/instances'] = [
	'ctrl' => 'Visitares\API\InstanceController :: getByUser'
];
$router['GET /instances/{token:alnum}/users/{id:int}/categories'] = [
	'ctrl' => 'Visitares\API\CategoryController :: getByUser'
];
$router['GET /instances/{token:alnum}/categories/{id:int}/forms'] = [
	'ctrl' => 'Visitares\API\FormController :: getByCategory'
];
$router['POST /instances/{token:alnum}/categories/{id:int}/forms'] = [
	'ctrl' => 'Visitares\API\FormController :: getByCategory'
];
$router['GET /instances/{token:alnum}/attachments/{id:int}/{filename:*}'] = [
	'ctrl' => 'Visitares\API\MessageController :: getAttachmentData'
];


/**
 * CSV
 */
$router['POST /instances/{token:alnum}/csv'] = [
	'ctrl' => 'Visitares\API\CsvController :: create'
];
$router['GET /instances/{token:alnum}/csv/{id:alnum}'] = [
	'ctrl' => 'Visitares\API\CsvController :: download'
];


/**
 * Images API
 */
$router['GET /images'] = [
	'ctrl' => 'Visitares\API\Images\ImagesController :: getAll'
];
$router['GET /images/{id:int}'] = [
	'ctrl' => 'Visitares\API\Images\ImagesController :: get'
];
$router['POST /images'] = [
	'ctrl' => 'Visitares\API\Images\ImagesController :: store'
];
$router['POST /images/{id:int}'] = [
	'ctrl' => 'Visitares\API\Images\ImagesController :: update'
];
$router['DELETE /images/{id:int}'] = [
	'ctrl' => 'Visitares\API\Images\ImagesController :: remove'
];

$router['GET /images/groups'] = [
	'ctrl' => 'Visitares\API\Images\ImageGroupsController :: getAll'
];
$router['GET /images/groups/instances/{token:alnum}'] = [
	'ctrl' => 'Visitares\API\Images\ImageGroupsController :: getByInstance'
];
$router['GET /images/groups/{id:int}'] = [
	'ctrl' => 'Visitares\API\Images\ImageGroupsController :: get'
];
$router['GET /images/groups/{id:int}/images'] = [
	'ctrl' => 'Visitares\API\Images\ImagesController :: getByGroup'
];
$router['POST /images/groups'] = [
	'ctrl' => 'Visitares\API\Images\ImageGroupsController :: store'
];
$router['POST /images/groups/{id:int}'] = [
	'ctrl' => 'Visitares\API\Images\ImageGroupsController :: update'
];
$router['DELETE /images/groups/{id:int}'] = [
	'ctrl' => 'Visitares\API\Images\ImageGroupsController :: remove'
];

/**
 * Media API
 */
$router['POST /instances/{token:alnum}/media/query'] = [
	'ctrl' => 'Visitares\API\Media\MediaController :: query'
];
$router['POST /instances/{token:alnum}/media'] = [
	'ctrl' => 'Visitares\API\Media\MediaController :: store'
];
$router['POST /instances/{token:alnum}/media/{id:int}'] = [
	'ctrl' => 'Visitares\API\Media\MediaController :: update'
];
$router['DELETE /instances/{token:alnum}/media/{id:int}'] = [
	'ctrl' => 'Visitares\API\Media\MediaController :: remove'
];

$router['POST /instances/{token:alnum}/mediagroup/query'] = [
	'ctrl' => 'Visitares\API\Media\MediaGroupController :: query'
];
$router['POST /instances/{token:alnum}/mediagroup'] = [
	'ctrl' => 'Visitares\API\Media\MediaGroupController :: store'
];
$router['POST /instances/{token:alnum}/mediagroup/{id:int}'] = [
	'ctrl' => 'Visitares\API\Media\MediaGroupController :: update'
];
$router['DELETE /instances/{token:alnum}/mediagroup/{id:int}'] = [
	'ctrl' => 'Visitares\API\Media\MediaGroupController :: remove'
];


/** ---------------------------------------------------------------------------------------------------- */



/**
 * Master API
 */
$router['GET /masters/{id:int}'] = ['ctrl' => 'Visitares\API\MasterController :: getById'];
$router['POST /masters/list'] = ['ctrl' => 'Visitares\API\Lists\MasterListController :: get'];
$router['POST /masters'] = ['ctrl' => 'Visitares\API\MasterController :: store'];
$router['POST /masters/{id:int}'] = ['ctrl' => 'Visitares\API\MasterController :: update'];
$router['DELETE /masters/{id:int}'] = ['ctrl' => 'Visitares\API\MasterController :: remove'];
$router['DELETE /masters/many'] = ['ctrl' => 'Visitares\API\MasterController :: removeMany'];

/**
 * Master-Media API
 */
$router['GET /masters/{mid:int}/media/{id:int}'] = ['ctrl' => 'Visitares\API\MasterMediaController :: getById'];
$router['POST /masters/{mid:int}/media/list'] = ['ctrl' => 'Visitares\API\Lists\MasterMediaListController :: get'];
$router['POST /masters/{mid:int}/media'] = ['ctrl' => 'Visitares\API\MasterMediaController :: store'];
$router['POST /masters/{mid:int}/media/{id:int}'] = ['ctrl' => 'Visitares\API\MasterMediaController :: update'];
$router['POST /masters/media/import'] = ['ctrl' => 'Visitares\API\MasterMediaController :: import'];
$router['DELETE /masters/{mid:int}/media/{id:int}'] = ['ctrl' => 'Visitares\API\MasterMediaController :: remove'];
$router['DELETE /masters/{mid:int}/media/many'] = ['ctrl' => 'Visitares\API\MasterMediaController :: removeMany'];

/**
 * Master-MediaGroup API
 */
$router['GET /masters/{mid:int}/mediagroup/{id:int}'] = ['ctrl' => 'Visitares\API\MasterMediaGroupController :: getById'];
$router['POST /masters/{mid:int}/mediagroup/list'] = ['ctrl' => 'Visitares\API\Lists\MasterMediaGroupListController :: get'];
$router['POST /masters/{mid:int}/mediagroup'] = ['ctrl' => 'Visitares\API\MasterMediaGroupController :: store'];
$router['POST /masters/{mid:int}/mediagroup/{id:int}'] = ['ctrl' => 'Visitares\API\MasterMediaGroupController :: update'];
$router['DELETE /masters/{mid:int}/mediagroup/{id:int}'] = ['ctrl' => 'Visitares\API\MasterMediaGroupController :: remove'];
$router['DELETE /masters/{mid:int}/mediagroup/many'] = ['ctrl' => 'Visitares\API\MasterMediaGroupController :: removeMany'];

/**
 * MetaGroup API
 */
$router['GET /metagroups/{id:int}'] = ['ctrl' => 'Visitares\API\MetaGroupController :: getById'];
$router['POST /metagroups/list'] = ['ctrl' => 'Visitares\API\Lists\MetaGroupListController :: get'];
$router['POST /metagroups'] = ['ctrl' => 'Visitares\API\MetaGroupController :: store'];
$router['POST /metagroups/{id:int}'] = ['ctrl' => 'Visitares\API\MetaGroupController :: update'];
$router['DELETE /metagroups/{id:int}'] = ['ctrl' => 'Visitares\API\MetaGroupController :: remove'];
$router['DELETE /metagroups/many'] = ['ctrl' => 'Visitares\API\MetaGroupController :: removeMany'];

/**
 * Timeline API
 */
$router['GET /timelines/{id:int}'] = ['ctrl' => 'Visitares\API\TimelineController :: getById'];
$router['POST /timelines/list'] = ['ctrl' => 'Visitares\API\Lists\TimelineListController :: get'];
$router['POST /timelines'] = ['ctrl' => 'Visitares\API\TimelineController :: store'];
$router['POST /timelines/{id:int}'] = ['ctrl' => 'Visitares\API\TimelineController :: update'];
$router['DELETE /timelines/{id:int}'] = ['ctrl' => 'Visitares\API\TimelineController :: remove'];
$router['DELETE /timelines/many'] = ['ctrl' => 'Visitares\API\TimelineController :: removeMany'];

/**
 * Post API
 */
$router['GET /posts/{id:int}'] = ['ctrl' => 'Visitares\API\PostController :: getById'];
$router['POST /posts/list'] = ['ctrl' => 'Visitares\API\Lists\PostListController :: get'];
$router['POST /posts'] = ['ctrl' => 'Visitares\API\PostController :: store'];
$router['POST /posts/{id:int}'] = ['ctrl' => 'Visitares\API\PostController :: update'];
$router['DELETE /posts/{id:int}'] = ['ctrl' => 'Visitares\API\PostController :: remove'];
$router['DELETE /posts/many'] = ['ctrl' => 'Visitares\API\PostController :: removeMany'];

/**
 * PostMedia API
 */
$router['GET /posts/{id:int}/media'] = ['ctrl' => 'Visitares\API\PostMediaController :: getAll'];
$router['POST /posts/{id:int}/media'] = ['ctrl' => 'Visitares\API\PostMediaController :: upload'];
$router['POST /posts/{id:int}/media/{mid:int}/publish/{token:string}'] = ['ctrl' => 'Visitares\API\PostMediaController :: publish'];
$router['DELETE /posts/{id:int}/media/{mid:int}'] = ['ctrl' => 'Visitares\API\PostMediaController :: remove'];

/**
 * Comment API
 */
$router['GET /posts/{pid:int}/comments'] = ['ctrl' => 'Visitares\API\PostCommentsController :: getByPost'];
$router['POST /posts/{pid:int}/comments/list'] = ['ctrl' => 'Visitares\API\Lists\PostCommentsListController :: get'];
$router['POST /posts/{pid:int}/comments'] = ['ctrl' => 'Visitares\API\PostCommentsController :: store'];
$router['POST /posts/{pid:int}/comments/{cid:int}'] = ['ctrl' => 'Visitares\API\PostCommentsController :: update'];
$router['DELETE /posts/{pid:int}/comments/{cid:int}'] = ['ctrl' => 'Visitares\API\PostCommentsController :: remove'];

/**
 * Likes API
 */
$router['POST /posts/{pid:int}/likes'] = ['ctrl' => 'Visitares\API\PostLikesController :: like'];
$router['DELETE /posts/{pid:int}/likes'] = ['ctrl' => 'Visitares\API\PostLikesController :: unlike'];

// Return the router object
return $router;
