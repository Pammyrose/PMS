    
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (CONTINUING) - PMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/continuing/continuing_physical.css') }}">
</head>

<body class="bg-light">

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-3">

            @include('users.continuing.partials.continuing_physical_header')

            <div class="bg-white rounded shadow p-3">
                @include('users.continuing.partials.continuing_physical_tabs')
                @include('users.continuing.partials.continuing_physical_summary')
                @include('users.continuing.partials.continuing_physical_toolbar')

                    @include('users.continuing.partials.continuing_physical_table')
                </div>

        </main>
    </div>


    @include('users.continuing.partials.continuing_physical_main_scripts')

    @include('users.continuing.partials.continuing_physical_modals')

    @include('users.continuing.partials.continuing_physical_modal_scripts')

</body>

</html>
