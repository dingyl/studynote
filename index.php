<form method="post" action="" enctype="multipart/form-data">
    <input type="file" name="image[]"/>
    <input type="file" name="image[]"/>

    <input type="text" name="name[]"/>
    <input type="text" name="name[]"/>

    <input type="submit">

</form>

<?php

echo "<pre>";

print_r($_FILES);
print_r($_POST);
