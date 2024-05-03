<div class="w-50 server-messages-wrapper">
    @if (session('success'))
        <div class="mb-2 p-3 alert alert-success border border-success rounded server-message">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Успех</h6>
                <button class="btn-close close-server-message"></button>
            </div>
            <hr>
            <p>
                {!! session('success') !!}
            </p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-2 p-3 alert alert-danger border border-danger rounded server-message">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Успех</h6>
                <button class="btn-close close-server-message"></button>
            </div>
            <hr>
            <p>
                {!! session('error') !!}
            </p>
        </div>
    @endif
    @if (session('errors'))
        @php
            $errList = '';
            foreach (session('errors')->all() as $err) {
                $errList .= '<li>' . $err . '</li>';
            }
        @endphp
        <div class="mb-2 p-3 alert alert-danger border border-danger rounded alert-dismissible server-message">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Ошибка</h6>
                <button class="btn-close  close-server-message" id="closeErrors"></button>
            </div>
            <hr>
            <p>
                Возникшие ошибки:
            <ul>
                {!! $errList !!}
            </ul>
            </p>
        </div>
    @endif
</div>
