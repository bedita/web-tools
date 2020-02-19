<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;
?>

<h2><?= __('An Internal Error Has Occurred') ?></h2>
<p>
    <strong><?= __('Error') ?> <?= $code ?>: </strong>
    <?= h($message) ?>
</p>

<?php if (Configure::read('debug')) : ?>

    <?php if (!empty($error->queryString)) : ?>
        <p>
            <strong>SQL Query: </strong>
            <?= h($error->queryString) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($error->params)) : ?>
            <strong>SQL Query Params: </strong>
            <?php Debugger::dump($error->params) ?>
    <?php endif; ?>

    <?php if ($error instanceof Error) : ?>
            <strong>Error in: </strong>
            <?= sprintf('%s, line %s', str_replace(ROOT, 'ROOT', $error->getFile()), $error->getLine()) ?>
    <?php endif; ?>

    <p><?= str_replace("\n", '<br>', $error->getTraceAsString()) ?></p>

<?php endif; ?>
