{{-- Sonner Toast Component --}}
<link rel="stylesheet" href="{{ asset('css/sonner.css') }}">
<script src="{{ asset('js/sonner.js') }}"></script>

<script>
    // Smoothly route standard window.alert calls to Sonner warning toast
    window.originalAlert = window.alert;
    window.alert = function (message) {
        if (typeof toast !== 'undefined' && toast.warning) {
            toast.warning(String(message));
        } else {
            window.originalAlert(message);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            toast.success("{{ addslashes(session('success')) }}");
        @endif

        @if(session('error'))
            toast.error("{{ addslashes(session('error')) }}");
        @endif

        @if(session('info'))
            toast.info("{{ addslashes(session('info')) }}");
        @endif

        @if(session('warning'))
            toast.warning("{{ addslashes(session('warning')) }}");
        @endif

        @if($errors->any())
            @if($errors->count() === 1)
                toast.error("Validation Error", {
                    description: "{{ addslashes($errors->first()) }}"
                });
            @else
                toast.error("Validation Error ({{ $errors->count() }} issues)", {
                    description: "{{ addslashes($errors->first()) }}"
                });
            @endif
        @endif
    });
</script>
