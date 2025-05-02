<?php
// konto.php
require 'config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Konto – TicketMan.se</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-p+U3Sern2ENu5PfI1NwRGsaiRU59euy5lH+wmQP0YzpYeyeK+Y6xjL+1q+ptwXQ7t5fuyCKjvkt/DBO3c1K6dA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-900 text-gray-200">

    <div class="w-full max-w-2xl bg-gray-800 rounded-xl shadow-2xl">
        <!-- Tabs -->
        <div class="flex">
            <button id="tab-login" class="w-1/2 py-4 text-center font-semibold border-b-2 border-indigo-500">
                Logga in
            </button>
            <button id="tab-register" class="w-1/2 py-4 text-center font-semibold border-b-2 border-transparent hover:border-gray-600 transition">
                Registrera
            </button>
        </div>

        <!-- Container för formulär -->
        <div id="form-container" class="p-8">
            <!-- Här laddas antingen login eller register via JS -->
        </div>
    </div>

    <script>
        // Ladda in formulär-HTML
        const loginForm = `
      <form action="login.php" method="POST" class="space-y-5">
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400"><i class="fas fa-envelope"></i></span>
          <input type="email" name="email" placeholder="Email" required
            class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
        </div>
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" placeholder="Lösenord" required
            class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
        </div>
        <button type="submit"
          class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg 
                 hover:bg-indigo-500 hover:shadow-xl transition">
          Logga in
        </button>
        <p class="text-center text-gray-400">
          Ny användare? <a href="#" id="link-to-register" class="text-indigo-400 hover:underline">Registrera</a>
        </p>
      </form>
    `;

        const registerForm = `
      <form action="register.php" method="POST" class="space-y-5">
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400"><i class="fas fa-user"></i></span>
          <input type="text" name="name" placeholder="Namn" required
            class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
        </div>
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400"><i class="fas fa-envelope"></i></span>
          <input type="email" name="email" placeholder="Email" required
            class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
        </div>
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" placeholder="Lösenord" required
            class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
        </div>
        <div class="relative">
          <span class="absolute inset-y-0 left-4 flex items-center text-gray-400"><i class="fas fa-lock"></i></span>
          <input type="password" name="confirm_password" placeholder="Bekräfta lösenord" required
            class="w-full pl-12 pr-4 py-3 bg-gray-700 text-white rounded-lg border border-gray-600 
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" />
        </div>
        <button type="submit"
          class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-lg 
                 hover:bg-indigo-500 hover:shadow-xl transition">
          Registrera
        </button>
        <p class="text-center text-gray-400">
          Redan medlem? <a href="#" id="link-to-login" class="text-indigo-400 hover:underline">Logga in</a>
        </p>
      </form>
    `;

        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        const container = document.getElementById('form-container');

        function showLogin() {
            tabLogin.classList.add('border-indigo-500');
            tabRegister.classList.remove('border-indigo-500');
            container.innerHTML = loginForm;
            bindLinks();
        }

        function showRegister() {
            tabRegister.classList.add('border-indigo-500');
            tabLogin.classList.remove('border-indigo-500');
            container.innerHTML = registerForm;
            bindLinks();
        }

        function bindLinks() {
            document.getElementById('link-to-register')?.addEventListener('click', e => {
                e.preventDefault();
                showRegister();
            });
            document.getElementById('link-to-login')?.addEventListener('click', e => {
                e.preventDefault();
                showLogin();
            });
        }

        // Init
        showLogin();
    </script>
</body>

</html>