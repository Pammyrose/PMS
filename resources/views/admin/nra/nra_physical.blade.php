    
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (NRA) - PMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/nra/nra_physical.css') }}">
</head>

<body class="bg-light">

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-3">

            @include('admin.nra.partials.nra_physical_header')

            <div class="bg-white rounded shadow p-3">
                @include('admin.nra.partials.nra_physical_tabs')
                @include('admin.nra.partials.nra_physical_summary')
                @include('admin.nra.partials.nra_physical_toolbar')

                    @include('admin.nra.partials.nra_physical_table')
                </div>

        </main>
    </div>


    @include('admin.nra.partials.nra_physical_main_scripts')

    @include('admin.nra.partials.nra_physical_modals')

    @include('admin.nra.partials.nra_physical_modal_scripts')

</body>

</html>
