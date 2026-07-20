<x-mail.layout title="Document indexed">
    <h1 style="margin:0 0 8px; font-size:20px; color:#111827;">Your document is ready 🔎</h1>
    <p style="margin:0 0 24px; font-size:14px; color:#6b7280; line-height:1.5;">
        "{{ $document->title }}" has been indexed and is now searchable in IntelliBase.
    </p>

    <a href="{{ config('app.url') }}"
       style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:12px 24px; border-radius:8px;">
        Open IntelliBase
    </a>
</x-mail.layout>
