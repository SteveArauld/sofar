<x-mail::message>
# Nova mensagem de apoio ao cliente

| | |
| :-- | :-- |
| Nome | {{ $data['name'] }} |
| Email | {{ $data['email'] }} |
| Assunto | {{ $data['subject'] }} |

**Mensagem**

{{ $data['message'] }}

<x-mail::subcopy>
Responda diretamente a este email para contactar {{ $data['name'] }}.
</x-mail::subcopy>
</x-mail::message>
