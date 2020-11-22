<?php


namespace Management\Model;


use Zend\Db\TableGateway\TableGateway;

class ArticleTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tg){
        $this->tableGateway = $tg;
    }

    /**
     * 插入数据库
     * @param Article $article
     */
    public function insertData(Article $article){
        $judge_article = array(
            'article_link'=>$article->article_link,
            'article_date'=>$article->article_date,
            'article_position'=>$article->article_position,
        );
        $rowSet = $this->tableGateway->select($judge_article);
        $row = $rowSet->current();
        if(!$row){
            $insert_data = array(
                'article_link'=>$article->article_link,
                'article_date'=>$article->article_date,
                'article_position'=>$article->article_position,
                'article_title'=>$article->article_title,
                'article_read'=>$article->article_read,
            );
            $this->tableGateway->insert($insert_data);
        }
    }

    public function findArticleId(Article $article){
        $judge_article = array(
            'article_link'=>$article->article_link,
            'article_date'=>$article->article_date,
            'article_position'=>$article->article_position,
        );
        $rowSet = $this->tableGateway->select($judge_article);
        $row = $rowSet->current();
        if($row){
            return $row->article_id;
        }else{
            return false;
        }
    }

}