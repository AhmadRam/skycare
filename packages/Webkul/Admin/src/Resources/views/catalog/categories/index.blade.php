<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.categories.index.title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.catalog.categories.index.title')
        </p>

        <div class="flex gap-x-2.5 items-center">
            <div
                class="p-[6px] items-center cursor-pointer transition-all hover:bg-gray-200 dark:hover:bg-gray-800 hover:rounded-[6px]">
                <input type="file" class="control" name="categories" id="fileInput" style="display: none"
                    onchange="submitImportForm()">
                <div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-300 font-semibold leading-[24px]"
                            onclick="openFileInput()"> Import </p>
                    </div>
                </div>
            </div>


            {!! view_render_event('bagisto.admin.catalog.categories.index.create-button.before') !!}

            @if (bouncer()->hasPermission('catalog.categories.create'))
                <a href="{{ route('admin.catalog.categories.create') }}">
                    <div class="primary-button">
                        @lang('admin::app.catalog.categories.index.add-btn')
                    </div>
                </a>
            @endif

            {!! view_render_event('bagisto.admin.catalog.categories.index.create-button.after') !!}
        </div>
    </div>

    {!! view_render_event('bagisto.admin.catalog.categories.list.before') !!}

    <x-admin::datagrid src="{{ route('admin.catalog.categories.index') }}" />

    {!! view_render_event('bagisto.admin.catalog.categories.list.after') !!}

    @pushOnce('scripts')
        <script>
            function submitImportForm() {
                var confirmation = confirm('هل أنت متأكد من استيراد هذا الملف؟');

                if (confirmation) {
                    var fileInput = document.getElementById('fileInput');
                    var formData = new FormData();
                    formData.append('categories', fileInput.files[0]);

                    fetch("{{ route('admin.catalog.categories.import') }}", {
                            method: "POST",
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': `{{ csrf_token() }}`,

                            }
                        })
                        .then(response => {}).then(data => {
                            alert('تم استيراد الملف بنجاح.');
                            window.location.reload();
                        })
                        .catch(error => {
                            alert(error);
                        });
                } else {
                    alert('تم إلغاء استيراد الملف.');
                }
            }

            function openFileInput() {
                document.getElementById('fileInput').click();
            }
        </script>
    @endPushOnce

</x-admin::layouts>
