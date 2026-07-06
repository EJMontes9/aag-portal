{{--
    Despachador de tema: el footer real vive en
    components/layout/themes/{institucional,corporativo}/footer.blade.php
    Ver App\Support\Theme para como se resuelve cual esta activo.
--}}
@include('components.layout.themes.' . active_theme() . '.footer')
