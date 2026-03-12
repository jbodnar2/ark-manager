<?php require_once __DIR__ . '/../partials/head.php'; ?>
<! -- // TODO: Allow editing, deleting user. -->

<style nonce="<?= htmlspecialchars(CSP_NONCE, ENT_QUOTES) ?>">

main {
    display: flow-root;
}

</style>

<div class="main-grid">
    <header class="header">
        <div class="group">
            <h1 class="header__title">
                <span class="icon icon--shield"></span> <?= $page_title ?>
            </h1>
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
            <input
                type="submit"
                value="Logout"
                class="logout-form__button btn btn--ghost"
            />
        </form>
    </div>

    <main class="main">
        <?php if (!empty($_SESSION['add-user']['success_message'])): ?>
        <div class="alert alert--success">
            <?= htmlspecialchars(
                $_SESSION['add-user']['success_message'],
                ENT_QUOTES,
            ) ?>
        </div>
        <?php unset(
            $_SESSION['add-user']['success_message'],
        ); ?> <?php endif; ?>
        <?php if (!empty($_SESSION['add-user']['error_message'])): ?>
        <div class="alert alert--error">
            <?= htmlspecialchars(
                $_SESSION['add-user']['error_message'],
                ENT_QUOTES,
            ) ?>
        </div>
        <?php unset($_SESSION['add-user']['error_message']); ?> <?php endif; ?>

        <div class="card-surface">
            <button
                class="btn btn--primary"
                command="show-modal"
                commandfor="add-user-form"
            >
                Add User
            </button>

            <dialog id="add-user-form">
                <form action="add-user" method="post">
                    <header class="dialog__header">
                        <h2>Add User</h2>
                    </header>

                    <div class="dialog__content dialog__content--grid">
                        <?php csrf_field(true); ?>

                        <div class="form-field">
                            <label for="first_name">First Name</label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                            />
                        </div>

                        <div class="form-field">
                            <label for="last_name">Last Name</label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                            />
                        </div>

                        <div class="form-field">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" />
                        </div>

                        <div class="form-field">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" />
                        </div>

                        <div class="form-field">
                            <label for="role">Role</label>
                            <select name="role" id="role">
                                <option value="viewer">Viewer</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                            />
                        </div>

                        <div class="form-field">
                            <label for="confirm_pwd">Confirm Password</label>
                            <input
                                type="password"
                                name="confirm_pwd"
                                id="confirm_pwd"
                            />
                        </div>
                    </div>

                    <footer class="dialog__footer">
                        <input
                            class="btn btn--primary"
                            type="submit"
                            value="submit"
                        />
                        <button
                            class="btn btn--secondary"
                            formmethod="dialog"
                            value="cancel"
                        >
                            Cancel
                        </button>
                    </footer>
                </form>
            </dialog>
        </div>

        <div class="card-surface table-container">
            <table>
                <thead>
                    <caption>
                        <?= $user_display_title ?>
                    </caption>
                    <tr>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $user['last_name'],
                                ENT_QUOTES,
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $user['first_name'],
                                ENT_QUOTES,
                            ) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($user['email'], ENT_QUOTES) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($user['role'], ENT_QUOTES) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer"></footer>
</div>
<?php require_once __DIR__ . '/../partials/foot.php'; ?>
