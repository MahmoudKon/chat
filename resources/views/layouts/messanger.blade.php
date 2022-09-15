
<!DOCTYPE html>
<html lang="en">
    <!-- Head -->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1, shrink-to-fit=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Messenger</title>

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/icon.png') }}" type="image/x-icon">

        <!-- Template CSS -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('assets') }}/css/template.bundle.dark.css">
        <link rel="stylesheet" href="{{ asset('assets') }}/css/template.dark.bundle.css" media="(prefers-color-scheme: dark)">

    <body>
        <!-- Layout -->
        <div class="layout overflow-hidden">
            @include('layouts.includes.nav')

            @include('layouts.includes.side')

            <!-- Chat -->
            <main class="main is-visible" id="load-chat" data-dropzone-area="">
                @include('layouts.includes.empty')
            </main>
            <!-- Chat -->

        </div>
        <!-- Layout -->

        @include('messanger.modals')

        <!-- Scripts -->
        <script>
            const AUTH_USER_ID = {{ auth()->id() }};
            audio = new Audio(`{{ asset('assets/audios/success.mp3') }}`);
        </script>
        <script type="text/javascript" src="{{ asset('assets') }}/js/jquery-3.6.1.min.js"></script>
        <script type="text/javascript" src="{{ asset('assets') }}/js/vendor.js"></script>
        <script type="text/javascript" src="{{ asset('assets') }}/js/template.js"></script>
        <script type="text/javascript" src="{{ asset('assets') }}/js/moment.js"></script>
        <script type="text/javascript" src="{{ asset('assets') }}/js/messenger.js"></script>
    </body>
</html>
