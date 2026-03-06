<?php

declare(strict_types=1);

require_once __DIR__ . '/../setup.php';
?>

<pre>
<?php var_dump($_SESSION); ?>

<h1>Manage Shoulders</h1>
<p>Welcome back, <?php echo htmlspecialchars(
    $_SESSION['user']['first_name'],
); ?>!</p>
<p>Your role is: <?php echo htmlspecialchars($_SESSION['user']['role']); ?></p>
<form action="/logout" class="sidebar__form logout-form" method="POST">
    <?php echo csrf_field(); ?>
    <input type="submit" value="Logout" class="logout-form__button btn btn--ghost">
</form>
<br/>
</pre>
