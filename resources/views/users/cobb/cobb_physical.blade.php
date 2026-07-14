    
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (COBB) - PMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/cobb/cobb_physical.css') }}">
    <style>
        tr.data-row > td:nth-child(3),
        tr.data-row > td[data-dynamic-section] {
            vertical-align: top;
        }
    </style>
</head>

<body class="bg-light">

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-3">

            @include('users.cobb.partials.cobb_physical_header')

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show js-timed-alert" role="alert" data-dismiss-after="4000">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show js-timed-alert" role="alert" data-dismiss-after="6000">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="bg-white rounded shadow p-3">
                @include('users.cobb.partials.cobb_physical_tabs')
                @include('users.cobb.partials.cobb_physical_toolbar')

                    @include('users.cobb.partials.cobb_physical_table')
                </div>

        </main>
    </div>


    @include('users.cobb.partials.cobb_physical_main_scripts')

    @include('users.cobb.partials.cobb_physical_main_scripts2')

    @include('components.physical_highlight_script')

    @include('users.cobb.partials.cobb_physical_modals')

    @include('users.cobb.partials.cobb_physical_modal_scripts')

</body>

</html>
