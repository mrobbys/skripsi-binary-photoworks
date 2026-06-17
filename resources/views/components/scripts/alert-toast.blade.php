<script type="module">

    @if (session()->has('alert'))
        Modal.fire({
            icon: '{{ e(session('alert.type')) }}',
            title: '{{ e(session('alert.title')) }}',
            text: '{{ e(session('alert.message')) }}',
            confirmButtonText: 'OK'
        });
    @endif

    @if (session()->has('toast'))
        Toast.fire({
            icon: '{{ e(session('toast.type')) }}',
            title: '{{ e(session('toast.title')) }}',
        })
    @endif
</script>
