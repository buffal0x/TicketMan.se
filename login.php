<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        die("All fields are required.");
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            die("Invalid email or password.");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<title><?php echo $title; ?> Login </title>
<?php include "head.php"; ?>


<body class="bg-gray-900 text-gray-200 font-sans leading-normal tracking-normal w-9/12 mx-auto">

    <?php include "header.php"; ?>
    <?php include "navbar.php"; ?>



    <!-- Login Card -->
    <div class="w-full bg-gray-800 rounded-xl shadow-2xl p-8 mt-4 mb-4 space-y-6">
        <div class="w-2/4 mx-auto rounded-lg shadow-lg bg-gray-900 p-6">
            <form action="login.php" method="POST" class="space-y-5 p-4">

                <!-- Email field -->
                <div class="relative">
                    <label for="email" class="sr-only">Email</label>
                    <span class="absolute inset-y-0 left-4 flex items-center text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Email"
                        required
                        class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
                </div>

                <!-- Password field -->
                <div class="relative">
                    <label for="password" class="sr-only">Lösenord</label>
                    <span class="absolute inset-y-0 left-4 flex items-center text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Lösenord"
                        required
                        class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
                </div>

                <!-- Submit button -->
                <button
                    type="submit"
                    class="w-full py-3 bg-blue-700 text-white font-semibold rounded-lg shadow-lg 
               hover:bg-blue-800 transition">
                    Logga in
                </button>
            </form>

            <p class="text-center text-gray-400">
                Har du inget konto?
                <a href="register.php" class="text-indigo-400 hover:underline">Registrera dig här</a>
            </p>
        </div>
        
    </div>
    <?php include "footer.php"; ?>

</body>

</html>