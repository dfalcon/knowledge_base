<x-mail.layout title="Welcome to IntelliBase">
    <h1 style="margin:0 0 8px; font-size:20px; color:#111827;">Welcome, {{ $user->name }}! 🎉</h1>
    <p style="margin:0 0 24px; font-size:14px; color:#6b7280; line-height:1.5;">
        Your account has been approved. You now have access to IntelliBase — upload documents
        and get instant AI-powered answers from your team's knowledge.
    </p>

    <a href="{{ config('app.url') }}"
       style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:12px 24px; border-radius:8px;">
        Get started
    </a>

    <p style="margin:24px 0 0; font-size:13px; color:#9ca3af; line-height:1.5;">
        Signed in as {{ $user->email }}.
    </p>
</x-mail.layout>
