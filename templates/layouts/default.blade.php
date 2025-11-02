<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="/assets/dynamiccrud.css">
    @yield('styles')
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
    
    <script src="/assets/dynamiccrud.js"></script>
    @yield('scripts')
</body>
</html>
