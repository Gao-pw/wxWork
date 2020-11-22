<?php


namespace Management\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class LoginController extends AbstractActionController
{
    private $userTable;

    /***
     * 首页登录
     */
    public function loginAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            $username = $_POST['userName'];
            $this->tryLogin($username);

        }
        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }

    public function tryLogin($username){
        $res = $this->getUserTable()->login($username);
        if($res === 1){
            //return $this->redirect()->toRoute('management',array('controller'=>'Xmt','action'=>'index'));
            return $this->redirect()->toUrl('/management/xmt/index');
        }else{
            echo "<script>alert('运营者姓名错误')</script>";
        }
    }

    /**
     * 数据库服务
     */
    public function getUserTable() {
        if (! $this->userTable) {
            $sm = $this->getServiceLocator ();
            $this->userTable = $sm->get ( 'Management\Model\UserTable' );
        }
        return $this->userTable;
    }
}