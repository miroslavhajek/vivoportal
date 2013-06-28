<?php
/**
 * CMS config (cms_config), this configuration could be overwritten
 * by configuration in modules and sites.
 * This config is registered as 'cms_config' service, when all configuration
 * sources (site, modules) are merged.
 */
return array(
    'templates' => array (
        'template_map' => array(
            // Content front component
            'Vivo\CMS\UI\Content\Layout'        => __DIR__.'/../view/Vivo/CMS/UI/Content/Layout.phtml',
            'Vivo\CMS\UI\Content\File:html'     => __DIR__.'/../view/Vivo/CMS/UI/Content/File.html.phtml',
            'Vivo\CMS\UI\Content\File:plain'    => __DIR__.'/../view/Vivo/CMS/UI/Content/File.plain.phtml',
            'Vivo\CMS\UI\Content\File:flash'    => __DIR__.'/../view/Vivo/CMS/UI/Content/File.flash.phtml',
            'Vivo\CMS\UI\Content\File:image'    => __DIR__.'/../view/Vivo/CMS/UI/Content/File.image.phtml',
            'Vivo\CMS\UI\Content\File'          => __DIR__.'/../view/Vivo/CMS/UI/Content/File.phtml',
            'Vivo\CMS\UI\Content\Fileboard'            => __DIR__.'/../view/Vivo/CMS/UI/Content/Fileboard.phtml',
            'Vivo\CMS\UI\Content\Fileboard:Media'      => __DIR__.'/../view/Vivo/CMS/UI/Content/Fileboard.Media.phtml',
            'Vivo\CMS\UI\Content\Fileboard:Separator'  => __DIR__.'/../view/Vivo/CMS/UI/Content/Fileboard.Separator.phtml',
            'Vivo\CMS\UI\Content\Overview'      => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.phtml',
            'Vivo\CMS\UI\Content\Overview:Inline'       => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.Inline.phtml',
            'Vivo\CMS\UI\Content\Overview:NavInline'    => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.NavInline.phtml',
            'Vivo\CMS\UI\Content\Overview:NavBlock'     => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.NavBlock.phtml',
            'Vivo\CMS\UI\Content\Overview:Desc' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.Desc.phtml',
            'Vivo\CMS\UI\Content\Overview:Date' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.Date.phtml',
            'Vivo\CMS\UI\Content\Overview:DateDesc' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.DateDesc.phtml',
            'Vivo\CMS\UI\Content\Overview:DateDescThumb' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.DateDescThumb.phtml',
            'Vivo\CMS\UI\Content\Overview:Thumb' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.Thumb.phtml',
            'Vivo\CMS\UI\Content\Overview:ThumbDesc' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.ThumbDesc.phtml',
            'Vivo\CMS\UI\Content\Overview:Carousel' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.Carousel.phtml',
            'Vivo\CMS\UI\Content\Overview:CarouselTouch' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.CarouselTouch.phtml',
            'Vivo\CMS\UI\Content\Overview:Expandable' => __DIR__.'/../view/Vivo/CMS/UI/Content/Overview.Expandable.phtml',
            'Vivo\CMS\UI\Content\Logon'         => __DIR__.'/../view/Vivo/CMS/UI/Content/Logon.phtml',
            'Vivo\CMS\UI\Content\Search'         => __DIR__.'/../view/Vivo/CMS/UI/Content/Search.phtml',

            // Content editor component
            'Vivo\CMS\UI\Content\Editor\Editor'    => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/Editor.phtml',
            'Vivo\CMS\UI\Content\Editor\File'      => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/File.phtml',
            'Vivo\CMS\UI\Content\Editor\Fileboard' => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/Fileboard.phtml',
            'Vivo\CMS\UI\Content\Editor\Layout'    => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/Layout.phtml',
            'Vivo\CMS\UI\Content\Editor\Overview'  => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/Overview.phtml',
            'Vivo\CMS\UI\Content\Editor\Navigation' => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/Navigation.phtml',
            'Vivo\CMS\UI\Content\Editor\File\WysiwygAdapter' => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/File/WysiwygAdapter.phtml',
            'Vivo\CMS\UI\Content\Editor\File\DefaultAdapter' => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/File/DefaultAdapter.phtml',
            'Vivo\CMS\UI\Content\Editor\Search'     => __DIR__.'/../view/Vivo/CMS/UI/Content/Editor/Search.phtml',

            // Other UI
            'Vivo\UI\Page'                      => __DIR__.'/../view/Vivo/UI/Page.phtml',
            'Vivo\UI\ComponentContainer'        => __DIR__.'/../view/Vivo/UI/ComponentContainer.phtml',
            'Vivo\UI\TabContainer'              => __DIR__.'/../view/Vivo/UI/TabContainer.phtml',
            'Vivo\UI\Paginator'                 => __DIR__.'/../view/Vivo/UI/Paginator.phtml',
            'Vivo\UI\Ribbon'                    => __DIR__.'/../view/Vivo/UI/TabContainerRibbon.phtml',
            'Vivo\UI\Ribbon\Tab'                => __DIR__.'/../view/Vivo/UI/Ribbon/Tab.phtml',
            'Vivo\UI\Ribbon\Group'              => __DIR__.'/../view/Vivo/UI/Ribbon/Group.phtml',
            'Vivo\UI\Ribbon\Item'               => __DIR__.'/../view/Vivo/UI/Ribbon/Item.phtml',
            'Vivo\UI\Alert'                     => __DIR__.'/../view/Vivo/UI/Alert.phtml',
            'Vivo\CMS\UI\Content\Navigation'    => __DIR__.'/../view/Vivo/CMS/UI/Content/Navigation.phtml',
            'Vivo\CMS\UI\Content\Navigation:Breadcrumbs' => __DIR__.'/../view/Vivo/CMS/UI/Content/Navigation.Breadcrumbs.phtml',
        ),
        'custom_templates' => array (
            // custom selectable templates
            'Vivo\CMS\Model\Content\Overview' => array (
                'Vivo\CMS\UI\Content\Overview',
                'Vivo\CMS\UI\Content\Overview:Inline',
                'Vivo\CMS\UI\Content\Overview:NavInline',
                'Vivo\CMS\UI\Content\Overview:NavBlock',
                'Vivo\CMS\UI\Content\Overview:Desc',
                'Vivo\CMS\UI\Content\Overview:Date',
                'Vivo\CMS\UI\Content\Overview:DateDesc',
                'Vivo\CMS\UI\Content\Overview:DateDescThumb',
                'Vivo\CMS\UI\Content\Overview:Thumb',
                'Vivo\CMS\UI\Content\Overview:Carousel',
                'Vivo\CMS\UI\Content\Overview:ThumbDesc',
                'Vivo\CMS\UI\Content\Overview:CarouselTouch',
                'Vivo\CMS\UI\Content\Overview:Expandable',
            ),
            'Vivo\CMS\Model\Content\Navigation'    => array(
                'Vivo\CMS\UI\Content\Navigation',
                'Vivo\CMS\UI\Content\Navigation:Breadcrumbs',
            ),
        ),
    ),
    'component_mapping' => array (
        'front_component' => array (
            'Vivo\CMS\Model\Content\Component'  => 'Vivo\CMS\UI\Content\Component',
            'Vivo\CMS\Model\Content\Layout'     => 'Vivo\CMS\UI\Content\Layout',
            'Vivo\CMS\Model\Content\File'       => 'Vivo\CMS\UI\Content\File',
            'Vivo\CMS\Model\Content\Overview'   => 'Vivo\CMS\UI\Content\Overview',
            'Vivo\CMS\Model\Content\Hyperlink'  => 'Vivo\CMS\UI\Content\Hyperlink',
            'Vivo\CMS\Model\Content\Logon'      => 'Vivo\CMS\UI\Content\Logon',
            'Vivo\CMS\Model\Content\Navigation' => 'Vivo\CMS\UI\Content\Navigation',
            'Vivo\CMS\Model\Content\Fileboard'  => 'Vivo\CMS\UI\Content\Fileboard',
            'Vivo\CMS\Model\Content\Search'     => 'Vivo\CMS\UI\Content\Search',
        ),
        'editor_component' => array (
            'Vivo\CMS\Model\Content\Component'  => 'Vivo\CMS\UI\Content\Editor\Editor',
            'Vivo\CMS\Model\Content\File'       => 'Vivo\CMS\UI\Content\Editor\File',
            'Vivo\CMS\Model\Content\Overview'   => 'Vivo\CMS\UI\Content\Editor\Overview',
            'Vivo\CMS\Model\Content\Layout'     => 'Vivo\CMS\UI\Content\Editor\Layout',
            'Vivo\CMS\Model\Content\Link'       => 'Vivo\CMS\UI\Content\Editor\Editor',
            'Vivo\CMS\Model\Content\Hyperlink'  => 'Vivo\CMS\UI\Content\Editor\Editor',
            'Vivo\CMS\Model\Content\Navigation' => 'Vivo\CMS\UI\Content\Editor\Navigation',
            'Vivo\CMS\Model\Content\Fileboard'  => 'Vivo\CMS\UI\Content\Editor\Fileboard',
            'Vivo\CMS\Model\Content\Search'     => 'Vivo\CMS\UI\Content\Editor\Search',
        ),
    ),
    'contents' => array (
        'available_contents' => array (
            'file'    => array(
                'class'     => 'Vivo\CMS\Model\Content\File',
                'label'     => 'Vivo\CMS\Model\Content\File - general file',
            ),
            'file_text/html'    => array(
                'class'     => 'Vivo\CMS\Model\Content\File',
                'label'     => 'Vivo\CMS\Model\Content\File - HTML file',
                'options'   => array(
                    'mimeType'  => 'text/html',
                ),
            ),
            'overview'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Overview',
                'label'     => 'Vivo\CMS\Model\Content\Overview',
            ),
            'link'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Link',
                'label'     => 'Vivo\CMS\Model\Content\Link',
            ),
            'hyperlink'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Hyperlink',
                'label'     => 'Vivo\CMS\Model\Content\Hyperlink',
            ),
            'component'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Component',
                'label'     => 'Vivo\CMS\Model\Content\Component',
            ),
            'layout'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Layout',
                'label'     => 'Vivo\CMS\Model\Content\Layout',
            ),
            'navigation'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Navigation',
                'label'     => 'Vivo\CMS\Model\Content\Navigation',
            ),
            'fileboard'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Fileboard',
                'label'     => 'Vivo\CMS\Model\Content\Fileboard',
            ),
            'search'    => array(
                'class'     => 'Vivo\CMS\Model\Content\Search',
                'label'     => 'Vivo\CMS\Model\Content\Search',
            ),
        ),
        'restrictions' => array (
            'document_type' => array (
                'Vivo\CMS\Model\Folder'     => array(
                    //Folder has no available contents
                ),
            ),
            'document_path' => array (
                '/layouts' => array (
                    'layout',
                ),
            ),
            'user_role' => array (
//                'managers' => array (
//                ),
            ),
//            'site' => array (
//               //Whitelist of allowed contents in current site.
//            ),
        ),
        //Editor adapters for specific content types
        'adapters' => array (
            'Vivo\CMS\Model\Content\File'    => array(
                //TODO - set service name of the default adapter for File content
                'default'       => 'Vivo\CMS\UI\Content\Editor\File\DefaultAdapter',
                'service_map'   => array(
                    'text/html'     => 'Vivo\CMS\UI\Content\Editor\File\WysiwygAdapter',
                ),
            ),
        ),
    ),
    'workflow' => array (
        'states' => array(
            100 => array(
                'state'=> 'NEW',
                'groups' => array(/* 'Anyone' */),
            ),
            200 => array(
                'state'=> 'PUBLISHED',
                'groups' => array(),
            ),
            300 => array(
                'state'=> 'ARCHIVED',
                'groups' => array(),
            ),
        ),
    ),
    'languages' => array(
        'cs' => 'čeština',
        'sk' => 'slovenčina',
        'en' => 'english',
        'de' => 'deutsch',
        'pl' => 'polski',
        'fr' => 'français',
        'it' => 'italiano',
        'es' => 'español',
        'ru' => 'по-русски',
    ),
    'backend' => array (
        'plugins' =>  array (
        ),
        //Backend Tree component
        'tree'      => array(
            'options'   => array(
                'max_items'     => 20,
            ),
        ),
    ),
    'service_manager' => array (
        //configuration of service manager, services defined here should not override
        //services defined in Vivo config
        'invokables' => array (
            'Vivo\CMS\UI\Blank'             => 'Vivo\CMS\UI\Blank',
            'Vivo\CMS\UI\Root'              => 'Vivo\CMS\UI\Root',
            'Vivo\UI\ComponentContainer'    => 'Vivo\UI\ComponentContainer',
            'Vivo\UI\TabContainer'          => 'Vivo\UI\TabContainer',
            'Vivo\CMS\UI\Manager\Explorer\Ribbon'  => 'Vivo\CMS\UI\Manager\Explorer\Ribbon',
        ),
        'factories' => array (
            'Vivo\CMS\UI\Content\File'       => 'Vivo\CMS\UI\Content\FileFactory',
            'Vivo\CMS\UI\Content\Hyperlink'  => 'Vivo\CMS\UI\Content\HyperlinkFactory',
            'Vivo\CMS\UI\Content\Layout'     => 'Vivo\CMS\UI\Content\LayoutFactory',
            'Vivo\CMS\UI\Content\Overview'   => 'Vivo\CMS\UI\Content\OverviewFactory',
            'Vivo\CMS\UI\Content\Logon'      => 'Vivo\CMS\UI\Content\LogonFactory',
            'Vivo\CMS\UI\Content\Navigation' => 'Vivo\CMS\UI\Content\NavigationFactory',
            'Vivo\CMS\UI\Content\Fileboard'  => 'Vivo\CMS\UI\Content\FileboardFactory',
            'Vivo\CMS\UI\Content\Search'     => 'Vivo\CMS\UI\Content\SearchFactory',
            'Vivo\UI\Page'                   => 'Vivo\Service\UI\PageFactory',
            'Vivo\UI\Alert'                  => 'Vivo\UI\AlertFactory',
            'Vivo\UI\Paginator'              => 'Vivo\UI\PaginatorFactory',
            'security_manager'               => 'Vivo\Service\SimpleSecurityManagerFactory',
//          'security_manager'               => 'Vivo\Service\DbSecurityManagerFactory',

            //backend
            //TODO move to own config
            'Vivo\Backend\UI\Backend'           => 'Vivo\Backend\UI\BackendFactory',
            'Vivo\Backend\UI\SiteSelector'      => 'Vivo\Backend\UI\SiteSelectorFactory',
            'Vivo\Backend\UI\Explorer\Explorer' => 'Vivo\Backend\UI\Explorer\ExplorerFactory',
            'Vivo\Backend\UI\Explorer\Editor'   => 'Vivo\Backend\UI\Explorer\EditorFactory',
            'Vivo\Backend\UI\Explorer\Editor\Content' => 'Vivo\Backend\UI\Explorer\Editor\ContentFactory',
            'Vivo\Backend\UI\Explorer\Editor\ContentTab' => 'Vivo\Backend\UI\Explorer\Editor\ContentTabFactory',
            'Vivo\Backend\UI\Explorer\Editor\Resource' => 'Vivo\Backend\UI\Explorer\Editor\ResourceFactory',
            'Vivo\Backend\UI\Explorer\Finder'   => 'Vivo\Backend\UI\Explorer\FinderFactory',
            'Vivo\Backend\UI\Explorer\Delete'   => 'Vivo\Backend\UI\Explorer\DeleteFactory',
            'Vivo\Backend\UI\Explorer\Creator'  => 'Vivo\Backend\UI\Explorer\CreatorFactory',
            'Vivo\Backend\UI\Explorer\Copy'     => 'Vivo\Backend\UI\Explorer\CopyFactory',
            'Vivo\Backend\UI\Explorer\Move'     => 'Vivo\Backend\UI\Explorer\MoveFactory',
            'Vivo\Backend\UI\Explorer\Viewer'   => 'Vivo\Backend\UI\Explorer\ViewerFactory',
            'Vivo\Backend\UI\Explorer\Browser'  => 'Vivo\Backend\UI\Explorer\BrowserFactory',
            'Vivo\Backend\UI\Logon'             => 'Vivo\Backend\UI\LogonFactory',
            'Vivo\Backend\ModuleResolver'       => 'Vivo\Backend\ModuleResolverFactory',

            //TODO: content editor factories
            'Vivo\CMS\UI\Content\Editor\Editor'     => 'Vivo\CMS\UI\Content\Editor\EditorFactory',
            'Vivo\CMS\UI\Content\Editor\File'       => 'Vivo\CMS\UI\Content\Editor\FileFactory',
            'Vivo\CMS\UI\Content\Editor\Overview'   => 'Vivo\CMS\UI\Content\Editor\OverviewFactory',
            'Vivo\CMS\UI\Content\Editor\Layout'     => 'Vivo\CMS\UI\Content\Editor\LayoutFactory',
            'Vivo\CMS\UI\Content\Editor\Navigation' => 'Vivo\CMS\UI\Content\Editor\NavigationFactory',
            'Vivo\CMS\UI\Content\Editor\Fileboard'  => 'Vivo\CMS\UI\Content\Editor\FileboardFactory',
            'Vivo\CMS\UI\Content\Editor\Search'     => 'Vivo\CMS\UI\Content\Editor\SearchFactory',

            //Content editor adapters
            'Vivo\CMS\UI\Content\Editor\File\WysiwygAdapter'
                    => 'Vivo\CMS\UI\Content\Editor\File\WysiwygAdapterFactory',
            'Vivo\CMS\UI\Content\Editor\File\DefaultAdapter'
                    => 'Vivo\CMS\UI\Content\Editor\File\DefaultAdapterFactory',

            'Vivo\CMS\FetchErrorDocumentListener' => 'Vivo\CMS\FetchErrorDocumentListenerFactory',
        ),
        'aliases' => array(
        ),
        'shared' => array(
        ),
        'initializers' => array(
        ),
    ),
    'di' => array (
        'instance' => array (
            'alias' => array (
            ),
            'Vivo\UI\Component' => array (
                'injection' => array (
                ),
                'parameters' => array (
                    'view' => 'Zend\View\Model\ViewModel',
                ),
            ),
        ),
    ),

    'ui' => array (
        //configuration of ui components
        'Vivo\UI\Page' => array (
            'doctype' => 'HTML5',
                'links' => array (
                    'apple_touch_icon_57'   => array(
                        'rel'  => 'apple-touch-icon-precomposed',
                        'href' => '/.Vivo.resource/apple-touch-icon-57.png',
                    ),
                    'apple_touch_icon_72'   => array(
                        'rel'  => 'apple-touch-icon-precomposed',
                        'sizes' => '72x72',
                        'href' => '/.Vivo.resource/apple-touch-icon-72.png',
                    ),
                    'apple_touch_icon_114'  => array(
                        'rel'  => 'apple-touch-icon-precomposed',
                        'sizes' => '114x114',
                        'href' => '/.Vivo.resource/apple-touch-icon-114.png',
                    ),
                    'apple_touch_icon_144'  => array(
                        'rel'  => 'apple-touch-icon-precomposed',
                        'sizes' => '144x144',
                        'href' => '/.Vivo.resource/apple-touch-icon-144.png',
                    ),
                    'favicon'       => array(
                        'rel'  => 'shortcut icon',
                        'href' => '/.Vivo.resource/favicon.ico',
                    ),
                    'print_css'     => array(
                        'rel'  => 'stylesheet',
                        'href' => '/.Vivo.resource/css/print.css',
                        'type' => 'text/css',
                        'media' => 'print',
                    ),
                    'front_css'     => array(
                        'rel'  => 'stylesheet',
                        'href' => '/.Vivo.resource/css/front.css',
                        'type' => 'text/css',
                        'media' => 'screen',
                        //'offset'    => 10000,
                    ),
                ),
                'scripts' => array (
//                    'script1'    => array(
//                        'src' => '/.Vivo.resource/js/...',
//                        'type' => 'text/javascript',
//                        'offset'    => 1000,
//                    ),
                ),
            'metas' => array (
                    array (
                        'name' => 'Robots',
                        'content' => 'INDEX,FOLLOW',
                    ),
                    array (
                        'charset' => 'UTF-8',
                    ),
                // array (
                //     'http-equiv' => 'Content-Type',
                //     'content' => 'text/html',
                //     'charset' => 'utf-8',
                // ),
            ),
        ),
        'Vivo\UI\Content\Navigation'    => array(
            //Cache for navigation containers
            'cache'     => null,
        ),
        'Vivo\UI\Content\Overview'      => array(
            //Cache for overview pages
            'cache'     => null,
        ),
    ),
    'output_filters' => array (
        //register output filters
        //'Vivo\Http\Filter\UpperCase',
        'Vivo\Http\Filter\ImageTransform',
    ),
    'security_manager_simple'  => array(
        //Define your options in your local.php config
        'options'           => array(
            //Security domain - if not set, the security domain of the active site will be used
            'security_domain'   => 'VIVO',
            'username'          => 'vivo.user',
            'password'          => 'password',
        ),
    ),
    'security_manager_db'  => array(
        //Define your options in your local.php config
        'options'           => array(
            //'super_password'        => 'Vivo.super.Pwd.497',
            'super_access_networks' => array(
                //'127.0.0.1',
            ),
        ),
    ),
    'mail'  => array(
        'simple_renderer'   => array(
            'options'   => array(
                //Map of template names mapped to phtml files
                'template_map'          => array(
                ),
                //Array of helper names to be copied from the main helper plugin manager
                //Note that there are Zend view helpers available by default
                //See Zend\View\HelperPluginManager
                'use_helpers'       => array(
                    'translate',
                ),
            ),
        ),
    ),
    'Vivo\CMS\ComponentFactory' => array (
        'specialComponents' => array (
            //theese component are used instead of missing component
            'layout_empty_panel'    => 'Vivo\CMS\UI\LayoutEmptyPanel',
            'unpublished_document'  => 'Vivo\CMS\UI\UnpublishedDocument',
        ),
    ),
    'error_documents' => array (
        'code' => array (
            '401' => '/error-401/',
            '403' => '/error-403/',
            '404' => '/error-404/',
            '500' => '/error-500/',
        ),
        'default' => '/error/',
    ),
    'document_sorting' => array (
        'native' => array(
            'parent'         => 'by_parent_document',
            'title:asc'      => 'title_asc',
            'title:desc'     => 'title_desc',
            'created:asc'    => 'created_asc',
            'created:desc'   => 'created_desc',
            'modified:asc'   => 'modified_asc',
            'modified:desc'  => 'modified_desc',
            'position:asc'   => 'position_asc',
            'position:desc'  => 'position_desc',
            'published:asc'  => 'publish_date_document_only_asc',
            'published:desc' => 'publish_date_document_only_desc',
            'random'     => 'random'
        ),
     ),
    'defaultSearchableFields' => array (
            '\title'           => 'title',
            '\resourceContent' => 'resourceContent',
     ),
);
