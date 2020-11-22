<?php
/**
 * bjfu xmt 微信获取
 */

namespace Management\Controller;


use Management\Model\Article;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Management\Model\SystemService;

class XmtController extends AbstractActionController
{
    public $username;
    public $user_id;
    public $user_type;
    /**
     * @var array|object
     */
    private $dictionaryTable;
    private $articleTable;
    private $userarticleTable;

    /**
     * XmtController constructor.
     */
    public function __construct(){
        $system = new SystemService();
        $res = $system->getSession();
        $this->user_type = $res['user_type'];
        $this->user_id = $res['user_id'];
        $this->username = $res['username'];
        
    }

    /**
     * 首页
     * @return array
     */
    public function indexAction()
    {
        $data = array(
            'username'=>$this->username,
            'user_type'=>$this->user_type,
        );
        return array('data'=>$data);
    }

    /**
     * 表单提交
     */
    public function pushRecordAction(){
        $request = $this->request;
        if($request->isPost()){
            var_dump($_POST);
//            foreach ($_POST['date'] as $k => $v) {
//                var_dump($v);
//            }
            for($i = 0;$i<count($_POST['date']);$i++){
                $article_data = array(
                    'article_date'=>$_POST['date'][$i],
                    'article_title'=>$_POST['title'][$i],
                    'article_link'=>$_POST['link'][$i],
                    'article_position'=>$_POST['position'][$i],
                    'article_read'=>$_POST['read'][$i],
                );
                $article = new Article();
                $article->exchangeArray($article_data);
                $this->getArticleTable()->insertData($article);
                $id = $this->getArticleTable()->findArticleId($article);
                if($id){
                    $this->getUserArticleTable()->insertData($id,$this->user_id);
                }
            }

            exit("push");
        }
    }

    /**
     * 测试用例
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function testJsonAction(){
        $request = $this->request;
        if($request->isPost()){
            $postData = file_get_contents('php://input');
            $post_arr = json_decode($postData);
            $re_date=$this->getWx($post_arr->date);
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine( 'Content-Type', 'application/json' );
            $response->setContent($re_date);
            return $response;
        }
    }

    /**
     * 调用微信接口
     * @param $date
     * @return array
     */
    public function getWx($date){
        $token = $this->getDictionaryTable()->getAccessToken();

        $url = 'https://api.weixin.qq.com/datacube/getarticletotal?access_token='.$token;
        $post_data = array(
            "begin_date" => $date,
            "end_date" => $date
        );
        $data_string =  json_encode($post_data);

        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为json输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string)
            )
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }


    //数据库服务

    /**
     * 字典数据表
     * @return array|object
     */
    public function getDictionaryTable() {
        if (! $this->dictionaryTable) {
            $sm = $this->getServiceLocator ();
            $this->dictionaryTable = $sm->get ( 'Management\Model\DictionaryTable' );
        }
        return $this->dictionaryTable;
    }

    /**
     * 文章数据表
     * @return array|object
     */
    public function getArticleTable() {
        if (! $this->articleTable) {
            $sm = $this->getServiceLocator ();
            $this->articleTable = $sm->get ( 'Management\Model\ArticleTable' );
        }
        return $this->articleTable;
    }

    public function getUserArticleTable() {
        if (! $this->userarticleTable) {
            $sm = $this->getServiceLocator ();
            $this->userarticleTable = $sm->get ( 'Management\Model\UserArticleTable' );
        }
        return $this->userarticleTable;
    }

    public function testAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            $re_date=json_encode(array('key'=>123,'value'=>456));
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine( 'Content-Type', 'application/json' );
            $response->setContent($re_date);
            return $response;
        }
    }



}