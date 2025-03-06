<ul>
    <?php foreach ($news_list as $news): ?>
        <li><a href="/news/<?= $news['id'] ?>"><?= $news['title'] ?></a></li>
    <?php endforeach; ?>
</ul>