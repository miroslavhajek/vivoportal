<?php
/**
 * Main Vivo config, this config can not be overwritten by sites, and modules.
 */
return array(
    'router' => array(
        'routes' => array(
            //routes for frontend
            'vivo' => array(
                //only add hostname to routematch
                'type' => 'Vivo\Router\Hostname',
                'may_terminate' => false,
                'child_routes' => array(
                    //route for frontend documents
                    //@example http://<sitehost>/<path>/
                    'cms' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'options' => array(
                            'regex'    => '(?<path>/.*)',
                            'spec'    => '%path%',
                            'defaults' => array(
                                'controller' => 'cms_front_controller',
                                'path' => '',
                            ),
                        ),
                        'may_terminate' => true,
                    ),
                    //route for frontend resources
                    //@example http://<sitehost>/.<moduleName>.<resourceType>/<path>
                    'resource' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'options' => array(
                            'regex'    => '/\.(?<source>.+?)\.(?<type>.+?)/(?<path>.+)',
                            'spec'    => '/.%source%.%type%/%path%',
                            'defaults' => array(
                                'controller' => 'resource_front_controller',
                                'type' => '',
                                'path' => '',
                                'source' => '',
                            ),
                        ),
                    ),
                    //route for entity resources
                    //@example http://<sitehost>/.entity/<entityPath>/.path/<resourcePath>
                    'resource_entity' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'options' => array(
                            'regex'    => '/\.entity(?<entity>.+?)((\.path/(?<path>.+)))',
                            'spec'    => '/.entity%entity%.path/%path%',
                            'defaults' => array(
                                'controller' => 'resource_front_controller',
                                'path' => '',
                                'source' => 'entity',
                            ),
                        ),
                    ),
                ),
            ),

            //routes configuration for backend
            'backend' => array(
                'type' => 'Vivo\Backend\Hostname',
                'may_terminate' => false,
                'options' => array (
                    'hosts' => array (
                    ),
                ),
                'child_routes' => array(
                    //route for everything else on backend hostname - controller redirects
                    'other' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'may_terminate' => true,
                        'options' => array(
                            'regex'    => '.*',
                            'spec'    => '/',
                            'defaults' => array(
                                'controller' => 'backend_controller',
                            ),
                        ),
                    ),
                    //default backend route
                    //@example http://<backendhost>/
                    'default' => array (
                        'type' => 'Literal',
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/',
                            'defaults' => array(
                                'controller' => 'backend_controller',
                            ),
                        ),
                    ),
                    //route for backend modules
                    //@example http://<backendhost>/<sitehost>/<moduleName>/
                    'modules' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'may_terminate' => true,
                        'options' => array(
                            'regex'    => '/(?<host>[^/]+)/(?<module>[^/]+)/(?<path>[^/]*)',
                            'spec'    => '/%host%/%module%/%path%',
                            'defaults' => array(
                                'controller' => 'backend_controller',
                                'path'   => '',
                                'module' => 'explorer',
                                'host' => '',
                            ),
                        ),
                    ),
                    //route for backend explorer module
                    //@example http://<backendhost>/<sitehost>/explorer/<path>/<explorerAction>
                    'explorer' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'may_terminate' => true,
                        'options' => array(
                            'regex'    => '/(?<host>[^/]+)/explorer/(?<path>[^/]+)/(?<explorerAction>[^/]*)',
                            'spec'    => '/%host%/%module%/%path%/%explorerAction%',
                            'defaults' => array(
                                'controller' => 'backend_controller',
                                'path'   => '',
                                'host' => '',
                                'module' => 'explorer',
                                'explorerAction' => '',
                            ),
                        ),
                    ),
                    //Route for backend Vivo resources
                    //@example http://<backendhost>/.<moduleName>.<resourceType>/<path>
                    'backend_resource' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'options' => array(
                            'regex'    => '/\.(?<source>.+?)\.(?<type>.+?)/(?<path>.+)',
                            'spec'    => '/.%source%.%type%/%path%',
                            'defaults' => array(
                                'controller' => 'resource_front_controller',
                                'type' => '',
                                'path' => '',
                                'source' => '',
                            ),
                        ),
                    ),

                    //Routes for front-end view from back-end

                    //route for viewing site in backend (iframe)
                    //@example http://<backendhost>/<sitehost>/view/<pathWithinSite>
                    'cms' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'may_terminate' => true,
                        'options' => array(
                            'regex'    => '/(?<host>.*)/view(?<path>/.*)?',
                            'spec'    => '/%host%/view%path%',
                            'defaults' => array(
                                'controller' => 'cms_front_controller',
                                'path'   => '',
                            ),
                        ),
                    ),
                    //route for Vivo and vmodule resources in backend view
                    //@example http://<backendhost>/<sitehost>/view/.<moduleName>.<resourceType>/<path>
                    'resource' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'options' => array(
                            'regex'    => '/(?<host>.+)/view/\.(?<source>.+?)\.(?<type>.+?)/(?<path>.+)',
                            'spec'    => '/%host%/view/.%source%.%type%/%path%',
                            'defaults' => array(
                                'controller' => 'resource_front_controller',
                                'type' => '',
                                'path' => '',
                                'source' => '',
                            ),
                        ),
                    ),
                    //Route for entity resources in backend view
                    'resource_entity' => array(
                        'type' => 'Zend\Mvc\Router\Http\Regex',
                        'options' => array(
                            'regex'    => '/(?<host>.+)/view/\.entity(?<entity>.+?)((\.path/(?<path>.+)))',
                            'spec'    => '/%host%/view/.entity%entity%.path/%path%',
                            'defaults' => array(
                                'controller' => 'resource_front_controller',
                                'path' => '',
                                'source' => 'entity',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    //Resource URL helper options
    'resource_url_helper'       => array(
        //Useful for debugging sites
        'check_resource'        => false,
        //Path where Vivo resources are found
        'vivo_resource_path'    => realpath(__DIR__ . '/../resource'),
        //This maps current request route name to an appropriate route name for Vivo and VModule resources
        'resource_route_map'    => array(
            'vivo/cms'                  => 'vivo/resource',
            'vivo/resource'             => 'vivo/resource',
            'vivo/resource_entity'      => 'vivo/resource',
            'backend/other'             => 'backend/backend_resource',
            'backend/default'           => 'backend/backend_resource',
            'backend/modules'           => 'backend/backend_resource',
            'backend/explorer'          => 'backend/resource',
            'backend/backend_resource'  => 'backend/backend_resource',
            'backend/cms'               => 'backend/resource',
            'backend/resource'          => 'backend/resource',
            'backend/resource_entity'   => 'backend/resource',
        ),
        //This maps current request route name to an appropriate route name for entity resources
        'entity_resource_route_map' => array(
            'vivo/cms'                  => 'vivo/resource_entity',
            'vivo/resource'             => 'vivo/resource_entity',
            'vivo/resource_entity'      => 'vivo/resource_entity',
            'backend/other'             => 'backend/resource_entity',
            'backend/default'           => 'backend/resource_entity',
            'backend/modules'           => 'backend/resource_entity',
            'backend/explorer'          => 'backend/resource_entity',
            'backend/backend_resource'  => 'backend/resource_entity',
            'backend/cms'               => 'backend/resource_entity',
            'backend/resource'          => 'backend/resource_entity',
            'backend/resource_entity'   => 'backend/resource_entity',
        ),
    ),
    'service_manager' => array(
        'allow_override' => true,
        'invokables'    => array(
            'uuid_generator'            => 'Vivo\Uuid\Generator',
            'storage_factory'           => 'Vivo\Storage\Factory',
            'site_event'                => 'Vivo\SiteManager\Event\SiteEvent',
            'cms_event'                 => 'Vivo\CMS\Event\CMSEvent',
            'io_util'                   => 'Vivo\IO\IOUtil',
            'indexer_query_builder'     => 'Vivo\Indexer\QueryBuilder',
            'indexer_document_builder'  => 'Vivo\Indexer\DocumentBuilder',
            'view_model'                => 'Zend\View\Model\ViewModel',
            'Vivo\Http\Filter\OutputFilterListener' => 'Vivo\Http\Filter\OutputFilterListener',
            'Vivo\Http\Filter\ImageTransform' => 'Vivo\Http\Filter\ImageTransform',
            'Vivo\watcher'              => 'Vivo\Repository\Watcher',
            'Vivo\component_event_listener' => 'Vivo\UI\ComponentEventListener',
            'Vivo\text'                 => 'Vivo\Text\Text',
        ),
        'factories' => array(
            'RoutePluginManager'        => 'Vivo\Service\RoutePluginManagerFactory',
            'translator'                => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'response'                  => 'Vivo\Service\ResponseFactory',
            //DB related services
            'db_service_manager'        => 'Vivo\Db\DbServiceManagerFactory',
            'db_provider_factory'       => 'Vivo\Db\DbProviderFactoryFactory',
            'db_provider_core'          => 'Vivo\Db\DbProviderCoreFactory',
            'db_table_name_provider'    => 'Vivo\Db\DbTableNameProviderFactory',
            'db_table_gateway_provider' => 'Vivo\Db\DbTableGatewayProviderFactory',
            'pdo_abstract_factory'      => 'Vivo\Db\PdoAbstractFactoryFactory',
            'zdb_abstract_factory'      => 'Vivo\Db\ZdbAbstractFactoryFactory',
            'module_db_provider'        => 'Vivo\Db\ModuleDbProviderFactory',

            'uuid_convertor'            => 'Vivo\Service\UuidConvertorFactory',
            'module_storage'            => 'Vivo\Service\ModuleStorageFactory',
            'remote_module'             => 'Vivo\Service\RemoteModuleFactory',
            'module_storage_manager'    => 'Vivo\Service\ModuleStorageManagerFactory',
            'module_manager_factory'    => 'Vivo\Service\ModuleManagerFactoryFactory',
            'site_manager'              => 'Vivo\SiteManager\SiteManagerFactory',
            'run_site_manager_listener' => 'Vivo\Service\RunSiteManagerListenerFactory',
            'lucene'                    => 'Vivo\Service\LuceneFactory',
            'storage_util'              => 'Vivo\Service\StorageUtilFactory',
            'indexer_adapter_lucene'    => 'Vivo\Service\IndexerAdapterLuceneFactory',
            'indexer'                   => 'Vivo\Indexer\IndexerFactory',
            'repository'                => 'Vivo\Repository\RepositoryFactory',
            'repository_events'         => 'Vivo\Repository\EventManagerFactory',
            'indexer_helper'            => 'Vivo\CMS\Indexer\IndexerHelperFactory',
            'Vivo\CMS\Api\Module'       => 'Vivo\CMS\Api\ModuleFactory',
            'Vivo\CMS\Api\CMS'          => 'Vivo\CMS\Api\CMSFactory',
            'Vivo\CMS\Api\Document'     => 'Vivo\CMS\Api\DocumentFactory',
            'Vivo\CMS\Api\Indexer'      => 'Vivo\CMS\Api\IndexerFactory',
            'Vivo\CMS\Api\Site'         => 'Vivo\CMS\Api\SiteFactory',
            'Vivo\CMS\Api\Util'         => 'Vivo\CMS\Api\UtilFactory',
            'Vivo\CMS\Api\Cache'        => 'Vivo\CMS\Api\CacheFactory',
            'Vivo\CMS\Api\Content\File'       => 'Vivo\CMS\Api\Content\FileFactory',
            'Vivo\CMS\Api\Content\Fileboard'  => 'Vivo\CMS\Api\Content\FileboardFactory',
            'Vivo\CMS\Api\Content\Gallery'    => 'Vivo\CMS\Api\Content\GalleryFactory',
            'module_resource_manager'   => 'Vivo\Module\ResourceManager\ResourceManagerFactory',
            'module_install_manager'    => 'Vivo\Service\ModuleInstallManagerFactory',
            'path_builder'              => 'Vivo\Service\PathBuilderFactory',
            'component_factory'         => 'Vivo\CMS\ComponentFactoryFactory',
            'indexer_adapter'           => 'Vivo\Service\IndexerAdapterFactory',
            'indexer_field_helper'      => 'Vivo\Service\IndexerFieldHelperFactory',
            'indexer_query_parser'      => 'Vivo\Service\IndexerQueryParserFactory',
            'module_name_resolver'      => 'Vivo\Service\ModuleNameResolverFactory',
            'metadata_manager'          => 'Vivo\Metadata\MetadataManagerFactory',
            'lookup_data_manager'       => 'Vivo\LookupData\LookupDataManagerFactory',
            'redirector'                => 'Vivo\Util\RedirectorFactory',
            'template_resolver'         => 'Vivo\View\Resolver\TemplateResolverFactory',
            'Vivo\CMS\Api\Manager\Manager' => 'Vivo\CMS\Api\Manager\ManagerFactory',
            'component_tree_controller' => 'Vivo\UI\ComponentTreeControllerFactory',
            'Vivo\CMS\AvailableContentsProvider' => 'Vivo\CMS\AvailableContentsProviderFactory',
            'Vivo\Metadata\Provider\SelectableTemplatesProvider' => 'Vivo\Metadata\Provider\SelectableTemplatesProviderFactory',
            'Vivo\Util\UrlHelper'       => 'Vivo\Util\UrlHelperFactory',
            'Vivo\document_url_helper'  => 'Vivo\CMS\Util\DocumentUrlHelperFactory',
            'Vivo\resource_url_helper'  => 'Vivo\CMS\Util\ResourceUrlHelperFactory',
            'Vivo\icon_url_helper'      => 'Vivo\CMS\Util\IconUrlHelperFactory',
            'Vivo\Http\HeaderHelper'    => 'Vivo\Http\HeaderHelperFactory',
            'Vivo\Transliterator\Path'  => 'Vivo\Transliterator\PathFactory',
            'Vivo\Transliterator\Url'   => 'Vivo\Transliterator\UrlFactory',
            'Vivo\Transliterator\DocTitleToPath'    => 'Vivo\Transliterator\DocTitleToPathFactory',
            'Vivo\Transliterator\MbStringCompare' => 'Vivo\Transliterator\MbStringCompareFactory',
            'sym_ref_convertor'         => 'Vivo\CMS\RefInt\SymRefConvertorFactory',
            'ref_int_listener'          => 'Vivo\CMS\RefInt\ListenerFactory',
            'mail_simple_renderer'      => 'Vivo\Mail\View\SimpleRendererFactory',
            'Vivo\input_filter_factory' => 'Vivo\InputFilter\InputFilterFactoryFactory',
            'Vivo\new_input_filter_factory' => 'Vivo\InputFilter\NewInputFilterFactoryFactory',
            'input_filter_conditions'   => 'Vivo\InputFilter\Condition\ConditionPluginManagerFactory',
            'Vivo\form_factory'         => 'Vivo\Form\FactoryFactory',
            'Vivo\new_form_factory'     => 'Vivo\Form\NewFormFactoryFactory',
            'cache_manager'             => 'Vivo\Cache\CacheManagerFactory',
            'session_manager'           => 'Vivo\Service\SessionManagerFactory',
            'mime'                      => 'Vivo\Util\MIMEFactory',
            'indexer_events'            => 'Vivo\Indexer\EventManagerFactory',
            'Vivo\nav_overview_defaults_processor' => 'Vivo\Service\EntityProcessor\NavAndOverviewDefaultsFactory',
            'Vivo\repository_storage'   => 'Vivo\Repository\RepositoryStorageFactory',
            'Vivo\component_creator'    => 'Vivo\UI\ComponentCreatorFactory',
            'Vivo\form_view_helper_utils'   => 'Vivo\Form\View\HelperUtilsFactory',
            'Vivo\apihelper_document_compare' => 'Vivo\CMS\Api\Helper\DocumentCompareFactory',
        ),
        'aliases' => array(
            'Vivo\site_event'                   => 'site_event',
            'Vivo\SiteManager\Event\SiteEvent'  => 'site_event',
            'Vivo\Repository\Repository'        => 'repository',
            'Zend\Http\Response'                => 'response',
            'Zend\Http\Request'                 => 'request',
            'Zend\View\HelperPluginManager'     => 'view_helper_manager',
            'Vivo\Util\Redirector'              => 'redirector',
            'Zend\View\Model\ViewModel'         => 'view_model',
            'Zend\Session\SessionManager'       => 'session_manager',
            'form_factory'                      => 'Vivo\form_factory',
            'input_filter_factory'              => 'Vivo\input_filter_factory',
            'Vivo\input_filter_conditions'      => 'input_filter_conditions',
            'Vivo\cache_manager'                => 'cache_manager',
            'Vivo\module_db_provider'           => 'module_db_provider',
            'Vivo\db_table_gateway_provider'    => 'db_table_gateway_provider',
        ),
        'shared' => array(
            'view_model'                    => false,
            'component_tree_controller'     => false,
            'Vivo\input_filter_factory'     => false,
        ),
        'initializers' => array(
            'component' => 'Vivo\Service\Initializer\ComponentInitializer',
            'default'   => 'Vivo\Service\Initializer\DefaultInitializer',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'cli_info'                  => 'Vivo\Controller\CLI\InfoController',
        ),
        'factories' => array(
            'cms_front_controller'      => 'Vivo\CMS\FrontControllerFactory',
            'resource_front_controller' => 'Vivo\Controller\ResourceFrontControllerFactory',
            'backend_controller'        => 'Vivo\Backend\BackendControllerFactory',
            'cli_module'                => 'Vivo\Controller\CLI\ModuleControllerFactory',
            'cli_cms'                   => 'Vivo\Controller\CLI\CmsControllerFactory',
            'cli_indexer'               => 'Vivo\Controller\CLI\IndexerControllerFactory',
            'cli_setup'                 => 'Vivo\Controller\CLI\SetupControllerFactory',
            'cli_util'                  => 'Vivo\Controller\CLI\UtilControllerFactory',
        ),
    ),
    'cms_front_controller' => array(
        'listeners' => array(
            // fetch
            'Vivo\CMS\Listener\FetchDocumentListener'       => 100,
            'Vivo\CMS\Listener\FetchDocumentByUrlListener'  => 200,
            'Vivo\CMS\Listener\FetchErrorDocumentListener'  => 300,
            // redirect
            'Vivo\CMS\Listener\RedirectMapListener' => 100,
            // create
            'Vivo\CMS\Listener\RssListener' => 500,
            'Vivo\CMS\Listener\ComponentTreeFromDocumentListener' => 100,
            // render
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => false,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'        => __DIR__ . '/../view/layout/layout.phtml',
            'vivo/index/index'     => __DIR__ . '/../view/vivo/index/index.phtml',
            'error/404'            => __DIR__ . '/../view/error/404.phtml',
            'error/index'          => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'action'                        => 'Vivo\View\Helper\Action',
            'action_link'                   => 'Vivo\View\Helper\ActionLink',
            'action_url'                    => 'Vivo\View\Helper\ActionUrl',
            'vivoform'                      => 'Vivo\View\Helper\VivoForm',
            'vivoformfieldset'              => 'Vivo\View\Helper\VivoFormFieldset',
            'container_component'           => 'Vivo\View\Helper\ContainerComponent',
            'overview_title'                => 'Vivo\View\Helper\OverviewTitle',
            'vivo_invalid_form_elements'    => 'Vivo\View\Helper\InvalidFormElements',
            'tae'                           => 'Vivo\View\Helper\Tae',
        ),
        'factories' => array(
            'url'               => 'Vivo\View\Helper\UrlFactory',
            'icon_url'          => 'Vivo\View\Helper\IconUrlFactory',
            'resource'          => 'Vivo\View\Helper\ResourceFactory',
            'resourceInfo'      => 'Vivo\View\Helper\ResourceInfoFactory',
            'document'          => 'Vivo\View\Helper\DocumentFactory',
            'cms'               => 'Vivo\View\Helper\CmsFactory',
            'vivo_head_title'   => 'Vivo\View\Helper\VivoHeadTitleFactory',
            'render_document'   => 'Vivo\View\Helper\RenderDocumentFactory',
            'user'              => 'Vivo\View\Helper\UserFactory',
            'transliterate'     => 'Vivo\View\Helper\TransliterateFactory',
        ),
    ),
    //Plugin manager configuration for navigation view helpers
    'navigation_view_helpers'   => array(
        'invokables'        => array(
            'vivo_menu'         => 'Vivo\View\Helper\Navigation\Menu',
            'vivo_site_map'     => 'Vivo\View\Helper\Navigation\SiteMap',
        ),
    ),
    //Filter plugin manager configuration
    'filters'       => array(
        'invokables'    => array(
            'Vivo\date_time'    => 'Vivo\Filter\DateTime',
        ),
        'factories'     => array(

        ),
        'initializers'  => array(

        ),
    ),
    //Validator plugin manager configuration
    'validators'    => array(
        'invokables' => array(
            'conditional'                   => 'Vivo\Validator\Conditional',
            'vivo_invalid'                  => 'Vivo\Validator\VivoInvalid',
            'vivo_personal_number_cz'       => 'Vivo\Validator\PersonalNumberCZ',
            'vivo_unique_entity_path'       => 'Vivo\CMS\Validator\UniqueEntityPath',
            'vivo_in_array_dynamic'         => 'Vivo\Validator\InArrayDynamic',
            'vivo_age'                      => 'Vivo\Validator\Age',
            'vivo_before_after'             => 'Vivo\Validator\BeforeAfter',
        ),
        'initializers'      => array(
            'validator_initializer'     => 'Vivo\Validator\Initializer',
        ),
        'aliases'           => array(
            'vivo_conditional'  => 'conditional',
        ),
    ),
    //Input filter conditions plugin manager config
    'input_filter_conditions'   => array(
        'invokables'    => array(
            'input'             => 'Vivo\InputFilter\Condition\Input',
            'notEmpty'          => 'Vivo\InputFilter\Condition\NotEmpty',
            'equals'            => 'Vivo\InputFilter\Condition\Equals',
            'inArray'           => 'Vivo\InputFilter\Condition\InArray',
            'allEmpty'          => 'Vivo\InputFilter\Condition\AllEmpty',
            'multiConditionAnd' => 'Vivo\InputFilter\Condition\MultiConditionAnd',
        ),
        'initializers'  => array(
            'condition_initializer' => 'Vivo\InputFilter\Condition\Initializer',
        ),
    ),
    'metadata_manager' => array(
        'config_path'       => __DIR__ . '/../config/metadata',
        'custom_properties' => array(),
    ),

    'transliterator'    => array(
        'path'              => array(
            'options'           => array(
                //Transliteration map
                'map'               => array(
                    //Cyrillic
                    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'ж' => 'zh',
                    'з' => 'z', 'и' =>'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
                    'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'c', 'ч' => 'ch',
                    'ш' => 'sh', 'щ' => 'shh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'eh', 'ю' => 'ju', 'я' => 'ja',
                    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'JO', 'Ж' => 'ZH',
                    'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
                    'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'C', 'Ч' => 'CH',
                    'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'EH', 'Ю' => 'JU', 'Я' => 'JA',
                    //Doubles
                    'ß' => 'ss', 'æ' => 'ae', 'Æ' => 'AE', 'œ' => 'oe', 'Œ' => 'OE',
                    //A
                    'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'ą' => 'a', 'à' => 'a', 'À' => 'A', 'â' => 'a', 'Â' => 'A',
                    'å' => 'a', 'Å' => 'A', 'ă' => 'a', 'Ă' => 'A',
                    //C
                    'č' => 'c', 'Č' => 'C', 'ć' => 'c', 'Ć' => 'C', 'ç' => 'c', 'Ç' => 'C',
                    //D
                    'ď' => 'd', 'Ď' => 'D', 'ð' => 'd', 'Ð' => 'D',
                    //E
                    'é' => 'e', 'É' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ę' => 'e', 'Ę' => 'E',
                    'è' => 'e', 'È' => 'E', 'ê' => 'e', 'Ê' => 'E',
                    //I
                    'í' => 'i', 'Í' => 'I', 'ï' => 'i', 'Ï' => 'I', 'î' => 'i', 'Î' => 'I',
                    //L
                    'ľ' => 'l', 'Ľ' => 'L', 'ĺ' => 'l', 'Ĺ' => 'L', 'ł' => 'l', '£' => 'L',
                    //N
                    'ň' => 'n', 'Ň' => 'N', 'ń' => 'n', 'Ń' => 'N', 'ñ' => 'n', 'Ñ' => 'N',
                    //O
                    'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O',
                    //R
                    'ř' => 'r', 'Ř' => 'R', 'ŕ' => 'r', 'Ŕ' => 'R',
                    //S
                    'š' => 's', 'Š' => 'S', 'ś' => 's', 'Ś' => 'S', 'ş' => 's', 'Ş' => 'S',
                    //T
                    'ť' => 't', 'Ť' => 'T', 'ţ' => 't', 'Ţ' => 'T',
                    //U
                    'ú' => 'u', 'Ú' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ü' => 'u', 'Ü' => 'U', 'ű' => 'u', 'Ű' => 'U',
                    'û' => 'u', 'Û' => 'U', 'ù' => 'u',
                    //Y
                    'ý' => 'y', 'Ý' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y',
                    //Z
                    'ž' => 'z', 'Ž' => 'Z', 'ź' => 'z', 'Ź' => 'Z', 'ż' => 'z', 'Ż' => 'Z',
                    //Symbols
                    '\\' => '/',
                ),
                //String with all allowed characters
                'allowedChars'      => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_/.',
                //Character used to replace illegal characters
                'replacementChar'   => '-',
                //Change case before processing
                'caseChangePre'     => \Vivo\Transliterator\Transliterator::CASE_CHANGE_NONE,
                //Change case after processing
                'caseChangePost'    => \Vivo\Transliterator\Transliterator::CASE_CHANGE_NONE,
            ),
        ),
        'url'               => array(
            'options'           => array(
                //Transliteration map
                'map'               => array(
                    //Cyrillic
                    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'ж' => 'zh',
                    'з' => 'z', 'и' =>'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
                    'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'c', 'ч' => 'ch',
                    'ш' => 'sh', 'щ' => 'shh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'eh', 'ю' => 'ju', 'я' => 'ja',
                    //Doubles
                    'ß' => 'ss', 'æ' => 'ae', 'œ' => 'oe',
                    //A
                    'á' => 'a', 'ä' => 'a', 'ą' => 'a', 'à' => 'a', 'â' => 'a', 'å' => 'a', 'ă' => 'a',
                    //C
                    'č' => 'c', 'ć' => 'c', 'ç' => 'c',
                    //D
                    'ď' => 'd', 'ð' => 'd',
                    //E
                    'é' => 'e', 'ě' => 'e', 'ë' => 'e', 'ę' => 'e', 'è' => 'e', 'ê' => 'e',
                    //I
                    'í' => 'i', 'ï' => 'i', 'î' => 'i',
                    //L
                    'ľ' => 'l', 'ĺ' => 'l', 'ł' => 'l',
                    //N
                    'ň' => 'n', 'ń' => 'n', 'ñ' => 'n',
                    //O
                    'ó' => 'o', 'ö' => 'o', 'ô' => 'o', 'ő' => 'o',
                    //R
                    'ř' => 'r', 'ŕ' => 'r',
                    //S
                    'š' => 's', 'ś' => 's', 'ş' => 's',
                    //T
                    'ť' => 't', 'ţ' => 't',
                    //U
                    'ú' => 'u', 'ů' => 'u', 'ü' => 'u', 'ű' => 'u', 'û' => 'u', 'ù' => 'u',
                    //Y
                    'ý' => 'y', 'ÿ' => 'y',
                    //Z
                    'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
                ),
                //String with all allowed characters
                'allowedChars'      => 'abcdefghijklmnopqrstuvwxyz-/',
                //Character used to replace illegal characters
                'replacementChar'   => '-',
                //Change case before processing
                'caseChangePre'     => \Vivo\Transliterator\Transliterator::CASE_CHANGE_TO_LOWER,
                //Change case after processing
                'caseChangePost'    => \Vivo\Transliterator\Transliterator::CASE_CHANGE_NONE,
            ),
        ),
        'doc_title_to_path' => array(
            'options'           => array(
                //Transliteration map
                'map'               => array(
                    //Cyrillic
                    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'ж' => 'zh',
                    'з' => 'z', 'и' =>'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
                    'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'c', 'ч' => 'ch',
                    'ш' => 'sh', 'щ' => 'shh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'eh', 'ю' => 'ju', 'я' => 'ja',
                    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'JO', 'Ж' => 'ZH',
                    'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
                    'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'C', 'Ч' => 'CH',
                    'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'EH', 'Ю' => 'JU', 'Я' => 'JA',
                    //Doubles
                    'ß' => 'ss', 'æ' => 'ae', 'Æ' => 'AE', 'œ' => 'oe', 'Œ' => 'OE',
                    //A
                    'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'ą' => 'a', 'à' => 'a', 'À' => 'A', 'â' => 'a', 'Â' => 'A',
                    'å' => 'a', 'Å' => 'A', 'ă' => 'a', 'Ă' => 'A',
                    //C
                    'č' => 'c', 'Č' => 'C', 'ć' => 'c', 'Ć' => 'C', 'ç' => 'c', 'Ç' => 'C',
                    //D
                    'ď' => 'd', 'Ď' => 'D', 'ð' => 'd', 'Ð' => 'D',
                    //E
                    'é' => 'e', 'É' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ę' => 'e', 'Ę' => 'E',
                    'è' => 'e', 'È' => 'E', 'ê' => 'e', 'Ê' => 'E',
                    //I
                    'í' => 'i', 'Í' => 'I', 'ï' => 'i', 'Ï' => 'I', 'î' => 'i', 'Î' => 'I',
                    //L
                    'ľ' => 'l', 'Ľ' => 'L', 'ĺ' => 'l', 'Ĺ' => 'L', 'ł' => 'l', '£' => 'L',
                    //N
                    'ň' => 'n', 'Ň' => 'N', 'ń' => 'n', 'Ń' => 'N', 'ñ' => 'n', 'Ñ' => 'N',
                    //O
                    'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O',
                    //R
                    'ř' => 'r', 'Ř' => 'R', 'ŕ' => 'r', 'Ŕ' => 'R',
                    //S
                    'š' => 's', 'Š' => 'S', 'ś' => 's', 'Ś' => 'S', 'ş' => 's', 'Ş' => 'S',
                    //T
                    'ť' => 't', 'Ť' => 'T', 'ţ' => 't', 'Ţ' => 'T',
                    //U
                    'ú' => 'u', 'Ú' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ü' => 'u', 'Ü' => 'U', 'ű' => 'u', 'Ű' => 'U',
                    'û' => 'u', 'Û' => 'U', 'ù' => 'u',
                    //Y
                    'ý' => 'y', 'Ý' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y',
                    //Z
                    'ž' => 'z', 'Ž' => 'Z', 'ź' => 'z', 'Ź' => 'Z', 'ż' => 'z', 'Ż' => 'Z',
                    //Symbols
                    '\\' => '/',
                ),
                //String with all allowed characters
                'allowedChars'      => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_',
                //Character used to replace illegal characters
                'replacementChar'   => '-',
                //Change case before processing
                'caseChangePre'     => \Vivo\Transliterator\Transliterator::CASE_CHANGE_TO_LOWER,
                //Change case after processing
                'caseChangePost'    => \Vivo\Transliterator\Transliterator::CASE_CHANGE_NONE,
            ),
        ),
        'mb_string_compare' => array(
            'options'           => array(
                //Transliteration map
                'map'               => array(
                    //Cyrillic
                    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'ж' => 'zh',
                    'з' => 'z', 'и' =>'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
                    'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'c', 'ч' => 'ch',
                    'ш' => 'sh', 'щ' => 'shh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'eh', 'ю' => 'ju', 'я' => 'ja',
                    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'JO', 'Ж' => 'ZH',
                    'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
                    'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'C', 'Ч' => 'CH',
                    'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'EH', 'Ю' => 'JU', 'Я' => 'JA',
                    //Doubles
                    'ß' => 'ss', 'æ' => 'ae', 'Æ' => 'AE', 'œ' => 'oe', 'Œ' => 'OE',
                    //A
                    'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'ą' => 'a', 'à' => 'a', 'À' => 'A', 'â' => 'a', 'Â' => 'A',
                    'å' => 'a', 'Å' => 'A', 'ă' => 'a', 'Ă' => 'A',
                    //C
                    'č' => 'c', 'Č' => 'C', 'ć' => 'c', 'Ć' => 'C', 'ç' => 'c', 'Ç' => 'C',
                    //D
                    'ď' => 'd', 'Ď' => 'D', 'ð' => 'd', 'Ð' => 'D',
                    //E
                    'é' => 'e', 'É' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ę' => 'e', 'Ę' => 'E',
                    'è' => 'e', 'È' => 'E', 'ê' => 'e', 'Ê' => 'E',
                    //I
                    'í' => 'i', 'Í' => 'I', 'ï' => 'i', 'Ï' => 'I', 'î' => 'i', 'Î' => 'I',
                    //L
                    'ľ' => 'l', 'Ľ' => 'L', 'ĺ' => 'l', 'Ĺ' => 'L', 'ł' => 'l', '£' => 'L',
                    //N
                    'ň' => 'n', 'Ň' => 'N', 'ń' => 'n', 'Ń' => 'N', 'ñ' => 'n', 'Ñ' => 'N',
                    //O
                    'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O',
                    //R
                    'ř' => 'r', 'Ř' => 'R', 'ŕ' => 'r', 'Ŕ' => 'R',
                    //S
                    'š' => 's', 'Š' => 'S', 'ś' => 's', 'Ś' => 'S', 'ş' => 's', 'Ş' => 'S',
                    //T
                    'ť' => 't', 'Ť' => 'T', 'ţ' => 't', 'Ţ' => 'T',
                    //U
                    'ú' => 'u', 'Ú' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ü' => 'u', 'Ü' => 'U', 'ű' => 'u', 'Ű' => 'U',
                    'û' => 'u', 'Û' => 'U', 'ù' => 'u',
                    //Y
                    'ý' => 'y', 'Ý' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y',
                    //Z
                    'ž' => 'z', 'Ž' => 'Z', 'ź' => 'z', 'Ź' => 'Z', 'ż' => 'z', 'Ż' => 'Z',
                ),
                //String with all allowed characters
                'allowedChars'      => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_!"$%&\'()*+,./:;<=>?@[\\]^{|}',
                //Character used to replace illegal characters
                'replacementChar'   => '-',
                //Change case before processing
                'caseChangePre'     => \Vivo\Transliterator\Transliterator::CASE_CHANGE_TO_LOWER,
                //Change case after processing
                'caseChangePost'    => \Vivo\Transliterator\Transliterator::CASE_CHANGE_NONE,
            ),
        ),
    ),

    'mime' => array(
        'types' => array(
            'text/html'                     => array('html', 'htm', 'php', 'phtml'),
            'text/xml'                      => array('xml'),
            'text/plain'                    => array('txt', 'text', 'c', 'c++', 'pl', 'cc', 'h', 'entity', 'messages'),
            'text/javascript'               => array('js'),
            'text/vbscript'                 => array('vb', 'vbs'),
            'text/sgml'                     => array('sgml'),
            'text/css'                      => array('css'),
            'text/x-smarty'                 => array('tpl'),
            'text/x-vcard'                  => array('vcf'),
            'text/x-speech'                 => array('talk'),
            'image/gif'                     => array('gif'),
            'image/png'                     => array('png'),
            'image/ief'                     => array('ief'),
            'image/jpeg'                    => array('jpg', 'jpeg', 'jpe'),
            'image/pjpeg'                   => array('jpg', 'jpeg', 'jpe'),
            'image/tiff'                    => array('tiff', 'tif'),
            'image/rgb'                     => array('rgb'),
            'image/g3fax'                   => array('g3f'),
            'image/x-png'                   => array('png'),
            'image/x-xbitmap'               => array('xbm'),
            'image/x-xpixmap'               => array('xpm'),
            'image/x-pict'                  => array('pict'),
            'image/x-portable-pixmap'       => array('ppm'),
            'image/x-portable-graymap'      => array('pgm'),
            'image/x-portable-bitmap'       => array('pbm'),
            'image/x-portable-anymap'       => array('pnm'),
            'image/x-ms-bmp'                => array('bmp'),
            'image/x-cmu-raster'            => array('ras'),
            'image/x-photo-cd'              => array('pcd'),
            'image/x-cals'                  => array('cal'),
            'image/x-mgx-dsf'               => array('dsf'),
            'image/x-cmx'                   => array('cmx'),
            'image/cgm'                     => array('cgm'),
            'image/fif'                     => array('fif'),
            'image/wavelet'                 => array('wi'),
            'image/vnd.dwg'                 => array('dwg'),
            'image/vnd.dxf'                 => array('dxf'),
            'image/vnd.svf'                 => array('svf'),
            'audio/mpeg'                    => array('mp3'),
            'audio/x-aiff'                  => array('aif', 'aiff', 'aifc'),
            'audio/x-wav'                   => array('wav'),
            'audio/x-mpeg'                  => array('mpa', 'abs', 'mpega'),
            'audio/x-mpeg-2'                => array('mp2a', 'mpa2'),
            'audio/echospeech'              => array('es'),
            'audio/voxware'                 => array('vox'),
            'audio/x-ms-wma'                => array('wma'),
            'video/mpeg'                    => array('mpeg', 'mpg', 'mpe'),
            'video/mpeg-2'                  => array('video', 'mpv2', 'mp2v'),
            'video/mp4'                     => array('mp4'),
            'video/avi'                     => array('avi'),
            'video/quicktime'               => array('mov', 'qt'),
            'video/x-msvideo'               => array('avi'),
            'video/vdo'                     => array('vdo'),
            'video/vivo'                    => array('viv'),
            'video/x-flv'                   => array('flv'),
            'video/ogg'                     => array('ogg', 'ogv'),
            'video/webm'                    => array('webm'),
            'video/x-ms-wmv'                => array('wmv'),
            'application/php'               => array('php'),
            'application/phtml'             => array('phtml'),
            'application/fastman'           => array('lcc'),
            'application/x-pn-realaudio'    => array('ra', 'ram'),
            'application/vnd.koan'          => array('skp'),
            'application/postscript'        => array('ai', 'eps', 'ps'),
            'application/rtf'               => array('rtf'),
            'application/pdf'               => array('pdf'),
            'application/vnd.mif'           => array('mif'),
            'application/x-troff'           => array('t', 'tr', 'roff'),
            'application/x-troff-man'       => array('man'),
            'application/x-troff-me'        => array('me'),
            'application/x-troff-ms'        => array('ms'),
            'application/x-latex'           => array('latex'),
            'application/x-tex'             => array('tex'),
            'application/x-texinfo'         => array('texinfo', 'texi'),
            'application/x-dvi'             => array('dvi'),
            'application/msword'            => array('doc'),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('docx'),
            'application/envoy'             => array('evy'),
            'application/x-gtar'            => array('gtar'),
            'application/x-tar'             => array('tar'),
            'application/x-bcpio'           => array('bcpio'),
            'application/x-cpio'            => array('cpio'),
            'application/x-shar'            => array('shar'),
            'application/zip'               => array('zip'),
            'application/xml'               => array('gpx'),
            'application/mac-binhex40'      => array('hqx'),
            'application/x-shockwave-flash' => array('swf'),
            'application/x-stuffit'         => array('sit', 'sea'),
            'application/fractals'          => array('fif'),
            'application/octet-stream'      => array('bin', 'uu', 'exe'),
            'application/x-wais-source'     => array('src', 'wsrc'),
            'application/hdf'               => array('hdf'),
            'application/x-sh'              => array('sh'),
            'application/x-csh'             => array('csh'),
            'application/x-perl'            => array('pl'),
            'application/x-tcl'             => array('tcl'),
            'application/andrew-inset'      => array('inset'),
            'application/futuresplash'      => array('spl'),
            'application/mbedlet'           => array('mbd'),
            'application/mspowerpoint'      => array('ppt', 'ppz'),
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array('pptx'),
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => array('ppsx'),
            'application/vnd.ms-powerpoint' => array('ppt'),
            'application/astound'           => array('asn'),
            'application/x-olescript'       => array('axs'),
            'application/x-oleobject'       => array('ods'),
            'application/x-webbasic'        => array('wba'),
            'application/x-alpha-form'      => array('frm'),
            'application/x-pcn'             => array('pcn'),
            'application/vnd.ms-excel'      => array('xls'),
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array('xlsx'),
            'application/ccv'               => array('ccv'),
            'application/x-p3d'             => array('p3d'),
            'application/vis5d'             => array('v5d'),
            'application/iges'              => array('igs'),
            'application/x-showcase'        => array('sc', 'sho', 'show'),
            'application/x-insight'         => array('ins', 'insight'),
            'application/x-annotator'       => array('ano'),
            'application/x-dirview'         => array('dir'),
            'application/x-enterlicense'    => array('lic'),
            'application/x-iconbook'        => array('icnbk'),
            'application/x-inpview'         => array('wb'),
            'application/x-install'         => array('inst'),
            'application/x-mailfolder'      => array('mail'),
            'application/x-ppages'          => array('pp'),
            'application/x-wingz'           => array('wkz'),
            'x-form/x-openscape'            => array('opp'),
            'x-music/x-midi'                => array('mid'),
            'x-conference/x-cooltalk'       => array('ice'),
            'x-script/x-wfxclient'          => array('wfx'),
            'x-world/x-vrml'                => array('wrl', 'vrml'),
            'x-world/x-vream'               => array('vrw'),
            'x-world/x-svr'                 => array('svr'),
            'x-world/x-wvr'                 => array('wvr'),
            'x-world/x-3dmf'                => array('3dmf'),
            'x-model/x-mesh'                => array('msh'),
            'drawing/x-dwf'                 => array('dwf'),
            'graphics/x-inventor'           => array('iv'),
            'font/woff'                     => array('woff'),
            'font/ttf'                      => array('ttf'),
            'font/opentype'                 => array('otf'),
            'application/vnd.oasis.opendocument.text'           => array('odt'),
            'application/vnd.oasis.opendocument.presentation'   => array('odp'),
            'application/vnd.oasis.opendocument.spreadsheet'    => array('ods'),
            'application/x-vnd.oasis.opendocument.text'         => array('odt'),
            'application/x-vnd.oasis.opendocument.presentation' => array('odp'),
            'application/x-vnd.oasis.opendocument.spreadsheet'  => array('ods'),
        ),
        'icons' => array(
            'video/avi' => 'File.avi',
            'video/x-msvideo' => 'File.avi',
            'application/msword' => 'File.doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'File.docx',
            'image/gif' => 'File.gif',
            'text/html' => 'File.html',
            'image/jpeg' => 'File.jpg',
            'image/pjpeg' => 'File.jpg',
            'audio/mpeg' => 'File.mp3',
            'application/pdf' => 'File.pdf',
            'application/php' => 'File.php',
            'application/phtml' => 'File.php',
            'image/png' => 'File.png',
            'image/x-png' => 'File.png',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'File.ppsx',
            'application/mspowerpoint' => 'File.ppt',
            'application/vnd.ms-powerpoint' => 'File.ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'File.pptx',
            'text/x-vcard' => 'File.vcf',
            'audio/x-wav' => 'File.wav',
            'audio/x-ms-wma' => 'File.wma',
            'video/x-ms-wmv' => 'File.wmv',
            'application/vnd.ms-excel' => 'File.xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'File.xlsx',
            'application/zip' => 'File.zip',
        ),
        'default_icon' => 'File',
    ),

    'response' => array (
        'headers' => array (
            'mime_type_expiration' => array (
                //define specific expiration time for content type
                'image/*'                       => 86400,
                'audio/*'                       => 86400,
                'text/*'                        => 86400,
                'font/*'                        => 86400,
                'application/x-shockwave-flash' => 86400,
            ),
            'default_expiration'    => 86400,
        ),
    ),

    'db_service'    => array(
        'abstract_factory'  => array(
            //PDO
            'pdo'       => array(
                'service_identifier'    => 'pdo',
                //The PDO connections are defined in a local config
                /*
                'config'                => array(
                    'config_name'    => array(
                        'dsn'       => '',
                        'username'  => '',
                        'password'  => '',
                        'options'   => array(
                        ),
                    ),
                ),
                */
            ),
            //Doctrine
            'dem'  => array(
                'service_identifier'    => 'dem',
            ),
            //Zend DB Adapter
            'zdb'  => array(
                'service_identifier'    => 'zdb',
            ),
        ),
    ),
    //Core setup
    'setup'         => array(
        'db'    => array(
            'table_name_provider'  => array(
                //Mapping of symbolic table names to real names used in db
                //When symbolic table name matches the real table name it can be safely omitted from these options
                //as the DbTableNameProvider returns the symbolic name unchanged when it is not found in the map
                'table_names'   => array(
                    //Tables in Vivo Portal core
                    //Symbolic => real
                    'core'          => array(
                        //'vivo_users'    => 'vivo_users',
                    ),
                    //Tables in modules (define in module config and override in a local config)
                    'module'        => array(
                        //Module name
                        //'MyModule'          => array(
                        //    //Symbolic => real
                        //    'symbolic_table_name'   => 'real_table_name',
                        //),
                    ),
                ),
            ),
        ),
    ),
    'indexer'   => array(
        'adapter'   => array(
            'type'      => 'dummy',
                //Solr options
//                'options'   => array(
//                    'id_field'      => 'uuid',
//                ),
        ),
        'default_searchable_fields' => array(
            '\title',
            '\resourceContent',
        ),
        'default_indexing_options'  => array(
            'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
            'indexed'       => true,
            'stored'        => true,
            'tokenized'     => false,
            'multi'         => false,
        ),
        'presets' => array(
            '\uuid' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\path' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\class' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\order' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_INT,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\hosts' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => true,
            ),
            '\publishedContents' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => true,
            ),
            '\state' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\created' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_DATETIME,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\createdBy' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\modified' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_DATETIME,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\modifiedBy' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\published'   => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_DATETIME,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\title'   => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => true,
                'multi'         => false,
            ),
            '\mimeType' => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\position'   => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\allowListingInOverview'   => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
            '\resourceContent'   => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => false,
                'tokenized'     => true,
                'multi'         => false,
            ),
            '\searchable'   => array(
                'type'          => Vivo\Indexer\IndexerInterface::FIELD_TYPE_STRING,
                'indexed'       => true,
                'stored'        => true,
                'tokenized'     => false,
                'multi'         => false,
            ),
        ),
    ),
    'indexer_helper'    => array(
        'extractable_mime_types'    => array(
            //NOTICE: The keys are arbitrary strings, not file extensions! The keys are not used anywhere, their
            //purpose is to enable 'unsetting' a config option in an overriding config file (e.g. a local config)
            //Do not add 'application/pdf' as extractable type, Solr often crashes on PDF files
            //'pdf'       => 'application/pdf',
            'rtf'       => 'application/rtf',
            'doc'       => 'application/msword',
            'docx'      => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'       => 'application/vnd.ms-excel',
            'xlsx'      => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'odt'       => 'application/vnd.oasis.opendocument.text',
            'odp'       => 'application/vnd.oasis.opendocument.presentation',
            'ods'       => 'application/vnd.oasis.opendocument.spreadsheet',
            'odt2'      => 'application/x-vnd.oasis.opendocument.text',
            'odp2'      => 'application/x-vnd.oasis.opendocument.presentation',
            'ods2'      => 'application/x-vnd.oasis.opendocument.spreadsheet',
        ),
    ),
    //Vivo Modules configuration
    'modules'  => array(
        //Storage for modules - configure in global/local config
        'storage'              => array(
            'class'     => '',
            'options'   => array(),
        ),
        //Name of stream (protocol) which will be registered for Vivo Module source file access in Storage
        'stream_name'   => 'vivo.module',
        //Vivo Module paths in Vivo Module Storage
        'module_paths'              => array(
            '/',
        ),
        'descriptor_name'       => 'vivo_module.json',
        //Default path where new modules will be added (in the module storage)
        'default_install_path'  => '/',
        //List of core modules loaded for all sites
        'core_modules'          => array(
        ),
        //Module resource manager configuration options
        'resource_manager'      => array(
            //Mapping of resource types to folders within modules
            'type_map'      => array(
                'view'      => 'view',
                'layout'    => 'view/layout',
                'resource'  => 'resource',
                'metadata'  => 'config/metadata',
            ),
            //Default resource type
            'default_type'  => 'resource',
        ),
        //Default db source used for modules
        //Configure in local config
        //'default_db_source'     => '',
    ),
    //Cache manager configuration - define in local config
    'cache_manager'         => array(
//        'cache_name'    => array(
            //Options to pass to StorageFactory::factory(), e.g.:
//            'adapter'   => array(
//                'name'      => 'filesystem',
//                'options'   => array(
//                    'cache_dir' => 'path/to/cache/dir',
//                    'namespace' => 'cache_namespace',
//                ),
//            ),
//            'plugins'   => array(
//                'serializer'    => array(),
//            ),
//        ),
    ),
    //Repository configuration
    'repository'    => array(
        //Storage for repository - configure in global/local config
        'storage'       => array(
            'class'         => '',
            'options'       => array(),
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'info' => array(
                    'options' => array(
                        'route'    => 'info [<action>]',
                        'defaults' => array(
                            'controller' => 'cli_info',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'module' => array(
                    'options' => array(
                        'route'    => 'module [<action>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'module_add' => array(
                    'options' => array(
                        'route'    => 'module add <module_url> [--force|-f]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'add',
                        ),
                    ),
                ),
                'module_install' => array(
                    'options' => array(
                        'route'    => 'module install <module_name> [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'install',
                        ),
                    ),
                ),
                'module_uninstall' => array(
                    'options' => array(
                        'route'    => 'module uninstall <module_name> [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'uninstall',
                        ),
                    ),
                ),
                'module_enable' => array(
                    'options' => array(
                        'route'    => 'module enable <module_name> [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'enable',
                        ),
                    ),
                ),
                'module_disable' => array(
                    'options' => array(
                        'route'    => 'module disable <module_name> [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'disable',
                        ),
                    ),
                ),
                'module_is_installed' => array(
                    'options' => array(
                        'route'    => 'module isinstalled <module_name> [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'isInstalled',
                        ),
                    ),
                ),
                'module_is_enabled' => array(
                    'options' => array(
                        'route'    => 'module isenabled <module_name> [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'isEnabled',
                        ),
                    ),
                ),
                'module_get_installed' => array(
                    'options' => array(
                        'route'    => 'module getinstalled [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'getInstalled',
                        ),
                    ),
                ),
                'module_get_enabled' => array(
                    'options' => array(
                        'route'    => 'module getenabled [<site>]',
                        'defaults' => array(
                            'controller' => 'cli_module',
                            'action'     => 'getEnabled',
                        ),
                    ),
                ),
                'indexer' => array(
                    'options' => array(
                        'route'    => 'indexer [<action>]',
                        'defaults' => array(
                            'controller' => 'cli_indexer',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'indexer_host_action' => array(
                    'options' => array(
                        'route'    => 'indexer <action> <host>',
                        'defaults' => array(
                            'controller' => 'cli_indexer',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'indexer_clear' => array(
                    'options' => array(
                        'route'    => 'indexer clear',
                        'defaults' => array(
                            'controller' => 'cli_indexer',
                            'action'     => 'clear',
                        ),
                    ),
                ),
                'indexer_reindex' => array(
                    'options' => array(
                        'route'    => 'indexer reindex <host> [--stopOnErrors|-soe]',
                        'defaults' => array(
                            'controller' => 'cli_indexer',
                            'action'     => 'reindex',
                        ),
                    ),
                ),
                'util' => array(
                    'options' => array(
                        'route'    => 'util [<action>]',
                        'defaults' => array(
                            'controller' => 'cli_util',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'util_crawl' => array(
                    'options' => array(
                        'route'    => 'util crawl <host> <service>',
                        'defaults' => array(
                            'controller' => 'cli_util',
                            'action'     => 'crawl',
                        ),
                    ),
                ),
                'cms' => array(
                    'options' => array(
                        'route'    => 'cms [<action>]',
                        'defaults' => array(
                            'controller' => 'cli_cms',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'cms_host_action' => array(
                    'options' => array(
                        'route'    => 'cms <action> <host>',
                        'defaults' => array(
                            'controller' => 'cli_cms',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'cms_unique_uuids' => array(
                    'options' => array(
                        'route'    => 'cms uniqueuuids <host> [--force|-f]',
                        'defaults' => array(
                            'controller' => 'cli_cms',
                            'action'     => 'unique-uuids',
                        ),
                    ),
                ),
                'cms_create_site'   => array(
                    'options' => array(
                        'route'    => 'cms createsite <name> <secdomain> <hosts> [<title>]',
                        'defaults' => array(
                            'controller' => 'cli_cms',
                            'action'     => 'create-site',
                        ),
                    ),
                ),
                'setup' => array(
                    'options' => array(
                        'route'    => 'setup [<action>]',
                        'defaults' => array(
                            'controller' => 'cli_setup',
                            'action'     => 'default',
                        ),
                    ),
                ),
                'setup_db' => array(
                    'options' => array(
                        'route'    => 'setup db [--force|-f]',
                        'defaults' => array(
                            'controller' => 'cli_setup',
                            'action'     => 'db',
                        ),
                    ),
                ),
            ),
        ),
    ),

    //Configuration of caches used for individual cache subjects
    //Use global/local configs to set-up caches
    //To turn cache off, set it to null
    'cache' => array(
        'repository'                    => null,
        'navigation'                    => null,
        'overview'                      => null,
        'translit_path'                 => null,
        'translit_url'                  => null,
        'translit_doc_title_to_path'    => null,
        'translit_mb_string_compare'    => null,
    ),

    'cms'      => array(
        //this config key is reserved for merged cms configuration
        //default values and structure are in cms.config.php
        //do not access to this key directly - use service 'cms_config'
    ),
    'options' => array(
        'template_not_found_action' => Vivo\View\Resolver\TemplateResolver::STATE_NOT_FOUND_ACTION_COMMENT,
	),
);
