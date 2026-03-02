<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="/assets/css/main.css">
	<link rel="shortcut icon" href="/assets/img/trees.svg" type="image/x-icon">
	<title>Manager Login</title>
</head>
<body>

    <div class="container">
        <!-- Begin .container -->
        <header>
    <h1>Manager Login</h1>
        </header>

        <main class="main">
            <form action="/auth" method="post">
                <div class="form-field">
                    <label for="username">User Name</label>
                    <input type="text" name="username" id="username">
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password">
                </div>
                <div class="form-field d-flex flex-row gap-xl">
                    <input class="btn btn--primary btn--lg" type="submit" value="login">
                    <input class="btn btn--primary btn--lg" type="reset" value="clear">
                </div>
            </form>
            <div class="alert alert--warning">
                <div class="alert__content">
                <div class="alert__title">Info</div>
                <div class="alert__message">



                </div>



                    </div>

                </div>

        </main>
        <!-- End .container -->
    </div>


    <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
