<footer id="main-footer" class="main-footer">
    <div class="pull-right hidden-xs">
        <b>
            {{ __("ver") }}
        </b>
        {{ config('laravel-enso.version') }}
    </div>
    <strong>
        {{ __("Copyright © 2016") }}
        <a href="{{ config('app.url') }}">
            {{ config('app.name') }}
        </a>
    </strong>
    {{ __("All rights reserved.") }}
</footer>