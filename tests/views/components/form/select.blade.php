<select name="{{ $attributes->get('name') }}">@foreach($attributes->get('options') as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
