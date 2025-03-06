<?php

namespace App\Modules\News\Models;

use App\Core\Model;
use App\Core\Database;

class NewsModel extends Model 
{
    protected string $table = "news";
    protected string $primaryKey = "id";

    public function __construct(Database $db) 
    {
        parent::__construct($db);
    }

    public function getAllNews(): array 
    {
        $news = $this->all();

        usort($news, function ($a, $b) {
            return strtotime($b['publication_date']) - strtotime($a['publication_date']);
        });

        return $news;
    }
}