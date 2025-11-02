<form method="POST" action="{{ $action }}" @if ($hasFiles) enctype="multipart/form-data" @endif class="dynamic-crud-form">
    <input type="hidden" name="csrf_token" value="{{ $csrfToken }}">
    
    @if ($id)
        <input type="hidden" name="id" value="{{ $id }}">
    @endif
    
    @foreach ($fields as $field)
        {!! $field !!}
    @endforeach
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        @if ($cancelUrl)
            <a href="{{ $cancelUrl }}" class="btn btn-secondary">{{ $cancelLabel }}</a>
        @endif
    </div>
</form>
