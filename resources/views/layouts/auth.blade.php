<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @include('includes.head')
</head>
<body>
  @include('includes.preloader')

  @yield('content')

  @include('includes.script')

  @yield('script')
</body>
 
</html>