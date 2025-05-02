<?php
require 'config.php';


?>
<!DOCTYPE html>
<html lang="en">
<title><?php echo $title; ?> Startsida </title>
<?php include "head.php"; ?>

<body class="bg-gray-900 text-gray-200 font-sans leading-normal tracking-normal w-9/12 mx-auto">

    <?php include "header.php"; ?>
    <?php include "navbar.php"; ?>

    <!-- Information Section -->
    <section class="container mx-auto px-6 py-20">
        <div class="grid md:grid-cols-3 gap-10 text-center">

            <div class="bg-gray-800 p-8 rounded-lg shadow-lg hover-scale transition-all">
                <div class="text-5xl">📅</div>
                <h2 class="text-2xl font-bold text-indigo-400 mt-4">Hantera Events</h2>
                <p class="mt-2 text-gray-400">Skapa och redigera events smidigt och snabbt.</p>
            </div>

            <div class="bg-gray-800 p-8 rounded-lg shadow-lg hover-scale transition-all">
                <div class="text-5xl">👥</div>
                <h2 class="text-2xl font-bold text-indigo-400 mt-4">Enkel Gästhantering</h2>
                <p class="mt-2 text-gray-400">Hantera gäster manuellt eller via Excel-import.</p>
            </div>

            <div class="bg-gray-800 p-8 rounded-lg shadow-lg hover-scale transition-all">
                <div class="text-5xl">📈</div>
                <h2 class="text-2xl font-bold text-indigo-400 mt-4">Detaljerad Statistik</h2>
                <p class="mt-2 text-gray-400">Få en överskådlig vy över event och deltagare.</p>
            </div>

        </div>
    </section>
    <?php include "footer.php"; ?>
</body>

</html>