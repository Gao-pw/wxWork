<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=xmtoa;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'user'=>'bjfuxmt',
        'password'=>"123456",
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
            => 'Zend\Db\Adapter\AdapterServiceFactory',

        ),
    ),
    'caches' => array(
        'memcached' => array( // //can be called directly via SM in the name of 'memcached'
            'adapter' => array(
                'name' => 'memcached',
                'lifetime' => 7200,
                'options' => array(
                    'servers' => array(
                        array(
                            '127.0.0.1',//服务器域名或ip
                            11211       //服务器tcp端口号，默认值是11211
                        )
                    ),
                    'namespace' => '',
                    'liboptions' => array(
                        'COMPRESSION' => true,
                        'binary_protocol' => true,
                        'no_block' => true,
                        'connect_timeout' => 100
                    )
                )
            ),
            'plugins' => array(
                'exception_handler' => array(
                    'throw_exceptions' => false
                )
            )
        )
    )
    // ...
);
