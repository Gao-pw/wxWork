<?php

namespace Management;

use Management\Model\Article;
use Management\Model\ArticleTable;
use Management\Model\UserArticle;
use Management\Model\UserArticleTable;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Mvc\ModuleRouteListener;
use Zend\Db\TableGateway\TableGateway;
use Management\Model\User;
use Management\Model\UserTable;
use Management\Model\Dictionary;
use Management\Model\DictionaryTable;



class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Management\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                'Management\Model\DictionaryTable' =>  function($sm) {
                    $tableGateway = $sm->get('DictionaryTableGateway');
                    $table = new DictionaryTable($tableGateway);
                    return $table;
                },
                'DictionaryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Dictionary());
                    return new TableGateway('dictionary', $dbAdapter, null, $resultSetPrototype);
                },
                'Management\Model\ArticleTable' =>  function($sm) {
                    $tableGateway = $sm->get('ArticleTableGateway');
                    $table = new ArticleTable($tableGateway);
                    return $table;
                },
                'ArticleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Article());
                    return new TableGateway('article', $dbAdapter, null, $resultSetPrototype);
                },
                'Management\Model\UserArticleTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserArticleTableGateway');
                    $table = new UserArticleTable($tableGateway);
                    return $table;
                },
                'UserArticleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserArticle());
                    return new TableGateway('user_article', $dbAdapter, null, $resultSetPrototype);
                },
            )
        );
    }
}