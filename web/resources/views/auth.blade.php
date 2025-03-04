@extends('base')
@section('content')
<center style="margin: 200px 0;">
    <img src="/branding/logo.png" class="w-200px mb-4">
    <h1>Bienvenid@ al Sonkoh Casino</h1>
    <p class="text-muted">Ingresa ahora mismo logueandote con Google</p>
    <div>
        <a href="/auth" class="btn btn-secondary"><div class="d-flex align-items-center gap-2"><img src="/img/google-icon.svg" style="width: 16px;"> Iniciar Sesión</div></a>
    </div>
</center>
@endsection