<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        die("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword]);

        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            die("Email already registered.");
        }
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<title><?php echo $title; ?> Register </title>
<?php include "head.php"; ?>

<body class="bg-gray-900 text-gray-200 font-sans leading-normal tracking-normal w-9/12 mx-auto">

    <?php include "header.php"; ?>
    <?php include "navbar.php"; ?>


    <div class="w-full bg-gray-800 rounded-xl shadow-2xl p-8 mt-4 mb-4 space-y-6">
        <div class="w-2/4 mx-auto rounded-lg shadow-lg bg-gray-900 p-6">
            <form action="register.php" method="POST" class="space-y-5 p-4">

                <!-- Name field -->
                <div class="relative">
                    <label for="name" class="sr-only">Namn</label>
                    <span class="absolute inset-y-0 left-4 flex items-center text-gray-400">
                        <i class="fas fa-user"></i>
                    </span>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Namn"
                        required
                        class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600
             focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
                </div>

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

                <!-- Confirm Password field -->
                <div class="relative">
                    <label for="confirm_password" class="sr-only">Bekräfta lösenord</label>
                    <span class="absolute inset-y-0 left-4 flex items-center text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Bekräfta lösenord"
                        required
                        class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600
             focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
                </div>

                <!-- Submit button -->
                <button
                    type="submit"
                    class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg
           hover:bg-indigo-500 hover:shadow-xl transition">
                    Registrera
                </button>
            </form>

            <p class="text-center mt-4 text-gray-400">Already have an account? <a href="login.php" class="text-purple-400 hover:underline">Login here</a></p>
        </div>

    </div>
    <?php include "footer.php"; ?>

</body>



</html>