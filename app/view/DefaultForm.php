<?php

use app\controller\DefaultController;

/**
 * @var DefaultController $this
 */
?>
<form action="" method="post">
    <input type="number" name="default[inn]" placeholder="ИНН" value="<?= $this->inn ?>"/>
    <input type="submit" value="Найти"/>
</form>