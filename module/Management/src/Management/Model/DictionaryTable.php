<?php


namespace Management\Model;


use Zend\Db\TableGateway\TableGateway;

class DictionaryTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tg){
        $this->tableGateway = $tg;
    }

    /**
     * 获取字典中的value
     * @param null $key
     * @return bool
     */
    public function getValue($key = null){
        $rowSet  = $this->tableGateway->select(array('key'=>$key));
        $row = $rowSet->current();
        if(!$row){
            return null;
        }else{
            return $row->value;
        }
    }

    /**
     * 获取字典中的access_token值
     */
    public function getAccessToken(){
        $rowSet  = $this->tableGateway->select(array('key'=>'accessToken'));
        $row = $rowSet->current();
        if(!$row){
            //后续更新
            $this->initAccessToken();
        }else{
            $date = $row->update_time;
            $date_now = date('Y-m-d H:i:s');
            $de = strtotime($date_now) - strtotime($date);
            if($de <= 6000){
                return $row->value;
            }else{
                return  $this->needAccess();
            }
        }
    }

    /**
     * 如果时间不够则需要更新时间
     */
    private function needAccess(){
        $appId = $this->getValue('appId');
        $appSecret = $this->getValue('appSecret');
        if(!is_null($appId)&&!is_null($appSecret)){
            $new_access_token = $this->getWxAccessToken($appId,$appSecret);
            $data = array(
              'value'=>$new_access_token,
              'update_time'=>date('Y-m-d H:i:s')
            );
            $this->tableGateway->update($data,array('key'=>'accessToken'));
            return $new_access_token;
        }else{
            exit("error access token");
        }
    }

    /**
     * 初始化access_token
     */
    private function initAccessToken(){
        exit("该数据库未找到access_token");
    }

    /**
     * curl微信服务器
     * @param $appId
     * @param $appSecret
     * @return array
     */
    private function getWxAccessToken($appId,$appSecret){
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url); //要访问的地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//跳过证书验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        $dataBlock = curl_exec($ch);//这是json数据
        curl_close($ch);
        $res = json_decode($dataBlock, true); //接受一个json格式的字符串并且把它转换为 PHP 变量
        return $res['access_token'];
    }
}