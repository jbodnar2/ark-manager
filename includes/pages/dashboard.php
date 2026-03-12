<?php require_once __DIR__ . '/../partials/head.php'; ?>



    <style nonce="<?= htmlspecialchars(CSP_NONCE, ENT_QUOTES) ?>">


    </style>

    <div class="main-grid">

    <header class="header">
        <div class="group">
        <h1 class="header__title"><span class="icon icon--shield"></span> <?= $page_title ?></h1>
        </div>
        <div class="header__userinfo">

            <span class="userinfo__name">
                <?= $user['first_name'] . ' ' . $user['last_name'] ?>
            </span>




            <span class="userinfo__role">
                <?= $user['role'] ?>
            </span>
        </div>

    </header>

    <div class="sidebar">

        <nav class="sidebar__nav">
            <ul class="nav__list">


                <?php if ($is_viewer): ?>
                <li class="nav__item">
                    <a href="/dashboard" class="nav__link">Dashboard</a>
                </li>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <li class="nav__item">
                        <a href="/users" class="nav__link">Manage Users</a>
                    </li>
                    <li class="nav__item">
                        <a href="/naans" class="nav__link">Manage NAANs</a>
                    </li>
                    <li class="nav__item">
                        <a href="/shoulders" class="nav__link">Manage Shoulders</a>
                    </li>
                <?php endif; ?>

                <?php if ($is_user): ?>
                <li class="nav__item">
                    <a href="/arks" class="nav__link">Manage ARKs</a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
        <form action="/logout" class="sidebar__form logout-form" method="POST">
            <?php echo csrf_field(); ?>
            <input type="submit" value="Logout" class="logout-form__button btn btn--ghost">
        </form>
    </div>


    <main class="main">

        <div class="surface">

        <table>
            <thead>
                <caption>Table Title</caption>
            </thead>
            <tbody></tbody>
        </table>
        </div>

    </main>


    <footer class="footer"></footer>
</div>
<?php require_once __DIR__ . '/../partials/foot.php'; ?>
