@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        @if (!$orders)
            <div class="col-md-12 d-flex justify-content-center">
                <h5>
                    No orders found
                </h5>
            </div>
        @else
            <div class="container-xl">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <h3 class="mb-1">Success</h3>
                        <p>{{ session('success') }}</p>

                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif
                @livewire('UserAuthLedger')

            </div>
        @endif
    </div>
@endsection
