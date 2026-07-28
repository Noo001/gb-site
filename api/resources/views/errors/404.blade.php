@if (request()->is('admin*'))
    @include('errors.404-admin')
@else
    @include('errors.404-site')
@endif
