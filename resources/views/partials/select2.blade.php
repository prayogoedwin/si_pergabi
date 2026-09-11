@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container { width: 100% !important; }
        .select2-container--open { z-index: 2000; }
        .select2-container .select2-selection--single {
            height: 42px !important;
            border-radius: 0.5rem !important;
            border: 1px solid #d6cfc3 !important;
            background: #ffffff !important;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #0c2244 !important;
            line-height: 40px;
            padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #0c2244 transparent transparent transparent;
        }
        .select2-dropdown {
            border: 1px solid #d6cfc3 !important;
            border-radius: 0.5rem;
            background: #ffffff !important;
            overflow: hidden;
            z-index: 2000 !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d6cfc3 !important;
            border-radius: 0.375rem;
            padding: 8px 10px !important;
            outline: none;
            background: #fff !important;
            color: #0c2244 !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 12px;
            color: #0c2244;
        }
        .select2-container--default .select2-results__option--selected {
            background: #efe8dc !important;
            color: #0c2244 !important;
        }
        .select2-container--default .select2-results__option--highlighted,
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background: #0c2244 !important;
            color: #fff !important;
        }
        .dark .select2-container .select2-selection--single {
            background: #06101c !important;
            border-color: rgba(240, 193, 75, 0.4) !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered,
        .dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #efe8dc !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #f0c14b transparent transparent transparent;
        }
        .dark .select2-dropdown {
            background: #071422 !important;
            border-color: rgba(240, 193, 75, 0.4) !important;
        }
        .dark .select2-search--dropdown .select2-search__field {
            background: #06101c !important;
            border-color: rgba(240, 193, 75, 0.4) !important;
            color: #efe8dc !important;
        }
        .dark .select2-container--default .select2-results__option {
            color: #efe8dc;
        }
        .dark .select2-container--default .select2-results__option--selected {
            background: #0c2244 !important;
            color: #f0c14b !important;
        }
        .dark .select2-container--default .select2-results__option--highlighted,
        .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background: #ee6b24 !important;
            color: #fff !important;
        }
        .dark .select2-container--default.select2-container--disabled .select2-selection--single {
            background: #06101c !important;
            opacity: 0.55;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('select').each(function () {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }
                if ($select.data('select2') === 'off' || $select.attr('data-select2') === 'off') {
                    return;
                }
                if ($select.closest('.dataTables_wrapper, .dataTables_length, .dataTables_filter').length) {
                    return;
                }
                if ($select.is('[x-model], [x-model\\.lazy]')) {
                    return;
                }

                var searchOff = $select.data('search') === 'off' || $select.find('option').length < 8;
                $select.select2({
                    width: '100%',
                    language: 'id',
                    allowClear: $select.data('allowClear') === true || $select.data('allow-clear') === true,
                    placeholder: $select.data('placeholder') || undefined,
                    minimumResultsForSearch: searchOff ? Infinity : 0,
                });

                if ($select.data('autosubmit') === true) {
                    $select.on('change', function () {
                        if (this.form) {
                            this.form.submit();
                        }
                    });
                }
            });
        });
    </script>
@endonce
