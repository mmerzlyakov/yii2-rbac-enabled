<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <?php

    var_dump(Yii::$app->authManager->getRoles());

    if (Yii::$app->user->can('superuser')){
        echo "\n\n\n\nSuperuser\n\n\n\n";
    }
    
    ?>

</div>
