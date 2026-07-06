{{--
    Despachador de tema: el header real vive en
    components/layout/themes/{institucional,gobierno}/header.blade.php
    Ver App\Support\Theme para como se resuelve cual esta activo.
--}}
@include('components.layout.themes.' . active_theme() . '.header')
