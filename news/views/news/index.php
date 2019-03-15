<style>

    * {
        margin: 0;
        padding: 0
    }

    table {
        width: 100%;
        text-align: center;
    }

    table > tr {
        border-bottom: 1px solid #cccccc;
    }
</style>

<a href="/news/create">创建</a>

<table>
    <tr>
        <th>id</th>
        <th>标题</th>
        <th>内容</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    <?php foreach ($news as $item) { ?>
        <tr>
            <td><?php echo $item->id ?></td>
            <td><?php echo $item->title ?></td>
            <td><?php echo $item->content ?></td>
            <td><?php echo date('Y-m-d H:i', $item->created_at) ?></td>
            <td>
                <a href="/news/update/<?php echo $item->id ?>">更新</a>
                <a href="/news/delete/<?php echo $item->id ?>">删除</a>
            </td>
        </tr>
    <?php } ?>
</table>

