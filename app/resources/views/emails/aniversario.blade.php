@php
    use Illuminate\Support\Str;

    $nome = $nome ?? ($fromName ??'Amigo(a)');

    $markdown = file_get_contents(resource_path('views/emails/aniversario.md'));

    $markdown = str_replace('{{ $nome }}', $nome, $markdown);
    $html = Str::markdown($markdown);

@endphp

{!! $html !!}


