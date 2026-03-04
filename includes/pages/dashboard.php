<?php require_once __DIR__ . '/../partials/head.php'; ?>

    <style nonce="<?= htmlspecialchars(CSP_NONCE, ENT_QUOTES) ?>">

    .main-grid {
        display: grid;
        grid-template-columns: minmax(200px, max-content) 1fr;   /* sidebar + content */
        grid-template-rows: auto 1fr auto;                     /* header, main area, footer */
        grid-template-areas:                                   /* named areas (must be exact) */
            "header header"
            "sidebar main"
            "footer footer";
        min-height: 100vh;                                     /* let grid fill the viewport */
        width: 100%;
    }

    .header, .sidebar {
        background-color: var(--color-white);
        padding: var(--space-md);
    }

    .userinfo__name::after {
        content: " | " ;
    }


    .header {
        grid-area: header; /* place the header into the top row spanning both columns */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar {
        grid-area: sidebar;
    }

    .logo__img {

        height: 4rem;
    }

    .main {
        grid-area: main;
    }

    .footer {
        grid-area: footer;
    }

    .sidebar {

        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        gap: var(--space-xl);
    }

    .sidebar__form {
        margin-block-start: auto;
    }

    .group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2rem;
        margin-inline-start: 1rem;
    }





    </style>

    <div class="main-grid">

    <header class="header">
        <div class="group">
        <div class="sidebar__logo">
            <img src="/assets/img/trees.svg" alt="" class="logo__img">
        </div>
        <h1 class="header__title">Dashboard</h1>
        </div>
        <div class="header__userinfo">

            <span class="userinfo__name">
                <?= $_SESSION['user']['first_name'] .
                    ' ' .
                    $_SESSION['user']['last_name'] ?>
            </span>




            <span class="userinfo__role">
                <?= $_SESSION['user']['role'] ?>
            </span>
        </div>

    </header>

    <div class="sidebar">

        <nav class="sidebar__nav">
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
        <form action="/logout" class="sidebar__form sidebar__form--logout" method="POST">
            <?php echo csrf_field(); ?>
            <input type="submit" value="Logout">
        </form>
    </div>


    <main class="main"></main>


    <footer class="footer"></footer>
</div>
<?php require_once __DIR__ . '/../partials/foot.php'; ?>
