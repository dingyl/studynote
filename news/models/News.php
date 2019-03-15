<?php

namespace models;

class News extends BaseModel
{

    public static function import($url)
    {
        # 获取内容
        $html = httpGet($url);
        preg_match_all('/<ul class="list_009">(.*?)<\/ul>/si', $html, $matches);
        $li_str = $matches[1][0];
        preg_match_all('/<li>(.*?)<\/li>/si', $li_str, $matche_lis);
        foreach ($matche_lis[1] as $matche_li) {
            preg_match_all('/<a href="(.*?)"[^>]*>(.*?)<\/a><span>\((.*?)\)<\/span>/si', $matche_li, $data);
            $content_url = $data[1][0];
            $content = self::grabContent($content_url);
            $title = $data[2][0];
            $created_at_text = $data[3][0];
            $created_at_str = preg_replace('/年|月/', '-', $created_at_text);
            $created_at = strtotime(str_replace('日', '', $created_at_str));
            $news = new News();
            $news->title = $title;
            $news->content = $content;
            $news->created_at = $created_at;
            $news->save();
        }
    }

    public static function grabContent($url)
    {
        $html = httpGet($url);
        # 获取正文
        preg_match_all('/<div class="article"[^>]*>(.*?)<\/div>/si', $html, $matches);
        return $matches[1][0];
    }
}