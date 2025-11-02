<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required) <span class="required">*</span>@endif</label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        value="{{ $value }}"
        @if ($required) required @endif
        @if ($readonly) readonly @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($min) min="{{ $min }}" @endif
        @if ($max) max="{{ $max }}" @endif
        @if ($minlength) minlength="{{ $minlength }}" @endif
        @if ($pattern) pattern="{{ $pattern }}" @endif
        class="form-control"
    >
    
    @if ($tooltip)
        <small class="form-text">{{ $tooltip }}</small>
    @endif
    
    @if ($error)
        <div class="error-message">{{ $error }}</div>
    @endif
</div>
