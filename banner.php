<!-- banner.php -->
<header
    id="hero"
    class="h-[40vh] mt-4 rounded-lg shadow-lg mb-4 bg-center bg-cover relative"
    style="
    background-image: url('/img/ticketman_wallpaper.png');
    background-size: 100%;
  ">
    <!-- Overlay -->
    <div id="hero-overlay" class="absolute inset-0 bg-black opacity-30 rounded-lg"></div>
</header>

<script>
    const hero = document.getElementById('hero');
    const overlay = document.getElementById('hero-overlay');
    const maxZoom = 1.05; // Max zoom i scale (105%)
    const maxFade = 0.3; // Start-opa på overlay

    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const h = hero.offsetHeight;
        // Progress från 0 → 1 när du scrollat lika mycket som headerns höjd
        const progress = Math.min(scrolled / h, 1);

        // Beräkna zoom: 1 → maxZoom
        const zoom = 1 + (maxZoom - 1) * progress;
        hero.style.backgroundSize = `${zoom * 100}%`;

        // Overlay opacity: maxFade → 0
        const newOpacity = maxFade * (1 - progress);
        overlay.style.opacity = newOpacity;
    });
</script>
<div class="mx-auto rounded-lg bg-gray-800 shadow-lg mt-4 mb-4 font-semibold p-6">
    <nav class="flex flex-wrap justify-center gap-4">
        <a href="dashboard.php"
            class="flex items-center px-6 py-4 bg-gray-200 bg-opacity-20 hover:bg-opacity-40 rounded-lg shadow-lg transition">
            <i class="fas fa-tachometer-alt mr-2"></i>
            Dashboard
        </a>
        <a href="edit.php"
            class="flex items-center px-6 py-4 bg-gray-200 bg-opacity-20 hover:bg-opacity-40 rounded-lg shadow-lg transition">
            <i class="fas fa-cog mr-2"></i>
            Manage Event
        </a>
        <a href="check.php"
            class="flex items-center px-6 py-4 bg-gray-200 bg-opacity-20 hover:bg-opacity-40 rounded-lg shadow-lg transition">
            <i class="fas fa-user-check mr-2"></i>
            Check In Guests
        </a>
        <a href="logout.php"
            class="flex items-center px-6 py-4 bg-gray-200 bg-opacity-20 hover:bg-opacity-40 rounded-lg shadow-lg transition">
            <i class="fas fa-sign-out-alt mr-2"></i>
            Log Out
        </a>
    </nav>
</div>