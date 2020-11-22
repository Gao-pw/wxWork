<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Management\Controller\Login'=> 'Management\Controller\LoginController',
            'Management\Controller\Xmt'=> 'Management\Controller\XmtController',
            'Management\Controller\SystemManagement' => 'Management\Controller\SystemManagementController'
        ),
    ),
    'router' => array(
        'routes' => array(
            'management' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/management',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Management\Controller',
                        'controller'    => 'Login',
                        'action'        => 'login',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action][/:param1][/:param2][/:param3][/:param4][/:param5]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'param1'		 => '[a-zA-Z0-9]*',
                                'param2'    => '[a-zA-Z0-9]*',
                                'param3'    => '[a-zA-Z0-9]*',
                                'param4'    => '[a-zA-Z0-9]*',
                                'param5'    => '[a-zA-Z0-9]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'management' => __DIR__ . '/../view',
        ),
    ),
);
