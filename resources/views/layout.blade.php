<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <x-connections></x-connections>
</head>

<body>

    <x-navbar></x-navbar>

    <x-msgs></x-msgs>

    @yield('body')

</body>

</html>
