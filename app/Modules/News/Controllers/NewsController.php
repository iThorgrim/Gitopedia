<?php

namespace App\Modules\News\Controllers;

use App\Core\Controller;

require_once ROOT_PATH . '/app/Modules/News/Models/NewsModel.php';
use App\Modules\News\Models\NewsModel;

class NewsController extends Controller 
{
    protected NewsModel $newsModel;

    public function __construct(\App\Core\Application $app) 
    {
        parent::__construct($app);
        
        $this->newsModel = new NewsModel($this->app->getDatabase());
    }

    public function index(): string 
    {
        return $this->viewWithLayout('index', 'layout', [
            'title' => 'Actualités - Gitopedia',
            'news_list' => $this->newsModel->getAllNews() 
        ]);
    }

    public function view_news_by_id(int $id): string
    {
        $news = $this->newsModel->find($id);
        
        if (!$news) {
            return $this->index();
        }

        return $this->viewWithLayout('view', 'layout', [
            'title' => $news['title'] . ' - Gitopedia',
            'news' => $news
        ]);
    }
}