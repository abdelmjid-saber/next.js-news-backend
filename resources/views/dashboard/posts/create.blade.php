@seoTitle(__('main.posts'))

<x-dashboard-layout>
    {{-- Head --}}
    <div class="flex justify-between items-end mb-4">
        <div>
            <nav class="fi-breadcrumbs mb-2 hidden sm:block">
                <ul class="flex flex-wrap items-center gap-x-2">
                    <li class="flex gap-x-2">
                        <Link href="{{ route('dashboard.posts.index') }}"
                            class="text-sm font-medium text-gray-500 outline-none transition duration-75 hover:text-gray-700 focus:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-gray-200">
                            {{ __('main.posts') }}
                        </Link>
                    </li>
                    <li class="flex items-center gap-x-2">
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-gray-500 text-xs rtl:rotate-180"></i>
                        <a href="#" class="text-sm font-medium text-gray-500 outline-none transition duration-75 hover:text-gray-700 focus:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-gray-200">
                            @lang('main.create')
                        </a>
                    </li>
                </ul>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                {{ __('main.create') }}
            </h1>
        </div>
        <div>
        </div>
    </div>
    {{-- Content --}}
    <x-splade-form :action="route('dashboard.posts.store')" method="POST">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-y-4 lg:gap-x-4">
            <div class="col-span-2">
                <x-section-content>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-splade-input name="title" label="{{ __('main.title') }}" required />
                            <x-splade-input name="slug" label="{{ __('main.slug') }}" required />
                        </div>
                        <x-splade-wysiwyg name="content" label="{{ __('main.content') }}" required />
                    </div>
                </x-section-content>
            </div>
            <div class="col-span-1">
                <x-section-content>
                    <div class="space-y-4">
                        <x-splade-select name="category_id" label="{{ __('main.category') }}" :options="$categories" choices required />
                        <x-splade-select name="tags[]" label="{{ __('main.tags') }}" :options="$tags" choices multiple relation />
                        <x-splade-select name="status" :label="__('main.status')" choices required>
                            <option value="" selected disabled>{{ __('main.select') }}</option>
                            <option value="Published" {{ old('status') == 'Published' ? 'selected' : ''  }}>{{ __('main.published') }}</option>
                            <option value="Pending Review" {{ old('status') == 'Pending Review' ? 'selected' : ''  }}>{{ __('main.pending_review') }}</option>
                            <option value="Draft" {{ old('status') == 'Draft' ? 'selected' : ''  }}>{{ __('main.draft') }}</option>
                            <option value="Rejected" {{ old('status') == 'Rejected' ? 'selected' : ''  }}>{{ __('main.rejected') }}</option>
                        </x-splade-select>
                        <x-splade-file name="featured_image" label="{{ __('main.featured_image') }}" filepond preview required />
                        <x-splade-textarea name="excerpt" label="{{ __('main.excerpt') }}" maxlength="158" autosize required />
                        <x-splade-submit :label="__('main.submit')" />
                    </div>
                </x-section-content>
            </div>
        </div>
    </x-splade-form>
</x-dashboard-layout>