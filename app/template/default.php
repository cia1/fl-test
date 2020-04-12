<?php

use engine\engine;

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>{{metaTitle}}</title>
    <link href="/template.css" rel="stylesheet"/>
</head>
<body>
{{pageTitle}}
<?php if (engine::issetMessage(engine::MESSAGE_TYPE_ERROR) === true) { ?>
    <div class="messageError">
        <?php foreach (engine::getMessage(engine::MESSAGE_TYPE_ERROR, true) as $item) { ?>
            <p><?= $item ?></p>
        <?php } ?>
    </div>
<?php } ?>
<?php if (engine::issetMessage(engine::MESSAGE_TYPE_SUCCESS) === true) { ?>
    <div class="messageSuccess">
        <?php foreach (engine::getMessage(engine::MESSAGE_TYPE_SUCCESS, true) as $item) { ?>
            <p><?= $item ?></p>
        <?php } ?>
    </div>
<?php } ?>
{{content}}
</body>
</html>