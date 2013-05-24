<?php
return array(
    'router' => array(
        'routes' => array(
			'signin' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	 => '/signin',
					'defaults' => array(
						'controller' 	=> 'User\Controller\Account',
						'action'		=> 'signin'
					)
				)
			),
			'signup' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	 => '/signup',
					'defaults' => array(
						'controller' 	=> 'User\Controller\Account',
						'action'		=> 'signup'
					)
				)
			),
			'logout' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	 => '/logout',
					'defaults' => array(
						'controller' 	=> 'User\Controller\Account',
						'action'		=> 'logout'
					)
				)
			),
			'account' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	 => '/account',
					'defaults' => array(
						'controller' 	=> 'User\Controller\Account',
						'action'		=> 'index',
					)
				)
			),
			'account_profile' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	 => '/account/profile',
					'defaults' => array(
						'controller' 	=> 'User\Controller\Account',
						'action'		=> 'profile',
					)
				)
			),
			'account_image' => array(
				'type' => 'Literal',
				'options' => array(
					'route'	=> '/account/image',
					'defaults' => array(
						'controller' => 'User\Controller\Account',
						'action' => 'image'
					)
				)
			),
        ),
    ),
    'service_manager' => array(
		'aliases' => array( 'db' => 'Zend\Db\Adapter\Adapter' ),
        'factories' => array(
			'user_mapper' => 'User\Mapper\User',
			'permissions_mapper' => 'User\Mapper\Permission',
        )
	),
    'controllers' => array(
        'invokables' => array(
            'User\Controller\Index' => 'User\Controller\IndexController',
			'User\Controller\Account' => 'User\Controller\AccountController',
        ),
    ),
	'controller_plugins' => array(
		'invokables' => array(
			'User' => '\User\Controller\Plugin\User'
		)
	),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/user'		=> __DIR__ . '/../view/layout/layout.phtml',
            'user/index/index'	=> __DIR__ . '/../view/user/index/index.phtml',
			'signup'			=> __DIR__ . '/../view/user/account/signup.phtml',
			'signin'			=> __DIR__ . '/../view/user/account/signin.phtml',
			'profile'			=> __DIR__ . '/../view/user/account/profile.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
	'upload_policies' => array(
		'dir' => 'uploads/user',
		'avatar' => array(
			'name' => 'avatar',
			'max_file_size' => 5000000, /* 5Mb */
			'width' => 200, /* pixels */
			'height' => 200, /* pixels */
			'extensions' => array(
				'image/jpeg' => 'jpg',
	 			'image/png' => 'png',
				'image/gif' => 'gif'
			)
		)
	)
);