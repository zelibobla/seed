<?php
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Core\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
			'voice' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/voice[/:id]',
					'constraints' => array(
						'id' => '[0-9]+'
					),
                    'defaults' => array(
                        'controller' => 'Core\Controller\Voice',
                    ),
                ),
            ),
			'cron' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/cron/:action',
					'defaults' => array(
						'controller' => 'Core\Controller\Cron',
					)
				)
			),
        ),
    ),
    'service_manager' => array(
		'aliases' => array( 'db' => 'Zend\Db\Adapter\Adapter' ),
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
			'note_mapper' => 'Core\Mapper\Note',
		  	'message_mapper' => 'Core\Mapper\Message',
			'postal' => 'Core\Model\MessagesStack'
        ),
    ),
    'translator' => array(
        'locale' => 'ru_RU',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
				'text_domain' => ''
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Core\Controller\Index' => 'Core\Controller\IndexController',
			'Core\Controller\Voice' => 'Core\Controller\VoiceController',
			'Core\Controller\Cron' => 'Core\Controller\CronController'
        ),
    ),
	'controller_plugins' => array(
		'invokables' => array(
			'_' => '\Core\Controller\Plugin\Translate',
			'view' => '\Core\Controller\Plugin\View',
		)
	),
	'view_helpers' => array(
		'invokables' => array(
			'date' => '\Core\Helper\Date'
		)
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions'       => true,
		'doctype'                  => 'HTML5',
		'not_found_template'       => 'error/404',
		'exception_template'       => 'error/index',
		'template_map' => array(
			'layout/core'         => __DIR__ . '/../view/layout/layout.phtml',
			'core/index/index'		=> __DIR__ . '/../view/core/index/index.phtml',
			'error/404'             => __DIR__ . '/../view/error/404.phtml',
			'error/index'           => __DIR__ . '/../view/error/index.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
	'settings' => array(
		'admin_email' => 'admin@some.ru', 
		'cron_secret' => 'tetr3pe21',
	),
);
