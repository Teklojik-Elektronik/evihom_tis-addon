@extends(backpack_view('blank'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h1>{{ __('messages.settings') }}</h1>
                
                @include('admin.partials.language_selector')
                
                {{-- Add more settings sections here --}}
            </div>
        </div>
    </div>
@endsection
