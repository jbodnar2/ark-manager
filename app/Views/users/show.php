<?php require_once __DIR__ . '/../partials/head.php'; ?>

<style nonce="<?= htmlspecialchars(CSP_NONCE, ENT_QUOTES) ?>"></style>



<div class="main-grid">
    <header class="header">
        <div class="group">
            <h1 class="header__title">
                <span class="icon icon--shield"></span> <?= $page_title ?>
            </h1>
        </div>
        <div class="header__userinfo">
            <span class="userinfo__name">
                <?= $current_user['first_name'] .
                    ' ' .
                    $current_user['last_name'] ?>
            </span>

            <span class="userinfo__role"> <?= $current_user['role'] ?> </span>
        </div>
    </header>

    <div class="sidebar">
        <nav class="sidebar__nav">
            <ul class="nav__list">
                <?php if ($is_viewer): ?>
                <li class="nav__item">
                    <a href="/dashboard" class="nav__link">Dashboard</a>
                </li>
                <?php endif; ?> <?php if ($is_admin): ?>
                <li class="nav__item">
                    <a href="/users" class="nav__link">Manage Users</a>
                </li>
                <li class="nav__item">
                    <a href="/naans" class="nav__link">Manage NAANs</a>
                </li>
                <li class="nav__item">
                    <a href="/shoulders" class="nav__link">Manage Shoulders</a>
                </li>
                <?php endif; ?> <?php if ($is_user): ?>
                <li class="nav__item">
                    <a href="/arks" class="nav__link">Manage ARKs</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <form action="/logout" class="sidebar__form logout-form" method="POST">
            <?= \App\Core\Security::csrfField() ?>
            <input
                type="submit"
                value="Logout"
                class="logout-form__button btn btn--ghost"
            />
        </form>
    </div>

    <main class="main">

        <div class="card-surface">



        <form action="void(0)">
            <?php App\Core\Security::csrfField(); ?>

            <div class="dialog__content dialog__content--grid">

                <div class="form__field">



                    <label for="first_name">First Name</label>


                    <button class="btn icon-only">
                        <span class="icon icon--lock"></span>
                    </button>



                    <input disabled type="text" name="first_name" id="first_name" value=<?= htmlspecialchars(
                        $user->first_name,
                        ENT_QUOTES,
                    ) ?>>
                </div>

                <div class="form__field">
                    <label for="last_name">First Name</label>
                    <input disabled type="text" name="first_name" id="last_name" value=<?= htmlspecialchars(
                        $user->last_name,
                        ENT_QUOTES,
                    ) ?>>
                </div>

                <div class="form__field">
                    <label for="first_name">Role</label>
                    <input disabled type="text" name="first_name" id="first_name" value=<?= $user->last_name ?>>
                    <select name="role" id="role">
                        <option value="admin"><?= $user->role ?></option>
                        <option value="viewer">User</option>
                        <option value="viewer">Viewer</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>



            </div>
        </form>
        </div>


    </main>

    <footer class="footer"></footer>
</div>
<?php require_once __DIR__ . '/../partials/foot.php'; ?>
