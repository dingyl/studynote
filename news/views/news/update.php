<html>
<head>
    <style>

    </style>
</head>
<body>

<form method="post">
    <table>
        <tr>
            <td>标题</td>
            <td>
                <input type="text" name="news[title]" value="<?php echo $news->title ?>">
            </td>
        </tr>
        <tr>
            <td>内容</td>
            <td>
                <textarea name="news[content]">
                    <?php echo $news->content ?>
                </textarea>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <input type="submit" value="提交">
            </td>
        </tr>
    </table>
</form>
<script>

</script>
</body>
</html>