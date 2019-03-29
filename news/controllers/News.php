<?php

namespace controllers;

class News extends BaseController
{
    /**
     * @return null
     * @throws \ReflectionException
     */
    public function index()
    {
        $news = \models\News::find();
        $this->view['news'] = $news;
        return null;
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

    /**
     * @param integer $id 编号
     * @return false|string
     * @throws \ReflectionException
     */
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
        return $this->renderView();
    }

    /**
     * 功    能: delete
     * 修改日期: 2019-03-18
     *
     * @param int $id 编号
     * @return array
     * @throws \ReflectionException
     */
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