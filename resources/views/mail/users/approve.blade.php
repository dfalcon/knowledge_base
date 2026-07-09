<x-mail.layout title="Approval required">
    <h1 style="margin:0 0 8px; font-size:20px; color:#111827;">New user awaiting approval</h1>
    <p style="margin:0 0 24px; font-size:14px; color:#6b7280; line-height:1.5;">
        A new employee has registered. Review the details and approve their access.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb; border-radius:8px; margin-bottom:24px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 6px; font-size:13px; color:#111827;"><strong>Name:</strong> {{ $user->name }}</p>
                <p style="margin:0; font-size:13px; color:#111827;"><strong>Email:</strong> {{ $user->email }}</p>
            </td>
        </tr>
    </table>

    <a href="{{ config('app.url') }}/api/admin/users/pending"
       style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:12px 24px; border-radius:8px;">
        Review requests
    </a>
</x-mail.layout>
