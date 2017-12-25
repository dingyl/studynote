<?php
require 'DbSearch.php';
$hosts = ['localhost:9200'];
$client =  DbSearch::getIns($hosts,'order','testtable');

//$client->save(['id'=>time(),'name'=>'“思政课居然可以这么上！”近日，上海大学一堂“时代音画”通识课上，该校社会学院和音乐学院教授联袂，用音乐旋律和历史回顾，声情并茂地讲授了“国歌如何一路走来”。整个课堂学生爆满，“蹭课族”只能席地而坐。不少学生听完课后表示，原以为沉闷闷的课堂，没想到却是热腾腾，收获满满，时间也转瞬即逝，总感觉没听够。']);
//$client->clear();
print_r($client->findAll());