<header class="bg-white shadow-sm z-20 sticky top-0">
  <div class="flex items-center justify-between px-6 py-3.5">
    <button id="toggleSidebar" class="lg:hidden text-2xl text-gray-700 focus:outline-none">
      <i class="fa-solid fa-bars"></i>
    </button>



    @auth
      <div class="flex items-center gap-3 ml-auto">
        <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium">
          @php
            $name = trim(Auth::user()->name);
            $parts = preg_split('/\s+/', $name);

            $firstInitial = strtoupper(substr($parts[0], 0, 1));
            $lastInitial = count($parts) > 1
              ? strtoupper(substr($parts[count($parts) - 1], 0, 1))
              : '';
          @endphp

          {{ $firstInitial . $lastInitial }}
        </div>

        <div class="hidden sm:block text-right">
          <p class="text-sm font-small">{{ Auth::user()->role }}</p>
                    <p class="text-sm font-medium">{{ Auth::user()->name }}</p>
        </div>
      </div>
    @endauth

  </div>
</header>