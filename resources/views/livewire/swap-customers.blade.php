<div class="container card">
    <style>
        .blinking-warning-circle {
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background-color: #f76707;
            animation: blink 1.5s infinite;
            display: inline-block;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            25% {
                opacity: 0.5;
            }

            50% {
                opacity: 0.2;
            }

            75% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
    <div class="card-header">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <h3 class="card-title">
                        {{ __('Swap Customer') }}
                    </h3>
                </div>
                @if (auth()->user()->role === 'admin' || auth()->user()->role === 'supplier' || auth()->user()->role === 'superAdmin')
                    {{-- <div class="m-auto d-flex align-items-center"> --}}
                    <div class="col-md-3">
                        <!-- Customer Selection -->
                        <select class="form-select form-control-solid mr-2" wire:model.change="userid">
                            <option value="" selected disabled>Select a user:</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} | {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($userid)
                        <div class="col-md-4 d-flex align-items-center justify-content-center">
                            <label class="mx-2">Swap To:</label>
                            <select class="form-select form-control-solid mr-2" wire:model.live="targetUserId" >
                                <option value="" selected disabled>Select a user:</option>
                                @foreach ($users as $user)
                                    @if ($user->id != $userid)
                                    <option value="{{ $user->id }}">{{ $user->name }} | {{ $user->email }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <button wire:click="moveSelectedCustomers" class="btn btn-primary">Swap Customers</button>
                        </div>
                    @endif
            </div>
            @endif
        </div>

        {{-- <div class="card-actions d-flex">

            <x-action.create route="{{ route('orders.create') }}" />
        </div> --}}
    </div>
    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-secondary">
                Show
                <div class="mx-2 d-inline-block">
                    <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="result per page">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="75">75</option>
                        <option value="150">150</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>
            <div class="ms-auto text-secondary">
                Search:
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm"
                        aria-label="Search invoice">
                </div>
            </div>

        </div>
    </div>


    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <x-spinner.loading-spinner />


    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col" class="align-middle text-center">
                        <input style="height: 15px; width: 15px;" type="checkbox" wire:model="selectAll" />
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('id')" href="#" role="button">
                            {{ __('Id') }}
                            @include('inclues._sort-icon', ['field' => 'id'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('name')" href="#" role="button">
                            {{ __('Name') }}
                            @include('inclues._sort-icon', ['field' => 'name'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('email')" href="#" role="button">
                            {{ __('Email') }}
                            @include('inclues._sort-icon', ['field' => 'email'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('address')" href="#" role="button">
                            {{ __('Address') }}
                            @include('inclues._sort-icon', ['field' => 'address'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('phone')" href="#" role="button">
                            {{ __('Phone') }}
                            @include('inclues._sort-icon', ['field' => 'phone'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('store_address')" href="#" role="button">
                            {{ __('Shop name') }}
                            @include('inclues._sort-icon', ['field' => 'store_address'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        <a wire:click.prevent="sortBy('customer_type')" href="#" role="button">
                            {{ __('Customer Type') }}
                            @include('inclues._sort-icon', ['field' => 'customer_type'])
                        </a>
                    </th>
                    <th scope="col" class="align-middle text-center">
                        {{ __('Action') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="align-middle text-center">
                            <input style="height: 15px; width: 15px;" type="checkbox"
                                wire:model="selectedCustomers" value="{{ $customer->id }}">
                        </td>
                        <td class="align-middle text-center">
                            {{ $loop->index + 1 }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->name }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->email }}
                        </td>
                        <td class="align-middle text-center">
                            {{ Str::limit($customer->address, 20, '...') }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->phone }}
                        </td>
                        <td class="align-middle text-center">
                            {{ $customer->store_address }}
                        </td>
                        <td class="align-middle text-center">
                            <x-status dot
                                color="{{ $customer->customer_type === \App\Enums\CustomerType::Normal ? 'green' : ($customer->customer_type === \App\Enums\CustomerType::Regular ? 'orange' : '') }}"
                                class="text-uppercase">
                                {{ $customer->customer_type->label() }}
                            </x-status>
                        </td>
                        <td class="align-middle text-center">
                            <x-button.show class="btn-icon" route="{{ route('customers.show', $customer->uuid) }}" />
                            <x-button.edit class="btn-icon" route="{{ route('customers.edit', $customer->uuid) }}" />
                            <x-button.delete class="btn-icon"
                                route="{{ route('customers.destroy', $customer->uuid) }}"
                                onclick="return confirm('Are you sure to remove {{ $customer->name }} ?')" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="align-middle text-center" colspan="8">
                            No results found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>



    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            Showing <span>{{ $customers->firstItem() }}</span> to <span>{{ $customers->lastItem() }}</span> of
            <span>{{ $customers->total() }}</span> entries
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $customers->links() }}
        </ul>
    </div>
</div>
