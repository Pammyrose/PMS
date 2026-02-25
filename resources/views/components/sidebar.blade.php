<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Accomplishment Monitoring System • Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1e40af',      // blue-800
            primarydark: '#1e3a8a',   // blue-900
            accent: '#3b82f6',        // blue-500
          }
        }
      }
    }
  </script>
  <!-- For icons (optional) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<style>
  /* Modern scrollbar */
  .scrollbar-modern::-webkit-scrollbar {
    width: 6px;
  }

  .scrollbar-modern::-webkit-scrollbar-track {
    background: transparent;
  }

  .scrollbar-modern::-webkit-scrollbar-thumb {
    background-color: #60a5fa;
    /* blue-400 */
    border-radius: 10px;
  }

  .scrollbar-modern::-webkit-scrollbar-thumb:hover {
    background-color: #3b82f6;
    /* blue-500 */
  }

  /* JS controlled hidden state for large screens */
  #sidebar.js-hidden {
    transform: translateX(-100%) !important;
  }

  /* When sidebar is collapsed, remove the left margin from main content on large screens */
  @media (min-width: 1024px) {
    .sidebar-collapsed {
      margin-left: 0 !important;
    }
  }

  /* Toggle button padding on md and a smooth shift when sidebar toggles */
  #toggleSidebar {
    transition: margin 0.2s ease, padding 0.2s ease;
  }
  @media (min-width: 768px) {
    #toggleSidebar { padding-left: 0.5rem; padding-right: 0.5rem; }
  }
  @media (min-width: 1024px) {
    /* When sidebar visible, move toggle button to the right edge of the sidebar */
    #toggleSidebar.shift-right { margin-left: 16rem; }
    #toggleSidebar.shift-right { transform: translateX(0); }
  }
</style>

<body class="bg-gray-50 text-gray-800 antialiased">

  <!-- SIDEBAR -->
  <aside id="sidebar"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-gradient-to-b from-primary to-primarydark text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

    <!-- Logo / Brand -->
    <div class="p-6 border-b border-blue-800/40">
      <h1 class="text-xl font-bold tracking-tight text-center">
        <div class="flex justify-center items-center h-14 mr-2">
          <img src="{{ asset('denr_logo.png') }}" class="h-14" />
        </div>

        DENR-CAR PMS
      </h1>
      <p class="text-blue-300 text-sm mt-1">Performance Monitoring System</p>
    </div>

    <!-- Navigation -->
    <nav class="mt-6 px-3 space-y-1 ">
      <a href="{{ route('dashboard') }}"
        class="flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('dashboard') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
        <i class="fa-solid fa-gauge mr-3 w-5 text-center"></i>
        Dashboard
      </a>
      <a href="{{ route('programs') }}"
        class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('programs') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
        <i class="fa-solid fa-folder mr-3 w-5 text-center"></i>
        Programs
      </a>

      <details class="group" {{ request()->routeIs(['gass_physical', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing']) ? 'open' : '' }}>

        <summary class="flex items-center px-4 py-3 rounded-lg cursor-pointer list-none
  {{ request()->routeIs(['gass_physical', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'])
  ? 'bg-blue-500'
  : 'hover:bg-blue-500' }}">


          <i class="fa-solid fa-layer-group mr-3 w-5 text-center"></i>
          Fields
          <i class="fa-solid fa-chevron-down ml-auto transition-transform group-open:rotate-180"></i>
        </summary>

        <div class="ml-6 mt-2 space-y-1 max-h-[360px] overflow-y-auto pr-2 scrollbar-modern">


          <a href="{{ route('gass_physical') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('gass_physical') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-briefcase mr-3 w-5 text-center"></i> GASS
          </a>

          <a href="{{ route('sto') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('sto') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-gears mr-3 w-5 text-center"></i> STO
          </a>

          <a href="{{ route('enf') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('enf') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-shield-halved mr-3 w-5 text-center"></i> ENF
          </a>

          <a href="{{ route('pa') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('pa') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-bullhorn mr-3 w-5 text-center"></i> PA
          </a>

          <a href="{{ route('engp') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('engp') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-helmet-safety mr-3 w-5 text-center"></i> ENGP
          </a>

          <a href="{{ route('lands') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('lands') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-map mr-3 w-5 text-center"></i> LANDS
          </a>

          <a href="{{ route('soilcon') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('soilcon') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-seedling mr-3 w-5 text-center"></i> SOILCON
          </a>

          <a href="{{ route('nra') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('nra') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-mountain-sun mr-3 w-5 text-center"></i> NRA
          </a>

          <a href="{{ route('paria') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('paria') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-paw mr-3 w-5 text-center"></i> PARIA
          </a>

          <a href="{{ route('cobb') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('cobb') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-people-group mr-3 w-5 text-center"></i> COBB
          </a>

          <a href="{{ route('continuing') }}"
            class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('continuing') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
            <i class="fa-solid fa-arrows-rotate mr-3 w-5 text-center"></i> CONTINUING
          </a>

        </div>
      </details>


      <a href="{{ route('user') }}"
        class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-500 {{ request()->routeIs('user') ? 'bg-blue-500' : 'hover:bg-blue-500' }}">
        <i class="fa-solid fa-users-gear mr-3 w-5 text-center"></i>
        Users & Roles
      </a>
    </nav>

    <div class="absolute bottom-6 left-0 right-0 px-4">
      <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-500 transition-colors">
        <i class="fa-solid fa-right-from-bracket mr-3 w-5 text-center"></i>
        Sign Out
      </a>

      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div id="mainContent" class="lg:ml-64 min-h-screen flex flex-col">




  </div>

  <!-- Mobile sidebar backdrop -->
  <div id="backdrop" class="fixed inset-0 bg-black/50 z-20 lg:hidden hidden transition-opacity duration-300"></div>

  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const backdrop = document.getElementById('backdrop');
    const mainContent = document.getElementById('mainContent');

    const handleToggle = () => {
      // Toggle both the Tailwind utility and a JS-specific class so it works across breakpoints
      sidebar.classList.toggle('-translate-x-full');
      sidebar.classList.toggle('js-hidden');

      // Toggle backdrop for small screens
      if (backdrop) backdrop.classList.toggle('hidden');

      // For large screens, collapse or expand the main content margin
      if (window.innerWidth >= 1024 && mainContent) {
        mainContent.classList.toggle('sidebar-collapsed');
      }
      // Update toggle button position after changing sidebar state
      updateTogglePosition();
    };

    const updateTogglePosition = () => {
      if (!toggleBtn) return;
      // On desktop, shift the toggle to the right when sidebar is visible
      if (window.innerWidth >= 1024) {
        if (!sidebar.classList.contains('js-hidden')) {
          toggleBtn.classList.add('shift-right');
        } else {
          toggleBtn.classList.remove('shift-right');
        }
      } else {
        // remove desktop shift classes on smaller screens
        toggleBtn.classList.remove('shift-right');
      }
      // ensure md padding active when width >= 768
      if (window.innerWidth >= 768) {
        toggleBtn.classList.add('md-px-2');
      } else {
        toggleBtn.classList.remove('md-px-2');
      }
    };

    if (toggleBtn) toggleBtn.addEventListener('click', handleToggle);

    // Update position on load and resize
    updateTogglePosition();
    window.addEventListener('resize', updateTogglePosition);

    if (backdrop) {
      backdrop.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.add('js-hidden');
        backdrop.classList.add('hidden');
        if (mainContent) mainContent.classList.add('sidebar-collapsed');
      });
    }

    // Optional: close sidebar on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.add('js-hidden');
        if (backdrop) backdrop.classList.add('hidden');
        if (mainContent) mainContent.classList.add('sidebar-collapsed');
      }
    });
  </script>

  <style>
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in {
      animation: fadeInUp 0.6s ease-out forwards;
    }
  </style>

</body>

</html>