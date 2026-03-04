<?php require_once __DIR__ . '/../partials/head.php'; ?>

    <header class="header"></header>


    <div class="sidebar">
        <div class="logo">
            <img src="/assets/img/trees.svg" alt="" class="logo__img" width=75>
        </div>
        <nav class="nav">
            <ul class="nav__list">
                <?php if (AuthController::hasRole('viewer')): ?>
                <li class="nav__item">
                    <a href="/dashboard" class="nav__link">Dashboard</a>
                </li>
                <?php endif; ?>
                <?php if (AuthController::hasRole('user')): ?>
                <li class="nav__item">
                    <a href="/arks" class="nav__link">Manage ARKs</a>
                </li>
                <?php endif; ?>
                <?php if (AuthController::hasRole('admin')): ?>
                <li class="nav__item">
                    <a href="/shoulders" class="nav__link">Manage Shoulders</a>
                </li>
                <?php endif; ?>
                <?php if (AuthController::hasRole('admin')): ?>
                <li class="nav__item">
                    <a href="/naans" class="nav__link">Manage NAANs</a>
                </li>
                <?php endif; ?>
                <?php if (AuthController::hasRole('admin')): ?>
                <li class="nav__item">
                    <a href="/users" class="nav__link">Manage Users</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <form action="/logout" class="logout" method="POST">
            <?php echo csrf_field(); ?>
            <input type="submit" value="Logout">
        </form>
    </div>


    <main class="main"></main>


    <footer class="footer"></footer>

<?php require_once __DIR__ . '/../partials/foot.php'; ?>
