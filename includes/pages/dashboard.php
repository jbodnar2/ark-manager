<?php

declare(strict_types=1);

require_once __DIR__ . '/../setup.php';
?>

<pre>
<?php var_dump($_SESSION); ?>

<h1>Dashboard</h1>
<p>Welcome back, <?php echo htmlspecialchars(
    $_SESSION['user']['first_name'],
); ?>!</p>
<p>Your role is: <?php echo htmlspecialchars($_SESSION['user']['role']); ?></p>
<a href="/logout">Logout</a>
<br/>
</pre>
