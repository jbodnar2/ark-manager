<?php
declare(strict_types=1);

class UserController
{
    private UserRepository $userRepo;
    private AuthService $authService;

    public function __construct(
        UserRepository $userRepo,
        AuthService $authService,
    ) {
        $this->userRepo = $userRepo;
        $this->authService = $authService;
    }

    private const DEFAULT_PAGE_TITLE = 'Manage Users';

    public function getView(): void
    {
        // Get all the users
        $users = $this->userRepo->getAllUsers();

        // User display title
        $user_display_title = 'All Active &amp; Inactive Users';

        // Set the page title
        $page_title = self::DEFAULT_PAGE_TITLE;

        // Extract info about current user
        $user = $_SESSION['user'] ?? [];
        $is_admin = $this->authService->hasRole('admin');
        $is_user = $this->authService->hasRole('user');
        $is_viewer = $this->authService->hasRole('viewer');

        // Load the view file
        require_once __DIR__ . '/pages/users.php';
    }

    public function getUserJSON(): void
    {
        $user_id = (int) ($_GET['id'] ?? 0);

        if (!$user_id) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
            exit();
        }

        $user = $this->userRepo->findById((int) $user_id);

        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit();
        }

        unset($user['password_hash'], $user['api_token']);

        header('Content-Type: application/json');
        echo json_encode($user);
        exit();
    }

    public function addUser(): void
    {
        if (
            !$this->authService->isLoggedIn() ||
            !$this->authService->hasRole('admin')
        ) {
            http_response_code(403);
            exit('Forbidden');
        }

        // 2. Data Collection
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_pwd'] ?? '';

        // 3. Validation (Controller handles flow, not SQL)
        if ($password !== $confirm) {
            $_SESSION['error_message'] = 'Passwords do not match.';
            header('Location: /users');
            exit();
        }

        // 4. Data Interaction (Repository call)
        try {
            $this->userRepo->createUser(
                $username,
                $first,
                $last,
                $email,
                $password,
                $role,
            );
            $_SESSION['add-user']['success_message'] =
                'User created successfully.';
        } catch (InvalidArgumentException $e) {
            $_SESSION['add-user']['error_message'] = $e->getMessage();
        } catch (PDOException $e) {
            $_SESSION['add-user']['error_message'] =
                'A database error occurred.';
        }

        header('Location: /users');
        exit();
    }

    public function generateUserToken(): void
    {
        if (
            !$this->authService->isAuthorized() ||
            !$this->authService->hasRole('admin')
        ) {
            http_response_code(403);
            exit('Forbidden');
        }

        $user_id = (int) ($_POST['user_id'] ?? 0);

        if ($user_id <= 0) {
            $_SESSION['error_message'] = 'Invalid User ID.';
            header('Location: /users');
            exit();
        }

        $rawToken = bin2hex(random_bytes(32));

        // 3. Save to Database
        try {
            $this->userRepo->updateToken($user_id, $rawToken);

            // 4. Store in session ONLY ONCE to display to the admin
            $_SESSION['new_api_token'] = [
                'user_id' => $user_id,
                'token' => $rawToken,
            ];
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Failed to generate token.';
        }

        header('Location: /users');
        exit();
    }

    public function revokeUserToken(): void
    {
        if (!$this->authService->hasRole('admin')) {
            http_response_code(403);
            exit('Forbidden');
        }

        $user_id = (int) ($_POST['user_id'] ?? 0);

        if ($user_id > 0) {
            $this->userRepo->revokeToken($user_id);
            $_SESSION['success_message'] = 'API token revoked successfully.';
        }

        header('Location: /users');
        exit();
    }
}

// curl -i -H "Authorization: Bearer your_64_character_token_here" \
//     -H "Accept: application/json" \
//     "https://manager.test/api/user?id=1"
//
//  curl -H "Authorization: Bearer your_64_character_token_here" \
//     -H "Accept: application/json" \
//     "https://manager.test/api/user?id=1"
