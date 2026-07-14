<div class="row-actions">
    <button type="button" class="edit-stock" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></button>
    <form method="POST" action="{{ route('stock.destroy', $id) }}" onsubmit="return confirm('{{ __('Delete this record?') }}')" style="display:inline">
        @csrf @method('DELETE')
        <button type="submit" class="danger" title="{{ __('Delete') }}"><i class="bi bi-trash3"></i></button>
    </form>
</div>
