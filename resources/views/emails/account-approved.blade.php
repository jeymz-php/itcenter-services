<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Approved</title>
</head>
<body style="margin:0;padding:0;background:#f5f7f6;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7f6;padding:32px 16px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 5px 22px rgba(10,51,35,.09)">
<tr><td style="background:linear-gradient(135deg,#124530,#249660);padding:24px 28px;color:#fff">
  <div style="font-size:17px;font-weight:800">UCC IT Center Services</div>
  <div style="font-size:12px;color:rgba(255,255,255,.72);margin-top:4px">Account Approval Notification</div>
</td></tr>
<tr><td style="padding:28px">
  <div style="width:58px;height:58px;border-radius:16px;background:#e4f7ef;color:#1e7d4f;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:18px">✓</div>
  <p style="margin:0 0 8px;font-size:18px;font-weight:800;color:#1e3530">Your account has been approved</p>
  <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#4d6b61">Hi {{ $user->first_name }}, an IT Center administrator approved your previously pending account. You may now use the available IT Center services.</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2fbf7;border:1px solid #a8e8cc;border-radius:10px;margin-bottom:20px">
    <tr><td style="padding:15px 17px;font-size:13px;line-height:1.7;color:#124530">
      <strong>ID Number:</strong> {{ $user->id_number }}<br>
      <strong>Campus:</strong> {{ config('campuses.'.$user->campus, $user->campus) }}<br>
      <strong>Status:</strong> Active / Approved
    </td></tr>
  </table>

  <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:20px"><tr><td style="border-radius:8px;background:#1e7d4f">
    <a href="{{ $loginUrl }}" target="_blank" style="display:inline-block;padding:12px 22px;color:#fff;text-decoration:none;font-size:13px;font-weight:700">Open IT Center Services</a>
  </td></tr></table>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#e3f2fd;border-left:3px solid #1565c0;border-radius:8px">
    <tr><td style="padding:13px 15px;font-size:12px;line-height:1.6;color:#0d477e">Please review the <strong>User Manual</strong> or <strong>Infographics</strong> before submitting your first service request.</td></tr>
  </table>
</td></tr>
<tr><td style="padding:17px 28px;border-top:1px solid #f0f4f2;font-size:11px;line-height:1.6;color:#8aa89f">This is an automated email from the UCC IT Center Services System.</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
