<?php

namespace controllers;


class News extends BaseController
{
    public function index()
    {
        $news = \models\News::find();
        $this->view['news'] = $news;
    }

    public function create()
    {
        $news = new \models\News();
        if ($this->isPost()) {
            $news->setAttributes($_POST['news']);
            $news->created_at = time();
            if ($news->save()) {
                $this->redirect('/news/index');
            }
        }
        $this->view['news'] = $news;
    }

    public function update($id)
    {
        $news = \models\News::findById($id);
        if ($this->isPost()) {
            $news->setAttributes($_POST['news']);
            if ($news->save()) {
                $this->redirect('/news/index');
            }
        }
        $this->view['news'] = $news;
    }

    public function delete($id)
    {
        $news = \models\News::findById($id);
        if ($news && $news->delete()) {
            $this->redirect('/news/index');
        } else {
            return $this->renderJson(ERROR_CODE_FAIL, '请求错误');
        }
    }
}