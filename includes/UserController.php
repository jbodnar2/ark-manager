<?php
declare(strict_types=1);

class UserController
{
    private UserRepository $userRepo;
    private $auth;

    public function __construct(UserRepository $userRepo, $auth)
    {
        $this->userRepo = $userRepo;
        $this->auth = $auth;
    }

    public function store(): void
    {
        // 1. Authorization check
        if (!$this->auth->isLoggedIn() || !$this->auth->hasRole('admin')) {
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
            $_SESSION['success_message'] = 'User created successfully.';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error_message'] = $e->getMessage();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'A database error occurred.';
        }

        // 5. Response/Redirect
        header('Location: /users');
        exit();
    }
}
