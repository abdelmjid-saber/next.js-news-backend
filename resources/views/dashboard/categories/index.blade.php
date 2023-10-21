@seoTitle(__('main.categories'))

<x-dashboard-layout>
    {{-- Head --}}
    <div class="flex justify-between items-end mb-4">
        <div>
            <nav class="fi-breadcrumbs mb-2 hidden sm:block">
                <ul class="flex flex-wrap items-center gap-x-2">
                    <li class="flex gap-x-2">
                        <Link href="{{ route('dashboard.categories.index') }}"
                            class="text-sm font-medium text-gray-500 outline-none transition duration-75 hover:text-gray-700 focus:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-gray-200">
                            {{ __('main.categories') }}
                        </Link>
                    </li>
                    <li class="flex items-center gap-x-2">
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-gray-500 text-xs rtl:rotate-180"></i>
                        <a href="#" class="text-sm font-medium text-gray-500 outline-none transition duration-75 hover:text-gray-700 focus:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-gray-200">
                            @lang('main.list')
                        </a>
                    </li>
                </ul>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                {{ __('main.categories') }}
            </h1>
        </div>
        <div>
            @can('create categories')
                <x-btn-link href="#create">
                    @lang('main.add_new')
                </x-btn-link>
            @endcan
        </div>
    </div>
    {{-- Create Modal --}}
        @can('create categories')
            <x-splade-modal name="create">
                <x-splade-form :action="route('dashboard.categories.store')" method="POST" class="space-y-4">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                        @lang('main.add_new')
                    </h3>
                    <x-splade-input name="name" label="{{ __('main.name') }}" required />
                    <x-splade-input name="slug" label="{{ __('main.slug') }}" required />
                    <x-splade-textarea name="description" label="{{ __('main.description') }}" maxlength="158" autosize required />
                    <x-splade-select name="status" :label="__('main.status')" choices required>
                        <option value="" selected disabled>{{ __('main.select') }}</option>
                        <option value="Published" {{ old('status') == 'Published' ? 'selected' : ''  }}>{{ __('main.published') }}</option>
                        <option value="Draft" {{ old('status') == 'Draft' ? 'selected' : ''  }}>{{ __('main.draft') }}</option>
                    </x-splade-select>
                    <x-splade-submit :label="__('main.submit')" />
                </x-splade-form>
            </x-splade-modal>
        @endcan
    {{-- Content --}}
    <x-section-content>
        <x-splade-table :for="$categories">
            <x-splade-cell action as="$category">
                {{-- Edit --}}
                @can('update categories')
                    <x-nav-link href="#update{{ $category->id }}"> 
                        @lang('main.edit')
                    </x-nav-link>
                @endcan

                {{-- Update Modal --}}
                @can('update categories')
                    <x-splade-modal name="update{{ $category->id }}">
                        <x-splade-form :action="route('dashboard.categories.update', $category)" method="PUT" :default="$category" class="space-y-4">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                                @lang('main.edit')
                            </h3>
                            <x-splade-input name="name" label="{{ __('main.name') }}" required />
                            <x-splade-textarea name="description" label="{{ __('main.description') }}" maxlength="158" autosize required />
                            <x-splade-select name="status" :label="__('main.status')" choices required>
                                <option value="" selected disabled>{{ __('main.select') }}</option>
                                <option value="Published" {{ $category->status == 'Published' ? 'selected' : ''  }}>{{ __('main.published') }}</option>
                                <option value="Draft" {{ $category->status == 'Draft' ? 'selected' : ''  }}>{{ __('main.draft') }}</option>
                                </x-splade-select>
                            <x-splade-submit :label="__('main.update')" />
                        </x-splade-form>
                    </x-splade-modal>
                @endcan

                {{-- Delete --}}
                @can('delete categories')
                    <x-nav-link href="{{ route('dashboard.categories.destroy', $category) }}" method="DELETE" confirm="{{ __('main.confirm_delete') }}" confirm-text="{{ __('main.confirm_text_delete') }}" class="text-red-600 dark:text-red-600"> 
                        @lang('main.delete')
                    </x-nav-link>
                @endcan
            </x-splade-cell>
        </x-splade-table>
    </x-section-content>
</x-dashboard-layout>